<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'sport_id',
        'type',
        'event_name',
        'sports_list',
        'billing_type',
        'price_per_hour',
        'price_per_day',
        'price_gov_schools',
        'price_club_institute',
        'price_intl_schools',
        'price_intl',
    ];

    protected function casts(): array
    {
        return [
            'price_per_hour' => 'decimal:2',
            'price_per_day' => 'decimal:2',
            'price_gov_schools' => 'decimal:2',
            'price_club_institute' => 'decimal:2',
            'price_intl_schools' => 'decimal:2',
            'price_intl' => 'decimal:2',
        ];
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
}
