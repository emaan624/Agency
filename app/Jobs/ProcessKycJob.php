<?php

namespace App\Jobs;

use App\Models\KycVerification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessKycJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly KycVerification $kyc) {}

    public function handle(NotificationService $notificationService): void
    {
        // Placeholder: auto-approve for demo; real implementation would verify documents
        $this->kyc->update(['status' => 'pending']);

        $notificationService->send(
            $this->kyc->user,
            'KYC Submitted',
            'Your KYC documents are under review. We will notify you within 24 hours.',
            'info'
        );
    }
}
