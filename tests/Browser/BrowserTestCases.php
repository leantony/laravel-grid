<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\TestCase;
use Tests\Setup\SharedSetup;

class BrowserTestCases extends TestCase
{
    use SharedSetup;

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver(): RemoteWebDriver
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->setBinary('/usr/bin/google-chrome');
        $chromeOptions->addArguments(['no-first-run', 'no-sandbox']);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            $capabilities
        );
    }

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
                ->assertSee('tester_1')
                ->assertSee('tester_6')
                ->assertSee('testrole_1');
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