<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

     /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('orders');
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'customer_name' => $this->order->customer->name,
            'phone' => $this->order->phone,
            'email' => $this->order->email,
            'delivery_boy_id' => $this->order->delivery_boy_id,
            'items' => $this->order->orderItems->map(function ($item) {
                return [
                    'vendor'=>$item->product->vendor->store_name,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }),
            'total_price' => $this->order->total_price,
            'location' => $this->order->location,
            'floor' => $this->order->floor,
            'building_near_to' => $this->order->building_near_to,
            'extra_address_information' => $this->order->extra_address_information,
            'status' => $this->order->status,
        ];
    }
}
