<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'facility_id',
        'name',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function pricing_tables()
    {
        return $this->hasMany(PricingTable::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
