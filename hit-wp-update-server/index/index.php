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

$test = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.  

Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.  

Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.  

Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.  

Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.   

At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam';

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
$rand = 794; // random_int(1,10000);
\hitwpupdateserver\app\core\keycrypt::$mainlength = 4096;
$priv = DOCROOT.'/keys/private/'.hash('sha256', $rand).'.priv';
$pub = DOCROOT.'/keys/public/'.hash('sha256', $rand).'.pubkey';

if(!file_exists($pub) OR !file_exists($priv)) {
    \hitwpupdateserver\app\core\keycrypt::createkeys($priv, $pub);
}

$test = \hitwpupdateserver\app\core\keycrypt::encrypt($test,$pub);

echo $test;
echo '<br>';

$test = \hitwpupdateserver\app\core\keycrypt::decrypt($test,$priv,false);

echo $test;
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