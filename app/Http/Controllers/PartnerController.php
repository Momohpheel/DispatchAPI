<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\Interfaces\PartnerRepositoryInterface;


class PartnerController extends Controller
{
    use Response;
    public $repository;

    public function __construct(PartnerRepositoryInterface $repository){
        $this->repository = $repository;
    }


    public function signup(Request $request){
        return $this->repository->signup($request);
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function kyc(){
        return $this->repository->kyc();
    }

    public function profile(Request $request){
        return $this->repository->profile();
    }

    public function pauseAccount(){
        return $this->repository->pauseAccount();
    }

    public function updateProfile(Request $request){
        return $this->repository->updateProfile();
    }

    public function addVehicle(Request $request){
        return $this->repository->addVehicle();
    }

    public function updateVehicle(Request $request, $id){
        return $this->repository->updateVehicle();
    }
    public function disableVehicle($id){
        return $this->repository->disableVehicle();
    }
    public function getVehicles(){
        return $this->repository->getVehicles();
    }
    public function getVehicle($id){
        return $this->repository->getVehicle();
    }

    public function dismissRider($id){
        return $this->repository->dismissRider();
    }

    public function updateRider(Request $request, $id){
        return $this->repository->updateRider();
    }

    public function ordersDoneByRider($id){
        return $this->repository->ordersDoneByRider();
    }

    public function createRider(Request $request){
        return $this->repository->createRider();
    }

    public function disableRider($id){
        return $this->repository->disableRider();
    }

    public function getRiders(){
        return $this->repository->getRiders();
    }

    public function assignOrder(Request $request){
        return $this->repository->assignOrder();
    }

    public function getOrders(){
        return $this->repository->getOrders();
    }

    public function getOneOrder($id){
        return $this->repository->getOneOrder();
    }

    public function setRouteCosting(Request $request){
        return $this->repository->setRouteCosting();
    }

    public function updateRouteCosting(Request $request, $id){
        return $this->repository->updateRouteCosting();
    }

    public function subscribe(Request $request){
        return $this->repository->subscribe();
    }

    public function addOperatingHours(Request $request){
        return $this->repository->addOperatingHours();
    }

    public function updateOperatingHours(Request $request, $id){
        return $this->repository->updateOperatingHours();
    }

    public function getPartnerHistory(){
        return $this->repository->getPartnerHistory();
    }

    public function makeTopPartner(){
        return $this->repository->makeTopPartner();
    }


}
