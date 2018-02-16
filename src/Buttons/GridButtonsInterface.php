<?php

namespace Leantony\Grid\Buttons;

interface GridButtonsInterface
{
    /**
     * Configure rendered buttons, if need be.
     * For example, within this function, you can call `addButton()` to add a button to the grid
     * You can also call `editButtonProperties()` to edit any properties for buttons that will be generated
     *
     * @return void
     */
    public function configureButtons();

    /**
     * Add a button to the grid
     *
     * @param $target string the location where the button will be rendered. Needs to be among the `$buttonTargets`
     * @param $button string the button name. Can be any name
     * @param $instance GenericButton the button instance
     *
     * @return void
     */
    public function addButton(string $target, string $button, GenericButton $instance);

    /**
     * Set default buttons for the grid
     *
     * @return void
     */
    public function setDefaultButtons();

    /**
     * Get an array of button instances to be rendered on the grid
     *
     * @param string|null $key
     * @return array
     */
    public function getButtons($key = null);

    /**
     * Sets an array of buttons that would be rendered to the grid
     *
     * @return void
     */
    public function setButtons();
}