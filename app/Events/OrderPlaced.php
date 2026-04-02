<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['items.menuItem', 'items.variant', 'restaurantTable']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('kitchen'),
            new Channel('orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id'           => $this->order->id,
                'table_number' => $this->order->restaurantTable->number,
                'status'       => $this->order->status,
                'source'       => $this->order->source,
                'notes'        => $this->order->notes,
                'total'        => $this->order->total,
                'created_at'   => $this->order->created_at->toISOString(),
                'items'        => $this->order->items->map(fn ($item) => [
                    'id'             => $item->id,
                    'name'           => $item->menuItem->name,
                    'variant'        => $item->variant?->name,
                    'quantity'       => $item->quantity,
                    'notes'          => $item->notes,
                    'kitchen_status' => $item->kitchen_status,
                ]),
            ],
        ];
    }
}
