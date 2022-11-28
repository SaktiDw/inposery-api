<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        "store_id",
        "products",
        "total",
        "payment",
        "change",
        "discount",
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
