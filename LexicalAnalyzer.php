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
    const TYPES = ['integer', 'float', 'char', 'void'];
    // Массив разделителей
    const DELIMITER_LEX = ['(', ')', '{', '}', ',', ':', ';'];
    // Массив операций
    const OPERATION_LEX = ['+', '-', '/', '*', '%', '=', '<<', '>>', '<', '>'];
    // начало программы
    const BEGIN_PROGRAM = 'main';

    public $state;
    public $current_string = '';
    public $current_position = 0;
    public $current_type;

    public function findClassLex($value) {

        switch ()

        switch($value) {
            case $this->isType($value):
                break;
            case $this->isIdentifier($value):
                break;
            case $this->isDelimiter($value):
                break;
            case $this->isOperation($value):
                break;
            case $this->isLiteral($value):
                break;
        }
    }

    public function setType(string $type, $value)
    {
        $this->current_type = ['type' => $type, 'value' => $value];
    }

    public function analyze(string $text)
    {
        $source = explode(PHP_EOL,$text);
        foreach ($source as $str)
        {
            $this->current_string = str_split(trim($str));
            $val = '';
            foreach ($this->current_string as $index => $symbol)
            {

            }
        }
        return true;
    }

    public function isLexIdentify($value = null)
    {
        if ($value === LexicalAnalyzer::BEGIN_PROGRAM) {
            return true;
        }

        if (!empty($this->current_type)) {
            if ($this->current_type['type'] !== '0') {
                return true;
            }
        }
        return false;
    }

    /**
     * If lex is type
     * @param $value
     * @return bool
     */
    public function isType($value)
    {
        if (in_array($value,LexicalAnalyzer::RESERV_LEX)) {
            $this->setType(LexicalAnalyzer::RESERV_CLASS, $value);
            if (in_array($value, LexicalAnalyzer::TYPES)) {
                $this->state = self::RESERV_CLASS;
            }
            return true;
        }
        return false;
    }

    /**
     * If lex is delimiter
     * @param $value
     * @return bool
     */
    public function isDelimiter($value)
    {
        if (in_array($value,LexicalAnalyzer::DELIMITER_LEX)) {
            $this->setType(LexicalAnalyzer::DELIMITER_CLASS, $value);
            return true;
        }
        return false;
    }

    /**
     * If lex is operation
     * @param $value
     * @return bool
     */
    public function isOperation($value)
    {
        if (in_array($value,LexicalAnalyzer::OPERATION_LEX)) {
            $this->setType(LexicalAnalyzer::OPERATION_CLASS, $value);
            $this->state = LexicalAnalyzer::OPERATION_CLASS;
            return true;
        }
        return false;
    }

    /**
     * If lex is identifier
     * @param $value
     * @return bool
     */
    public function isIdentifier($value)
    {
        if ($this->state === LexicalAnalyzer::OPERATION_CLASS) {
            preg_match('/^([a-zA-Z_]+[\da-zA-Z_]+)/', $value, $isIdentifier);

            if ($isIdentifier) {
                $this->setType(LexicalAnalyzer::IDENTIFIER_CLASS, $value);
                return true;
            }
        } else if ($value === LexicalAnalyzer::BEGIN_PROGRAM) {
            $this->setType(LexicalAnalyzer::IDENTIFIER_CLASS, $value);
            return true;
        }
        return false;
    }

    /**
     * If lex is literal
     * @param $value
     * @return bool
     */
    public function isLiteral($value)
    {
        preg_match('/^([\d]+)/', $value, $isLex);
        if ($isLex) {
            $this->setType(LexicalAnalyzer::LITERAL_CLASS, $value);
            return true;
        }
        return false;
    }
}