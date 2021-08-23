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

}
