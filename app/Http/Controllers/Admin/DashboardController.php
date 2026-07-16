<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Message;
use App\Models\News;
use App\Models\Project;
use App\Models\Service;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->stats();
        $chart = $this->visitorChart();

        // Recent activity + system health are restricted to super admins. The flag
        // is passed to the view so Blade never re-resolves auth()->user() itself.
        /** @var Admin|null $admin */
        $admin = auth('admin')->user();

        $isSuperAdmin = (bool) $admin?->isSuperAdmin();

        $recentActivities = $isSuperAdmin
            ? ActivityLog::with('admin')->latest()->take(8)->get()
            : collect();

        $health = $isSuperAdmin ? $this->systemHealth() : null;

        return view('admin.dashboard', compact('stats', 'chart', 'recentActivities', 'health', 'isSuperAdmin'));
    }

    /*
    |--------------------------------------------------------------------------
    | System health — queue signals so failed emails/jobs are not silent.
    |--------------------------------------------------------------------------
    | failed_jobs > 0 means an email/notification exhausted its retries and did
    | NOT send. A large pending backlog usually means the schedule:run cron (and
    | thus the queue worker) is not running. Surfaced on the dashboard for admins.
    */

    private function systemHealth(): array
    {
        return [
            'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
            'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE REPORT (print-friendly HTML → Save as PDF from browser)
    |--------------------------------------------------------------------------
    */

    public function report()
    {
        return view('admin.reports.dashboard', $this->reportData());
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL (.xlsx)
    |--------------------------------------------------------------------------
    */

    public function export(): StreamedResponse
    {
        $data = $this->reportData();

        $spreadsheet = new Spreadsheet;

        /* ---- Sheet 1: Summary ---- */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');

        $sheet->setCellValue('A1', ($data['company'] ?? 'Equator Group').' — Dashboard Report');
        $sheet->mergeCells('A1:B1');
        $sheet->setCellValue('A2', 'Generated at');
        $sheet->setCellValue('B2', $data['generated_at']);

        $sheet->setCellValue('A4', 'Metric');
        $sheet->setCellValue('B4', 'Value');

        $rows = [
            ['Total Services', $data['stats']['services']],
            ['Total Projects', $data['stats']['projects']],
            ['Total Admin Users', $data['stats']['users']],
            ['Total Messages', $data['stats']['messages']],
            ['— Unread', $data['messages']['unread']],
            ['— Read', $data['messages']['read']],
            ['— Replied', $data['messages']['replied']],
            ['— Archived', $data['messages']['archived']],
            ['— Spam', $data['messages']['spam']],
            ['Projects — Planned', $data['projects']['planned']],
            ['Projects — Ongoing', $data['projects']['ongoing']],
            ['Projects — Completed', $data['projects']['completed']],
            ['Page Views (12 months)', $data['chart']['total_views']],
            ['Unique Visitors (all-time)', $data['chart']['total_visitors']],
        ];

        $r = 5;
        foreach ($rows as $row) {
            $sheet->setCellValue("A{$r}", $row[0]);
            $sheet->setCellValue("B{$r}", $row[1]);
            $r++;
        }

        $this->styleHeader($sheet, 'A1:B1');
        $this->styleHeader($sheet, 'A4:B4');
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(18);

        /* ---- Sheet 2: Visitors (12 months) ---- */
        $vSheet = $spreadsheet->createSheet();
        $vSheet->setTitle('Visitors (12 Months)');
        $vSheet->setCellValue('A1', 'Month');
        $vSheet->setCellValue('B1', 'Page Views');
        $vSheet->setCellValue('C1', 'Unique Visitors');

        $r = 2;
        foreach ($data['chart']['labels'] as $i => $label) {
            $vSheet->setCellValue("A{$r}", $label);
            $vSheet->setCellValue("B{$r}", $data['chart']['views'][$i] ?? 0);
            $vSheet->setCellValue("C{$r}", $data['chart']['visitors'][$i] ?? 0);
            $r++;
        }

        $this->styleHeader($vSheet, 'A1:C1');
        $vSheet->getColumnDimension('A')->setWidth(16);
        $vSheet->getColumnDimension('B')->setWidth(14);
        $vSheet->getColumnDimension('C')->setWidth(16);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'dashboard-report-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function stats(): array
    {
        return [
            'services' => Service::count(),
            'messages' => Message::count(),
            'unread_messages' => Message::where('status', Message::STATUS_UNREAD)->count(),
            'projects' => Project::count(),
            'users' => Admin::count(),
            'news' => News::count(),
            'published_news' => News::where('status', 'published')->count(),
        ];
    }

    private function reportData(): array
    {
        $messageCounts = Message::selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');
        $projectCounts = Project::selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');

        return [
            'company' => app_setting('company_name', 'Equator Group'),
            'generated_at' => now()->format('d M Y, H:i'),
            'stats' => $this->stats(),
            'messages' => [
                'unread' => (int) ($messageCounts['unread'] ?? 0),
                'read' => (int) ($messageCounts['read'] ?? 0),
                'replied' => (int) ($messageCounts['replied'] ?? 0),
                'archived' => (int) ($messageCounts['archived'] ?? 0),
                'spam' => (int) ($messageCounts['spam'] ?? 0),
            ],
            'projects' => [
                'planned' => (int) ($projectCounts['planned'] ?? 0),
                'ongoing' => (int) ($projectCounts['ongoing'] ?? 0),
                'completed' => (int) ($projectCounts['completed'] ?? 0),
            ],
            'chart' => $this->visitorChart(),
        ];
    }

    private function styleHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263592');
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Build a 12-month visitor analytics series (page views + unique visitors).
     */
    private function visitorChart(): array
    {
        $start = now()->copy()->startOfMonth()->subMonths(11);

        $views = Visitor::query()
            ->selectRaw("DATE_FORMAT(visited_at, '%Y-%m') as ym")
            ->selectRaw('COUNT(*) as total')
            ->where('visited_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $visitors = Visitor::query()
            ->selectRaw("DATE_FORMAT(visited_at, '%Y-%m') as ym")
            ->selectRaw('COUNT(DISTINCT ip_address) as total')
            ->where('visited_at', '>=', $start)
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $viewsSeries = [];
        $visitorsSeries = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->format('M Y');
            $viewsSeries[] = (int) ($views[$key] ?? 0);
            $visitorsSeries[] = (int) ($visitors[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'views' => $viewsSeries,
            'visitors' => $visitorsSeries,
            'total_views' => array_sum($viewsSeries),
            'total_visitors' => Visitor::distinct('ip_address')->count('ip_address'),
        ];
    }
}
