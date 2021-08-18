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


    public function allTopPartner(){
        try{
            $partners = Partner::where('is_top_partner', true)->get();

            return $this->success(false, "All Top Partners", $partners, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't find all partners", 400);
        }
    }

    public function allPartner(){
        try{
            $partners = Partner::all();

            return $this->success(false, "All Partners", $partners, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't find all partners", 400);
        }
    }



    /*
    *
    *   PARTNER AUTHENTICATION
    *   (signup, login)
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
                $partner->order_count_per_day = 5;
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
                'email' => "required|string",
                "password" => "required|string"
            ]);

            $partner = Partner::where('email', $validated['email'])->first();
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
            }else{
                return $this->error(true, "Error logging partner", 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging partner", 400);
        }
    }

    public function forgotPassword(){}



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
            $partner->image =  env('APP_URL') .'/storage/images/'.$image_to_store;
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
                'image' => "image|mimes:jpg,png,jpeg|max:2000",
                'business_name' => 'string',
                'business_description' => 'string',
                'business_phone' => 'string',
                'business_email' => 'string',
                'business_bank_account' => 'string',
                'business_bank_name' => 'string',

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
                    $partner->image =  env('APP_URL') .'/storage/images/'.$image_to_store ?? $partner->image;
                    $partner->name = $validated['business_name'] ?? $partner->name;
                    $partner->description = $validated['business_description'] ?? $partner->description;
                    $partner->phone = $validated['business_phone'] ?? $partner->phone;
                    $partner->email = $validated['business_email'] ?? $partner->email;
                    $partner->bank_account = $validated['business_bank_account'] ?? $partner->bank_account;
                    $partner->bank_name = $validated['business_bank_name'] ?? $partner->bank_name;
                    $partner->save();

                    return $this->success(false, "Partner Profile Updated", $partner, 200);

        }catch(Exception $e){
            return $this->error(true, "partner profile couldn't be updated", 400);
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

            $partner = Partner::find(auth()->user()->id);
            if ($partner->vehicle_count > 0 || $partner->vehicle_count == 'unlimited'){
                $validated = $request->validate([
                    'name' => 'required|string',
                    'plate_number' => 'required|string',
                    'color' => 'string',
                    'model' => 'string',
                    'type' => 'required|string'
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


                    if ($partner->vehicle_count != 'unlimited'){
                        $partner->vehicle_count--;
                        $partner->save();
                    }


                    return $this->success(false, "vehicle registered", $vehicle, 200);
                }else{
                    return $this->error(true, "vehicle with given plate number exists", 400);
                }

            }else{
                return $this->error(true, "You have exceeded the number of vehicles to register", 400);
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
            $vehicles = Vehicle::with(['partner', 'rider'])->where('partner_id', $id)->get();

            return $this->success(false, "Vehicles fetched", $vehicles, 200);

        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function getVehicle($id){
        try{
            $pid = auth()->user()->id;
            $vehicle = Vehicle::with(['partner', 'rider'])->where('id', $id)->where('partner_id', $pid)->first();

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
                $rider->image =  env('APP_URL') .'/storage/images/'.$image_to_store ?? $rider->image;
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
                $rider->image = env('APP_URL') .'/storage/images/'.$image_to_store; //$validated['image'];
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
                'fuel_cost' => "required",//base_fare
                'bike_fund' => "required",//partner_cost
                'ops_fee' => "required",//express_markup
                'easy_log' => "required",
                'easy_disp' => "required",
                'express' => "required",
                'max_km' => "required",
                'min_km' => "required",
            ]);

            $route_costing = new RouteCosting;
            // $route_costing->base_fare = $validated['base_fare'];
            // $route_costing->partner_cost = $validated['partner_cost'];
            // $route_costing->express_markup = $validated['express_markup'];
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

            //$calculation = (($distance * $fuel_cost) + $rider_salary + ($distance * $bike_fund )) * $ops_fee * $easy_log * $easy_disp;
            //dd($route_cost);

            return $this->success(false, "Route-Costing Added", $route_costing,200);
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function updateRouteCosting(Request $request, $id){
        try{
            $validated = $request->validate([
                'fuel_cost' => "required",//base_fare
                'bike_fund' => "required",//partner_cost
                'ops_fee' => "required",//express_markup
                'easy_log' => "required",
                'easy_disp' => "required",
                'express' => "required",
                'max_km' => "required",
                'min_km' => "required",
            ]);
            $partner_id = auth()->user()->id;
            $route_costing = RouteCosting::where('id', $id)->where('partner_id', $partner_id)->first();
            // $route_costing->base_fare = $validated['base_fare'] ?? $route_costing->base_fare;
            // $route_costing->partner_cost = $validated['partner_cost'] ?? $route_costing->partner_cost;
            // $route_costing->express_markup = $validated['express_markup'] ?? $route_costing->express_markup;
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
                $partner->vehicle_count = 5;
            }else if ($subs->name == 'Starter'){
                $partner->order_count_per_day = 15;
                $partner->vehicle_count = 15;
            }else if ($subs->name == 'Business'){
                $partner->order_count_per_day = 25;
                $partner->vehicle_count = 25;
            }else {
                $partner->order_count_per_day = 'unlimited';
                $partner->vehicle_count = 'unlimited';
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

            $operating_hours->day = strtolower($validated['day']);
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
            $operating_hours->day = strtolower($validated['day']) ?? $operating_hours->day;
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

    public function dashboard(){
        //partner profile
        //order count
        try{
            $partner = Partner::find(auth()->user()->id);
            if (isset($partner)){
                $data = [
                    'partner' => $partner,
                    'count' => $this->count() //pickedup, vehicle,pending, delivered,
                ];
                return $this->success(false, "Dashboard", $data, 200);
            }else{
                return $this->error(true, "No Partner found", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error creating user", 400);
        }
    }

    public function count(){
        $id = auth()->user()->id;
        $dropoffs = Dropoff::with('order')->where('partner_id', $id)->get();
        $vehicles = Vehicle::where('partner_id', $id)->get();
        $pending = [];
        $delivered = [];
        $picked = [];

        foreach ($dropoffs as $dropoff){
            if ($dropoff->status == 'pending'){
                array_push($pending, $dropoff);
            }
            if ($dropoff->status == 'delivered'){
                array_push($delivered, $dropoff);
            }
            if ($dropoff->status == 'picked'){
                array_push($picked, $dropoff);
            }
        }
        $data = [
            'pending' => count($pending),
            'delivered' => count($delivered),
            'picked' => count($picked),
            'vehicles' => count($vehicles)
        ];

        return $data;



    }


    public function countForVehicle($id){
        $pid = auth()->user()->id;
        $dropoffs = Dropoff::with('order')->where('partner_id', $pid)->where('vehicle_id', $id)->get();

        $pending = [];
        $delivered = [];
        $picked = [];

        foreach ($dropoffs as $dropoff){
            if ($dropoff->status == 'pending'){
                array_push($pending, $dropoff);
            }
            if ($dropoff->status == 'delivered'){
                array_push($delivered, $dropoff);
            }
            if ($dropoff->status == 'picked'){
                array_push($picked, $dropoff);
            }
        }
        $data = [
            'pending' => count($pending),
            'delivered' => count($delivered),
            'picked' => count($picked),
        ];

        return $this->success(false, "Total Count for Vehicle", $data, 200);



    }

    public function getOrderbyVehicle($id){
        try{
            $partner_id = auth()->user()->id;
            $orders = DropOff::with('order')->where('partner_id', $partner_id)->where('vehicle_id', $id)->get();

            return $this->success(false, "Orders", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching orders", 400);
        }
    }

    public function getOrderByStatus($status){

        try{


            $orders = Order::with('dropoff')->where('partner_id', auth()->user()->id)->get();
            $data = [];


            switch($status){
                case 'pending':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'pending'){
                                $dro['dropoff'] = $this->getOneDropoff($dro->id);
                                array_push($data, $dro);
                            }
                        }
                    }

                    return $this->success(false, "Pending Orders", $data, 200);
                case 'delivered':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'delivered'){
                                $dro['dropoff'] = $this->getOneDropoff($dro->id);
                                array_push($data, $dro);
                            }
                        }
                    }

                    return $this->success(false, "Delivered Orders", $data, 200);

                case 'pickedup':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'picked'){
                                $dro['dropoff'] = $this->getOneDropoff($dro->id);
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


    }
