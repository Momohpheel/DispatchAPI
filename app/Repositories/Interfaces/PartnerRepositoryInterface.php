<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface PartnerRepositoryInterface{

    public function signup(Request $request);

    public function login(Request $request);

    public function kyc();

    public function profile(Request $request);

    public function getProfile();

    public function pauseAccount();

    public function updateProfile(Request $request);

    public function addVehicle(Request $request);

    public function updateVehicle(Request $request, $id);

    public function disableVehicle($id);

    public function getVehicles();
    public function pendingOrders();

    public function getVehicle($id);

    public function dismissRider($id);

    public function updateRider(Request $request, $id);

    public function ordersDoneByRider(Request $request, $id);

    public function createRider(Request $request);

    public function allPartner();

    public function subscriptionDetails();

    public function getOperatingHour();

    public function allPlateNumbers();
    public function VehicleEarnings(Request $request, $id);

    public function RiderEarnings(Request $request, $id);

    public function PartnerEarnings(Request $request);

    public function getPayoutLog();
    public function getPaymentHistory();

    public function getRouteCost();

    public function allTopPartner();

    public function disableRider($id);

    public function getRiders();

    public function getRider($id);

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

    public function getOneDropoff($id);

    public function getOrderbyVehicle($id);

    public function dashboard();

    public function forgotPassword(Request $request);

    public function resetPassword(Request $request, $token);

    public function payment(Request $request);

    public function subscription();

    public function todaysEarnings();

}
