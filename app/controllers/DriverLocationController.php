<?php
class DriverLocationController extends \BaseController
{
    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */


    public function __construct()
    {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }
    }

    public function ViewMap()
    {
        $request_id = Request::segment(3);

        if($request_id>0){
            $request = RideRequest::where('id', '=', $request_id)->first();

            $walker = Walker::find($request->confirmed_walker);

            if($walker){
                $request = RideRequest::where('id', '=', $request->id)
                    ->where('confirmed_walker', '=', $walker->id)
                    ->where('is_confirmed', '=', 1)
                    ->orderBy('created_at', 'desc')->first();

                if($request->id > 0) {
                    $pickup_address = $request->src_address;
                    $dropoff_address = $request->dest_address;
                    $pickup_latitude = $request->latitude;
                    $pickup_longitude = $request->longitude;
                    $dropoff_latitude = $request->D_latitude;
                    $dropoff_longitude = $request->D_longitude;
                    $walker_name = $walker->contact_name;
                    $walker_phone = $walker->phone;

                    $full_walk = WalkLocation::where('request_id', '=', $request->id)->orderBy('updated_at' , 'desc' )->first();

                    $title = ucwords('');
                    return View::make('driverlocation.driverLocationMap')
                        ->with('title', $title)
                        ->with('page', 'driverlocation')
                        ->with('request_id', $request->id)
                        ->with('walker_id', $walker->id)
                        ->with('walker_name', $walker_name)
                        ->with('pickup_address', $pickup_address)
                        ->with('dropoff_address', $dropoff_address)
                        ->with('dropoff_latitude', $dropoff_latitude)
                        ->with('dropoff_longitude', $dropoff_longitude)
                        ->with('pickup_latitude', $pickup_latitude)
                        ->with('pickup_longitude', $pickup_longitude)
                        ->with('walker_phone', $walker_phone)
                        ->with('full_walk', $full_walk);
                } else{
                    echo "<br />No any request found";
                }
            } else{
                $request = RideRequest::where('id','=',$request_id)->first();
                Log::info('request ' . print_r($request_id, true));
                if($request) {
                    $pickup_address = $request->src_address;
                    $dropoff_address = $request->dest_address;
                    $pickup_latitude = $request->latitude;
                    $pickup_longitude = $request->longitude;
                    $dropoff_latitude = $request->D_latitude;
                    $dropoff_longitude = $request->D_longitude;
                    $walker_id = 0;
                    $walker_name = 'Walker not yet assigned';
                    $walker_phone = 'NA';
                    $title = ucwords('');
                    return View::make('driverlocation.driverLocationMap')
                        ->with('title', $title)
                        ->with('page', 'driverlocation')
                        ->with('request_id', $request->id)
                        ->with('walker_id', $walker_id)
                        ->with('pickup_address', $pickup_address)
                        ->with('walker_name', $walker_name)
                        ->with('dropoff_address', $dropoff_address)
                        ->with('dropoff_latitude', $dropoff_latitude)
                        ->with('dropoff_longitude', $dropoff_longitude)
                        ->with('pickup_latitude', $pickup_latitude)
                        ->with('pickup_longitude', $pickup_longitude)
                        ->with('walker_phone', $walker_phone);
                }
            }
        }else{
            echo "<br />Please provide request id";
        }
    }

    public function GetWalkerLocation(){
        $request_id = $_POST['request_id'];
        $walker_id = $_POST['walker_id'];

        $walker = Walker::find($walker_id);
        $walker_name = $walker->contact_name;
        $walker_phone = $walker->phone;

        $request = RideRequest::where('id', '=', $request_id)->first();

        if($request->is_started==0){
            $full_walk = Walker::find($request->confirmed_walker);
        }else{
            $full_walk = WalkLocation::where('request_id', '=', $request_id)
                ->orderBy('updated_at' , 'desc' )->first();
        }

        return Response::json(array('latitude' => $full_walk->latitude, 'longitude' => $full_walk->longitude,
            'walker_name'=>$walker_name, 'walker_phone'=>$walker_phone, 'request_id'=>$request_id));
    }

    /*public function walkers_location_xml() {

        $requestid = Request::segment(3);
        $response = "";
        $response .= '<markers>';

        $walk_location = DB::table('walk_location')
            ->select('walk_location.*')
            ->where('request_id','=',$requestid)
            ->get();
        //$walker_ids = array();
        foreach ($walk_location as $walk) {
                $response .= '<marker ';
                $response .= 'lat="' . $walk->latitude . '" ';
                $response .= 'lng="' . $walk->longitude . '" ';
                $response .= '/>';
                //array_push($walker_ids, $walker->id);
        }
        $response .= '</markers>';
        $content = View::make('driverlocation.driverslocation_xml')->with('response', $response);
        return Response::make($content, '200')->header('Content-Type', 'text/xml');
    }*/
}