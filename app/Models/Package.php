<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'details', 'is_active'];

    public function images()
    {
        return $this->hasMany(PackageImage::class);
    }

    public function prices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getCurrentPriceAttribute()
    {
        $today = now();
        
        // Check for date range price first
        $dateRangePrice = $this->prices()
            ->where('price_type', 'date_range')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
            
        if ($dateRangePrice) {
            return $dateRangePrice;
        }
        
        // Check for weekday/weekend price
        $dayOfWeek = $today->dayOfWeek;
        $isWeekend = in_array($dayOfWeek, [5, 6, 0]); // Fri, Sat, Sun
        
        $priceType = $isWeekend ? 'weekend' : 'weekday';
        
        return $this->prices()
            ->where('price_type', $priceType)
            ->first();
    }
}
