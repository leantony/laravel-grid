<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Modal;


class ModalRenderer
{
    public function start($data)
    {
        return view('leantony::modal.modal-partial-start', ['modal' => $data]);
    }

    public function end()
    {
        return view('leantony::modal.modal-partial-end');
    }
}