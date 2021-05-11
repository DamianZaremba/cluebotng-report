<?php

/*
 * Edits list
 * - Returns edits in the database
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

$query = "SELECT * FROM `vandalism`";
$Where = false;

if (isset($_REQUEST['eid']) && !empty($_REQUEST['eid'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `id` >= '" . mysqli_real_escape_string($mysql, $_REQUEST['eid']) . "'";
}

if (isset($_REQUEST['user']) && !empty($_REQUEST['user'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `user` = '" . mysqli_real_escape_string($mysql, $_REQUEST['user']) . "'";
}

if (isset($_REQUEST['article']) && !empty($_REQUEST['article'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `article` = '" . mysqli_real_escape_string($mysql, $_REQUEST['article']) . "'";
}

if (isset($_REQUEST['heuristic']) && !empty($_REQUEST['heuristic'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `heuristic` = '" . mysqli_real_escape_string($mysql, $_REQUEST['heuristic']) . "'";
}

if (isset($_REQUEST['regex']) && !empty($_REQUEST['regex'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `regex` = '" . mysqli_real_escape_string($mysql, $_REQUEST['regex']) . "'";
}

if (isset($_REQUEST['old_id']) && !empty($_REQUEST['old_id'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `old_id` = '" . mysqli_real_escape_string($mysql, $_REQUEST['old_id']) . "'";
}

if (isset($_REQUEST['new_id']) && !empty($_REQUEST['new_id'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `new_id` = '" . mysqli_real_escape_string($mysql, $_REQUEST['new_id']) . "'";
}

if (isset($_REQUEST['reverted']) && !empty($_REQUEST['reverted'])) {
    if (!$Where) {
        $Where = true;
        $query .= " WHERE";
    }

    $query .= " `reverted` = '" . mysqli_real_escape_string($mysql, $_REQUEST['reverted']) . "'";
}

$result = mysqli_query($mysql, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data['edit-' . $row['id']] = array(
            "id" => $row['id'],
            "timestamp" => $row['timestamp'],
            "user" => $row['user'],
            "article" => $row['article'],
            "heuristic" => $row['heuristic'],
            "regex" => $row['regex'],
            "reason" => $row['reason'],
            "diff" => $row['diff'],
            "old_id" => $row['old_id'],
            "new_id" => $row['new_id'],
            "reverted" => $row['reverted'],
            "beaten" => 0,
            "score" => null,
            "fp_submitted" => 0,
            "reviewed_submitted" => 0,
        );

        if (preg_match("/ANN scored at ([0-9.]+)/", $row['reason'], $matches) === 1) {
            $data['edit-' . $row['id']]['score'] = (float)$matches[1];
        }

        $bresult = mysqli_query($mysql, "SELECT * FROM `beaten` WHERE `diff` = '" . mysqli_real_escape_string($mysql, $row['diff']) . "'");
        if (mysqli_num_rows($bresult) > 0) {
            $data['edit-' . $row['id']]['beaten'] = 1;

            $brow = mysqli_fetch_assoc($bresult);
            $data['edit-' . $row['id']]['beaten_by'] = $brow['user'];
        }

        $fpresult = mysqli_query($mysql, "SELECT * FROM `reports` WHERE `revertid` = '" . mysqli_real_escape_string($mysql, $row['id']) . "'");
        if (mysqli_num_rows($fpresult) > 0) {
            $data['edit-' . $row['id']]['fp_submitted'] = 1;

            $fprow = mysqli_fetch_assoc($fpresult);
            $data['edit-' . $row['id']]['fp_data'] = array(
                "timestamp" => strtotime($fprow['timestamp']),
                "reporterid" => $fprow['reporterid'],
                "reporter" => $fprow['reporter'],
                "status" => $fprow['status'],
                "friendly_status" => $statuses[$fprow['status']],
                "comments" => array(),
            );

            $fpcresult = mysqli_query($mysql, "SELECT * FROM `comments` WHERE `revertid` = '" . mysqli_real_escape_string($mysql, $row['id']) . "'");
            if (mysqli_num_rows($fpcresult) > 0) {
                while ($fpcrow = mysqli_fetch_assoc($fpcresult)) {
                    $data['edit-' . $row['id']]['fp_data']['comments']['commentid-' . $fpcrow['commentid']] = array(
                        "timestamp" => strtotime($fpcrow['timestamp']),
                        "user" => $fpcrow['user'],
                        "userid" => $fpcrow['userid'],
                        "comment" => $fpcrow['comment'],
                    );
                }
            }
        }

        $review_data = file_get_contents('http://review.cluebot.cluenet.org/api?getEdit&geIds=' . urlencode($row['new_id']));
        if (isset($review_data) && !empty($review_data)) {
            $review_xml = simplexml_load_string($review_data);
            if (!isset($review_xml->{"Error"})) {
                $edit = $review_xml->{"GetEdit"}->{"Edit"}->{0};
                if (isset($edit->{"ID"})) {
                    $data['edit-' . $row['id']]['review_submitted'] = 1;

                    $required = (string)$edit->Required;
                    $constructive = (string)$edit->Constructive;
                    $skipped = (string)$edit->Skipped;
                    $vandalism = (string)$edit->Vandalism;
                    $max = max($constructive, $skipped, $vandalism);
                    $sum = $constructive + $skipped + $vandalism;

                    $data['edit-' . $row['id']]['review_data'] = array(
                        "scores" => array(
                            "required" => $required,
                            "constructive" => $constructive,
                            "skipped" => $skipped,
                            "vandalism" => $vandalism,
                        ),
                        "classification" => (string)$edit->Classification,
                        "status" => (string)$edit->Status,
                        "newclassification" => (string)$edit->NewClassification,
                    );
                }
            }
        }
    }
}

die(output_encoding($data));
