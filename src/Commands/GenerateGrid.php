<?php

namespace Leantony\Grid\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateGrid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:grid
    {--model=}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a grid from an eloquent model.';

    /**
     * The namespace format for the grids
     *
     * @var string
     */
    protected $namespaceFormat = "App\\Grids";

    /**
     * Items to look for in the stub
     *
     * @var array
     */
    protected $searches = [
        'namespace' => '{{ namespace }}',
        'model' => '{{ model }}',
        'table' => '{{ tablename }}',
        'isLinkable' => '{{ linkable }}',
        'rows' => '{{ rows }}',
        'routeRoot' => '{{ routeRoot }}',
        'binding' => '{{ binding }}'
    ];

    /**
     * Skip this columns. These would not be considered on the grid, regardless of fillable status or not
     *
     * @var array
     */
    protected $excludedColumns = [
        'password',
        'password_hash',
    ];

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $binding;

    /**
     * GenerateGrid constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stub = $this->getStubContents();
        list($model, $rows) = $this->generateRows($this->getModelOption());

        // binding
        list($namespace, $replaced, $filename) = $this->dumpBinding($model);

        // class
        $this->dumpClass($model, $rows, $stub);

        return true;
    }

    /**
     * Get stub contents
     *
     * @return string
     */
    protected function getStubContents(): string
    {
        $stub = $this->filesystem->get($this->getStub());
        return $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/Grid.txt';
    }

    /**
     * Generate grid rows for this model
     *
     * @param $model
     * @return array|bool
     */
    protected function generateRows($model)
    {
        $columns = [];

        $model = app($model);
        if (!$model instanceof Model) {
            $this->error("Invalid model supplied.");
            return false;
        }

        // primary key
        $model->getKeyName();
        $columns = array_merge($columns, [$model->getKeyName()]);
        // fillable
        $columns = array_merge($columns, $model->getFillable());
        // timestamps
        if ($model->timestamps) {
            $columns = array_merge($columns, [$model->getCreatedAtColumn()]);
        }

        $rows = collect($columns)->reject(function ($v) {
            return in_array($v, $this->excludedColumns);

        })->map(function ($columnName) {
            if($columnName === 'id') {
                // a pk
                return [
                    $columnName => [
                        'sort' => true,
                        'filter' => 'text',
                        'filterOperator' => '=',
                        'styles' => ['row' => 'col-md-2']
                    ],
                ];
            }
            else if (Str::endsWith($columnName, '_id')) {
                // a join column
                return [
                    $columnName => [
                        'sort' => true,
                        'filter' => 'select'
                    ],
                ];
            } else if (Str::endsWith($columnName, '_at')) {
                // a date column
                return [
                    $columnName => [
                        'sort' => true,
                        'date' => 'true',
                        'filter' => 'date',
                        'filterOperator' => '<='
                    ],
                ];
            } else
                // any other column
                return [
                    $columnName => [
                        'sort' => true,
                        'filter' => 'text',
                        'filterOperator' => 'like',
                        'searchable' => true
                    ],
                ];
        });

        $this->info("Grid generated shall render " . $rows->count() . ' rows for model ' . class_basename($model));

        return [$model, $rows->collapse()->toArray()];
    }

    /**
     * Get the model to be used
     *
     * @return array|string
     */
    protected function getModelOption()
    {
        $model = trim($this->option('model'));
        return $model;
    }

    /**
     * Dump the binding class
     *
     * @param $model
     * @return array
     */
    protected function dumpBinding($model): array
    {
        $st = __DIR__ . '/../Stubs/GridInterface.txt';

        list($namespace, $interfaceName, $replaced) = $this->makeReplacementsForBinding($model, $this->generateDynamicNamespace(), $st);

        $this->binding = $interfaceName;

        $filename = $this->makeFileName($interfaceName);

        $p = $this->getPath($namespace);

        $this->dumpFile($p, $filename, $replaced);

        $this->info("Dumped generated binding at " . $p);

        return array($namespace, $replaced, $filename);
    }

    /**
     * Make replacements
     *
     * @param $model
     * @param $stub
     * @return array
     */
    protected function makeReplacementsForBinding($model, $namespace, $stub): array
    {
        $interfaceName = Str::studly($model->getTable()) . 'GridInterface';

        $replaced = str_replace(['{{ namespace }}', '{{ name }}'], [
            $namespace, $interfaceName
        ], $this->filesystem->get($stub));

        return array($namespace, $interfaceName, $replaced);
    }

    /**
     * Generate dynamic namespace for the file, based on the module
     *
     * @return string
     */
    protected function generateDynamicNamespace()
    {
        return $this->namespaceFormat;
    }

    /**
     * Make a name for the file
     *
     * @param $filename
     * @return string
     */
    protected function makeFileName($filename): string
    {
        return $filename . '.php';
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        // since app_path would take care of the 'app' part
        // we ignore it here
        $name = str_replace("App", "", $name);

        return app_path() . str_replace('\\', '/', $name);
    }

    /**
     * Dump the file generated
     *
     * @param $path
     * @param $filename
     * @param $contents
     */
    protected function dumpFile($path, $filename, $contents)
    {
        $this->makeDirectory($path);

        $this->filesystem->put($path . DIRECTORY_SEPARATOR . $filename, $contents);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->filesystem->isDirectory(dirname($path))) {
            $this->filesystem->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Dump the generated grid class
     *
     * @param $model
     * @param $rows
     * @param $stub
     */
    protected function dumpClass($model, $rows, $stub)
    {
        list($namespace, $tableName, $replaced) = $this->makeReplacements($model, $rows, $stub);

        $filename = $this->makeFileName($tableName . 'Grid');

        $path = $this->getPath($namespace);

        $this->dumpFile($path, $filename, $replaced);

        $this->info("Dumped generated grid at " . $path);
    }

    /**
     * Make replacements
     *
     * @param $model
     * @param $rows
     * @param $stub
     * @return array
     */
    protected function makeReplacements($model, $rows, $stub): array
    {
        $namespace = $this->generateDynamicNamespace();

        $modelName = Str::plural(ucfirst(class_basename($model)));
        $tableName = Str::studly($model->getTable());
        $routeRoot = Str::plural(strtolower(class_basename($model)));

        $contents = $this->replaceRows($rows, $stub);

        $replaced = $this->replaceOtherContent([
            'namespace' => $namespace,
            'modelName' => $modelName,
            'tableName' => $tableName,
            'routeRoot' => $routeRoot,
            'binding' => $this->binding,
        ], $contents);

        return array($namespace, $tableName, $replaced);
    }

    /**
     * Replace the row section of the stub
     *
     * @param $rows
     * @param $stub
     * @return string
     */
    protected function replaceRows($rows, $stub)
    {
        $value = str_replace(['{{ rows }}'], var_export54($rows, "\t\t") . ';', $stub);
        $this->info("Replaced rows successfully...");
        return $value;
    }

    /**
     * Replace content in the stub
     *
     * @param array $replacements
     * @param $stub
     * @return string
     */
    protected function replaceOtherContent(array $replacements, &$stub)
    {

        $replaced = str_replace(array_values(array_except($this->searches, 'rows')), [
            $replacements['namespace'], $replacements['modelName'],
            $replacements['tableName'], 'false', $replacements['routeRoot'], $replacements['binding']
        ], $stub);
        $this->info("Replaced other contents successfully...");
        return $replaced;
    }
}
