<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;

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
        }

        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        if ($payment->booking->user_id !== request()->user()->id && request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json($payment);
    }
}
