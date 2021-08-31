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
            $partners = Partner::all();

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
            $partner = Partner::where('id', $id)->latest()->get();

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
            $vehicle = Vehicle::where('partner_id', $id)->latest()->first();

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

            if ($validated['status'] != 'pending' || $validated['status'] != 'delivered' || $validated['status'] != 'picked' || $validated['status'] != 'cancelled'){
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

            return $this->success(false, "Partner", $partner, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }


    public function oneUser($id){
        try{
            $user = User::where('id', $id)->first();

            return $this->success(false, "User", $user, 200);


        }catch(Exception $e){
            return $this->error(true, "Error: ".$e->getMessage(), 400);
        }
    }

}
