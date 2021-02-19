<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Buttons;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class GenericButton implements Htmlable
{
    /**
     * A variable to be used to determine the position of the button
     * in respect to others within the same section
     * A button with a lower position will be rendered to the far left
     * while one with a higher position will be rendered
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
    public $renderIf = null;

    /**
     * Specify a custom way to render the button
     *
     * @var callable
     */
    public $renderCustom = null;

    /**
     * The link of the button
     *
     * @var string|callable
     */
    public $url = '#';

    /**
     * The buttons name
     *
     * @var string
     */
    public $name = 'Unknown';

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
    public $class = 'btn btn-info';

    /**
     * The icon to be displayed, if any
     *
     * @var null
     */
    public $icon = null;

    /**
     * If a modal should be displayed
     *
     * @var bool
     */
    public $showModal = false;

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
     * The id of the grid in question. Will be used for PJAX
     *
     * @var string
     */
    protected $gridId;

    /**
     * Type of button. Can be one of either `rows` or `toolbar`
     * @var string
     */
    protected $type = 'toolbar';

    /**
     * CreateButton constructor.
     * @param array $params
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
    public function getRenderIf(): callable
    {
        return $this->renderIf;
    }

    /**
     * @param callable $renderIf
     * @return GenericButton
     */
    public function setRenderIf(callable $renderIf): GenericButton
    {
        $this->renderIf = $renderIf;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return GenericButton
     */
    public function setType(string $type): GenericButton
    {
        $this->type = $type;
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
     * @return string|callable
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|callable $url
     * @return GenericButton
     */
    public function setUrl($url): GenericButton
    {
        $this->url = $url;
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
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
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
     * @return string
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
     * @throws \Throwable
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Render the button
     *
     * @param array $args
     * @return string
     * @throws \Throwable
     */
    public function render(...$args)
    {
        // apply preset attributes
        $this->dataAttributes = $this->getDataAttributes();

        // check if modal is needed, and adjust the class attribute
        $this->isShowModal() ? $this->setClass($this->getClass() . ' show_modal_form') : false;

        // can render
        if (!is_callable($this->renderIf)) {
            $this->renderIf = function () {
                return true;
            };
        }
        // custom render
        if ($this->renderCustom && is_callable($this->renderCustom)) {
            return call_user_func($this->renderCustom, $this->compactData($args));
        }

        // collapse the array of args into a single 1d array, so that the values passed can be
        // accessed as key value pair
        $args = Arr::collapse($args);

        return view($this->getButtonView(), $this->compactData($args))->render();
    }

    /**
     * @return array
     */
    public function getDataAttributes(): array
    {
        if ($this->pjaxEnabled) {
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
     * @return bool
     */
    public function isShowModal(): bool
    {
        return $this->showModal;
    }

    /**
     * @param bool $showModal
     * @return GenericButton
     */
    public function setShowModal(bool $showModal): GenericButton
    {
        $this->showModal = $showModal;
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
     * Specify the data to be sent to the view
     *
     * @param array $params
     * @return array
     */
    protected function compactData($params)
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
     * Return the view name used to render the button
     *
     * @return string
     */
    public function getButtonView(): string
    {
        return 'leantony::grid.buttons.button';
    }
}