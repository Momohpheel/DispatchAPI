<?php

namespace App\Repositories\Interfaces;

interface RiderRepositoryInterface{

    public function login(Request $request);

    public function start_order(Request $request, $id);

    public function checkOrders(Request $request);

    public function history();

    public function updatePhone(Request $request);



}
