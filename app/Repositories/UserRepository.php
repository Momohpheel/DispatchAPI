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
use App\Models\OperatingHours as OpHour;
use App\Traits\Response;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

class UserRepository implements UserRepositoryInterface{

    use Response, Logs;

    public function __construct(){

    }

    public function onboard(Request $request){
        $validated = $request->validate([
            'partner' => 'required|string',
        ]);

        $partner = Partner::find($validated['partner']);

        if (!empty($partner)){
            $data = [
                "name" => $partner->name,
                "phone" => $partner->phone,
                "email" => $partner->email,
                "id" => $partner->id,
                "code_name" => $partner->code_name,
                "image" => $partner->image

            ];
            return $this->success('User Onboarded successfully', $data, 200);
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

            return $this->success("User created", $data, 200);
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
                    return $this->success("User found", $data, 200);
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

    public function order(Request $request, $id){
        try{
            $partner = Partner::where('id', $id)->first();
            $now = Carbon::now();
            $day = $now->format('l');
            $time =  $now->format('h A');

            //check if order is place within partner's operating hours

            // $dayTime = OpHour::where('partner_id', $partner->id)->get();
            // $current_day = strtolower($day);

            $dayTime = ['sunday', 'monday', 'friday'];
            $current_day = 'sunday';
            //if current time is greater that start time and less than current time
            foreach ($dayTime as $day){
                if ($current_day == $day->day){
                    // $time = OpHour::where('day', $day->day)->where('partner_id', $partner->id)->first();
                    // if ($time->start_time is less than $time and $time is greater than $time->end_time){

                    // }
                    return "start";
                }
            }
                    return "end";

            if ($partner->is_paused == false){
                if ($partner->is_enabled == true){
                    if ($partner->order_count_per_day > 0){


                            ///make order



                    }else{
                        return $this->error(true, "Partner has exceeded her order limit", 400);
                    }
                }else{
                    return $this->error(true, "Partner is disabled", 400);
                }
            }else{
                return $this->error(true, "Partner is not active", 400);
            }





            $validated = $request->validate([
                'o_address' => "required|string",
                'dropoff.d_address.*' => "required|string",
                'o_latitude' => "required",
                'o_longitude' => "required",
                'dropoff.d_latitude.*' => "required",
                'dropoff.d_longitude.*' => "required",
                'dropoff.product_name.*' => "required|string",
                'dropoff.receiver_name.*' => "required|string",
                'dropoff.receiver_phone.*' => "required|string",
                'dropoff.receiver_email.*' => "required|string",
                'dropoff.quantity.*' => "required|string",
            ]);


            $order = new Order;
            $order->o_address = $validated['o_address'];
            $order->o_latitude = $validated['o_latitude'];
            $order->o_longitude = $validated['o_longitude'];
            $order->user_id = auth()->user()->id;
            $order->partner_id = $partner->id;
            $order->save();
            $min = 200;
            //pair with rider who is under the partner
            //and is not disabled or dismissed and nearby
            foreach($validated['dropoff'] as $dropoff ){
                $dropoff = new DropOff;
                $dropoff->d_address = $dropoff['d_address'];
                $dropoff->d_latitude = $dropoff['d_latitude'];
                $dropoff->d_longitude = $dropoff['d_longitude'];
                $dropoff->product_name = $dropoff['product_name'];
                $dropoff->receiver_name = $dropoff['receiver_name'];
                $dropoff->receiver_phone = $dropoff['receiver_phone'];
                $dropoff->receiver_email = $dropoff['receiver_email'];
                $dropoff->quantity = $dropoff['quantity'];
                $dropoff->partner_id = $partner->id;
                //rider id

                $riders = Rider::where('partner_id', $partner->id)->where('is_available', true)->get();
                foreach ($riders as $rider){
                    $rider_lat = $rider->latitude;
                    $rider_long = $rider->longitude;
                    $url = file_get_contents("https://maps.googleapis.com/maps/api/directions/json?origin=".$rider_lat.",".$rider_long."&destination=".$order->o_latitude.",".$order->o_longitude."&sensor=false&key=AIzaSyDiUJ5BCTHX1UG9SbCrcwNYbIxODhg1Fl8");
                    $url = json_decode($url);

                    $meters = $url->{'routes'}[0]->{'legs'}[0]->{'distance'}->{'value'};
                    $time = $url->{'routes'}[0]->{'legs'}[0]->{'duration'}->{'value'};
                    $distance = $meters/1000;
                    if ($distance < $min) {
                        $min = $distance;
                        $getrider = $rider;
                    }
                }

                if (isset($getrider)){
                    $dropoff->rider_id = $getrider->id;
                }else{
                    return $this->error(true, 'Sorry all our riders are fully booked and are unable to fulfill your orders at the moment, please try again', 400);
                }

                $dropoff->save();

                $order->droppoff()->attach($dropoff);


                //reduce partner order count
                if ($partner->order_count_by_id != 'unlimited'){
                    $partner->order_count_by_id--;
                    $partner->save();
                 }
            }



            return $this->success("Order created! You are successfully paired with a rider", $order, 200);
        }catch(Excption $e){
            return $this->error(true, "Error occured while creating order", 400);
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

            return $this->success("Address saved", $address, 200);
        }catch(Exception $e){
            return $this->error(true, "Address couldn't save", 400);
        }

    }

    public function getSavedAddresses(){
        try{
            $id = auth()->user()->id;
            $addresses = Address::where('user_id', $id)->get();

            return $this->success("User saved addresses", $addresses, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find user's addresses", 400);
        }
    }

    public function count(){

        try{
            $orders = Dropoff::where('user_id', auth()->user()->id)->get();
            $pending = Dropoff::where('user_id', auth()->user()->id)->where('status', 'pending')->get();
            $pickedUp = Dropoff::where('user_id', auth()->user()->id)->where('status', 'pickedUp')->get();
            $delivered = Dropoff::where('user_id', auth()->user()->id)->where('status', 'delivered')->get();

            $data = [
                "orders" => $orders->count(),
                "pending" => $pending->count(),
                "pickedUp" => $pickedUp->count(),
                "delivered" => $delivered->count(),
            ];

            return $this->success("User count orders", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "ERROR!", 400);
        }

    }

    public function calculatePrice(Request $request){
        //$calculation = (($distance_rnd * $fuel_cost) + $rider_salary + ($distance_rnd * $bike_fund )) * $ops_fee * $easy_log * $easy_disp;
    }

    public function payment(){}

    public function rateRider(){

        try{
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


            return $this->success("Rider rating successful", $rating, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }

    }

    public function ratePartner(){
        try{
            $userId = auth()->user()->id;
            $rated = Rating::where('user_id', $userId)->where('partner_id', $validated['rider_id'])->first();
            if (!$rated){
                $rating = new Rating;
                $rating->rating = $validated['rating'];
                $rating->user_id = $userId;
                $rating->partner_id = $validated['rider_id'];
                $rating->save();
            }else{
                $rating->rating = $validated['rating'];
                $rating->partner_id = $validated['rider_id'];
                $rating->save();
            }

            return $this->success("Partner rating successful", $rating, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured!", 400);
        }
    }

}
