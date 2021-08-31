<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface{

    public function onboard(Request $request);

    public function profile(Request $request);

    public function getProfile();

    public function updateProfile(Request $request);

    public function uploadImage(Request $request);

    public function login(Request $request);

    public function order(Request $request, $id);

    public function getAllOrders($id);

    public function getOrder($id);

    public function deleteDropOff($d_id);

    public function checkVehiclesAvailablePerPartner($id);

    public function calculatePrice($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius, $id);

    public function payment(Request $request);

    public function getOrderByStatus(Request $request, $status);

    public function getUserHistory();

    public function dashboard($id);

    public function rateRider(Request $request);

    public function count($id);

    public function orderHistory($id);

    public function logout();

    public function allOrderHistory();

    public function forgotPassword(Request $request);

    public function resetPassword(Request $request, $token);

    public function getOneDropoff($id);

    public function ratePartner(Request $request);

    public function saveAddress(Request $request);

    public function getSavedAddresses();

    public function getTransactionHistory();

    public function getAllTransactionHistory();



}
