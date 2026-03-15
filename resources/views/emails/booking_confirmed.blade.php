<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 12px; overflow: hidden; }
        .header { background: #1a1c21; color: #fff; padding: 30px; text-align: center; }
        .content { padding: 40px; }
        .details-box { background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #4a90e2; }
        .footer { background: #f4f6f8; padding: 20px; text-align: center; font-size: 12px; color: #777; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #def7ec; color: #03543f; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        h1 { margin: 0; font-size: 24px; }
        h3 { color: #1a1c21; border-bottom: 2px solid #eee; padding-bottom: 8px; }
        .account-details { background: #ebf3ff; border: 1px solid #cce0ff; border-radius: 8px; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmed</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $userName }}</strong>,</p>
            <p>We are excited to inform you that your booking request for the <strong>Northern Province Sports Complex</strong> has been officially approved!</p>
            
            <div class="details-box">
                <h3>Booking Information <span class="badge">Approved</span></h3>
                <p><strong>Reference:</strong> #BK-{{ $booking->id }}</p>
                <p><strong>Facility:</strong> {{ $booking->facility->name }}</p>
                <p><strong>Sport:</strong> {{ $booking->sport->name ?? 'N/A' }}</p>
                <p><strong>Date:</strong> {{ $booking->booking_date->format('l, F j, Y') }}</p>
                <p><strong>Time:</strong> {{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</p>
                <p><strong>Total Fee:</strong> LKR {{ number_format($booking->price, 2) }}</p>
            </div>

            <div class="account-details">
                <h3>Payment Details</h3>
                <p>To finalize your reservation, please make the payment to the following account:</p>
                <p><strong>Bank:</strong> Bank of Ceylon (BOC)</p>
                <p><strong>Account Name:</strong> Sports Development Fund - NP</p>
                <p><strong>Account Number:</strong> 000123456789</p>
                <p><strong>Branch:</strong> Vavuniya Main Branch</p>
                <p><em>Note: Please include your Booking ID (#BK-{{ $booking->id }}) as the transaction reference.</em></p>
            </div>

            <p style="margin-top: 30px;">If you have any questions or need to make changes, please contact our office at <strong>+94 24 222 3456</strong>.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Northern Province Sports Complex. All rights reserved.</p>
            <p>This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
