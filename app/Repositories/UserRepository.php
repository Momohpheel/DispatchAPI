<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
use App\Models\Order;
use App\Models\DropOff;
use App\Models\Address;
use App\Traits\Response;

class UserRepository{

    use Response;

    public function onboard(Request $request){
        $validated = $request->validate([
            'partner' => 'required|string',
        ]);

        $partner = Partner::find($validated['partner']);

        if (exists($partner)){
            return $this->success('User Onboarded successfully', $partner, 200);
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

            return $this->success("User created", $user, 200);
        }catch(Exception $e){
            return $this->error(true, "Error creating user", 400);
        }

    }

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'phone' => "required|string",
                "password" => "required|string"
            ]);

            $user = User::where('phone', $validated['phone'])->first();
            if ($user){
                $check = Hash::check($validated['password'], $user->password);
                if ($check){
                    return $this->success("User found", $user, 200);
                }else{
                    return $this->error(true, "Error logging user", 400);
                }
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging user", 400);
        }
    }

    public function order(Request $request){
        //partnerId should be passed to this route so
        //we can know who we are sending the payments to


        //check partner operating time to see if they're active

        //check partner order_count_per_day to see if they can still take orders

        //
        try{
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
            $order->user_id = 1;//auth()->user->id
            $order->save();

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
                $dropoff->save();

                $order->droppoff()->attach($dropoff);
            }

            return $this->success("Order created", $order, 200);
        }catch(Excption $e){
            return $this->error(true, "Error creating order", 400);
        }
    }

    public function calculatePrice(){}

    public function chargeUser(){}

    public function getUserHistory(){}

    public function rateRider(){}

    public function ratePartner(){}

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
            $address->user_id = 1; //auth()->user()->id;
            $address->save();

            return $this->success("Address saved", $address, 200);
        }catch(Exception $e){
            return $this->error(true, "Address couldn't save", 400);
        }

    }




}
