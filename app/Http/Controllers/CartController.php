<?php

namespace App\Http\Controllers;

use App\Events\NewOrderPlaced;
use App\Models\CartItem;
use App\Models\DeliveryCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use App\Models\Vendor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addItem(Request $request)
    {
        $user = Auth::user();
        $customer = $user->customer;
        $cart = $customer->cart;

        $product = Products::find($request->product_id);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $quantity = $request->input('quantity', 1);
        $price = $product->price * $quantity;
        $existingItem = $cart->cartItems()->where('product_id', $product->id)->first();

        if ($existingItem) {
            // If the item already exists in the cart, update the quantity and price
            $existingItem->quantity += $quantity;
            $existingItem->price += $price;
            $existingItem->save();
        } else {
            // Create a new cart item
            $cart->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'cartItems' => $cart->cartItems
            ], Response::HTTP_OK);
    }

    public function getCartItems()
    {
        $user = auth()->user();
        $customer = $user->customer;
        $cart = $customer->cart;
        $cartItems = $cart->cartItems;

        return response()->json(['cart_items' => $cartItems], 200);
    }
    public function updateCartItem(Request $request,  $cartItem_id)
    {
        $user = auth()->user();
        $customer = $user->customer;
        $cart = $customer->cart;
        $cartItem = CartItem::where('id',$cartItem_id)->first();
        if(!$cartItem){
            return response()->json([
                'status' => 'error',
                'message' => 'Cart item not found'
                ], Response::HTTP_NOT_FOUND);
        }
        if ($cartItem->cart_id !== $cart->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $product = Products::find($cartItem->product_id);
        if(!$product){
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
                ], Response::HTTP_NOT_FOUND);
        }
        $cartItem->quantity = $request->input('quantity', 1);
        $cartItem->price = $product->price * $cartItem->quantity; // Calculate the new price
        $cartItem->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item updated',
            'cartItem' => $cartItem
        ], 200);
    }
    public function deleteCartItem(CartItem $cartItem)
    {
        $user = auth()->user();
        $customer = $user->customer;
        $cart = $customer->cart;

        if ($cartItem->cart_id !== $cart->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted'], 200);
    }

    // public function placeOrder(Request $request)
    // {
    //     $rules = [
    //         'location' => 'required|string|max:255',
    //         'floor' => 'required|string|max:255',
    //         'building_near_to' => 'required|string|max:255',
    //         'extra_address_information' => 'nullable|string|max:255',
    //         'phone' => 'required|string|max:255',
    //         'email' => 'nullable|email|max:255',
    //     ];
    
    //     // Run the validation
    //     $validator = Validator::make($request->all(), $rules);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors()->first()
    //         ], Response::HTTP_BAD_REQUEST);
    //     }
    //     $user = Auth::user();
    //     $customer = $user->customer;
    //     $cart = $customer->cart;
    //     $items = $cart->cartItems;
    //     if(!$items){
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Cart is empty'
    //             ], Response::HTTP_BAD_REQUEST);
    //     }
    //     // Create a new order
    //     $order = new Order();
    //     $order->customer_id = $customer->id;
    //     $order->location = $request->input('location');
    //     $order->floor = $request->input('floor');
    //     $order->building_near_to = $request->input('building_near_to');
    //     $order->extra_address_information = $request->input('extra_address_information');
    //     $order->phone = $request->input('phone');
    //     $order->email = $request->input('email');
    //     $order->save();

    //     // Create order items
    //     $totalPrice = 0;
    //     foreach ($items as $item) {
    //         $product = Products::find($item->product_id);
    //         $orderItem = new OrderItem();
    //         $orderItem->order_id = $order->id;
    //         $orderItem->product_id = $item->product_id;
    //         $orderItem->quantity = $item->quantity;
    //         $orderItem->price = $product->price * $item->quantity;
    //         $orderItem->save();
    //         $totalPrice += $orderItem->price;
    //     }
    //     $order->total_price = $totalPrice;
    //     $order->save();

    //     // Empty customer's cart
    //     $cart->cartItems()->delete();

    //     // Return JSON response with order details
    //     return response()->json([
    //         'order_number' => $order->id,
    //         'total_price' => $totalPrice,
    //         'date_and_time' => $order->created_at
    //     ], Response::HTTP_OK);
    // }
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $customer = $user->customer;
        $cart = $customer->cart;

        // Validate the request data
        $request->validate([
            'location' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'floor' => 'required|string',
            'building_near_to' => 'required|string',
            'extra_address_information' => 'string',
            'phone' => 'required|string',
            'email' => 'nullable|email',
        ]);

        // Create a new order
        $order = Order::create([
            'customer_id' => $customer->id,
            'location' => $request->location,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'floor' => $request->floor,
            'building_near_to' => $request->building_near_to,
            'extra_address_information' => $request->extra_address_information,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);
        $totalPrice = 0;
        $maxDistance = 0;

        $cartItemsCount = count($cart->cartItems);
        if($cartItemsCount ==0){
            return response()->json([
                'status'=>'error',
                'message' => 'Cart is empty'
            ], Response::HTTP_FORBIDDEN);
        }

        // Get the customer's latitude and longitude
        $customerLocation = $this->getLatLong($request->customer_lat, $request->customer_lng);
        // Add the cart items to the order
        foreach ($cart->cartItems as $cartItem) {
            $product = Products::find($cartItem->product_id);
            $vendor = Vendor::find($product->vendor_id);

            // Create a new order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $cartItem->quantity,
                'price' => $product->price * $cartItem->quantity,
            ]);
            $totalPrice += $cartItem->price;

           // Calculate the distance between the customer and the vendor
        $vendorLocation = $this->getLatLong($vendor->lat, $vendor->lng);
        $distance = $this->calculateDistance($customerLocation, $vendorLocation);
        if ($distance > $maxDistance) {
            $maxDistance = $distance;
        }
            // Remove the cart item
            $cartItem->delete();
        }
        $deliveryCost = $this->calculateDeliveryCost($maxDistance);

        $order->total_price = $totalPrice;
        $order->save();
        // Dispatch the event
        event(new NewOrderPlaced($order));
        // Return the order information
        return response()->json([
            'status' => 'success',
            'message' => 'Order placed',
            'order' => $order,
            'delivery-cost' => $deliveryCost,
        ], Response::HTTP_OK);
    }

    private function getLatLong($lat, $lng)
    {
        return compact('lat', 'lng');
    }


    private function calculateDistance($point1, $point2)
    {
        $lat1 = $point1['lat'];
        $lng1 = $point1['lng'];
        $lat2 = $point2['lat'];
        $lng2 = $point2['lng'];

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }
    private function calculateDeliveryCost($distance)
    {
        // Retrieve the delivery cost from the delivery_costs table based on the distance
        $deliveryCost = DeliveryCost::where('distance', '>=', $distance)
            ->orderBy('distance', 'asc')
            ->pluck('cost')
            ->first();

        return $deliveryCost;
    }
}
