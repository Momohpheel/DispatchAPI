<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
use App\Models\History;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\DropOff;
use App\Models\Address;
use App\Models\OperatingHours as OpHour;
use App\Traits\Response;
use App\Events\Illuminate\Auth\Events\History as UserHistory;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;

class UserRepository implements UserRepositoryInterface{

    use Response;

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
            $hours = OpHour::where('partner_id', $partner->id)->get();


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
                $dropoff->rider_id = null;
                $dropoff->save();

                $order->droppoff()->attach($dropoff);

                event(new UserHistory($order));

                //reduce partner order count
                if ($partner->order_count_by_id != 'unlimited'){
                    $partner->order_count_by_id--;
                    $partner->save();
                 }
            }



            return $this->success("Order created", $order, 200);
        }catch(Excption $e){
            return $this->error(true, "Error creating order", 400);
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

        // $data = [
        //     "orders" => 2,
        // ]
    }

    public function calculatePrice(Request $request){
        //$calculation = (($distance_rnd * $fuel_cost) + $rider_salary + ($distance_rnd * $bike_fund )) * $ops_fee * $easy_log * $easy_disp;
    }

    public function payment(){}

    public function rateRider(){}

    public function ratePartner(){}

}
