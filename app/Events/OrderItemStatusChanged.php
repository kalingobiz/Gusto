<?php

namespace App\Events;

use App\Models\OrderItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderItem $orderItem)
    {
        $this->orderItem->load(['menuItem', 'order.restaurantTable']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('kitchen'),
            new Channel('orders.' . $this->orderItem->order_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order-item.status-changed';
    }

    public function broadcastWith(): array
    {
        return [
            'item' => [
                'id'             => $this->orderItem->id,
                'order_id'       => $this->orderItem->order_id,
                'table_number'   => $this->orderItem->order->restaurantTable->number,
                'name'           => $this->orderItem->menuItem->name,
                'quantity'       => $this->orderItem->quantity,
                'kitchen_status' => $this->orderItem->kitchen_status,
            ],
        ];
    }
}
