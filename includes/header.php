<?PHP

ini_set('display_errors', 'Off');
error_reporting(E_ALL | E_STRICT);
session_start();

require_once 'includes/Page.php';
require_once 'web-settings.php';
require_once 'includes/dbFunctions.php';
require_once 'includes/recaptchalib.php';

foreach (glob('pages/*.page.php') as $page) {
    require_once $page;
}

$mysql = mysqli_connect($dbHost, $dbUser, $dbPass);
if (!$mysql) {
    die('Error.  Could not connect to database.');
}

if (!mysqli_select_db($mysql, $dbSchema)) {
    die('Error.  Database has insufficient permissions.');
}

date_default_timezone_set('America/New_York');

ini_set('user_agent', 'ClueBot/2.0 (ClueBot NG Report Interface)');
