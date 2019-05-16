<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class HotelGallery extends Model
{
    protected $table = Constants::HOTEL_GALLERY_DB;
    protected $fillable = [
        'type_app_id', 'hotel_id', 'path', 'mime_type', 'created_at'
    ];
    public $timestamps = false;
}
