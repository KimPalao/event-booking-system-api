<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmed;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function store(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $amount = $booking->ticket->price * $booking->quantity;

        $paymentStatus = $this->paymentService->pay($booking->id, $amount);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'status' => $paymentStatus ? 'success' : 'failed',
        ]);

        if ($paymentStatus) {
            $booking->update(['status' => 'confirmed']);
            $booking->user->notify(new BookingConfirmed($booking));
            return response()->json(['message' => 'Payment successful', 'data' => $payment], 201);
        }

        return response()->json(['message' => 'Payment unsuccessful', 'data' => $payment], 400);

    }

    public function show(Payment $payment)
    {
        if ($payment->booking->user_id !== request()->user()->id && request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($payment);
    }
}
