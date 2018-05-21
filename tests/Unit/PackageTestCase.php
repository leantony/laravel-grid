<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
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

    public function insert_random_data()
    {
        $now = Carbon::now();
        DB::table('roles')->insert([
            'name' => 'testrole',
            'description' => 'testrole is good',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('users')->insert([
            'name' => 'tester',
            'email' => 'hello@testuser.com',
            'role_id' => Role::query()->first()->id,
            'password' => 'secret',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
    }

    /** @test */
    public function it_runs_the_migrations()
    {
        $users = DB::table('users')->where('id', '=', 1)->first();
        $this->assertNotNull($users);
    }

    /**
     * @test
     */
    public function test_generate_grid_command()
    {
        Artisan::call('make:grid', [
            '--model' => User::class,
        ]);

        $resultAsText = Artisan::output();

        $this->assertContains('Finished performing replacements to the stub files', $resultAsText);
    }
}