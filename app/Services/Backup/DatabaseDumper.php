<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Dumps a MySQL database to a gzipped .sql file. Uses the `mysqldump` binary when
 * available (fast, reliable); falls back to a pure-PHP PDO dump when exec/Process
 * is disabled (common on shared hosting). The output file is identical to restore:
 *
 *     gunzip -c database.sql.gz | mysql -u user -p dbname
 */
class DatabaseDumper
{
    public function __construct(private readonly string $connection) {}

    /** @return string the strategy used ('mysqldump' or 'php') */
    public function dump(string $gzPath): string
    {
        if ($this->processAvailable()) {
            try {
                $this->dumpViaMysqldump($gzPath);

                return 'mysqldump';
            } catch (\Throwable $e) {
                report($e); // fall back to the portable PHP dumper
                @unlink($gzPath);
            }
        }

        $this->dumpViaPhp($gzPath);

        return 'php';
    }

    private function config(): array
    {
        return config("database.connections.{$this->connection}");
    }

    private function processAvailable(): bool
    {
        if (! function_exists('proc_open')) {
            return false;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('proc_open', $disabled, true)) {
            return false;
        }

        $probe = new Process(['mysqldump', '--version']);
        $probe->setTimeout(15);

        try {
            $probe->run();

            return $probe->isSuccessful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function dumpViaMysqldump(string $gzPath): void
    {
        $c = $this->config();

        // Credentials via a temp options file so the password never appears in
        // the process list / argv.
        $cnf = tempnam(sys_get_temp_dir(), 'eqbk');
        file_put_contents($cnf, implode("\n", [
            '[client]',
            'host="'.($c['host'] ?? '127.0.0.1').'"',
            'port="'.($c['port'] ?? 3306).'"',
            'user="'.($c['username'] ?? '').'"',
            'password="'.($c['password'] ?? '').'"',
            '',
        ]));
        @chmod($cnf, 0600);

        $process = new Process([
            'mysqldump',
            '--defaults-extra-file='.$cnf,
            '--single-transaction',   // consistent InnoDB snapshot, no table locks
            '--skip-lock-tables',
            '--no-tablespaces',       // avoids PROCESS privilege errors on shared hosting
            '--default-character-set=utf8mb4',
            '--routines',
            '--events',
            $c['database'],
        ]);
        $process->setTimeout(config('backup.process_timeout', 1800));

        $gz = gzopen($gzPath, 'wb6');

        try {
            $process->run(function ($type, $buffer) use ($gz) {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buffer);
                }
            });
        } finally {
            gzclose($gz);
            @unlink($cnf);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('mysqldump failed: '.trim($process->getErrorOutput()));
        }
    }

    private function dumpViaPhp(string $gzPath): void
    {
        $pdo = DB::connection($this->connection)->getPdo();
        $database = $this->config()['database'];

        // Structure first (small, buffered).
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $creates = [];
        foreach ($tables as $table) {
            $row = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $creates[$table] = $row['Create Table'] ?? $row['Create View'] ?? null;
        }

        $gz = gzopen($gzPath, 'wb6');
        gzwrite($gz, "-- Equator portable PHP dump of `{$database}`\n");
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");

        // Stream data unbuffered so large tables don't exhaust memory.
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        try {
            foreach ($tables as $table) {
                if ($creates[$table] === null) {
                    continue;
                }

                gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n{$creates[$table]};\n\n");

                $stmt = $pdo->query("SELECT * FROM `{$table}`");
                while ($rowData = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $cols = '`'.implode('`,`', array_keys($rowData)).'`';
                    $vals = implode(',', array_map(
                        fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                        array_values($rowData)
                    ));
                    gzwrite($gz, "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n");
                }
                $stmt->closeCursor();
                gzwrite($gz, "\n");
            }

            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            gzclose($gz);
        }
    }
}
