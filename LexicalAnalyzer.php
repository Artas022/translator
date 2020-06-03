<?php

require_once "FSM.php";

class LexicalAnalyzer
{

    public $tables = [
        'IDENTIFIER' => [],
        'LITERAL' => [],
        'DELIMITER' => [],
        'OPERATION' => [],
        'KEYWORD' => [],
    ];

    public $lexems = [];

    // идентификаторы
    const IDENTIFIER_CLASS = 0;
    // зарезервированные/ключевые слова
    const KEY_WORD_CLASS = 'KEYWORD';
    const ID_CLASS = 'IDENTIFIER';
    const DELIMITER_CLASS = 'DELIMITER';
    const OPERATION_CLASS = 'OPERATION';
    const LITERAL_CLASS = 'LITERAL';

    const STATE_WAITING_ID = 'ID';
    const STATE_LITERAL = 'LITERAL';
    const STATE_ERROR = 'ERROR';
    const STATE_WAITING_STRING = 'LITERAL_STRING';

    // Массив зарезервированных слов
    const KEY_WORDS = ['integer', 'float', 'char', 'void', 'string', 'return', 'console', 'for', 'while', 'if', 'else', 'true', 'false', 'null'];
    const TYPES = ['integer', 'float', 'char', 'void', 'string'];
    // Массив разделителей
    const DELIMITER_LEX = ['(', ')', '{', '}', ',', ':', ';', "\"",'[',']'];
    const DELIMITER_STRING = ['"'];
    // Массив операций
    const OPERATIONS = ['+', '-', '/', '*', '%', '=', '<<', '>>', '<', '>'];

    public $identifier_table = [];

    public $prev_state;
    public $state;

    public $current_row_index = 0;
    public $current_str_index = 0;

    public $prev_value = '';
    public $value = '';

    public $current_string = '';
    public $next_symbol = '';

    public function findClassLex($symbol)
    {
        $this->value .= $symbol;

        $this->checkClass();

        return true;
    }

    public function checkClass()
    {
        switch ($this->state) {
            case self::STATE_WAITING_ID:
                $this->isKeyWord();
                $this->isIdentifier();
                break;
            case self::STATE_WAITING_STRING:
                $this->isLiteral();
                break;
            default:
                $this->isKeyWord();
                $this->isIdentifier();
                $this->isDelimiter();
                $this->isLiteral();
                $this->isOperation();
        }
        if (ctype_space($this->value)) {
            $this->value = trim($this->value);
        }
        return true;
    }

    public function setType(string $type, $value, $state = '')
    {
        $this->tables[$type][] = $value;
        $this->lexems[] = ['type' => $type, 'value' => $value];
        $this->prev_value = $value;
        $this->value = '';
        $this->state = '';
        $this->prev_state = $state;
    }

    public function analyze(array $text)
    {
        foreach ($text as $row_index => $str) {
            $this->current_string = str_split(trim($str));
            $this->current_row_index = $row_index+1;

            $str_length = count($this->current_string);
            $this->resetState();

            foreach ($this->current_string as $index => $symbol) {
                $this->current_str_index = $index;

                $this->next_symbol = $index + 1 < $str_length ? $this->current_string[$index + 1] : '';

                $this->findClassLex($symbol);
                if ($this->state === self::STATE_ERROR) {
                    return $this->value;
                }
            }
        }

        if ($this->state === self::STATE_WAITING_STRING) {
            $this->addError('Incorrect string value!', $this->value);
            return $this->value;
        }

        return $this->tables;
    }

    public function isKeyWord()
    {
        if (in_array($this->value, self::KEY_WORDS)) {
            $this->setType(self::KEY_WORD_CLASS, $this->value, self::KEY_WORD_CLASS);
            return true;
        }
        return false;
    }

    public function addError(string $msg, $value)
    {
        $this->value = $msg . ' (row '.$this->current_row_index.', position '.$this->current_str_index.')';
        $this->state = self::STATE_ERROR;
        return true;
    }

    public function isIdentifier_old()
    {
        preg_match('/^[_A-Za-z]+[\d_A-Za-z]{0,}$/', $this->value, $is);
        if ($this->state === self::STATE_WAITING_ID) {
            preg_match('/^[_A-Za-z]+[\d_A-Za-z]{0,}$/', $this->next_symbol, $isNext);
            if (!$isNext) {
                if (in_array($this->value, $this->identifier_table)) {
                    $this->setIdentifier($this->value);
                    $this->setType(self::ID_CLASS, $this->value, self::ID_CLASS);
                    $this->state = '';
                    return true;
                }
                if (!in_array($this->prev_value, self::TYPES)) {
                    $this->addError('Unknown variable!', $this->value);
                    return false;
                }
                $this->setIdentifier($this->value);
                $this->setType(self::ID_CLASS, $this->value, self::ID_CLASS);
                $this->state = '';
                return true;
            }
        }
        if ($is) {
            if (in_array($this->value, $this->identifier_table)) {
                $this->setIdentifier($this->value);
                $this->setType(self::ID_CLASS, $this->value, self::ID_CLASS);
                $this->state = '';
                return true;
            }
            $this->state = self::STATE_WAITING_ID;
        }
        return false;
    }

    public function isIdentifier()
    {
        preg_match('/^[_A-Za-z]+[\d_A-Za-z]{0,}$/', $this->value, $is);
        if ($is)
        {
            preg_match('/^[_A-Za-z]+[\d_A-Za-z]{0,}$/', $this->next_symbol, $isNext);
            if (!$isNext)
            {
                if (!in_array($this->value, $this->identifier_table)) {
                    $this->setIdentifier($this->value);
                    $this->setType(self::ID_CLASS, $this->value);
                } else {
                    $this->setType(self::ID_CLASS, $this->value);
                    $this->value = '';
                }
                return true;
            }
        }
        return false;
    }

    public function setIdentifier($value)
    {
        $this->identifier_table[] = $value;
    }

    public function isDelimiter()
    {
        if (in_array($this->value, self::DELIMITER_LEX)) {
            $this->setType(self::DELIMITER_CLASS,  $this->value);
            return true;
        }
        return false;
    }

    public function isOperation()
    {
        if (in_array($this->value, self::OPERATIONS)) {
            if (in_array($this->next_symbol, self::OPERATIONS)) {
                return true;
            }
            $this->setType(self::OPERATION_CLASS, $this->value, self::OPERATION_CLASS);
            return true;
        }
        return false;
    }

    public function isLiteral()
    {
        // is number
        preg_match('/^[\d.,]+$/', $this->value, $isNumber);
        if ($isNumber) {
            preg_match('/^[\d.,]+$/',$this->next_symbol,$isNext);
            if ($isNext) {
                $this->state = self::STATE_LITERAL;
                return true;
            } else {
                $this->setType(self::LITERAL_CLASS, $this->value, self::LITERAL_CLASS);
            }
        }
        // if string
        if ($this->state === self::STATE_WAITING_STRING)
        {
            $string = $this->value.$this->next_symbol;
            preg_match('/^[a-zA-Z\d\s]+"$/', $string, $isString);
            if ($isString) {
                $this->setType(self::LITERAL_CLASS, $this->value);
                return true;
            }
        } else {
            $string = $this->prev_value.$this->value;
            preg_match('/^"[a-zA-Z\d\s]+/', $string, $isString);
            if ($isString)
            {
                $this->state = self::STATE_WAITING_STRING;
                return true;
            }
        }
        return false;
    }

    public function resetState()
    {
        $this->state = '';
        $this->prev_state = '';
    }
}