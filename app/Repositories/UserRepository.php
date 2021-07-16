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
use App\Models\Rating;
use App\Models\History;
use App\Models\OperatingHours as OpHour;
use App\Traits\Response;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

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
                // "image" => $partner->image

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
                    // "image" => $user->image,
                    "access_token" => $access_token
                ];
                $this->history('Profile', $data['name']." created their profile", $data['id'], 'user');

                return $this->success(false, "User created", $data, 200);
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
                        // "image" => $user->image,
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

    public function updateProfile(Request $request){
        try{
            $validated = $request->validate([
                'name' => "string",
                'phone' => "string",
                'email' => 'email',
                'image' => 'image|max:2000|mimes:png,jpg'
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
                $check_user->name = $validated['name'] ?? $check_user->name;
                $check_user->phone = $validated['phone'] ?? $check_user->phone;
                $check_user->email = $validated['email'] ?? $check_user->email;
                $check_user->image = $image_to_store ? env('APP_URL') .'/storage/images/'.$image_to_store : $check_user->image;
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
            $partner = Partner::where('id', $id)->first();
            if (!$partner){
                return $this->error(true, "Partner not found!", 400);
            }
            $now = Carbon::now()->addHour();
            $day = $now->format('l');
            $c_time =  Carbon::parse($now->format('h:i:s'));

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
                                if ($partner->order_count_per_day > 0){


                                        ///make order
                                        return $this->job($request, $id);


                                }else{
                                    //throw new Exception("Partner has exceeded her order limit");
                                    return $this->error(true, "Partner has exceeded her order limit", 400);
                                }
                            }else{
                                //throw new Exception("Partner is disabled");
                                return $this->error(true, "Partner is disabled", 400);
                            }
                        }else{
                            //throw new Exception("Partner is not active");
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


    public function job($request, $id){
        $validated = $request->validate([
            'o_address' => "required|string",
            'o_latitude' => "required",
            'o_longitude' => "required",
            'dropoff.*' => "required",
            'dropoff.*.d_address' => "required|string",
            'dropoff.*.d_latitude' => "required",
            'dropoff.*.d_longitude' => "required",
            'dropoff.*.product_name' => "required|string",
            'dropoff.*.receiver_name' => "required|string",
            'dropoff.*.receiver_phone' => "required|string",
            'dropoff.*.receiver_email' => "required|string",
            'dropoff.*.quantity' => "required|string",
            'dropoff.*vehicle_type' => 'required|string'
        ]);


        $order = new Order;
        $order->o_address = $validated['o_address'];
        $order->o_latitude = $validated['o_latitude'];
        $order->o_longitude = $validated['o_longitude'];
        $order->user_id = auth()->user()->id;
        $order->partner_id = $id;
        $order->rider_id = null;
        $order->save();
        $min = 200;
        $getrider;
        //pair with rider who is under the partner
        //and is not disabled or dismissed and nearby
        //dd($validated['dropoff'][0]);
        foreach($validated['dropoff'] as $dropoff ){
            $newdropoff = new DropOff;
            $newdropoff->d_address = $dropoff['d_address'];
            $newdropoff->d_latitude = $dropoff['d_latitude'];
            $newdropoff->d_longitude = $dropoff['d_longitude'];
            $newdropoff->product_name = $dropoff['product_name'];
            $newdropoff->receiver_name = $dropoff['receiver_name'];
            $newdropoff->receiver_phone = $dropoff['receiver_phone'];
            $newdropoff->receiver_email = $dropoff['receiver_email'];
            $newdropoff->quantity = $dropoff['quantity'];

            //will it differ in each dropoff address
            $newdropoff->vehicle_type = $dropoff['vehicle_type'];
            $newdropoff->partner_id = $id;

            //rider id
            //check rider with specific vehicle type
            //$riders = Rider::where('partner_id', $partner->id)->where('is_available', true)->get();
            // foreach ($riders as $rider){
            //     if ($rider->vehicle->type ==  $dropoff['vehicle_type']){
            //             $rider_lat = $rider->latitude;
            //             $rider_long = $rider->longitude;
            //             $url = file_get_contents("https://maps.googleapis.com/maps/api/directions/json?origin=".$rider_lat.",".$rider_long."&destination=".$order->o_latitude.",".$order->o_longitude."&sensor=false&key=AIzaSyDiUJ5BCTHX1UG9SbCrcwNYbIxODhg1Fl8");
            //             $url = json_decode($url);

            //             $meters = $url->{'routes'}[0]->{'legs'}[0]->{'distance'}->{'value'};
            //             $time = $url->{'routes'}[0]->{'legs'}[0]->{'duration'}->{'value'};
            //             $distance = $meters/1000;
            //             if ($distance < $min) {
            //                 $min = $distance;
            //                 $getrider = $rider;
            //             }

            //         }


            //     }

                if (isset($getrider)){
                    //rider_id or vehicle_id
                    $newdropoff->rider_id = $getrider->id ?? null;
                    $newdropoff->price = $this->calculatePrice($min, $id) ?? null;

                }
                // else{
                //     return $this->error(true, 'Sorry all our riders are fully booked and are unable to fulfill your orders at the moment, please try again', 400);
                // }

            $newdropoff->status = 'pending';
            $newdropoff->save();


            $order->dropoff()->attach($newdropoff);

            $this->history('Jobs', auth()->user()->name." ordered a dispatch from ".$order->o_address." to ". $newdropoff->d_address, auth()->user()->id, 'user');

                $partner = Partner::find($id);
            //reduce partner order count
            if ($partner->order_count_per_day != 'unlimited'){
                $partner->order_count_per_day--;
                $partner->save();
             }
        }


        $this->history('Jobs', auth()->user()->name." made ".$newdropoff->count()." orders", auth()->user()->id, 'user');

        return $this->success(false, "Order created! You are successfully paired with a rider", $order, 200);
    }


    public function getAllOrders(){
        try{

            $id = auth()->user()->id;
            $orders = Order::where('user_id', $id)->load('dropoff');
            $data = [];
            foreach ($orders as $order){
                $data = $order->dropoff();
            }
                return $this->success(false, "User Order History", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "Error Occured!", 400);
        }
    }

    public function getOrder($id){
        //get current order, check if order has started
        //get all dropoffs under order
        try{
            $orders = Order::where('id', $id)->get();
            $data = [];
            foreach ($orders as $order){
                $data = $order->dropoff();
            }

            return $this->success(false, "Order", $data, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't get particular order", 400);
        }


    }

    public function deleteDropOff($d_id, $o_id){
        //delete dropoff without touching the order and dropoff table,
        //deleting the pivot data row/column
        try{
            $dropoff = Dropoff::where('id', $d_id)->first();
            $dropoff->order()->detach($o_id);


            //increase order limit of partner by 1
            $partner = Partner::find($dropoff->partner_id);
            if ($partner->order_count_per_day != 'unlimited'){
                $partner->order_count_per_day++;
                $partner->save();
             }

            return $this->success(false, "DropOff deleted!", [], 200);
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


    public function count(){

        try{

            $orders = Order::where('user_id', auth()->user()->id)->get();
            $pendings = Order::where('user_id', auth()->user()->id)->get(); //->where('status', 'pending')
            $pickedUp = Order::where('user_id', auth()->user()->id)->get(); //->where('status', 'pickedUp')
            $delivered = Order::where('user_id', auth()->user()->id)->get(); //->where('status', 'delivered')
            $data = [];
            $p_data = [];
            $d_data = [];
            $pu_data = [];
            foreach ($orders as $order){
               $datum = $order->load('dropoff');
                foreach ($datum->dropoff as $dro){
                    array_push($data, $dro);
                }
            }

            foreach ($pendings as $pending){
                $p_datum = $pending->load('dropoff');
                 foreach ($p_datum->dropoff as $dro){
                     if ($dro->status == 'pending'){
                        array_push($p_data, $dro);
                     }


                 }
            }

            foreach ($pendings as $pending){
                $d_datum = $pending->load('dropoff');
                 foreach ($d_datum->dropoff as $dro){
                     if ($dro->status == 'delivered'){
                        array_push($d_data, $dro);
                     }


                 }
            }

            foreach ($pendings as $pending){
                $pu_datum = $pending->load('dropoff');
                 foreach ($pu_datum->dropoff as $dro){
                     if ($dro->status == 'picked'){
                        array_push($pu_data, $dro);
                     }


                 }
            }


            $data = [
                "orders" => count($data),
                "pending" => count($p_data), //$pending->dropoff()->count(),
                "pickedUp" => count($pu_data), //$pickedUp->dropoff()->count(),
                "delivered" => count($d_data), // $delivered->dropoff()->count(),
            ];

            return $this->success(false, "User count orders", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "ERROR!", 400);
        }

    }



    public function calculatePrice($distance, $id){
        try{

            $partner = Partner::find($id);
            $distance_rnd = number_format($distance,2);
            $distance = intval($distance);
            $route_cost = RouteCosting::where('partner_id', $id)->where('min_km', '<=', $distance)->where('max_km', '>=', $distance)->first();
            //$calculation = (($distance * $fuel_cost) + $rider_salary + ($distance * $bike_fund )) * $ops_fee * $easy_log * $easy_disp;
            $calculation = (($distance_rnd * $route_cost->fuel_cost) + $route_cost->rider_salary + ($distance_rnd * $route_cost->bike_fund)) * $route_cost->ops_fee * $route_cost->easy_log * $route_cost->easy_disp;
            $cal = ceil($calculation / 50) * 50;
            $cost = number_format($cal, 2);

            return $cost;

        }catch(Exception $e){
            return $this->error(true, "error in getting price", 400);
        }

    }

    public function payment(){}

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

    public function orderHistory(){
        try{

            $orders = Order::with('dropoff')->where('user_id', auth()->user()->id)->get();

            $history = [];
            $drop_o = [];

            foreach ($orders as $order) {
                $data = [
                    'order_id' => $order->id,
                    'pickup_address' => $order->o_address,
                    'dropoff' => array()
                ];

                if (!empty($order->dropoff)){
                    foreach ($order->dropoff as $dropoff){
                            $datu = [
                                'address' => $dropoff->d_address ?? null,
                                'status' => $dropoff->status ?? null
                            ];
                            array_push($data['dropoff'], $datu);

                    }

                    //array_push($data['dropoff'], $drop_o);
                }

                array_push($history, $data);
            }




            return $this->success(false, "Order history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }

}
