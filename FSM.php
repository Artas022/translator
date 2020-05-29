<?php


class FSM
{
    public $state;
    public $s_state;

    const DEFAULT = 'DEFAULT';
    const REQUEUE_IDENTIFIER = 'IDENTIFIER';
    const REQUEUE_NOT_LITERAL = 'NOT_LITERAL';
    const WAITING_SPACE = 'WAITING_SPACE';

    public function __construct()
    {
        $this->state = FSM::DEFAULT;
    }

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