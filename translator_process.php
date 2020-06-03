
<?php

require_once "LexicalAnalyzer.php";
require_once "SyntaxAnalyzer.php";

function start()
{
    $lex = new \LexicalAnalyzer();
    $answer = null;
    if (isset($_POST['data']))
    {
        $data = $_POST['data'];
        $answer = $lex->analyze($data);
        $syntax = new \SyntaxAnalyzer();
        $answer = $syntax->analyze($lex, $data);
    }
    return json_encode($answer);
}
echo start();
