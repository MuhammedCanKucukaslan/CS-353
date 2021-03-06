<?php
include("../session.php");
$cid = $_SESSION['id'];

$sql = "SELECT wallet FROM thecustomer WHERE c_id = $cid";
$currentWallet = $db->query($sql);
$row = $currentWallet->fetch_assoc();
$currentWallet = $row['wallet'];

if (isset($_POST['TourDetails'])) {
    $tsId = $_POST['tsId'];
    header("location: tourDetails.php?tsId=$tsId");
}
if (isset($_POST['TGProfile'])) {
    $tgId = $_POST['tgId'];
    header("location: profile.php?tgId=$tgId");
}
if (isset($_POST['ReserveTour'])) {
    $ts_id = $_POST['tsId'];
    $number = $_POST['numofPeople'];
    $cost = $_POST['cost'];
    
    if ($number < 1) {
        echo '<script>alert("You Have To Reserve For 1 or More People")</script>';
    } else {
        $totalCost = $number * $cost;
        if ($currentWallet < $totalCost)
        {
            echo '<script>alert("You Dont Have Enough Money")</script>';
        }
        else
        {
            $newWallet = $currentWallet - $totalCost;
            $sql = "UPDATE thecustomer SET wallet=$newWallet WHERE c_id=$cid";
            $db->query($sql);
            $sql = "INSERT INTO reservation (c_id, ts_id, e_id, number, status, bill, isRated, reason) VALUES ($cid, $ts_id, null, $number, 'pending', $totalCost, 'no', null)";
            $db->query($sql);
            header("Refresh:0");
        }
        
    }
}

if (isset($_POST['ResDetails'])) {
    $resId = $_POST['resId'];
    header("location: reservationDetails.php?resId=$resId");
}

if (isset($_POST['CancelRes'])) 
{
    $res_id = $_POST['resId'];

    $sql = "SELECT bill FROM reservation WHERE res_id=$res_id";
    $bill = $db->query($sql);
    $row = $bill->fetch_assoc();
    $bill = $row['bill'];

    $sql = "UPDATE thecustomer SET wallet = wallet + $bill WHERE c_id=$cid";
    $db->query($sql);

    $sql = "DELETE FROM reservation WHERE res_id = $res_id";
    $db->query($sql);
    header("Refresh:0");
}

$sql = "SELECT reservation.res_id, tour_section.ts_id, tour_section.cost, reservation.bill, reservation.number, tour.type, tour_section.start_date, tour_section.end_date, tour_guide.name, tour_guide.lastname, reservation.status, reservation.reason
FROM tour_section, reservation, guides, tour, tour_guide 
WHERE tour.t_id = tour_section.t_id 
AND reservation.ts_id = tour_section.ts_id 
AND guides.tg_id = tour_guide.tg_id 
AND guides.ts_id = tour_section.ts_id 
AND guides.status = 'approved'
AND (reservation.status = 'pending' OR reservation.status = 'rejected') 
AND tour_section.start_date > NOW()
AND reservation.c_id = $cid ";

$resultTourPending = $db->query($sql);

$sql = "SELECT reservation.res_id, tour_section.cost, reservation.bill, reservation.number, tour.type, tour_section.start_date, tour_section.end_date, tour_guide.name, tour_guide.lastname 
FROM tour_section, reservation, guides, tour, tour_guide 
WHERE tour.t_id = tour_section.t_id 
AND reservation.ts_id = tour_section.ts_id 
AND guides.tg_id = tour_guide.tg_id 
AND guides.ts_id = tour_section.ts_id
AND guides.status = 'approved'
AND reservation.status = 'approved' 
AND tour_section.end_date > NOW()
AND reservation.c_id = $cid ";

$resultTourReserved = $db->query($sql);

$sql = "SELECT tour.type, tour_section.cost, tour_section.start_date, tour_section.end_date, tour_guide.name, tour_guide.lastname, tour_guide.tg_id, tour_section.ts_id
FROM tour_section, guides, tour_guide, tour
WHERE tour.t_id = tour_section.t_id 
AND guides.tg_id = tour_guide.tg_id 
AND guides.ts_id = tour_section.ts_id
AND guides.status = 'approved'
AND tour_section.start_date > NOW()
AND tour_section.ts_id NOT IN (SELECT reservation.ts_id
FROM reservation 
WHERE reservation.c_id = $cid
AND (reservation.status = 'approved' OR reservation.status = 'pending'))";

if (isset($_POST['filterTours'])) {
    $start = $_POST['tour-start'];
    $end = $_POST['tour-end'];
    $sql .= "AND tour_section.start_date >= '$start' AND tour_section.end_date <= '$end'";
}

if (isset($_POST['clearFilter'])) {
    $start = $_POST['tour-start'];
    $end = $_POST['tour-end'];
    str_replace("AND tour_section.start_date >= '$start' AND tour_section.end_date <= '$end'", "", $sql);
}

if (isset($_POST['deleteRej']))
{
    $res_id = $_POST['resId'];
    $sql = "DELETE FROM reservation WHERE res_id = $res_id";
    $db ->query($sql);
    header("Refresh:0");
}

$resultTour = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Customer Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../styles/navbar.php">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>

    <div style="border: 2px solid red; border-radius: 5px;" class="pill-nav">
        <a href="../customer">Home</a>
        <a href="reserveFlight.php">Reserve a Flight</a>
        <a href="pastTours.php">Past Tours</a>
        <a class="nav-link active" href="bookTour.php">Book a Tour</a>
        <a href="reserveHotel.php">Reserve a Hotel</a>
        <a href="profile.php">Profile</a>
        <form action="../logout.php">
            <input type="submit" name="logout" class="btn btn-danger" value="Logout" />
        </form>
    </div>
    <!-- End of Navbar -->
    <h2 style="background-color:powderblue; border-radius:7px; width:25%; font-family:courier;">Wallet: <?php echo $currentWallet?>$</h2>
    <br>
    <form method="post" action="bookTour.php">
        <label for="start">Start date:</label>
        <input type="date" id="start" name="tour-start">

        <label for="end">End date:</label>
        <input type="date" id="end" name="tour-end">

        <button class="btn btn-primary" type="submit" name="filterTours">Filter</button>
        <button class="btn btn-warning" type="submit" name="clearFilter">Clear Filter</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Tour Name</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Tour Guide Name</th>
                <th scope="col"># of People</th>
                <th scope="col">Total Cost</th>
                <th scope="col">Status</th>
                <th scope="col">Options</th>
                <th scope="col">Rejection Reason (If Applicable)</th>
            </tr>
        </thead>
        <tbody>
            <h3>Pending Tours</h3>
            <?php while ($row = $resultTourPending->fetch_assoc()) : ?>
            <tr id=<?php $row['res_id'] ?>>
                <td> <?php echo $row['type'] ?> </td>
                <td> <?php echo $row['start_date'] ?> </td>
                <td> <?php echo $row['end_date'] ?> </td>
                <td> <?php echo $row['name'] . " " . $row['lastname'] ?> </td>
                <td> <?php echo $row['number']?> </td>
                <td> <?php echo $row['bill']?> </td>
                <td> <?php echo $row['status']?> </td>
                <td>
                    <form method="post" action="bookTour.php">
                        <button class="btn btn-primary" type="submit" name="TourDetails">Details</button>
                        <form method="post" action="reserveHotel.php">
                    <?php
                        if ($row['status'] == 'rejected')
                        {
                            $reason = $row['reason'];
                            echo '<button class="btn btn-danger" type="submit" name="deleteRej">Delete</button>
                            <td>' .$reason. '</td>';
                        }
                        else
                        {
                            $todayDate = date('Y-m-d');
                            if ($row['start_date'] > $todayDate)
                            {
                                echo '<button onclick="return  confirm(\'Are You Sure You Want To Delete This Reservation Y/N\')"
                                class="btn btn-warning" type="submit" name="CancelRes">Cancel Reservation</button>';
                            }
                            
                        }                        
                        ?>
                        <input type="hidden" name="resId" value="<?php echo $row['res_id']; ?>">
                        <input type="hidden" name="tsId" value="<?php echo $row['ts_id']; ?>">
                        <input type="hidden" name="status" value="<?php echo $row['status']; ?>">
                        <input type="hidden" name="reason" value="<?php echo $row['reason']; ?>">
                    </form>
                    </form>
                    
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Tour Name</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Tour Guide Name</th>
                <th scope="col">Price Per Person</th>
                <th scope="col"># of People</th>
                <th scope="col">Total Cost</th>
                <th scope="col">Options</th>
            </tr>
        </thead>
        <tbody>
            <h3>Reserved Tours</h3>
            <?php while ($row = $resultTourReserved->fetch_assoc()) : ?>
            <tr id=<?php $row['res_id'] ?>>
                <td> <?php echo $row['type'] ?> </td>
                <td> <?php echo $row['start_date'] ?> </td>
                <td> <?php echo $row['end_date'] ?> </td>
                <td> <?php echo $row['name'] . " " . $row['lastname'] ?> </td>
                <td> <?php echo $row['cost']?> </td>
                <td> <?php echo $row['number']?> </td>
                <td> <?php echo $row['bill']?> </td>
                <td>
                    <form method="post" action="bookTour.php">
                        <button class="btn btn-primary" type="submit" name="ResDetails">Details</button>
                        <?php
                        $todayDate = date('Y-m-d');
                        if ($row['start_date'] > $todayDate)
                        {
                            echo '<button onclick="return  confirm(\'Are You Sure You Want To Delete This Reservation Y/N\')"
                            class="btn btn-warning" type="submit" name="CancelRes">Cancel Reservation</button>';
                        }
                         ?>
                        <input type="hidden" name="resId" value="<?php echo $row['res_id']; ?>">
                    </form>
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>


    <table class="table">
        <thead>
            <tr>
                <th scope="col">Tour Name</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Tour Guide Name</th>
                <th scope="col">Price Per Person</th>
                <th scope="col">Options</th>
            </tr>
        </thead>
        <tbody>
            <h3> Available Tours </h3>
            <?php while ($row = $resultTour->fetch_assoc()) : ?>
                <tr id=<?php $row['ts_id'] ?>>
                    <td> <?php echo $row['type'] ?> </td>
                    <td> <?php echo $row['start_date'] ?> </td>
                    <td> <?php echo $row['end_date'] ?> </td>
                    <td> <?php echo $row['name'] . " " . $row['lastname'] ?> </td>
                    <td> <?php echo $row['cost']?> </td>
                    <td>
                        <form method="post" action="bookTour.php">
                            <input type="number" id="people" name="numofPeople" placeholder="# of People">
                            <button class="btn btn-primary" type="submit" name="TGProfile">Tour Guide Profile</button>
                            <button class="btn btn-primary" type="submit" name="TourDetails">Details</button>

                            <button class="btn btn-primary" type="submit" name="ReserveTour">Reserve</button>

                            <input type="hidden" name="tgId" value="<?php echo $row['tg_id']; ?>">
                            <input type="hidden" name="tsId" value="<?php echo $row['ts_id']; ?>">
                            <input type="hidden" name="cost" value="<?php echo $row['cost']?>">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>