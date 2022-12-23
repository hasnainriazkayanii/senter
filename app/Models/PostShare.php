<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostShare extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'customer_id',
        'shared_to',
        'thoughts',
    ];

    public function shared_by(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function post(){
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
    function post_likes()
    {
        return $this->hasManyThrough(PostLike::class, Post::class);
    }
}
