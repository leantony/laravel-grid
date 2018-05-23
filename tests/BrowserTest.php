<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\TestCase;
use Tests\Setup\SharedSetup;

class BrowserTest extends TestCase
{
    use SharedSetup;

    protected static $baseServePort = 9100;

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver(): RemoteWebDriver
    {
        $chromeOptions = new ChromeOptions();
        $chromeOptions->setBinary('/usr/bin/google-chrome');
        $chromeOptions->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
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

    /**
     * @throws \Throwable
     * @test
     */
    public function can_do_an_export_as_excel()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->clickLink('excel')
                ->assertQueryStringHas('export', 'xlsx');
        });
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function can_do_an_export_as_html()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->clickLink('html')
                ->assertQueryStringHas('export', 'html');
        });
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function can_do_an_export_as_csv()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->clickLink('csv')
                ->assertQueryStringHas('export', 'csv');
        });
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function can_do_an_export_as_pdf()
    {
        $this->browse(function ($browser) {
            /** @var $browser Browser */
            $browser->visit('/users')
                ->clickLink('pdf')
                ->assertQueryStringHas('export', 'pdf');
        });
    }
}