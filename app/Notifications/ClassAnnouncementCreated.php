<?php

namespace App\Notifications;

use App\Models\ClassAnnouncement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClassAnnouncementCreated extends Notification
{
    use Queueable;

    private $announcement;

    /**
     * Create a new notification instance.
     */
    public function __construct(ClassAnnouncement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Gửi qua database để hiển thị ở chuông thông báo
        // Có thể bổ sung 'mail' nếu muốn gửi email
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $classCode = $this->announcement->classRoom->class_code ?? '';
        $senderName = $this->announcement->teacher->user->name ?? 'Giáo viên';

        return [
            'type' => 'announcement',
            'announcement_id' => $this->announcement->id,
            'class_id' => $this->announcement->class_id,
            'class_code' => $classCode,
            'title' => "Thông báo mới",
            'message' => $this->announcement->title,
            'sender_name' => $senderName,
            'sender_image' => $this->announcement->teacher->user->avatar ?? null,
        ];
    }
}
