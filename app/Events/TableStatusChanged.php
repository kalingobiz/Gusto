<?php

namespace App\Events;

use App\Models\RestaurantTable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public RestaurantTable $table) {}

    public function broadcastOn(): array
    {
        return [new Channel('tables')];
    }

    public function broadcastAs(): string
    {
        return 'table.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'table_id' => $this->table->id,
            'number'   => $this->table->number,
            'status'   => $this->table->status,
        ];
    }
}
