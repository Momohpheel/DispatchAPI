<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface{

    public function onboard(Request $request);

    public function profile(Request $request);

    public function login(Request $request);

    public function order(Request $request, $id);

    public function calculatePrice($distance, $id);

    public function payment();

    public function getUserHistory();

    public function rateRider();

    public function ratePartner();

    public function saveAddress(Request $request);

    public function getSavedAddresses();




}
