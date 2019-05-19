<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class HotelSupplier extends Model
{
    protected $table = Constants::HOTEL_SUPPLIER_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'app_id', 'supplier_id', 'hotel_id', 'info'
    ];
}
