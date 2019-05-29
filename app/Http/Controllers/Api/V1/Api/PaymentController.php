<?php

namespace App\Http\Controllers\Api\V1\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use Illuminate\Http\Request;
use App\Http\Requests;

class PaymentController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
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
    public function store(Request $request)
    {
        if (!$request->input('check_in'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your check_in"
            );
        if (!$request->input('check_out'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your check_out"
            );
        if (!$request->input('room_id'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your room_id"
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your name"
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your phone"
            );
        $curl = curl_init();
        $data = array(
            'start_date' => $request->input('check_in'),
            'end_date' => $request->input('check_out'),
            'room_id' => $request->input('room_id'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/api/payment/hotel",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('AppToken'),
                "content-type: multipart/form-data;",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($err)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $err
            );
        if ($info['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($response)->error
            );
        return $this->respond(json_decode($response));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $hotel_id)
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
    public function destroy($id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
