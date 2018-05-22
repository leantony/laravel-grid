<?php

namespace Tests\Setup\Controller;

use Illuminate\Http\Request;
use Tests\Setup\Grids\UsersGrid;
use Tests\Setup\Grids\UsersGridCustomized;
use Tests\Setup\TestModels\User;

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
        $query = User::with('role');

        return $grid
            ->create(['query' => $query, 'request' => $request])
            ->render();
    }

    /**
     * @param UsersGridCustomized $usersGridCustomized
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function index_two(UsersGridCustomized $usersGridCustomized, Request $request)
    {
        $query = User::with('role');

        return $usersGridCustomized
            ->create(['query' => $query, 'request' => $request])
            ->render();
    }

    public function show($id)
    {
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