<?php

require_once "LexicalAnalyzer.php";

function start()
{
    $lex = new \LexicalAnalyzer();

    $full = "integer main()
{
	integer a = 51;
	a = 4;
	console << a;

	return 0;
}";
    $part = 'integer a = "5rt";';
    $text = $part;
    $lex->analyze($text);

}
start();
?>
