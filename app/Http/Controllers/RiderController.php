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

    public function start_order($id){
        return $this->repository->start_order($id);
    }

    public function getOrders(Request $request){
        return $this->repository->getOrders($request);
    }

    public function history(){
        return $this->repository->history();
    }

    public function updatePhone(Request $request){
        return $this->repository->updatePhone($request);
    }

    public function changeOrderStatus(Request $request, $id){
        return $this->repository->changeOrderStatus($request, $id);
    }

    public function setDriverLocation(Request $request){
        return $this->repository->setDriverLocation($request);
    }

    public function end_order($id){
        return $this->repository->end_order( $id);
    }

    public function getProfile(){
        return $this->repository->getProfile();
    }


    public function dashboard($id){
        return $this->repository->dashboard($id);
    }

    public function getOrderByStatus($status){
        return $this->repository->getOrderByStatus($status);
    }

}

