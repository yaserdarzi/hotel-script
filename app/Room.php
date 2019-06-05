<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Room extends Model
{
    use SoftDeletes;
    protected $table = Constants::ROOM_DB;
    protected $fillable = [
        'app_id', 'hotel_id', 'title', 'image', 'desc', 'bed', 'capacity',
        'is_breakfast', 'is_lunch', 'is_dinner', 'is_capacity',
        'price', 'sort'
    ];
    protected $dates = ['deleted_at'];

    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'id', 'hotel_id')
            ->select(
                'id',
                'name',
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/thumb/', logo) ) ELSE '' END as logo_thumb")
            )
            ->where('deleted_at', null);
    }

    public function gallery()
    {
        return $this->hasMany(RoomGallery::class, 'room_id', 'id')
            ->select('*', DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/room/',room_id,'/', path) ) ELSE '' END as path"));
    }

    public function tools()
    {
        return $this->hasMany(RoomTools::class, 'room_id', 'id')
            ->select('*', DB::raw("CASE WHEN icon != '' THEN (concat ( '" . url('') . "/files/room/tools/', icon) ) ELSE '' END as icon"));
    }
}
