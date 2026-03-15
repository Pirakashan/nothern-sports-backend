<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'slug',
        'description',
        'image',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sports()
    {
        return $this->hasMany(Sport::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
