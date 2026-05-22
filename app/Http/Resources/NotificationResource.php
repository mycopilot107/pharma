<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->action_url,
            'priority' => $this->priority,
            'remind_at' => $this->remind_at?->toIso8601String(),
            'read_at' => $this->read_at?->toIso8601String(),
            'is_unread' => $this->isUnread(),
        ];
    }
}
