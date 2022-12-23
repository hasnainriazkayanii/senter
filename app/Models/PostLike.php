<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'customer_id',
    ];

    public function liked_by(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function post(){
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
}
