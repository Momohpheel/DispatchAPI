<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
use App\Traits\Response;
class UserController extends Controller
{

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


}
