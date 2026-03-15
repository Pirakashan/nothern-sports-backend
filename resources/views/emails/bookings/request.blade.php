<x-mail::message>
# New Booking Request

A new booking request has been submitted for your district.

**Booking Details:**
- **Customer:** {{ $booking->guest_name }}
- **Email:** {{ $booking->guest_email }}
- **Facility:** {{ $booking->facility->name }}
- **Sport:** {{ $booking->sport ? $booking->sport->name : 'N/A' }}
- **Date:** {{ $booking->booking_date->format('Y-m-d') }}
- **Time:** {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}

<x-mail::button :url="config('app.url') . '/admin'">
Review Request in Admin Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
