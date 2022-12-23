<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = [
        'title',
        'description',
        'status',
        'customer_id',
        'type',
        'sport_category_id',
        'is_test',
    ];
    protected $appends = ['images','thumbnail','is_liked','is_commented'];
    

    public function comments(){
        return $this->hasMany(PostComment::class, 'post_id', 'id');
    }
    public function likes(){
        return $this->hasMany(PostLike::class, 'post_id', 'id');
    }
    
    public function shares(){
        return $this->hasMany(PostShare::class, 'post_id', 'id');
    }
    public function owner(){
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
    public function sportcategory(){
        return $this->belongsTo(SportCategory::class, 'sport_category_id', 'id');
    }
   
    public function getImagesAttribute()
    {
        $media= $this->getMedia('images'); //or however you want to manipulate it
        if(empty($media ) || count($media)==0){
            $this->image=Null;
        }
        else{
            $this->image=$media[0];
        }
        $this->clearMediaCollection('media');
        return $this->image;
        
    }
    public function getIsLikedAttribute()
    {
        if(request()->is_liked_by){
            $this->liked = $this->likes()->where('customer_id',  request()->is_liked_by)->exists();
            return $this->liked;
        }
        return false;
        
    }

    public function getIsCommentedAttribute()
    {
        if(request()->is_liked_by){
            $this->commented = $this->comments()->where('customer_id',  request()->is_liked_by)->exists();
            return $this->commented;
        }
        return false;
        
    }

    public function getThumbnailAttribute()
    {
        $media= $this->getMedia('thumbnails'); //or however you want to manipulate it
        if(empty($media ) || count($media)==0){
            $this->image=Null;
        }
        else{
            $this->image=$media[0];
        }
        $this->clearMediaCollection('media');
        return $this->image;
        
    }

}
