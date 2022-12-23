<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;
    protected $fillable = [
        'follower_id',
        'customer_id',
    ];

    public function followed_by(){
        return $this->hasOne(Customer::class, 'id', 'follower_id');
    }
    public function customer(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
