<?php

namespace App\Listeners;

use App\Events\NewOrderPlaced;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendOrderNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(NewOrderPlaced $event)
    {
        Log::info('New order placed: ' . $event->order->id);

        $order = $event->order;
        $title = 'New Order Placed';
        $body = 'A new order has been placed.';
        $type = 'order';

        $deliveryBoys = User::where('role','deliveryBoy')->get();

        foreach ($deliveryBoys as $deliveryBoy) {
            $userFCMToken = $deliveryBoy->fcm_token;

            $requestData = [
                'to' => $userFCMToken,
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'order_id' => $order->id,
                ],
                'priority' => 'high',
            ];

            if ($deliveryBoy->notification) {
                Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . env('FIREBASE_SERVER_KEY'),
                ])->post('https://fcm.googleapis.com/fcm/send', $requestData);
            }
        }
    }

    public function failed(NewOrderPlaced $event, $exception)
    {
        Log::error("Failed to send order notification: {$exception->getMessage()}");
    }
}
