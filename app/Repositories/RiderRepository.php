<?php
namespace App\Repositories;

use App\Traits\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Partner;
use App\Models\History;
use App\Models\Rider;
use App\Traits\Logs;
use App\Models\Order;
use App\Models\DropOff;
use App\Models\Address;
use App\Models\gpsLog;
use App\Repositories\Interfaces\RiderRepositoryInterface;
use Illuminate\Http\Request;

class RiderRepository implements RiderRepositoryInterface{

    use Response, Logs;

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'workname' => "required|string",
                "pin" => "required|string"
            ]);


                $rider = Rider::where('workname', $validated['workname'])->first();
                if ($rider){
                    $check = Hash::check($validated['pin'], $rider->password);
                    if ($check){
                        $access_token = $rider->createToken('authToken')->accessToken;
                        //$data = ["access_token" => $access_token];
                        $rider['access_token'] =  $access_token;
                        return $this->success(false, "rider found", $rider, 200);
                    }else{
                        return $this->error(true, "Password incorrect", 400);
                    }
                }else{
                    return $this->error(true, "Rider doesn't exist", 400);
                }



        }catch(Exception $e){
            return $this->error(true, "Error logging rider", 400);
        }
    }

    public function dashboard($id){
        try{

            $rider = Rider::find($id);

            $data = [
                'rider' => $rider,
                'count' => $this->count($rider->partner_id)
            ];

            return $this->success(false, "Dashboard", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "Error", 400);
        }
    }

    public function getProfile(){
        try{
            $rider_id = auth()->user()->id;
            $rider = Rider::find($rider_id);

            $data = [
                'name' => $rider->name,
                'workname' => $rider->workname,
                'rating' => $rider->rating,
                'phone' => $rider->phone,
                'image' => $rider->image,
                'earnings' => $rider->earning,
                'vehicle' => $rider->vehicle ?? []
            ];

            return $this->success(false, "Profile", $data, 200);
        }catch(Exception $e){
            return $this->error(true, "Error getting rider profile", 400);
        }
    }
    public function start_order(Request $request, $id){
        try{
            $order = DropOff::where('id', $id)->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->first();
           if ($order){

            $order->start_time = now();
            $order->save();
            return $this->success(false, "Rider has initiated order", $order, 200);

           } else{
               return $this->error(true, "it's either this order doesn't exist or no payment has been made", 400);
           }
        }catch(Exception $e){
            return $this->error(true, "Error" ,400);
        }
    }

    public function end_order($id){
        try{
            $order = DropOff::where('id', $id)->where('rider_id', auth()->user()->id)->first();
            $order->end_time = now();
            $order->save();
            return $this->success(false, "Rider has completed order", $order, 200);
        }catch(Exception $e){
            return $this->error(true, "Error" ,400);
        }
    }

    public function changeOrderStatus(Request $request, $id){
        try{

            $validated = $request->validate([
                'status' => 'required|string'
            ]);

            $dropoff = Dropoff::where('id', $id)->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->first();
            $dropoff->status = $validated['status'];
            $dropoff->save();

            return $this->success(false, "Dropoff status changed to ".$validated['status'] ,$dropoff, 200);

        }catch(Exception $e){
            return $this->error(true, "Error Occuredn", 400);
        }
    }

    public function getOrders(){
        try{
            $orders = DropOff::with('order')->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->get();

            foreach ($orders as $order){
                $id = $order->order->user_id;
                $user = User::find($id);
                $order['user'] = $user;
            }

            return $this->success(false, "Rider's orders", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "" ,400);
        }
    }

    public function history(){
        try{
            $id = auth()->user()->id;
            $history = History::where('rider_id', $id)->get();

            return $this->success(false, "rider's history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find rider's history", 400);
        }
    }

    public function setDriverLocation(Request $request){
        try{

            $validated = $request->validate([
                'longitude' => 'required|string',
                'latitude' => 'required|string'
            ]);

            $gps = new gpsLog;
            $gps->longitude = $validated['longitude'];
            $gps->latitude = $validated['latitude'];
            $gps->rider_id = auth()->user()->id;
            $gps->save();

            $rider = Rider::where('id', auth()->user()->id)->first();
            $rider->longitude = $validated['longitude'];
            $rider->latitude = $validated['latitude'];
            $rider->save();

            return $this->success(false, "Rider location set", 200);

        }catch(Exception $e){
            return $this->error(true, "Rider location wasn't set", $rider, 400);
        }

    }

    public function updatePhone(Request $request){
        try{
            $validated = $request->validate([
                'phone' => 'required|string'
            ]);

            $id = auth()->user()->id;
            $rider = Rider::find($id);

            $rider->phone = $validated['phone'];
            $rider->save();

            return $this->success(false, "rider's phone number has been updated", $rider, 200);
        }catch(Exception $e){
            return $this->error(true, "Error Occured while updating rider's phone number", 400);
        }

    }

    public function count($partner){

        try{

            $orders = Order::with('dropoff')->where('partner_id', $partner)->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->get(); //->where('status', 'pending')


            $data = [];
            $p_data = [];
            $d_data = [];
            $pu_data = [];
            foreach ($orders as $order){

                foreach ($order->dropoff as $dro){
                    array_push($data, $dro);
                }
            }

            foreach ($orders as $order){
                //$p_datum = $order->load('dropoff');
                 foreach ($order->dropoff as $dro){
                     if ($dro->status == 'pending'){
                        array_push($p_data, $dro);
                     }
                     if ($dro->payment_status != 'delivered'){
                        array_push($d_data, $dro);
                     }
                     if ($dro->status == 'picked'){
                        array_push($pu_data, $dro);
                     }

                 }
            }



            $data = [
                "orders" => count($data),
                "pending" => count($p_data), //$pending->dropoff()->count(),
                "pickedUp" => count($pu_data), //$pickedUp->dropoff()->count(),
                "delivered" => count($d_data), // $delivered->dropoff()->count(),
            ];

            return $data;
            //return $this->success(false, "User count orders", $data, 200);

        }catch(Exception $e){
            return $this->error(true, "ERROR fetching count!", 400);
        }

    }




    public function getOrderByStatus($status){

        try{

            //$rider = Rider::find(auth()->user()->id);
            $orders = Order::with('dropoff')->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->get();
            $data = [];


            switch($status){
                case 'pending':
                    foreach ($orders as $order){
                        foreach ($order->dropoff as $dro){
                            if ($dro->status == 'pending'){

                                $dro['dropoff'] = $this->getOneDropoff($dro->id);
                                // $order = Order::find($dro->id);
                                // $dro['order'] = $order;


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
