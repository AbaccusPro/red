<?php

namespace App\Events;

use Cmgmyr\Messenger\Models\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessagePublished extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $message;
    public $receiver;
    public $sender;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message, $receiver)
    {
        $this->message = $message;
        $this->sender = $message->user;
        $this->receiver = $receiver;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->receiver->username.'-message-created'];
    }
}
