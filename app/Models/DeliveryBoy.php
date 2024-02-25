<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'driver_license_id',
        'image'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
    return $this->hasMany(Order::class);
    }
}
