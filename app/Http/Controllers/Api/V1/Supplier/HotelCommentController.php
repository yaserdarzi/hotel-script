<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Exceptions\ApiException;
use App\Hotel;
use App\HotelComment;
use App\HotelGallery;
use App\HotelSupplier;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class HotelCommentController extends ApiController
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
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $hotelComment = HotelComment::
        where(['app_id' => $request->input('app_id'), 'hotel_id' => $hotel_id])
            ->select(
                'id',
                'name',
                DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/comment/thumb/', path) ) ELSE '' END as path_thumb"),
                'mime_type'
            )->get();
        return $this->respond($hotelComment);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('comment'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن نظرات اجباری می باشد.'
            );
        if (!$request->file('path'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مدیا اجباری می باشد.'
            );
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/comment', 0777, true, true);
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/comment/thumb/', 0777, true, true);
        if (!in_array($request->file('path')->getClientMimeType(), Constants::PHOTO_TYPE))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مدیا اجباری می باشد.'
            );
        $mime_type = $request->file("path")->getClientMimeType();
        $path = md5(\File::get($request->file("path"))) . '.' . $request->file("path")->getClientOriginalExtension();
        $exists = \Storage::disk('upload')->has('/hotel/' . $hotel_id . '/comment/' . $path);
        if ($exists == null) {
            \Storage::disk('upload')->put('/hotel/' . $hotel_id . '/comment/' . $path, \File::get($request->file("path")->getRealPath()));
            //generate thumbnail
            $image_resize = Image::make($request->file("path")->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file("path"));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 400, 400, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/hotel/' . $hotel_id . '/comment/thumb/' . $path);
            $image_resize->save($thumb);
        }
        HotelComment::create([
            'app_id' => $request->input('app_id'),
            'hotel_id' => $hotel_id,
            'name' => $request->input('name'),
            'comment' => $request->input('comment'),
            'path' => $path,
            'mime_type' => $mime_type,
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $id)
    {
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $hotelComment = HotelComment::where('app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id, 'id' => $id])
            ->select(
                '*',
                DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/comment/', path) ) ELSE '' END as path"),
                DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/comment/thumb/', path) ) ELSE '' END as path_thumb")
            )
            ->first();
        return $this->respond($hotelComment);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        $hotelCommentInfo = HotelComment::where('app_id', $request->input('app_id'))
            ->where(['id' => $id])->first();
        if (!$hotelCommentInfo)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('comment'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن نظرات اجباری می باشد.'
            );
        $path = $hotelCommentInfo->path;
        $mime_type = $hotelCommentInfo->mime_type;
        if ($request->file('path')) {
            \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/comment', 0777, true, true);
            \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/comment/thumb/', 0777, true, true);
            if (!in_array($request->file('path')->getClientMimeType(), Constants::PHOTO_TYPE))
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن مدیا اجباری می باشد.'
                );
            $mime_type = $request->file("path")->getClientMimeType();
            $path = md5(\File::get($request->file("path"))) . '.' . $request->file("path")->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/hotel/' . $hotel_id . '/comment/' . $path);
            if ($exists == null) {
                \Storage::disk('upload')->put('/hotel/' . $hotel_id . '/comment/' . $path, \File::get($request->file("path")->getRealPath()));
                //generate thumbnail
                $image_resize = Image::make($request->file("path")->getRealPath());
                //get width and height of image
                $data = getimagesize($request->file("path"));
                $imageWidth = $data[0];
                $imageHeight = $data[1];
                $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 400, 400, false);
                $image_resize->resize($newDimen[0], $newDimen[1]);
                $thumb = public_path('/files/hotel/' . $hotel_id . '/comment/thumb/' . $path);
                $image_resize->save($thumb);
            }
        }
        HotelComment::where('id', $id)->update([
            'name' => $request->input('name'),
            'comment' => $request->input('comment'),
            'path' => $path,
            'mime_type' => $mime_type,
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
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        if (!HotelComment::where('app_id', $request->input('app_id'))->where(['id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        HotelComment::where('id', $id)->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////


}
