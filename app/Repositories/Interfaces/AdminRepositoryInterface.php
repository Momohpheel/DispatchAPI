<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface AdminRepositoryInterface{

    public function signup(Request $request);

    public function login(Request $request);

    public function allUsers();

    public function allPartners();

    public function disablePartner($id);

    public function allTopPartners();

    public function ridersByPartner($id);

    public function ordersByPartner($id);

    public function oneOrder($id);

    public function onePartner($id);

    public function oneUser($id);

    public function dashboard();

    // public function allTransactions();

    // public function transactionByPartner();

    // public function allOrders();
}
