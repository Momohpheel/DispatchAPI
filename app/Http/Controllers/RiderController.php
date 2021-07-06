<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\Interfaces\RiderRepositoryInterface;


class RiderController extends Controller
{


    public $repository;

    public function __construct(RiderRepositoryInterface $repository){
        $this->repository = $repository;
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function start_order(Request $request, $id){
        return $this->repository->start_order($request, $id);
    }

    public function checkOrders(Request $request){
        return $this->repository->checkOrders($request);
    }

    public function history(){
        return $this->repository->history();
    }

    public function updatePhone(Request $request){
        return $this->repository->updatePhone($request);
    }

    public function end_order($id){
        return $this->repository->end_order( $id);
    }

    public function getProfile(){
        return $this->repository->getProfile();
    }
}
