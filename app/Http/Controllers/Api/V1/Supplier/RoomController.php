<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Exceptions\ApiException;
use App\Hotel;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Room;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class RoomController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($hotel_id, Request $request)
    {
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $room = Room::where('app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id])
            ->select(
                'id',
                'title',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )->get();
        return $this->respond($room);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($hotel_id, Request $request)
    {
        if ($request->input('role')!=Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        if (!$request->input('title'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عنوان اجباری می باشد.'
            );
        if (!$request->file('image'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تصویر اجباری می باشد.'
            );
        if (!in_array($request->file('image')->getClientMimeType(), Constants::PHOTO_TYPE))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تصویر اجباری می باشد.'
            );
        if (!$request->input('desc'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن توضیحات اجباری می باشد.'
            );
        if (!$request->input('bed'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نوع اتاق اجباری می باشد.'
            );
        if (!$request->input('capacity'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت اجباری می باشد.'
            );
        if (!$request->input('sort'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ترتیب نمایش اجباری می باشد.'
            );
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/room/', 0777, true, true);
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/room/thumb/', 0777, true, true);
        $image = md5(\File::get($request->file('image'))) . '.' . $request->file('image')->getClientOriginalExtension();
        $exists = \Storage::disk('upload')->has('/hotel/' . $hotel_id . '/room/' . $image);
        if ($exists == null) {
            \Storage::disk('upload')->put('/hotel/' . $hotel_id . '/room/' . $image, \File::get($request->file('image')->getRealPath()));
        }
        //generate thumbnail
        $image_resize = Image::make($request->file('image')->getRealPath());
        //get width and height of image
        $data = getimagesize($request->file('image'));
        $imageWidth = $data[0];
        $imageHeight = $data[1];
        $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
        $image_resize->resize($newDimen[0], $newDimen[1]);
        $thumb = public_path('/files/hotel/' . $hotel_id . '/room/thumb/' . $image);
        $image_resize->save($thumb);
        $is_breakfast = false;
        if ($request->input('is_breakfast'))
            $is_breakfast = true;
        $is_lunch = false;
        if ($request->input('is_lunch'))
            $is_lunch = true;
        $is_dinner = false;
        if ($request->input('is_dinner'))
            $is_dinner = true;
        Room::create([
            'app_id' => $request->input('app_id'),
            'hotel_id' => $hotel_id,
            'title' => $request->input('title'),
            'image' => $image,
            'desc' => $request->input('desc'),
            'bed' => $request->input('bed'),
            'capacity' => $request->input('capacity'),
            'is_breakfast' => $is_breakfast,
            'is_lunch' => $is_lunch,
            'is_dinner' => $is_dinner,
            'sort' => $request->input('sort'),
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $room_id)
    {
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $room = Room::where('app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id, 'id' => $room_id])
            ->select(
                '*',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/', image) ) ELSE '' END as image"),
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )
            ->first();
        return $this->respond($room);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($hotel_id, Request $request, $id)
    {
        if ($request->input('role')!=Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $roomInfo = Room::where('app_id', $request->input('app_id'))
            ->where(['id' => $id])->first();
        if (!$roomInfo)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!$request->input('title'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن عنوان اجباری می باشد.'
            );
        if (!$request->input('desc'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن توضیحات اجباری می باشد.'
            );
        if (!$request->input('bed'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نوع اتاق اجباری می باشد.'
            );
        if (!$request->input('capacity'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت اجباری می باشد.'
            );
        if (!$request->input('sort'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ترتیب نمایش اجباری می باشد.'
            );
        $image = $roomInfo->image;
        if ($request->file('image')) {
            \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/room/', 0777, true, true);
            \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/room/thumb/', 0777, true, true);
            $image = md5(\File::get($request->file('image'))) . '.' . $request->file('image')->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/hotel/' . $hotel_id . '/room/' . $image);
            if ($exists == null) {
                \Storage::disk('upload')->put('/hotel/' . $hotel_id . '/room/' . $image, \File::get($request->file('image')->getRealPath()));
            }
            //generate thumbnail
            $image_resize = Image::make($request->file('image')->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file('image'));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/hotel/' . $hotel_id . '/room/thumb/' . $image);
            $image_resize->save($thumb);
        }
        $is_breakfast = false;
        if ($request->input('is_breakfast'))
            $is_breakfast = true;
        $is_lunch = false;
        if ($request->input('is_lunch'))
            $is_lunch = true;
        $is_dinner = false;
        if ($request->input('is_dinner'))
            $is_dinner = true;
        Room::where('id', $id)->update([
            'title' => $request->input('title'),
            'image' => $image,
            'desc' => $request->input('desc'),
            'bed' => $request->input('bed'),
            'capacity' => $request->input('capacity'),
            'is_breakfast' => $is_breakfast,
            'is_lunch' => $is_lunch,
            'is_dinner' => $is_dinner,
            'sort' => $request->input('sort'),
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($hotel_id, $id, Request $request)
    {
        if ($request->input('role')!=Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!Room::where('app_id', $request->input('app_id'))->where(['id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        Room::where('id', $id)->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////


}
