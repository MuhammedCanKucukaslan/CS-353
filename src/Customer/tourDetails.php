<?php
include("../session.php");
require_once(getRootDirectory()."/util/navbar.php");
$cid = $_SESSION['id'];

$tsId = $_GET['tsId'];

$sql = "SELECT activity.a_id, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type
FROM tour_activity, activity, tour_section 
WHERE tour_activity.a_id = activity.a_id
AND tour_section.ts_id = $tsId
AND tour_section.ts_id = tour_activity.ts_id
AND tour_activity.type = 'basic'";
$resultBasic = $db -> query($sql);

$sql = "SELECT activity.a_id, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type 
FROM activity, tour_activity, tour_section
WHERE tour_activity.a_id = activity.a_id
AND tour_activity.a_id = activity.a_id
AND tour_section.ts_id = $tsId
AND tour_section.ts_id = tour_activity.ts_id
AND tour_activity.type = 'extra'
ORDER BY activity.date, activity.start_time";
$resultExtraReserved = $db -> query($sql);

$sql = "SELECT activity.a_id, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type
FROM tour_activity, activity
WHERE activity.a_id = tour_activity.a_id
AND tour_activity.type = 'extra'
AND activity.a_id NOT IN (SELECT activity.a_id
FROM activity, tour_activity, tour_section
WHERE tour_activity.a_id = activity.a_id
AND tour_section.ts_id = $tsId
AND tour_section.ts_id = tour_activity.ts_id
AND tour_activity.type = 'extra')
ORDER BY activity.date, activity.start_time";
$resultExtraNotReserved = $db -> query($sql);

if (isset($_POST['cancelEvent'])) {
    $activityId = $_POST['details'];
    $sql = "DELETE FROM reservation_activity WHERE res_id = $resId AND a_id = $activityId";
    $db->query($sql);
    header("Refresh:0");
}
if (isset($_POST['reserveEvent'])) {
    $activityId = $_POST['activityId'];
    $sql = "INSERT INTO reservation_activity VALUES ($resId, $activityId)";
    $db->query($sql);
    header("Refresh:0");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Reservation Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<?php
echo getCustomerNav("./");
?>
<br>

<h3>The Activities For Current Tour</h3>
<table class="table">
    <thead>

    <tr>
        <th scope="col">Activity Name</th>
        <th scope="col">Location</th>
        <th scope="col">Date</th>
        <th scope="col">Time</th>
        <th scope="col">Type</th>
    </tr>
    </thead>
    <tbody>

    <?php while($row = $resultExtraReserved->fetch_assoc()) : ?>
        <tr id=<?php $row['a_id']?>>
            <td> <?php echo $row['name'] ?> </td>
            <td> <?php echo $row['location'] ?> </td>
            <td> <?php echo $row['date'] ?> </td>
            <td> <?php echo $row['start_time']. " - " . $row['end_time'] ?> </td>
            <td> <?php echo $row['type'] ?> </td>
            <td>

                <form method="post" action="tourDetails.php?tsId=<?php echo $resId?>">
                    <?php
                    $todayDate = date('Y-m-d');
                    $todayTime = date('H:i:s');
                    if ($row['date'] > $todayDate || ($row['date'] == $todayDate && $row['start_time'] < $todayTime))
                    {
                        echo '<button class="btn btn-warning" type="submit"
                            name="cancelEvent">Cancel Extra
                            Event</button>'; } ?>
                    <input type="hidden" name="details" value="<?php echo $row['a_id']; ?>">
                </form>

            </td>
        </tr>
    <?php endwhile; ?>

    <?php while($row = $resultExtraNotReserved->fetch_assoc()) : ?>
        <tr id=<?php $row['a_id']?>>
            <td> <?php echo $row['name'] ?> </td>
            <td> <?php echo $row['location'] ?> </td>
            <td> <?php echo $row['date'] ?> </td>
            <td> <?php echo $row['start_time']. " - " . $row['end_time'] ?> </td>
            <td> <?php echo $row['type'] ?> </td>
            <td>

                <form method="post" action="tourDetails.php?resId=<?php echo $resId?>">

                    <input type="hidden" name="activityId" value="<?php echo $row['a_id']; ?>">
                </form>

            </td>
        </tr>
    <?php endwhile; ?>

    <?php while($row = $resultBasic->fetch_assoc()) : ?>
        <tr id=<?php $row['a_id']?>>
            <td> <?php echo $row['name'] ?> </td>
            <td> <?php echo $row['location'] ?> </td>
            <td> <?php echo $row['date'] ?> </td>
            <td> <?php echo $row['start_time']. " - " . $row['end_time'] ?> </td>
            <td> <?php echo $row['type'] ?> </td>
            <td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</body>

</html>