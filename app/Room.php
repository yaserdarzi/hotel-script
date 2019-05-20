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
        'sort', 'is_breakfast', 'is_lunch', 'is_dinner'
    ];
    protected $dates = ['deleted_at'];

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
