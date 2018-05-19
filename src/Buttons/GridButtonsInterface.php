<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Buttons;

interface GridButtonsInterface
{
    /**
     * Configure rendered buttons, if need be.
     *
     * @return void
     */
    public function configureButtons();

    /**
     * Add a button to the grid
     *
     * @param $target string the location where the button will be rendered
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
    public function getButtons($key);

    /**
     * Sets an array of buttons that would be rendered to the grid
     *
     * @return void
     */
    public function setButtons();
}