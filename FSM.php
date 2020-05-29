<?php


class FSM
{
    public $state;

    function setState($value)
    {
        $this->state = $value;
    }

    function updateState($value)
    {
        if (!$this->state) {
            $this->setState($value);
        }
    }

}