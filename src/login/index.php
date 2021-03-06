<?php
include("../config.php");
session_start();

if( isset($_SESSION['id'])){
    if( strcmp("tour_guide", $_SESSION['type'] ?? "none") == 0) {
        header("Location: ../guide");
    }
    else if( strcmp("employee", $_SESSION['type'] ?? "none") == 0) {
        header("Location: ../employee");
    }
    
    else if( strcmp("thecustomer", $_SESSION['type'] ?? "none") == 0) {
        header("Location: ../customer");
    }
    
    // else continue (do nothing)
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $pass = $_POST['password'];
    $passwordHash = hash('sha256', $pass);
    $userType = $_POST['users'];
    $realUserType = $userType;
    if (strcmp($userType, 'admin') == 0)
    {
        $userType = 'employee';
    }

    $sql = "SELECT * FROM $userType WHERE email = '$email' AND password_hash = '$passwordHash' ";
    $result = mysqli_query($db, $sql);
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    if ($row === null) {
        echo '<script>alert("This user does not exist, please check Email and Password"); </script>';
    } else {
        $count = mysqli_num_rows($result);

        if ($count == 1) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['lastname'] = $row['lastname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['birthday'] = $row['birthday'];
            if ($realUserType == 'thecustomer') 
            {
                $_SESSION['id'] = $row['c_id'];
                $_SESSION['type'] = "thecustomer";
                header("location: ../customer");
            } else if ($realUserType == 'employee') 
            {
                $_SESSION['id'] = $row['e_id'];
                $_SESSION['type'] = "employee";
                //header("location: ../dashboard/dashboardE.php");
                header("location: ../employee/index.php");
            } else if ($realUserType == 'tour_guide') 
            {
                $_SESSION['id'] = $row['tg_id'];
                $_SESSION['type'] = "tour_guide";
                header("location: ../guide/");
            }
            else if ($realUserType == 'admin')
            {
                // chnage this to the admin page
                $_SESSION['id'] = $row['e_id'];
                header("location: ../admin/");
            }
        }
    }
}
?>

<html>

<head>
    <link rel="stylesheet" href="../styles/loginstyles.php" media="screen">
</head>
<form name="loginform" action="" method="post">
    <h1 class="a11y-hidden">Login Form</h1>
    <h2>Login Form</h2>

    <label for="users">Login As:</label>
    <select name="users" id="users">
        <option value="thecustomer">Customer</option>
        <option value="employee">Employee</option>
        <option value="tour_guide">Tour Guide</option>
        <option value="admin">Admin</option>
    </select>

    <div>
        <label class="label-email">
            <input type="email" id="userEmail" class="text" name="email" placeholder="Email" tabindex="1" required />
            <span class="required">Email</span>
        </label>
    </div>
    <div>
        <label class="label-password">
            <input type="password" id="userPass" class="text" name="password" placeholder="Password" tabindex="2"
                required />
            <span class="required">Password</span>
        </label>
    </div>
    <input type="submit" value="Log In" />
    <div class="email">
        <a href="../register/">Not a user? Sign up</a>
    </div>
</form>

</html>