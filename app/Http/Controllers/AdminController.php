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


    public function signup(Request $request){
        return $this->repository->signup($request);
    }

    public function login(Request $request){
        return $this->repository->login($request);
    }

    public function allUsers(){
        return $this->repository->allUsers();
    }

    public function allPartners(){
        return $this->repository->allPartners();
    }

    public function disablePartner($id){
        return $this->repository->login($id);
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


}
