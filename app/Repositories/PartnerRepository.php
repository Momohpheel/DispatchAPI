<?php

namespace App\Repositories;

use App\Traits\Response;
use App\Models\Partner;
use App\Models\Rider;

class PartnerRepository{

    use Response;

    public function signup(Request $request){
        try{
            $validated = $request->validate([
                'name' => "required|string",
                'phone' => "required|string",
                'email' => "required|string",
                "password" => "required|string",
                "code_name" => "required|string"
            ]);

            $partner = new Partner;
            $partner->name = $validated['name'];
            $partner->phone = $validated['phone'];
            $partner->email = $validated['email'];
            $partner->code_name = $validated['code_name'];
            $partner->password = Hash::make($validated['password']);
            $partner->save();

            return $this->success("Partner registered", $partner, 200);
        }catch(Exception $e){
            return $this->error(true, "Error creating partner", 400);
        }

    }

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'code_name' => "required|string",
                "password" => "required|string"
            ]);

            $partner = Partner::where('code_name', $validated['code_name'])->first();
            if ($partner){
                $check = Hash::check($validated['password'], $partner->password);
                if ($check){
                    return $this->success("Partner found", $partner, 200);
                }else{
                    return $this->error(true, "Error logging partner", 400);
                }
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging partner", 400);
        }
    }

    public function createRider(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'workname' => 'required|string',
                'phone' => 'required|string',
                'password' => 'required|string'
            ]);

            $rider = new Rider;
            $rider->name = $validated['name'];
            $rider->phone = $validated['phone'];
            $rider->workname = $validated['workname'];
            $rider->code_name = $validated['code_name'];
            $rider->password = Hash::make($validated['password']);
            $rider->partner_id = 1; //auth()->user()->id;
            $rider->save();

            return $this->success("Rider registered", $rider, 200);
        }catch(Exception $e){
            return $this->error(true, "Error creating rider", 400);
        }


    }

    public function profile(Request $request){}

    public function getOrders(){
        try{
            $orders = Order::where('partner_id', 1)->load('dropoff');

            return $this->success("Orders", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching orders", 400);
        }
    }

    public function disableRider($id){
        try{
            $rider = Rider::where('id', $id)->where('partner_id', 1)->first();
            $rider->is_enabled = false;
            $rider->save();

            return $this->success("Rider has been disabled", $rider, 200);
        }catch(Exception $e){
            return $this->error(true, "Error disabling rider", 400);
        }

    }

    public function assignOrder(Request $request){
        try{
        $validated = $request->validate([
            'rider_id' => 'required',
            'dropoff_id' => 'required'
        ]);

        $order = DropOff::where('id', $validated['dropoff_id'])->where('partner_id', 1)->first();
        $rider = Rider::where('id', $validated['rider_id'])->where('partner_id', 1)->first();
        if ($order){

            if ($order->status == 'no response yet'){
                $order->rider_id = $validated['rider_id'];
                $order->save();

                return $this->success("Order has been successfully assigned to ". $rider->name, $order, 200);
            }else{
                return $this->success("Order is ".$order->status, $order, 400);
            }
        }
    }catch(Exception $e){
        return $this->error(true, "Error assigning order to rider", 400);
    }
    }

    public function getRiders(){
        try{
            $riders = Rider::where('partner_id', 1)->get();

            return $this->success("Riders", $riders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching riders", 400);
        }

    }

    public function pauseAccount(){
        try{
            $partner = Partner::find(1); //auth->user()->id
            $partner->is_paused = true;
            $partner->save();

            return $this->success("Partner has been paused from operating", $partner, 200);
        }catch(Exception $e){
            return $this->error(true, "Error pausing partner", 400);
        }

    }

    public function setRouteCosting(){}

    public function subscribe(){}

    public function updateOperatingHours(){}



}
