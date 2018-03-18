<?php
include "lang.php";
$interpreter = new Interpreter;
if (isset($_GET['file'])) {
	$code_to_parse = file_get_contents($_GET['file']);
	$interpreter->interpret($code_to_parse);
}