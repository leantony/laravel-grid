<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Leantony\Grid\Facades\Modal;
use Orchestra\Testbench\TestCase;
use Tests\TestModels\Role;
use Tests\TestModels\User;

class PackageTestCase extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['testing'];

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
            $usersBuilder->insert([
                'name' => 'tester_' . $v,
                'email' => 'hello@testuser' . $v . '.com',
                'role_id' => Role::query()->get()->random()->id,
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

        // routes
        $app['router']->get('users', ['as' => 'users.index', 'uses' => 'Tests\Controller\UsersTestController@index']);
        $app['router']->get('users/create', ['as' => 'users.create', 'uses' => 'Tests\Controller\UsersTestController@create']);
        $app['router']->post('users/create', ['as' => 'users.store', 'uses' => 'Tests\Controller\UsersTestController@store']);
        $app['router']->get('users/:id', ['as' => 'users.show', 'uses' => 'Tests\Controller\UsersTestController@show']);
        $app['router']->patch('users/:id', ['as' => 'users.update', 'uses' => 'Tests\Controller\UsersTestController@update']);
        $app['router']->delete('users/:id', ['as' => 'users.destroy', 'uses' => 'Tests\Controller\UsersTestController@destroy']);
    }

    /** @test */
    public function config_is_loaded()
    {
        $this->assertEquals(true, Config::get('grid.warn_when_empty'));
    }

    /** @test */
    public function can_run_the_migrations()
    {
        $users = DB::table('users')->where('id', '=', 1)->first();
        $this->assertNotNull($users);
    }

    /**
     * @test
     */
    public function grid_is_generated_using_command()
    {
        Artisan::call('make:grid', [
            '--model' => User::class,
        ]);

        $resultAsText = Artisan::output();

        $this->assertContains('Finished performing replacements to the stub files', $resultAsText);
    }

    /**
     * @test
     */
    public function grid_is_displayed()
    {
        $response = $this->get('/users');

        $content = $response->getContent();

        $response->assertStatus(200);
        $this->assertNotNull($content);
        $this->assertContains('Users', $content);
        $this->assertContains('<div class="row laravel-grid" id="user-grid">', $content);
        $this->assertContains('<option value="1">testrole_1</option>', $content);
        $this->assertContains('50 entries', $content);
    }
}