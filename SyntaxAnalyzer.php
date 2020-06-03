<?php

require_once 'LexicalAnalyzer.php';

class SyntaxAnalyzer
{
    /**
     * @var LexicalAnalyzer $lex_analyzer
     */
    public $lex_analyzer;

    /**
     * Array of rules
     */
    const RULES = [
        'ENTRY_POINT' => '/((void|float|char|string|integer)\smain)\(\).+(})$/',
        'DECLARE_IDENTIFIER' => '/(float|char|string|integer)\s([_A-Za-z][_A-Za-z\d]{0,})(;)/',
        'INIT_IDENTIFIER_TEXT' => '/((char|string)(\[[\d]+\])?\s)?([_A-Za-z][_A-Za-z\d]{0,})\s=\s("[A-Za-z\d\s]+")/',
        'INIT_IDENTIFIER_NUMBER' => '/(integer|float)(\[[\d]+\])?\s([_A-Za-z][_A-Za-z\d]{0,})\s=\s([\d\.]+)/',
        'IF-ELSE' => '/(if|else)(.+(\(.+\))\s{0,}({)?)?/',
        'OUTPUT' => '/console\s<<\s(("[A-Za-z\s]+")|([\d.24a-zA-z]+))/',
    ];

    /**
     * @var null|string
     */
    public $error = null;

    /**
     * Generate error
     * @param $msg
     */
    public function generateError($msg)
    {
        $this->error = 'Error: ' . $msg;
    }

    /**
     * Main process
     * @param LexicalAnalyzer $lexicalAnalyzer
     * @param $source
     * @return string|null
     */
    public function analyze(LexicalAnalyzer $lexicalAnalyzer, $source)
    {
        $this->lex_analyzer = $lexicalAnalyzer;

        if (!$this->isEntryPoint($source)) $this->generateError('Can\'t find entry point!');
        if ($this->error) return $this->error;

        $txt_program = array_slice($source, 2,-1);

        foreach ($txt_program as $str) {
            if ($this->checkInRules($str)) {
                if ($this->error) return $this->error;
            }
        }

        return 'Success';
    }

    /**
     * Find ';' at the end of row
     * @param $str
     * @return bool
     */
    public function findEndDelimiter($str)
    {
        $last_symbol = strlen($str)-1;
        return $str[$last_symbol] === ';';
    }

    /**
     * Is output operation
     * @param $rule
     * @param $str
     * @return bool
     */
    public function isOutput($rule, $str)
    {
        preg_match($rule, $str, $is);
        if ($is) {
            if ($this->findEndDelimiter($str)) {
                return true;
            } else {
                $this->generateError('Can\'t find ";" at the end of row!');
                return true;
            }
        }

        return false;
    }

    /**
     * is Declaration|Identifier rule
     * @param $rule
     * @param $str
     * @return bool
     */
    public function isDeclaration($rule, $str)
    {
        preg_match($rule, $str, $isInit);
        preg_match(self::RULES['INIT_IDENTIFIER_TEXT'], $str, $isText);
        preg_match(self::RULES['INIT_IDENTIFIER_NUMBER'], $str, $isNumber);
        if ($isInit || $isText || $isNumber) {
            if ($this->findEndDelimiter($str)) {
                return true;
            } else {
                $this->generateError('Can\'t find ";" at the end of row!');
                return true;
            }
        }
        return false;
    }

    /**
     * is Condition rule
     * @param $rule
     * @param $str
     * @return bool
     */
    public function isCondition($rule, $str)
    {
        preg_match($rule, $str, $isCondition);
        if ($isCondition) {
            if ($isCondition[1] === 'if' || $isCondition[1] === 'else') {
                if ($str[strlen($str)-1] === '{') {
                    return true;
                } else {
                    $this->generateError('Can\'t find "{" for condition!');
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process of checking rules
     * @param string $str
     * @return bool
     */
    public function checkInRules(string $str)
    {
        $r = null;
        foreach (self::RULES as $rule_name => $rule) {

            if ($rule_name === 'DECLARE_IDENTIFIER') {
                $r = $this->isDeclaration($rule, $str);
            }
            if ($rule_name === 'IF-ELSE') {
                $r = $this->isCondition($rule, $str);
            }
            if ($rule_name === 'OUTPUT') {
                $r = $this->isOutput($rule, $str);
            }

            if ($r) return true;
        }

        if (!$r && ($str !== '{' && $str !== '}')) {
            $this->generateError('Unknown syntax, can\'t find rule!');
        }

        return true;
    }

    /**
     * Find entry point
     * @param array $program_text
     * @return bool
     */
    public function isEntryPoint(array $program_text)
    {
        $full_text = implode($program_text,' ');
        preg_match(self::RULES['ENTRY_POINT'], trim($full_text), $is);
        return !empty($is);
    }

}