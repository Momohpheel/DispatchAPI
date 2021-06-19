<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Partner;
use App\Traits\Response;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserController extends Controller
{

    use Response;
    public $repository;

    public function __construct(UserRepositoryInterface $repository){
        $this->repository = $repository;
    }

    public function onboard(Request $request){
        return $this->repository->onboard($request);
    }

    public function profile(Request $request){
    }

    public function login(){}

    public function order(Request $request){}

    public function saveAddress(Request $request){}





}
