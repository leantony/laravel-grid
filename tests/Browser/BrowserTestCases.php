<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\TestCase;
use Tests\Setup\SharedSetup;

class BrowserTestCases extends TestCase
{
    use SharedSetup;

    protected static $baseServeHost = '127.0.0.1';
    protected static $baseServePort = 9000;

    /**
     * Test seeing the grid
     *
     * @return void
     * @throws \Throwable
     * @test
     */
    public function can_see_grid()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->assertSee('Users')
                ->assertSee('tester_1');
        });
    }

    /**
     * Test refreshing the grid
     *
     * @return void
     * @throws \Throwable
     * @test
     */
    public function can_refresh_grid()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->clickLink('Refresh')
                ->assertPathIs('/users');
        });
    }
}