<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScrapingCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The message to include in the notification.
     *
     * @var string
     */
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Product Scraping Completed Successfully')
            ->line('The product scraping process has completed successfully.')
            ->line($this->message)
            ->action('View Products', url('/admin/products'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Scraping Completed',
            'message' => $this->message,
            'type' => 'success',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

class ScrapingFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The error message to include in the notification.
     *
     * @var string
     */
    protected $message;

    /**
     * Whether this is a critical failure.
     *
     * @var bool
     */
    protected $critical;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, bool $critical = false)
    {
        $this->message = $message;
        $this->critical = $critical;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Use additional channels like Slack for critical failures
        if ($this->critical) {
            return ['mail', 'database', 'slack'];
        }
        
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->critical ? 'CRITICAL: Product Scraping Failed' : 'Product Scraping Failed')
            ->line($this->critical ? 'A critical error occurred during the product scraping process.' : 'An error occurred during the product scraping process.')
            ->line($this->message)
            ->action('View Logs', url('/admin/logs'));
            
        if ($this->critical) {
            $mail->error();
        }
        
        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->critical ? 'Critical Scraping Failure' : 'Scraping Failed',
            'message' => $this->message,
            'type' => $this->critical ? 'critical' : 'error',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}