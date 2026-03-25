<?php
namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Project $project,
        private User    $requestedBy,
        private string  $reportType = 'summary'
    ) {}

    public function handle(ReportService $reportService): void
    {
        $data = match($this->reportType) {
            'summary'  => $reportService->getProjectSummary($this->project),
            'velocity' => $reportService->getVelocityChart($this->project),
            default    => [],
        };

        $pdf  = Pdf::loadView("reports.pdf.{$this->reportType}", [
            'project' => $this->project,
            'data'    => $data,
        ])->setPaper('a4');

        $filename = "reports/{$this->project->slug}-{$this->reportType}-".now()->format('Ymd').".pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $this->requestedBy->notify(new \App\Notifications\ReportReadyNotification($filename));
    }
}
