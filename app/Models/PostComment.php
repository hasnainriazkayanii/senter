<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'customer_id',
        'comment',
    ];
    public function comment_by(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function post(){
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
    public function sub_comments(){
        return $this->hasMany(SubComment::class, 'comment_id', 'id');
    }

}
