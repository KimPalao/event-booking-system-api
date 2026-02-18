<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Ticket;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['data' => Booking::whereUserId(request()->user()->id)->with('ticket.event')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'ticket_id' => $ticket->id,
            'quantity' => $request->input('quantity'),
            'status' => 'pending',
        ]);
        return response()->json($booking, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function cancel(Request $request, Booking $booking)
     {
        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $booking->update(['status' => 'cancelled']);
        return response()->json($booking);
    }
}
