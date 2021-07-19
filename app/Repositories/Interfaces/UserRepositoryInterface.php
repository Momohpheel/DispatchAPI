<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface{

    public function onboard(Request $request);

    public function profile(Request $request);

    public function getProfile();

    public function updateProfile(Request $request);

    public function login(Request $request);

    public function order(Request $request, $id);

    public function getAllOrders();

    public function getOrder($id);

    public function deleteDropOff($d_id);

    public function calculatePrice($distance, $id);

    public function payment();

    public function getUserHistory();

    public function rateRider(Request $request);

    public function count();

    public function orderHistory();

    public function logout();

    public function ratePartner(Request $request);

    public function saveAddress(Request $request);

    public function getSavedAddresses();




}
