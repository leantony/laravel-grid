<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Tests\Setup\SharedSetup;
use Tests\Setup\TestModels\User;

class PackageTest extends TestCase
{
    use SharedSetup;

    /**
     * Test to see if config is loaded
     * @test
     */
    public function config_is_loaded()
    {
        $this->assertEquals(true, Config::get('grid.warn_when_empty'));
        $this->assertEquals(['pdf', 'csv', 'html', 'json', 'xlsx'], Config::get('grid.export.allowed_types'));
    }

    /**
     * Test to see if migrations can be run
     * @test
     */
    public function can_run_the_migrations()
    {
        $users = DB::table('users')->where('id', '=', 1)->first();
        $this->assertNotNull($users);
    }

    /**
     * Test to see if grid is generated using command option
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
     * Test to see if the grid is displayed
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