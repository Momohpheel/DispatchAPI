<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface{

    public function onboard($request);

    public function profile($request);

    public function login($request);

    public function order($request);

    public function saveAddress($request);




}
