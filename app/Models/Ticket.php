<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $fillable = [
        'event_id',
        'quantity',
        'type',
        'price',
    ];

    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, HasUuids;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
