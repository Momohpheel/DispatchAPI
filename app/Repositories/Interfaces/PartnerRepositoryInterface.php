<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface PartnerRepositoryInterface{

    public function signup(Request $request);

    public function login(Request $request);

    public function kyc();

    public function profile(Request $request);

    public function pauseAccount();

    public function updateProfile(Request $request);

    public function addVehicle(Request $request);

    public function updateVehicle(Request $request, $id);

    public function disableVehicle($id);

    public function getVehicles();

    public function getVehicle($id);

    public function dismissRider($id);

    public function updateRider(Request $request, $id);

    public function ordersDoneByRider($id);

    public function createRider(Request $request);

    public function allPartner();

    public function disableRider($id);

    public function getRiders();

    public function assignOrder(Request $request);

    public function getOrders();

    public function getOneOrder($id);

    public function setRouteCosting(Request $request);

    public function updateRouteCosting(Request $request, $id);

    public function subscribe(Request $request);

    public function addOperatingHours(Request $request);

    public function updateOperatingHours(Request $request, $id);

    public function getPartnerHistory();

    public function makeTopPartner();

    public function count();

    public function countForVehicle($id);

    public function getOrderByStatus($status);




}
