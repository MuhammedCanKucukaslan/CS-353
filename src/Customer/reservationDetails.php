<?php
include("../session.php");
require_once(getRootDirectory()."/util/navbar.php");
$cid = $_SESSION['id'];

$sql = "SELECT wallet FROM thecustomer WHERE c_id = $cid";
$currentWallet = $db->query($sql);
$row = $currentWallet->fetch_assoc();
$currentWallet = $row['wallet'];

$resId = $_GET['resId'];
$sql = "SELECT activity.a_id, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type
FROM tour_activity, activity, reservation
WHERE tour_activity.ts_id = reservation.ts_id
AND tour_activity.a_id = activity.a_id
AND reservation.res_id = $resId
AND tour_activity.type = 'basic'
ORDER BY activity.date, activity.start_time";
$resultBasic = $db -> query($sql);

$sql = "SELECT activity.a_id, tour_activity.cost, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type 
FROM reservation_activity, activity, tour_activity
WHERE reservation_activity.a_id = activity.a_id
AND tour_activity.a_id = activity.a_id
AND reservation_activity.res_id = $resId
AND tour_activity.type = 'extra'
ORDER BY activity.date, activity.start_time";
$resultExtraReserved = $db -> query($sql);

$sql = "SELECT activity.a_id, tour_activity.cost, activity.name, activity.location, activity.date, activity.start_time, activity.end_time, tour_activity.type
FROM tour_activity, reservation, activity
WHERE reservation.ts_id = tour_activity.ts_id
AND activity.a_id = tour_activity.a_id
AND tour_activity.type = 'extra'
AND reservation.res_id = $resId
AND activity.a_id NOT IN (SELECT activity.a_id
FROM reservation_activity, activity, tour_activity
WHERE reservation_activity.a_id = activity.a_id
AND tour_activity.a_id = activity.a_id
AND res_id = $resId
AND tour_activity.type = 'extra')
ORDER BY activity.date, activity.start_time";
$resultExtraNotReserved = $db -> query($sql);

if (isset($_POST['cancelEvent'])) {
    $activityId = $_POST['details'];
    $costOfEvent = $_POST['costOfEvent'];

    $newWallet = $currentWallet + $costOfEvent;
    $sql = "UPDATE thecustomer SET wallet=$newWallet WHERE c_id=$cid";
    $db->query($sql);

    $sql = "DELETE FROM reservation_activity WHERE res_id = $resId AND a_id = $activityId";
    $db->query($sql);

    header("Refresh:0");
}
if (isset($_POST['reserveEvent'])) {
    $activityId = $_POST['activityId'];
    $costOfEvent = $_POST['costOfEvent'];

    $sql = "INSERT INTO reservation_activity VALUES ($resId, $activityId)";
    $db->query($sql);

    $newWallet = $currentWallet - $costOfEvent;
    $sql = "UPDATE thecustomer SET wallet=$newWallet WHERE c_id=$cid";
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
    <h2 style="background-color:powderblue; border-radius:7px; width:25%; font-family:courier;">Wallet: <?php echo $currentWallet?>$</h2>
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
                <th scope="col">Extra Cost</th>
                <th scope="col">Options</th>
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
                <td> <?php echo $row['cost'] ?> </td>
                <td>

                    <form method="post" action="reservationDetails.php?resId=<?php echo $resId?>">
                        <?php 
                    $todayDate = date('Y-m-d');
                    $todayTime = date('H:i:s');
                    if ($row['date'] > $todayDate || ($row['date'] == $todayDate && $row['start_time'] < $todayTime))
                    {
                        echo '<button class="btn btn-warning" type="submit"
                            name="cancelEvent">Cancel Extra
                            Event</button>'; } ?>
                        <input type="hidden" name="details" value="<?php echo $row['a_id']; ?>">
                        <input type="hidden" name="costOfEvent" value="<?php echo $row['a_id']; ?>">
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
                <td> <?php echo $row['cost'] ?> </td>
                <td>

                    <form method="post" action="reservationDetails.php?resId=<?php echo $resId?>"><button class="btn btn-info" type="submit"
                            name="reserveEvent">Reserve Extra Event</button>
                        <input type="hidden" name="activityId" value="<?php echo $row['a_id']; ?>">
                        <input type="hidden" name="costOfEvent" value="<?php echo $row['a_id']; ?>">
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
                <td> None </td>
                <td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>