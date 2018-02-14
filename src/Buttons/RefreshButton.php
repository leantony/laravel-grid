<?php

namespace Leantony\Grid\Buttons;

class RefreshButton extends GenericButton
{
    public $position = 2;

    /**
     * Generate the button
     *
     * @return GenericButton
     */
    public function generate()
    {
        return $this->setName('Refresh')
            ->setLink($this->link)
            ->setIcon('fa-refresh')
            ->setTitle($this->title)
            ->setDataAttributes([
                'trigger-pjax' => true,
                'pjax-target' => '#' . $this->getGridId()
            ])
            ->setBeforeRender(function () {
                return true;
            });
    }
}