<?php

namespace App\Services;

use App\Events\OrderItemStatusChanged;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Events\TableStatusChanged;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemLog;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\VoidLog;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private BomService $bom) {}

    public function getOrCreateTableSession(string $tableToken): array
    {
        $table = RestaurantTable::where('token', $tableToken)->firstOrFail();
        $session = $table->activeSession;

        if (!$session) {
            $session = TableSession::create([
                'restaurant_table_id' => $table->id,
            ]);
            $table->update(['status' => 'occupied']);
            broadcast(new TableStatusChanged($table))->toOthers();
        }

        return [$table, $session];
    }

    public function placeOrder(string $tableToken, array $items, ?int $userId = null, string $source = 'staff', ?string $notes = null): Order
    {
        return DB::transaction(function () use ($tableToken, $items, $userId, $source, $notes) {
            [$table, $session] = $this->getOrCreateTableSession($tableToken);

            $order = Order::create([
                'restaurant_table_id' => $table->id,
                'table_session_id'    => $session->id,
                'user_id'             => $userId,
                'status'              => 'confirmed',
                'source'              => $source,
                'notes'               => $notes,
            ]);

            foreach ($items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $unitPrice = $menuItem->price;

                if (!empty($item['variant_id'])) {
                    $variant = $menuItem->variants()->findOrFail($item['variant_id']);
                    $unitPrice += $variant->price_modifier;
                }

                $orderItem = OrderItem::create([
                    'order_id'       => $order->id,
                    'menu_item_id'   => $item['menu_item_id'],
                    'variant_id'     => $item['variant_id'] ?? null,
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $unitPrice,
                    'line_total'     => $unitPrice * $item['quantity'],
                    'notes'          => $item['notes'] ?? null,
                ]);

                OrderItemLog::create([
                    'order_item_id' => $orderItem->id,
                    'user_id'       => $userId,
                    'action'        => 'created',
                    'to_status'     => 'pending',
                    'ip_address'    => request()->ip(),
                ]);

                $this->bom->deductStock($orderItem);
            }

            $order->recalculateTotals();
            $order->load(['items.menuItem', 'restaurantTable']);

            broadcast(new OrderPlaced($order));

            return $order;
        });
    }

    public function markItemDone(OrderItem $orderItem, int $userId): OrderItem
    {
        $from = $orderItem->kitchen_status;
        $orderItem->update(['kitchen_status' => 'done']);

        OrderItemLog::create([
            'order_item_id' => $orderItem->id,
            'user_id'       => $userId,
            'action'        => 'status_changed',
            'from_status'   => $from,
            'to_status'     => 'done',
            'ip_address'    => request()->ip(),
        ]);

        broadcast(new OrderItemStatusChanged($orderItem));

        $order = $orderItem->order;
        $allDone = $order->items()
            ->where('kitchen_status', '!=', 'voided')
            ->where('kitchen_status', '!=', 'done')
            ->doesntExist();

        if ($allDone) {
            $order->update(['status' => 'ready']);
            broadcast(new OrderStatusChanged($order));
        }

        return $orderItem;
    }

    public function markItemInProgress(OrderItem $orderItem, int $userId): OrderItem
    {
        $from = $orderItem->kitchen_status;
        $orderItem->update(['kitchen_status' => 'in_progress']);

        OrderItemLog::create([
            'order_item_id' => $orderItem->id,
            'user_id'       => $userId,
            'action'        => 'status_changed',
            'from_status'   => $from,
            'to_status'     => 'in_progress',
            'ip_address'    => request()->ip(),
        ]);

        broadcast(new OrderItemStatusChanged($orderItem));

        return $orderItem;
    }

    public function voidItem(OrderItem $orderItem, string $reason, int $userId, ?int $approvedBy = null): OrderItem
    {
        return DB::transaction(function () use ($orderItem, $reason, $userId, $approvedBy) {
            $from = $orderItem->kitchen_status;
            $orderItem->update(['kitchen_status' => 'voided']);

            VoidLog::create([
                'order_item_id' => $orderItem->id,
                'user_id'       => $userId,
                'approved_by'   => $approvedBy,
                'reason'        => $reason,
            ]);

            OrderItemLog::create([
                'order_item_id' => $orderItem->id,
                'user_id'       => $userId,
                'action'        => 'voided',
                'from_status'   => $from,
                'to_status'     => 'voided',
                'ip_address'    => request()->ip(),
                'metadata'      => ['reason' => $reason],
            ]);

            $this->bom->reverseDeduction($orderItem);

            $orderItem->order->recalculateTotals();

            broadcast(new OrderItemStatusChanged($orderItem));

            return $orderItem;
        });
    }

    public function markServed(Order $order, int $userId): Order
    {
        $order->update(['status' => 'served']);
        broadcast(new OrderStatusChanged($order));
        return $order;
    }

    public function recordPayment(Order $order, string $method, float $amount, int $userId, ?string $reference = null, ?string $bankName = null): Payment
    {
        return DB::transaction(function () use ($order, $method, $amount, $userId, $reference, $bankName) {
            $payment = Payment::create([
                'order_id'   => $order->id,
                'user_id'    => $userId,
                'amount'     => $amount,
                'method'     => $method,
                'reference'  => $reference,
                'bank_name'  => $bankName,
            ]);

            $order->update(['status' => 'paid']);

            $session = $order->tableSession;
            if ($session && $session->isActive()) {
                $hasPendingOrders = $session->orders()
                    ->whereNotIn('status', ['paid', 'voided'])
                    ->exists();

                if (!$hasPendingOrders) {
                    $session->close();
                    $order->restaurantTable->update(['status' => 'cleaning']);
                    broadcast(new TableStatusChanged($order->restaurantTable));
                }
            }

            broadcast(new OrderStatusChanged($order));

            return $payment;
        });
    }
}
