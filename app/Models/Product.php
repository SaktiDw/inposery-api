<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'sell_price',
        'store_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($product) { // before delete() method call this
            $product->transaction()->each(function ($transaction) {
                $transaction->delete(); // <-- direct deletion
            });
            // do the rest of the cleanup...
        });
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }

    public function getFullUrlMediaAttribute()
    {
        return $this->getMedia()->map(function ($mediaObject) {
            $mediaObject->full_url = $mediaObject->getFullUrl();
            return $mediaObject;
        });
    }
}
