<?php
include("../session.php");
require_once(getRootDirectory()."/employee/navbar.php");

$sql = "SELECT hotel.name as hotel_name, booking.start_date, booking.end_date, hotel.address, booking.r_id, thecustomer.c_id, thecustomer.name, booking.b_id,
room.capacity, room.r_id, room.type
FROM booking, hotel, room, thecustomer
WHERE
booking.r_id = room.r_id AND
hotel.h_id = room.h_id AND
start_date > NOW() AND
thecustomer.c_id = booking.c_id AND
status = 'pending'
";
$resultPendingHotelReservations = $db->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (isset($_POST['accept']))
    {
        
        $b_id = $_POST['accept'];
        $sql = "UPDATE booking SET status = 'approved' WHERE b_id = $b_id";
        $result = $db->query($sql);
    }
    else if (isset($_POST['decline']))
    {
        $b_id = $_POST['decline'];
        $sql = "UPDATE booking SET status = 'rejected' WHERE b_id = $b_id";
        $result = $db->query($sql);

        $reason = $_POST['reason'];
        $sql = "UPDATE booking SET reason = '$reason' WHERE b_id = $b_id";
        $result = $db->query($sql);
    }
    header("Refresh:0");
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>pending tours</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../styles/navbar.php">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <?php
        //echo getCustomerNav("./");
    ?>
    <br>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">Hotel Name</th>
                <th scope="col">Hotel Location</th>
                <th scope="col">start date</th>
                <th scope="col">end date</th>
                <th scope="col">room capacity</th>
                <th scope="col">room number</th>
                <th scope="col">type</th>
                <th scope="col">customer name</th>
            </tr>
        </thead>
        <tbody>
            <h3> Pending hotel reservations </h3>
            <?php while ($row = $resultPendingHotelReservations->fetch_assoc()) : ?>
            <tr id=<?php $row['b_id'] ?>>
                <td> <?php echo $row['hotel_name'] ?> </td>
                <td> <?php echo $row['address'] ?> </td>
                <td> <?php echo $row['start_date'] ?> </td>
                <td> <?php echo $row['end_date']  ?> </td>
                <td> <?php echo $row['capacity']  ?> </td>
                <td> <?php echo $row['r_id']  ?> </td>
                <td> <?php echo $row['type']  ?> </td>
                <td> <?php echo $row['name']  ?> </td>
                <td> <?php echo $row['b_id'];  ?> </td>
                
                <td><form action="" method="post" id="form1">
                    <button class="btn btn-primary" type="submit" name="accept">accept</button>                    
                    <input type="hidden" name="accept" value= "<?php echo $row['b_id']; ?>">
                    </form></td>

                <td> <form action="" method="post" id="form1">
                    <button class="btn btn-primary" type="submit" name="decline">decline</button>                    
                    <input type="hidden" name="decline" value= "<?php echo $row['b_id']; ?>">

                    <label for="fname">reason</label>
                    <input type="text" id="reason" name="reason" placeholder="reason.." required="true">
                    
                    </form></td>

                <td><form action="c" method="post" id="form1">
                    <button class="btn btn-primary" type="submit" name="ResDetails">details</button>                    
                    <input type="hidden" name="ts_id" value="">
                    </form></td>

                   
                    
                
                
                   
                    
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <hr class="rounded">
</body>

</html>