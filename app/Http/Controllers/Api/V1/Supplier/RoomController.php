<?php

namespace App\Http\Controllers\Api\V1\Room;

use App\Http\Controllers\ApiController;
use App\Room;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class RoomController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($hotel_id, Request $request)
    {
        $arrayStartDate = explode('/', $request->input('start_date'));
        $arrayEndDate = explode('/', $request->input('end_date'));
        $fromDate = \Morilog\Jalali\jDateTime::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $toDate = \Morilog\Jalali\jDateTime::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);


        dd($fromDate );
//
//
//        $toDate = strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]);
//        $yesterday = strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2] . " -1 days");
//        $data = array();
//        if ($fromDate && $toDate) {
//            $products = ProductsEpisode::
//            where(Constants::PRODUCTS_EPISODE_DB . '.start_date', '>', $yesterday)
//                ->where(Constants::PRODUCTS_EPISODE_DB . '.start_date', '<=', $toDate)
//                ->join(Constants::PRODUCTS_DB, Constants::PRODUCTS_DB . ".id", "=", Constants::PRODUCTS_EPISODE_DB . ".product_id")
//                ->where([Constants::PRODUCTS_DB . '.status' => 1, Constants::PRODUCTS_EPISODE_DB . '.status' => 1])
//                ->with('prices');
//            if ($request->input('categories_title') != '') {
//                $catId = Categories::where(['title' => $request->input('categories_title')])->pluck('id');
//                $products = $products->whereIn(Constants::PRODUCTS_DB . '.categories_id', $catId);
//            }
//            $products = $products->select(
//                Constants::PRODUCTS_EPISODE_DB . '.product_id',
//                Constants::PRODUCTS_EPISODE_DB . '.product_id as id',
//                Constants::PRODUCTS_EPISODE_DB . '.start_date',
//                Constants::PRODUCTS_DB . '.title',
//                Constants::PRODUCTS_DB . '.images',
//                Constants::PRODUCTS_EPISODE_DB . '.capacity',
//                Constants::PRODUCTS_EPISODE_DB . '.id as episode_id',
//                Constants::PRODUCTS_EPISODE_DB . '.start_hours',
//                Constants::PRODUCTS_EPISODE_DB . '.end_hours',
//                Constants::PRODUCTS_DB . '.time_limitation',
//                DB::raw("'product' as type")
//            )
//                ->orderBy('start_date')
//                ->get();
//            foreach ($products as $value) {
//                $capacityShoppingBag = ShoppingBag::where(['product_episode_id' => $value->episode_id])->sum('count');
//                $countFactorEpisode = FactorDetails::where('product_episode_id', $value->episode_id)->sum('count');
//                $value->capacity = $value->capacity - ($countFactorEpisode + $capacityShoppingBag);
//                if ($value->capacity > 9)
//                    $value->capacity = 9;
//            }
//            $tours = ToursEpisode::
//            where(Constants::TOURS_EPISODE_DB . '.start_date', '>', $yesterday)
//                ->where(Constants::TOURS_EPISODE_DB . '.start_date', '<=', $toDate)
//                ->where([Constants::TOURS_DB . '.status' => 1, Constants::TOURS_EPISODE_DB . '.status' => 1])
//                ->with('prices')
//                ->join(Constants::TOURS_DB, Constants::TOURS_DB . ".id", "=", Constants::TOURS_EPISODE_DB . ".tour_id")
//                ->select(
//                    Constants::TOURS_EPISODE_DB . '.tour_id',
//                    Constants::TOURS_EPISODE_DB . '.tour_id as id',
//                    Constants::TOURS_EPISODE_DB . '.start_date',
//                    Constants::TOURS_DB . '.title',
//                    Constants::TOURS_DB . '.images',
//                    Constants::TOURS_EPISODE_DB . '.capacity',
//                    Constants::TOURS_EPISODE_DB . '.id as episode_id',
//                    Constants::TOURS_EPISODE_DB . '.start_hours',
//                    Constants::TOURS_EPISODE_DB . '.end_hours',
//                    Constants::TOURS_DB . '.time_limitation',
//                    DB::raw("'tour' as type")
//                )
//                ->orderBy('start_date')
//                ->get();
//            foreach ($tours as $value) {
//                $capacityShoppingBag = ShoppingBag::where(['tour_episode_id' => $value->episode_id])->sum('count');
//                $countFactorEpisode = FactorDetails::where('tour_episode_id', $value->episode_id)->sum('count');
//                $value->capacity = $value->capacity - ($countFactorEpisode + $capacityShoppingBag);
//            }
//            $data['total'] = array_merge($products->toArray(), $tours->toArray());
//        }
//        if (isset($data['total'])) {
//            $i = 0;
//            foreach ($data['total'] as $key => $value) {
//                if ($value['capacity'] > 0) {
//                    if ($value['type'] == "product")
//                        $value["images"] = url('files/products/' . $value["images"]);
//                    elseif ($value['type'] == "tour")
//                        $value["images"] = url('files/tours/' . $value["images"]);
//                    $data['total'][$i++] = $value;
//                }
//            }
//        }
//        return $this->respond($data);

        $room = Room::whereIn('type_app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id])
            ->select('*', DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/room/', image) ) ELSE '' END as image"))
            ->get();
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $room_id)
    {
        $room = Room::whereIn('type_app_id', $request->input('app_id'))
            ->with("gallery")
            ->where(['hotel_id' => $hotel_id, 'id' => $room_id])
            ->select('*', DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/room/', image) ) ELSE '' END as image"))
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
