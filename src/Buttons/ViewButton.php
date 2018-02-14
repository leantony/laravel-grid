<?php

namespace Leantony\Grid\Buttons;

class ViewButton extends GenericButton
{
    public $position = 1;

    /**
     * Generate the button
     *
     * @return GenericButton
     */
    public function generate()
    {
        return $this->setName('View')
            ->setDynamicLink(true)// let the link be generated with each record on the table
            ->setIcon('fa-eye')
            ->setClass('btn btn-primary btn-xs show_modal_form')
            ->setTitle('view item')
            ->setDataAttributes([])
            ->setBeforeRender(function () {
                return true;
            });
    }
}