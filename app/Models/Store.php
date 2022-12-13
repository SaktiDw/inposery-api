<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class Store extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $table = 'stores';
    protected $fillable = [
        "name", "image", "user_id"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
    public function receipt()
    {
        return $this->hasMany(Receipt::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($store) { // before delete() method call this
            $store->product()->each(function ($product) {
                $product->delete(); // <-- direct deletion
            });
            $store->receipt()->each(function ($receipt) {
                $receipt->delete(); // <-- direct deletion
            });
            // do the rest of the cleanup...
        });
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300);
        // ->nonQueued();
        $this
            ->addMediaConversion('blur')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->blur(50);
        // ->nonQueued();
    }

    public function getFullUrlMediaAttribute()
    {
        return $this->getMedia()->map(function ($mediaObject) {
            $mediaObject->full_url = $mediaObject->getFullUrl();
            return $mediaObject;
        });
    }
}
