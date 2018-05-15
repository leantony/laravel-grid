<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid;

class ModalRenderer
{
    /**
     * Render the modal opening section
     *
     * @param $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function start($data)
    {
        return view('leantony::modal.modal-partial-start', ['modal' => $data]);
    }

    /**
     * Render the modal closing section
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function end()
    {
        return view('leantony::modal.modal-partial-end');
    }
}