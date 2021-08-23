<?php

namespace App\Repositories\Interfaces;

interface AdminRepositoryInterface{

    public function signup(Request $request);

    public function login(Request $request);

    public function allPartners();

    public function disablePartner();

    public function allTopPartners();

    public function ridersByPartner();

    public function ordersByPartner();

    public function allTransactions();

    public function transactionByPartner();

    public function allOrders();
}
