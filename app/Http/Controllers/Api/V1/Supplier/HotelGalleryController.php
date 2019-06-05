<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Exceptions\ApiException;
use App\Hotel;
use App\HotelGallery;
use App\HotelSupplier;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class HotelGalleryController extends ApiController
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
        $hotelGallery = HotelGallery::
        where(['app_id' => $request->input('app_id'), 'hotel_id' => $hotel_id])
            ->select(
                'id',
                DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/', path) ) ELSE '' END as path"),
                DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/thumb/', path) ) ELSE '' END as path_thumb"),
                'mime_type'
            )->get();
        return $this->respond($hotelGallery);
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
        if (!$request->file('path'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن مدیا اجباری می باشد.'
            );
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/', 0777, true, true);
        \Storage::disk('upload')->makeDirectory('/hotel/' . $hotel_id . '/thumb/', 0777, true, true);
        foreach ($request->file('path') as $value) {
            $mime_type = $value->getClientMimeType();
            $path = md5(\File::get($value)) . '.' . $value->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/hotel/' . $hotel_id . '/' . $path);
            if ($exists == null) {
                \Storage::disk('upload')->put('/hotel/' . $hotel_id . '/' . $path, \File::get($value->getRealPath()));
                if (in_array($mime_type, Constants::PHOTO_TYPE)) {
                    //generate thumbnail
                    $image_resize = Image::make($value->getRealPath());
                    //get width and height of image
                    $data = getimagesize($value);
                    $imageWidth = $data[0];
                    $imageHeight = $data[1];
                    $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
                    $image_resize->resize($newDimen[0], $newDimen[1]);
                    $thumb = public_path('/files/hotel/' . $hotel_id . '/thumb/' . $path);
                    $image_resize->save($thumb);
                }
            }
            HotelGallery::create([
                'app_id' => $request->input('app_id'),
                'hotel_id' => $hotel_id,
                'path' => $path,
                'mime_type' => $mime_type,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
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
        //
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
    public function update(Request $request, $id)
    {
        //
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
        if (!HotelGallery::where('app_id', $request->input('app_id'))->where(['id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        HotelGallery::where('id', $id)->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////


}
