<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'district_id',
        'facility_id',
        'sport_id',
        'organization_type',
        'event_type',
        'booking_mode',
        'slots',
        'booking_date',
        'booking_end_date',
        'start_time',
        'end_time',
        'price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'booking_end_date' => 'date:Y-m-d',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'price' => 'decimal:2',
            'slots' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    // Check time overlap with existing confirmed bookings
    public static function hasOverlap(
        int $facilityId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $excludeId = null,
        ?string $bookingEndDate = null,
        ?array $requestedSlots = null
    ): bool {
        // If specific slots are requested, check slot-level availability
        if ($requestedSlots !== null && count($requestedSlots) > 0) {
            $bookedSlots = self::getBookedSlots($facilityId, $bookingDate, $startTime, $endTime, $excludeId, $bookingEndDate);
            return count(array_intersect($requestedSlots, $bookedSlots)) > 0;
        }

        // Otherwise fall back to facility-level overlap check
        $endDate = $bookingEndDate ?? $bookingDate;

        $query = self::where('facility_id', $facilityId)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($bookingDate, $endDate, $startTime, $endTime) {
                // Check if date ranges overlap
                $q->where(function ($dateQ) use ($bookingDate, $endDate) {
                    $dateQ->where('booking_date', '<=', $endDate)
                          ->where(function ($inner) use ($bookingDate) {
                              $inner->whereNotNull('booking_end_date')
                                    ->where('booking_end_date', '>=', $bookingDate);
                          })
                          ->orWhere(function ($inner) use ($bookingDate, $endDate) {
                              $inner->whereNull('booking_end_date')
                                    ->whereBetween('booking_date', [$bookingDate, $endDate]);
                          });
                })
                // Check if time ranges overlap
                ->where(function ($timeQ) use ($startTime, $endTime) {
                    $timeQ->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get all slot numbers that are already booked (confirmed/pending) for a given
     * facility, date, and time range.
     */
    public static function getBookedSlots(
        int $facilityId,
        string $bookingDate,
        string $startTime,
        string $endTime,
        ?int $excludeId = null,
        ?string $bookingEndDate = null
    ): array {
        $endDate = $bookingEndDate ?? $bookingDate;

        $query = self::where('facility_id', $facilityId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('slots')
            ->where(function ($q) use ($bookingDate, $endDate, $startTime, $endTime) {
                $q->where(function ($dateQ) use ($bookingDate, $endDate) {
                    $dateQ->where('booking_date', '<=', $endDate)
                          ->where(function ($inner) use ($bookingDate) {
                              $inner->whereNotNull('booking_end_date')
                                    ->where('booking_end_date', '>=', $bookingDate);
                          })
                          ->orWhere(function ($inner) use ($bookingDate, $endDate) {
                              $inner->whereNull('booking_end_date')
                                    ->whereBetween('booking_date', [$bookingDate, $endDate]);
                          });
                })
                ->where(function ($timeQ) use ($startTime, $endTime) {
                    $timeQ->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $bookedSlots = [];
        foreach ($query->get() as $booking) {
            $slots = is_array($booking->slots) ? $booking->slots : json_decode($booking->slots, true);
            if (is_array($slots)) {
                $bookedSlots = array_merge($bookedSlots, $slots);
            }
        }

        return array_values(array_unique(array_map('intval', $bookedSlots)));
    }
}
