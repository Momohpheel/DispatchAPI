<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interfaces\AdminRepositoryInterface;


class AdminController extends Controller
{
    public $repository;

    public function __construct(AdminRepositoryInterface $repository){
        $this->repository = $repository;
    }


    public function dashboard(){
        return $this->repository->dashboard();
    }

    public function signup(Request $request){
        return $this->repository->signup($request);
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function allUsers(){
        return $this->repository->allUsers();
    }

    public function oneUser($id){
        return $this->repository->oneUser($id);
    }

    public function allPartners(){
        return $this->repository->allPartners();
    }

    public function onePartner($id){
        return $this->repository->onePartner($id);
    }

    public function disablePartner($id){
        return $this->repository->disablePartner($id);
    }

    public function allTopPartners(){
        return $this->repository->allTopPartners();
    }

    public function ridersByPartner($id){
        return $this->repository->ridersByPartner($id);
    }

    public function ordersByPartner($id){
        return $this->repository->ordersByPartner($id);
    }

    public function allOrders(){
        return $this->repository->allOrders();
    }

    public function oneOrder($id){
        return $this->repository->oneOrder($id);
    }

}
