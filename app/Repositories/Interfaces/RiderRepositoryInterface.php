<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface RiderRepositoryInterface{

    public function login(Request $request);

    public function getProfile();

    public function start_order(Request $request, $id);

    public function end_order($id);

    public function changeOrderStatus(Request $request, $id);

    public function setDriverLocation(Request $request);

    public function getOrders();

    public function history();

    public function updatePhone(Request $request);

    public function dashboard($id);
    public function getOrderByStatus($status);

}
