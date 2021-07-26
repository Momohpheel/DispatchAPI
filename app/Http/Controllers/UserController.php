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
        return $this->repository->profile($request);
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function order(Request $request, $id){
        return $this->repository->order($request, $id);
    }

    public function getAllOrders(){
        return $this->repository->getAllOrders();
    }

    public function getOrder($id){
        return $this->repository->getOrder($id);
    }

    public function deleteDropOff($d_id){
        return $this->repository->deleteDropOff($d_id);
    }

    public function logout(){
        return $this->repository->logout();
    }

    public function calculatePrice($distance, $id){
        return $this->repository->calculatePrice($distance, $id);
    }

    public function payment(Request $request, $id){
        return $this->repository->payment($request,$id);
    }

    public function callback(){
        return $this->repository->callback();
    }

    public function getUserHistory(){
        return $this->repository->getUserHistory();
    }

    public function rateRider(Request $request){
        return $this->repository->rateRider($request);
    }

    public function ratePartner(Request $request){
        return $this->repository->ratePartner($request);
    }

    public function orderHistory(){
        return $this->repository->orderHistory();
    }

    public function saveAddress(Request $request){
        return $this->repository->saveAddress($request);
    }

    public function count(){
        return $this->repository->count();
    }

    public function getSavedAddresses(){
        return $this->repository->getSavedAddresses();
    }

    public function updateProfile(Request $request){
        return $this->repository->updateProfile($request);
    }

    public function getProfile(){
        return $this->repository->getProfile();
    }


}
