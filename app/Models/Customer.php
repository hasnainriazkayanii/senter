<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\Null_;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'dob',
        'bio',
        'is_verified',
        'sports',
    ];
    protected $hidden = [
        'password',
    ];
    protected $appends = ['profile_image'];


    public function posts()
    {
        return $this->hasMany(Post::class, 'customer_id', 'id');
    }
    public function likes()
    {
        return $this->hasMany(PostLike::class, 'customer_id', 'id');
    }
    public function comments()
    {
        return $this->hasMany(PostLike::class, 'customer_id', 'id');
    }

    public function getProfileImageAttribute()
    {
        $media = $this->getMedia('profile_images'); //or however you want to manipulate it
        if (empty($media) || count($media) == 0) {
            $this->image = Null;
        } else {
            $this->image = $media[0];
        }
        $this->clearMediaCollection('media');
        return $this->image;
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'customer_id', 'id');
    }

    public function followings()
    {
        return $this->belongsToMany(Customer::class, 'followers', 'follower_id', 'customer_id');
    }
}
