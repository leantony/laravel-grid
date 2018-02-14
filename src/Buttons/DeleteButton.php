<?php

namespace Leantony\Grid\Buttons;

class DeleteButton extends GenericButton
{
    public $position = 2;

    public $class = 'data-remote btn btn-danger btn-xs btn-grid-row';

    public $dynamicLink = true;

    /**
     * Generate the button
     *
     * @return GenericButton
     */
    public function generate()
    {
        return $this->setName(empty($this->getName()) ? 'Delete' : $this->getName())
            ->setDynamicLink($this->isDynamicLink())// let the link be generated with each record on the table
            ->setIcon($this->icon ?? 'fa-trash')
            ->setTitle(empty($this->title) ? 'delete item' : $this->title)
            ->setClass($this->class)
            ->setDataAttributes([
                'method' => 'DELETE',
                'confirm' => 'Sure?',
                'trigger-pjax' => true,
                'pjax-target' => '#' . $this->getGridId()
            ])
            ->setBeforeRender(function () {
                return true;
            });
    }
}