<?PHP

require_once 'includes/header.php';

$result = mysqli_query($mysql, 'SELECT `new_id` FROM `reports` JOIN `vandalism` ON `revertid` = `id` WHERE `status` = 2');

$ids = array();

while ($row = mysqli_fetch_assoc($result)) {
    $ids[] = $row['new_id'];
}

echo implode("\n", $ids);
