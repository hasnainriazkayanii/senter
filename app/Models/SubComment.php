<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubComment extends Model
{
    use HasFactory;
    protected $fillable = [
        'comment_id',
        'customer_id',
        'comment',
    ];
    public function comment_by(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
