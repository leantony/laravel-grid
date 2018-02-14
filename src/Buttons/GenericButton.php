<?php

namespace Leantony\Grid\Buttons;

use Illuminate\Contracts\Support\Htmlable;
use InvalidArgumentException;

class GenericButton implements Htmlable
{
    /**
     * A variable to be used to determine the position of the button
     * in respect to others within the same section
     * A button with a lower position will be rendered to the far left, while one with a higher position will be rendered
     * to the far right
     *
     * @var integer
     */
    public $position = null;

    /**
     * A function with a boolean return type that would be called before a button is rendered
     *
     * @var callable
     */
    public $beforeRender = null;

    /**
     * Specify if the button has a link that is set dynamically as opposed to a static link
     * E.g button in a row, would ideally have this set to true
     *
     * @var bool
     */
    public $dynamicLink = false;

    /**
     * Specify a custom way to render the button
     *
     * @var callable
     */
    public $renderCustom = null;

    /**
     * The link of the button
     *
     * @var string
     */
    public $link = '#';

    /**
     * The buttons name
     *
     * @var string
     */
    public $name = '';

    /**
     * The buttons ability to support pjax
     *
     * @var bool
     */
    public $pjaxEnabled = false;

    /**
     * The classes for the button
     *
     * @var string
     */
    public $class = 'btn btn-default';

    /**
     * The icon to be displayed, if any
     *
     * @var null
     */
    public $icon = null;

    /**
     * The title of the button
     *
     * @var string
     */
    public $title = '';

    /**
     * Any available data attributes
     *
     * @var array
     */
    public $dataAttributes = [];

    /**
     * A dynamic route name applied to row buttons
     *
     * @var string
     */
    protected $dynamicRouteName = null;

    /**
     * A callback to generate urls for dynamic buttons
     *
     * @var callable
     */
    protected $urlRenderer = null;

    /**
     * The id of the grid in question. Will be used for PJAX
     *
     * @var string
     */
    protected $gridId;

    /**
     * CreateButton constructor.
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
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new InvalidArgumentException("The property " . $name . " does not exist on " . get_called_class());
    }

    /**
     * Set a class attribute
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @return callable
     */
    public function getBeforeRender(): callable
    {
        return $this->beforeRender;
    }

    /**
     * @param callable $beforeRender
     * @return GenericButton
     */
    public function setBeforeRender(callable $beforeRender): GenericButton
    {
        $this->beforeRender = $beforeRender;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDynamicLink(): bool
    {
        return $this->dynamicLink;
    }

    /**
     * @param bool $dynamicLink
     * @return GenericButton
     */
    public function setDynamicLink(bool $dynamicLink): GenericButton
    {
        $this->dynamicLink = $dynamicLink;
        return $this;
    }

    /**
     * @return callable
     */
    public function getRenderCustom(): callable
    {
        return $this->renderCustom;
    }

    /**
     * @param callable $renderCustom
     * @return GenericButton
     */
    public function setRenderCustom(callable $renderCustom): GenericButton
    {
        $this->renderCustom = $renderCustom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGridId()
    {
        return $this->gridId;
    }

    /**
     * @param mixed $gridId
     * @return GenericButton
     */
    public function setGridId($gridId)
    {
        $this->gridId = $gridId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return GenericButton
     */
    public function setLink(string $link): GenericButton
    {
        $this->link = $link;
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
     * @return GenericButton
     */
    public function setName(string $name): GenericButton
    {
        $this->name = $name;
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
     * @return GenericButton
     */
    public function setClass(string $class): GenericButton
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param null $icon
     * @return GenericButton
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
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
     * @return GenericButton
     */
    public function setTitle(string $title): GenericButton
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     * @return GenericButton
     */
    public function setPosition(string $position): GenericButton
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPjaxEnabled(): bool
    {
        return $this->pjaxEnabled;
    }

    /**
     * @param bool $pjaxEnabled
     * @return GenericButton
     */
    public function setPjaxEnabled(bool $pjaxEnabled): GenericButton
    {
        $this->pjaxEnabled = $pjaxEnabled;
        return $this;
    }


    /**
     * @return array
     */
    public function getDataAttributes(): array
    {
        if($this->pjaxEnabled) {
            // set by default some attributes to control PJAX on the front end
            return array_merge($this->dataAttributes, [
                'trigger-pjax' => true,
                'pjax-target' => '#' . $this->getGridId()
            ]);
        }
        return $this->dataAttributes;
    }

    /**
     * @param array $dataAttributes
     * @return GenericButton
     */
    public function setDataAttributes(array $dataAttributes): GenericButton
    {
        $this->dataAttributes = $dataAttributes;
        return $this;
    }

    /**
     * @return string
     */
    public function getDynamicRouteName(): string
    {
        return $this->dynamicRouteName;
    }

    /**
     * @param string $dynamicRouteName
     * @return GenericButton
     */
    public function setDynamicRouteName(string $dynamicRouteName): GenericButton
    {
        $this->dynamicRouteName = $dynamicRouteName;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Render the button
     *
     * @return string
     */
    public function render()
    {
        if (is_callable($this->beforeRender) && call_user_func($this->beforeRender)) {
            if ($this->renderCustom && is_callable($this->renderCustom)) {
                return call_user_func($this->renderCustom, $this->compactData(func_get_args()));
            }
            return view('leantony::grid.buttons.button', $this->compactData(func_get_args()))->render();
        }
        return null;
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
     * @return callable
     */
    public function getUrlRenderer(): callable
    {
        return $this->urlRenderer;
    }

    /**
     * The callback that would be used to render the url for a button with a dynamic url
     * E.g those in a grid row
     *
     * @param callable $url
     * @return GenericButton
     */
    public function setUrlRenderer(callable $url): GenericButton
    {
        $this->urlRenderer = $url;
        return $this;
    }
}