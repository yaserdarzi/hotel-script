<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    protected $table = Constants::ROOM_BOOKING_DB;
    protected $fillable = [
        'type_app_id', 'room_id', 'user_id', 'invoice_id', 'date',
        'created_at'
    ];
    public $timestamps = false;
}
