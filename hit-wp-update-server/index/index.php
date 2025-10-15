<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
ini_set('error_log', 'php-errors.log');
ob_start();
define( 'KB_IN_BYTES', 1024 );

require '../config.php';
require '../autoload.php';

use hitwpupdateserver\app\core\app;
use hitwpupdateserver\app\core\crypt;
use hitwpupdateserver\app\core\BinaryConverter;
// Beispielverwendung:
$converter = new BinaryConverter();
//crypt::generateAndSaveKeys(DOCROOT.'/mycryptkeys.php');

crypt::loadKeys(DOCROOT.'/mycryptkeys.php');

$test = 'Du bist eine verdammt geile Sau und du weißt es.';

//echo $test;

//echo '<br>';

$test = crypt::encrypt($test);
$test = $converter->textToBinary($test);
echo $test;

echo '<br>';
$test = $converter->binaryToText($test);
$test = crypt::decrypt($test);

//echo $test;

echo '<br>';






/*
// Text zu Binär
$text = "Hallo Welt!123";
$binaryText = $converter->textToBinary($text);
echo "Text: '$text'\n";
echo "Binär: $binaryText\n\n";

// Binär zurück zu Text
$decodedText = $converter->binaryToText($binaryText);
echo "Zurück zu Text: '$decodedText'\n\n";

// Zahl zu Binär
$number = 42;
$binaryNumber = $converter->numberToBinary($number);
echo "Zahl: $number\n";
echo "Binär: $binaryNumber\n\n";

// Binär zurück zu Zahl
$decodedNumber = $converter->binaryToNumber($binaryNumber);
echo "Zurück zu Zahl: $decodedNumber\n\n";

// Beispiel mit verschiedenen Trennzeichen
$binaryWithDash = $converter->textToBinary("PHP", '-');
echo "Text 'PHP' mit Bindestrich: $binaryWithDash\n";
echo "Zurück zu Text: " . $converter->binaryToText($binaryWithDash, '-') . "\n";
*/