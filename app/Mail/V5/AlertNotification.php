<?php

namespace App\Mail\V5;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $alert;
    public string $dashboardUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(array $alert)
    {
        $this->alert = $alert;
        $this->dashboardUrl = route('v5.logs.dashboard');
        
        // Set queue priority based on alert priority
        $this->onQueue($this->getQueueByPriority($alert['priority'] ?? 'medium'));
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->getSubjectByPriority();
        
        return $this->view('emails.v5.alert-notification')
                    ->subject($subject)
                    ->with([
                        'alert' => $this->alert,
                        'dashboardUrl' => $this->dashboardUrl,
                        'isHighPriority' => in_array($this->alert['priority'] ?? '', ['high', 'critical']),
                        'isCritical' => ($this->alert['priority'] ?? '') === 'critical',
                    ]);
    }

    /**
     * Get queue name based on priority
     */
    private function getQueueByPriority(string $priority): string
    {
        return match ($priority) {
            'critical' => 'alerts-critical',
            'high' => 'alerts-high',
            'medium' => 'alerts-medium',
            default => 'alerts-low',
        };
    }

    /**
     * Get email subject based on priority
     */
    private function getSubjectByPriority(): string
    {
        $priority = strtoupper($this->alert['priority'] ?? 'MEDIUM');
        $type = ucwords(str_replace('_', ' ', $this->alert['type'] ?? 'System Alert'));
        
        $prefix = match ($this->alert['priority'] ?? 'medium') {
            'critical' => '🚨 CRITICAL ALERT',
            'high' => '⚠️ HIGH PRIORITY ALERT',
            'medium' => '📊 SYSTEM ALERT',
            default => '📋 SYSTEM NOTIFICATION',
        };

        return "{$prefix}: {$type} - Boukii V5";
    }
}