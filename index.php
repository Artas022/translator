<?php

require_once "LexicalAnalyzer.php";

function start()
{
    $lex = new \LexicalAnalyzer();

    $text = "int main()
{
	int a = 5;
	a = 4;
	console << a;

	return 0;
}";

    $lex->analyze($text);

}
start();
?>
