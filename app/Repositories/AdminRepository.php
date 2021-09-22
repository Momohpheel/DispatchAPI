<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Traits\Response;
use App\Models\Partner;
use App\Models\Admin;
use App\Models\User;
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
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\ForgotPassword;
use Illuminate\Support\Str;
use Carbon\Carbon;


class AdminRepository implements AdminRepositoryInterface{

    use Response;

    public function signup(Request $request){
        try{
            $validated = $request->validate([
                'name' => "required|string",
                'phone' => "required|string",
                'email' => "required|string",
                "password" => "required|string",
            ]);

            $admin = Admin::where('email', $validated['email'])->first();
            if (!$admin){
                $admin = new Admin;
                $admin->name = $validated['name'];
                $admin->phone = $validated['phone'];
                $admin->email = $validated['email'];
                $admin->password = Hash::make($validated['password']);
                $admin->save();
                $access_token = $admin->createToken('authToken')->accessToken;

                // $data = [
                //     "access_token" => $access_token
                // ];
                return $this->success(false,"Admin registered", $access_token, 200);
            }else{
                return $this->error(true, "Admin exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error creating Admin", 400);
        }
    }

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'email' => "required|string",
                "password" => "required|string"
            ]);

            $admin = Admin::where('email', $validated['email'])->first();
            if ($admin){
                $check = Hash::check($validated['password'], $admin->password);
                if ($check){

                    $access_token = $admin->createToken('authToken')->accessToken;
                    // $data = [
                    //     "access_token" => $access_token
                    // ];
                    return $this->success(false, "Admin found", $access_token, 200);
                }else{
                    return $this->error(true, "Error logging Admin", 400);
                }
            }else{
                return $this->error(true, "Error logging admin", 400);
            }

        }catch(Exception $e){
            return $this->error(true, "Error logging admin", 400);
        }
    }

    public function dashboard(){

        try{
                $admin = Admin::find(auth()->user()->id);

                $data = [
                    'adminProfile' => $admin,
                    'count' => $this->count()
                ];

                return $this->success(false, "Dashboard", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "Error creating user", 400);
        }
    }

    public function count(){

        //count -  partners, users, orders, pending orders, cancelled orders, delivered, cancelled orders

        $partners = Partner::all();
        $dropoffs = Dropoff::all();
        $users = User::all();

        $pending = [];
        $delivered = [];
        $picked = [];
        $cancelled = [];

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
            if ($dropoff->status == 'cancelled'){
                array_push($cancelled, $dropoff);
            }
        }
        $data = [
            'users' => count($users),
            'partners' => count($partners),
            'orders' => count($dropoffs),
            'pendingOrders' => count($pending),
            'deliveredOrders' => count($delivered),
            'pickedOrders' => count($picked),
            'cancelledOrders' => count($cancelled)
        ];

        return $data;
    }

    public function allPartners(){
        try{
            $partners = Partner::with('subscription')->get();

            return $this->success(false, "All Partners", $partners, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't find all partners", 400);
        }
    }

    public function allUsers(){
        try{
            $users = User::latest()->get();

            return $this->success(false, "All Users", $users, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't find all users", 400);
        }
    }

    public function disablePartner($id){
        try{
            $partner = Partner::where('id', $id)->latest()->first();

            if ($partner->is_enabled == false){
                $partner->is_enabled = true;
                $partner->save();

                //disable all riders under partner
                $riders = Rider::where('partner_id', auth()->user()->id)->get();
                if ($riders){
                    foreach ($riders as $rider) {
                            $rider->is_enabled = true;
                            $rider->save();

                    }
                }

                return $this->success(false, "Partner has been enabled from operating", $partner, 200);
            }else{
                $partner->is_enabled = false;
                $partner->save();

                    //enable all riders under partner
                    $riders = Rider::where('partner_id', auth()->user()->id)->get();
                    if ($riders){
                        foreach ($riders as $rider) {
                            $rider->is_enabled = false;
                            $rider->save();

                        }
                    }
            return $this->success(false, "Partner has been disabled from operating", $partner, 200);
            }

        }catch(Exception $e){
            return $this->error(true, "Error pausing partner", 400);
        }

    }

    public function allTopPartners(){
        try{
            $partners = Partner::where('is_top_partner', true)->latest()->get();

            return $this->success(false, "All Top Partners", $partners, 200);
        }catch(Exception $e){
            return $this->error(true, "Couldn't find all partners", 400);
        }
    }

    public function ridersByPartner($id){
        try{
            $rider = Rider::where('partner_id', $id)->latest()->get();

            return $this->success(false, "Partner's riders", $rider, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }

    }

    public function ordersByPartner($id){
        try{
            $orders = Dropoff::with(['partner', 'order'])->where('partner_id', $id)->latest()->get();

            foreach($orders as $order){
                $userId = $order->order->user_id ?? null;
                $user = User::where('id', $userId)->first() ?? null;
                $order['user'] = $user;
            }

            return $this->success(false, "Partner's order", $orders, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

    public function getVehicles($id){
        try{
            $vehicle = Vehicle::where('partner_id', $id)->latest()->get();

            return $this->success(false, "Partner's vehicles", $vehicle, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }

    }

    public function allOrders(){
        try{
            $orders = Dropoff::latest()->get();

            foreach($orders as $order){
                $userId = $order->order->user_id ?? null;
                $user = User::where('id', $userId)->first() ?? null;
                $order['user'] = $user;
            }

            return $this->success(false, "All Orders", $orders, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }


    public function changeOrderStatus(Request $request, $id){
        try{
            $validated = $request->validate([
                'status' => 'required|string'
            ]);

            if ($validated['status'] == 'pending' || $validated['status'] == 'delivered' || $validated['status'] == 'picked' || $validated['status'] == 'cancelled'){
                $order = Dropoff::where('id', $id)->first();
                $order->status = $validated['status'];
                $order->save();
            }else{
                return $this->error(true, "Wrong status", 400);
            }

            return $this->success(false, "Order", $order, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

    public function ridersOrders($id){
        try{
            $orders = Dropoff::where('rider_id', $id)->latest()->get();

            foreach($orders as $order){
                $userId = $order->order->user_id ?? null;
                $user = User::where('id', $userId)->first() ?? null;
                $order['user'] = $user;
            }

            return $this->success(false, "Riders Orders...", $orders, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }
    public function oneOrder($id){
        try{
            $order = Dropoff::with(['partner', 'order', 'vehicle', 'rider'])->where('id', $id)->first();


                $userId = $order->order->user_id ?? null;
                $user = User::where('id', $userId)->first() ?? null;
                $order['user'] = $user;


            return $this->success(false, "Order", $order, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }


    public function onePartner($id){
        try{
            $partner = Partner::where('id', $id)->first();

            $now = Carbon::now()->addHour();

            $vehicles = Vehicle::where('partner_id', $partner->id)->get();
            $todaysDropoff = Dropoff::where('partner_id', auth()->user()->id)->where('payment_status', '!=', 'cancelled')->where('created_at', 'LIKE',$now->format('Y-m-d').'%')->get();
            //vehicle left
            $vehicles_count = count($vehicles);
            $orders_count = count($todaysDropoff);
            if ($partner->subscription->vehicles_allowed == 'unlimited'){
                $vehicle_left = 'unlimited';
            }else{
                $vehicle_left = $partner->subscription->vehicles_allowed - $vehicles_count;
            }

            if ($partner->subscription->orders_allowed == 'unlimited'){
                $orders_left = 'unlimited';
            }else{
                $orders_left = $partner->subscription->orders_allowed - $orders_count;
            }
                 $date = Carbon::parse($partner->subscription_expiry_date);
                 $diff = $date->diffInDays($now);




                 $partner['subscription_name'] = $partner->subscription->name;
                 $partner['vehicles_count'] = $vehicles_count;
                 $partner['vehicles_left'] = (string)$vehicle_left;
                 $partner['order_count'] = $orders_count;
                 $partner['orders_left'] = (string)$orders_left;
                 $partner['subscription_validity'] = $diff;


            return $this->success(false, "Partner", $partner, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }


    public function oneUser($id){
        try{
            $user = User::where('id', $id)->first();


            $orders = Dropoff::where('user_id', $id)->latest()->get();
            $delivered = [];

            foreach ($orders as $order){

                if ($order->status == 'delivered'){
                    array_push($delivered, $order);
                }

            }

            $user['delivered_orders_count'] = count($delivered);
            $user['all_orders_count'] = count($orders);



            return $this->success(false, "User", $user, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

    public function usersOrders($id){
        try{
            $dropoff = Dropoff::with(['order', 'rider', 'vehicle'])->where('id', $id)->latest()->first();

            $user = User::where('id', $dropoff->order->user_id)->first();
            $dropoff['user'] = $user;
            if (isset($dropoff)){
                return $this->success(false, "User's Orders", $dropoff, 200);
                // return $dropoff;
            }else{
                return $this->error(true, "No dropoff found", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

    public function createPartner(Request $request){
        try{

            $validated = $request->validate([
                "password" => "required|string",
                'code_name' => "required|string",
                'image' => "required|image|mimes:jpg,png,jpeg|max:2000",
                'business_name' => 'required|string',
                'business_phone' => 'required|string',
                'business_email' => 'required|string|email',
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
                $partner->subscription_date = Carbon::now();
                $partner->subscription_expiry_date = Carbon::now()->addDays(30);
                $partner->top_partner_expiry_date = Carbon::now()->addDays(30);                $partner->subscription_status = 'not paid';
                $partner->order_count_per_day = 5;
                $partner->save();
                $access_token = $partner->createToken('authToken')->accessToken;

                $partner['access_token'] = $access_token;
                return $this->success(false,"Partner registered", $partner, 200);
            }else{
                return $this->error(true, "Partner exists", 400);
            }
        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

    public function hashId($id){
        $text = 'OrderID';


    }
}
