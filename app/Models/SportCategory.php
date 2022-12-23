<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SportCategory extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia;
    protected $fillable = [
        'title',
    ];
    protected $appends = ['icon'];

    public function getIconAttribute()
    {
        $media= $this->getMedia('icons'); //or however you want to manipulate it
        if(empty($media ) || count($media)==0){
            $this->image=Null;
        }
        else{
            $this->image=$media[0]->getFullUrl();
        }
        $this->clearMediaCollection('media');
        return $this->image;
        
    }
}
