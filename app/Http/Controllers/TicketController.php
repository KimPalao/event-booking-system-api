<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        if ($event->created_by !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'type' => $request->input('type'),
            'price' => $request->input('price'),
            'quantity' => $request->input('quantity'),
        ]);
        return response()->json($ticket, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->event->created_by !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $ticket->update($request->all());
        return response()->json($ticket, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Ticket $ticket)
    {
        if ($ticket->event->created_by !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $ticket->delete();
        return response()->json(null, 204);
    }
}
