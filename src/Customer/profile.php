<?php
include("../session.php");
require_once(getRootDirectory()."/util/navbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <?php
        echo getCustomerNav("./");
    ?>
    <!-- End of Navbar -->
    <h1>welcome to Profile</h1>
</body>

</html>