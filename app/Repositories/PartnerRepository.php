<?php
namespace App\Repositories;

use App\Traits\Response;
use App\Models\Partner;
use App\Models\Rider;
use App\Models\Subscription;
use App\Models\OperatingHours;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\routeCosting as RouteCosting;
use App\Models\DropOff;
use App\Models\Vehicle;
use App\Models\Address;
use App\Traits\Logs;
use App\Repositories\Interfaces\PartnerRepositoryInterface;


class PartnerRepository implements PartnerRepositoryInterface{


    use Response, Logs;

    /*
    *
    *
    *   PARTNER AUTHENTICATION
    *   (signup, login)
    *
    */
    public function signup(Request $request){
        try{
            $validated = $request->validate([
                'name' => "required|string",
                'phone' => "required|string",
                'email' => "required|string",
                "password" => "required|string",
                "code_name" => "required|string"
            ]);

            $partner = Partner::where('code_name', $validated['code_name'])->where('name', $validated['name'])->first();
            if (!$partner){
                $partner = new Partner;
                $partner->name = $validated['name'];
                $partner->phone = $validated['phone'];
                $partner->email = $validated['email'];
                $partner->code_name = $validated['code_name'];
                $partner->password = Hash::make($validated['password']);
                $partner->subscription_id = 1;
                $partner->save();
                $access_token = $partner->createToken('authToken')->accessToken;

                $data = [
                    "name" => $partner->name,
                    "phone" => $partner->phone,
                    "email" => $partner->email,
                    "id" => $partner->id,
                    "code_name" => $partner->code_name,
                    "access_token" => $access_token
                ];
                return $this->success(false,"Partner registered", $data, 200);
            }else{
                return $this->error(true, "Partner exists", 400);
            }
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

                    $access_token = $partner->createToken('authToken')->accessToken;
                    $data = [
                        "name" => $partner->name,
                        "phone" => $partner->phone,
                        "email" => $partner->email,
                        "id" => $partner->id,
                        "code_name" => $partner->code_name,
                        "access_token" => $access_token
                    ];
                    return $this->success(false, "Partner found", $data, 200);
                }else{
                    return $this->error(true, "Error logging partner", 400);
                }
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging partner", 400);
        }
    }





    /*
    *
    *
    *   PARTNER PROFILE SETUP & FUNCTIONS
    *   (kyc, addProfile, updateProfile, pauseAccount, )
    *
    */
    public function kyc(){}

    public function profile(Request $request){

        try{
            $validated = $request->validate([
                'image' => "required|image|mimes:jpg,png,jpeg|max:2000",
                'business_name' => 'required|string',
                'business_description' => 'required|string',
                'business_phone' => 'required|string',
                'business_email' => 'required|string',
                'business_bank_account' => 'required|string',
                'business_bank_name' => 'required|string',

            ]);

            if ($request->hasFile('image')){
                    $image_name = $validated['image']->getClientOriginalName();
                    $image_name_withoutextensions =  pathinfo($image_name, PATHINFO_FILENAME);
                    $name = str_replace(" ", "", $image_name_withoutextensions);
                    $image_extension = $validated['image']->getClientOriginalExtension();
                    $image_to_store = $name . '_' . time() . '.' . $image_extension;
                    $path = $validated['image']->storeAs('public/images', trim($image_to_store));
            }

            $id = auth()->user()->id;
            $partner = Partner::find($id);
            $partner->image = $image_to_store;
            $partner->name = $validated['business_name'];
            $partner->description = $validated['business_description'];
            $partner->phone = $validated['business_phone'];
            $partner->email = $validated['business_email'];
            $partner->bank_account = $validated['business_bank_account'];
            $partner->bank_name = $validated['business_bank_name'];
            $partner->save();

            return $this->success(false, "Partner Profile Created", $partner, 200);

        }catch(Exception $e){

            return $this->error(true, "Error Creating Partner Profile", 400);

        }
    }

    public function pauseAccount(){
        try{
            $partner = Partner::find(auth()->user()->id);

            if ($partner->is_paused == false){
                $partner->is_paused = true;
                $partner->save();

                //disable all riders under partner
                $riders = Rider::where('partner_id', auth()->user()->id)->get();
                if ($riders){
                    foreach ($riders as $rider) {
                            $rider->is_enabled = false;
                            $rider->save();

                    }
                }

                return $this->success(false, "Partner has been paused from operating", $partner, 200);
            }else{
                $partner->is_paused = false;
                $partner->save();

                    //enable all riders under partner
                    $riders = Rider::where('partner_id', auth()->user()->id)->get();
                    if ($riders){
                        foreach ($riders as $rider) {
                            $rider->is_enabled = true;
                            $rider->save();

                        }
                    }
            return $this->success(false, "Partner has been un-paused from operating", $partner, 200);
            }

        }catch(Exception $e){
            return $this->error(true, "Error pausing partner", 400);
        }

    }

    public function updateProfile(Request $request){
        try{
            $validated = $request->validate([
                'image' => "required|image|mimes:jpg,png,jpeg|max:2000",
            ]);

            if (request()->hasFile('image')){
                $image_name = request()->file()->getClientOriginalName();
                $image_ext = pathfile($image_name);


            }
            $partner = Partner::find(1);
            $partner->image = $image;
            $partner->save();

        }catch(Exception $e){

        }
    }

    public function getProfile(){
        try{
            $partner_id = auth()->user()->id;
            $partner = Partner::find($partner_id);

            $data = [
                'name' => $partner->name,
                'email' => $partner->email,
                'rating' => $partner->rating,
                'phone' => $partner->phone,
                'code_name' => $partner->code_name,
                'earnings' => $partner->earnings,
                'subscription_date' => $partner->subscription_date,
                'subscription_type' => $partner->subscription()->name,
                'subscription_expiry_date' => $partner->subscription_expiry_date,
                'order_count_per_day' => $partner->order_count_per_day

            ];

            return $this->success(false, "Profile", $data, 200);
        }catch(Exception $e){
            return $this->error(true, "Error getting rider profile", 400);
        }
    }



    /*
    *
    * ENDPOINTS RELATING TO VEHICLES
    *(addVehicle, updateVehicle, disableVehicle, getAllVehicle, getOneVehicle)
    *
    **/
    public function addVehicle(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'plate_number' => 'required|string',
                'color' => 'required|string',
                'model' => 'required|string',
                'type' => 'string'
            ]);
            $id = auth()->user()->id;
            $vehicle = Vehicle::where('plate_number', $validated['plate_number'])->where('partner_id', $id)->first();
            if (!$vehicle){
                $vehicle = new Vehicle;
                $vehicle->name = $validated['name'];
                $vehicle->plate_number = $validated['plate_number'];
                $vehicle->color = $validated['color'];
                $vehicle->model = $validated['model'];
                $vehicle->partner_id = $id;
                $vehicle->type = $validated['type'];
                $vehicle->save();

                return $this->success(false, "vehicle registered", $vehicle, 200);
            }else{
                return $this->error(true, "vehicle with given plate number exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error creating vehicle", 400);
        }

    }

    public function updateVehicle(Request $request, $id){
        try{
            $validated = $request->validate([
                'name' => 'string',
                'plate_number' => 'string',
                'color' => 'string',
                'model' => 'string',
                'type' => 'string'
            ]);
            $id = auth()->user()->id;
            $vehicle = Vehicle::where('plate_number', $validated['plate_number'])->where('partner_id', $id)->first();
            if ($vehicle){
                $vehicle->name = $validated['name'] ?? $vehicle->name;
                $vehicle->plate_number = $validated['plate_number'] ?? $vehicle->plate_number;
                $vehicle->color = $validated['color'] ?? $vehicle->color;
                $vehicle->model = $validated['model'] ?? $vehicle->model;
                $vehicle->type = $validated['type'] ?? $vehicle->type;
                $vehicle->partner_id = $id ?? $vehicle->partner_id; //auth()->user()->id;
                $vehicle->save();

                return $this->success(false, "vehicle updated", $vehicle, 200);
            }else{
                return $this->error(true, "vehicle with given plate number doesn't exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error updating vehicle", 400);
        }

    }

    public function disableVehicle($id){
        try{

            $partner_id = auth()->user()->id;
            $vehicle = Vehicle::where('id', $id)->where('partner_id', $partner_id)->first();
            if ($vehicle){
                $vehicle->is_enabled = !($vehicle->is_enabled);
                $vehicle->save();

                if ($vehicle->is_enabled == false){
                    return $this->success(false, "vehicle disabled", $vehicle, 200);
                }else{
                    return $this->success(false, "vehicle enabled", $vehicle, 200);
                }

            }else{
                return $this->error(true, "vehicle with given plate number doesn't exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function getVehicles(){
        try{
            $id = auth()->user()->id;
            $vehicles = Vehicle::with('partner')->where('partner_id', $id)->get();

            return $this->success(false, "Vehicles fetched", $vehicles, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    // public function getCars(){}
    // public function getBikes(){}
    // public function getVans(){}

    public function getVehicle($id){
        try{
            $pid = auth()->user()->id;
            $vehicle = Vehicle::with('partner')->where('id', $id)->where('partner_id', $pid)->first();

            return $this->success(false, "Vehicle fetched", $vehicle, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }



    /**
    *
    *
    *ENDPOINS RELATING TO THE PARTNER'S RIDERS
    * (createRiderProfile, updateRiderProfile, getAllRiders, getOneRider,
    *disableRider, dismissRider, ordersByRider, assignOrder)
    *
    *
    */
    public function dismissRider($id){
        try{
            $partner_id = 1; //auth()->user()->id
            $rider = Rider::where('id', $id)->where('partner_id', $partner_id)->first();
            $rider->is_dismissed = true;
            $rider->save();

            return $this->success(false, "Rider has been disabled", $rider, 200);
        }catch(Exception $e){
            return $this->error(true, "Error disabling rider", 400);
        }
    }

    public function updateRider(Request $request, $id){
        try{
            $validated = $request->validate([
                'name' => 'string',
                'workname' => 'string',
                'phone' => 'string',
                'password' => 'string',
                'image' => 'image|mimes:png,jpeg,jpg|max:2000',
                'vehicle_id' => 'string'
            ]);

            if ($request->hasFile('image')){
                $image_name = $validated['image']->getClientOriginalName();
                $image_name_withoutextensions =  pathinfo($image_name, PATHINFO_FILENAME);
                $name = str_replace(" ", "", $image_name_withoutextensions);
                $image_extension = $validated['image']->getClientOriginalExtension();
                $image_to_store = $name . '_' . time() . '.' . $image_extension;
                $path = $validated['image']->storeAs('public/images', trim($image_to_store));
        }

            $rider = Rider::where('workname', $validated['workname'])->where('phone', $validated['phone'])->where('partner_id', $partner->id)->first();
            if ($rider){
                $rider->name = $validated['name'] ?? $rider->name;
                $rider->phone = $validated['phone'] ?? $rider->phone;
                $rider->workname = $validated['workname'] ?? $rider->workname;
                $rider->code_name = $validated['code_name'] ?? $rider->code_name;
                $rider->image = $$image_to_store ?? $rider->image;
                $rider->vehicle_id = $validated['vehicle_id'] ?? $rider->vehicle_id;
                $rider->password = Hash::make($validated['password']) ?? $rider->password;
                $rider->partner_id = auth()->user()->id;
                $rider->save();

                return $this->success(false, "Rider profile updated", $rider, 200);
            }else{
                return $this->error(true, "Rider with given workname or phone number  doesn't exist", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error updating rider", 400);
        }
    }

    public function ordersDoneByRider($id){
        try{

            $orders = DropOff::with('order')->where('rider_id', $id)->where('partner_id', auth()->user()->id)->get();

            return $this->success(false, "Orders done by the rider", $orders, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function createRider(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required|string',
                'workname' => 'required|string',
                'phone' => 'required|string',
                'password' => 'required|string',
                'image' => 'required|image|mimes:png,jpeg,jpg|max:2000',
                'vehicle_id' => 'required'
            ]);


            if ($request->hasFile('image')){
                $image_name = $validated['image']->getClientOriginalName();
                $image_name_withoutextensions =  pathinfo($image_name, PATHINFO_FILENAME);
                $name = str_replace(" ", "", $image_name_withoutextensions);
                $image_extension = $validated['image']->getClientOriginalExtension();
                $image_to_store = $name . '_' . time() . '.' . $image_extension;
                $path = $validated['image']->storeAs('public/images', trim($image_to_store));
        }


            $id = auth()->user()->id;
            $partner = Partner::find($id);
            $rider = Rider::where('workname', $validated['workname'])->where('phone', $validated['phone'])->where('partner_id', $id)->first();
            if (!$rider){
                $rider = new Rider;
                $rider->name = $validated['name'];
                $rider->phone = $validated['phone'];
                $rider->workname = $validated['workname'];
                $rider->vehicle_id = $validated['vehicle_id'];
                $rider->image = $image_to_store; //$validated['image'];
                $rider->password = Hash::make($validated['password']);
                $rider->partner_id = auth()->user()->id;
                $rider->save();

                return $this->success(false, "Rider registered", $rider, 200);
            }else{
                return $this->error(true, "Rider with given workname or phone number exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error creating rider", 400);
        }


    }

    public function disableRider($id){
        try{
            $partner_id = auth()->user()->id;
            $rider = Rider::where('id', $id)->where('partner_id', $partner_id)->first();
            $rider->is_enabled = !($rider->is_enabled);
            $rider->save();


            if ($rider->is_enabled == true){
                return $this->success(false, "Rider has been disabled", $rider, 200);
            }else{
                return $this->success(false, "Rider has been enabled", $rider, 200);
            }

        }catch(Exception $e){
            return $this->error(true, "Error disabling rider", 400);
        }

    }

    public function getRiders(){
        try{
            $riders = Rider::with(['partner', 'vehicle'])->where('partner_id', auth()->user()->id)->get();

            return $this->success(false, "Riders", $riders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching riders", 400);
        }

    }

    public function assignOrder(Request $request){

        try{
            $validated = $request->validate([
                'rider_id' => 'required',
                'dropoff_id' => 'required'
            ]);

            $order = DropOff::where('id', $validated['dropoff_id'])->where('partner_id', auth()->user()->id)->first();
            $rider = Rider::where('id', $validated['rider_id'])->where('partner_id', auth()->user()->id)->first();
            if ($order){

                if ($rider->vehicle->type == $order->vehicle_type){
                    if ($order->status != 'completed'){
                        $order->rider_id = $validated['rider_id'];
                        $order->save();

                        return $this->success(false, false,"Order has been successfully assigned to ". $rider->name, $order, 200);
                    }else{
                        return $this->error(true, "Order is ".$order->status, 400);
                    }
                }else{
                    return $this->error(true, "Riders vehicle type doesn't match order vehicle type!", 400);
                }

            }else{
                return $this->error(true, "Order doesn't exist", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error assigning order to rider", 400);
        }
    }






    /*
    *
    *
    * ORDERS RELATED TO PARTNERS
    * (getOrders, getOneOrder)
    *
     */
    public function getOrders(){
        try{
            $partner_id = auth()->user()->id;
            $orders = Order::with('dropoff')->where('partner_id', $partner_id)->get();

            return $this->success(false, "Orders", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching orders", 400);
        }
    }

    public function getOneOrder($id){
        try{
            $partner_id = auth()->user()->id;
            $order = Order::with('dropoff')->where('id', $id)->where('partner_id', $partner_id)->get();

            return $this->success(false, "Order", $order, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching order", 400);
        }
    }





    public function setRouteCosting(Request $request){
        try{
            $validated = $request->validate([
                'fuel_cost' => "required",
                'bike_fund' => "required",
                'ops_fee' => "required",
                'easy_log' => "required",
                'easy_disp' => "required",
                'express' => "required",
                'max_km' => "required",
                'min_km' => "required",
            ]);

            $route_costing = new RouteCosting;
            $route_costing->fuel_cost = $validated['fuel_cost'];
            $route_costing->bike_fund = $validated['bike_fund'];
            $route_costing->ops_fee = $validated['ops_fee'];
            $route_costing->easy_log = $validated['easy_log'];
            $route_costing->easy_disp = $validated['easy_disp'];
            $route_costing->express = $validated['express'];
            $route_costing->min_km = $validated['min_km'];
            $route_costing->max_km = $validated['max_km'];
            $route_costing->partner_id = auth()->user()->id;
            $route_costing->save();

            return $this->success(false, "Route-Costing Added", $route_costing,200);
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function updateRouteCosting(Request $request, $id){
        try{
            $validated = $request->validate([
                'fuel_cost' => "required",
                'bike_fund' => "required",
                'ops_fee' => "required",
                'easy_log' => "required",
                'easy_disp' => "required",
                'express' => "required",
                'max_km' => "required",
                'min_km' => "required",
            ]);
            $partner_id = auth()->user()->id;
            $route_costing = RouteCosting::where('id', $id)->where('partner_id', $partner_id)->first();

            $route_costing->fuel_cost = $validated['fuel_cost'] ?? $route_costing->fuel_cost;
            $route_costing->bike_fund = $validated['bike_fund'] ?? $route_costing->bike_fund;
            $route_costing->ops_fee = $validated['ops_fee'] ?? $route_costing->ops_fee;
            $route_costing->easy_log = $validated['easy_log'] ?? $route_costing->easy_log;
            $route_costing->easy_disp = $validated['easy_disp'] ?? $route_costing->easy_disp;
            $route_costing->express = $validated['express'] ?? $route_costing->express;
            $route_costing->min_km = $validated['min_km'] ?? $route_costing->min_km;
            $route_costing->max_km = $validated['max_km'] ?? $route_costing->max_km;
            $route_costing->save();

            return $this->success(false, "Route-Costing Updating", $route_costing, 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function subscribe(Request $request){
        $validated = $request->validate([
            'subscription_id' => 'required'
        ]);

        $subs = Subscription::find($validated['subscription_id']);
        if ($subs){
            //check if partner has enough in her wallet
            //take money from partner wallet
            $partner_id = auth()->user()->id;
            $partner = Partner::find($partner_id);
            $partner->subscription_id = $subs->id;
            $partner->subscription_status = 'paid';

            if ($subs->name == 'Free'){
                $partner->order_count_per_day = 5;
            }else if ($subs->name == 'Starter'){
                $partner->order_count_per_day = 15;
            }else if ($subs->name == 'Business'){
                $partner->order_count_per_day = 25;
            }else {
                $partner->order_count_per_day = 'unlimited';
            }
             //check what type of subscription and input appropiately here
            $partner->save();

            return $this->success(false, "Subscribtion successful", $partner, 200);
        }else{
            return $this->error(true, "Subscription not found", 400);
        }
    }

    public function addOperatingHours(Request $request){
        try{
            $validated = $request->validate([
                'day' => 'required|string',
                'start_time' => 'required|string',
                'end_time' => 'required|string'
            ]);

            $operating_hours = new OperatingHours;
            $operating_hours->day = $validated['day'];
            $operating_hours->start_time = $validated['start_time'];
            $operating_hours->end_time = $validated['end_time'];
            $operating_hours->partner_id = auth()->user()->id;
            $operating_hours->save();

            return $this->success(false, "Operating Hours added", $operating_hours, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't add operating hours", 400);
        }
    }

    public function updateOperatingHours(Request $request, $id){
        try{
            $validated = $request->validate([
                'day' => 'required|string',
                'start_time' => 'required|string',
                'end_time' => 'required|string'
            ]);

            $operating_hours = OperatingHours::where('id', $id)->where('partner_id', 1)->first();
            $operating_hours->day = $validated['day'] ?? $operating_hours->day;
            $operating_hours->start_time = $validated['start_time'] ?? $operating_hours->start_time;
            $operating_hours->end_time = $validated['end_time'] ?? $operating_hours->end_time;
            //$operating_hours->partner_id = 1; //auth()->user()->id;
            $operating_hours->save();

            return $this->success(false, "Operating Hours updated", $operating_hours, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't update operating hours", 400);
        }
    }


    public function getPartnerHistory(){
        try{
            $id = auth()->user()->id;
            $history = History::where('partner_id', $id)->get();

            return $this->success(false, "Partner history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find partner history", 400);
        }
    }

    public function makeTopPartner(){
        try{
            //pay to ba a top partner
            $id = auth()->user()->id;
            $partner = Partner::find($id);
            $partner->is_top_partner = true;
            $partner->top_partner_pay_date = now();
            $partner->save();

            return $this->success(false, "Partner has been made a top partner", $partner, 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }


    //wallet transactions


}
