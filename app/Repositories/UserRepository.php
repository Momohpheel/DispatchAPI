<?php
namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
use App\Traits\Logs;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\DropOff;
use App\Models\Address;
use App\Models\Vehicle;
use App\Models\Rating;
use App\Models\Rider;
use App\Models\Payment;
use App\Models\History;
use App\Models\TransactionLogs;
use App\Models\OperatingHours as OpHour;
use App\Traits\Response;
use App\Models\routeCosting as RouteCosting;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Socialite;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use App\Mail\ForgotPassword;

class UserRepository implements UserRepositoryInterface{

    use Response, Logs;



    public function onboard(Request $request){
        $validated = $request->validate([
            'partner' => 'required|string',
        ]);

        $partner = Partner::where('code_name', $validated['partner'])->first();

        if (!empty($partner)){
            $data = [
                "name" => $partner->name,
                "phone" => $partner->phone,
                "email" => $partner->email,
                "id" => $partner->id,
                "code_name" => $partner->code_name,
                "image" => $partner->image

            ];
            return $this->success(false, 'User Onboarded successfully', $data, 200);
        }else{
            return $this->error(true, "Partner doesn't exist" , 400);
        }


    }


    public function profile(Request $request){
        try{

            $validated = $request->validate([
                'name' => "required|string",
                'phone' => "required|string",
                'email' => "required|string",
                "password" => "required|string"
            ]);




            $check_user = User::where('email', $validated['email'])->where('phone', $validated['phone'])->first();
            if (!$check_user){
                $user = new User;
                $user->name = $validated['name'];
                $user->phone = $validated['phone'];
                $user->email = $validated['email'];
                $user->password = Hash::make($validated['password']);
                $user->save();

                $access_token = $user->createToken('authToken')->accessToken;
                $data = [
                    "name" => $user->name,
                    "phone" => $user->phone,
                    "email" => $user->email,
                    "id" => $user->id,
                    "image" => $user->image,
                    "access_token" => $access_token
                ];
                $this->history('Profile', $data['name']." created their profile", $data['id'], 'user');

                return $this->success(false, "User created", $data, 201);
            }else{
                return $this->error(true, "User exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error creating user", 400);
        }

    }

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'email' => "required|string",
                "password" => "required|string"
            ]);

            $user = User::where('email', $validated['email'])->first();
            if ($user){
                $check = Hash::check($validated['password'], $user->password);
                if ($check){
                    $access_token = $user->createToken('authToken')->accessToken;
                    $data = [
                        "name" => $user->name,
                        "phone" => $user->phone,
                        "email" => $user->email,
                        "id" => $user->id,
                        "image" => $user->image,
                        "access_token" => $access_token
                    ];
                    return $this->success(false, "User found", $data, 200);
                }else{
                    return $this->error(true, "Incorrect Password", 400);
                }
            }else{
                return $this->error(true, "User with email not found", 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging user", 400);
        }
    }

    public function forgotPassword(Request $request){
        try{
            $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            $token = Str::random(64);

            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
              ]);

           Mail::to($request->email)->send(new ForgotPassword($token));

            return $this->success("Email to reset password has been sent...", [], 200);

        }catch(Exception $e){
            return $this->error(true, "Error Occured: $e->getMessage()", 400);
        }
    }


    public function resetPassword($token){
        try{

            $request->validate([
                'email' => 'required|email|exists:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required'
            ]);

            $updatePassword = DB::table('password_resets')
                                ->where([
                                  'email' => $request->email,
                                  'token' => $token
                                ])
                                ->first();

            if(!$updatePassword){
                return $this->error(true, "Invalid token!", 400);
            }

            $user = User::where('email', $request->email)
                        ->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where(['email'=> $request->email])->delete();

            return $this->success("Reset Password Successfull...", $user, 200);

        }catch(Exception $e){
            return $this->error(true, "Error Occured: $e->getMessage()", 400);
        }
    }

    public function uploadImage(Request $request){
        try{

            $validated = $request->validate([
                'image' => 'required|image|max:2000|mimes:png,jpg'
            ]);

            if ($request->hasFile('image')){
                $image_name = $validated['image']->getClientOriginalName();
                $image_name_withoutextensions =  pathinfo($image_name, PATHINFO_FILENAME);
                $name = str_replace(" ", "", $image_name_withoutextensions);
                $image_extension = $validated['image']->getClientOriginalExtension();
                $image_to_store = $name . '_' . time() . '.' . $image_extension;
                $path = $validated['image']->storeAs('public/images', trim($image_to_store));
            }

            $check_user = User::where('id', auth()->user()->id)->first();
            if ($check_user){

                $check_user->image = isset($image_to_store) ? env('APP_URL') .'/storage/images/'.$image_to_store : $check_user->image;
                $check_user->save();

                return $this->success(false, "user profile image uploaded", $check_user, 200);

            }else{
                return $this->error(true, "Unauthenticated", 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Unable to upload image", 400);
        }
    }


    public function updateProfile(Request $request){
        try{
            $validated = $request->validate([
                'name' => "required|string",
                'phone' => "required|string",
                'email' => 'required|email'
            ]);


            $check_user = User::where('id', auth()->user()->id)->first();
            if ($check_user){
                $check_user->name = $validated['name'] ?? $check_user->name;
                $check_user->phone = $validated['phone'] ?? $check_user->phone;
                $check_user->email = $validated['email'] ?? $check_user->email;
                $check_user->save();

                return $this->success(false, "user profile updated", $check_user, 200);
            }else{
                return $this->error(true, "Unauthenticated", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function getProfile(){
        try{

            $profile = User::where('id', auth()->user()->id)->first();
            if ($profile){

                $data = [
                    "name" => $profile->name,
                    "phone" => $profile->phone,
                    "email" => $profile->email,
                    "image" => $profile->image,
                ];
                return $this->success(false, "user profile", $data, 200);
            }else{
                return $this->error(true, "Unauthenticated", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function order(Request $request, $id){
        try{
            $partner = Partner::with('subscription')->where('id', $id)->first();
            if (!$partner){
                return $this->error(true, "Partner not found!", 400);
            }


            $now = Carbon::now()->addHour();
            $day = $now->format('l');
            $c_time =  Carbon::now()->addHour();

            $todaysDropoff = Dropoff::where('partner_id', $id)->where('created_at', 'LIKE',$now->format('Y-m-d').'%')->get();

            //check if order is place within partner's operating hours

            $dayTime = OpHour::where('partner_id', $partner->id)->get();
            $current_day = strtolower($day);

            //if current time is greater that start time and less than current time
            foreach ($dayTime as $day){
                if ($current_day == $day->day){
                    $time = OpHour::where('day', $day->day)->where('partner_id', $partner->id)->first();
                    $stime = Carbon::parse($time->start_time);
                    $etime = Carbon::parse($time->end_time);

                    //try to format to 23:00 format and test again
                    if ($c_time->gt($stime) && $c_time->lessThan($etime)){

                        if ($partner->is_paused == false){
                            if ($partner->is_enabled == true){
                                //if ($partner->order_count_per_day > 0){
                                if ($partner->subscription->no_of_orders > count($todaysDropoff) || $partner->subscription->no_of_orders == 'unlimited'){

                                        ///make order
                                        return $this->job($request, $id);

                                }else{
                                    return $this->error(true, "Partner has exceeded her order limit", 400);
                                }
                                // }else{
                                //     return $this->error(true, "Partner has exceeded her order limit", 400);
                                // }
                            }else{
                                return $this->error(true, "Partner is disabled", 400);
                            }
                        }else{
                            return $this->error(true, "Partner is not active", 400);
                        }
                    }

                }
            }
                    return $this->error(true, "Partner is closed for the day, try reschedule your order tomorrow!", 400);



        }catch(Excption $e){
            $this->history('Jobs', auth()->user()->name." couldnt make ".$dropoff->count()." orders", auth()->user()->id, 'user');

            return $this->error(true, "Error occured!" , 400);
        }
    }

    public function checkVehiclesAvailablePerPartner($id){
        try{
            $partner = Partner::find($id);
            $vehicles = Vehicle::where('partner_id', $id)->where('is_enabled', true)->where('is_removed', false)->get();

            $data = [
                'car' => false,
                'bike' => false,
                'van' => false
            ];

            foreach ($vehicles as $vehicle){
                if ($vehicle->type == 'car'){
                    $data['car'] = true;
                }

                if ($vehicle->type == 'bike'){
                    $data['bike'] = true;
                }

                if ($vehicle->type == 'van'){
                    $data['van'] = true;
                }
            }

            return $this->success(false, "Vehicles available...", $data, 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured!" , 400);
        }
    }

    public function job($request, $id){
        $validated = $request->validate([
            'o_address' => "required|string",
            'o_latitude' => "required",
            'o_longitude' => "required",
            // 'dropoff.*' => "required",
            'dropoff.*.d_address' => "required|string",
            'dropoff.*.d_latitude' => "required",
            'dropoff.*.d_longitude' => "required",
            'dropoff.*.product_name' => "required|string",
            'dropoff.*.receiver_name' => "required|string",
            'dropoff.*.receiver_phone' => "required|string",
            //'dropoff.*.receiver_email' => "string",
            'dropoff.*.quantity' => "required|string",
            'dropoff.*.vehicle_type' => 'required|string'
        ]);


        $order = new Order;
        $order->o_address = $validated['o_address'];
        $order->o_latitude = $validated['o_latitude'];
        $order->o_longitude = $validated['o_longitude'];
        $order->user_id = auth()->user()->id;
        $order->partner_id = $id;
        $order->rider_id = null;
        $order->save();
        $min = 200; //200
        $getrider;
        $totals = 0;
        $discounts = 0;
        $partner = Partner::find($id);
        //pair with rider who is under the partner
        //and is not disabled or dismissed and nearby
        //dd($validated['dropoff'][0]);
        foreach($validated['dropoff'] as $dropoff ){
            if ($partner->order_count_per_day > 0){
                $newdropoff = new DropOff;
                $newdropoff->d_address = $dropoff['d_address'];
                $newdropoff->d_latitude = $dropoff['d_latitude'];
                $newdropoff->d_longitude = $dropoff['d_longitude'];
                $newdropoff->product_name = $dropoff['product_name'];
                $newdropoff->receiver_name = $dropoff['receiver_name'];
                $newdropoff->receiver_phone = $dropoff['receiver_phone'];
                //$newdropoff->receiver_email = $dropoff['receiver_email'] ?? null;
                $newdropoff->quantity = $dropoff['quantity'];

                //will it differ in each dropoff address
                $newdropoff->vehicle_type = $dropoff['vehicle_type'];
                $newdropoff->partner_id = $id;
                $newdropoff->order_id = $order->id;

                //rider id
                //check rider with specific vehicle type
                $final_riders_proximity = array();
                $riders = Rider::with('vehicle')->where('partner_id', $partner->id)->where('is_available', true)->get();
                if (empty($riders)){
                    return $this->error(true, 'This partner doesnt have a rider', 400);
                }
                foreach ($riders as $rider){
                    if ($rider->vehicle->type ==  $dropoff['vehicle_type']){
                            $rider_lat = $rider->latitude;
                            $rider_long = $rider->longitude;
                            //get closest rider from final group

                                    $proximity_sorted = array();
                                    $earthRadius = 6371000;

                                    // convert from degrees to radians
                                    $latFrom = deg2rad($rider_lat);
                                    $lonFrom = deg2rad($rider_long);
                                    $latTo = deg2rad($order->latitude);
                                    $lonTo = deg2rad($order->longitude);

                                    $latDelta = $latTo - $latFrom;
                                    $lonDelta = $lonTo - $lonFrom;

                                    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

                                    $distance =  $angle * $earthRadius;

                                    $distance = $distance/1000;


                                    $proximity_sorted['rider_id'] = $rider->id;
                                    $proximity_sorted['distance'] = $distance;
                                    array_push($final_riders_proximity, $proximity_sorted);

                            }


                    }



                    uasort($final_riders_proximity, function ($a, $b) {
                        if ($a['distance']==$b['distance']) return 0;
                            return ($a['distance']<$b['distance'])?-1:1;
                        //return $a['distance'] > $b['distance'];
                    });

                    $closest_rider = array();
                    $closest_rider = array_splice($final_riders_proximity, 0, 1);
                    $price = 0;


                    foreach ($closest_rider as $pairing){

                        $getrider = Rider::with('vehicle')->where('id', $pairing['rider_id'])->first();

                        $price = $this->calculatePrice($order->o_latitude, $order->o_longitude, $dropoff['d_latitude'], $dropoff['d_longitude'], $earthRadius = 6371000, $id) ?? 0;

                        $newdropoff->rider_id = $getrider->id ?? null;
                        $newdropoff->vehicle_id = $getrider->vehicle->id ?? null;

                        //calculate price and discount
                        $newdropoff->price = $price;
                        $newdropoff->discount = 0;

                        $totals += $newdropoff->price;
                        $discounts += $newdropoff->discount;
                    }

                    if (isset($getrider)){
                        //rider_id or vehicle_id
                        $newdropoff->rider_id = $getrider->id ?? null;
                        $newdropoff->vehicle_id = $getrider->vehicle->id ?? null;

                        //calculate price and discount
                        $newdropoff->price = $price;
                        $newdropoff->discount = 0;

                        $totals += $newdropoff->price;
                        $discounts += $newdropoff->discount;
                    }else{
                        $order = Order::find($order->id);
                        $order->delete();
                        return $this->error(true, 'Sorry all our riders are fully booked and are unable to fulfill your orders at the moment, please try again', 400);
                    }

                        $newdropoff->payment_status = 'not paid';
                        $newdropoff->status = 'pending';
                        $newdropoff->save();


                //$order->dropoff()->attach($newdropoff);

                $this->history('Jobs', auth()->user()->name." ordered a dispatch from ".$order->o_address." to ". $newdropoff->d_address, auth()->user()->id, 'user');

                    $partner = Partner::find($id);
                    //reduce partner order count
                    if ($partner->order_count_per_day != 'unlimited'){
                        $partner->order_count_per_day--;
                        $partner->save();
                    }
            }
        }



        $calculations = [
            "total_amount" => $totals ?? null,
            "discount" => $discounts ?? null,
            "total" => ($totals - $discounts) ?? null
        ];

        json_encode($calculations);

        $order['calculation'] = $calculations;
        $this->history('Jobs', auth()->user()->name." made ".$newdropoff->count()." orders", auth()->user()->id, 'user');


        return $this->success(false, "Order created! You are successfully paired with a rider", $order, 200);

    }


    public function getAllOrders($id){
        try{
            $id = auth()->user()->id;
            $orders = Order::with('dropoff')->where('partner_id', $id)->where('user_id', $id)->latest()->get();

            return $this->success(false, "User Order History", $orders, 200);

        }catch(Exception $e){
            return $this->error(true, "Error Occured!", 400);
        }
    }

    public function getOrder($id){
        //get current order, check if order has started
        //get all dropoffs under order
        try{

            $totals = 0;
            $discounts = 0;
            $user = User::find(auth()->user()->id);
            $orders = Order::where('user_id', auth()->user()->id)->where('id', $id)->first();
            if (!$orders){
                return $this->error(true, "This order can't be found", 400);
            }
            $orders['wallet'] = $user->wallet;
            $dropoffs = Dropoff::where('order_id', $orders->id)->where('status', '!=', 'cancelled')->get();


            if (isset($dropoffs)){
                    foreach ($dropoffs as $dropoff){
                        if ($dropoff->status != 'cancelled'){
                            $totals += $dropoff->price;
                            $discounts += $dropoff->discount;

                        }
                    }
            }else{
                return $this->error(true, "You dont have this order!", 400);
            }
            $calculations = [
                "total_amount" => $totals ?? null,
                "discount" => $discounts ?? null,
                "total" => ($totals - $discounts) ?? null
            ];

            json_encode($calculations);

            $orders['calculation'] = $calculations;
            $orders['dropoff'] = $dropoffs;


            return $this->success(false, "Order", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't get particular order", 400);
        }


    }

    public function deleteDropOff($d_id){
        //delete dropoff without touching the order and dropoff table,
        //deleting the pivot data row/column
        try{
            $dropoff = Dropoff::with('order')->where('id', $d_id)->first();

            if (isset($dropoff) && $dropoff->order->user_id == auth()->user()->id){

                $dropoff->status = 'cancelled';
                $dropoff->save();

                  //increase order limit of partner by 1
                $partner = Partner::find($dropoff->partner_id);
                if ($partner->order_count_per_day != 'unlimited'){
                    $partner->order_count_per_day++;
                    $partner->save();
                }

                return $this->success(false, "DropOff declared cancelled!", [], 200);
            }else{
                return $this->error(true, "DropOff not found!", 200);
            }

        }catch(Exception $e){
            return $this->error(true, "Couldn't delete dropoff!", 400);
        }
    }




    public function getUserHistory(){
        try{
            $id = auth()->user()->id;
            $history = History::where('user_id', $id)->get();

            return $this->success("User history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find user history", 400);
        }


    }


    public function saveAddress(Request $request){
        try{
            $validated = $request->validate([
                'address_name' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string'
            ]);

            $address = new Address;
            $address->name = $validated['address_name'];
            $address->latitude = $validated['latitude'];
            $address->longitude = $validated['longitude'];
            $address->user_id = auth()->user()->id;
            $address->save();

            $this->history('Save Address', auth()->user()->name." saved ".$address->name." as one of their frequently used addresses", auth()->user()->id, 'user');

            return $this->success(false, "Address saved", $address, 200);
        }catch(Exception $e){
            return $this->error(true, "Address couldn't save", 400);
        }

    }

    public function getSavedAddresses(){
        try{
            $id = auth()->user()->id;
            $addresses = Address::where('user_id', $id)->get();

            return $this->success(false, "User saved addresses", $addresses, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find user's addresses", 400);
        }
    }


    public function dashboard($id){
        try{

            $partner = Partner::find($id);
            if (isset($partner)){
                $count = $this->count($id);
                $user = User::find(auth()->user()->id);
                //$user = Auth::user();
                $data = [
                    'partner' => $partner,//only specific partner data should be displayed
                    'orderCount' => $count,
                    'user' => $user
                ];
                return $this->success(false, "Dashboard", $data, 200);
            }else{
                return $this->error(true, "No Partner found", 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Error creating user", 400);
        }
    }


    public function count($id){

        try{

            $pendings = Order::with('dropoff')->where('partner_id', $id)->where('user_id', auth()->user()->id)->get(); //->where('status', 'pending')

            $data = [];
            $p_data = [];
            $d_data = [];
            $pu_data = [];
            foreach ($pendings as $pending){

                foreach ($pending->dropoff as $dro){
                    array_push($data, $dro);
                }
            }

            foreach ($pendings as $pending){
                //$p_datum = $pending->load('dropoff');
                 foreach ($pending->dropoff as $dro){
                     if ($dro->status == 'pending'){
                        array_push($p_data, $dro);
                     }
                     if ($dro->payment_status != 'paid'){
                        array_push($d_data, $dro);
                     }
                     if ($dro->status == 'picked'){
                        array_push($pu_data, $dro);
                     }
                 }
            }



            $data = [
                "orders" => count($data),
                "pending" => count($p_data), //$pending->dropoff()->count(),
                "pickedUp" => count($pu_data), //$pickedUp->dropoff()->count(),
                "unpaid" => count($d_data), // $delivered->dropoff()->count(),
            ];

            return $data;
            //return $this->success(false, "User count orders", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "ERROR fetching count!", 400);
        }

    }

    public function getOrderByStatus(Request $request, $status){

        try{
            $validated = $request->validate([
                'partner_id' => 'required'
            ]);

            $partner = Partner::find($request->partner_id);
            if (empty($partner)){
                return $this->error(true, "Wrong partner...", 400);
            }

            $orders = Order::with('dropoff')->where('partner_id', $request->partner_id)->where('user_id', auth()->user()->id)->latest()->get();
            $data = [];


            switch($status){
                case 'pending':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'pending'){

                                    $dro['dropoff'] = $this->getOneDropoff($dro->id);
                                    // $order = Order::find($dro->id);
                                    // $dro['order'] = $order;

                                array_push($data, $dro);
                            }
                        }
                    }

                    return $this->success(false, "Pending Orders", $data, 200);
                case 'unpaid':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->payment_status != 'paid'){

                                $dro['dropoff'] = $this->getOneDropoff($dro->id);

                                    // $order = Order::find($dro->id);
                                    // $dro['order'] = $order;


                                array_push($data, $dro);
                            }
                        }
                    }

                    return $this->success(false, "Unpaid Orders", $data, 200);

                case 'pickedup':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'picked'){

                                $dro['dropoff'] = $this->getOneDropoff($dro->id);
                                    // $order = Order::find($dro->id);
                                    // $dro['order'] = $order;

                                array_push($data, $dro);
                            }
                        }
                    }

                    return $this->success(false, "Picked-Up Orders", $data, 200);
                default:
                    return $this->error(true, "Couldn't get order...", 400);
            }


        }catch(Exception $e){
            return $this->error(true, "Couldn't get order", 400);
        }
    }

    public function calculatePrice($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius, $id){
        try{

            $partner = Partner::find($id);
            $latFrom = deg2rad($latitudeFrom);
            $lonFrom = deg2rad($longitudeFrom);
            $latTo = deg2rad($latitudeTo);
            $lonTo = deg2rad($longitudeTo);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
              cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

            $distance =  $angle * $earthRadius;
            $distance = $distance/1000;
            $distance_rnd_up = number_format($distance,2);
            $route_cost = RouteCosting::where('partner_id', $id)->first();

            $cost = ($distance_rnd_up * $route_cost->cost_perkm) + $route_cost->base_fare;

            return (int)$cost;

        }catch(Exception $e){
            return $this->error(true, "error in getting price", 400);
        }

    }

    public function rateRider(Request $request){

        try{

            $validated = $request->validate([
                'rider_id' => 'required|string',
                'rating' => 'required|string'
            ]);

            $userId = auth()->user()->id;
            $rated = Rating::where('user_id', $userId)->where('rider_id', $validated['rider_id'])->first();
            if (!$rated){
                $rating = new Rating;
                $rating->rating = $validated['rating'];
                $rating->user_id = $userId;
                $rating->rider_id = $validated['rider_id'];
                $rating->save();
            }else{
                $rating->rating = $validated['rating'];
                $rating->rider_id = $validated['rider_id'];
                $rating->save();
            }

            $rider = Rider::find($validated['rider_id']);
            $rider_rating = Rating::where('rider_id', $validated['rider_id'])->get();
            $num = $rider_rating->count();
            //$sums = $rider_rating->sum('rating');
            $zero = 0;
            foreach ($rider_rating as $rate){
                $zero = $zero + $rate;
            }

            $ratings = $zero/$num;

            $rider->rating = $ratings;
            $rider->save();

            return $this->success(false, "Rider rating successful", $rating, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }

    }

    public function ratePartner(Request $request){
        try{

            $validated = $request->validate([
                'partner_id' => 'required|string',
                'rating' => 'required|string'
            ]);
            $userId = auth()->user()->id;
            $rated = Rating::where('user_id', $userId)->where('partner_id', $validated['partner_id'])->first();
            if (!$rated){
                $rating = new Rating;
                $rating->rating = $validated['rating'];
                $rating->user_id = $userId;
                $rating->partner_id = $validated['partner_id'];
                $rating->save();
            }else{
                $rating->rating = $validated['rating'];
                $rating->partner_id = $validated['partner_id'];
                $rating->save();
            }

            $partner = Partner::find($validated['partner_id']);
            $partner_rating = Rating::where('partner_id', $validated['partner_id'])->get();
            $num = $partner_rating->count();
            //$sums = $partner_rating->sum('rating');
            $zero = 0;
            foreach ($partner_rating as $rate){
                $zero = $zero + $rate;
            }

            $ratings = $zero/$num;

            $partner->rating = $ratings;
            $partner->save();

            return $this->success(false, "Partner rating successful", $rating, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }


    public function logout(){
        try{
            \Auth::user()->token()->delete();
            return $this->success(false, "User Logged out successfully", [], 200);
        }catch(Exception $e){
            return $this->error(true, "Error logging user out!", 400);
        }

    }

    public function orderHistory($id){
        try{
            $orders = Order::with('dropoff')->where('partner_id', $id)->where('user_id', auth()->user()->id)->latest()->get();
            //$orders = Order::with('dropoff')->where('user_id', auth()->user()->id)->get();

            $history = [];
            $drop_o = [];

            foreach ($orders as $order) {
                $data = [
                    // 'order_id' => $order->id,
                    'pickup_address' => $order->o_address,
                ];

                if (!empty($order->dropoff)){
                    foreach ($order->dropoff as $dropoff){

                            $dropoff_data = [
                                'status' => $dropoff->status ?? null,
                                'time' => $dropoff->created_at,
                                'dropoff_id' => $dropoff->id,
                                'dropoff' => $this->getOneDropoff($dropoff->id)
                            ];

                            $dropoff['dropoff'] = $this->getOneDropoff($dropoff->id);
                            $ar = array_merge($data, $dropoff_data);
                            json_encode($ar);
                            array_push($history, $dropoff);
                    }

                    //array_push($data['dropoff'], $drop_o);
                }


            }




            return $this->success(false, "Order history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }

    public function getOneDropoff($id){
        try{
            $dropoff = Dropoff::with(['order', 'rider', 'vehicle'])->where('id', $id)->first();

            $user = User::where('id', $dropoff->order->user_id)->first();
            $dropoff['user'] = $user;
            if (isset($dropoff)){
                //return $this->success(false, "Dropoff", $dropoff, 200);
                return $dropoff;
            }else{
                return $this->error(true, "No dropoff found", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }

    public function fundWallet($request){
        try{


            if ($request['trans_status'] == 'success'){
                $user = User::where('id', auth()->user()->id)->first();
                $user->wallet = $user->wallet + $request['amount'];
                $user->save();

                //wallet history
                $this->walletLogs('wallet', "You added ".$request['amount']." to your wallet", auth()->user()->id, 'user');
                //trnasaction history
                $this->transactionLog('Funding Wallet', $user->name. " added ".$request['amount']." to their wallet", (int)$request['amount'] ,auth()->user()->id, 'user');
                //user history

                $log = $this->paymentLog($request);

                return $log;
            }


        }catch(Exception $e){
            return $this->error(true, "Couldn't fund wallet", 400);
        }
    }


    public function payment(Request $request){
        try {
            //transaction history
            //wallet history
            //user history
            $validated = $request->validate([
                'customer_name' => 'required',
                'customer_email' => 'required',
                'trans_description' => 'required',
                'datetime' => 'required',
                'trans_status' => 'required',
                'order_id' => 'string',
                'reference_num' => 'string',


                'type' => 'required',
                'status' => 'required',


                'amount' => 'required',
                'origin_of_payment' => 'required',
                'paystack_message' => 'string'
            ]);



            if ($validated['type'] == 'payOrderwithCard'){
                $orders = Order::with('dropoff')->where('id', intval($validated['order_id']))->where('user_id', auth()->user()->id)->get();
                $user = User::find(auth()->user()->id);
                if ($validated['trans_status'] == 'success'){

                    $partner = Partner::find($orders->partner_id);
                    $partner->wallet = $partner->wallet + $validated['amount'];
                    $partner->save();
                    //increase partner's earninigs/wallet
                    foreach ($orders as $order){


                        foreach ($order->dropoff as $dropoff){
                            //increase rider and vehicle earnings
                            $rider = Rider::with('vehicle')->where('id', $dropoff->rider_id)->first();
                            $rider->earning = $rider->earning + $dropoff->price;
                            $rider->save();

                            $vehicle = Vehicle::find($rider->vehicle->id);
                            $vehicle->earning = $vehicle->earning +  $dropoff->price;
                            $vehicle->save();


                            //dropoff payment-status change to paid if true
                            $job = Dropoff::where('id', $dropoff->id)->first();
                            $job->payment_status = 'paid';
                            $job->save();

                            $this->transactionLog('Delivery Fees', $validated['customer_name']." paid for an order", (int)$dropoff->price , auth()->user()->id, 'user');
                       }




                    }

                     //wallet history
                     $this->walletLogs('wallet', $validated['amount']." was paid from your card for a job", auth()->user()->id, 'user');
                     //trnasaction history
                     //$this->transactionLog('Delivery Fees', $validated['customer_name']." paid for an order", $request['amount'] , auth()->user()->id, 'user');
                     //user history
                    $log = $this->paymentLog($validated);



                }else{
                    return $this->error(true, "Error while processing transactions!", 400);
                }


            }else if ($validated['type'] == 'fundWallet'){
                    $log =  $this->fundWallet($validated);
            }else if ($validated['type'] == 'payOrderwithWallet'){
                $orders = Order::with('dropoff')->where('id', intval($validated['order_id']))->where('user_id', auth()->user()->id)->get();
                $user = User::find(auth()->user()->id);
                    //reduce user wallet
                    $user = User::where('id', auth()->user()->id)->first();
                    if ($user->wallet > $validated['amount']){
                        $user->wallet = $user->wallet - $validated['amount'];
                        $user->save();

                        //increase partner's earninigs/wallet
                        foreach ($orders as $order){
                            $partner = Partner::find($order->partner_id);
                            $partner->wallet = $partner->wallet + $validated['amount'];
                            $partner->save();

                            foreach ($order->dropoff as $dropoff){
                                //increase rider and vehicle earnings
                                $rider = Rider::with('vehicle')->where('id', $dropoff->rider_id)->first();
                                $rider->earning = $rider->earning + $dropoff->price;
                                $rider->save();

                                $vehicle = Vehicle::find($rider->vehicle->id);
                                $vehicle->earning = $vehicle->earning +  $dropoff->price;
                                $vehicle->save();


                                //dropoff payment-status change to paid if true
                                $job = Dropoff::where('id', $dropoff->id)->first();
                                $job->payment_status = 'paid';
                                $job->save();

                                $this->transactionLog('Delivery Fees', $validated['customer_name']." paid for an order", $dropoff->price , auth()->user()->id, 'user');

                            }




                        }

                        //wallet history
                        $this->walletLogs('wallet', $validated['amount']." was deducted from your wallet for a job", auth()->user()->id, 'user');
                        //trnasaction history
                        //$this->transactionLog('Delivery Fees', $user->name." paid for an order", $request['amount'] , auth()->user()->id, 'user');
                        //user history
                        $log = $this->paymentLog($validated);


                    }else{
                        return $this->error(true, "User doesn't have enough in his wallet!", 400);
                    }


            }else{
                return $this->error(true, "The transaction type is unknown!", 400);
            }



            return $this->success(false, "Logged Payment Successfully", $log , 200);



        }catch(Exception $e){
            return $this->error(true, "Error occured while processing payment!", 400);
        }

    }


    public function paymentLog($validated){
        $payment = new Payment;
        $payment->customer_name = $validated['customer_name'];
        $payment->customer_email = $validated['customer_email'];
        $payment->trans_description = $validated['trans_description'];
        $payment->datetime = $validated['datetime'];
        $payment->trans_status = $validated['trans_status'];
        $payment->order_id = $validated['order_id'] ?? null;
        $payment->reference_num = $validated['reference_num'];
        $payment->status = $validated['status'];
        $payment->amount = $validated['amount'];
        $payment->origin_of_payment = $validated['origin_of_payment'];
        $payment->paystack_message = $validated['paystack_message'];
        $payment->save();

        return $payment;
    }

    public function allOrderHistory(){
        try{
            //partner_image
            //$user = User::find(auth()->user()->id);
            $orders = Order::with(['dropoff', 'partner'])->where('user_id', auth()->user()->id)->latest()->get();

            $data = [];
            if (isset($orders)){
                foreach ($orders as $order){
                    $user_order = [
                        'partner' => $order->partner->name,
                        'partner_image' => $order->partner->image,
                    ];

                    foreach ($order->dropoff as $dropoff) {

                        if ($dropoff->payment_status == 'paid'){
                            $user_drop = [
                                'price' => $dropoff->price,
                                'dropoff_id' => $dropoff->id,
                                'time' => $dropoff->created_at->diffForHumans()
                            ];

                            $ar = array_merge($user_order, $user_drop);

                            array_push($data, $ar);

                            if (count($data) == 3){
                                return $this->success(false, "Last 3 user orders...", $data , 200);
                            }
                        }

                    }


                }

                return $this->success(false, "Last 3 user orders...", $data , 200);

            }else{
                return $this->error(true, "No Orders" , 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Error occured while processing payment!", 400);
        }
    }


    public function getTransactionHistory(){
        try{
            $transLogs = TransactionLogs::limit(5)->where('user_id', auth()->user()->id)->latest()->get();

            return $this->success(false, "Transaction history...", $transLogs , 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }

    public function getAllTransactionHistory(){
        try{
            $transLogs = TransactionLogs::where('user_id', auth()->user()->id)->latest()->get();

            return $this->success(false, "Transaction history...", $transLogs , 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }
}
