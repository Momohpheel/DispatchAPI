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

    public function signup(Request $request){
        try{
            $validated = $request->validate([
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

    public function allPartners(){

    }

    public function disablePartner(){

    }

    public function allTopPartners(){

    }

    public function ridersByPartner(){

    }

    public function ordersByPartner(){

    }


    public function allTransactions(){

    }

    public function transactionByPartner(){

    }

    public function allOrders(){

    }

}
