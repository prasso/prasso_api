<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\UserService;

class ProxyController extends BaseController
{
    protected $userService;

    public function __construct(Request $request,UserService $userServ)
    {
        parent::__construct( $request);
        $this->middleware('instructorusergroup');

        $this->userService = $userServ;
    }

    public function getLatLonFromAddress(Request $request)
    {
        if (!Controller::userOkToViewPageByHost($this->userService))
        {
            return redirect('/login');
        }
        info("getLatLonFromAddress");
        $q = $request->input('q');
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($q) . "&format=json&addressdetails=1&limit=1";
        info($url);
        $response = Http::get($url);
        info($response->body());

        $result = json_decode($response->body())[0];
        $lat = $result->lat;
        $lon = $result->lon;

        $data = [compact('lat', 'lon')];
        return response()->json($data);
    }
}