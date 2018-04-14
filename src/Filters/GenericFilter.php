<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Filters;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GenericFilter implements Htmlable
{
    /**
     * The name of the filter
     *
     * @var string
     */
    protected $name;

    /**
     * The html ID of the element
     *
     * @var string
     */
    protected $id;

    /**
     * If the filter is enabled
     *
     * @var bool
     */
    protected $enabled = true;

    /**
     * The title of the html element
     *
     * @var string
     */
    protected $title;

    /**
     * The class of the html element
     *
     * @var string
     */
    protected $class = 'form-control grid-filter';

    /**
     * A custom function that will be used to render the filter
     *
     * @var callable
     */
    protected $renderCustom = null;

    /**
     * The type of filter. While there are no hardcoded elements that suit the filter, text is a sensible default
     *
     * @var string
     */
    protected $type = 'text';

    /**
     * The name of the form attached to this element. Defaults to the filter form ID
     * @see https://stackoverflow.com/questions/5967564/form-inside-a-table
     *
     * @var string
     */
    protected $formId = 'leantony-grid-filter';

    /**
     * The data to be used for filtering. Essential if the element is a dropdown
     *
     * @var array|Collection
     */
    protected $data = null;

    /**
     * Any HTML5 data attributes for the element
     *
     * @var array
     */
    protected $dataAttributes = [];

    /**
     * GenericFilter constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $k => $v) {
            $this->__set($k, $v);
        }
    }

    /**
     * Get a class attribute
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new InvalidArgumentException("The property " . $name . " does not exist on " . get_called_class());
    }

    /**
     * Set class attributes
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     * @throws \Exception
     * @throws \Throwable
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Render the filter
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function render()
    {
        if ($this->renderCustom && is_callable($this->renderCustom)) {
            return call_user_func($this->renderCustom, $this->compactData(func_get_args()));
        }
        switch ($this->type) {
            case 'text':
                // all filters apart from dropdowns are rendered as text elements.
                // css classes or js libraries can be used to change this
                return view('leantony::grid.filters.text', $this->compactData(func_get_args()))->render();
            case 'select':
                return view('leantony::grid.filters.dropdown', $this->compactData(func_get_args()))->render();
            default:
                throw new \Exception("Unknown filter type.");
        }
    }

    /**
     * Specify the data to be sent to the view
     *
     * @param array $params
     * @return array
     */
    protected function compactData($params = [])
    {
        foreach (array_merge($params, $this->getExtraParams()) as $key => $value) {
            $this->__set($key, $value);
        }
        return get_object_vars($this);
    }

    /**
     * Allow extra parameters to be added on this object
     *
     * @return array
     */
    public function getExtraParams()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getFormId(): string
    {
        return $this->formId;
    }

    /**
     * @param string $formId
     * @return GenericFilter
     */
    public function setFormId(string $formId): GenericFilter
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GenericFilter
     */
    public function setName(string $name): GenericFilter
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GenericFilter
     */
    public function setId(string $id): GenericFilter
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return GenericFilter
     */
    public function setTitle(string $title): GenericFilter
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return GenericFilter
     */
    public function setClass(string $class): GenericFilter
    {
        $this->class = $class;
        return $this;
    }
}