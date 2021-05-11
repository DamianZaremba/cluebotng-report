<?php

/*
 * Reports get
 * - Returns a specific report in the database
 */

$data = array();
$statuses = array(
    'Reported',
    'Invalid',
    'Sending to Review Interface',
    'Bug',
    'Resolved',
    'Queued to be reviewed',
    'Partially reviewed',
    'Reviewed - Included in dataset as Constructive',
    'Reviewed - Included in dataset as Vandalism',
    'Reviewed - Not included in dataset'
);

$query = "SELECT * FROM `reports`";
if (isset($_REQUEST['rid']) && !empty($_REQUEST['rid'])) {
    $query .= " WHERE `revertid` = '" . mysqli_real_escape_string($mysql, $_REQUEST['rid']) . "'";
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "Specified rid was in an invalid format",
    );
    die(output_encoding($data));
}

$result = mysqli_query($mysql, $query);
if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    $data = array(
        "revertid" => $row['revertid'],
        "timestamp" => strtotime($row['timestamp']),
        "reporter" => $row['reporter'],
        "status" => $row['status'],
        "friendly_status" => $statuses[$row['status']],
        "comments" => array(),
    );

    $cresult = mysqli_query($mysql, "SELECT * FROM `comments` WHERE `revertid` = '" . mysqli_real_escape_string($mysql, $row['revertid']) . "'");
    if (mysqli_num_rows($cresult) > 0) {
        while ($crow = mysqli_fetch_assoc($cresult)) {
            $data['comments']['commmentid-' . $crow['commentid']] = array(
                "commentid" => $crow['commentid'],
                "timestamp" => strtotime($crow['timestamp']),
                "user" => $crow['user'],
                "comment" => $crow['comment'],
            );
        }
    }

    die(output_encoding($data));
} else {
    $data = array(
        "error" => "argument_error",
        "error_message" => "Specified rid was not found",
    );
    die(output_encoding($data));
}
