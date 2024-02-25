<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\DeliveryBoy;
use App\Models\DeliveryCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function getOrderedOrders()
    {
        $orders = Order::where('status', 'ordered')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'orders in wait',
            'orders' => $orders,
        ]);
    }
    public function getDeliveredOrders(){
        $orders = Order::where('status', 'delivered')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'orders in delivered',
            'orders' => $orders,
            ]);
    }
    
    public function takeOrder( $order_id)
    {
        $user = Auth::user();
            $deliveryBoy = $user->deliveryBoy->id;
            $order = Order::where('id', $order_id)->first();
            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                    
                ]);
            }
            if($order->status=='on_the_way' && $order->delivery_boy_id ==$deliveryBoy){
                $orderItems = $this->getOrderItemsInfo($order->orderItems);
                return response()->json([
                    'status' => 'error',
                    'message' => 'you had already taken this order',
                    'order_id' => $order->id,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'order_details' => $orderItems,
                ]);
            }elseif($order->status=='on_the_way'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already taken ',
                    
                ]);
            }
            $order->delivery_boy_id = $deliveryBoy;
            $order->status = 'on_the_way';
            $order->save();
            $orderItems = $this->getOrderItemsInfo($order->orderItems);

            return response()->json([
                'status' => 'success',
                'message'=> 'this order for you, deliver it faster as you can',
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'order_details' => $orderItems,
            ]);
        
    }

    public function getOrderItemsInfo($Items){
        $orderItems = [];
        foreach ($Items as $orderItem) {
            $product = Products::with('vendor')->find($orderItem->product_id);
            $vendor = $product->vendor;
            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'store_id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_address' => $vendor->store_address,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->price,
            ];
        }
        return $orderItems;
    }
    public function setOrderDelivered($order_id)
    {
        $user= Auth::user();
        $order = Order::where('id', $order_id)->where('customer_id',$user->customer->id)->first();
        if(!$order){
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ]);
        }
        if($order->status !== 'on_the_way'){
            return response()->json([
                'status' => 'error',
                'message' => 'Order not on the way',
                ]);
        }
        if($order->status === 'delivered'){
            return response()->json([
                'status' => 'error',
                'message' => 'Order already delivered',
                ]);
        }
        $order->status = 'delivered';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order marked as delivered',
        ]);
    }
}
