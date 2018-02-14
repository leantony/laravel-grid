<?php

namespace Leantony\Grid\Buttons;

class CreateButton extends GenericButton
{
    public $position = 1;

    /**
     * Generate the button
     *
     * @return GenericButton
     */
    public function generate()
    {
        return $this->setName('Create')
            ->setLink($this->link)
            ->setIcon('fa-plus-circle')
            ->setClass('btn btn-success show_modal_form')
            ->setTitle($this->title)
            ->setDataAttributes([])
            ->setBeforeRender(function () {
                return true;
            });
    }
}