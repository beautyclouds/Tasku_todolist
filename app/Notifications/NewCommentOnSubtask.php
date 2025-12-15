<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;
use App\Models\Comment;
use App\Models\Subtask;
use App\Models\BoardCard; // Perlu untuk mengambil Card Title

class NewCommentOnSubtask extends Notification
{
    use Queueable;

    protected User $actor;
    protected Comment $comment;
    protected Subtask $subtask;
    protected BoardCard $card;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $actor, Comment $comment, Subtask $subtask, BoardCard $card)
    {
        $this->actor = $actor;
        $this->comment = $comment;
        $this->subtask = $subtask;
        $this->card = $card;
    }

    /**
     * Get the notification's delivery channels.
     * Kita hanya butuh channel 'database' untuk notif popover/list.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * Ini adalah data JSON yang akan masuk ke kolom 'data' di tabel 'notifications'.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'action' => 'New Comment', // Untuk logic generateNotifText di Vue
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'subtask_id' => $this->subtask->id,
            'subtask_name' => $this->subtask->title,
            'card_id' => $this->card->id,
            'card_title' => $this->card->title,
            'message_preview' => \Illuminate\Support\Str::limit($this->comment->content, 50),
        ];
    }
}