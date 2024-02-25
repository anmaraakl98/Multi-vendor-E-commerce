<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'delivery_boy_id',
        'total_price',
        'location',
        'floor',
        'building_near_to',
        'extra_address_information',
        'phone',
        'email',
        'status',
        'longitude',
        'latitude'
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function deliveryBoy()
    {
        return $this->belongsTo(DeliveryBoy::class);
    }
    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}
