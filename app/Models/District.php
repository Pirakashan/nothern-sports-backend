<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact',
        'working_hours',
    ];

    public function facilities()
    {
        return $this->hasMany(Facility::class);
    }

    public function sports()
    {
        return $this->hasMany(Sport::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function subAdmins()
    {
        return $this->hasMany(User::class)->where('role', 'sub_admin');
    }
}
