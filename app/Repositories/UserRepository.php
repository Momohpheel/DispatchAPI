<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
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
        $validated = $request->validate([
            'name' => "required|string",
            'phone' => "required|string",
            'email' => "required|string",
            "password" => "required|string"
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->name = $validated['phone'];
        $user->name = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->save();

        return $this->success("Profile saved", $user, 200);
    }

    public function login(){}

    public function order(Request $request){}

    public function saveAddress(Request $request){}




}
