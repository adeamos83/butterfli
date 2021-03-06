<?php

class CustomerController extends BaseController {

    public function isAdmin($token) {
        return false;
    }

    public function getOwnerData($owner_id, $token, $is_admin) {

        if ($owner_data = Owner::where('token', '=', $token)->where('id', '=', $owner_id)->first()) {
            return $owner_data;
        } elseif ($is_admin) {
            $owner_data = Owner::where('id', '=', $owner_id)->first();
            if (!$owner_data) {
                return false;
            }
            return $owner_data;
        } else {
            return false;
        }
    }

    private function get_timezone_offset($remote_tz, $origin_tz = null) {
        if ($origin_tz === null) {
            if (!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }

    public function create() {
        if (Request::isMethod('post')) {
            $name = ucwords(trim(Input::get('name')));
            $age = Input::get('age');
            $breed = Input::get('type');
            $likes = Input::get('notes');
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $picture = Input::file('picture');

            $validator = Validator::make(
                            array(
                        'name' => $name,
                        'age' => $age,
                        'breed' => $breed,
                        'token' => $token,
                        'owner_id' => $owner_id,
                        'picture' => $picture,
                            ), array(
                        'name' => 'required',
                        'age' => 'required|integer',
                        'breed' => 'required',
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                        /* 'picture' => 'required|mimes:jpeg,bmp,png', */
                        'picture' => 'required',
                            ), array(
                        'name.required' => 2,
                        'age.required' => 3,
                        'breed.required' => 4,
                        'token.required' => '',
                        'owner_id.required' => 6,
                        /* 'picture' => 'required|mimes:jpeg,bmp,png', */
                        'picture.required' => 7,
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        // Do necessary operations
                        // check if there's already a dog
                        $dog = Dog::where('owner_id', $owner_id)->first();
                        if ($dog === null) {
                            $dog = new Dog;
                        }

                        $dog->name = $name;
                        $dog->age = $age;
                        $dog->breed = $breed;
                        $dog->likes = $likes;
                        $dog->owner_id = $owner_data->id;


                        // Upload File
                        $file_name = time();
                        $file_name .= rand();
                        $ext = Input::file('picture')->getClientOriginalExtension();
                        Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                        $local_url = $file_name . "." . $ext;

                        // Upload to S3
                        if (Config::get('app.s3_bucket') != "") {
                            $s3 = App::make('aws')->get('s3');
                            $pic = $s3->putObject(array(
                                'Bucket' => Config::get('app.s3_bucket'),
                                'Key' => $file_name,
                                'SourceFile' => public_path() . "/uploads/" . $local_url,
                            ));

                            $s3->putObjectAcl(array(
                                'Bucket' => Config::get('app.s3_bucket'),
                                'Key' => $file_name,
                                'ACL' => 'public-read'
                            ));

                            $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                        } else {
                            $s3_url = asset_url() . '/uploads/' . $local_url;
                        }
                        if (isset($dog->image_url)) {
                            if ($dog->image_url != "") {
                                $icon = $dog->image_url;
                                unlink_image($icon);
                            }
                        }
                        $dog->image_url = $s3_url;

                        $dog->save();

                        $owner = Owner::find($owner_data->id);
                        $owner->dog_id = $dog->id;
                        $owner->save();

                        $response_array = array('success' => true);
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_code' => 405, 'error_messages' => array(9));
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        } else {
            //handles get request
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $validator = Validator::make(
                            array(
                        'token' => $token,
                        'owner_id' => $owner_id,
                            ), array(
                        'token' => 'required',
                        'owner_id' => 'required|integer'
                            ), array(
                        'token.required' => '',
                        'owner_id.required' => 6
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {

                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        $dog = Dog::find($owner_data->dog_id);
                        if ($dog) {
                            $response_array = array(
                                'success' => true,
                                'thing_id' => $dog->id,
                                'age' => $dog->age,
                                'type' => $dog->breed,
                                'notes' => $dog->likes,
                                'image_url' => $dog->image_url,
                            );
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 12, 'error_messages' => array(12), 'error_code' => 445);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {

                    if ($is_admin) {
                        $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Setting Owner Location

    public function update_thing() {
        if (Request::isMethod('post')) {
            $name = ucwords(trim(Input::get('name')));
            $age = Input::get('age');
            $breed = Input::get('type');
            $likes = Input::get('notes');
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $picture = Input::file('picture');

            $validator = Validator::make(
                            array(
                        'token' => $token,
                        'owner_id' => $owner_id,
                        'age' => $age,
                        'picture' => $picture,
                            ), array(
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                        'age' => 'integer',
                        'picture' => '',
                            /* 'picture' => 'mimes:jpeg,bmp,png', */
                            ), array(
                        'token.required' => '',
                        'owner_id.required' => 6,
                        'age.integer' => 3,
                        'picture' => 7,
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {

                        $dog_data = Dog::where('owner_id', $owner_id)->first();
                        if ($dog_data) {
                            $dog = Dog::find($dog_data->id);
                            if ($name) {
                                $dog->name = $name;
                            }
                            if ($age) {
                                $dog->age = $age;
                            }
                            if ($breed) {
                                $dog->breed = $breed;
                            }
                            if ($likes) {
                                $dog->likes = $likes;
                            }

                            if (Input::hasFile('picture')) {
                                // upload image
                                $file_name = time();
                                $file_name .= rand();
                                $file_name = sha1($file_name);

                                $ext = Input::file('picture')->getClientOriginalExtension();
                                Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                                $local_url = $file_name . "." . $ext;

                                // Upload to S3
                                if (Config::get('app.s3_bucket') != "") {
                                    $s3 = App::make('aws')->get('s3');
                                    $pic = $s3->putObject(array(
                                        'Bucket' => Config::get('app.s3_bucket'),
                                        'Key' => $file_name,
                                        'SourceFile' => public_path() . "/uploads/" . $local_url,
                                    ));

                                    $s3->putObjectAcl(array(
                                        'Bucket' => Config::get('app.s3_bucket'),
                                        'Key' => $file_name,
                                        'ACL' => 'public-read'
                                    ));

                                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                                } else {
                                    $s3_url = asset_url() . '/uploads/' . $local_url;
                                }

                                if (isset($dog->image_url)) {
                                    if ($dog->image_url != "") {
                                        $icon = $dog->image_url;
                                        unlink_image($icon);
                                    }
                                }

                                $dog->image_url = $s3_url;
                            }

                            $dog->save();
                            $response_array = array('success' => true);
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 96, 'error_messages' => array(96), 'error_code' => 405);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Rate Walker

    public function set_walker_rating() {
        if (Request::isMethod('post')) {
            $comment = "";
            if (Input::has('comment')) {
                $comment = Input::get('comment');
            }
            $request_id = Input::get('request_id');
            $rating = 0;
            if (Input::has('rating')) {
                $rating = Input::get('rating');
            }
            $token = Input::get('token');
            $owner_id = Input::get('id');

            $validator = Validator::make(
                            array(
                        'request_id' => $request_id,
                        /* 'rating' => $rating, */
                        'token' => $token,
                        'owner_id' => $owner_id,
                            ), array(
                        'request_id' => 'required|integer',
                        /* 'rating' => 'required|integer', */
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                            ), array(
                        'request_id.required' => 19,
                        /* 'rating' => 'required|integer', */
                        'token.required' => '',
                        'owner_id.required' => 6,
                            )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        // Do necessary operations
                        if ($request =RideRequest::find($request_id)) {
                            if ($request->owner_id == $owner_data->id) {
                                if ($request->is_completed == 1) {
                                    if ($request->is_walker_rated == 0) {
                                        $walker_review = new WalkerReview;
                                        $walker_review->request_id = $request_id;
                                        $walker_review->walker_id = $request->confirmed_walker;
                                        $walker_review->rating = $rating;
                                        $walker_review->owner_id = $owner_data->id;
                                        $walker_review->comment = $comment;
                                        $walker_review->save();

                                        $request->is_walker_rated = 1;
                                        $request->save();

                                        if ($rating) {
                                            if ($walker = Walker::find($request->confirmed_walker)) {
                                                $old_rate = $walker->rate;
                                                $old_rate_count = $walker->rate_count;
                                                $new_rate_counter = ($walker->rate_count + 1);
                                                $new_rate = (($walker->rate * $walker->rate_count) + $rating) / $new_rate_counter;
                                                $walker->rate_count = $new_rate_counter;
                                                $walker->rate = $new_rate;
                                                $walker->save();
                                            }
                                        }

                                        $response_array = array('success' => true);
                                        $response_code = 200;
                                    } else {
                                        $response_array = array('success' => false, 'error' => 20, 'error_messages' => array(20), 'error_code' => 409);
                                        $response_code = 200;
                                    }
                                } else {
                                    $response_array = array('success' => false, 'error' => 21, 'error_messages' => array(21), 'error_code' => 409);
                                    $response_code = 200;
                                }
                            } else {
                                $response_array = array('success' => false, 'error' => 22, 'error_messages' => array(22), 'error_code' => 407);
                                $response_code = 200;
                            }
                        } else {
                            $response_array = array('success' => false, 'error' => 23, 'error_messages' => array(23), 'error_code' => 408);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Setting Owner Location

    public function set_location() {
        if (Request::isMethod('post')) {
            $latitude = Input::get('latitude');
            $longitude = Input::get('longitude');
            $token = Input::get('token');
            $owner_id = Input::get('id');

            $validator = Validator::make(
                            array(
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'token' => $token,
                        'owner_id' => $owner_id,
                            ), array(
                        'latitude' => 'required',
                        'longitude' => 'required',
                        'token' => 'required',
                        'owner_id' => 'required|integer'
                            ), array(
                        'latitude.required' => 49,
                        'longitude.required' => 49,
                        'token.required' => '',
                        'owner_id.required' => 6,
                            )
            );
            /* $var = Keywords::where('id', 2)->first(); */

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $response_array = array('success' => true);
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        /* $response_array = array('success' => false, 'error' => '' . $var->keyword . 'ID not Found', 'error_code' => 410); */
                        $response_array = array('success' => false, 'error' => 50, 'error_messages' => array(50), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

// Get Walk Location


    public function get_walk_location() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $timestamp = Input::get('ts');


        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );
        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request =RideRequest::find($request_id)) {
                        if ($request->owner_id == $owner_id) {
                            if (isset($timestamp)) {
                                $walk_locations = WalkLocation::where('request_id', '=', $request_id)->where('created_at', '>', $timestamp)->orderBy('created_at')->get();
                            } else {
                                $walk_locations = WalkLocation::where('request_id', '=', $request_id)->orderBy('created_at')->get();
                            }
                            $locations = array();

                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;


                            foreach ($walk_locations as $walk_location) {
                                $location = array();
                                $location['latitude'] = $walk_location->latitude;
                                $location['longitude'] = $walk_location->longitude;
                                $location['distance'] = convert($walk_location->distance, $unit);
                                $location['bearing'] = $walk_location->bearing;
                                $location['timestamp'] = $walk_location->created_at;
                                array_push($locations, $location);
                            }

                            $response_array = array('success' => true, 'locationdata' => $locations);
                            $response_code = 200;
                        } else {
                            /* $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with' . $var->keyword . ' ID', 'error_messages' => array('Request ID doesnot matches with' . $var->keyword . ' ID'), 'error_code' => 407); */
                            $response_array = array('success' => false, 'error' => 51, 'error_messages' => array(51), 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_providers_all() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                        )
        );
        /* $var = Keywords::where('id', 2)->first(); */
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $settings = Settings::where('key', 'default_search_radius')->first();
                    $distance = $settings->value;
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    if ($unit == 0) {
                        $multiply = 1.609344;
                    } elseif ($unit == 1) {
                        $multiply = 1;
                    }
                    $query = "SELECT "
                            . "walker.id, "
                            . "walker.latitude, "
                            . "walker.longitude, "
                            . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ,8) as distance "
                            . "from walker "
                            . "where is_available = 1 and "
                            . "is_active = 1 and "
                            . "is_approved = 1 and "
                            . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ) ,8) <= $distance "
                            . "order by distance "
                            . "LIMIT 5";
                    $walkers = DB::select(DB::raw($query));
                    $p = 0;
                    foreach ($walkers as $key) {
                        $provider[$p]['id'] = $key->id;
                        $provider[$p]['distance'] = $key->distance;
                        $provider[$p]['latitude'] = $key->latitude;
                        $provider[$p]['longitude'] = $key->longitude;
                        $provider[$p]['bearing'] = $key->bearing;
                        $walker_services = ProviderServices::where('provider_id', $key->id)->first();
                        if ($walker_services != NULL) {
                            $walker_type = ProviderType::where('id', $walker_services->type)->first();

                            if ($walker_type != NULL) {
                                $provider[$p]['type'] = $walker_type->name;
                                $provider[$p]['base_price'] = $walker_services->base_price;
                                $provider[$p]['distance_cost'] = $walker_services->price_per_unit_distance;
                                $provider[$p]['time_cost'] = $walker_services->price_per_unit_time;
                            } else {
                                $provider[$p]['type'] = '';
                                $provider[$p]['base_price'] = '';
                                $provider[$p]['distance_cost'] = '';
                                $provider[$p]['time_cost'] = '';
                            }
                        }
                        $p++;
                    }

                    if ($walkers != NULL) {
                        $response_array = array(
                            'success' => true,
                            'walkers' => $provider,
                        );
                        $response_code = 200;
                    } else {
                        $response_array = array(
                            'success' => false,
                            'error' => 54,
                            'error_messages' => array(54),
                        );
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410); */
                    $response_array = array('success' => false, 'error_messages' => array(53), 'error' => 53, 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error_messages' => array(11), 'error' => 11, 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_nearby_providers() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $type = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                        )
        );
        /* $var = Keywords::where('id', 2)->first(); */
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    // If type is not an array
                    if (!is_array($type)) {
                        // and if type wasn't passed at all
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();
                            if ($provider_type === null) {
                                $type = array(1);
                            } else {
                                $type = array($provider_type->id);
                            }
                        } else {
                            $type = explode(',', $type);
                        }
                    }

                    foreach ($type as $key) {
                        $typ[] = $key;
                    }
                    $ty = implode(",", $typ);
                    $typequery = "SELECT distinct provider_id from walker_services where type IN($ty)";
                    $typewalkers = DB::select(DB::raw($typequery));
                    //Log::info('typewalkers = ' . print_r($typewalkers, true));
                    if ($typewalkers == NULL) {
                        /* $driver = Keywords::where('id', 1)->first();
                          $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.','error_messages' => array('No ' . $driver->keyword . ' found matching the service type.'), 'error_code' => 405); */
                        $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 405);
                        $response_code = 200;
                        return Response::json($response_array, $response_code);
                    }
                    foreach ($typewalkers as $key) {
                        $types[] = $key->provider_id;
                    }
                    $typestring = implode(",", $types);
                    //Log::info('typestring = ' . print_r($typestring, true));

                    $settings = Settings::where('key', 'default_search_radius')->first();
                    $distance = $settings->value;
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    if ($unit == 0) {
                        $multiply = 1.609344;
                    } elseif ($unit == 1) {
                        $multiply = 1;
                    }
                    $query = "SELECT "
                            . "walker.*, "
                            . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ,8) as distance "
                            . "from walker "
                            . "where is_available = 1 and "
                            . "is_active = 1 and "
                            . "is_approved = 1 and "
                            . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                            . "walker.deleted_at IS NULL and "
                            . "walker.id IN($typestring) "
                            . "order by distance";
                    $walkers = DB::select(DB::raw($query));

                    //Log::info('walkers = ' . print_r($walkers, true));
                    $p = 0;
                    foreach ($walkers as $key) {
                        $provider[$p]['id'] = $key->id;
                        $provider[$p]['distance'] = $key->distance;
                        $provider[$p]['latitude'] = $key->latitude;
                        $provider[$p]['longitude'] = $key->longitude;
                        $walker_services = ProviderServices::where('provider_id', $key->id)->first();
                        if ($walker_services != NULL) {
                            $walker_type = ProviderType::where('id', $walker_services->type)->first();

                            if ($walker_type != NULL) {
                                $provider[$p]['type'] = $walker_type->name;
                                $provider[$p]['base_price'] = currency_converted($walker_services->base_price);
                                $provider[$p]['distance_cost'] = currency_converted($walker_services->price_per_unit_distance);
                                $provider[$p]['time_cost'] = currency_converted($walker_services->price_per_unit_time);
                            } else {
                                $provider[$p]['type'] = '';
                                $provider[$p]['base_price'] = '';
                                $provider[$p]['distance_cost'] = '';
                                $provider[$p]['time_cost'] = '';
                            }
                        }
                        $p++;
                    }
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    if ($unit == 0) {
                        $unit_set = 'kms';
                    } elseif ($unit == 1) {
                        $unit_set = 'miles';
                    }

                    // Log::info('providers = '.print_r($provider, true));

                    if ($walkers != NULL) {
                        $response_array = array(
                            'success' => true,
                            'unit' => $unit_set,
                            'walkers' => $provider,
                        );
                        $response_code = 200;
                    } else {
                        $response_array = array(
                            'success' => false,
                            'unit' => $unit_set,
                            'error' => 56,
                            'error_messages' => array(56),
                        );
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Get Available Providers if provider_selection == 1 in settings table

    public function get_providers() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $type = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                        )
        );

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
                    if ($is_multiple_service->value == 0) {

                        $archk = is_array($type);
                        //Log::info('type = ' . print_r($archk, true));
                        if ($archk == 1) {
                            $type = $type;
                            //Log::info('type = ' . print_r($type, true));
                        } else {
                            $type = explode(',', $type);
                            //Log::info('type = ' . print_r($type, true));
                        }

                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }

                        foreach ($type as $key) {
                            $typ[] = $key;
                        }
                        $ty = implode(",", $typ);

                        $typequery = "SELECT distinct provider_id from walker_services where type IN($ty)";
                        $typewalkers = DB::select(DB::raw($typequery));
                        //Log::info('typewalkers = ' . print_r($typewalkers, true));

                        if ($typewalkers == NULL) {
                            /* $driver = Keywords::where('id', 1)->first();
                              $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.','error_messages' => array('No ' . $driver->keyword . ' found matching the service type.'), 'error_code' => 405); */
                            $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 405);
                            $response_code = 200;
                            return Response::json($response_array, $response_code);
                        }

                        foreach ($typewalkers as $key) {
                            $types[] = $key->provider_id;
                        }
                        $typestring = implode(",", $types);
                        //Log::info('typestring = ' . print_r($typestring, true));

                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }
                        $query = "SELECT "
                                . "walker.*, "
                                . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                . "cos( radians(latitude) ) * "
                                . "cos( radians(longitude) - radians('$longitude') ) + "
                                . "sin( radians('$latitude') ) * "
                                . "sin( radians(latitude) ) ) ,8) as distance "
                                . "from walker "
                                . "where is_available = 1 and "
                                . "is_active = 1 and "
                                . "is_approved = 1 and "
                                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                . "cos( radians(latitude) ) * "
                                . "cos( radians(longitude) - radians('$longitude') ) + "
                                . "sin( radians('$latitude') ) * "
                                . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                                . "walker.id IN($typestring) "
                                . "order by distance "
                                . "LIMIT 5";
                        $walkers = DB::select(DB::raw($query));
                        //Log::info('walkers = ' . print_r($walkers, true));
                        if ($walkers != NULL) {
                            $owner = Owner::find($owner_id);
                            $owner->latitude = $latitude;
                            $owner->longitude = $longitude;
                            $owner->save();

                            $request = new RideRequest;
                            $request->owner_id = $owner_id;
                            $request->request_start_time = date("Y-m-d H:i:s");
                            $request->save();
                            foreach ($type as $key) {
                                $reqserv = new RequestServices;
                                $reqserv->request_id = $request->id;
                                $reqserv->type = $key;
                                $reqserv->save();
                            }
                            $p = 0;
                            foreach ($walkers as $prov) {
                                $providers[$p]['id'] = $prov->id;
                                $providers[$p]['contact_name'] = $prov->contact_name;
                                $providers[$p]['picture'] = $prov->picture;
                                $providers[$p]['phone'] = $prov->phone;
                                $providers[$p]['latitude'] = $prov->latitude;
                                $providers[$p]['longitude'] = $prov->longitude;
                                $providers[$p]['rating'] = $prov->rate;
                                $providers[$p]['car_model'] = $prov->car_model;
                                $providers[$p]['car_number'] = $prov->car_number;
                                $providers[$p]['bearing'] = $prov->bearing;
                                $provserv = ProviderServices::where('provider_id', $prov->id)->get();
                                $types = ProviderType::where('id', '=', $prov->type)->first();
                                foreach ($provserv as $ps) {
                                    if ($ps->base_price != 0) {
                                        $providers[$p]['base_price'] = $ps->base_price;
                                        $providers[$p]['price_per_unit_time'] = $ps->price_per_unit_time;
                                        $providers[$p]['price_per_unit_distance'] = $ps->price_per_unit_distance;
                                        $providers[$p]['base_distance'] = $types->base_distance;
                                    } else {
                                        /* $settings = Settings::where('key', 'base_price')->first();
                                          $base_price = $settings->value; */
                                        $providers[$p]['base_price'] = $types->base_price;
                                        $providers[$p]['price_per_unit_time'] = $types->price_per_unit_time;
                                        $providers[$p]['price_per_unit_distance'] = $types->price_per_unit_distance;
                                        $providers[$p]['base_distance'] = $types->base_distance;
                                    }
                                }
                                /* $rat = WalkerReview::where('walker_id', $prov->id)->get();
                                  $countRating = count($rat); */

                                /* if ($countRating > 0) {
                                  $sum = 0;
                                  $count = 0;
                                  foreach ($rat as $ratp) {
                                  $sum = $ratp->rating + $sum;
                                  $count = $count + 1;
                                  }
                                  $avgrat = $sum / $count;
                                  $providers[$p]['rating'] = $avgrat;
                                  } else {
                                  $providers[$p]['rating'] = 0;
                                  } */
                                $s = 0;
                                $total_price = 0;
                                foreach ($provserv as $ps) {
                                    foreach ($type as $tp) {
                                        $providers[$p]['type'] = $tp;
                                        if ($tp == $ps->type) {
                                            $total_price = $total_price + $ps->base_price;
                                        }
                                    }
                                    $s = $s + 1;
                                }
                                $providers[$p]['total_price'] = $total_price;

                                $p = $p + 1;
                            }
                            //Log::info('providers = ' . print_r($providers, true));
                            $response_array = array(
                                'success' => true,
                                'request_id' => $request->id,
                                'provider' => $providers,
                            );
                            $response_code = 200;
                        }
                    } else {

                        // Do necessary operations
                        $archk = is_array($type);
                        //Log::info('type = ' . print_r($archk, true));
                        if ($archk == 1) {
                            $type = (int) $type;
                            //Log::info('type = ' . print_r($type, true));
                            $count = 1;
                        } else {
                            $type1 = explode(',', $type);
                            $type = array();
                            foreach ($type1 as $key) {
                                $type[] = (int) $key;
                            }
                            //Log::info('type = ' . print_r($type, true));
                            $count = count($type);
                        }
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }

                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }

                        $query = "SELECT "
                                . "walker.id, "
                                . "walker.contact_name, "
                                . "walker.picture, "
                                . "walker.phone, "
                                . "walker.latitude, "
                                . "walker.longitude, "
                                . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                . "cos( radians(latitude) ) * "
                                . "cos( radians(longitude) - radians('$longitude') ) + "
                                . "sin( radians('$latitude') ) * "
                                . "sin( radians(latitude) ) ) ,8) as distance "
                                . "from walker "
                                . "where is_available = 1 and "
                                . "is_active = 1 and "
                                . "is_approved = 1 and "
                                . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                . "cos( radians(latitude) ) * "
                                . "cos( radians(longitude) - radians('$longitude') ) + "
                                . "sin( radians('$latitude') ) * "
                                . "sin( radians(latitude) ) ) ) ,8) <= $distance "
                                . "order by distance "
                                . "LIMIT 5";
                        $walker = DB::select(DB::raw($query));
                        $typewalker = array();
                        $typewalker1 = array();

                        foreach ($walker as $key) {
                            $typewalker[] = $key->id;
                        }

                        $flag = 0;
                        if ($typewalker) {
                            $walkers = ProviderServices::whereIn('provider_id', $typewalker)->whereIn('type', $type)->groupBy('provider_id')->havingRaw('count(distinct type) = ' . $count)->get();
                            foreach ($walkers as $key) {
                                $typewalker1[] = $key->provider_id;
                            }
                            if ($typewalker1) {
                                $walkers = Walker::whereIn('id', $typewalker1)->get();
                                if ($walkers)
                                    $flag = 1;
                            }
                        }

                        if ($flag == 1) {

                            $c = 0;
                            foreach ($walkers as $key) {
                                $provider[$c]['id'] = $key->id;
                                $provider[$c]['contact_name'] = $key->contact_name;
                                $provider[$c]['picture'] = $key->picture;
                                $provider[$c]['phone'] = $key->phone;
                                $provider[$c]['latitude'] = $key->latitude;
                                $provider[$c]['longitude'] = $key->longitude;
                                $provider[$c]['rating'] = $key->rate;
                                $provider[$c]['car_model'] = $key->car_model;
                                $provider[$c]['car_number'] = $key->car_number;
                                $provider[$c]['bearing'] = $key->bearing;
                                $provserv = ProviderServices::where('provider_id', $key->id)->get();

                                foreach ($provserv as $ps) {
                                    $provider[$c]['type'] = $ps->type;
                                    $provider[$c]['base_price'] = $ps->base_price;
                                }

                                /* $rat = WalkerReview::where('walker_id', $key->id)->get();
                                  $countRating = count($rat);

                                  if ($countRating > 0) {
                                  $sum = 0;
                                  $count = 0;
                                  foreach ($rat as $ratp) {
                                  $sum = $ratp->rating + $sum;
                                  $count = $count + 1;
                                  }
                                  $avgrat = $sum / $count;
                                  $provider[$c]['rating'] = $avgrat;
                                  } else {
                                  $provider[$c]['rating'] = 0;
                                  } */
                                $s = 0;
                                $total_price = 0;
                                foreach ($provserv as $ps) {

                                    foreach ($type as $tp) {
                                        if ($tp == $ps->type) {
                                            $total_price = $total_price + $ps->base_price;
                                        }
                                    }
                                    $s = $s + 1;
                                }
                                $provider[$c]['total_price'] = $total_price;
                                $c = $c + 1;
                            }
                            //Log::info('provider = ' . print_r($provider, true));
                            $response_array = array(
                                'success' => true,
                                'provider' => $provider,
                            );
                            $response_code = 200;
                        } else {
                            $response_array = array(
                                'success' => false,
                                'error' => 56,
                                'error_messages' => array(56),
                            );
                            $response_code = 200;
                        }
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_providers_old() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $type = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if (!$type) {
                        // choose default type
                        $provider_type = ProviderType::where('is_default', 1)->first();

                        if (!$provider_type) {
                            $type = 1;
                        } else {
                            $type = $provider_type->id;
                        }
                    }
                    $ty = $type;
                    /* foreach ($type as $key) {
                      $typ[] = $key;
                      }
                      $ty = implode(",", $typ); */

                    $typequery = "SELECT distinct provider_id from walker_services where type IN($ty)";
                    $typewalkers = DB::select(DB::raw($typequery));
                    //Log::info('typewalkers = ' . print_r($typewalkers, true));
                    foreach ($typewalkers as $key) {
                        $types[] = $key->provider_id;
                    }
                    $typestring = implode(",", $types);
                    //Log::info('typestring = ' . print_r($typestring, true));

                    if ($typestring == '') {
                        $response_array = array('success' => false, 'error' => 'No provider found matching the service type.', 'error_messages' => array('No provider found matching the service type.'), 'error_code' => 405);
                        $response_code = 200;
                        return Response::json($response_array, $response_code);
                    }

                    $settings = Settings::where('key', 'default_search_radius')->first();
                    $distance = $settings->value;
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $unit = $settings->value;
                    if ($unit == 0) {
                        $multiply = 1.609344;
                    } elseif ($unit == 1) {
                        $multiply = 1;
                    }
                    $query = "SELECT "
                            . "walker.id, "
                            . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ,8) as distance "
                            . "from walker "
                            . "where is_available = 1 and "
                            . "is_active = 1 and "
                            . "is_approved = 1 and "
                            . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                            . "cos( radians(latitude) ) * "
                            . "cos( radians(longitude) - radians('$longitude') ) + "
                            . "sin( radians('$latitude') ) * "
                            . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                            . "walker.id IN($typestring) "
                            . "order by distance "
                            . "LIMIT 5";
                    $walkers = DB::select(DB::raw($query));
                    //Log::info('walkers = ' . print_r($walkers, true));
                    if ($walkers != NULL) {
                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = date("Y-m-d H:i:s");
                        $request->save();
                        foreach ($type as $key) {
                            $reqserv = new RequestServices;
                            $reqserv->request_id = $request->id;
                            $reqserv->type = $key;
                            $reqserv->save();
                        }
                        /* $reqserv = new RequestServices;
                          $reqserv->request_id = $request->id;
                          $reqserv->type = $type;
                          $reqserv->save(); */
                        $response_array = array(
                            'success' => true,
                            'request_id' => $request->id,
                            'walkers' => $walkers,
                        );
                        $response_code = 200;
                    } else {
                        $response_array = array(
                            'success' => false,
                            'error' => 56,
                            'error_messages' => array(56),
                        );
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Create Request if provider_selection == 2 in settings table

    public function create_request_providers() {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $provider_id = Input::get('provider_id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $typein = Input::get('type');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'provider_id' => $provider_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'provider_id' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'provider_id.required' => 57,
                        )
        );
        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();

                    if ($is_multiple_service->value == 0) {

                        $req =RideRequest::find($request_id);
                        $req->current_walker = $provider_id;
                        $req->save();

                        $response_array = array(
                            'success' => true,
                            'request_id' => $req->id,
                        );
                        $response_code = 200;
                    } else {

                        $archk = is_array($typein);
                        //Log::info('type = ' . print_r($archk, true));
                        if ($archk == 1) {
                            $type = $typein;
                            //Log::info('type = ' . print_r($typein, true));
                        } else {
                            $type = explode(',', $typein);
                            //Log::info('type = ' . print_r($type, true));
                        }
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = date("Y-m-d H:i:s");
                        $request->current_walker = $provider_id;
                        $request->latitude = $latitude;
                        $request->longitude = $longitude;
                        $request->save();
                        $flag = 0;
                        $base_price = 0;

                        $typs = array();
                        $typi = array();
                        $typp = array();

                        foreach ($type as $key) {
                            $reqserv = new RequestServices;
                            $reqserv->request_id = $request->id;
                            $reqserv->type = $key;
                            $reqserv->save();

                            $typ1 = ProviderType::where('id', $key)->first();
                            $ps = ProviderServices::where('type', $key)->where('provider_id', $provider_id)->first();
                            if ($ps->base_price > 0) {
                                $typp1 = 0.00;
                                $typp1 = $ps->base_price;
                            } else {
                                $typp1 = 0.00;
                            }
                            $typs['name'] = $typ1->name;
                            $typs['price'] = $typp1;

                            array_push($typi, $typs);

                            if ($ps) {
                                $base_price = $base_price + $ps->base_price;
                            }
                        }

                        $settings = Settings::where('key', 'provider_timeout')->first();
                        $time_left = $settings->value;

                        $msg_array = array();
                        $msg_array['type'] = $typi;
                        $msg_array['unique_id'] = 1;
                        $msg_array['request_id'] = $request->id;
                        $msg_array['time_left_to_respond'] = $time_left;
                        $msg_array['request_service'] = $key;
                        $msg_array['total_base_price'] = $base_price;

                        $owner = Owner::find($owner_id);
                        $request_data = array();
                        $request_data['owner'] = array();
                        $request_data['owner']['name'] = $owner->contact_name;
                        $request_data['owner']['picture'] = $owner->picture;
                        $request_data['owner']['phone'] = $owner->phone;
                        $request_data['owner']['address'] = $owner->address;
                        $request_data['owner']['latitude'] = $request->latitude;
                        $request_data['owner']['longitude'] = $request->longitude;
                        $request_data['owner']['rating'] = $owner->rate;
                        $request_data['owner']['num_rating'] = $owner->rate_count;
                        /* $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
                          $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count(); */

                        $msg_array['request_data'] = $request_data;

                        $title = "New Request";
                        $message = $msg_array;
                        //Log::info('first_walker_id = ' . print_r($provider_id, true));
                        //Log::info('New request = ' . print_r($message, true));
                        /* don't do json_encode in above line because if */
                        send_notifications($provider_id, "walker", $title, $message);

                        $response_array = array(
                            'success' => true,
                            'request_id' => $request->id,
                        );
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Cancel Request
    public function cancellation() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'request_id' => $request_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'request_id' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'request_id.required' => 19,
                        )
        );
        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $req =RideRequest::find($request_id);
                    if ($req->is_paid == 0) {
                        DB::delete("delete from request_services where request_id = '" . $request_id . "';");
                        DB::delete("delete from walk_location where request_id = '" . $request_id . "';");
                        $req->is_cancelled = 1;
                        $req->save();
                        $response_array = array(
                            'success' => true,
                            'deleted request_id' => $req->id,
                        );
                        $response_code = 200;
                    } else {
                        $deduce = 0.85;
                        $refund = $req->total * $deduce;
                        $req->is_cancelled = 1;
                        $req->refund = $refund;

                        if (Input::has('cod')) {
                            if (Input::get('cod') == 1) {
                                $request->cod = 1;
                            } else {
                                $request->cod = 0;
                            }
                        }
                        $req->save();
                        // Refund Braintree Stuff.
                        DB::delete("delete from request_services where request_id = '" . $request_id . "';");
                        DB::delete("delete from walk_location where request_id = '" . $request_id . "';");
                        $response_array = array(
                            'success' => true,
                            'refund' => $refund,
                            'deleted request_id' => $req->id,
                        );
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(11), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Create Request if provider_selection == 1 in settings table

    public function create_request() {
        $all_input = Input::all();
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $d_latitude = Input::get('d_latitude');
        $d_longitude = Input::get('d_longitude');
        $enterpriseclient_id = Input::get('enterpriseclient_id');
        $user_create_time = date('Y-m-d H:i:s');
        if (Input::has('create_date_time')) {
            $user_create_time = Input::get('create_date_time');
        }
        $payment_opt = 0;
        if (Input::has('payment_mode')) {
            $payment_opt = Input::get('payment_mode');
        }
        if (Input::has('payment_opt')) {
            $payment_opt = Input::get('payment_opt');
        }
        $time_zone = "UTC";
        if (Input::has('time_zone')) {
            $time_zone = trim(Input::get('time_zone'));
        }
        $src_address = "Address Not Available";
        if (Input::has('src_address')) {
            $src_address = trim(Input::get('src_address'));
        } else {
            $src_address = get_address($latitude, $longitude);
        }
        $dest_address = "Address Not Available";
        if (Input::has('dest_address')) {
            $dest_address = trim(Input::get('dest_address'));
        } else {
            $dest_address = get_address($d_latitude, $d_longitude);
        }
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'd_latitude' => $d_latitude,
                    'd_longitude' => $d_longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    'd_latitude' => 'required',
                    'd_longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                    'd_latitude.required' => 49,
                    'd_longitude.required' => 49,
                    'enterpriseclient_id' => 120,
                        )
        );

        if(! isset($enterpriseclient_id)) {
            $enterpriseclient_id = 1;
        }

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $unit = "";
            $driver_data = "";

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    /* SEND REFERRAL & PROMO INFO */
                    $settings = Settings::where('key', 'referral_code_activation')->first();
                    $referral_code_activation = $settings->value;
                    if ($referral_code_activation) {
                        $referral_code_activation_txt = "referral on";
                    } else {
                        $referral_code_activation_txt = "referral off";
                    }

                    $settings = Settings::where('key', 'promotional_code_activation')->first();
                    $promotional_code_activation = $settings->value;
                    if ($promotional_code_activation) {
                        $promotional_code_activation_txt = "promo on";
                    } else {
                        $promotional_code_activation_txt = "promo off";
                    }
                    /* SEND REFERRAL & PROMO INFO */
                    // Do necessary operations
                    $request = DB::table('request')->where('owner_id', $owner_data->id)
                            ->where('is_completed', 0)
                            ->where('is_cancelled', 0)
                            ->where('current_walker', '!=', 0)
                            ->first();
                    if ($request) {
                        $response_array = array('success' => false, 'error' => 58, 'error_messages' => array(58), 'error_code' => 405);
                        $response_code = 200;
                    } else {
                        /* SEND REFERRAL & PROMO INFO */
                        if ($payment_opt != 1) {
                            $card_count = Payment::where('owner_id', '=', $owner_id)->count();
                            if ($card_count <= 0) {
                                $response_array = array('success' => false, 'error' => 59, 'error_messages' => array(59), 'error_code' => 417);
                                $response_code = 200;
                                $response = Response::json($response_array, $response_code);
                                return $response;
                            }
                        }
                        /* if ($owner_data->debt > 0) {
                          $response_array = array('success' => false, 'error' => 72, 'error_messages' => array(72), 'error_code' => 417);
                          $response_code = 200;
                          $response = Response::json($response_array, $response_code);
                          return $response;
                          } */
                        if (Input::has('type')) {
                            //Log::info('out');
                            $type = Input::get('type');
                            if (!$type) {
                                // choose default type
                                $provider_type = ProviderType::where('is_default', '=',1)->first();

                                if (!$provider_type) {
                                    $type = 2;
                                } else {
                                    $type = $provider_type->id;
                                }
                            }

                            $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
                            $typewalkers = DB::select(DB::raw($typequery));

                            //Log::info('typewalkers = ' . print_r($typewalkers, true));

                            if (count($typewalkers) > 0) {

                                foreach ($typewalkers as $key) {

                                    $types[] = $key->provider_id;
                                }

                                $typestring = implode(",", $types);
                                //Log::info('typestring = ' . print_r($typestring, true));
                            } else {
                                /* $driver = Keywords::where('id', 1)->first();
                                  send_notifications($owner_id, "owner", 'No ' . $driver->keyword . ' Found', 'No ' . $driver->keyword . ' found matching the service type.'); */
                                send_notifications($owner_id, "owner", 'No ' . Config::get('app.generic_keywords.Provider') . ' Found', 55);

                                /* $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.','error_messages' => array('No ' . $driver->keyword . ' found matching the service type.'), 'error_code' => 416); */
                                $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 416);
                                $response_code = 200;
                                return Response::json($response_array, $response_code);
                            }

                            $settings = Settings::where('key', 'default_search_radius')->first();
                            $distance = $settings->value;
                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $multiply = 1.609344;
                            } elseif ($unit == 1) {
                                $multiply = 1;
                            }
                            $query = "SELECT walker.*, "
                                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ,8) as distance "
                                    . "FROM walker "
                                    . "where is_available = 1 and "
                                    . "is_active = 1 and "
                                    . "is_approved = 1 and "
                                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                                    . "walker.deleted_at IS NULL and "
                                    . "walker.id IN($typestring) "
                                    . "order by distance";
                            $walkers = DB::select(DB::raw($query));
                            $walker_list = array();

                            $enterprise_client = EnterpriseClient::find($enterpriseclient_id);

                            $params = array(
                                'origin_latitude' =>        $latitude,
                                'origin_longitude' =>       $longitude,
                                'destination_latitude' =>   $d_latitude,
                                'destination_longitude' =>  $d_longitude,
                                'enterprise_client' =>      $enterprise_client,
                                'is_wheelchair' =>          0,
                                'is_roundtrip' =>           0,
                                'type' =>                   $type
                            );

                            $fare = CalculateFare($params);

                            $thetotal = $fare['ride_estimated_price'];
                            if($fare['is_roundtrip'] == 1) {
                                $thetotal = $thetotal / 2;
                            }

                            $owner = Owner::find($owner_id);
                            $owner->latitude = $latitude;
                            $owner->longitude = $longitude;
                            $owner->save();

                            $passenger_phone = "";
                            if(strlen($owner->phone) > 0) {
                                $passenger_phone = $owner->phone;
                            }

                            $request = new RideRequest;
                            $request->metadata('fare', $fare);
                            $request->owner_id = $owner_id;
                            $request->healthcare_id = $enterpriseclient_id;
                            $request->payment_mode = $payment_opt;
                            $request->time_zone = $time_zone;
                            $request->src_address = $src_address;
                            $request->service_type = $type;
                            $request->passenger_contact_name = $owner->contact_name;
                            $request->passenger_phone = $passenger_phone;

                            /* $user_timezone = $owner->timezone; */
                            $user_timezone = Config::get('app.timezone');
                            $default_timezone = Config::get('app.timezone');
                            /* $offset = $this->get_timezone_offset($default_timezone, $user_timezone); */
                            $date_time = get_user_time($default_timezone, $user_timezone, date("Y-m-d H:i:s"));
                            $request->D_latitude = 0;
                            if (isset($d_latitude)) {
                                $request->D_latitude = $d_latitude;
                            }
                            $request->D_longitude = 0;
                            if (isset($d_longitude)) {
                                $request->D_longitude = $d_longitude;
                            }
                            $request->dest_address = $dest_address;
                            /* $request->request_start_time = date("Y-m-d H:i:s"); */
                            $request->request_start_time = $date_time;
                            $request->latitude = $latitude;
                            $request->longitude = $longitude;
                            $request->req_create_user_time = $user_create_time;
//                            $request->distance = $ride_info['distance'];
//                            $request->time = $ride_info['time'];
//                            $request->total = $ride_info['amount'];
                            $request->total = $thetotal;
                            $request->save();

                            $ride_detail = new RideDetails;
                            $ride_detail->request_id = $request->id;
                            $ride_detail->save();

                            $reqserv = new RequestServices;
                            $reqserv->request_id = $request->id;
                            $reqserv->type = $type;
                            $reqserv->save();
                        } else {
                            //Log::info('in');
                            $settings = Settings::where('key', 'default_search_radius')->first();
                            $distance = $settings->value;
                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $multiply = 1.609344;
                            } elseif ($unit == 1) {
                                $multiply = 1;
                            }
                            $query = "SELECT walker.*, "
                                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ,8) as distance "
                                    . "FROM walker "
                                    . "where is_available = 1 and "
                                    . "is_active = 1 and "
                                    . "is_approved = 1 and "
                                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                                    . "cos( radians(latitude) ) * "
                                    . "cos( radians(longitude) - radians('$longitude') ) + "
                                    . "sin( radians('$latitude') ) * "
                                    . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                                    . "walker.deleted_at IS NULL "
                                    . "order by distance";
                            $walkers = DB::select(DB::raw($query));
                            $walker_list = array();

                            $owner = Owner::find($owner_id);
                            $owner->latitude = $latitude;
                            $owner->longitude = $longitude;
                            $owner->save();

                            $enterprise_client = EnterpriseClient::find($enterpriseclient_id);

                            $params = array(
                                'origin_latitude' =>        $latitude,
                                'origin_longitude' =>       $longitude,
                                'destination_latitude' =>   $d_latitude,
                                'destination_longitude' =>  $d_longitude,
                                'enterprise_client' =>      $enterprise_client,
                                'is_wheelchair' =>          0,
                                'is_roundtrip' =>           0,
                                'type' =>                   $type
                            );

                            $fare = CalculateFare($params);
                            $request = new RideRequest;
                            $request->metadata('fare', $fare);
                            $request->owner_id = $owner_id;
                            $request->payment_mode = $payment_opt;
                            $request->time_zone = $time_zone;
                            $request->src_address = $src_address;


                            /* $user_timezone = $owner->timezone; */
                            $user_timezone = Config::get('app.timezone');
                            $default_timezone = Config::get('app.timezone');
                            /* $offset = $this->get_timezone_offset($default_timezone, $user_timezone); */
                            $date_time = get_user_time($default_timezone, $user_timezone, date("Y-m-d H:i:s"));
                            $request->D_latitude = 0;
                            if (isset($d_latitude)) {
                                $request->D_latitude = $d_latitude;
                            }
                            $request->D_longitude = 0;
                            if (isset($d_longitude)) {
                                $request->D_longitude = $d_longitude;
                            }
                            $request->dest_address = $dest_address;
                            $request->request_start_time = $date_time;
                            $request->latitude = $latitude;
                            $request->longitude = $longitude;
                            $request->req_create_user_time = $user_create_time;
                            $request->save();

                            $reqserv = new RequestServices;
                            $reqserv->request_id = $request->id;
                            $reqserv->save();
                        }
                        $i = 0;
                        $first_walker_id = 0;
                        foreach ($walkers as $walker) {
                            /*$request_meta = new RequestMeta;
                            $request_meta->request_id = $request->id;
                            $request_meta->walker_id = $walker->id;*/
                            if ($i == 0) {
                                $first_walker_id = $walker->id;
                                $driver_data = array();
                                $driver_data['unique_id'] = 1;
                                $driver_data['id'] = "" . $first_walker_id;
                                $driver_data['contact_name'] = "" . $walker->contact_name;
                                $driver_data['phone'] = "" . $walker->phone;
                                /*  $driver_data['email'] = "" . $walker->email; */
                                $driver_data['picture'] = "" . $walker->picture;
                                $driver_data['bio'] = "" . $walker->bio;
                                /* $driver_data['address'] = "" . $walker->address;
                                  $driver_data['state'] = "" . $walker->state;
                                  $driver_data['country'] = "" . $walker->country;
                                  $driver_data['zipcode'] = "" . $walker->zipcode;
                                  $driver_data['login_by'] = "" . $walker->login_by;
                                  $driver_data['social_unique_id'] = "" . $walker->social_unique_id;
                                  $driver_data['is_active'] = "" . $walker->is_active;
                                  $driver_data['is_available'] = "" . $walker->is_available; */
                                $driver_data['latitude'] = "" . $walker->latitude;
                                $driver_data['longitude'] = "" . $walker->longitude;
                                /* $driver_data['is_approved'] = "" . $walker->is_approved; */
                                $driver_data['type'] = "" . $walker->type;
                                $driver_data['car_model'] = "" . $walker->car_model;
                                $driver_data['car_number'] = "" . $walker->car_number;
                                $driver_data['rating'] = $walker->rate;
                                $driver_data['num_rating'] = $walker->rate_count;
                                $i++;
                            }
                            //$request_meta->save();
                        }
                        $req =RideRequest::find($request->id);
                        //$req->current_walker = $first_walker_id; //now we need to stop automatic driver assignment so I am changing here current_walker to 0;
                        $req->current_walker = 0;
                        $req->save();

                        $settings = Settings::where('key', 'provider_timeout')->first();
                        $time_left = $settings->value;





                        // Send Notification
                        $walker = Walker::find($first_walker_id);
                        if ($walker) {
                            $msg_array = array();
                            $msg_array['unique_id'] = 1;
                            $msg_array['request_id'] = $request->id;
                            $msg_array['time_left_to_respond'] = $time_left;


                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $unit_set = 'kms';
                            } elseif ($unit == 1) {
                                $unit_set = 'miles';
                            }

                            $msg_array['unit'] = $unit_set;



                            $msg_array['payment_mode'] = $payment_opt;

                            $owner = Owner::find($owner_id);
                            $request_data = array();
                            $request_data['owner'] = array();
                            $request_data['owner']['name'] = $owner->contact_name;
                            $request_data['owner']['picture'] = $owner->picture;
                            $request_data['owner']['phone'] = $owner->phone;
                            $request_data['owner']['address'] = $owner->address;
                            $request_data['owner']['latitude'] = $request->latitude;
                            $request_data['owner']['longitude'] = $request->longitude;
                            if ($d_latitude != NULL) {
                                $request_data['owner']['d_latitude'] = $d_latitude;
                                $request_data['owner']['d_longitude'] = $d_longitude;
                            }
                            $request_data['owner']['owner_dist_lat'] = $request->D_latitude;
                            $request_data['owner']['owner_dist_long'] = $request->D_longitude;
                            $request_data['owner']['payment_type'] = $payment_opt;
                            $request_data['owner']['rating'] = $owner->rate;
                            $request_data['owner']['num_rating'] = $owner->rate_count;
                            $request_data['dog'] = array();
                            if ($dog = Dog::find($owner->dog_id)) {

                                $request_data['dog']['name'] = $dog->name;
                                $request_data['dog']['age'] = $dog->age;
                                $request_data['dog']['breed'] = $dog->breed;
                                $request_data['dog']['likes'] = $dog->likes;
                                $request_data['dog']['picture'] = $dog->image_url;
                            }
                            $msg_array['request_data'] = $request_data;

                            $title = "New Request";
                            $message = $msg_array;

                            send_notifications($first_walker_id, "walker", $title, $message);
                        } else {
//                            send_notifications($owner_id, "owner", 'No ' . Config::get('app.generic_keywords.Provider') . ' Found', 'No ' . Config::get('app.generic_keywords.Provider') . ' found for the selected service in your area currently');

//                            $response_array = array('success' => true);
//                            $response_code = 200;
//                            return Response::json($response_array, $response_code);
                        }
                        // Send SMS 
                        $settings = Settings::where('key', 'sms_request_created')->first();
                        $pattern = $settings->value;
                        $pattern = str_replace('%user%', $owner_data->contact_name, $pattern);
                        $pattern = str_replace('%id%', $request->id, $pattern);
                        $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
//                        sms_notification(1, 'admin', $pattern);
                        sms_notification(NULL, 'ride_assignee_1', $pattern);
                        sms_notification(NULL, 'ride_assignee_2', $pattern);
                        sms_notification(NULL, 'ride_assignee_3', $pattern);


                        // Send Email
                        $settings = Settings::where('key', 'admin_email_address')->first();
                        $admin_email = $settings->value;
                        $follow_url = web_url() . "/booking/signin";
                        $pattern = array(
                            'admin_email' =>    $admin_email,
                            'trip_id' =>        $request->id,
                            'follow_url' =>     $follow_url,
                            'has_wheelchair' => 0
                        );
                        $subject = "Ride Booking Request";
//                        email_notification(1, 'admin', $pattern, $subject, 'new_request', null);

                        email_notification(NULL, 'ride_assignee', $pattern, $subject, 'new_request', null);
                        email_notification(NULL, 'ride_assignee_2', $pattern, $subject, 'new_request', null);
                        email_notification(NULL, 'ride_assignee_3', $pattern, $subject, 'new_request', null);



                        if (!empty($driver_data)) {
                            $response_array = array(
                                'success' => true,
                                'unique_id' => 1,
                                'is_referral_active' => $referral_code_activation,
                                'is_referral_active_txt' => $referral_code_activation_txt,
                                'is_promo_active' => $promotional_code_activation,
                                'is_promo_active_txt' => $promotional_code_activation_txt,
                                'request_id' => $request->id,
                                'walker' => $driver_data,
                            );
                        } else {
                            $response_array = array(
                                'success' => true,
                                'unique_id' => 1,
                                'request_id' => $request->id,
                            );
                        }


                        $response_code = 200;


                    }
                } else {

                    print_r("Inactive Token" , true);
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    //create crequest with fare

    public function create_request_fare() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $distance = Input::get('distance');
        $time = Input::get('time');
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                        )
        );

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    if ($owner_data->debt > 0) {
                        $response_array = array('success' => false, 'error' => 72, 'error_messages' => array(72), 'error_code' => 417);
                        $response_code = 200;
                        $response = Response::json($response_array, $response_code);
                        return $response;
                    }

                    if (Input::has('type')) {
                        $type = Input::get('type');
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }
                        $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
                        $typewalkers = DB::select(DB::raw($typequery));

                        //Log::info('typewalkers = ' . print_r($typewalkers, true));

                        if (count($typewalkers) > 0) {

                            foreach ($typewalkers as $key) {

                                $types[] = $key->provider_id;
                            }

                            $typestring = implode(",", $types);
                            //Log::info('typestring = ' . print_r($typestring, true));
                        } else {
                            /* $var = Keywords::where('id', 1)->first();
                              $response_array = array('success' => false, 'error' => 'No ' . $var->keyword . ' found matching the service type.','error_messages' => array('No ' . $var->keyword . ' found matching the service type.'), 'error_code' => 405); */
                            $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 405);
                            $response_code = 200;
                            return Response::json($response_array, $response_code);
                        }

                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }
                        $query = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance and walker.id IN($typestring) order by distance";

                        $walkers = DB::select(DB::raw($query));
                        $walker_list = array();

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = date("Y-m-d H:i:s");
                        $request->latitude = $latitude;
                        $request->longitude = $longitude;
                        $request->save();

                        $reqserv = new RequestServices;
                        $reqserv->request_id = $request->id;
                        $reqserv->type = $type;
                        $reqserv->save();
                    } else {
                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }
                        $query = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance order by distance";
                        $walkers = DB::select(DB::raw($query));
                        $walker_list = array();

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = date("Y-m-d H:i:s");
                        $request->latitude = $latitude;
                        $request->longitude = $longitude;
                        $request->save();

                        $reqserv = new RequestServices;
                        $reqserv->request_id = $request->id;
                        $reqserv->save();
                    }
                    $i = 0;
                    $first_walker_id = 0;
                    foreach ($walkers as $walker) {
                        $request_meta = new RequestMeta;
                        $request_meta->request_id = $request->id;
                        $request_meta->walker_id = $walker->id;
                        if ($i == 0) {
                            $first_walker_id = $walker->id;
                            $i++;
                        }
                        $request_meta->save();
                    }
                    $req =RideRequest::find($request->id);
                    $req->current_walker = $first_walker_id;
                    $req->save();

                    $settings = Settings::where('key', 'provider_timeout')->first();
                    $time_left = $settings->value;

                    // Send Notification
                    $walker = Walker::find($first_walker_id);

                    if ($walker) {
                        $msg_array = array();
                        $msg_array['unique_id'] = 1;
                        $msg_array['request_id'] = $request->id;
                        $msg_array['time_left_to_respond'] = $time_left;
                        $owner = Owner::find($owner_id);
                        $request_data = array();
                        $request_data['owner'] = array();
                        $request_data['owner']['name'] = $owner->contact_name;
                        $request_data['owner']['picture'] = $owner->picture;
                        $request_data['owner']['phone'] = $owner->phone;
                        $request_data['owner']['address'] = $owner->address;
                        $request_data['owner']['latitude'] = $request->latitude;
                        $request_data['owner']['longitude'] = $request->longitude;
                        $request_data['owner']['rating'] = $owner->rate;
                        $request_data['owner']['num_rating'] = $owner->rate_count;
                        /* $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
                          $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count(); */

                        $request_data['dog'] = array();
                        if ($dog = Dog::find($owner->dog_id)) {

                            $request_data['dog']['name'] = $dog->name;
                            $request_data['dog']['age'] = $dog->age;
                            $request_data['dog']['breed'] = $dog->breed;
                            $request_data['dog']['likes'] = $dog->likes;
                            $request_data['dog']['picture'] = $dog->image_url;
                        }
                        $msg_array['request_data'] = $request_data;

                        $title = "New Request";
                        $message = $msg_array;
                        //Log::info('first_walker_id = ' . print_r($first_walker_id, true));
                        //Log::info('New request = ' . print_r($message, true));
                        /* don't do json_encode in above line because if */
                        send_notifications($first_walker_id, "walker", $title, $message);
                    }

                    $pt = ProviderServices::where('provider_id', $first_walker_id)->get();

                    // Send SMS 
                    $settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner_data->contact_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);

                    // send email
                    //*
                    $settings = Settings::where('key', 'email_new_request')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
                    $subject = "New Ride Request Created";
                    email_notification(1, 'admin', $pattern, $subject);
                    //*/
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $follow_url = web_url() . "/booking/signin";
                    $pattern = array('admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url);
                    $subject = "Ride Booking Request";
                    email_notification(1, 'admin', $pattern, $subject, 'new_request', null);

                    $response_array = array(
                        'success' => true,
                        'request_id' => $request->id,
                    );
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    //fare calculator

    public function fare_calculator() {

        if (Request::isMethod('post')) {
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $distance = Input::get('distance');
            $time = Input::get('time');

            $validator = Validator::make(
                            array(
                        'token' => $token,
                        'owner_id' => $owner_id,
                        'distance' => $distance,
                        'time' => $time,
                            ), array(
                        'token' => 'required',
                        'owner_id' => 'required|integer',
                        'distance' => 'required',
                        'time' => 'required',
                            ), array(
                        'token.required' => '',
                        'owner_id.required' => 6,
                        'distance.required' => 73,
                        'time.required' => 74,
                            )
            );

            /* $var = Keywords::where('id', 2)->first(); */

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);

                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        $request_typ = ProviderType::where('is_default', '=', 1)->first();

                        $setbase_distance = $request_typ->base_distance;
                        $base_price1 = $request_typ->base_price;
                        $price_per_unit_distance1 = $request_typ->price_per_unit_distance;
                        $price_per_unit_time1 = $request_typ->price_per_unit_time;
                        // Do necessary operations

                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;

                        /* $setbase_price = Settings::where('key', 'base_price')->first();
                          $base_price = $setbase_price->value; */
                        if ($unit == 0) {
                            $distanceKm = $distance * 0.001;
                            /* $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                              $price_per_unit_distance = $setdistance_price->value * $distanceKm;
                             */
                            if ($distanceKm <= $setbase_distance) {
                                $price_per_unit_distance = 0;
                            } else {
                                $price_per_unit_distance = $price_per_unit_distance1 * ($distanceKm - $setbase_distance);
                            }
                        } else {
                            $distanceMiles = $distance * 0.000621371;
                            /* $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                              $price_per_unit_distance = $setdistance_price->value * $distanceMiles; */
                            if ($distanceMiles <= $setbase_distance) {
                                $price_per_unit_distance = 0;
                            } else {
                                $price_per_unit_distance = $price_per_unit_distance1 * ($distanceMiles - $setbase_distance);
                            }
                        }
                        $timeMinutes = $time * 0.0166667;
                        /* $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                          $price_per_unit_time = $settime_price->value * $timeMinutes; */
                        $price_per_unit_time = $price_per_unit_time1 * $timeMinutes;

                        /* $total = $base_price + $price_per_unit_distance + $price_per_unit_time; */
                        $total = $base_price1 + $price_per_unit_distance + $price_per_unit_time;

                        $total = $total;

                        /* $currency_selected = Keywords::find(5);
                          $cur_symb = $currency_selected->keyword; */
                        $cur_symb = Config::get('app.generic_keywords.Currency');
                        $response_array = array(
                            'success' => true,
                            'setbase_distance' => $setbase_distance,
                            'base_price' => currency_converted($base_price1),
                            'price_per_unit_distance' => currency_converted($price_per_unit_distance1),
                            'price_per_unit_time' => currency_converted($price_per_unit_time1),
                            'estimated_fare' => ceil(currency_converted($total)),
                            'currency' => $cur_symb,
                        );
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                        $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                    } else {
                        $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                    }
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Get cancel request

    
    
    public function cancel_request() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $cancel_reason = Input::get('cancel_reason');
        
        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                    
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,  
                        )
        );



        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request =RideRequest::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {

                            $status_string = "";
                            if ($cancel_reason == 1) {
                                $status_string = "Driver delayed";
                            } elseif ($cancel_reason == 2) {
                                $status_string = "I have changed my mind";
                            }
                            elseif ($cancel_reason == 3) {
                                $status_string = "I want to book another cab";
                            }
                            
                          RideRequest::where('id', $request_id)->update(array('is_cancelled' => 1,'cancel_reason' => $status_string));
                            RequestMeta::where('request_id', $request_id)->update(array('is_cancelled' => 1));

                            if ($request->promo_id) {
                                $promo_update_counter = PromoCodes::find($request->promo_id);
                                $promo_update_counter->uses = $promo_update_counter->uses + 1;
                                $promo_update_counter->save();

                                UserPromoUse::where('user_id', '=', $owner_id)->where('code_id', '=', $request->promo_id)->delete();

                                $owner = Owner::find($owner_id);
                                $owner->promo_count = $owner->promo_count - 1;
                                $owner->save();

                                $request =RideRequest::find($request_id);
                                $request->promo_id = 0;
                                $request->promo_code = "";
                                $request->save();
                            }

                            if ($request->confirmed_walker) {
                                $walker = Walker::find($request->confirmed_walker);
                                $walker->is_available = 1;
                                $walker->save();
                            }
                            if ($request->current_walker) {

                                $msg_array = array();
                                $msg_array['request_id'] = $request_id;
                                $msg_array['unique_id'] = 2;

                                $owner = Owner::find($owner_id);
                                $request_data = array();
                                $request_data['owner'] = array();
                                $request_data['owner']['name'] = $owner->contact_name;
                                $request_data['owner']['picture'] = $owner->picture;
                                $request_data['owner']['phone'] = $owner->phone;
                                $request_data['owner']['address'] = $owner->address;
                                $request_data['owner']['latitude'] = $request->latitude;
                                $request_data['owner']['longitude'] = $request->longitude;
                                $request_data['owner']['rating'] = $owner->rate;
                                $request_data['owner']['num_rating'] = $owner->rate_count;

                                $request_data['dog'] = array();
                                if ($dog = Dog::find($owner->dog_id)) {
                                    $request_data['dog']['name'] = $dog->name;
                                    $request_data['dog']['age'] = $dog->age;
                                    $request_data['dog']['breed'] = $dog->breed;
                                    $request_data['dog']['likes'] = $dog->likes;
                                    $request_data['dog']['picture'] = $dog->image_url;
                                }
                                $msg_array['request_data'] = $request_data;

                                $title = "Request Cancelled";
                                $message = $msg_array;
                                send_notifications($request->current_walker, "walker", $title, $message);
                            }
                            $response_array = array(
                                'success' => true,
                            );

                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 75, 'error_messages' => array(75), 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }
    
    

    // Get Request Status

    public function get_running_request() {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $request_data = "";
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                /* SEND REFERRAL & PROMO INFO */
                $settings = Settings::where('key', 'referral_code_activation')->first();
                $referral_code_activation = $settings->value;
                if ($referral_code_activation) {
                    $referral_code_activation_txt = "referral on";
                } else {
                    $referral_code_activation_txt = "referral off";
                }
                $settings = Settings::where('key', 'promotional_code_activation')->first();
                $promotional_code_activation = $settings->value;
                if ($promotional_code_activation) {
                    $promotional_code_activation_txt = "promo on";
                } else {
                    $promotional_code_activation_txt = "promo off";
                }
                /* SEND REFERRAL & PROMO INFO */
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $running_requests = Request::select('request.id as request_id', 'request.D_latitude as dest_latitude', 'request.D_longitude as dest_longitude', 'request.latitude as src_latitude', 'request.longitude as src_longitude', 'owner.id as user_id', 'owner.contact_name as user_contact_name', 'owner.phone as user_phone', 'owner.email as user_email', 'owner.picture as user_picture', 'owner.bio as user_bio', 'owner.address as user_address', 'owner.state as user_state', 'owner.country as user_country', 'owner.zipcode as user_zipcode', 'owner.rate as user_rate', 'owner.rate_count as user_rate_count', 'walker.id as provider_id', 'walker.contact_name as provider_contact_name', 'walker.phone as provider_phone', 'walker.email as provider_email', 'walker.picture as provider_picture', 'walker.bio as provider_bio', 'walker.address as provider_address', 'walker.state as provider_state', 'walker.country as provider_country', 'walker.zipcode as provider_zipcode', 'walker.latitude as provider_latitude', 'walker.longitude as provider_longitude', 'walker.type as provider_type', 'walker.car_model as provider_car_model', 'walker.car_number as provider_car_number', 'walker.rate as provider_rate', 'walker.rate_count as provider_rate_count', 'walker.bearing as bearing')
                            ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                            ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                            ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                            ->where('request.owner_id', '=', $owner_id)
                            ->where('request.is_cancelled', '=', 0)
                            ->where('request.current_walker', '>', 0)
                            ->where('request.is_walker_rated', '=', 0)
                            ->orderBy('request.id', 'DESC')
                            ->get();
                    $request_data = array();
                    foreach ($running_requests as $requests) {
                        $data['request_id'] = $requests->request_id;
                        /* $data['owner']['user_id'] = $requests->user_id;
                          $data['owner']['owner_lat'] = $requests->src_latitude;
                          $data['owner']['latitude'] = $requests->src_latitude;
                          $data['owner']['owner_long'] = $requests->src_longitude;
                          $data['owner']['longitude'] = $requests->src_longitude;
                          $data['owner']['owner_dist_lat'] = $requests->dest_latitude;
                          $data['owner']['d_latitude'] = $requests->dest_latitude;
                          $data['owner']['owner_dist_long'] = $requests->dest_longitude;
                          $data['owner']['d_longitude'] = $requests->dest_longitude;
                          $data['owner']['contact_name'] = $requests->user_contact_name;
                          $data['owner']['phone'] = $requests->user_phone;
                          $data['owner']['email'] = $requests->user_email;
                          $data['owner']['picture'] = $requests->user_picture;
                          $data['owner']['bio'] = $requests->user_bio;
                          $data['owner']['address'] = $requests->user_address;
                          $data['owner']['state'] = $requests->user_state;
                          $data['owner']['country'] = $requests->user_country;
                          $data['owner']['zipcode'] = $requests->user_zipcode;
                          $data['owner']['rating'] = $requests->user_rate;
                          $data['owner']['num_rating'] = $requests->user_rate_count; */
                        $data['walker']['id'] = $requests->provider_id;
                        $data['walker']['contact_name'] = $requests->provider_contact_name;
                        $data['walker']['phone'] = $requests->provider_phone;
                        $data['walker']['email'] = $requests->provider_email;
                        $data['walker']['picture'] = $requests->provider_picture;
                        $data['walker']['bio'] = $requests->provider_bio;
                        $data['walker']['address'] = $requests->provider_address;
                        $data['walker']['state'] = $requests->provider_state;
                        $data['walker']['country'] = $requests->provider_country;
                        $data['walker']['zipcode'] = $requests->provider_zipcode;
                        $data['walker']['latitude'] = $requests->provider_latitude;
                        $data['walker']['longitude'] = $requests->provider_longitude;
                        $data['walker']['type'] = $requests->provider_type;
                        $data['walker']['rating'] = $requests->provider_rate;
                        $data['walker']['num_rating'] = $requests->provider_rate_count;
                        $data['walker']['car_model'] = $requests->provider_car_model;
                        $data['walker']['car_number'] = $requests->provider_car_number;
                        $data['walker']['bearing'] = $requests->bearing;
                        array_push($request_data, $data);
                    }

                    if (!empty($request_data)) {
                        $response_array = array(
                            'success' => true,
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'requests' => $request_data,
                            'error_code' => 502,
                            /* 'error' => 'Searching for ' . $driver->keyword . 's.', */
                            'error_messages' => array(76),
                            'error' => 76,
                        );
                    } else {
                        $response_array = array(
                            'success' => false,
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'requests' => $request_data,
                            'error_code' => 584,
                            /* 'error' => 'Searching for ' . $driver->keyword . 's.', */
                            'error_messages' => array(77),
                            'error' => 77,
                        );
                    }
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_request() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $walker_data = "";
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    /* SEND REFERRAL & PROMO INFO */
                    $settings = Settings::where('key', 'referral_code_activation')->first();
                    $referral_code_activation = $settings->value;
                    if ($referral_code_activation) {
                        $referral_code_activation_txt = "referral on";
                    } else {
                        $referral_code_activation_txt = "referral off";
                    }

                    $settings = Settings::where('key', 'promotional_code_activation')->first();
                    $promotional_code_activation = $settings->value;
                    if ($promotional_code_activation) {
                        $promotional_code_activation_txt = "promo on";
                    } else {
                        $promotional_code_activation_txt = "promo off";
                    }
                    /* SEND REFERRAL & PROMO INFO */
                    // Do necessary operations
                    if ($request =RideRequest::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {
                            if ($request->current_walker != 0) {

                                if ($request->confirmed_walker != 0) {
                                    $walker = Walker::where('id', $request->confirmed_walker)->first();
                                    $array = explode(" ", $walker->contact_name, 2);
                                    $first_name = $array[0];
                                    $last_name = $array[1];
                                    $walker_data = array();
                                    $walker_data['unique_id'] = 1;
                                    $walker_data['id'] = $walker->id;
                                    $walker_data['contact_name'] = $walker->contact_name;
                                    $walker_data['first_name'] = $first_name;
                                    $walker_data['last_name'] = $last_name;
                                    $walker_data['phone'] = $walker->phone;
                                    $walker_data['bio'] = $walker->bio;
                                    $walker_data['picture'] = $walker->picture;
                                    $walker_data['latitude'] = $walker->latitude;
                                    $walker_data['longitude'] = $walker->longitude;
                                    if ($request->D_latitude != NULL) {
                                        $walker_data['d_latitude'] = $request->D_latitude;
                                        $walker_data['d_longitude'] = $request->D_longitude;
                                    }
                                    $walker_data['type'] = $walker->type;
                                    $walker_data['rating'] = $walker->rate;
                                    $walker_data['num_rating'] = $walker->rate_count;
                                    $walker_data['car_model'] = $walker->car_model;
                                    $walker_data['car_number'] = $walker->car_number;
                                    $walker_data['bearing'] = $walker->bearing;
                                    $walker_data['eta'] = $request->estimated_time;
                                    /* $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ? : 0;
                                      $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count(); */

                                    $settings = Settings::where('key', 'default_distance_unit')->first();
                                    $unit = $settings->value;
                                    if ($unit == 0) {
                                        $unit_set = 'kms';
                                    } elseif ($unit == 1) {
                                        $unit_set = 'miles';
                                    }
                                    $requestserv = RequestServices::where('request_id', $request->id)->first();
                                    $bill = array();
                                    $request_typ = ProviderType::where('id', '=', $requestserv->type)->first();
                                    /* $currency_selected = Keywords::find(5);
                                      $cur_symb = $currency_selected->keyword; */
                                    $cur_symb = Config::get('app.generic_keywords.Currency');

                                    $pt_new = ProviderType::where('id', $walker->type)->first();

                                    $ps_new = ProviderServices::where('id', $walker->type)->first();

                                    if ($request->is_completed == 1) {
                                        $bill['unit'] = $unit_set;
                                        $bill['payment_mode'] = $request->payment_mode;
                                        $bill['distance'] = (string) $request->distance;
                                        $bill['time'] = $request->time;

                                        if ($requestserv->base_price != 0) {
                                            $bill['base_distance'] = $request_typ->base_distance;
                                            $bill['base_price'] = currency_converted($requestserv->base_price);
                                            $bill['distance_cost'] = currency_converted($requestserv->distance_cost);
                                            $bill['time_cost'] = currency_converted($requestserv->time_cost);
                                        } else {
                                            /* $setbase_price = Settings::where('key', 'base_price')->first();
                                              $bill['base_price'] = currency_converted($setbase_price->value);
                                              $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                                              $bill['distance_cost'] = currency_converted($setdistance_price->value);
                                              $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                              $bill['time_cost'] = currency_converted($settime_price->value); */
                                            $bill['base_distance'] = $request_typ->base_distance;
                                            $bill['base_price'] = currency_converted($request_typ->base_price);
                                            $bill['distance_cost'] = currency_converted($request_typ->price_per_unit_distance);
                                            $bill['time_cost'] = currency_converted($request_typ->price_per_unit_time);
                                        }

                                        //
                                        if ($pt_new->base_price != 0) {

                                            $bill['price_per_unit_distance'] = currency_converted($pt_new->price_per_unit_distance);
                                            $bill['price_per_unit_time'] = currency_converted($pt_new->price_per_unit_time);
                                        } else {

                                            $bill['price_per_unit_distance'] = currency_converted($ps_new->price_per_unit_distance);

                                            $bill['price_per_unit_time'] = currency_converted($ps_new->price_per_unit_time);
                                        }
                                        //

                                        if ($request->payment_mode == 2) {
                                            $bill['walker']['email'] = $walker->email;
                                            $bill['walker']['amount'] = currency_converted($request->transfer_amount);
                                            $admins = Admin::first();
                                            $bill['admin']['email'] = $admins->username;
                                            $bill['admin']['amount'] = currency_converted($request->total - $request->transfer_amount);
                                        }
                                        $bill['currency'] = $cur_symb;
                                        /* $bill['total'] = currency_converted($request->total); */
                                        $bill['main_total'] = currency_converted($request->total);
                                        $tot = currency_converted($request->total - $request->ledger_payment - $request->promo_payment);
                                        if ($tot <= 0) {
                                            $tot = 0;
                                        }
                                        $bill['total'] = $tot;
                                        $bill['referral_bonus'] = currency_converted($request->ledger_payment);
                                        $bill['promo_bonus'] = currency_converted($request->promo_payment);
                                        $bill['payment_type'] = $request->payment_mode;
                                        $bill['is_paid'] = $request->is_paid;
                                        $discount = 0;
                                        if ($request->promo_code != "") {
                                            if ($request->promo_code != "") {
                                                $promo_code = PromoCodes::where('id', $request->promo_code)->first();
                                                if ($promo_code) {
                                                    $promo_value = $promo_code->value;
                                                    $promo_type = $promo_code->type;
                                                    if ($promo_type == 1) {
                                                        // Percent Discount
                                                        $discount = $request->total * $promo_value / 100;
                                                    } elseif ($promo_type == 2) {
                                                        // Absolute Discount
                                                        $discount = $promo_value;
                                                    }
                                                }
                                            }
                                        }
                                        $bill['promo_discount'] = currency_converted($discount);
                                        $bill['actual_total'] = currency_converted($request->total + $request->ledger_payment + $discount);
                                    }
                                    $cards = "";
                                    /* $cards['none'] = ""; */
                                    $dif_card = 0;
                                    $cardlist = Payment::where('owner_id', $owner_id)->where('is_default', 1)->first();
                                    /* $cardlist = Payment::where('id', $owner_data->default_card_id)->first(); */

                                    if (count($cardlist) >= 1) {
                                        $cards = array();
                                        $default = $cardlist->is_default;
                                        if ($default == 1) {
                                            $dif_card = $cardlist->id;
                                            $cards['is_default_text'] = "default";
                                        } else {
                                            $cards['is_default_text'] = "not_default";
                                        }
                                        $cards['card_id'] = $cardlist->id;
                                        $cards['owner_id'] = $cardlist->owner_id;
                                        $cards['customer_id'] = $cardlist->customer_id;
                                        $cards['last_four'] = $cardlist->last_four;
                                        $cards['card_token'] = $cardlist->card_token;
                                        $cards['card_type'] = $cardlist->card_type;
                                        $cards['is_default'] = $default;
                                    }

                                    $code_data = Ledger::where('owner_id', '=', $owner_data->id)->first();
                                    $owner = array();
                                    $owner['owner_lat'] = $request->latitude;
                                    $owner['owner_long'] = $request->longitude;
                                    $owner['owner_dist_lat'] = $request->D_latitude;
                                    $owner['owner_dist_long'] = $request->D_longitude;
                                    $owner['payment_type'] = $request->payment_mode;

                                    $owner['default_card'] = $dif_card;
                                    $owner['dest_latitude'] = $request->D_latitude;
                                    $owner['dest_longitude'] = $request->D_longitude;
                                    $owner['referral_code'] = $code_data->referral_code;
                                    $owner['is_referee'] = $owner_data->is_referee;
                                    $owner['promo_count'] = $owner_data->promo_count;



                                    $charge = array();

                                    $settings = Settings::where('key', 'default_distance_unit')->first();
                                    $unit = $settings->value;
                                    if ($unit == 0) {
                                        $unit_set = 'kms';
                                    } elseif ($unit == 1) {
                                        $unit_set = 'miles';
                                    }
                                    $charge['unit'] = $unit_set;


                                    if ($requestserv->base_price != 0) {
                                        $charge['base_distance'] = $request_typ->base_distance;
                                        $charge['base_price'] = currency_converted($requestserv->base_price);
                                        $charge['distance_price'] = currency_converted($requestserv->distance_cost);
                                        $charge['price_per_unit_time'] = currency_converted($requestserv->time_cost);
                                    } else {
                                        /* $setbase_price = Settings::where('key', 'base_price')->first();
                                          $charge['base_price'] = currency_converted($setbase_price->value);
                                          $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                                          $charge['distance_price'] = currency_converted($setdistance_price->value);
                                          $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                          $charge['price_per_unit_time'] = currency_converted($settime_price->value); */
                                        $charge['base_distance'] = $request_typ->base_distance;
                                        $charge['base_price'] = currency_converted($request_typ->base_price);
                                        $charge['distance_price'] = currency_converted($request_typ->price_per_unit_distance);
                                        $charge['price_per_unit_time'] = currency_converted($request_typ->price_per_unit_time);
                                    }
                                    $charge['total'] = currency_converted($request->total);
                                    $charge['is_paid'] = $request->is_paid;

                                    $loc1 = WalkLocation::where('request_id', $request->id)->first();
                                    $loc2 = WalkLocation::where('request_id', $request->id)->orderBy('id', 'desc')->first();
                                    if ($loc1) {
                                        $time1 = strtotime($loc2->created_at);
                                        $time2 = strtotime($loc1->created_at);
                                        $difference = intval(($time1 - $time2) / 60);
                                    } else {
                                        $difference = 0;
                                    }
                                    $difference = $request->time;


                                    $rserv = RequestServices::where('request_id', $request_id)->get();
                                    $typs = array();
                                    $typi = array();
                                    $typp = array();
                                    $total_price = 0;
                                    foreach ($rserv as $typ) {
                                        $typ1 = ProviderType::where('id', $typ->type)->first();
                                        $typ_price = ProviderServices::where('type', $typ->type)->first();

                                        if ($typ_price->base_price > 0) {
                                            $typp1 = 0.00;
                                            $typp1 = $typ_price->base_price;
                                        } elseif ($typ_price->price_per_unit_distance > 0) {
                                            $typp1 = 0.00;
                                            foreach ($rserv as $key) {
                                                $typp1 = $typp1 + $key->distance_cost;
                                            }
                                        } else {
                                            $typp1 = 0.00;
                                        }
                                        $typs['name'] = $typ1->name;
                                        $typs['price'] = currency_converted($typ1->base_price);
                                        $total_price = $total_price + $typp1;
                                        array_push($typi, $typs);
                                    }
                                    $bill['type'] = $typi;

                                    $response_array = array(
                                        'success' => true,
                                        'unique_id' => 1,
                                        'status' => $request->status,
                                        'is_referral_active' => $referral_code_activation,
                                        'is_referral_active_txt' => $referral_code_activation_txt,
                                        'is_promo_active' => $promotional_code_activation,
                                        'is_promo_active_txt' => $promotional_code_activation_txt,
                                        'confirmed_walker' => $request->confirmed_walker,
                                        'is_walker_started' => $request->is_walker_started,
                                        'is_walker_arrived' => $request->is_walker_arrived,
                                        'is_walk_started' => $request->is_started,
                                        'is_completed' => $request->is_completed,
                                        'is_walker_rated' => $request->is_walker_rated,
                                        'is_cancelled' => $request->is_cancelled,
                                        'dest_latitude' => $request->D_latitude,
                                        'dest_longitude' => $request->D_longitude,
                                        'promo_id' => $request->promo_id,
                                        'promo_code' => $request->promo_code,
                                        'walker' => $walker_data,
                                        'time' => $difference,
                                        'bill' => $bill,
                                        'owner' => $owner,
                                        'card_details' => $cards,
                                        'charge_details' => $charge,
                                        'eta'=> $request->estimated_time
                                    );

                                    $user_timezone = $walker->timezone;
                                    $default_timezone = Config::get('app.timezone');

                                    $accepted_time = get_user_time($default_timezone, $user_timezone, $request->request_start_time);

                                    $time = DB::table('walk_location')
                                            ->where('request_id', $request_id)
                                            ->min('created_at');

                                    $end_time = get_user_time($default_timezone, $user_timezone, $time);

                                    $response_array['accepted_time'] = $accepted_time;
                                    if ($request->is_started == 1) {
                                        $response_array['start_time'] = DB::table('walk_location')
                                                ->where('request_id', $request_id)
                                                ->min('created_at');

                                        $settings = Settings::where('key', 'default_distance_unit')->first();
                                        $unit = $settings->value;

                                        $response_array['distance'] = DB::table('walk_location')
                                                ->where('request_id', $request_id)
                                                ->max('distance');

                                        $response_array['distance'] = (string) convert($response_array['distance'], $unit);
                                        if ($unit == 0) {
                                            $unit_set = 'kms';
                                        } elseif ($unit == 1) {
                                            $unit_set = 'miles';
                                        }
                                        $response_array['unit'] = $unit_set;
                                    }
                                    if ($request->is_completed == 1) {
                                        $response_array['end_time'] = $end_time;
                                    }
                                } else {
                                    if ($request->current_walker != 0) {
                                        $walker = Walker::find($request->current_walker);
                                        $walker_data = array();
                                        $walker_data['unique_id'] = 1;
                                        $walker_data['id'] = $walker->id;
                                        $walker_data['contact_name'] = $walker->contact_name;
                                        $walker_data['phone'] = $walker->phone;
                                        $walker_data['bio'] = $walker->bio;
                                        $walker_data['picture'] = $walker->picture;
                                        $walker_data['latitude'] = $walker->latitude;
                                        $walker_data['longitude'] = $walker->longitude;
                                        $walker_data['type'] = $walker->type;
                                        $walker_data['car_model'] = $walker->car_model;
                                        $walker_data['car_number'] = $walker->car_number;
                                        $walker_data['bearing'] = $walker->bearing;
                                        // $walker_data['payment_type'] = $request->payment_mode;
                                        $walker_data['rating'] = $walker->rate;
                                        $walker_data['num_rating'] = $walker->rate_count;
                                        $walker_data['eta'] = $request->estimated_time;
                                    }
                                    $cards = "";
                                    /* $cards['none'] = ""; */
                                    $dif_card = 0;
                                    $cardlist = Payment::where('owner_id', $owner_id)->where('is_default', 1)->first();
                                    /* $cardlist = Payment::where('id', $owner_data->default_card_id)->first(); */

                                    if (count($cardlist) >= 1) {
                                        $cards = array();
                                        $default = $cardlist->is_default;
                                        if ($default == 1) {
                                            $dif_card = $cardlist->id;
                                            $cards['is_default_text'] = "default";
                                        } else {
                                            $cards['is_default_text'] = "not_default";
                                        }
                                        $cards['card_id'] = $cardlist->id;
                                        $cards['owner_id'] = $cardlist->owner_id;
                                        $cards['customer_id'] = $cardlist->customer_id;
                                        $cards['last_four'] = $cardlist->last_four;
                                        $cards['card_token'] = $cardlist->card_token;
                                        $cards['card_type'] = $cardlist->card_type;
                                        $cards['is_default'] = $default;
                                    }
                                    $code_data = Ledger::where('owner_id', '=', $owner_data->id)->first();
                                    $owner = array();
                                    $owner['owner_lat'] = $request->latitude;
                                    $owner['owner_long'] = $request->longitude;
                                    $owner['owner_dist_lat'] = $request->D_latitude;
                                    $owner['owner_dist_long'] = $request->D_longitude;
                                    $owner['payment_type'] = $request->payment_mode;
                                    $owner['default_card'] = $dif_card;
                                    $owner['dest_latitude'] = $request->D_latitude;
                                    $owner['dest_longitude'] = $request->D_longitude;
                                    $owner['referral_code'] = $code_data->referral_code;
                                    $owner['is_referee'] = $owner_data->is_referee;
                                    $owner['promo_count'] = $owner_data->promo_count;
                                    /* $driver = Keywords::where('id', 1)->first(); */
                                    $requestserv = RequestServices::where('request_id', $request->id)->first();
                                    $charge = array();
                                    $request_typ = ProviderType::where('id', '=', $requestserv->type)->first();
                                    $settings = Settings::where('key', 'default_distance_unit')->first();
                                    $unit = $settings->value;
                                    if ($unit == 0) {
                                        $unit_set = 'kms';
                                    } elseif ($unit == 1) {
                                        $unit_set = 'miles';
                                    }
                                    $charge['unit'] = $unit_set;
                                    if ($requestserv->base_price != 0) {
                                        $charge['base_distance'] = $request_typ->base_distance;
                                        $charge['base_price'] = currency_converted($requestserv->base_price);
                                        $charge['distance_price'] = currency_converted($requestserv->distance_cost);
                                        $charge['price_per_unit_time'] = currency_converted($requestserv->time_cost);
                                    } else {
                                        /* $setbase_price = Settings::where('key', 'base_price')->first();
                                          $charge['base_price'] = currency_converted($setbase_price->value);
                                          $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                                          $charge['distance_price'] = currency_converted($setdistance_price->value);
                                          $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                          $charge['price_per_unit_time'] = currency_converted($settime_price->value); */
                                        $charge['base_distance'] = $request_typ->base_distance;
                                        $charge['base_price'] = currency_converted($request_typ->base_price);
                                        $charge['distance_price'] = currency_converted($request_typ->price_per_unit_distance);
                                        $charge['price_per_unit_time'] = currency_converted($request_typ->price_per_unit_time);
                                    }
                                    $charge['total'] = currency_converted($request->total);
                                    $charge['is_paid'] = $request->is_paid;
                                    $response_array = array(
                                        'success' => true,
                                        'unique_id' => 1,
                                        'status' => $request->status,
                                        'is_referral_active' => $referral_code_activation,
                                        'is_referral_active_txt' => $referral_code_activation_txt,
                                        'is_promo_active' => $promotional_code_activation,
                                        'is_promo_active_txt' => $promotional_code_activation_txt,
                                        'confirmed_walker' => 0,
                                        'is_walker_started' => $request->is_walker_started,
                                        'is_walker_arrived' => $request->is_walker_arrived,
                                        'is_walk_started' => $request->is_started,
                                        'is_completed' => $request->is_completed,
                                        'is_walker_rated' => $request->is_walker_rated,
                                        'is_cancelled' => $request->is_cancelled,
                                        'dest_latitude' => $request->D_latitude,
                                        'dest_longitude' => $request->D_longitude,
                                        'promo_id' => $request->promo_id,
                                        'promo_code' => $request->promo_code,
                                        'walker' => $walker_data,
                                        'bill' => "",
                                        'owner' => $owner,
                                        'card_details' => $cards,
                                        'charge_details' => $charge,
                                        'confirmed_walker' => 0,
                                        'eta' => $request->estimated_time,
                                        'error_code' => 484,
                                        /* 'error' => 'Searching for ' . $driver->keyword . 's.', */
                                        'error' => 78,
                                        'error_messages' => array(78),
                                    );
                                }
                            } else {
                                /* $driver = Keywords::where('id', 1)->first(); */
                                if ($request->current_walker != 0) {
                                    $walker = Walker::find($request->current_walker);
                                    $walker_data = array();
                                    $walker_data['unique_id'] = 1;
                                    $walker_data['id'] = $walker->id;
                                    $walker_data['contact_name'] = $walker->contact_name;
                                    $walker_data['phone'] = $walker->phone;
                                    $walker_data['bio'] = $walker->bio;
                                    $walker_data['picture'] = $walker->picture;
                                    $walker_data['latitude'] = $walker->latitude;
                                    $walker_data['longitude'] = $walker->longitude;
                                    $walker_data['type'] = $walker->type;
                                    $walker_data['car_model'] = $walker->car_model;
                                    $walker_data['car_number'] = $walker->car_number;
                                    $walker_data['bearing'] = $walker->bearing;
                                    // $walker_data['payment_type'] = $request->payment_mode;
                                    $walker_data['rating'] = $walker->rate;
                                    $walker_data['num_rating'] = $walker->rate_count;
                                    $walker_data['eta'] = $request->estimated_time;
                                }
                                $cards = "";
                                /* $cards['none'] = ""; */
                                $dif_card = 0;
                                $cardlist = Payment::where('owner_id', $owner_id)->where('is_default', 1)->first();
                                /* $cardlist = Payment::where('id', $owner_data->default_card_id)->first(); */

                                if (count($cardlist) >= 1) {
                                    $cards = array();
                                    $default = $cardlist->is_default;
                                    if ($default == 1) {
                                        $dif_card = $cardlist->id;
                                        $cards['is_default_text'] = "default";
                                    } else {
                                        $cards['is_default_text'] = "not_default";
                                    }
                                    $cards['card_id'] = $cardlist->id;
                                    $cards['owner_id'] = $cardlist->owner_id;
                                    $cards['customer_id'] = $cardlist->customer_id;
                                    $cards['last_four'] = $cardlist->last_four;
                                    $cards['card_token'] = $cardlist->card_token;
                                    $cards['card_type'] = $cardlist->card_type;
                                    $cards['is_default'] = $default;
                                }
                                $code_data = Ledger::where('owner_id', '=', $owner_data->id)->first();
                                $owner = array();
                                $owner['owner_lat'] = $request->latitude;
                                $owner['owner_long'] = $request->longitude;
                                $owner['owner_dist_lat'] = $request->D_latitude;
                                $owner['owner_dist_long'] = $request->D_longitude;
                                $owner['payment_type'] = $request->payment_mode;
                                $owner['default_card'] = $dif_card;
                                $owner['dest_latitude'] = $request->D_latitude;
                                $owner['dest_longitude'] = $request->D_longitude;
                                $owner['referral_code'] = $code_data->referral_code;
                                $owner['is_referee'] = $owner_data->is_referee;
                                $owner['promo_count'] = $owner_data->promo_count;
                                /* $driver = Keywords::where('id', 1)->first(); */
                                $requestserv = RequestServices::where('request_id', $request->id)->first();
                                $charge = array();
                                $request_typ = ProviderType::where('id', '=', $requestserv->type)->first();
                                $settings = Settings::where('key', 'default_distance_unit')->first();
                                $unit = $settings->value;
                                if ($unit == 0) {
                                    $unit_set = 'kms';
                                } elseif ($unit == 1) {
                                    $unit_set = 'miles';
                                }
                                $charge['unit'] = $unit_set;
                                if ($requestserv->base_price != 0) {
                                    $charge['base_distance'] = $request_typ->base_distance;
                                    $charge['base_price'] = currency_converted($requestserv->base_price);
                                    $charge['distance_price'] = currency_converted($requestserv->distance_cost);
                                    $charge['price_per_unit_time'] = currency_converted($requestserv->time_cost);
                                } else {
                                    /* $setbase_price = Settings::where('key', 'base_price')->first();
                                      $charge['base_price'] = currency_converted($setbase_price->value);
                                      $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                                      $charge['distance_price'] = currency_converted($setdistance_price->value);
                                      $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                      $charge['price_per_unit_time'] = currency_converted($settime_price->value); */
                                    $charge['base_distance'] = $request_typ->base_distance;
                                    $charge['base_price'] = currency_converted($request_typ->base_price);
                                    $charge['distance_price'] = currency_converted($request_typ->price_per_unit_distance);
                                    $charge['price_per_unit_time'] = currency_converted($request_typ->price_per_unit_time);
                                }
                                $charge['total'] = currency_converted($request->total);
                                $charge['is_paid'] = $request->is_paid;
                                $response_array = array(
                                    'success' => true,
                                    'unique_id' => 1,
                                    'status' => $request->status,
                                    'is_referral_active' => $referral_code_activation,
                                    'is_referral_active_txt' => $referral_code_activation_txt,
                                    'is_promo_active' => $promotional_code_activation,
                                    'is_promo_active_txt' => $promotional_code_activation_txt,
                                    'confirmed_walker' => 0,
                                    'is_walker_started' => $request->is_walker_started,
                                    'is_walker_arrived' => $request->is_walker_arrived,
                                    'is_walk_started' => $request->is_started,
                                    'is_completed' => $request->is_completed,
                                    'is_walker_rated' => $request->is_walker_rated,
                                    'is_cancelled' => $request->is_cancelled,
                                    'dest_latitude' => $request->D_latitude,
                                    'dest_longitude' => $request->D_longitude,
                                    'promo_id' => $request->promo_id,
                                    'promo_code' => $request->promo_code,
                                    'walker' => $walker_data,
                                    'bill' => "",
                                    'owner' => $owner,
                                    'card_details' => $cards,
                                    'charge_details' => $charge,
                                    'current_walker' => 0,
                                    'eta'=> $request->estimated_time,
                                    'error_code' => 483,
                                    /* 'error' => 'No ' . $driver->keyword . 's are available currently. Please try after sometime.', */
                                    'error' => 79,
                                    'error_messages' => array(79),
                                );
                            }
                            $response_code = 200;
                        } else {
                            /* $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with ' . $var->keyword . ' ID','error_messages' => array('Request ID doesnot matches with ' . $var->keyword . ' ID') . 's are available currently. Please try after sometime.'), 'error_code' => 407); */
                            $response_array = array('success' => false, 'error' => 75, 'error_messages' => array(75), 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // Get Request Status


    public function get_request_location() {

        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request =RideRequest::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {

                            if ($request->confirmed_walker != 0) {
                                if ($request->is_started == 0) {
                                    $walker = Walker::find($request->confirmed_walker);
                                    $distance = 0;
                                } else {
                                    $walker = WalkLocation::where('request_id', $request->id)->orderBy('created_at', 'desc')->first();
                                    $distance = WalkLocation::where('request_id', $request->id)->max('distance');
                                }

                                $settings = Settings::where('key', 'default_distance_unit')->first();
                                $unit = $settings->value;
                                if ($unit == 0) {
                                    $unit_set = 'kms';
                                } elseif ($unit == 1) {
                                    $unit_set = 'miles';
                                }
                                $distance = convert($distance, $unit);

                                $loc1 = WalkLocation::where('request_id', $request->id)->first();
                                $loc2 = WalkLocation::where('request_id', $request->id)->orderBy('id', 'desc')->first();
                                if ($loc1) {
                                    $time1 = strtotime($loc2->created_at);
                                    $time2 = strtotime($loc1->created_at);
                                    $difference = intval(($time1 - $time2) / 60);
                                } else {
                                    $difference = 0;
                                }
                                $difference = $request->time;

                                $response_array = array(
                                    'success' => true,
                                    'latitude' => $walker->latitude,
                                    'longitude' => $walker->longitude,
                                    'bearing' => $walker->bearing,
                                    'distance' => (string) $distance,
                                    'time' => $difference,
                                    'unit' => $unit_set
                                );
                            } else {
                                $response_array = array(
                                    'success' => false,
                                    'error' => 80,
                                    'error_messages' => array(80),
                                    'error_code' => 421,
                                );
                            }
                            $response_code = 200;
                        } else {
                            /* $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with ' . $var->keyword . ' ID','error_messages' => array('Request ID doesnot matches with ' . $var->keyword . ' ID'), 'error_code' => 407); */
                            $response_array = array('success' => false, 'error' => 75, 'error_messages' => array(75), 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // check status and Send Request to walker
    // if request not timed out do nothing
    // else send new request
    // if user accepted change stat of request

    public function schedule_request() {
        /* Cronjob counter */
        /* echo asset_url() . "/cron_count.txt"; */
        $css_msg = file(asset_url() . "/cron_count.txt");
        if ($css_msg[0] > '100') {
            $css_msg[0] = 0;
        } else {
            $css_msg[0] ++;
        }
        /* echo $css_msg[0]; */
        $t = file_put_contents(public_path() . '/cron_count.txt', $css_msg[0]);
        $css_msg[0];
        /* Cronjob counter END */

        $time = date("Y-m-d H:i:s");
        $timezone_app = Config::get('app.timezone');
        date_default_timezone_set($timezone_app);
        $timezone_sys = date_default_timezone_get();

        $query = "SELECT request.*,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where status = 0 and is_cancelled = 0";
        $results = DB::select(DB::raw($query));

        /* SEND REFERRAL & PROMO INFO */
        $settings = Settings::where('key', 'referral_code_activation')->first();
        $referral_code_activation = $settings->value;
        if ($referral_code_activation) {
            $referral_code_activation_txt = "referral on";
        } else {
            $referral_code_activation_txt = "referral off";
        }

        $settings = Settings::where('key', 'promotional_code_activation')->first();
        $promotional_code_activation = $settings->value;
        if ($promotional_code_activation) {
            $promotional_code_activation_txt = "promo on";
        } else {
            $promotional_code_activation_txt = "promo off";
        }
        /* SEND REFERRAL & PROMO INFO */
        $driver_data = "";

        foreach ($results as $result) {
            $settings = Settings::where('key', 'provider_timeout')->first();
            $timeout = $settings->value;
            $settings = Settings::where('key', 'change_provider_tolerance')->first();
            $timeout = $timeout + $settings->value;
            if ($result->diff >= $timeout) {
                // Archiving Old Walker
                RequestMeta::where('request_id', '=', $result->id)->where('walker_id', '=', $result->current_walker)->update(array('status' => 2));
                $request = RideRequest::where('id', $result->id)->first();
                $request_meta = RequestMeta::where('request_id', '=', $result->id)->where('status', '=', 0)->orderBy('created_at')->first();
                // update request
                if (isset($request_meta->walker_id)) {
                    // assign new walker
                  RideRequest::where('id', '=', $result->id)->update(array('current_walker' => $request_meta->walker_id, 'request_start_time' => date("Y-m-d H:i:s")));

                    // Send Notification

                    $walker = Walker::find($request_meta->walker_id);
                    $settings = Settings::where('key', 'provider_timeout')->first();
                    $time_left = $settings->value;

                    $owner = Owner::find($result->owner_id);

                    $msg_array = array();
                    $msg_array['unique_id'] = 1;
                    $msg_array['request_id'] = $request->id;
                    $msg_array['time_left_to_respond'] = $time_left;

                    $msg_array['payment_mode'] = $request->payment_mode;
                    $msg_array['client_profile'] = array();
                    $msg_array['client_profile']['name'] = $owner->contact_name;
                    $msg_array['client_profile']['picture'] = $owner->picture;
                    $msg_array['client_profile']['bio'] = $owner->bio;
                    $msg_array['client_profile']['address'] = $owner->address;
                    $msg_array['client_profile']['phone'] = $owner->phone;

                    $owner = Owner::find($result->owner_id);
                    $request_data = array();
                    $request_data['owner'] = array();
                    $request_data['owner']['name'] = $owner->contact_name;
                    $request_data['owner']['picture'] = $owner->picture;
                    $request_data['owner']['phone'] = $owner->phone;
                    $request_data['owner']['address'] = $owner->address;
                    $request_data['owner']['latitude'] = $request->latitude;
                    $request_data['owner']['longitude'] = $request->longitude;
                    if ($request->d_latitude != NULL) {
                        $request_data['owner']['d_latitude'] = $request->D_latitude;
                        $request_data['owner']['d_longitude'] = $request->D_longitude;
                    }
                    $request_data['owner']['rating'] = $owner->rate;
                    $request_data['owner']['num_rating'] = $owner->rate_count;
                    /* $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
                      $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count(); */
                    $msg_array['request_data'] = $request_data;

                    $title = "New Request";

                    $message = $msg_array;
                    //Log::info('New Request = ' . print_r($message, true));
                    send_notifications($request_meta->walker_id, "walker", $title, $message);
                    $driver_data = array();
                    $driver_data['unique_id'] = 1;
                    $driver_data['id'] = "" . $walker->id;
                    $driver_data['contact_name'] = "" . $walker->contact_name;
                    $driver_data['phone'] = "" . $walker->phone;
                    /*  $driver_data['email'] = "" . $walker->email; */
                    $driver_data['picture'] = "" . $walker->picture;
                    $driver_data['bio'] = "" . $walker->bio;
                    /* $driver_data['address'] = "" . $walker->address;
                      $driver_data['state'] = "" . $walker->state;
                      $driver_data['country'] = "" . $walker->country;
                      $driver_data['zipcode'] = "" . $walker->zipcode;
                      $driver_data['login_by'] = "" . $walker->login_by;
                      $driver_data['social_unique_id'] = "" . $walker->social_unique_id;
                      $driver_data['is_active'] = "" . $walker->is_active;
                      $driver_data['is_available'] = "" . $walker->is_available; */
                    $driver_data['latitude'] = "" . $walker->latitude;
                    $driver_data['longitude'] = "" . $walker->longitude;
                    /* $driver_data['is_approved'] = "" . $walker->is_approved; */
                    $driver_data['type'] = "" . $walker->type;
                    $driver_data['car_model'] = "" . $walker->car_model;
                    $driver_data['car_number'] = "" . $walker->car_number;
                    $driver_data['rating'] = $walker->rate;
                    $driver_data['num_rating'] = $walker->rate_count;
                    /* $driver_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ? : 0;
                      $driver_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count(); */
                    $client_push_data = array(
                        'success' => true,
                        'unique_id' => 1,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'request_id' => $result->id,
                        'walker' => $driver_data,
                    );
                    $message1 = $client_push_data;
                    $owner_data = Owner::find($result->owner_id);
                    $title1 = "New " . Config::get('app.generic_keywords.Provider') . " assigned";
                    send_notifications($owner_data->id, "owner", $title1, $message1);
                } else {
                    $owner = Owner::find($result->owner_id);
                    /* CLIENT PUSH FOR GETTING DRIVER DETAILS */
                    $client_push_data = array(
                        'success' => false,
                        'unique_id' => 1,
                        'error' => 81,
                        'error_messages' => array(81),
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'request_id' => $result->id,
                        'error_code' => 411,
                        'walker' => $driver_data,
                    );
                    $message1 = $client_push_data;
                    $owner_data = Owner::find($result->owner_id);
                    $title1 = "No " . Config::get('app.generic_keywords.Provider') . " Found.";
                    /* if ($owner_data->is_deleted == 0) { */
                    send_notifications($owner_data->id, "owner", $title1, $message1);
                    /* } */
                    /* CLIENT PUSH FOR GETTING DRIVER DETAILS END */
                    // request ended
                    if ($result->promo_id) {
                        $promo_update_counter = PromoCodes::find($result->promo_id);
                        $promo_update_counter->uses = $promo_update_counter->uses + 1;
                        $promo_update_counter->save();

                        UserPromoUse::where('user_id', '=', $result->owner_id)->where('code_id', '=', $result->promo_id)->delete();

                        $owner = Owner::find($result->owner_id);
                        $owner->promo_count = $owner->promo_count - 1;
                        $owner->save();

                        $request =RideRequest::find($result->id);
                        $request->promo_id = 0;
                        $request->promo_code = "";
                        $request->save();
                    }
                  RideRequest::where('id', '=', $result->id)->update(array('current_walker' => 0, 'status' => 1, 'is_cancelled' => 1));



                    /* $driver = Keywords::where('id', 1)->first(); */
                    $owne = Owner::where('id', $result->owner_id)->first();
                    /* $driver_keyword = $driver->keyword; */
                    $driver_keyword = Config::get('app.generic_keywords.Provider');
                    $owner_data_id = $owne->id;
                    send_notifications($owner_data_id, "owner", 'No ' . $driver_keyword . ' Found', 'No ' . $driver_keyword . ' are available right now in your area. Kindly try after sometime.');

                    $owner = Owner::find($result->owner_id);

                    $settings = Settings::where('key', 'sms_request_unanswered')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%id%', $result->id, $pattern);
                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);

                    // send email
                    /* $settings = Settings::where('key', 'email_request_unanswered')->first();
                      $pattern = $settings->value;
                      $pattern = str_replace('%id%', $result->id, $pattern);
                      $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $result->id, $pattern);
                      $subject = "New Request Unanswered";
                      email_notification(1, 'admin', $pattern, $subject); */
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $follow_url = web_url() . "/booking/signin";
                    $pattern = array('admin_email' => $admin_email);
                    $subject = "New Request Unansweres";
                    email_notification(1, 'admin', $pattern, $subject, 'request_not_answered', null);
                }
            }
        }
        $walker_data = Walker::where('password', '=', "")->get();
        if ($walker_data) {
            foreach ($walker_data as $walker_info) {
                $password = my_random6_number();
                $pattern = "Hello... ! " . ucwords($walker_info->contact_name) . " . Your " . Config::get('app.website_title') . " Web Login ID is : " . $walker_info->email . " and Password is : " . $password;
                sms_notification($walker_info->id, 'walker', $pattern);
                $subject = "Your " . Config::get('app.website_title') . " Web Login information";
                email_notification($walker_info->id, 'walker', $pattern, $subject);
                Walker::where('id', $walker_info->id)->update(array('password' => Hash::make($password)));
            }
        }
        $owner_data = Owner::where('password', '=', "")->get();
        if ($owner_data) {
            foreach ($owner_data as $owner) {
                $password = my_random6_number();
                $pattern = "Hello... ! " . ucwords($owner->contact_name) . " . Your " . Config::get('app.website_title') . " Web Login ID is : " . $owner->email . " and Password is : " . $password;
                sms_notification($owner->id, 'owner', $pattern);
                $subject = "Your " . Config::get('app.website_title') . " Web Login information";
                email_notification($owner->id, 'owner', $pattern, $subject);
                Owner::where('id', $owner->id)->update(array('password' => Hash::make($password)));
            }
        }
    }

    public function schedule_future_request() {
        /* Cronjob counter */
        /* echo asset_url() . "/cron_count.txt"; */
        $css_msg = file(asset_url() . "/cron_count_2.txt");
        if ($css_msg[0] > '100') {
            $css_msg[0] = '0';
        } else {
            $css_msg[0] ++;
        }
        /* echo $css_msg[0]; */
        $t = file_put_contents(public_path() . '/cron_count_2.txt', $css_msg[0]);
        $css_msg[0];
        /* Cronjob counter END */
        /* SEND REFERRAL & PROMO INFO */
        $settings = Settings::where('key', 'referral_code_activation')->first();
        $referral_code_activation = $settings->value;
        if ($referral_code_activation) {
            $referral_code_activation_txt = "referral on";
        } else {
            $referral_code_activation_txt = "referral off";
        }
        $settings = Settings::where('key', 'promotional_code_activation')->first();
        $promotional_code_activation = $settings->value;
        if ($promotional_code_activation) {
            $promotional_code_activation_txt = "promo on";
        } else {
            $promotional_code_activation_txt = "promo off";
        }
        /* SEND REFERRAL & PROMO INFO */
        $time = date("Y-m-d H:i:s");
        $timezone_app = Config::get('app.timezone');
        date_default_timezone_set($timezone_app);
        $timezone_sys = date_default_timezone_get();

        /* $query = "SELECT scheduled_requests.*,TIMESTAMPDIFF(SECOND,server_start_time, '$time') as diff from scheduled_requests";
          $results = DB::select(DB::raw($query)); */
        $settings = Settings::where('key', 'scheduled_request_pre_start_minutes')->first();
        $pre_request_time = $settings->value;
        $now = date("Y-m-d H:i:s", strtotime("now"));
        $now_30 = date("Y-m-d H:i:s", strtotime("+" . $pre_request_time . " minutes"));

        $settings = Settings::where('key', 'number_of_try_for_scheduled_requests')->first();
        $total_retry = $settings->value;
        $auto_cancled_results = ScheduledRequest::where('retryflag', '>=', $total_retry)->get();
        foreach ($auto_cancled_results as $remove_schedules) {
            $driver_data = "";
            $owner = Owner::find($remove_schedules->owner_id);
            /* CLIENT PUSH FOR GETTING DRIVER DETAILS */
            $client_push_data = array(
                'success' => false,
                'unique_id' => 1,
                'error' => 'No ' . Config::get('app.generic_keywords.Provider') . ' found around you.',
                'error_messages' => array('No ' . Config::get('app.generic_keywords.Provider') . ' found around you.'),
                'is_referral_active' => $referral_code_activation,
                'is_referral_active_txt' => $referral_code_activation_txt,
                'is_promo_active' => $promotional_code_activation,
                'is_promo_active_txt' => $promotional_code_activation_txt,
                'request_id' => $remove_schedules->id,
                'error_code' => 411,
                'walker' => $driver_data,
            );
            $message1 = $client_push_data;
            // $owner_data = Owner::find($remove_schedules->owner_id);

            $owner_data = DB::table('owner')->where('id', $remove_schedules->owner_id)->first();

            //print_r( $owner_data); die();
            //echo $remove_schedules->owner_id;die();
            $driver_keyword = Config::get('app.generic_keywords.Provider');
            $owner_data_id = $owner_data->id;
            //echo $owner_data_id; die();
            $title1 = 'No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled.';
            /* if ($owner_data->is_deleted == 0) { */
            /* $driver_keyword = $driver->keyword; */

            send_notifications($owner_data->id, "owner", $title1, $message1);

            /* SMS NOTIFICATION */
            sms_notification($owner_data->id, 'owner', 'Hello...!, ' . $owner_data->contact_name . ' your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled because no ' . $driver_keyword . ' are available right now in your area.');

            /* EMAIL NOTIFICATION */
            $subject = 'No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled.';
            $pattern = 'Hello...!, ' . $owner_data->contact_name . '<br/>No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' with the schedule date time : ' . $remove_schedules->start_time . ' was auto canceled.';
            email_notification($owner_data->id, 'owner', $pattern, $subject, null, 'imp');
            /* } */
            /* CLIENT PUSH FOR GETTING DRIVER DETAILS END */
            // request ended
            if ($remove_schedules->promo_id) {
                $promo_update_counter = PromoCodes::find($remove_schedules->promo_id);
                $promo_update_counter->uses = $promo_update_counter->uses + 1;
                $promo_update_counter->save();

                UserPromoUse::where('user_id', '=', $remove_schedules->owner_id)->where('code_id', '=', $remove_schedules->promo_id)->delete();

                $owner = Owner::find($remove_schedules->owner_id);
                $owner->promo_count = $owner->promo_count - 1;
                $owner->save();
            }
            /* $driver = Keywords::where('id', 1)->first(); */

            /* $owner = Owner::find($remove_schedules->owner_id);

              $settings = Settings::where('key', 'sms_request_unanswered')->first();
              $pattern = $settings->value;
              $pattern = str_replace('%id%', $remove_schedules->id, $pattern);
              $pattern = str_replace('%user%', $owner->contact_name, $pattern);
              $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
              sms_notification(1, 'admin', $pattern); */

            // send email
            /* $settings = Settings::where('key', 'email_request_unanswered')->first();
              $pattern = $settings->value;
              $pattern = str_replace('%id%', $remove_schedules->id, $pattern);
              $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $remove_schedules->id, $pattern);
              $subject = "New Request Unanswered";
              email_notification(1, 'admin', $pattern, $subject); */
            /* $settings = Settings::where('key', 'admin_email_address')->first();
              $admin_email = $settings->value;
              $follow_url = web_url() . "/booking/signin";
              $pattern = array('admin_email' => $admin_email);
              $subject = "New Request Unansweres";
              email_notification(1, 'admin', $pattern, $subject, 'request_not_answered', null); */
            ScheduledRequest::where('id', '=', $remove_schedules->id)->delete();
        }


        $results = ScheduledRequest::where('server_start_time', '<=', $now_30)->where('retryflag', '<', $total_retry)->get();
        $details = array();
        foreach ($results as $schedules) {
            $details[] = $schedules;

            $owner_id = $schedules->owner_id;
            $latitude = $schedules->latitude;
            $longitude = $schedules->longitude;
            $d_latitude = $schedules->dest_latitude;
            $d_longitude = $schedules->dest_longitude;
            $payment_opt = $schedules->payment_mode;
            $time_zone = $schedules->time_zone;
            $src_address = $schedules->src_address;
            $dest_address = $schedules->dest_address;
            $usr_strt_time = $schedules->start_time;
            $unit = "";
            $driver_data = "";
            $type = $schedules->type;
            if (!$type) {
                // choose default type
                $provider_type = ProviderType::where('is_default', 1)->first();

                if (!$provider_type) {
                    $type = 1;
                } else {
                    $type = $provider_type->id;
                }
            }

            $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
            $typewalkers = DB::select(DB::raw($typequery));

            if (count($typewalkers) > 0) {
                foreach ($typewalkers as $key) {

                    $types[] = $key->provider_id;
                }

                $typestring = implode(",", $types);
            } else {
                send_notifications($owner_id, "owner", 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type for scheduled request.', 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type for scheduled request.');
                $trys_for_request = $schedules->retryflag + 1;
                ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
                $response_array = array('success' => false, 'error' => 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type.', 'error_messages' => array('No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type.'), 'error_code' => 416);
                $response_code = 200;
                return Response::json($response_array, $response_code);
            }

            $settings = Settings::where('key', 'default_search_radius')->first();
            $distance = $settings->value;
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            if ($unit == 0) {
                $multiply = 1.609344;
            } elseif ($unit == 1) {
                $multiply = 1;
            }
            $query = "SELECT walker.*, "
                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ,8) as distance "
                    . "FROM walker "
                    . "where is_available = 1 and "
                    . "is_active = 1 and "
                    . "is_approved = 1 and "
                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                    . "walker.deleted_at IS NULL and "
                    . "walker.id IN($typestring) "
                    . "order by distance";
            $walkers = DB::select(DB::raw($query));
            if (!empty($walkers)) {
                $walker_list = array();

                $request = new RideRequest;
                $request->owner_id = $owner_id;
                $request->payment_mode = $payment_opt;

                $request->promo_id = $schedules->promo_id;
                $request->promo_code = $schedules->promo_code;

                $default_timezone = $user_timezone = Config::get('app.timezone');
                $date_time = get_user_time($default_timezone, $user_timezone, date("Y-m-d H:i:s"));
                $request->D_latitude = 0;
                if (isset($d_latitude)) {
                    $request->D_latitude = $schedules->dest_latitude;
                }
                $request->D_longitude = 0;
                if (isset($d_longitude)) {
                    $request->D_longitude = $schedules->dest_longitude;
                }
                $request->request_start_time = $date_time;
                $request->latitude = $latitude;
                $request->longitude = $longitude;
                $request->time_zone = $time_zone;
                $request->src_address = $src_address;
                $request->dest_address = $dest_address;
                $request->req_create_user_time = $usr_strt_time;
                $request->save();

                $reqserv = new RequestServices;
                $reqserv->request_id = $request->id;
                $reqserv->type = $type;
                $reqserv->save();

                $i = 0;
                $first_walker_id = 0;
                foreach ($walkers as $walker) {
                    $request_meta = new RequestMeta;
                    $request_meta->request_id = $request->id;
                    $request_meta->walker_id = $walker->id;
                    if ($i == 0) {
                        $first_walker_id = $walker->id;
                        $driver_data = array();
                        $driver_data['unique_id'] = 1;
                        $driver_data['id'] = "" . $first_walker_id;
                        $driver_data['contact_name'] = "" . $walker->contact_name;
                        $driver_data['phone'] = "" . $walker->phone;
                        /*  $driver_data['email'] = "" . $walker->email; */
                        $driver_data['picture'] = "" . $walker->picture;
                        $driver_data['bio'] = "" . $walker->bio;
                        /* $driver_data['address'] = "" . $walker->address;
                          $driver_data['state'] = "" . $walker->state;
                          $driver_data['country'] = "" . $walker->country;
                          $driver_data['zipcode'] = "" . $walker->zipcode;
                          $driver_data['login_by'] = "" . $walker->login_by;
                          $driver_data['social_unique_id'] = "" . $walker->social_unique_id;
                          $driver_data['is_active'] = "" . $walker->is_active;
                          $driver_data['is_available'] = "" . $walker->is_available; */
                        $driver_data['latitude'] = "" . $walker->latitude;
                        $driver_data['longitude'] = "" . $walker->longitude;
                        /* $driver_data['is_approved'] = "" . $walker->is_approved; */
                        $driver_data['type'] = "" . $walker->type;
                        $driver_data['car_model'] = "" . $walker->car_model;
                        $driver_data['car_number'] = "" . $walker->car_number;
                        $driver_data['rating'] = $walker->rate;
                        $driver_data['num_rating'] = $walker->rate_count;
                        $i++;
                    }
                    $request_meta->save();
                }
                $req =RideRequest::find($request->id);
                $req->current_walker = $first_walker_id;
                $req->save();

                $settings = Settings::where('key', 'provider_timeout')->first();
                $time_left = $settings->value;

                // Send Notification
                $walker = Walker::find($first_walker_id);
                if ($walker) {
                    $msg_array = array();
                    $msg_array['unique_id'] = 1;
                    $msg_array['request_id'] = $request->id;
                    $msg_array['time_left_to_respond'] = $time_left;


                    $msg_array['payment_mode'] = $payment_opt;

                    $owner = Owner::find($owner_id);
                    $request_data = array();
                    $request_data['owner'] = array();
                    $request_data['owner']['name'] = $owner->contact_name;
                    $request_data['owner']['picture'] = $owner->picture;
                    $request_data['owner']['phone'] = $owner->phone;
                    $request_data['owner']['address'] = $owner->address;
                    $request_data['owner']['latitude'] = $request->latitude;
                    $request_data['owner']['longitude'] = $request->longitude;
                    if ($d_latitude != NULL) {
                        $request_data['owner']['d_latitude'] = $d_latitude;
                        $request_data['owner']['d_longitude'] = $d_longitude;
                    }
                    $request_data['owner']['owner_dist_lat'] = $request->D_latitude;
                    $request_data['owner']['owner_dist_long'] = $request->D_longitude;
                    $request_data['owner']['payment_type'] = $payment_opt;
                    $request_data['owner']['rating'] = $owner->rate;
                    $request_data['owner']['num_rating'] = $owner->rate_count;
                    $request_data['dog'] = array();
                    if ($dog = Dog::find($owner->dog_id)) {

                        $request_data['dog']['name'] = $dog->name;
                        $request_data['dog']['age'] = $dog->age;
                        $request_data['dog']['breed'] = $dog->breed;
                        $request_data['dog']['likes'] = $dog->likes;
                        $request_data['dog']['picture'] = $dog->image_url;
                    }
                    $msg_array['request_data'] = $request_data;

                    ScheduledRequest::where('id', '=', $schedules->id)->delete();

                    $title = "New Request";
                    $message = $msg_array;

                    send_notifications($first_walker_id, "walker", $title, $message);

                    $client_push_data = array(
                        'success' => true,
                        'unique_id' => 1,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'request_id' => $request->id,
                        'walker' => $driver_data,
                    );
                    $message = $client_push_data;
                    $title = "Activated the scheduled " . Config::get('app.generic_keywords.Trip') . "";
                    send_notifications($owner->id, "owner", $title, $message);

                    // Send SMS 
                    $settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);

                    // send email
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $follow_url = web_url() . "/booking/signin";
                    $pattern = array('admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url);
                    $subject = "Ride Booking Request";
                    email_notification(1, 'admin', $pattern, $subject, 'new_request', null);
                } else {
                    $trys_for_request = $schedules->retryflag + 1;
                    ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
                }
            } else {
                $trys_for_request = $schedules->retryflag + 1;
                ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
            }
            $response_code = 200;
        }
        $response_array = array(
            'details' => $details,
            'current' => $now,
            'pre_request_time' => $now_30,
            'success' => true,
        );
        $response_code = 200;
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function schedule_future_request_oldddddd() {
        /* Cronjob counter */
        /* echo asset_url() . "/cron_count.txt"; */
        $css_msg = file(asset_url() . "/cron_count_2.txt");
        if ($css_msg[0] > '100') {
            $css_msg[0] = '0';
        } else {
            $css_msg[0] ++;
        }
        /* echo $css_msg[0]; */
        $t = file_put_contents(public_path() . '/cron_count_2.txt', $css_msg[0]);
        $css_msg[0];
        /* Cronjob counter END */
        /* SEND REFERRAL & PROMO INFO */
        $settings = Settings::where('key', 'referral_code_activation')->first();
        $referral_code_activation = $settings->value;
        if ($referral_code_activation) {
            $referral_code_activation_txt = "referral on";
        } else {
            $referral_code_activation_txt = "referral off";
        }
        $settings = Settings::where('key', 'promotional_code_activation')->first();
        $promotional_code_activation = $settings->value;
        if ($promotional_code_activation) {
            $promotional_code_activation_txt = "promo on";
        } else {
            $promotional_code_activation_txt = "promo off";
        }
        /* SEND REFERRAL & PROMO INFO */
        $time = date("Y-m-d H:i:s");
        $timezone_app = Config::get('app.timezone');
        date_default_timezone_set($timezone_app);
        $timezone_sys = date_default_timezone_get();

        /* $query = "SELECT scheduled_requests.*,TIMESTAMPDIFF(SECOND,server_start_time, '$time') as diff from scheduled_requests";
          $results = DB::select(DB::raw($query)); */
        $settings = Settings::where('key', 'scheduled_request_pre_start_minutes')->first();
        $pre_request_time = $settings->value;
        $now = date("Y-m-d H:i:s", strtotime("now"));
        $now_30 = date("Y-m-d H:i:s", strtotime("+" . $pre_request_time . " minutes"));

        $settings = Settings::where('key', 'number_of_try_for_scheduled_requests')->first();
        $total_retry = $settings->value;
        $auto_cancled_results = ScheduledRequest::where('retryflag', '>=', $total_retry)->get();
        foreach ($auto_cancled_results as $remove_schedules) {
            $driver_data = "";
            $owner = Owner::find($remove_schedules->owner_id);
            /* CLIENT PUSH FOR GETTING DRIVER DETAILS */
            $client_push_data = array(
                'success' => false,
                'unique_id' => 1,
                'error' => 81,
                'error_messages' => array(81),
                'is_referral_active' => $referral_code_activation,
                'is_referral_active_txt' => $referral_code_activation_txt,
                'is_promo_active' => $promotional_code_activation,
                'is_promo_active_txt' => $promotional_code_activation_txt,
                'request_id' => $remove_schedules->id,
                'error_code' => 411,
                'walker' => $driver_data,
            );
            $message1 = $client_push_data;
            $owner_data = Owner::find($remove_schedules->owner_id);
            $driver_keyword = Config::get('app.generic_keywords.Provider');
            $owner_data_id = $owner_data->id;
            $title1 = 'No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled.';
            /* if ($owner_data->is_deleted == 0) { */
            /* $driver_keyword = $driver->keyword; */

            send_notifications($owner_data_id, "owner", $title1, $message1);

            /* SMS NOTIFICATION */
            sms_notification($owner_data_id, 'owner', 'Hello...!, ' . $owner_data->contact_name . ' your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled because no ' . $driver_keyword . ' are available right now in your area.');

            /* EMAIL NOTIFICATION */
            $subject = 'No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' was auto canceled.';
            $pattern = 'Hello...!, ' . $owner_data->contact_name . '<br/>No ' . $driver_keyword . ' are available right now in your area. So your scheduled ' . Config::get('app.generic_keywords.Trip') . ' with the schedule date time : ' . $remove_schedules->start_time . ' was auto canceled.';
            email_notification($owner_data_id, 'owner', $pattern, $subject, null, 'imp');
            /* } */
            /* CLIENT PUSH FOR GETTING DRIVER DETAILS END */
            // request ended
            if ($remove_schedules->promo_id) {
                $promo_update_counter = PromoCodes::find($remove_schedules->promo_id);
                $promo_update_counter->uses = $promo_update_counter->uses + 1;
                $promo_update_counter->save();

                UserPromoUse::where('user_id', '=', $remove_schedules->owner_id)->where('code_id', '=', $remove_schedules->promo_id)->delete();

                $owner = Owner::find($remove_schedules->owner_id);
                $owner->promo_count = $owner->promo_count - 1;
                $owner->save();
            }
            /* $driver = Keywords::where('id', 1)->first(); */

            /* $owner = Owner::find($remove_schedules->owner_id);

              $settings = Settings::where('key', 'sms_request_unanswered')->first();
              $pattern = $settings->value;
              $pattern = str_replace('%id%', $remove_schedules->id, $pattern);
              $pattern = str_replace('%user%', $owner->contact_name, $pattern);
              $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
              sms_notification(1, 'admin', $pattern); */

            // send email
            /* $settings = Settings::where('key', 'email_request_unanswered')->first();
              $pattern = $settings->value;
              $pattern = str_replace('%id%', $remove_schedules->id, $pattern);
              $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $remove_schedules->id, $pattern);
              $subject = "New Request Unanswered";
              email_notification(1, 'admin', $pattern, $subject); */
            /* $settings = Settings::where('key', 'admin_email_address')->first();
              $admin_email = $settings->value;
              $follow_url = web_url() . "/booking/signin";
              $pattern = array('admin_email' => $admin_email);
              $subject = "New Request Unansweres";
              email_notification(1, 'admin', $pattern, $subject, 'request_not_answered', null); */
            ScheduledRequest::where('id', '=', $remove_schedules->id)->delete();
        }


        $results = ScheduledRequest::where('server_start_time', '<=', $now_30)->where('retryflag', '<', $total_retry)->get();
        $details = array();
        foreach ($results as $schedules) {
            $details[] = $schedules;

            $owner_id = $schedules->owner_id;
            $latitude = $schedules->latitude;
            $longitude = $schedules->longitude;
            $d_latitude = $schedules->dest_latitude;
            $d_longitude = $schedules->dest_longitude;
            $payment_opt = $schedules->payment_mode;
            $time_zone = $schedules->time_zone;
            $src_address = $schedules->src_address;
            $dest_address = $schedules->dest_address;
            $usr_strt_time = $schedules->start_time;
            $unit = "";
            $driver_data = "";
            $type = $schedules->type;
            if (!$type) {
                // choose default type
                $provider_type = ProviderType::where('is_default', 1)->first();

                if (!$provider_type) {
                    $type = 1;
                } else {
                    $type = $provider_type->id;
                }
            }

            $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
            $typewalkers = DB::select(DB::raw($typequery));

            if (count($typewalkers) > 0) {
                foreach ($typewalkers as $key) {

                    $types[] = $key->provider_id;
                }

                $typestring = implode(",", $types);
            } else {
                send_notifications($owner_id, "owner", 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type for scheduled request.', 'No ' . Config::get('app.generic_keywords.Provider') . ' found matching the service type for scheduled request.');
                $trys_for_request = $schedules->retryflag + 1;
                ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
                $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 416);
                $response_code = 200;
                return Response::json($response_array, $response_code);
            }

            $settings = Settings::where('key', 'default_search_radius')->first();
            $distance = $settings->value;
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            if ($unit == 0) {
                $multiply = 1.609344;
            } elseif ($unit == 1) {
                $multiply = 1;
            }
            $query = "SELECT walker.*, "
                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ,8) as distance "
                    . "FROM walker "
                    . "where is_available = 1 and "
                    . "is_active = 1 and "
                    . "is_approved = 1 and "
                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ) ,8) <= $distance and "
                    . "walker.deleted_at IS NULL and "
                    . "walker.id IN($typestring) "
                    . "order by distance";
            $walkers = DB::select(DB::raw($query));
            if (!empty($walkers)) {
                $walker_list = array();

                $request = new RideRequest;
                $request->owner_id = $owner_id;
                $request->payment_mode = $payment_opt;

                $request->promo_id = $schedules->promo_id;
                $request->promo_code = $schedules->promo_code;

                $default_timezone = $user_timezone = Config::get('app.timezone');
                $date_time = get_user_time($default_timezone, $user_timezone, date("Y-m-d H:i:s"));
                $request->D_latitude = 0;
                if (isset($d_latitude)) {
                    $request->D_latitude = $schedules->dest_latitude;
                }
                $request->D_longitude = 0;
                if (isset($d_longitude)) {
                    $request->D_longitude = $schedules->dest_longitude;
                }
                $request->request_start_time = $date_time;
                $request->latitude = $latitude;
                $request->longitude = $longitude;
                $request->time_zone = $time_zone;
                $request->src_address = $src_address;
                $request->dest_address = $dest_address;
                $request->req_create_user_time = $usr_strt_time;
                $request->save();

                $reqserv = new RequestServices;
                $reqserv->request_id = $request->id;
                $reqserv->type = $type;
                $reqserv->save();

                $i = 0;
                $first_walker_id = 0;
                foreach ($walkers as $walker) {
                    $request_meta = new RequestMeta;
                    $request_meta->request_id = $request->id;
                    $request_meta->walker_id = $walker->id;
                    if ($i == 0) {
                        $first_walker_id = $walker->id;
                        $driver_data = array();
                        $driver_data['unique_id'] = 1;
                        $driver_data['id'] = "" . $first_walker_id;
                        $driver_data['contact_name'] = "" . $walker->contact_name;
                        $driver_data['phone'] = "" . $walker->phone;
                        /*  $driver_data['email'] = "" . $walker->email; */
                        $driver_data['picture'] = "" . $walker->picture;
                        $driver_data['bio'] = "" . $walker->bio;
                        /* $driver_data['address'] = "" . $walker->address;
                          $driver_data['state'] = "" . $walker->state;
                          $driver_data['country'] = "" . $walker->country;
                          $driver_data['zipcode'] = "" . $walker->zipcode;
                          $driver_data['login_by'] = "" . $walker->login_by;
                          $driver_data['social_unique_id'] = "" . $walker->social_unique_id;
                          $driver_data['is_active'] = "" . $walker->is_active;
                          $driver_data['is_available'] = "" . $walker->is_available; */
                        $driver_data['latitude'] = "" . $walker->latitude;
                        $driver_data['longitude'] = "" . $walker->longitude;
                        /* $driver_data['is_approved'] = "" . $walker->is_approved; */
                        $driver_data['type'] = "" . $walker->type;
                        $driver_data['car_model'] = "" . $walker->car_model;
                        $driver_data['car_number'] = "" . $walker->car_number;
                        $driver_data['rating'] = $walker->rate;
                        $driver_data['num_rating'] = $walker->rate_count;
                        $i++;
                    }
                    $request_meta->save();
                }
                $req =RideRequest::find($request->id);
                $req->current_walker = $first_walker_id;
                $req->save();

                $settings = Settings::where('key', 'provider_timeout')->first();
                $time_left = $settings->value;

                // Send Notification
                $walker = Walker::find($first_walker_id);
                if ($walker) {
                    $msg_array = array();
                    $msg_array['unique_id'] = 1;
                    $msg_array['request_id'] = $request->id;
                    $msg_array['time_left_to_respond'] = $time_left;


                    $msg_array['payment_mode'] = $payment_opt;

                    $owner = Owner::find($owner_id);
                    $request_data = array();
                    $request_data['owner'] = array();
                    $request_data['owner']['name'] = $owner->contact_name;
                    $request_data['owner']['picture'] = $owner->picture;
                    $request_data['owner']['phone'] = $owner->phone;
                    $request_data['owner']['address'] = $owner->address;
                    $request_data['owner']['latitude'] = $request->latitude;
                    $request_data['owner']['longitude'] = $request->longitude;
                    if ($d_latitude != NULL) {
                        $request_data['owner']['d_latitude'] = $d_latitude;
                        $request_data['owner']['d_longitude'] = $d_longitude;
                    }
                    $request_data['owner']['owner_dist_lat'] = $request->D_latitude;
                    $request_data['owner']['owner_dist_long'] = $request->D_longitude;
                    $request_data['owner']['payment_type'] = $payment_opt;
                    $request_data['owner']['rating'] = $owner->rate;
                    $request_data['owner']['num_rating'] = $owner->rate_count;
                    $request_data['dog'] = array();
                    if ($dog = Dog::find($owner->dog_id)) {

                        $request_data['dog']['name'] = $dog->name;
                        $request_data['dog']['age'] = $dog->age;
                        $request_data['dog']['breed'] = $dog->breed;
                        $request_data['dog']['likes'] = $dog->likes;
                        $request_data['dog']['picture'] = $dog->image_url;
                    }
                    $msg_array['request_data'] = $request_data;

                    ScheduledRequest::where('id', '=', $schedules->id)->delete();

                    $title = "New Request";
                    $message = $msg_array;

                    send_notifications($first_walker_id, "walker", $title, $message);

                    $client_push_data = array(
                        'success' => true,
                        'unique_id' => 1,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'request_id' => $request->id,
                        'walker' => $driver_data,
                    );
                    $message = $client_push_data;
                    $title = "Activated the scheduled " . Config::get('app.generic_keywords.Trip') . "";
                    send_notifications($owner->id, "owner", $title, $message);

                    // Send SMS 
                    $settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner->contact_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);

                    // send email
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $follow_url = web_url() . "/booking/signin";
                    $pattern = array('admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url);
                    $subject = "Ride Booking Request";
                    email_notification(1, 'admin', $pattern, $subject, 'new_request', null);
                } else {
                    $trys_for_request = $schedules->retryflag + 1;
                    ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
                }
            } else {
                $trys_for_request = $schedules->retryflag + 1;
                ScheduledRequest::where('id', $schedules->id)->update(array('retryflag' => $trys_for_request));
            }
            $response_code = 200;
        }
        $response_array = array(
            'details' => $details,
            'current' => $now,
            'pre_request_time' => $now_30,
            'success' => true,
        );
        $response_code = 200;
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function auto_transfer_to_providers() {
        /* Cronjob counter */
        /* echo asset_url() . "/cron_count.txt"; */
        $css_msg = file(asset_url() . "/auto_transfer_provider.txt");
        if ($css_msg[0] > '100') {
            $css_msg[0] = '0';
        } else {
            $css_msg[0] ++;
        }
        /* echo $css_msg[0]; */
        $t = file_put_contents(public_path() . '/auto_transfer_provider.txt', $css_msg[0]);
        $css_msg[0];
        /* Cronjob counter END */
        $now = date("Y-m-d H:i:s", strtotime("now"));
        /* echo "\n"; */

        /* AUTO TRANSFER TO SERVICE PROVIDER ACCOUNT */
        $settings = Settings::where('key', 'auto_transfer_schedule_at_after_selected_number_of_days')->first();
        $transfer_schedule_duration = ($settings->value * 1440);
        $transfer_date = date("Y-m-d H:i:s", strtotime("-" . $transfer_schedule_duration . " minutes"));

        $provider_transfer = Walker::where('provider_transfer_date', '<=', $transfer_date)->where('payment_remaining', '>=', 1)->where('refund_remaining', '>=', 1)->get();

        $settings = Settings::where('key', 'auto_transfer_provider_payment')->first();
        $transfer_allow = $settings->value;
        $fail_reason = "";
        foreach ($provider_transfer as $provider_data_trans) {
            if (Config::get('app.default_payment') == 'stripe') {
                if ($transfer_allow == 1 && $provider_data_trans->merchant_id != "" && Config::get('app.currency_symb') == '$' && ($provider_data_trans->account_currency = 'usd' || $provider_data_trans->account_currency = 'USD') && ($provider_data_trans->account_country = 'US' || $provider_data_trans->account_country = 'us')) {

                    $transfer_amount = $provider_data_trans->payment_remaining - $provider_data_trans->refund_remaining;
                    $payment_ramaining = $provider_data_trans->payment_remaining;
                    $refund_ramaining = $provider_data_trans->refund_remaining;

                    if ($transfer_amount > 0) {
                        /* echo $provider_data_trans->id;
                          echo "\n"; */
                        $transfer_floor = ($transfer_amount);
                        Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                        try {
                            $transfer = Stripe_Transfer::create(array(
                                        "amount" => $transfer_floor * 100, // amount in cents
                                        "currency" => "usd",
                                        "recipient" => $provider_data_trans->merchant_id)
                            );
                            if ($transfer->status != 'canceled' || $transfer->status != 'failed') {
                                /* SUCESS */
                                $walker_data = Walker::find($provider_data_trans->id);
                                $walker_data->provider_transfer_date = $now;
                                $walker_data->payment_remaining = $walker_data->payment_remaining - $transfer_floor - ($provider_data_trans->refund_remaining);
                                $walker_data->refund_remaining = $walker_data->refund_remaining - ($walker_data->refund_remaining);
                                $walker_data->save();

                                /* EMAIL NOTIFICATION */
                                /* $settings = Settings::where('key', 'auto_transfer_to_provider_account_on_success')->first();
                                  $pattern = $settings->value;
                                  $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                                  $pattern = str_replace('%payment%', $payment_ramaining, $pattern);
                                  $pattern = str_replace('%refund%', $refund_ramaining, $pattern);
                                  $pattern = str_replace('%amount%', $transfer_floor, $pattern);
                                  $subject = "Credited to Your Account";
                                  email_notification($walker_data->id, 'walker', $pattern, $subject); */
                                /* EMAIL NOTIFICATION END */
                                /* SMS NOTIFICATION */
                                /* $settings = Settings::where('key', 'sms_provider_auto_transaction_success')->first();
                                  $pattern = $settings->value;
                                  $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                                  $pattern = str_replace('%amount%', $transfer_floor, $pattern);
                                  sms_notification($walker_data->id, 'walker', $pattern); */
                                /* SMS NOTIFICATION END */
                            } else {
                                /* FAIL */
                                /* echo "fail no transfer";
                                  echo "\n"; */
                                $fail_reason = "Transaction Failed";
                                $walker_data = Walker::find($provider_data_trans->id);
                                $walker_data->provider_transfer_date = $now;
                                $walker_data->save();
                                /* EMAIL NOTIFICATION */
                                /* $settings = Settings::where('key', 'auto_transfer_to_provider_account_on_fail')->first();
                                  $pattern = $settings->value;
                                  $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                                  $pattern = str_replace('%reason%', $fail_reason, $pattern);
                                  $subject = "Credited Fail To Your Account";
                                  email_notification($walker_data->id, 'walker', $pattern, $subject); */
                                /* EMAIL NOTIFICATION END */
                                /* SMS NOTIFICATION */
                                /* $settings = Settings::where('key', 'sms_provider_auto_transaction_fail')->first();
                                  $pattern = $settings->value;
                                  $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                                  $pattern = str_replace('%reason%', $fail_reason, $pattern);
                                  sms_notification($walker_data->id, 'walker', $pattern); */
                                /* SMS NOTIFICATION END */
                            }
                        } catch (Stripe_InvalidRequestError $e) {
                            /* echo "admin fail";
                              echo "\n"; */
                            /* FAIL TO ADMIN */
                            $fail_reason = "Transaction Failed";
                            $walker_data = Walker::find($provider_data_trans->id);
                            $walker_data->provider_transfer_date = $now;
                            $walker_data->save();
                            /* EMAIL NOTIFICATION */
                            /* $settings = Settings::where('key', 'auto_transfer_to_provider_account_on_fail')->first();
                              $pattern = $settings->value;
                              $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                              $pattern = str_replace('%reason%', $fail_reason, $pattern);
                              $subject = "Credited Fail To Your Account";
                              email_notification($walker_data->id, 'walker', $pattern, $subject); */
                            /* EMAIL NOTIFICATION END */
                            /* SMS NOTIFICATION */
                            /* $settings = Settings::where('key', 'sms_provider_auto_transaction_fail')->first();
                              $pattern = $settings->value;
                              $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                              $pattern = str_replace('%reason%', $fail_reason, $pattern);
                              sms_notification($walker_data->id, 'walker', $pattern); */
                            /* SMS NOTIFICATION END */
                        }
                        /* $request->transfer_amount = floor($total - $settng->value * $total / 100); */
                    } else {
                        /* echo "fail no enough amount";
                          echo "\n"; */
                        $fail_reason = "Not Enough Transfer amount";
                        $walker_data = Walker::find($provider_data_trans->id);
                        $walker_data->provider_transfer_date = $now;
                        $walker_data->save();
                        /* EMAIL NOTIFICATION */
                        /* $settings = Settings::where('key', 'auto_transfer_to_provider_account_on_fail')->first();
                          $pattern = $settings->value;
                          $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                          $pattern = str_replace('%reason%', $fail_reason, $pattern);
                          $subject = "Credited Fail To Your Account";
                          email_notification($walker_data->id, 'walker', $pattern, $subject); */
                        /* EMAIL NOTIFICATION END */
                        /* SMS NOTIFICATION */
                        /* $settings = Settings::where('key', 'sms_provider_auto_transaction_fail')->first();
                          $pattern = $settings->value;
                          $pattern = str_replace('%name%', $walker_data->contact_name, $pattern);
                          $pattern = str_replace('%reason%', $fail_reason, $pattern);
                          sms_notification($walker_data->id, 'walker', $pattern); */
                        /* SMS NOTIFICATION END */
                        /* SEND EMAIL OF FAILER */
                        /* SEND SMS OF FAILER */
                    }
                }
            }
        }
        /* AUTO TRANSFER TO SERVICE PROVIDER ACCOUNT END */
    }

    // Request in Progress

    public function request_in_progress() {


        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6
                        )
        );

        /* $var = Keywords::where('id', 2)->first(); */

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $request = RideRequest::where('status', '=', 1)->where('is_completed', '=', 0)->where('is_cancelled', '=', 0)->where('owner_id', '=', $owner_id)->where('current_walker', '!=', 0)->orderBy('created_at', 'desc')->first();
                    if ($request) {
                        $request_id = $request->id;
                    } else {
                        $request_id = -1;
                    }
                    $response_array = array(
                        'request_id' => $request_id,
                        'success' => true,
                    );
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function create_future_request() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $d_latitude = 0;
        if (Input::has('d_latitude')) {
            $d_latitude = Input::get('d_latitude');
        }
        $d_longitude = 0;
        if (Input::has('d_longitude')) {
            $d_longitude = Input::get('d_longitude');
        }
        $time_zone = trim(Input::get('time_zone'));
        $start_time = trim(Input::get('start_time'));
        $src_address = "Address Not Available";
        if (Input::has('src_address')) {
            $src_address = trim(Input::get('src_address'));
        }
        $dest_address = "Address Not Available";
        if (Input::has('dest_address')) {
            $dest_address = trim(Input::get('dest_address'));
        }
        $payment_opt = 0;
        if (Input::has('payment_mode')) {
            $payment_opt = Input::get('payment_mode');
        }
        if (Input::has('payment_opt')) {
            $payment_opt = Input::get('payment_opt');
        }
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'time_zone' => $time_zone,
                    'start_time' => $start_time,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    'time_zone' => 'required',
                    'start_time' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                    'time_zone.required' => 82,
                    'start_time.required' => 83,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $unit = "";
            $driver_data = "";
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO
            // SEND REFERRAL & PROMO INFO

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                $all_scheduled_requests = array();
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    /* TIME ZONE BASED TIME CONVERSION */
                    date_default_timezone_set($time_zone);
                    $datetime = new DateTime($start_time);
                    $datetime->format('Y-m-d H:i:s');
                    foreach ($datetime as $row1) {
                        $start_time = $row1; // returns 2014-06-04 15:00
                        break;     // stops at the first position
                    }
                    $timeEurope = new DateTimeZone(Config::get('app.timezone'));
                    $datetime->setTimezone($timeEurope);
                    $datetime->format('Y-m-d H:i:s');
                    foreach ($datetime as $row) {
                        $server_time = $row; // returns 2014-06-04 15:00
                        break;     // stops at the first position
                    }
                    $chk_dt = date_time_differ($server_time, "+2 weeks");
                    if ($chk_dt->invert == 0 && $chk_dt->d >= 0 && $chk_dt->d <= 14) {
                        /* TIME ZONE BASED TIME CONVERSION END */


                        /* $response_array = array(
                          'success' => true,
                          'time_zone' => $time_zone,
                          'time' => $start_time,
                          'server_time_zone' => Config::get('app.timezone'),
                          'server_time' => $server_time,
                          'error' => 59,
                          'error_messages' => array(59),
                          'error_code' => 417
                          );
                          $response_code = 200;
                          $response = Response::json($response_array, $response_code);
                          return $response; */
                        if ($payment_opt != 1) {
                            $card_count = Payment::where('owner_id', '=', $owner_id)->count();
                            if ($card_count <= 0) {
                                $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                foreach ($ScheduledRequest as $data1) {
                                    $data['id'] = $data1->id;
                                    $data['owner_id'] = $data1->owner_id;
                                    $data['latitude'] = $data1->latitude;
                                    $data['longitude'] = $data1->longitude;
                                    $data['dest_latitude'] = $data1->dest_latitude;
                                    $data['dest_longitude'] = $data1->dest_longitude;
                                    $data['time_zone'] = $data1->time_zone;
                                    $data['src_address'] = $data1->src_address;
                                    $data['dest_address'] = $data1->dest_address;
                                    $data['promo_code'] = $data1->promo_code;
                                    $data['promo_id'] = $data1->promo_id;
                                    $pay_mode_txt = "Card";
                                    if ($data1->payment_mode) {
                                        $pay_mode_txt = "Cash";
                                    }
                                    $data['payment_mode'] = $data1->payment_mode;
                                    $data['pay_mode_txt'] = $pay_mode_txt;
                                    $data['server_start_time'] = $data1->server_start_time;
                                    $data['start_time'] = $data1->start_time;
                                    array_push($all_scheduled_requests, $data);
                                }
                                $response_array = array(
                                    'success' => false,
                                    'error' => 59,
                                    'error_messages' => array(59),
                                    'error_code' => 417,
                                    /* 'now_dt' => $chk_dt, */
                                    'is_referral_active' => $referral_code_activation,
                                    'is_referral_active_txt' => $referral_code_activation_txt,
                                    'is_promo_active' => $promotional_code_activation,
                                    'is_promo_active_txt' => $promotional_code_activation_txt,
                                    'all_scheduled_requests' => $all_scheduled_requests,
                                );
                                $response_code = 200;
                                $response = Response::json($response_array, $response_code);
                                return $response;
                            }
                        }
                        $type = trim(Input::get('type'));
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }
                        $new_request = new ScheduledRequest;
                        $new_request->owner_id = $owner_data->id;
                        $new_request->latitude = $latitude;
                        $new_request->longitude = $longitude;
                        $new_request->time_zone = $time_zone;
                        $new_request->src_address = $src_address;
                        $new_request->payment_mode = $payment_opt;
                        $new_request->server_start_time = $server_time;
                        $new_request->start_time = $start_time;
                        $new_request->type = $type;

                        if (Input::has('promo_code')) {
                            $promo_code = Input::get('promo_code');
                            $payment_mode = 0;
                            if (Input::has('payment_mode')) {
                                $payment_mode = $payment_opt = Input::get('payment_mode');
                            }
                            $settings = Settings::where('key', 'promotional_code_activation')->first();
                            $prom_act = $settings->value;
                            if ($prom_act) {
                                if ($payment_mode == 0) {
                                    $settings = Settings::where('key', 'get_promotional_profit_on_card_payment')->first();
                                    $prom_act_card = $settings->value;
                                    if ($prom_act_card) {
                                        if ($promos = PromoCodes::where('coupon_code', $promo_code)->where('uses', '>', 0)->where('state', '=', 1)->first()) {
                                            if ((date("Y-m-d H:i:s") >= date("Y-m-d H:i:s", strtotime(trim($promos->expiry)))) || (date("Y-m-d H:i:s") <= date("Y-m-d H:i:s", strtotime(trim($promos->start_date))))) {
                                                $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                                foreach ($ScheduledRequest as $data1) {
                                                    $data['id'] = $data1->id;
                                                    $data['owner_id'] = $data1->owner_id;
                                                    $data['latitude'] = $data1->latitude;
                                                    $data['longitude'] = $data1->longitude;
                                                    $data['dest_latitude'] = $data1->dest_latitude;
                                                    $data['dest_longitude'] = $data1->dest_longitude;
                                                    $data['time_zone'] = $data1->time_zone;
                                                    $data['src_address'] = $data1->src_address;
                                                    $data['dest_address'] = $data1->dest_address;
                                                    $data['promo_code'] = $data1->promo_code;
                                                    $data['promo_id'] = $data1->promo_id;
                                                    $pay_mode_txt = "Card";
                                                    if ($data1->payment_mode) {
                                                        $pay_mode_txt = "Cash";
                                                    }
                                                    $data['payment_mode'] = $data1->payment_mode;
                                                    $data['pay_mode_txt'] = $pay_mode_txt;
                                                    $data['server_start_time'] = $data1->server_start_time;
                                                    $data['start_time'] = $data1->start_time;
                                                    array_push($all_scheduled_requests, $data);
                                                }
                                                $response_array = array(
                                                    'success' => FALSE,
                                                    'error' => 64,
                                                    'error_messages' => array(64),
                                                    'error_code' => 505,
                                                    /* 'now_dt' => $chk_dt, */
                                                    'is_referral_active' => $referral_code_activation,
                                                    'is_referral_active_txt' => $referral_code_activation_txt,
                                                    'is_promo_active' => $promotional_code_activation,
                                                    'is_promo_active_txt' => $promotional_code_activation_txt,
                                                    'all_scheduled_requests' => $all_scheduled_requests,
                                                );
                                                $response_code = 200;
                                                return Response::json($response_array, $response_code);
                                            } else {
                                                $promo_is_used = UserPromoUse::where('user_id', '=', $owner_id)->where('code_id', '=', $promos->id)->count();
                                                if ($promo_is_used) {
                                                    $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                                    foreach ($ScheduledRequest as $data1) {
                                                        $data['id'] = $data1->id;
                                                        $data['owner_id'] = $data1->owner_id;
                                                        $data['latitude'] = $data1->latitude;
                                                        $data['longitude'] = $data1->longitude;
                                                        $data['dest_latitude'] = $data1->dest_latitude;
                                                        $data['dest_longitude'] = $data1->dest_longitude;
                                                        $data['time_zone'] = $data1->time_zone;
                                                        $data['src_address'] = $data1->src_address;
                                                        $data['dest_address'] = $data1->dest_address;
                                                        $data['promo_code'] = $data1->promo_code;
                                                        $data['promo_id'] = $data1->promo_id;
                                                        $pay_mode_txt = "Card";
                                                        if ($data1->payment_mode) {
                                                            $pay_mode_txt = "Cash";
                                                        }
                                                        $data['payment_mode'] = $data1->payment_mode;
                                                        $data['pay_mode_txt'] = $pay_mode_txt;
                                                        $data['server_start_time'] = $data1->server_start_time;
                                                        $data['start_time'] = $data1->start_time;
                                                        array_push($all_scheduled_requests, $data);
                                                    }
                                                    $response_array = array(
                                                        'success' => FALSE,
                                                        'error' => 'Promotional code already used.',
                                                        'error_messages' => array('Promotional code already used.'),
                                                        'error_code' => 512,
                                                        /* 'now_dt' => $chk_dt, */
                                                        'is_referral_active' => $referral_code_activation,
                                                        'is_referral_active_txt' => $referral_code_activation_txt,
                                                        'is_promo_active' => $promotional_code_activation,
                                                        'is_promo_active_txt' => $promotional_code_activation_txt,
                                                        'all_scheduled_requests' => $all_scheduled_requests,
                                                    );
                                                    $response_code = 200;
                                                    return Response::json($response_array, $response_code);
                                                } else {
                                                    $promo_update_counter = PromoCodes::find($promos->id);
                                                    $promo_update_counter->uses = $promo_update_counter->uses - 1;
                                                    $promo_update_counter->save();

                                                    $user_promo_entry = new UserPromoUse;
                                                    $user_promo_entry->code_id = $promos->id;
                                                    $user_promo_entry->user_id = $owner_id;
                                                    $user_promo_entry->save();

                                                    $owner = Owner::find($owner_id);
                                                    $owner->promo_count = $owner->promo_count + 1;
                                                    $owner->save();

                                                    $new_request->promo_id = $promos->id;
                                                    $new_request->promo_code = $promos->coupon_code;
                                                    /* if ($promos->is_event) {
                                                      $event_data = UserEvents::where('id', $promos->event_id)->first();
                                                      $d_latitude = $event_data->event_latitude;
                                                      $d_longitude = $event_data->event_longitude;
                                                      $dest_address = $event_data->event_place_address;
                                                      } */
                                                }
                                            }
                                        } else {
                                            $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                            foreach ($ScheduledRequest as $data1) {
                                                $data['id'] = $data1->id;
                                                $data['owner_id'] = $data1->owner_id;
                                                $data['latitude'] = $data1->latitude;
                                                $data['longitude'] = $data1->longitude;
                                                $data['dest_latitude'] = $data1->dest_latitude;
                                                $data['dest_longitude'] = $data1->dest_longitude;
                                                $data['time_zone'] = $data1->time_zone;
                                                $data['src_address'] = $data1->src_address;
                                                $data['dest_address'] = $data1->dest_address;
                                                $data['promo_code'] = $data1->promo_code;
                                                $data['promo_id'] = $data1->promo_id;
                                                $pay_mode_txt = "Card";
                                                if ($data1->payment_mode) {
                                                    $pay_mode_txt = "Cash";
                                                }
                                                $data['payment_mode'] = $data1->payment_mode;
                                                $data['pay_mode_txt'] = $pay_mode_txt;
                                                $data['server_start_time'] = $data1->server_start_time;
                                                $data['start_time'] = $data1->start_time;
                                                array_push($all_scheduled_requests, $data);
                                            }
                                            $response_array = array(
                                                'success' => FALSE,
                                                'error' => 64,
                                                'error_messages' => array(64),
                                                'error_code' => 505,
                                                /* 'now_dt' => $chk_dt, */
                                                'is_referral_active' => $referral_code_activation,
                                                'is_referral_active_txt' => $referral_code_activation_txt,
                                                'is_promo_active' => $promotional_code_activation,
                                                'is_promo_active_txt' => $promotional_code_activation_txt,
                                                'all_scheduled_requests' => $all_scheduled_requests,
                                            );
                                            $response_code = 200;
                                            return Response::json($response_array, $response_code);
                                        }
                                    } else {
                                        $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                        foreach ($ScheduledRequest as $data1) {
                                            $data['id'] = $data1->id;
                                            $data['owner_id'] = $data1->owner_id;
                                            $data['latitude'] = $data1->latitude;
                                            $data['longitude'] = $data1->longitude;
                                            $data['dest_latitude'] = $data1->dest_latitude;
                                            $data['dest_longitude'] = $data1->dest_longitude;
                                            $data['time_zone'] = $data1->time_zone;
                                            $data['src_address'] = $data1->src_address;
                                            $data['dest_address'] = $data1->dest_address;
                                            $data['promo_code'] = $data1->promo_code;
                                            $data['promo_id'] = $data1->promo_id;
                                            $pay_mode_txt = "Card";
                                            if ($data1->payment_mode) {
                                                $pay_mode_txt = "Cash";
                                            }
                                            $data['payment_mode'] = $data1->payment_mode;
                                            $data['pay_mode_txt'] = $pay_mode_txt;
                                            $data['server_start_time'] = $data1->server_start_time;
                                            $data['start_time'] = $data1->start_time;
                                            array_push($all_scheduled_requests, $data);
                                        }
                                        $response_array = array(
                                            'success' => FALSE,
                                            'error' => 66,
                                            'error_messages' => array(66),
                                            'error_code' => 505,
                                            /* 'now_dt' => $chk_dt, */
                                            'is_referral_active' => $referral_code_activation,
                                            'is_referral_active_txt' => $referral_code_activation_txt,
                                            'is_promo_active' => $promotional_code_activation,
                                            'is_promo_active_txt' => $promotional_code_activation_txt,
                                            'all_scheduled_requests' => $all_scheduled_requests,
                                        );
                                        $response_code = 200;
                                        return Response::json($response_array, $response_code);
                                    }
                                } else if (($payment_mode == 1)) {
                                    $settings = Settings::where('key', 'get_promotional_profit_on_cash_payment')->first();
                                    $prom_act_cash = $settings->value;
                                    if ($prom_act_cash) {
                                        if ($promos = PromoCodes::where('coupon_code', $promo_code)->where('uses', '>', 0)->where('state', '=', 1)->first()) {
                                            if ((date("Y-m-d H:i:s") >= date("Y-m-d H:i:s", strtotime(trim($promos->expiry)))) || (date("Y-m-d H:i:s") <= date("Y-m-d H:i:s", strtotime(trim($promos->start_date))))) {
                                                $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                                foreach ($ScheduledRequest as $data1) {
                                                    $data['id'] = $data1->id;
                                                    $data['owner_id'] = $data1->owner_id;
                                                    $data['latitude'] = $data1->latitude;
                                                    $data['longitude'] = $data1->longitude;
                                                    $data['dest_latitude'] = $data1->dest_latitude;
                                                    $data['dest_longitude'] = $data1->dest_longitude;
                                                    $data['time_zone'] = $data1->time_zone;
                                                    $data['src_address'] = $data1->src_address;
                                                    $data['dest_address'] = $data1->dest_address;
                                                    $data['promo_code'] = $data1->promo_code;
                                                    $data['promo_id'] = $data1->promo_id;
                                                    $pay_mode_txt = "Card";
                                                    if ($data1->payment_mode) {
                                                        $pay_mode_txt = "Cash";
                                                    }
                                                    $data['payment_mode'] = $data1->payment_mode;
                                                    $data['pay_mode_txt'] = $pay_mode_txt;
                                                    $data['server_start_time'] = $data1->server_start_time;
                                                    $data['start_time'] = $data1->start_time;
                                                    array_push($all_scheduled_requests, $data);
                                                }
                                                $response_array = array(
                                                    'success' => FALSE,
                                                    'error' => 64,
                                                    'error_messages' => array(64),
                                                    'error_code' => 505,
                                                    /* 'now_dt' => $chk_dt, */
                                                    'is_referral_active' => $referral_code_activation,
                                                    'is_referral_active_txt' => $referral_code_activation_txt,
                                                    'is_promo_active' => $promotional_code_activation,
                                                    'is_promo_active_txt' => $promotional_code_activation_txt,
                                                    'all_scheduled_requests' => $all_scheduled_requests,
                                                );
                                                $response_code = 200;
                                                return Response::json($response_array, $response_code);
                                            } else {
                                                $promo_is_used = UserPromoUse::where('user_id', '=', $owner_id)->where('code_id', '=', $promos->id)->count();
                                                if ($promo_is_used) {
                                                    $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                                    foreach ($ScheduledRequest as $data1) {
                                                        $data['id'] = $data1->id;
                                                        $data['owner_id'] = $data1->owner_id;
                                                        $data['latitude'] = $data1->latitude;
                                                        $data['longitude'] = $data1->longitude;
                                                        $data['dest_latitude'] = $data1->dest_latitude;
                                                        $data['dest_longitude'] = $data1->dest_longitude;
                                                        $data['time_zone'] = $data1->time_zone;
                                                        $data['src_address'] = $data1->src_address;
                                                        $data['dest_address'] = $data1->dest_address;
                                                        $data['promo_code'] = $data1->promo_code;
                                                        $data['promo_id'] = $data1->promo_id;
                                                        $pay_mode_txt = "Card";
                                                        if ($data1->payment_mode) {
                                                            $pay_mode_txt = "Cash";
                                                        }
                                                        $data['payment_mode'] = $data1->payment_mode;
                                                        $data['pay_mode_txt'] = $pay_mode_txt;
                                                        $data['server_start_time'] = $data1->server_start_time;
                                                        $data['start_time'] = $data1->start_time;
                                                        array_push($all_scheduled_requests, $data);
                                                    }
                                                    $response_array = array(
                                                        'success' => FALSE,
                                                        'error' => 'Promotional code already used.',
                                                        'error_messages' => array('Promotional code already used.'),
                                                        'error_code' => 512,
                                                        /* 'now_dt' => $chk_dt, */
                                                        'is_referral_active' => $referral_code_activation,
                                                        'is_referral_active_txt' => $referral_code_activation_txt,
                                                        'is_promo_active' => $promotional_code_activation,
                                                        'is_promo_active_txt' => $promotional_code_activation_txt,
                                                        'all_scheduled_requests' => $all_scheduled_requests,
                                                    );
                                                    $response_code = 200;
                                                    return Response::json($response_array, $response_code);
                                                } else {
                                                    $promo_update_counter = PromoCodes::find($promos->id);
                                                    $promo_update_counter->uses = $promo_update_counter->uses - 1;
                                                    $promo_update_counter->save();

                                                    $user_promo_entry = new UserPromoUse;
                                                    $user_promo_entry->code_id = $promos->id;
                                                    $user_promo_entry->user_id = $owner_id;
                                                    $user_promo_entry->save();

                                                    $owner = Owner::find($owner_id);
                                                    $owner->promo_count = $owner->promo_count + 1;
                                                    $owner->save();

                                                    $new_request->promo_id = $promos->id;
                                                    $new_request->promo_code = $promos->coupon_code;
                                                    /* if ($promos->is_event) {
                                                      $event_data = UserEvents::where('id', $promos->event_id)->first();
                                                      $d_latitude = $event_data->event_latitude;
                                                      $d_longitude = $event_data->event_longitude;
                                                      $dest_address = $event_data->event_place_address;
                                                      } */
                                                }
                                            }
                                        } else {
                                            $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                            foreach ($ScheduledRequest as $data1) {
                                                $data['id'] = $data1->id;
                                                $data['owner_id'] = $data1->owner_id;
                                                $data['latitude'] = $data1->latitude;
                                                $data['longitude'] = $data1->longitude;
                                                $data['dest_latitude'] = $data1->dest_latitude;
                                                $data['dest_longitude'] = $data1->dest_longitude;
                                                $data['time_zone'] = $data1->time_zone;
                                                $data['src_address'] = $data1->src_address;
                                                $data['dest_address'] = $data1->dest_address;
                                                $data['promo_code'] = $data1->promo_code;
                                                $data['promo_id'] = $data1->promo_id;
                                                $pay_mode_txt = "Card";
                                                if ($data1->payment_mode) {
                                                    $pay_mode_txt = "Cash";
                                                }
                                                $data['payment_mode'] = $data1->payment_mode;
                                                $data['pay_mode_txt'] = $pay_mode_txt;
                                                $data['server_start_time'] = $data1->server_start_time;
                                                $data['start_time'] = $data1->start_time;
                                                array_push($all_scheduled_requests, $data);
                                            }
                                            $response_array = array(
                                                'success' => FALSE,
                                                'error' => 64,
                                                'error_messages' => array(64),
                                                'error_code' => 505,
                                                /* 'now_dt' => $chk_dt, */
                                                'is_referral_active' => $referral_code_activation,
                                                'is_referral_active_txt' => $referral_code_activation_txt,
                                                'is_promo_active' => $promotional_code_activation,
                                                'is_promo_active_txt' => $promotional_code_activation_txt,
                                                'all_scheduled_requests' => $all_scheduled_requests,
                                            );
                                            $response_code = 200;
                                            return Response::json($response_array, $response_code);
                                        }
                                    } else {
                                        $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                        foreach ($ScheduledRequest as $data1) {
                                            $data['id'] = $data1->id;
                                            $data['owner_id'] = $data1->owner_id;
                                            $data['latitude'] = $data1->latitude;
                                            $data['longitude'] = $data1->longitude;
                                            $data['dest_latitude'] = $data1->dest_latitude;
                                            $data['dest_longitude'] = $data1->dest_longitude;
                                            $data['time_zone'] = $data1->time_zone;
                                            $data['src_address'] = $data1->src_address;
                                            $data['dest_address'] = $data1->dest_address;
                                            $data['promo_code'] = $data1->promo_code;
                                            $data['promo_id'] = $data1->promo_id;
                                            $pay_mode_txt = "Card";
                                            if ($data1->payment_mode) {
                                                $pay_mode_txt = "Cash";
                                            }
                                            $data['payment_mode'] = $data1->payment_mode;
                                            $data['pay_mode_txt'] = $pay_mode_txt;
                                            $data['server_start_time'] = $data1->server_start_time;
                                            $data['start_time'] = $data1->start_time;
                                            array_push($all_scheduled_requests, $data);
                                        }
                                        $response_array = array(
                                            'success' => FALSE,
                                            'error' => 67,
                                            'error_messages' => array(67),
                                            'error_code' => 505,
                                            /* 'now_dt' => $chk_dt, */
                                            'is_referral_active' => $referral_code_activation,
                                            'is_referral_active_txt' => $referral_code_activation_txt,
                                            'is_promo_active' => $promotional_code_activation,
                                            'is_promo_active_txt' => $promotional_code_activation_txt,
                                            'all_scheduled_requests' => $all_scheduled_requests,
                                        );
                                        $response_code = 200;
                                        return Response::json($response_array, $response_code);
                                    }
                                }
                            } else {
                                $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                                foreach ($ScheduledRequest as $data1) {
                                    $data['id'] = $data1->id;
                                    $data['owner_id'] = $data1->owner_id;
                                    $data['latitude'] = $data1->latitude;
                                    $data['longitude'] = $data1->longitude;
                                    $data['dest_latitude'] = $data1->dest_latitude;
                                    $data['dest_longitude'] = $data1->dest_longitude;
                                    $data['time_zone'] = $data1->time_zone;
                                    $data['src_address'] = $data1->src_address;
                                    $data['dest_address'] = $data1->dest_address;
                                    $data['promo_code'] = $data1->promo_code;
                                    $data['promo_id'] = $data1->promo_id;
                                    $pay_mode_txt = "Card";
                                    if ($data1->payment_mode) {
                                        $pay_mode_txt = "Cash";
                                    }
                                    $data['payment_mode'] = $data1->payment_mode;
                                    $data['pay_mode_txt'] = $pay_mode_txt;
                                    $data['server_start_time'] = $data1->server_start_time;
                                    $data['start_time'] = $data1->start_time;
                                    array_push($all_scheduled_requests, $data);
                                }
                                $response_array = array(
                                    'success' => FALSE,
                                    'error' => 68,
                                    'error_messages' => array(68),
                                    'error_code' => 505,
                                    /* 'now_dt' => $chk_dt, */
                                    'is_referral_active' => $referral_code_activation,
                                    'is_referral_active_txt' => $referral_code_activation_txt,
                                    'is_promo_active' => $promotional_code_activation,
                                    'is_promo_active_txt' => $promotional_code_activation_txt,
                                    'all_scheduled_requests' => $all_scheduled_requests,
                                );
                                $response_code = 200;
                                return Response::json($response_array, $response_code);
                            }
                        }
                        $new_request->dest_address = $dest_address;
                        $new_request->dest_latitude = $d_latitude;
                        $new_request->dest_longitude = $d_longitude;
                        $new_request->save();
                        $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                        foreach ($ScheduledRequest as $data1) {
                            $data['id'] = $data1->id;
                            $data['owner_id'] = $data1->owner_id;
                            $data['latitude'] = $data1->latitude;
                            $data['longitude'] = $data1->longitude;
                            $data['dest_latitude'] = $data1->dest_latitude;
                            $data['dest_longitude'] = $data1->dest_longitude;
                            $data['time_zone'] = $data1->time_zone;
                            $data['src_address'] = $data1->src_address;
                            $data['dest_address'] = $data1->dest_address;
                            $data['promo_code'] = $data1->promo_code;
                            $data['promo_id'] = $data1->promo_id;
                            $pay_mode_txt = "Card";
                            if ($data1->payment_mode) {
                                $pay_mode_txt = "Cash";
                            }
                            $data['payment_mode'] = $data1->payment_mode;
                            $data['pay_mode_txt'] = $pay_mode_txt;
                            $data['server_start_time'] = $data1->server_start_time;
                            $data['start_time'] = $data1->start_time;
                            array_push($all_scheduled_requests, $data);
                        }
                        $response_array = array(
                            'success' => true,
                            /* 'now_dt' => $chk_dt, */
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'all_scheduled_requests' => $all_scheduled_requests,
                        );
                        $response_code = 200;
                    } else {
                        $response_array = array(
                            'success' => false,
                            'error' => 84,
                            'error_messages' => array(84),
                            'error_code' => 506,
                            /* 'now_dt' => $chk_dt, */
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'all_scheduled_requests' => $all_scheduled_requests,
                        );
                        $response_code = 200;
                    }
                } else {
                    $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                    foreach ($ScheduledRequest as $data1) {
                        $data['id'] = $data1->id;
                        $data['owner_id'] = $data1->owner_id;
                        $data['latitude'] = $data1->latitude;
                        $data['longitude'] = $data1->longitude;
                        $data['dest_latitude'] = $data1->dest_latitude;
                        $data['dest_longitude'] = $data1->dest_longitude;
                        $data['time_zone'] = $data1->time_zone;
                        $data['src_address'] = $data1->src_address;
                        $data['dest_address'] = $data1->dest_address;
                        $data['promo_code'] = $data1->promo_code;
                        $data['promo_id'] = $data1->promo_id;
                        $pay_mode_txt = "Card";
                        if ($data1->payment_mode) {
                            $pay_mode_txt = "Cash";
                        }
                        $data['payment_mode'] = $data1->payment_mode;
                        $data['pay_mode_txt'] = $pay_mode_txt;
                        $data['server_start_time'] = $data1->server_start_time;
                        $data['start_time'] = $data1->start_time;
                        array_push($all_scheduled_requests, $data);
                    }
                    $response_array = array(
                        'success' => false,
                        'error' => 9,
                        'error_messages' => array(9),
                        'error_code' => 405,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'all_scheduled_requests' => $all_scheduled_requests,
                    );
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_future_request() {
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $unit = "";
            $driver_data = "";
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                $all_scheduled_requests = array();
                $ScheduledRequest = ScheduledRequest::select('scheduled_requests.*', 'walker_type.name AS type_name', 'walker_type.icon AS type_icon')
                                ->leftJoin('walker_type', 'scheduled_requests.type', '=', 'walker_type.id')
                                ->where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                foreach ($ScheduledRequest as $data1) {
                    $data['id'] = $data1->id;
                    $data['owner_id'] = $data1->owner_id;
                    $data['latitude'] = $data1->latitude;
                    $data['longitude'] = $data1->longitude;
                    $data['dest_latitude'] = $data1->dest_latitude;
                    $data['dest_longitude'] = $data1->dest_longitude;
                    $data['time_zone'] = $data1->time_zone;
                    $data['src_address'] = $data1->src_address;
                    $data['dest_address'] = $data1->dest_address;
                    $data['promo_code'] = $data1->promo_code;
                    $data['promo_id'] = $data1->promo_id;
                    $pay_mode_txt = "Card";
                    if ($data1->payment_mode) {
                        $pay_mode_txt = "Cash";
                    }
                    $data['payment_mode'] = $data1->payment_mode;
                    $data['pay_mode_txt'] = $pay_mode_txt;
                    $data['server_start_time'] = $data1->server_start_time;
                    $data['start_time'] = $data1->start_time;
                    $data['type_name'] = $data1->type_name;
                    $data['type_icon'] = $data1->type_icon;
                    if ($data1->dest_latitude != 0 || $data1->dest_longitude != 0) {
                        $data['map_image'] = "https://maps-api-ssl.google.com/maps/api/staticmap?"
                                . "size=249x249&"
                                . "style=feature:landscape|visibility:off&"
                                . "style=feature:poi|visibility:off&"
                                . "style=feature:transit|visibility:off&"
                                . "style=feature:road.highway|element:geometry|lightness:39&"
                                . "style=feature:road.local|element:geometry|gamma:1.45&"
                                . "style=feature:road|element:labels|gamma:1.22&"
                                . "style=feature:administrative|visibility:off&"
                                . "style=feature:administrative.locality|visibility:on&"
                                . "style=feature:landscape.natural|visibility:on&"
                                . "scale=2&"
                                . "markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|" . $data1->latitude . "," . $data1->longitude . "&"
                                . "markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|" . $data1->dest_latitude . "," . $data1->dest_longitude . "";
                    } else {
                        $data['map_image'] = "https://maps-api-ssl.google.com/maps/api/staticmap?"
                                . "size=249x249&"
                                . "style=feature:landscape|visibility:off&"
                                . "style=feature:poi|visibility:off&"
                                . "style=feature:transit|visibility:off&"
                                . "style=feature:road.highway|element:geometry|lightness:39&"
                                . "style=feature:road.local|element:geometry|gamma:1.45&"
                                . "style=feature:road|element:labels|gamma:1.22&"
                                . "style=feature:administrative|visibility:off&"
                                . "style=feature:administrative.locality|visibility:on&"
                                . "style=feature:landscape.natural|visibility:on&"
                                . "scale=2&"
                                . "markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|" . $data1->latitude . "," . $data1->longitude . "";
                    }
                    $data['is_running'] = 0;
                    $data['walker'] = "";
                    array_push($all_scheduled_requests, $data);
                }
                $request_data = array();
                $running_requests = Request::select('request.id as request_id', 'request.req_create_user_time', 'request.dest_address as dest_address', 'request.src_address as source_address', 'request.is_completed as is_completed', 'request.is_started as is_started', 'request.is_walker_arrived as is_walker_arrived', 'request.is_walker_started as is_walker_started', 'request.confirmed_walker as confirmed_walker', 'request.D_latitude as dest_latitude', 'request.D_longitude as dest_longitude', 'request.latitude as src_latitude', 'request.longitude as src_longitude', 'owner.id as user_id', 'owner.contact_name as user_contact_name', 'owner.phone as user_phone', 'owner.email as user_email', 'owner.picture as user_picture', 'owner.bio as user_bio', 'owner.address as user_address', 'owner.state as user_state', 'owner.country as user_country', 'owner.zipcode as user_zipcode', 'owner.rate as user_rate', 'owner.rate_count as user_rate_count', 'walker.id as provider_id', 'walker.contact_name as provider_contact_name', 'walker.phone as provider_phone', 'walker.email as provider_email', 'walker.picture as provider_picture', 'walker.bio as provider_bio', 'walker.address as provider_address', 'walker.state as provider_state', 'walker.country as provider_country', 'walker.zipcode as provider_zipcode', 'walker.latitude as provider_latitude', 'walker.longitude as provider_longitude', 'walker.type as provider_type', 'walker.car_model as provider_car_model', 'walker.car_number as provider_car_number', 'walker.rate as provider_rate', 'walker.rate_count as provider_rate_count', 'walker.bearing as bearing')
                        ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                        ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                        ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                        ->where('request.owner_id', '=', $owner_id)
                        ->where('request.is_cancelled', '=', 0)
                        ->where('request.current_walker', '>', 0)
                        ->where('request.is_walker_rated', '=', 0)
                        ->orderBy('request.id', 'DESC')
                        ->get();
                foreach ($running_requests as $requests) {
                    $data2['request_id'] = $requests->request_id;
                    $data2['latitude'] = $requests->src_latitude;
                    $data2['longitude'] = $requests->src_longitude;
                    $data2['d_latitude'] = $requests->dest_latitude;
                    $data2['d_longitude'] = $requests->dest_longitude;
                    /* $data2['owner']['user_id'] = $requests->user_id;
                      $data2['owner']['owner_lat'] = $requests->src_latitude;
                      $data2['owner']['latitude'] = $requests->src_latitude;
                      $data2['owner']['owner_long'] = $requests->src_longitude;
                      $data2['owner']['longitude'] = $requests->src_longitude;
                      $data2['owner']['owner_dist_lat'] = $requests->dest_latitude;
                      $data2['owner']['d_latitude'] = $requests->dest_latitude;
                      $data2['owner']['owner_dist_long'] = $requests->dest_longitude;
                      $data2['owner']['d_longitude'] = $requests->dest_longitude;
                      $data2['owner']['contact_name'] = $requests->user_contact_name;
                      $data2['owner']['phone'] = $requests->user_phone;
                      $data2['owner']['email'] = $requests->user_email;
                      $data2['owner']['picture'] = $requests->user_picture;
                      $data2['owner']['bio'] = $requests->user_bio;
                      $data2['owner']['address'] = $requests->user_address;
                      $data2['owner']['state'] = $requests->user_state;
                      $data2['owner']['country'] = $requests->user_country;
                      $data2['owner']['zipcode'] = $requests->user_zipcode;
                      $data2['owner']['rating'] = $requests->user_rate;
                      $data2['owner']['num_rating'] = $requests->user_rate_count; */
                    if ($requests->confirmed_walker) {
                        $status = "" . Config::get('app.generic_keywords.Provider') . " Confirm";
                    }
                    if ($requests->confirmed_walker == 0) {
                        $status = "" . Config::get('app.generic_keywords.Provider') . " not yet confirmed";
                    }
                    if ($requests->is_walker_started) {
                        $status = "" . Config::get('app.generic_keywords.Provider') . " Started";
                    }
                    if ($requests->is_walker_arrived) {
                        $status = "" . Config::get('app.generic_keywords.Provider') . " Arrived";
                    }

                    if ($requests->is_started) {
                        $status = "" . Config::get('app.generic_keywords.Trip') . " Started";
                    }
                    if ($requests->is_completed) {
                        $status = "" . Config::get('app.generic_keywords.Trip') . " Completed";
                    }
                    $data2['walker']['id'] = $requests->provider_id;
                    $data2['walker']['contact_name'] = $requests->provider_contact_name;
                    $data2['walker']['phone'] = $requests->provider_phone;
                    $data2['walker']['email'] = $requests->provider_email;
                    $data2['walker']['picture'] = $requests->provider_picture;
                    $data2['walker']['bio'] = $requests->provider_bio;
                    $data2['walker']['address'] = $requests->provider_address;
                    $data2['walker']['state'] = $requests->provider_state;
                    $data2['walker']['country'] = $requests->provider_country;
                    $data2['walker']['zipcode'] = $requests->provider_zipcode;
                    $data2['walker']['latitude'] = $requests->provider_latitude;
                    $data2['walker']['longitude'] = $requests->provider_longitude;
                    $data2['walker']['type'] = $requests->provider_type;
                    $data2['walker']['rating'] = $requests->provider_rate;
                    $data2['walker']['num_rating'] = $requests->provider_rate_count;
                    $data2['walker']['car_model'] = $requests->provider_car_model;
                    $data2['walker']['car_number'] = $requests->provider_car_number;
                    $data2['walker']['bearing'] = $requests->bearing;
                    $data2['request_id'] = $requests->request_id;
                    $data2['src_address'] = $requests->source_address;
                    $data2['dest_address'] = $requests->dest_address;
                    $data2['status'] = $status;
                    $data2['create_date_time'] = $requests->req_create_user_time;
                    array_push($request_data, $data2);
                }
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $response_array = array(
                        'success' => true,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'all_scheduled_requests' => $all_scheduled_requests,
                        'requests' => $request_data,
                    );
                    $response_code = 200;
                } else {
                    $response_array = array(
                        'success' => false,
                        'error' => 9,
                        'error_messages' => array(9),
                        'error_code' => 405,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'all_scheduled_requests' => $all_scheduled_requests,
                        'requests' => $request_data,
                    );
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function delete_future_request() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'request_id' => $request_id,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            $unit = "";
            $driver_data = "";
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                $all_scheduled_requests = array();
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = ScheduledRequest::find($request_id)) {
                        if ($request->owner_id == $owner_data->id) {
                            if ($request->promo_id) {
                                $promo_update_counter = PromoCodes::find($request->promo_id);
                                $promo_update_counter->uses = $promo_update_counter->uses + 1;
                                $promo_update_counter->save();

                                UserPromoUse::where('user_id', '=', $owner_id)->where('code_id', '=', $request->promo_id)->delete();

                                $owner = Owner::find($owner_id);
                                $owner->promo_count = $owner->promo_count - 1;
                                $owner->save();
                            }
                            ScheduledRequest::where('owner_id', '=', $owner_id)->where('id', '=', $request_id)->delete();
                            $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                            foreach ($ScheduledRequest as $data1) {
                                $data['id'] = $data1->id;
                                $data['owner_id'] = $data1->owner_id;
                                $data['latitude'] = $data1->latitude;
                                $data['longitude'] = $data1->longitude;
                                $data['dest_latitude'] = $data1->dest_latitude;
                                $data['dest_longitude'] = $data1->dest_longitude;
                                $data['time_zone'] = $data1->time_zone;
                                $data['src_address'] = $data1->src_address;
                                $data['dest_address'] = $data1->dest_address;
                                $data['promo_code'] = $data1->promo_code;
                                $data['promo_id'] = $data1->promo_id;
                                $pay_mode_txt = "Card";
                                if ($data1->payment_mode) {
                                    $pay_mode_txt = "Cash";
                                }
                                $data['payment_mode'] = $data1->payment_mode;
                                $data['pay_mode_txt'] = $pay_mode_txt;
                                $data['server_start_time'] = $data1->server_start_time;
                                $data['start_time'] = $data1->start_time;
                                array_push($all_scheduled_requests, $data);
                            }
                            $response_array = array(
                                'success' => true,
                                'is_referral_active' => $referral_code_activation,
                                'is_referral_active_txt' => $referral_code_activation_txt,
                                'is_promo_active' => $promotional_code_activation,
                                'is_promo_active_txt' => $promotional_code_activation_txt,
                                'all_scheduled_requests' => $all_scheduled_requests,
                            );
                            $response_code = 200;
                        } else {
                            $response_array = array(
                                'success' => false,
                                'error' => 75,
                                'error_messages' => array(75),
                                'error_code' => 407,
                                'is_referral_active' => $referral_code_activation,
                                'is_referral_active_txt' => $referral_code_activation_txt,
                                'is_promo_active' => $promotional_code_activation,
                                'is_promo_active_txt' => $promotional_code_activation_txt,
                            );
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array(
                            'success' => false,
                            'error' => 52,
                            'error_messages' => array(52),
                            'error_code' => 408,
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                        );
                        $response_code = 200;
                    }
                } else {
                    $ScheduledRequest = ScheduledRequest::where('owner_id', $owner_id)->orderBy('id', 'DESC')->get();
                    foreach ($ScheduledRequest as $data1) {
                        $data['id'] = $data1->id;
                        $data['owner_id'] = $data1->owner_id;
                        $data['latitude'] = $data1->latitude;
                        $data['longitude'] = $data1->longitude;
                        $data['dest_latitude'] = $data1->dest_latitude;
                        $data['dest_longitude'] = $data1->dest_longitude;
                        $data['time_zone'] = $data1->time_zone;
                        $data['src_address'] = $data1->src_address;
                        $data['dest_address'] = $data1->dest_address;
                        $data['promo_code'] = $data1->promo_code;
                        $data['promo_id'] = $data1->promo_id;
                        $pay_mode_txt = "Card";
                        if ($data1->payment_mode) {
                            $pay_mode_txt = "Cash";
                        }
                        $data['payment_mode'] = $data1->payment_mode;
                        $data['pay_mode_txt'] = $pay_mode_txt;
                        $data['server_start_time'] = $data1->server_start_time;
                        $data['start_time'] = $data1->start_time;
                        array_push($all_scheduled_requests, $data);
                    }
                    $response_array = array(
                        'success' => false,
                        'error' => 9,
                        'error_messages' => array(9),
                        'error_code' => 405,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'all_scheduled_requests' => $all_scheduled_requests,
                    );
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function create_request_later() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $date_time = Input::get('datetime');

        // dd(date('Y-m-d h:i:s', strtotime("$date_time + 2 hours")));


        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'datetime' => $date_time,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    'datetime' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                    'datetime.required' => 83,
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations


                    if ($owner_data->debt > 0) {

                        $response_array = array('success' => false, 'error' => 72, 'error_messages' => array(72), 'error_code' => 417);
                        $response_code = 200;
                        $response = Response::json($response_array, $response_code);
                        return $response;
                    }

                    if (Input::has('type')) {
                        $type = Input::get('type');
                        if (!$type) {
                            // choose default type
                            $provider_type = ProviderType::where('is_default', 1)->first();

                            if (!$provider_type) {
                                $type = 1;
                            } else {
                                $type = $provider_type->id;
                            }
                        }


                        $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
                        $typewalkers = DB::select(DB::raw($typequery));
                        //Log::info('typewalkers = ' . print_r($typewalkers, true));
                        foreach ($typewalkers as $key) {
                            $types[] = $key->provider_id;
                        }
                        $typestring = implode(",", $types);
                        //Log::info('typestring = ' . print_r($typestring, true));

                        if ($typestring == '') {
                            /* $driver = Keywords::where('id', 1)->first();
                              $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.','error_messages' => array('No ' . $driver->keyword . ' found matching the service type.'), 'error_code' => 405); */
                            $response_array = array('success' => false, 'error' => 55, 'error_messages' => array(55), 'error_code' => 405);
                            $response_code = 200;
                            return Response::json($response_array, $response_code);
                        }
                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }
                        $query1 = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance and walker.id IN($typestring);";

                        $ssstrings = DB::select(DB::raw($query1));
                        foreach ($ssstrings as $ssstrin) {
                            $ssstri[] = $ssstrin->id;
                        }
                        $ssstring = implode(",", $ssstri);

                        $datewant = new DateTime($date_time);
                        $datetime = $datewant->format('Y-m-d H:i:s');

                        $dategiven = $datewant->sub(new DateInterval('P0Y0M0DT1H59M59S'))->format('Y-m-d H:i:s');
                        $end_time = $datewant->add(new DateInterval('P0Y0M0DT1H59M59S'))->format('Y-m-d H:i:s');


                        /* $setting = Settings::where('key', 'allow_calendar')->first();
                          if ($setting->value == 1)
                          $pvquery = "SELECT distinct provider_id from provider_availability where start <= '" . $datetime . "' and end >= '" . $datetime . "' and provider_id IN($ssstring) and provider_id NOT IN(SELECT confirmed_walker FROM request where request_start_time>='" . $dategiven . "' and request_start_time<='" . $end_time . "');";
                          else */
                        $pvquery = "SELECT id from walker where id IN($ssstring) and id NOT IN(SELECT confirmed_walker FROM request where request_start_time>='" . $dategiven . "' and request_start_time<='" . $end_time . "');";
                        $pvques = DB::select(DB::raw($pvquery));
                        //  dd($pvques);
                        $ssstr = array();
                        foreach ($pvques as $ssstn) {
                            $ssstr[] = $ssstn->provider_id;
                        }
                        $pvque = implode(",", $ssstr);
                        $walkers = array();
                        if ($pvque) {
                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $multiply = 1.609344;
                            } elseif ($unit == 1) {
                                $multiply = 1;
                            }
                            $query = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance and walker.id IN($typestring) and id IN($pvque) order by distance;";

                            $walkers = DB::select(DB::raw($query));
                        }
                        $walker_list = array();

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = $datetime;
                        $request->latitude = $latitude;
                        $request->longitude = $longitude;
                        $request->later = 1;
                        if (Input::has('cod')) {
                            if (Input::get('cod') == 1) {
                                $request->cod = 1;
                            } else {
                                $request->cod = 0;
                            }
                        }
                        $request->save();

                        $reqserv = new RequestServices;
                        $reqserv->request_id = $request->id;
                        $reqserv->type = $type;
                        $reqserv->save();
                    } else {
                        $settings = Settings::where('key', 'default_search_radius')->first();
                        $distance = $settings->value;
                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $multiply = 1.609344;
                        } elseif ($unit == 1) {
                            $multiply = 1;
                        }
                        $query1 = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance;";

                        $ssstrings = DB::select(DB::raw($query1));
                        foreach ($ssstrings as $ssstrin) {
                            $ssstri[] = $ssstrin->id;
                        }
                        $ssstring = implode(",", $ssstri);

                        $datewant = new DateTime($date_time);
                        $datetime = $datewant->format('Y-m-d H:i:s');

                        $dategiven = $datewant->sub(new DateInterval('P0Y0M0DT1H59M59S'))->format('Y-m-d H:i:s');
                        $end_time = $datewant->add(new DateInterval('P0Y0M0DT1H59M59S'))->format('Y-m-d H:i:s');

                        /* $setting = Settings::where('key', 'allow_calendar')->first();
                          if ($setting->value == 1)
                          $pvquery = "SELECT distinct provider_id from provider_availability where start <= '" . $datetime . "' and end >= '" . $datetime . "' and provider_id IN($ssstring) and provider_id NOT IN(SELECT confirmed_walker FROM request where request_start_time>='" . $dategiven . "' and request_start_time<='" . $end_time . "');";
                          else */
                        $pvquery = "SELECT id from walker where id IN($ssstring) and id NOT IN(SELECT confirmed_walker FROM request where request_start_time>='" . $dategiven . "' and request_start_time<='" . $end_time . "');";

                        $pvques = DB::select(DB::raw($pvquery));

                        $ssstr = array();
                        foreach ($pvques as $ssstn) {
                            $ssstr[] = $ssstn->provider_id;
                        }
                        $pvque = implode(",", $ssstr);
                        $walkers = array();
                        if ($pvque) {
                            $settings = Settings::where('key', 'default_distance_unit')->first();
                            $unit = $settings->value;
                            if ($unit == 0) {
                                $multiply = 1.609344;
                            } elseif ($unit == 1) {
                                $multiply = 1;
                            }
                            $query = "SELECT walker.id, ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ,8) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) ,8) <= $distance and id IN($pvque) order by distance;";

                            $walkers = DB::select(DB::raw($query));
                        }
                        $walker_list = array();

                        $owner = Owner::find($owner_id);
                        $owner->latitude = $latitude;
                        $owner->longitude = $longitude;
                        $owner->save();

                        $request = new RideRequest;
                        $request->owner_id = $owner_id;
                        $request->request_start_time = $datetime;
                        $request->latitude = $latitude;
                        $request->longitude = $longitude;
                        $request->save();

                        $reqserv = new RequestServices;
                        $reqserv->request_id = $request->id;
                        $reqserv->save();
                    }
                    $i = 0;
                    $first_walker_id = 0;
                    if ($walkers) {
                        foreach ($walkers as $walker) {
                            $request_meta = new RequestMeta;
                            $request_meta->request_id = $request->id;
                            $request_meta->walker_id = $walker->id;
                            if ($i == 0) {
                                $first_walker_id = $walker->id;
                                $i++;
                            }
                            $request_meta->save();
                        }

                        $req =RideRequest::find($request->id);
                        $req->current_walker = $first_walker_id;
                        $req->save();
                    }
                    $settings = Settings::where('key', 'provider_timeout')->first();
                    $time_left = $settings->value;

                    // Send Notification
                    $walker = Walker::find($first_walker_id);
                    if ($walker) {
                        $msg_array = array();
                        $msg_array['unique_id'] = 3;
                        $msg_array['request_id'] = $request->id;
                        $msg_array['time_left_to_respond'] = $time_left;
                        $owner = Owner::find($owner_id);
                        $request_data = array();
                        $request_data['owner'] = array();
                        $request_data['owner']['name'] = $owner->contact_name;
                        $request_data['owner']['picture'] = $owner->picture;
                        $request_data['owner']['phone'] = $owner->phone;
                        $request_data['owner']['address'] = $owner->address;
                        $request_data['owner']['latitude'] = $request->latitude;
                        $request_data['owner']['longitude'] = $request->longitude;
                        $request_data['owner']['rating'] = $owner->rate;
                        $request_data['owner']['num_rating'] = $owner->rate_count;
                        /* $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ? : 0;
                          $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count(); */
                        $date_want = new DateTime($date_time);
                        $datetime1 = $date_want->format('Y-m-d H:i:s');
                        $request_data['datetime'] = $datetime1;
                        $request_data['dog'] = array();
                        if ($dog = Dog::find($owner->dog_id)) {

                            $request_data['dog']['name'] = $dog->name;
                            $request_data['dog']['age'] = $dog->age;
                            $request_data['dog']['breed'] = $dog->breed;
                            $request_data['dog']['likes'] = $dog->likes;
                            $request_data['dog']['picture'] = $dog->image_url;
                        }
                        $msg_array['request_data'] = $request_data;

                        $title = "New Request";
                        $message = $msg_array;
                        //Log::info('first_walker_id = ' . print_r($first_walker_id, true));
                        //Log::info('New request = ' . print_r($message, true));
                        /* don't do json_encode in above line because if */
                        send_notifications($first_walker_id, "walker", $title, $message);
                    }
                    // Send SMS 
                    $settings = Settings::where('key', 'sms_request_created')->first();
                    $pattern = $settings->value;
                    $pattern = str_replace('%user%', $owner_data->contact_name, $pattern);
                    $pattern = str_replace('%id%', $request->id, $pattern);
                    $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
                    sms_notification(1, 'admin', $pattern);

                    // send email
                    /* $settings = Settings::where('key', 'email_new_request')->first();
                      $pattern = $settings->value;
                      $pattern = str_replace('%id%', $request->id, $pattern);
                      $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
                      $subject = "New Request Created";
                      email_notification(1, 'admin', $pattern, $subject); */
                    $settings = Settings::where('key', 'admin_email_address')->first();
                    $admin_email = $settings->value;
                    $follow_url = web_url() . "/booking/signin";
                    $pattern = array('admin_email' => $admin_email, 'trip_id' => $request->id, 'follow_url' => $follow_url);
                    $subject = "Ride Booking Request";
                    email_notification(1, 'admin', $pattern, $subject, 'new_request', null);

                    $response_array = array(
                        'success' => true,
                        'request_id' => $request->id,
                    );
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $var = Keywords::where('id', 2)->first();
                      $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function eta() {

        $secret = Input::get('secret');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'secret' => $secret,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'secret' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'secret.required' => 'Security key is required.',
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $request = RideRequest::where('security_key', $secret)->first();
                    if ($request) {

                        if ($request->is_started == 0) {
                            $walker = Walker::find($request->confirmed_walker);
                            $distance = 0;
                        } else {
                            $walker = WalkLocation::where('request_id', $request->id)->orderBy('created_at', 'desc')->first();
                            $distance = WalkLocation::where('request_id', $request->id)->max('distance');
                        }

                        $settings = Settings::where('key', 'default_distance_unit')->first();
                        $unit = $settings->value;
                        if ($unit == 0) {
                            $unit_set = 'kms';
                        } elseif ($unit == 1) {
                            $unit_set = 'miles';
                        }
                        $distance = convert($distance, $unit);


                        $response_array = array(
                            'success' => true,
                            'latitude' => $walker->latitude,
                            'longitude' => $walker->longitude,
                            'destination_latitude' => $request->D_latitude,
                            'destination longitude' => $request->D_longitude,
                            'distance' => (string) $distance,
                            'unit' => $unit_set
                        );

                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    /* $var = Keywords::where('id', 2)->first();
                      $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found','error_messages' => array('' . $var->keyword . ' ID not Found'), 'error_code' => 410); */
                    $response_array = array('success' => false, 'error' => 53, 'error_messages' => array(53), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function check_promo_code() {
        $promo_code = Input::get('promo_code');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'promo_code' => $promo_code,
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'promo_code' => 'required',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'promo_code.required' => 'Promotional code must be required.',
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $settings = Settings::where('key', 'promotional_code_activation')->first();
                    $prom_act = $settings->value;
                    if ($prom_act) {
                        // check promo code
                        $check_code = PromoCodes::where('coupon_code', $promo_code)->where('uses', '>', 0)->first();
                        if ($check_code != NULL) {
                            if ($check_code->state == 1 && date('Y-m-d H:i:s', strtotime($check_code->expiry)) > date('Y-m-d H:i:s') && date('Y-m-d H:i:s', strtotime($check_code->start_date)) <= date('Y-m-d H:i:s')) {
                                if ($check_code->type == 1) {
                                    $discount = $check_code->value . " %";
                                } elseif ($check_code->type == 2) {
                                    $discount = "$ " . $check_code->value;
                                }
                                $response_array = array('success' => true, 'discount' => $discount);
                            } else {
                                $response_array = array('success' => false, 'error' => 61, 'error_messages' => array(61), 'error_code' => 418);
                            }
                        } else {
                            $response_array = array('success' => false, 'error' => 61, 'error_messages' => array(61), 'error_code' => 419);
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 68, 'error_messages' => array(68), 'error_code' => 419);
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                }
            } else {
                $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
            }
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function payment_select() {
        /*
         * 0=payment with credit card
         * 1=payment with Cash
         */
        $payment_opt = Input::get('payment_opt');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'payment_select' => $payment_opt,
                    'owner_id' => $owner_id,
                        ), array(
                    'payment_select' => 'required',
                    'owner_id' => 'required|integer'
                        ), array(
                    'payment_select.required' => 'Payment type must be required.',
                    'owner_id.required' => 6
                        )
        );
        //echo "test";

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $request = RideRequest::where('owner_id', '=', $owner_id)->where('status', '=', 0)->orderBy('created_at', 'desc')->first();
            if ($request) {
                if (isset($request->id)) {
                    /* $request =RideRequest::find($request->id);
                      $request->payment_mode = $payment_opt;
                      $request->save(); */
                  RideRequest::where('id', $request->id)->update(array('payment_mode' => $payment_opt));

                    /* Owner::where('id', $owner_id)->update(array('payment_select' => $payment_opt)); */
                    $response_array = array('success' => true, 'error' => 85, 'error_messages' => array(85), 'error_code' => 407);
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 86, 'error_messages' => array(86), 'error_code' => 507);
                    $response_code = 200;
                }
            } else {
                $response_array = array('success' => false, 'error' => 86, 'error_messages' => array(86), 'error_code' => 507);
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_provider_list() {
        $latitude = Input::get('usr_lat');
        $longitude = Input::get('user_long');


        $validator = Validator::make(
                        array(
                    'usr_lat' => $latitude,
                    'user_long' => $longitude,
                        ), array(
                    'usr_lat' => 'required',
                    'user_long' => 'required',
                        ), array(
                    'usr_lat.required' => 49,
                    'user_long.required' => 49,
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {

            $settings = Settings::where('key', 'default_search_radius')->first();
            $distance = $settings->value;
            $settings = Settings::where('key', 'default_distance_unit')->first();
            $unit = $settings->value;
            if ($unit == 0) {
                $multiply = 1.609344;
            } elseif ($unit == 1) {
                $multiply = 1;
            }
            $query = "SELECT *, "
                    . "ROUND(" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ,8) as distance "
                    . "from walker "
                    . " where deleted_at IS NULL and "
                    . "is_available = 1 and "
                    . "is_active = 1 and "
                    . "is_approved = 1 and "
                    . "TIMESTAMPDIFF(SECOND, lastseen, now()) < 300 AND "
                    . "ROUND((" . $multiply . " * 3956 * acos( cos( radians('$latitude') ) * "
                    . "cos( radians(latitude) ) * "
                    . "cos( radians(longitude) - radians('$longitude') ) + "
                    . "sin( radians('$latitude') ) * "
                    . "sin( radians(latitude) ) ) ) ,8) <= $distance "
                    . "order by distance "
                    . "LIMIT 5";
            Log::info($query);
            $walkers_list = DB::select(DB::raw($query));

            $walker_data = array();
            if ($walkers_list) {

                foreach ($walkers_list as $walkers) {
                    $walker_list = array();
                    $walker_list['id'] = $walkers->id;
                    $walker_list['contact_name'] = $walkers->contact_name;
                    $walker_list['phone'] = $walkers->phone;
                    $walker_list['email'] = $walkers->email;
                    $walker_list['bio'] = $walkers->bio;
                    $walker_list['address'] = $walkers->address;
                    $walker_list['state'] = $walkers->state;
                    $walker_list['country'] = $walkers->country;
                    $walker_list['zipcode'] = $walkers->zipcode;
                    $walker_list['latitude'] = $walkers->latitude;
                    $walker_list['longitude'] = $walkers->longitude;
                    $walker_list['type'] = $walkers->type;
                    $walker_list['car_model'] = $walkers->car_model;
                    $walker_list['car_number'] = $walkers->car_number;
                    $walker_list['bearing'] = $walkers->bearing;
                    array_push($walker_data, $walker_list);
                }

                if (!empty($walker_data)) {
                    $response_array = array(
                        'success' => true,
                        'walker_list' => $walker_data,
                    );
                } else {
                    $response_array = array(
                        'success' => false,
                        'error' => 81,
                        'error_messages' => array(81),
                        'error_code' => 411,
                        'walker_list' => $walker_data,
                    );
                }
                $response_code = 200;
            } else {
                $response_array = array(
                    'success' => false,
                    'error' => 81,
                    'error_messages' => array(81),
                    'error_code' => 411,
                    'walker_list' => $walker_data,
                );
                $response_code = 201;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function user_set_destination() {
        $request_id = Input::get('request_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $dest_lat = Input::get('dest_lat');
        $dest_long = Input::get('dest_long');
        $dest_address = "Address Not Available";
        if (Input::has('dest_address')) {
            $dest_address = trim(Input::get('dest_address'));
        }

        $validator = Validator::make(
                        array(
                    'request_id' => $request_id,
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'dest_lat' => $dest_lat,
                    'dest_long' => $dest_long,
                        ), array(
                    'request_id' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'dest_lat' => 'required',
                    'dest_long' => 'required',
                        ), array(
                    'request_id.required' => 19,
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'dest_lat.required' => 49,
                    'dest_long.required' => 49,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    if ($request =RideRequest::find($request_id)) {

                        if ($request->owner_id == $owner_data->id) {
                            if ($request->promo_id == 0) {
                              RideRequest::where('id', $request_id)->update(array('D_latitude' => $dest_lat, 'D_longitude' => $dest_long, 'dest_address' => $dest_address));
                                $response_array = array(
                                    'success' => true,
                                    'error' => 87,
                                    'error_messages' => array(87),
                                );
                                $response_code = 200;
                            } else {
                                $promo_data = PromoCodes::where('id', $request->promo_id)->first();
                                if ($promo_data) {
                                    /* if ($promos->is_event) {

                                      $event_data = UserEvents::where('id', $promo_data->event_id)->first();
                                    RideRequest::where('id', $request_id)->update(array('D_latitude' => $event_data->event_latitude, 'D_longitude' => $event_data->event_longitude, 'dest_address' => $event_data->event_place_address));
                                      $response_array = array('success' => false, 'error' => 'Sorry, You can\'t re-set address of event.', 'error_messages' => array('Sorry, You can\'t re-set address of event.'), 'error_code' => 512);
                                      $response_code = 200;
                                      } else { */
                                  RideRequest::where('id', $request_id)->update(array('D_latitude' => $dest_lat, 'D_longitude' => $dest_long, 'dest_address' => $dest_address));
                                    $response_array = array(
                                        'success' => true,
                                        'error' => 87,
                                        'error_messages' => array(87),
                                    );
                                    $response_code = 200;
                                    /* } */
                                } else {
                                  RideRequest::where('id', $request_id)->update(array('D_latitude' => $dest_lat, 'D_longitude' => $dest_long, 'dest_address' => $dest_address));
                                    $response_array = array(
                                        'success' => true,
                                        'error' => 87,
                                        'error_messages' => array(87),
                                    );
                                    $response_code = 200;
                                }
                            }
                            if ($request->current_walker) {
                                $msg_array = array();
                                $msg_array['request_id'] = $request_id;
                                $msg_array['unique_id'] = 4;

                                $last_destination =RideRequest::find($request_id);
                                $owner = Owner::find($owner_id);
                                $request_data = array();
                                $request_data['owner'] = array();
                                $request_data['owner']['name'] = $owner->contact_name;
                                $request_data['owner']['picture'] = $owner->picture;
                                $request_data['owner']['phone'] = $owner->phone;
                                $request_data['owner']['address'] = $owner->address;
                                $request_data['owner']['latitude'] = $request->latitude;
                                $request_data['owner']['longitude'] = $request->longitude;
                                $request_data['owner']['dest_latitude'] = $last_destination->D_latitude;
                                $request_data['owner']['dest_longitude'] = $last_destination->D_longitude;
                                $request_data['owner']['rating'] = $owner->rate;
                                $request_data['owner']['num_rating'] = $owner->rate_count;

                                $request_data['dog'] = array();
                                if ($dog = Dog::find($owner->dog_id)) {
                                    $request_data['dog']['name'] = $dog->name;
                                    $request_data['dog']['age'] = $dog->age;
                                    $request_data['dog']['breed'] = $dog->breed;
                                    $request_data['dog']['likes'] = $dog->likes;
                                    $request_data['dog']['picture'] = $dog->image_url;
                                }
                                $msg_array['request_data'] = $request_data;

                                $title = "Set Destination";
                                $message = $msg_array;
                                if ($request->confirmed_walker == $request->current_walker) {
                                    send_notifications($request->confirmed_walker, "walker", $title, $message);
                                }
                            }
                        } else {
                            $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with Owner ID', 'error_messages' => array('Request ID doesnot matches with Owner ID'), 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 52, 'error_messages' => array(52), 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function user_create_event() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $event_name = ucwords(Input::get('name'));
        $time_zone = trim(Input::get('time_zone'));
        $start_time = trim(Input::get('start_time'));
        $member_number = trim(Input::get('members'));
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $pre_pay_for_each_member = 0;
        if (Input::has('amount')) {
            $pre_pay_for_each_member = trim(Input::get('amount'));
        }
        $address = "Address Not Available";
        if (Input::has('address')) {
            $address = trim(Input::get('address'));
        }

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'name' => $event_name,
                    'event_date_time' => $start_time,
                    'member_number' => $member_number,
                    'event_time_zone' => $time_zone,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'latitude' => 'required',
                    'longitude' => 'required',
                    'name' => 'required',
                    'event_date_time' => 'required',
                    'member_number' => 'required|integer',
                    'event_time_zone' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'latitude.required' => 49,
                    'longitude.required' => 49,
                    'name.required' => 'Event name is missing.',
                    'event_date_time.required' => 'Event Date & time are missing.',
                    'member_number.required' => 'Numbe of Event Members is missing.',
                    'event_time_zone.required' => 'Event country timezone is missing.',
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $card_count = Payment::where('owner_id', '=', $owner_id)->count();
                    if ($card_count <= 0) {
                        $response_array = array('success' => false, 'error' => 59, 'error_messages' => array(59), 'error_code' => 417);
                        $response_code = 200;
                        $response = Response::json($response_array, $response_code);
                        return $response;
                    }
                    $all_events = array();
                    /* TIME ZONE BASED TIME CONVERSION */
                    date_default_timezone_set($time_zone);
                    $datetime = new DateTime($start_time);
                    $datetime->format('Y-m-d H:i:s');
                    foreach ($datetime as $row1) {
                        $start_time = $row1; // returns 2014-06-04 15:00
                        break;     // stops at the first position
                    }
                    $timeEurope = new DateTimeZone(Config::get('app.timezone'));
                    $datetime->setTimezone($timeEurope);
                    $datetime->format('Y-m-d H:i:s');
                    foreach ($datetime as $row) {
                        $server_time = $row; // returns 2014-06-04 15:00
                        break;     // stops at the first position
                    }
                    $chk_dt = date_time_differ_two($server_time);
                    /* print_r($chk_dt); */
                    /* if ($chk_dt->invert != 0 && ($chk_dt->d >= 14 || $chk_dt->m > 0)) { */
                    if (1) {
                        /* TIME ZONE BASED TIME CONVERSION END */
                        $new_event = new UserEvents;
                        $new_event->owner_id = $owner_id;
                        $new_event->event_name = $event_name;
                        $new_event->start_time = $start_time;
                        $new_event->server_start_time = $server_time;
                        $new_event->event_place_address = $address;
                        $new_event->event_total_members = $member_number;
                        $new_event->event_latitude = $latitude;
                        $new_event->event_longitude = $longitude;
                        $new_event->time_zone = $time_zone;
                        $new_event->event_pre_pay_for_each_member = $pre_pay_for_each_member;
                        $new_event->event_pre_pay_total = $pre_pay_for_each_member * $member_number;
                        $new_event->save();
                        /* for ($loop = 0; $loop < $member_number; $loop++) { */
                        $promo = new PromoCodes;
                        regenerate1:
                        $unic_code = my_random6_number();
                        if (PromoCodes::where('coupon_code', '=', $unic_code)->count()) {
                            goto regenerate1;
                        }
                        $promo_start = strtotime($server_time) - (60 * 60 * 3);
                        $promo_start = date('Y-m-d H:i:s', $promo_start);
                        $promo_end = strtotime($server_time) + (60 * 60 * 3);
                        $promo_end = date('Y-m-d H:i:s', $promo_end);
                        $promo->coupon_code = $unic_code;
                        $promo->value = $pre_pay_for_each_member;
                        $promo->type = 2;
                        /* $promo->uses = 1; */
                        $promo->uses = $member_number;
                        $promo->start_date = $promo_start;
                        $promo->expiry = $promo_end;
                        $promo->state = 1;
                        $promo->is_event = 1;
                        $promo->event_id = $new_event->id;
                        $promo->save();
                        /* } */
                        $userevents = UserEvents::select('user_events.*', 'promo_codes.coupon_code', 'promo_codes.uses')
                                ->leftJoin('promo_codes', 'user_events.id', '=', 'promo_codes.event_id')
                                ->where('user_events.owner_id', $owner_id)
                                ->orderBy('user_events.id', 'DESC')
                                ->get();
                        /* $userevents = UserEvents::select('user_events.*')
                          ->where('user_events.owner_id', $owner_id)
                          ->orderBy('user_events.id', 'DESC')
                          ->get(); */
                        foreach ($userevents as $data1) {
                            $data['id'] = $data1->id;
                            $data['owner_id'] = $data1->owner_id;
                            $data['event_name'] = $data1->event_name;
                            $data['start_time'] = $data1->start_time;
                            $data['server_start_time'] = $data1->server_start_time;
                            $data['event_place_address'] = $data1->event_place_address;
                            $data['event_total_members'] = $data1->event_total_members;
                            $data['event_latitude'] = $data1->event_latitude;
                            $data['event_longitude'] = $data1->event_longitude;
                            $data['time_zone'] = $data1->time_zone;
                            $data['event_pre_pay_for_each_member'] = sprintf2($data1->event_pre_pay_for_each_member, 2);
                            $data['event_pre_pay_total'] = sprintf2($data1->event_pre_pay_total, 2);
                            $data['promo_code'] = $data1->coupon_code;
                            $data['un_used_promo'] = $data1->uses;
                            array_push($all_events, $data);
                        }
                        /* PDF GENERATOR CODE */
                        $pdf = App::make('dompdf');

                        $parameterr = array();
                        $parameter['rec_email'] = trim($owner_data->email);
                        $parameter['event_admin_email'] = $owner_data->email;
                        $parameter['sender_name'] = $owner_data->contact_name;
                        $parameter['event_name'] = $event_name;
                        $parameter['event_bonus'] = $pre_pay_for_each_member;
                        $parameter['event_address'] = $address;
                        $parameter['event_promo'] = $unic_code;
                        $parameter['event_time'] = $start_time;
                        $pdf = PDF::loadView('event_pass_pdf', $parameter)->setPaper('letter')->setOrientation('portrait')->setWarnings(false);
                        /* return $pdf->download($weekend . " " . 'weekly_report.pdf'); */
                        $output = $pdf->output();
                        $file_path = public_path() . '/uploads/' . time() . '.pdf';
                        $t = file_put_contents($file_path, $output);
                        /* PDF GENERATOR CODE END */

                        $pattern = array(
                            'rec_email' => trim($owner_data->email),
                            'event_admin_email' => $owner_data->email,
                            'sender_name' => $owner_data->contact_name,
                            'event_name' => $event_name,
                            'event_bonus' => $pre_pay_for_each_member,
                            'event_address' => $address,
                            'event_promo' => $unic_code,
                            'event_time' => $start_time,
                        );
                        $subject = ucwords(Config::get('app.website_title')) . " Invitation pass code for " . ucwords($event_name);
                        email_notification(trim($owner_data->email), 'invite', $pattern, $subject, 'invite', "imp", $file_path);


                        $response_array = array(
                            'success' => true,
                            /* 'path' => $file_path, */
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'all_scheduled_requests' => $all_events,
                        );
                        $response_code = 200;
                        unlink_image($file_path);
                    } else {
                        $userevents = UserEvents::select('user_events.*', 'promo_codes.coupon_code', 'promo_codes.uses')
                                ->leftJoin('promo_codes', 'user_events.id', '=', 'promo_codes.event_id')
                                ->where('user_events.owner_id', $owner_id)
                                ->orderBy('user_events.id', 'DESC')
                                ->get();
                        /* $userevents = UserEvents::select('user_events.*')
                          ->where('user_events.owner_id', $owner_id)
                          ->orderBy('user_events.id', 'DESC')
                          ->get(); */
                        foreach ($userevents as $data1) {
                            $data['id'] = $data1->id;
                            $data['owner_id'] = $data1->owner_id;
                            $data['event_name'] = $data1->event_name;
                            $data['start_time'] = $data1->start_time;
                            $data['server_start_time'] = $data1->server_start_time;
                            $data['event_place_address'] = $data1->event_place_address;
                            $data['event_total_members'] = $data1->event_total_members;
                            $data['event_latitude'] = $data1->event_latitude;
                            $data['event_longitude'] = $data1->event_longitude;
                            $data['time_zone'] = $data1->time_zone;
                            $data['event_pre_pay_for_each_member'] = sprintf2($data1->event_pre_pay_for_each_member, 2);
                            $data['event_pre_pay_total'] = sprintf2($data1->event_pre_pay_total, 2);
                            $data['promo_code'] = $data1->coupon_code;
                            $data['un_used_promo'] = $data1->uses;
                            array_push($all_events, $data);
                        }
                        $response_array = array(
                            'success' => false,
                            'error' => 88,
                            'error_messages' => array(88),
                            'error_code' => 405,
                            'is_referral_active' => $referral_code_activation,
                            'is_referral_active_txt' => $referral_code_activation_txt,
                            'is_promo_active' => $promotional_code_activation,
                            'is_promo_active_txt' => $promotional_code_activation_txt,
                            'all_scheduled_requests' => $all_events,);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function user_get_event() {
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $all_events = array();
                    /* print_r($chk_dt); */
                    $userevents = UserEvents::select('user_events.*', 'promo_codes.coupon_code', 'promo_codes.uses')
                            ->leftJoin('promo_codes', 'user_events.id', '=', 'promo_codes.event_id')
                            ->where('user_events.owner_id', $owner_id)
                            ->orderBy('user_events.id', 'DESC')
                            ->get();
                    foreach ($userevents as $data1) {
                        $data['id'] = $data1->id;
                        $data['owner_id'] = $data1->owner_id;
                        $data['event_name'] = $data1->event_name;
                        $data['start_time'] = $data1->start_time;
                        $data['server_start_time'] = $data1->server_start_time;
                        $data['event_place_address'] = $data1->event_place_address;
                        $data['event_total_members'] = $data1->event_total_members;
                        $data['event_latitude'] = $data1->event_latitude;
                        $data['event_longitude'] = $data1->event_longitude;
                        $data['time_zone'] = $data1->time_zone;
                        $data['event_pre_pay_for_each_member'] = sprintf2($data1->event_pre_pay_for_each_member, 2);
                        $data['event_pre_pay_total'] = sprintf2($data1->event_pre_pay_total, 2);
                        $data['promo_code'] = $data1->coupon_code;
                        $data['un_used_promo'] = $data1->uses;
                        array_push($all_events, $data);
                    }


                    $response_array = array(
                        'success' => True,
                        'is_referral_active' => $referral_code_activation,
                        'is_referral_active_txt' => $referral_code_activation_txt,
                        'is_promo_active' => $promotional_code_activation,
                        'is_promo_active_txt' => $promotional_code_activation_txt,
                        'all_scheduled_requests' => $all_events,);
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function invite_members() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $event_id = Input::get('event_id');
        $email = Input::get('email');
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'event_id' => $event_id,
                    'email' => $email,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'event_id' => 'required',
                    'email' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'event_id.required' => 'Event id was missing.',
                    'email.required' => 'Invited user\'s email id was missing.',
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $invite_emails = array();
                    $email = explode(',', rtrim($email, ","));

                    $event_data = UserEvents::where('id', '=', $event_id)->first();
                    $promo_code_data = PromoCodes::where('event_id', '=', $event_id)->first();

                    $event_admin_email = $owner_data->email;
                    $sender_name = $owner_data->contact_name;
                    $event_name = $event_data->event_name;
                    $event_bonus = $event_data->event_pre_pay_for_each_member;
                    $event_address = $event_data->event_place_address;
                    $event_start_time = $event_data->start_time;
                    $event_promo = $promo_code_data->coupon_code;
                    $check_limit = EventMembers::where('event_id', '=', $event_id)->count();
                    if ($check_limit >= $event_data->event_total_members) {
                        $response_array = array('success' => false, 'error' => 89, 'error_messages' => array(89), 'error_code' => 405, 'limit' => $check_limit,);
                        $response_code = 200;
                    } else {
                        foreach ($email as $email_data) {
                            if ($check_limit >= $event_data->event_total_members) {
                                $response_array = array('success' => false, 'error' => 89, 'error_messages' => array(89), 'error_code' => 405, 'limit' => $check_limit,);
                                $response_code = 200;
                            } else {
                                $count_is_available = EventMembers::where('email', '=', $email_data)->where('event_id', '=', $event_id)->where('owner_id', '=', $owner_id)->count();
                                if (!$count_is_available) {
                                    $event_mem = new EventMembers;
                                    $event_mem->event_id = $event_id;
                                    $event_mem->owner_id = $owner_id;
                                    $event_mem->email = trim($email_data);
                                    $event_mem->phone = 0;
                                    $event_mem->save();

                                    $pattern = array(
                                        'rec_email' => trim($email_data),
                                        'event_admin_email' => $event_admin_email,
                                        'sender_name' => $sender_name,
                                        'event_name' => $event_name,
                                        'event_bonus' => $event_bonus,
                                        'event_address' => $event_address,
                                        'event_promo' => $event_promo,
                                        'event_time' => $event_start_time,
                                    );
                                    array_push($invite_emails, trim($email_data));
                                    $subject = ucwords(Config::get('app.website_title')) . " Invitation for " . ucwords($event_name);
                                    email_notification(trim($email_data), 'invite', $pattern, $subject, 'invite', "imp");
                                }
                                $check_limit = EventMembers::where('event_id', '=', $event_id)->count();
                            }
                        }
                        $response_array = array('success' => TRUE, 'emails' => $invite_emails, 'limit' => $check_limit);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function user_delete_event() {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $event_id = Input::get('event_id');
        $validator = Validator::make(
                        array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                    'event_id' => $event_id,
                        ), array(
                    'token' => 'required',
                    'owner_id' => 'required|integer',
                    'event_id' => 'required',
                        ), array(
                    'token.required' => '',
                    'owner_id.required' => 6,
                    'event_id.required' => 98,
                        )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 8, 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            // SEND REFERRAL & PROMO INFO
            $settings = Settings::where('key', 'referral_code_activation')->first();
            $referral_code_activation = $settings->value;
            if ($referral_code_activation) {
                $referral_code_activation_txt = "referral on";
            } else {
                $referral_code_activation_txt = "referral off";
            }

            $settings = Settings::where('key', 'promotional_code_activation')->first();
            $promotional_code_activation = $settings->value;
            if ($promotional_code_activation) {
                $promotional_code_activation_txt = "promo on";
            } else {
                $promotional_code_activation_txt = "promo off";
            }
            // SEND REFERRAL & PROMO INFO
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $event_id = Input::get('event_id');
                    if ($promo_code_data = PromoCodes::where('event_id', '=', $event_id)->first()) {
                        $current_time = time('Y-m-d H:i:s') + (12 * 60 * 60);
                        $start_dt = strtotime($promo_code_data->start_date);
                        if ($current_time < $start_dt) {
                            PromoCodes::where('event_id', '=', $event_id)->delete();
                            UserEvents::where('id', '=', $event_id)->where('owner_id', '=', $owner_id)->delete();
                            $all_events = array();
                            /* print_r($chk_dt); */
                            $userevents = UserEvents::select('user_events.*', 'promo_codes.coupon_code', 'promo_codes.uses')
                                    ->leftJoin('promo_codes', 'user_events.id', '=', 'promo_codes.event_id')
                                    ->where('user_events.owner_id', $owner_id)
                                    ->orderBy('user_events.id', 'DESC')
                                    ->get();
                            foreach ($userevents as $data1) {
                                $data['id'] = $data1->id;
                                $data['owner_id'] = $data1->owner_id;
                                $data['event_name'] = $data1->event_name;
                                $data['start_time'] = $data1->start_time;
                                $data['server_start_time'] = $data1->server_start_time;
                                $data['event_place_address'] = $data1->event_place_address;
                                $data['event_total_members'] = $data1->event_total_members;
                                $data['event_latitude'] = $data1->event_latitude;
                                $data['event_longitude'] = $data1->event_longitude;
                                $data['time_zone'] = $data1->time_zone;
                                $data['event_pre_pay_for_each_member'] = sprintf2($data1->event_pre_pay_for_each_member, 2);
                                $data['event_pre_pay_total'] = sprintf2($data1->event_pre_pay_total, 2);
                                $data['promo_code'] = $data1->coupon_code;
                                $data['un_used_promo'] = $data1->uses;
                                array_push($all_events, $data);
                            }
                            $response_array = array(
                                'success' => True,
                                /* 'date' => $current_time,
                                  'start_dt' => $start_dt, */
                                'error_messages' => array(101),
                                'is_referral_active' => $referral_code_activation,
                                'is_referral_active_txt' => $referral_code_activation_txt,
                                'is_promo_active' => $promotional_code_activation,
                                'is_promo_active_txt' => $promotional_code_activation_txt,
                                'all_scheduled_requests' => $all_events,);
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 99, 'error_messages' => array(99), 'error_code' => 405);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 100, 'error_messages' => array(100), 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 9, 'error_messages' => array(9), 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => 10, 'error_messages' => array(10), 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 11, 'error_messages' => array(11), 'error_code' => 406);
                }
                $response_code = 200;
            }
        }


        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function GetExpectedRideAmount($start_lat,$start_long,$end_lat,$end_long,$service_type,$is_wheelchair){

        $geocode=file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$start_lat.','.$start_long.'&destinations='.$end_lat.','.$end_long.'&mode=driving&language=pl-PL');
        $output= json_decode($geocode,true);


        $durationInMinutes = $output['rows'][0]['elements'][0]['duration']['value'] / 60;
        $distanceInMiles   = $output['rows'][0]['elements'][0]['distance']['value'] / (1000 * 1.609344);

        $distanceInMiles = round($distanceInMiles,2);

        $pt = ProviderType::where('id', $service_type)->first();
        $base_price = $pt->base_price;
        $price_per_unit_distance = $pt->price_per_unit_distance;
        $price_per_unit_time = $pt->price_per_unit_time;

        $wheelchair_cost = 0;

        $distance_cost = $price_per_unit_distance * $distanceInMiles;
        $time_cost = $price_per_unit_time * $durationInMinutes;


        $ride_total = $base_price + $distance_cost + $time_cost;
        if ($is_wheelchair == 1) {
            $ride_total = $ride_total + 10;
            $wheelchair_cost = "10.00";
        }
        $amount = round($ride_total,2);

        return array('amount' => $amount, 'distance' => $distanceInMiles, 'time' => $durationInMinutes);
    }

}
