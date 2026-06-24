<?php

/*
|--------------------------------------------------------------------------
| Backup — dependency-free, shared-hosting friendly
|--------------------------------------------------------------------------
|
| Each run produces ONE zip:  backup-{type}-{Y-m-d-His}.zip
|   ├─ database.sql.gz   (mysqldump, or pure-PHP PDO fallback if exec is off)
|   ├─ env               (a copy of .env — secrets needed to restore)
|   ├─ files/…           (storage/app/public — only for weekly/monthly by default)
|   └─ manifest.json     (metadata + sha256)
|
| Backups are written locally first, then optionally copied to an off-site disk
| (real disaster recovery — a backup on the same host dies with the host).
|
*/

return [

    // Local working copy: storage/app/{path}/{type}/…
    'path' => env('BACKUP_PATH', 'backups'),

    // OFF-SITE copy for disaster recovery. Set to a configured filesystem disk
    // (e.g. 's3', or an 'ftp'/'sftp' disk you add). Null = local only (NOT DR).
    'offsite_disk' => env('BACKUP_OFFSITE_DISK'),
    'offsite_path' => env('BACKUP_OFFSITE_PATH', 'equator-backups'),

    // Database connection to dump.
    'database' => env('DB_CONNECTION', 'mysql'),

    // Uploaded files / storage to archive (weekly + monthly).
    'include' => [
        storage_path('app/public'),
    ],

    // Regenerable / noise to skip.
    'exclude' => [
        storage_path('app/public/_audit_tmp'),
        storage_path('app/purifier'),
    ],

    'include_env' => true,

    // Set true to also bundle files in the DAILY backup (heavier).
    'daily_includes_files' => env('BACKUP_DAILY_FILES', false),

    // How many to keep per tier.
    'retention' => [
        'daily' => env('BACKUP_KEEP_DAILY', 7),
        'weekly' => env('BACKUP_KEEP_WEEKLY', 5),
        'monthly' => env('BACKUP_KEEP_MONTHLY', 12),
    ],

    // backup:verify fails if the newest backup is older than this.
    'max_age_hours' => env('BACKUP_MAX_AGE_HOURS', 26),

    'process_timeout' => 1800, // seconds for mysqldump
];
