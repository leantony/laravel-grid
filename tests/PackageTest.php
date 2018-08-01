<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Leantony\Grid\Buttons\GenericButton;
use Orchestra\Testbench\TestCase;
use Tests\Setup\Grids\UsersGrid;
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
     * @throws \Exception
     * @test
     */
    public function grid_can_add_columns()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $expectedColumns = ['id', 'name', 'role_id', 'email', 'created_at'];
        $columns = array_keys($grid->getColumns());
        $this->assertEquals($expectedColumns, $columns);
    }

    /**
     * Test to see if the grid is displayed
     * @test
     * @throws \Exception
     * @throws \Throwable
     */
    public function grid_is_displayed()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */

        $content = $grid->render();

        $this->assertNotNull($content);
        $this->assertContains('Users', $content);
        $this->assertContains('<div class="row laravel-grid" id="user-grid">', $content);
        $this->assertContains('<option value="1">testrole_1</option>', $content);
        $this->assertContains('50 entries', $content);
    }

    /**
     * @throws \Exception
     * @test
     */
    public function grid_can_add_button()
    {
        $grid = $this->getGridInstances()['users_default'];
        // for toolbar
        $initialButtonsCount = collect($grid->getButtons())->count();

        /** @var $grid UsersGrid */
        $button = (new GenericButton())
            ->setName('temp')
            ->setGridId($grid->getId())
            ->setType('toolbar')
            ->setPjaxEnabled(false)
            ->setUrl(function () {
                return url('/users');
            });

        $grid->addButton('toolbar', 'temp', $button);

        $addedButton = collect($grid->getButtons())->first();
        $countAfter = collect($grid->getButtons())->count();

        $this->assertGreaterThan($initialButtonsCount, $countAfter);
        $this->assertTrue($button instanceof $addedButton);
        $this->assertEquals($button->getName(), $addedButton->getName());
    }

    /**
     * @throws \Exception
     * @test
     */
    public function grid_can_set_default_route_parameter()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $grid->setDefaultRouteParameter('email');
        $this->assertEquals($grid->getDefaultRouteParameter(), 'email');
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function grid_can_add_footer()
    {
        $grid = $this->getGridInstances()['users_customized'];
        /** @var $grid UsersGrid */
        $grid->setShowFooter(true);
        $existingTotal = User::query()->sum('id');
        $content = $grid->render();

        $this->assertContains("Total:${existingTotal}", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_disable_rendering_of_filters()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $grid->withoutFilters();
        // a sample filter that's enabled on the grid
        $usersFilterId = 'grid-filter-user';
        $content = $grid->render();
        $this->assertNotContains("${usersFilterId}", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_enable_rendering_of_filters()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        // the grid filter form id
        $gridFilterFormId = 'user-grid-filter';
        // a sample filter that's enabled on the grid
        $usersFilterId = 'grid-filter-name';
        $content = $grid->render();
        $this->assertContains("${gridFilterFormId}", $content);
        $this->assertContains("${usersFilterId}", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_enable_rendering_of_search()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $id = 'search-user-grid';
        $content = $grid->render();
        $this->assertContains("${id}", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_disable_rendering_of_search()
    {
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $grid->withoutSearchForm();
        $id = 'search-user-grid';
        $content = $grid->render();
        $this->assertNotContains("${id}", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_render_using_default_layout()
    {
        // default scenario
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $content = $grid->render();
        // default layout uses a bootstrap4 card
        $this->assertContains("card-header", $content);
        $this->assertContains("card-body", $content);
        $this->assertContains("card-footer", $content);
    }

    /**
     * @throws \Exception
     * @test
     * @throws \Throwable
     */
    public function grid_can_render_using_custom_layout()
    {
        // using custom template
        $grid = $this->getGridInstances()['users_default'];
        /** @var $grid UsersGrid */
        $grid->withCustomTemplate('leantony::grid.templates.dummy');
        $content = $grid->render();
        // default layout uses a bootstrap4 card
        $this->assertContains("dummy", $content);
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function grid_can_add_custom_filter_titles()
    {
        $filterText = "filter-by-foo-bar";
        $existingFilterTextSampleNotExisting = "filter by name";
        $existingFilterTextSampleExisting = "filter by created_at";
        $grid = $this->getGridInstances()['users_customized'];
        /** @var $grid UsersGrid */
        $content = $grid->render();

        $this->assertContains($filterText, $content);
        $this->assertContains($existingFilterTextSampleExisting, $content);
        $this->assertNotContains($existingFilterTextSampleNotExisting, $content);
    }

    /**
     * @throws \Throwable
     * @test
     */
    public function grid_can_add_boolean_filter()
    {
        $filterText = "False";
        $grid = $this->getGridInstances()['users_customized'];
        /** @var $grid UsersGrid */
        $content = $grid->render();
        $this->assertContains($filterText, $content);
    }
}
