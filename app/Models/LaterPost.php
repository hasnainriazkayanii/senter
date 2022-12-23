<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaterPost extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'customer_id',
    ];

    public function customer(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function post(){
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
}
