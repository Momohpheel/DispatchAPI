<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\Interfaces\PartnerRepositoryInterface;


class PartnerController extends Controller
{

    public $repository;

    public function __construct(PartnerRepositoryInterface $repository){
        $this->repository = $repository;
    }


    public function allPartner(){
        return $this->repository->allPartner();
    }

    public function allTopPartner(){
        return $this->repository->allTopPartner();
    }

    public function signup(Request $request){
        return $this->repository->signup($request);
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function forgotPassword(Request $request){
        return $this->repository->forgotPassword($request);
    }

    public function resetPassword($token){
        return $this->repository->resetPassword($token);
    }

    public function kyc(){
        return $this->repository->kyc();
    }

    public function profile(Request $request){
        return $this->repository->profile($request);
    }

    public function pauseAccount(){
        return $this->repository->pauseAccount();
    }

    public function updateProfile(Request $request){
        return $this->repository->updateProfile($request);
    }

    public function getRider($id){
        return $this->repository->getRider($id);
    }

    public function getProfile(){
        return $this->repository->getProfile();
    }

    public function addVehicle(Request $request){
        return $this->repository->addVehicle($request);
    }

    public function updateVehicle(Request $request, $id){
        return $this->repository->updateVehicle($request, $id);
    }
    public function disableVehicle($id){
        return $this->repository->disableVehicle($id);
    }
    public function getVehicles(){
        return $this->repository->getVehicles();
    }
    public function getVehicle($id){
        return $this->repository->getVehicle($id);
    }

    public function dismissRider($id){
        return $this->repository->dismissRider($id);
    }

    public function updateRider(Request $request, $id){
        return $this->repository->updateRider($request, $id);
    }

    public function ordersDoneByRider($id){
        return $this->repository->ordersDoneByRider($id);
    }

    public function createRider(Request $request){
        return $this->repository->createRider($request);
    }

    public function disableRider($id){
        return $this->repository->disableRider($id);
    }

    public function getRiders(){
        return $this->repository->getRiders();
    }

    public function assignOrder(Request $request){
        return $this->repository->assignOrder($request);
    }

    public function getOrders(){
        return $this->repository->getOrders();
    }

    public function getOneOrder($id){
        return $this->repository->getOneOrder($id);
    }

    public function setRouteCosting(Request $request){
        return $this->repository->setRouteCosting($request);
    }

    public function updateRouteCosting(Request $request, $id){
        return $this->repository->updateRouteCosting($request, $id);
    }

    public function subscribe(Request $request){
        return $this->repository->subscribe($request);
    }

    public function addOperatingHours(Request $request){
        return $this->repository->addOperatingHours($request);
    }

    public function updateOperatingHours(Request $request, $id){
        return $this->repository->updateOperatingHours($request, $id);
    }

    public function getPartnerHistory(){
        return $this->repository->getPartnerHistory();
    }

    public function makeTopPartner(){
        return $this->repository->makeTopPartner();
    }

    public function count(){
        return $this->repository->count();
    }

    public function countForVehicle($id){
        return $this->repository->countForVehicle($id);
    }

    public function getOrderByStatus($status){
        return $this->repository->getOrderByStatus($status);
    }


    public function getOrderbyVehicle($id){
        return $this->repository->getOrderbyVehicle($id);
    }

    public function dashboard(){
        return $this->repository->dashboard();
    }

}
