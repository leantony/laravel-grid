<?php

namespace Tests\Controller;

use Illuminate\Http\Request;
use Tests\TestModels\User;

class UsersTestController extends \Illuminate\Routing\Controller
{
    /**
     * @param UsersGrid $grid
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function index(UsersGrid $grid, Request $request)
    {
        $query = User::query();

        return $grid
            ->create(['query' => $query, 'request' => $request])
            ->render();
    }

    public function show($id) {
        //
    }

    public function create(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function destroy($id, Request $request)
    {
        //
    }

    public function view($id, Request $request)
    {
        //
    }
}