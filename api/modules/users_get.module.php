<?php

/*
 * Users get
 * - Returns a specific user in the database
 */

$data = array();

$query = "SELECT * FROM `users`";

if (isset($_REQUEST['uid'])) {
    $query .= " WHERE `userid` = '" . mysqli_real_escape_string($mysql, $_REQUEST['uid']) . "'";
} elseif (isset($_REQUEST['username'])) {
    $query .= " WHERE `username` = '" . mysqli_real_escape_string($mysql, $_REQUEST['username']) . "'";
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "Neither uid or username was specified.",
    );
    die(output_encoding($data));
}

$result = mysqli_query($mysql, $query);
if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    $data = array(
        "userid" => $row['userid'],
        "username" => $row['username'],
        "admin" => $row['admin'],
        "superadmin" => $row['superadmin'],
        "password" => '',
        "email" => '',
    );
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "Specified uid or username was not found.",
    );
    die(output_encoding($data));
}

die(output_encoding($data));
