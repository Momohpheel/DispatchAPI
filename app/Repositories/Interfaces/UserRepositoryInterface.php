<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface{

    public function onboard($request);

    public function profile($request);

    public function login($request);

    public function order($request);

    public function calculatePrice(Request $request);

    public function payment();

    public function getUserHistory();

    public function rateRider();

    public function ratePartner();

    public function saveAddress($request);

    public function getSavedAddresses();




}
