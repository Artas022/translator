<?php

require_once "FSM.php";

class LexicalAnalyzer
{

    public $tables;

    // идентификаторы
    const IDENTIFIER_CLASS = 0;
    // зарезервированные/ключевые слова
    const RESERV_CLASS = 1;
    // разделители
    const DELIMITER_CLASS = 2;
    // операции
    const OPERATION_CLASS = 3;
    // лексемы
    const LITERAL_CLASS = 3;

    // Массив зарезервированных слов
//    const RESERV_LEX = ['int', 'float', 'double', 'string', 'char', 'void'];
    // Служебные слова (типы)
    const SERVICE_LEX = ['integer', 'float', 'char', 'void'];
    // Массив разделителей
    const DELIMITER_LEX = ['(', ')', '{', '}', ',', ':', ';', '"'];
    // Массив операций
    const OPERATION_LEX = ['+', '-', '/', '*', '%', '=', '<<', '>>', '<', '>'];
    // начало программы
    const BEGIN_PROGRAM = 'main';

    public $state;
    public $current_string = '';
    public $current_position = 0;
    public $current_type;

    public function findClassLex($symbol, &$value, FSM $fsm) {

        if ($fsm->state === FSM::DEFAULT) {
            $this->checkClass($value, $symbol, $fsm);
        } else {
            $t = 2;
        }

        switch ($fsm->state) {
            case FSM::REQUEUE_IDENTIFIER:
                if (!$this->isIdentifier($symbol, $value)) {
                    $fsm->setState(FSM::DEFAULT);
                    $this->setType('IDENTIFIER', $value);
                    $value = '';
                    $this->findClassLex($symbol, $value, $fsm);
                } else {
                    $value.= trim($symbol);
                }
                break;
            case FSM::REQUEUE_NOT_LITERAL:
                if ($this->isLiteral($symbol, $value, $fsm)) {
                    $value .= trim($symbol);
                } else {
                    $this->setType('LITERAL', $value);
                    $fsm->setState(FSM::DEFAULT);
                    $value = '';
                    $this->findClassLex($symbol, $value, $fsm);
                }
                break;
            case FSM::DEFAULT:
                $value.= trim($symbol);
                break;
        }
        return true;
    }

    public function checkClass(&$value, &$symbol, FSM &$fsm)
    {
        if ($this->isType($value, $fsm)) {
            $value = '';
            return true;
        }
        if ($this->isDelimiter($symbol)) {
            return true;
        }
        if ($this->isOperation($symbol)) {
            return true;
        }
        if  ($this->isLiteral($symbol, $value, $fsm, true)) {
            return true;
        }
        return false;
    }

    public function setType(string $type, $value)
    {
        $this->tables[] = $value.':'.$type;
    }

    public function analyze(string $text)
    {
        $fsm = new FSM();
        $source = explode(PHP_EOL,$text);
        foreach ($source as $str)
        {
            $this->current_string = str_split(trim($str));
            $val = '';
            foreach ($this->current_string as $index => $symbol) {
                if (ctype_space($symbol)) {
                    continue;
                }
                $this->findClassLex($symbol, $val, $fsm);
            }
        }
        return true;
    }

    /**
     * If lex is type
     * @param $value
     * @param FSM $fsm
     * @return bool
     */
    public function isType($value, FSM &$fsm)
    {
        if (in_array($value,LexicalAnalyzer::SERVICE_LEX)) {
            $this->setType('SERVICE', $value);
            $fsm->setState(FSM::REQUEUE_IDENTIFIER);
            return true;
        }
        return false;
    }

    /**
     * If lex is delimiter
     * @param $value
     * @return bool
     */
    public function isDelimiter(&$value)
    {
        if (in_array($value,LexicalAnalyzer::DELIMITER_LEX)) {
            $this->setType('DELIMITER', $value);
            $value = '';
            return true;
        }
        return false;
    }

    /**
     * If lex is operation
     * @param $value
     * @return bool
     */
    public function isOperation(&$value)
    {
        if (in_array($value,LexicalAnalyzer::OPERATION_LEX)) {
            $this->setType('OPERATION', $value);
            $value = '';
            return true;
        }
        return false;
    }

    /**
     * If lex is identifier
     * @param $symbol
     * @param $value
     * @return bool
     */
    public function isIdentifier($symbol, &$value)
    {
        $val = $value.$symbol;
        preg_match('/^[a-zA-Z_]+[\da-zA-Z_]{0,}$/', $val, $isIdentifier);

        return !empty($isIdentifier);
    }

    /**
     * If lex is literal
     * @param $value
     * @return bool
     */
    public function isLiteral($symbol, &$value, FSM &$fsm, $flag = false)
    {
        $val = $value.$symbol;
        preg_match('/^[\d+]$|^[\d]+[a-zA-z_\d.,-]+?$/', $val, $isLex);
        if ($isLex) {
            $fsm->setState(FSM::REQUEUE_NOT_LITERAL);
            return true;
        }
        return false;
    }
}