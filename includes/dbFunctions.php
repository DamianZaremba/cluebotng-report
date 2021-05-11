<?PHP

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

function statusIdToName($status)
{
    global $statuses;
    return $statuses[$status];
}

function statusNameToId($status)
{
    global $statuses;
    return array_search($status, $statuses);
}

function createReport($id, $user)
{
    global $mysql;
    if (isset($_SESSION['userid'])) {
        $userid = $_SESSION['userid'];
        $user = $_SESSION['username'];
    } else {
        $userid = -1;
    }

    $query = 'INSERT INTO `reports` (`revertid`,`reporterid`,`reporter`,`status`) VALUES (';
    $query .= '\'' . mysqli_real_escape_string($mysql, $id) . '\',';
    $query .= '\'' . mysqli_real_escape_string($mysql, $userid) . '\',';
    $query .= '\'' . mysqli_real_escape_string($mysql, $user) . '\',';
    $query .= '0';
    $query .= ')';

    mysqli_query($mysql, $query);

    rc('[[report:' . $id . ']] new https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?page=View&id=' . $id . ' * ' . $user . ' * New Report');
}

function createComment($id, $user, $comment, $forceUser = false)
{
    global $mysql;
    if (!$forceUser) {
        if (isset($_SESSION['userid'])) {
            $userid = $_SESSION['userid'];
            $user = $_SESSION['username'];
        } else {
            $userid = -1;
        }
    } else {
        $userid = -2;
    }

    $query = 'INSERT INTO `comments` (`revertid`,`userid`,`user`,`comment`) VALUES (';
    $query .= '\'' . mysqli_real_escape_string($mysql, $id) . '\',';
    $query .= '\'' . mysqli_real_escape_string($mysql, $userid) . '\',';
    $query .= '\'' . mysqli_real_escape_string($mysql, $user) . '\',';
    $query .= '\'' . mysqli_real_escape_string($mysql, $comment) . '\'';
    $query .= ')';

    mysqli_query($mysql, $query);

    rc('[[report:' . $id . ']] comment https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?page=View&id=' . $id . ' * ' . $user . ' * ' . $comment);
}

function updateStatusIfIncorrect($id, $statusId, $username)
{
    global $mysql;
    $row = mysqli_fetch_assoc(mysqli_query($mysql, 'SELECT `status` FROM `reports` WHERE `revertid` = \'' . mysqli_real_escape_string($mysql, $id) . '\''));
    if ($row['status'] != $statusId) {
        updateStatus($id, $statusId, $username);
    }
}

function updateStatus($id, $statusId, $username)
{
    global $mysql;
    mysqli_query($mysql, 'UPDATE `reports` SET `status` = \'' . mysqli_real_escape_string($mysql, $statusId) . '\' WHERE `revertid` = \'' . mysqli_real_escape_string($mysql, $id) . '\'');
    createComment($id, 'System', $username . ' has marked this report as "' . statusIdToName($statusId) . '".', true);
}

function getReport($id)
{
    global $mysql;
    $id = '\'' . mysqli_real_escape_string($mysql, $id) . '\'';
    $result = mysqli_query(
        $mysql,
        'SELECT `revertid`, UNIX_TIMESTAMP(`timestamp`) AS `time`, `reporterid`, `reporter`, `status`
			FROM `reports`
			WHERE `revertid` = ' . $id
    );

    if (mysqli_num_rows($result) == 0) {
        return null;
    }

    $reportData = mysqli_fetch_assoc($result);

    $data = array(
        'id' => $reportData['revertid'],
        'timestamp' => $reportData['time'],
        'anonymous' => $reportData['reporterid'] == -1 ? true : false,
        'username' => $reportData['reporter'],
        'status' => statusIdToName($reportData['status']),
        'comments' => array()
    );

    $result = mysqli_query(
        $mysql,
        'SELECT `commentid`, UNIX_TIMESTAMP( `timestamp` ) AS `time`, `userid`, `user`, `comment`
			FROM `comments`
			WHERE `revertid` = ' . $id
    );

    while ($row = mysqli_fetch_assoc($result)) {
        $data['comments'][] = array(
            'id' => $row['commentid'],
            'timestamp' => $row['time'],
            'userid' => $row['userid'],
            'anonymous' => $row['userid'] == -1 ? true : false,
            'username' => $row['user'],
            'comment' => $row['comment']
        );
    }

    foreach ($data['comments'] as &$comment) {
        if ($comment['userid'] != -1) {
            $row = mysqli_fetch_assoc(mysqli_query($mysql, 'SELECT `admin`, `superadmin` FROM `users` WHERE `userid` = ' . $comment['userid']));
            if ($row and $row['admin'] == 1) {
                $comment['admin'] = true;
            } else {
                $comment['admin'] = false;
            }
            if ($row and $row['superadmin'] == 1) {
                $comment['sadmin'] = true;
            } else {
                $comment['sadmin'] = false;
            }
        } else {
            $comment['admin'] = false;
            $comment['sadmin'] = false;
        }
    }

    return $data;
}

function isAdmin()
{
    if (isSAdmin()) {
        return true;
    }
    if (!isset($_SESSION['admin'])) {
        return false;
    }
    if ($_SESSION['admin'] === true) {
        return true;
    }
    return false;
}

function isSAdmin()
{
    if (!isset($_SESSION['sadmin'])) {
        return false;
    }
    if ($_SESSION['sadmin'] === true) {
        return true;
    }
    return false;
}

function rc($line)
{
    global $rcport;
    global $mysql;
    $r = mysqli_fetch_assoc(mysqli_query($mysql, 'SELECT `node` from `cluster_node` where type="relay"'));
    if (!$r) {
        return;
    }
    $rc = fsockopen('udp://' . $r['node'], $rcport);
    $line = str_replace(array("\r", "\n"), array('', '/'), $line);
    if (strlen($line) > 400) {
        $line = substr($line, 0, 394) . ' [...]';
    }
    fwrite($rc, $line);
    fclose($rc);
}
