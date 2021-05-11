<?php

/*
 * Setup base env stuff
 */
ini_set('user_agent', 'ClueBot/2.0 (ClueBot NG API)');
date_default_timezone_set('America/New_York');

/*
 * Include our functions and settings
 */
require_once('../web-settings.php');
require_once('includes/output_functions.php');

/*
 * If a format was requested then overwrite the default $output_format
 */
if (isset($_REQUEST['format']) && !empty($_REQUEST['format'])) {
    $output_format = (string)strtolower($_REQUEST['format']);
}

/*
 * Send the relevent header for the format we are going to be outputting later
 */
switch ($output_format) {
    case "xml":
        header('Content-Type: text/xml');
        break;

    case "json":
        header('Content-Type: text/json');
        break;

    case "php":
        header('Content-Type: text/php');
        break;

    case "debug":
    default:
        header('Content-Type: text/plain');
        break;
}

/*
 * Try and connect to mysql - we need this for pretty much everything
 */
$mysql = @mysqli_connect($dbHost, $dbUser, $dbPass);
if (!$mysql) {
    $data = array(
        'error' => 'db_error',
        'error_message' => 'Could not connect to database server',
    );
    die(output_encoding($data));
}

/*
 * Try and select the db
 */
if (!@mysqli_select_db($dbSchema)) {
    $data = array(
        'error' => 'db_error',
        'error_message' => 'Could not access database scheme',
    );
    die(output_encoding($data));
}

/*
 * Set the action to help - this is the default
 */
$action = 'help';

/*
 * If an action was requested then overwrite the default $action var
 */
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = (string)strtolower($_REQUEST['action']);
}

/*
 * Convert dots to _ and lower the action string
 */
$action = strtolower(str_replace('.', '_', $action));

/*
 * Build the full path to the action
 */
$action_module = realpath('modules/' . $action . '.module.php');

/*
 * Build the base dirs to compare later
 */
$base_dir = realpath('modules/');
$action_dir = dirname($action_module);

/*
 * Check the the action_module is valid (missing files return false from realpath)
 */
if ($action_module === false || !is_file($action_module)) {
    $data = array(
        'error' => 'unknown_action',
        'error_message' => 'The requested action is unknown',
    );
    die(output_encoding($data));
} else {
    /*
     * Check the action dir falls within the base dir
     * This is a simple security check
     */
    if ($action_dir === $base_dir) {
        require_once($action_module);
    } else {
        $data = array(
            'error' => 'invalid_action',
            'error_message' => 'The requested action was badly formed',
        );
        die(output_encoding($data));
    }
}
