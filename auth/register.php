<?php include('../config/database.php'); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js"></script>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <form method="POST" onsubmit="return validateRegister()">

<input type="text" name="firstname" placeholder="First Name" required>

<input type="text" name="lastname" placeholder="Last Name" required>

<input type="email" name="email" placeholder="Email Address" required>

<input type="password" id="password" name="password" placeholder="Password" required>

<input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

<select name="role" required>
<option value="">Select Role</option>
<option value="complainant">Complainant</option>
<option value="staff">Staff (Recipient)</option>
</select>

<button type="submit" name="register">Register</button>

</form>

    <div class="link">
        <a href="login.php">Already have an account? Login</a>
    </div>
</div>

</body>
</html>

<?php
if(isset($_POST['register'])){

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if($password != $confirm_password){
        echo "<script>alert('Passwords do not match');</script>";
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Email already exists');</script>";
    } 
    else{

        mysqli_query($conn,
        "INSERT INTO users (firstname, lastname, email, password, role) 
        VALUES ('$firstname','$lastname','$email','$password_hash','$role')");

        echo "<script>alert('Registration successful! Wait for admin approval.');</script>";
    }
}
?>