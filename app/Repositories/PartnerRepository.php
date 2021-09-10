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
use App\Models\User;
use App\Models\routeCosting as RouteCosting;
use App\Models\DropOff;
use App\Models\Vehicle;
use App\Models\Address;
use App\Traits\Logs;
use App\Repositories\Interfaces\PartnerRepositoryInterface;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\ForgotPassword;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                "password" => "required|string",
                "code_name" => "required|string",
                'image' => "required|image|mimes:jpg,png,jpeg|max:2000",
                'business_name' => 'required|string',
                'business_phone' => 'required|string',
                'business_email' => 'required|string',
                'business_bank_account' => 'required|string',
                'business_bank_name' => 'required|string',
            ]);

            $partner = Partner::where('code_name', $validated['code_name'])->where('name', $validated['business_name'])->first();
            if (!$partner){
                if ($request->hasFile('image')){
                    $image_name = $validated['image']->getClientOriginalName();
                    $image_name_withoutextensions =  pathinfo($image_name, PATHINFO_FILENAME);
                    $name = str_replace(" ", "", $image_name_withoutextensions);
                    $image_extension = $validated['image']->getClientOriginalExtension();
                    $image_to_store = $name . '_' . time() . '.' . $image_extension;
                    $path = $validated['image']->storeAs('public/images', trim($image_to_store));
            }
                $partner = new Partner;
                $partner->name = $validated['business_name'];
                $partner->phone = $validated['business_phone'];
                $partner->email = $validated['business_email'];
                $partner->code_name = $validated['code_name'];
                $partner->password = Hash::make($validated['password']);
                $partner->image =  env('APP_URL') .'/storage/images/'.$image_to_store;
                $partner->bank_account = $validated['business_bank_account'];
                $partner->bank_name = $validated['business_bank_name'];
                //$partner->image =   env('APP_URL') .'/storage/images/defaultPartner.png';
                $partner->subscription_id = 1;
                $partner->order_count_per_day = 5;
                $partner->save();
                $access_token = $partner->createToken('authToken')->accessToken;

                // $data = [
                //     "partner" => $partner,
                //     "access_token" => $access_token
                // ];
                $partner['access_token'] = $access_token;
                return $this->success(false,"Partner registered", $partner, 200);
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

            $partner = Partner::with('subscription')->where('id',auth()->user()->id)->first();
            $vehicles = Vehicle::where('partner_id', $partner->id)->get();
            //if ($partner->vehicle_count > 0 || $partner->vehicle_count == 'unlimited'){
                if ($partner->subscription->vehicle_count > count($vehicles) || $partner->subscription->vehicle_count == 'unlimited'){
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
            $partner_id = auth()->user()->id;
            $vehicle = Vehicle::where('id', $id)->where('partner_id', $partner_id)->first();
            $vehicle_exists_somewhere = Vehicle::where('id','!=', $id)->where('plate_number', $validated['plate_number'])->where('partner_id', $partner_id)->first();


                if ($vehicle){
                    if ($vehicle_exists_somewhere){
                        return $this->error(true, "Vehicle already belongs to partner", 400);
                    }else{
                        $vehicle->name = $validated['name'] ?? $vehicle->name;
                        $vehicle->plate_number = $validated['plate_number'] ?? $vehicle->plate_number;
                        $vehicle->color = $validated['color'] ?? $vehicle->color;
                        $vehicle->model = $validated['model'] ?? $vehicle->model;
                        $vehicle->type = $validated['type'] ?? $vehicle->type;
                        $vehicle->partner_id = $id ?? $vehicle->partner_id; //auth()->user()->id;
                        $vehicle->save();

                        return $this->success(false, "vehicle updated", $vehicle, 200);
                    }
                }else{
                    return $this->error(true, "vehicle doesn't exists", 400);
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
                'pin' => 'string|max:4',
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
                'password' => 'required|string|max:4',
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
                $rider->rating = 0;
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
            $riders = Rider::with(['partner', 'vehicle'])->where('is_available', true)->where('partner_id', auth()->user()->id)->get();

            return $this->success(false, "Riders", $riders, 200);
        }catch(Exception $e){
            return $this->error(true, "Error fetching riders", 400);
        }

    }

    public function getRider($id){
        try{
            $rider = Rider::with(['partner', 'vehicle'])->where('is_available', true)->where('partner_id', auth()->user()->id)->first();

            return $this->success(false, "Rider", $rider, 200);
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
            $rider = Rider::with('vehicle')->where('id', $validated['rider_id'])->where('is_available', true)->where('partner_id', auth()->user()->id)->first();
            if ($order){

                if ($rider->vehicle->type == $order->vehicle_type){
                    if ($order->status != 'completed'){
                        $order->rider_id = $validated['rider_id'];
                        $order->save();

                        return $this->success(false,"Order has been successfully assigned to ". $rider->name, $order, 200);
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
                'express' => "required",
                'base_fare' => "required",
                'cost_perkm' => "required",
            ]);

            $route_costing = new RouteCosting;
            $route_costing->base_fare = $validated['base_fare'];
            $route_costing->cost_perkm = $validated['cost_perkm'];
            $route_costing->express = $validated['express'];
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
                'express' => "string",
                'base_fare' => "string",
                'cost_perkm' => "string",
            ]);
            $partner_id = auth()->user()->id;
            $route_costing = RouteCosting::where('id', $id)->where('partner_id', $partner_id)->first();
            $route_costing->base_fare = $validated['base_fare'] ?? $route_costing->base_fare;
            $route_costing->cost_perkm = $validated['cost_perkm'] ?? $route_costing->cost_perkm;
            $route_costing->express = $validated['express'] ?? $route_costing->express;
            $route_costing->partner_id = auth()->user()->id;
            $route_costing->save();

            return $this->success(false, "Route-Costing Updating", $route_costing, 200);
        }catch(Exception $e){
            return $this->error(true, "Error occured", 400);
        }
    }

    public function subscribe(Request $request){
        $validated = $request->validate([
            'subscription_id' => 'required',
            'payment_type' => 'required|string'
        ]);


        $subs = Subscription::find($validated['subscription_id']);
        if ($subs){

            //check if partner has enough in her wallet
            //take money from partner wallet
            $partner_id = auth()->user()->id;
            $partner = Partner::find($partner_id);
            if ($request->payment_type == 'wallet'){
                if ($partner->wallet > $subs->price){
                    $partner->wallet = $partner->wallet - $subs->price;
                    $log = true;
                }else{
                    return $this->error(true, "Partner doesn't have enough funds in her wallet", 400);
                }
            }else if ($request->payment_type == 'card'){
                //card...
                $log = $this->payment($request);
            }

            if ($log){
                $partner->subscription_id = $subs->id;
                //check what type of subscription and input appropiately here
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

                $partner->subscription_expiry_date = Carbon::now()->addDays(30);
                $partner->subscription_date = Carbon::now();
                $partner->subscription_status = 'paid';

                $partner->save();


                return $this->success(false, "Subscribtion successful", $partner, 200);
            }
        }else{
            return $this->error(true, "Subscription not found", 400);
        }
    }

    public function addOperatingHours(Request $request){
        try{
            $validated = $request->validate([
                'time.*.day' => 'required|string',
                'time.*.start_time' => 'required|string',
                'time.*.end_time' => 'required|string'
            ]);



            foreach ($validated['time'] as $time){
                $operating_hours = new OperatingHours;
                $operating_hours->day = strtolower($time['day']);
                $operating_hours->start_time = $time['start_time'];
                $operating_hours->end_time = $time['end_time'];
                $operating_hours->partner_id = auth()->user()->id;
                $operating_hours->save();
            }

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
            $expiry_date = Carbon::now()->addDays(30)->toDateString();
            $partner->top_partner_expiry_date = $expiry_date;
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
            //manually check top-partner expiry
            //manually check subscription expiry


            $partner = Partner::find(auth()->user()->id);

            if (isset($partner)){
                if ($partner->is_top_partner == true){
                    $now = Carbon::now()->addHour();
                    if ($partner->top_partner_expiry_date == $now){
                        $partner->is_top_partner = false;
                        $partner->top_partner_expiry_date = null;
                        $partner->save();
                    }
                }

                if (isset($partner->subscription_expiry_date)){
                    $now = Carbon::now()->addHour();
                    if ($partner->subscription_expiry_date == $now){
                        $partner->subscription_id = 1;
                        $partner->subscription_date = null;
                        $partner->subscription_expiry_date = null;
                        $partner->save();
                    }
                }

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


            $orders = Dropoff::where('partner_id', auth()->user()->id)->get();
            $data = [];


            switch($status){
                case 'pending':
                    foreach ($orders as $order){
                        // foreach ($order->dropoff as $dro){
                            if ($order->status == 'pending'){
                                $rider = Rider::find($order->rider_id);
                                $r_order = Order::find($order->order_id);
                                //$user = User::find($r_order->user_id);

                                $order['rider'] = $rider;
                                $order['order'] = $r_order;
                                //$order['user'] = $user;
                                return $r_order->user_id;
                                //$order['dropoff'] = $this->getOneDropoff($order->id);
                                array_push($data, $order);
                            }
                        //}
                    }

                    return $this->success(false, "Pending Orders", $data, 200);
                case 'delivered':
                    foreach ($orders as $order){
                        //foreach ($order->dropoff as $dro){
                            if ($order->status == 'delivered'){
                                $order['dropoff'] = $this->getOneDropoff($order->id);
                                array_push($data, $order);
                            }
                        //}
                    }

                    return $this->success(false, "Delivered Orders", $data, 200);

                case 'pickedup':
                    foreach ($orders as $order){
                        //foreach ($order->dropoff as $dro){
                            if ($order->status == 'picked'){
                                $order['dropoff'] = $this->getOneDropoff($order->id);
                                array_push($data, $order);
                            }
                        //}
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


    public function fundWallet($request){
        try{



            if ($request['trans_status'] == 'success'){
                $partner = Partner::where('id', auth()->user()->id)->first();
                $partner->wallet = $partner->wallet + $request['amount'];
                $partner->save();
                //wallet history
                $this->walletLogs('wallet', "You added ".$request['amount']." to your wallet", auth()->user()->id, 'partner');
                //trnasaction history
                $this->transactionLog('Funding Wallet', $partner->name. " added ".$request['amount']." to their wallet", $request['amount'] ,auth()->user()->id, 'partner');
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

                'reference_num' => 'required',

                'subscription_id' => 'string',
                'type' => 'required',
                'status' => 'required',


                'amount' => 'required',
                'origin_of_payment' => 'required',
                'paystack_message' => 'required'
            ]);
            $partner = Partner::find(auth()->user()->id);

            if ($validated['type'] == 'subscriptionWithCard'){
                //code...

                $partner = Partner::find(auth()->user()->id);
                if ($validated['trans_status'] == 'success'){
                    $subs = Subscription::find($validated['subscription_id']);
                    if ($subs){
                        $partner->subscription_id = $validated['subscription_id'];
                        $partner->subscription_expiry_date = Carbon::now()->addDays(30);
                        $partner->subscription_date = Carbon::now();
                        $partner->subscription_status = 'paid';
                        $partner->save();
                    }

                    //log transactions
                    //$this->transactionLog('Delivery Fees', $validated['customer_name']." paid for an order", (int)$dropoff->price , auth()->user()->id, 'user');
                    //$this->walletLogs('wallet', $validated['amount']." was paid from your card for a job", auth()->user()->id, 'user');

                    $log = $this->paymentLog($validated);

                    return $log;



                }


            }else if ($validated['type'] == 'subscriptionWithWallet'){
                $subs = Subscription::find($validated['subscription_id']);
                if ($subs){
                    if ($partner->wallet > $subs->price){
                        $partner->wallet = $partner->wallet - $subs->price;

                        $partner->subscription_id = $validated['subscription_id'];
                        $partner->subscription_expiry_date = Carbon::now()->addDays(30);
                        $partner->subscription_date = Carbon::now();
                        $partner->subscription_status = 'paid';
                        $partner->save();

                        $log = $this->paymentLog($validated);

                        return $log;
                    }else{
                        return $this->error(true, "Partner doesn't have enough funds in her wallet", 400);
                    }
                }
            }
            else if ($validated['type'] == 'fundWallet'){
                $log =  $this->fundWallet($validated);
            }
            else if ($validated['type'] == 'topPartnerwithCard'){

                if ($validated['trans_status'] == 'success'){

                        $partner->is_top_partner = true;
                        $partner->top_partner_expiry_date = Carbon::now()->addDays(30);
                        $partner->save();


                    //log transactions
                    //$this->transactionLog('Delivery Fees', $validated['customer_name']." paid for an order", (int)$dropoff->price , auth()->user()->id, 'user');
                    //$this->walletLogs('wallet', $validated['amount']." was paid from your card for a job", auth()->user()->id, 'user');

                    $log = $this->paymentLog($validated);

                    return $log;

                }
            }
            else if ($validated['type'] == 'topPartnerwithWallet'){
                if ($validated['trans_status'] == 'success'){
                    $toppartnerPrice = (int)$validated['amount'];
                    if ($partner->wallet > $toppartnerPrice){

                        $partner->wallet = $partner->wallet - $toppartnerPrice;
                        $partner->is_top_partner = true;
                        $partner->top_partner_expiry_date = Carbon::now()->addDays(30);
                        $partner->save();

                        $log = $this->paymentLog($validated);

                        return $log;
                    }else{
                        return $this->error(true, "Partner doesn't have enough funds in her wallet", 400);
                    }
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

    public function getTransactionHistory(){
           try{
                $transLogs = TransactionLogs::where('partner_id', auth()->user()->id)->get();

                return $this->success(false, "Transaction history...", $transLogs , 200);
            }catch(Exception $e){
                return $this->error(true, "Error occured!", 400);
            }
        }
    }

