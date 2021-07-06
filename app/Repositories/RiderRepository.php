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
use App\Repositories\Interfaces\RiderRepositoryInterface;
use Illuminate\Http\Request;

class RiderRepository implements RiderRepositoryInterface{

    use Response, Logs;

    public function login(Request $request){
        try{
            $validated = $request->validate([
                'workname' => "required|string",
                "password" => "required|string",
                "code_name" => "required|string"
            ]);

            $partner = Partner::where('code_name', $validated['code_name'])->first();
            if ($partner){
                $rider = Rider::where('workname', $validated['workname'])->where('partner_id', $partner->id)->first();
                if ($rider){
                    $check = Hash::check($validated['password'], $rider->password);
                    if ($check){
                        $access_token = $rider->createToken('authToken')->accessToken;
                        $data = ["access_token" => $access_token];
                        return $this->success("rider found", $data, 200);
                    }else{
                        return $this->error(true, "Error logging rider", 400);
                    }
                }

            }

        }catch(Exception $e){
            return $this->error(true, "Error logging partner", 400);
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
                'earnings' => $rider->earnings,
            ];

            return $this->success("Profile", $data, 200);
        }catch(Exception $e){
            return $this->error(true, "Error getting rider profile", 400);
        }
    }
    public function start_order(Request $request, $id){
        try{
            $order = DropOff::where('id', $id)->where('rider_id', auth()->user()->id)->where('payment_status', 'paid')->first();
            $order->start_time = now();
            $order->save();
            return $this->success("Rider has initiated order", $order, 200);
        }catch(Exception $e){
            return $this->error(true, "Error" ,400);
        }
    }

    public function end_order($id){
        try{
            $order = DropOff::where('id', $id)->where('rider_id', auth()->user()->id)->first();
            $order->end_time = now();
            $order->save();
            return $this->success("Rider has completed order", $order, 200);
        }catch(Exception $e){
            return $this->error(true, "Error" ,400);
        }
    }

    public function checkOrders(){
        try{
            $orders = DropOff::where('rider_id', auth()->user()->id)->get();

            return $this->success("Rider's orders", $orders, 200);
        }catch(Exception $e){
            return $this->error(true, "" ,400);
        }
    }

    public function history(){
        try{
            $id = auth()->user()->id;
            $history = History::where('rider_id', $id)->get();

            return $this->success("rider's history", $history, 200);

        }catch(Exception $e){
            return $this->error(true, "Couldn't find rider's history", 400);
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

            return $this->success("rider's phone number has been updated", $rider, 200);
        }catch(Exception $e){
            return $this->error(true, "Error Occured while updating rider's phone number", 400);
        }

    }

}
