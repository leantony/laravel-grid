<?php

namespace Tests\Browser;

use Orchestra\Testbench\Dusk\TestCase;

class BrowserTestCase extends TestCase
{
    protected static $baseServeHost = '127.0.0.1';
    protected static $baseServePort = 9000;
}