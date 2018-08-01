<?php

namespace Tests\Setup;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Leantony\Grid\Facades\Modal;
use Tests\Setup\Grids\UsersGrid;
use Tests\Setup\Grids\UsersGridCustomized;
use Tests\Setup\TestModels\Role;
use Tests\Setup\TestModels\User;

trait SharedSetup
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['testing'];

    protected $grid;

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__ . '/../database/migrations'));

        $this->insert_random_data();
    }

    protected function getPackageProviders($app)
    {
        return [\Leantony\Grid\Providers\GridServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Modal' => Modal::class
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getGridInstances()
    {
        return [
            'users_default' => (new UsersGrid())
                ->create(['query' => User::with('role'), 'request' => app('request')]),
            'users_customized' => (new UsersGridCustomized())
                ->create(['query' => User::with('role'), 'request' => app('request')])
        ];
    }

    /**
     * Random data creation
     * @return void
     */
    public function insert_random_data()
    {
        $now = Carbon::now();
        $rolesBuilder = DB::table('roles');
        $usersBuilder = DB::table('users');

        collect(range(1, 6))->each(function ($v) use ($rolesBuilder, $now) {
            $rolesBuilder->insert([
                'name' => 'testrole_' . $v,
                'description' => 'testrole is good',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        collect(range(1, 50))->each(function ($v) use ($usersBuilder, $now) {
            $role_id = Role::query()->get()->random()->id;
            $usersBuilder->insert([
                'name' => 'tester_' . $v,
                'email' => 'hello@testuser' . $v . '.com',
                'is_admin' => $role_id === 1,
                'role_id' => $role_id,
                'password' => 'secret',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    /**
     * Define environment setup.
     *
     * @param  Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // some sample config
        $app['config']->set('grid.warn_when_empty', true);
        $app['config']->set('grid.export.allowed_types', ['pdf', 'csv', 'html', 'json', 'xlsx']);

        // routes
        $app['router']->get('users', ['as' => 'users.index', 'uses' => 'Tests\Setup\Controller\UsersTestController@index']);
        $app['router']->get('users/create', ['as' => 'users.create', 'uses' => 'Tests\Setup\Controller\UsersTestController@create']);
        $app['router']->post('users/create', ['as' => 'users.store', 'uses' => 'Tests\Setup\Controller\UsersTestController@store']);
        $app['router']->get('users/:id', ['as' => 'users.show', 'uses' => 'Tests\Setup\Controller\UsersTestController@show']);
        $app['router']->patch('users/:id', ['as' => 'users.update', 'uses' => 'Tests\Setup\Controller\UsersTestController@update']);
        $app['router']->delete('users/:id', ['as' => 'users.destroy', 'uses' => 'Tests\Setup\Controller\UsersTestController@destroy']);

        // customized grid
        $app['router']->get('userz', ['as' => 'users.index_2', 'uses' => 'Tests\Setup\Controller\UsersTestController@index_two']);
    }
}