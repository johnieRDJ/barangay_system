<?php
session_start();
include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ============================
   🔴 HANDLE PROFILE UPDATE
============================ */
if(isset($_POST['save'])){

    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);

    mysqli_query($conn,
    "UPDATE users 
     SET address='$address',
         phone='$phone',
         about='$about'
     WHERE user_id='$user_id'");

     // ✅ LOG
mysqli_query($conn,
"INSERT INTO logs (user_id, action)
 VALUES ('$user_id','Updated profile information')");
}

/* ============================
   🔴 HANDLE IMAGE UPLOAD
============================ */
if(isset($_POST['upload'])){

    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];

    $path = "../uploads/profile/" . $image;

    move_uploaded_file($tmp, $path);

    mysqli_query($conn,
    "UPDATE users SET profile_image='$image'
     WHERE user_id='$user_id'");

     // ✅ LOG
mysqli_query($conn,
"INSERT INTO logs (user_id, action)
 VALUES ('$user_id','Uploaded profile image')");
}

/* ============================
   🔴 HANDLE DELETE IMAGE
============================ */
if(isset($_POST['delete'])){

    $get = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT profile_image FROM users WHERE user_id='$user_id'"));

    if($get['profile_image']){
        unlink("../uploads/profile/".$get['profile_image']);
    }

    mysqli_query($conn,
    "UPDATE users SET profile_image=NULL
     WHERE user_id='$user_id'");

     // ✅ LOG
mysqli_query($conn,
"INSERT INTO logs (user_id, action)
 VALUES ('$user_id','Deleted profile image')");
}

/* ============================
   🔴 GET USER DATA
============================ */
$user = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'"));
?>

<h2>My Profile</h2>

<div style="border:1px solid #ccc; padding:15px; width:400px;">

<!-- 🔴 PROFILE IMAGE -->
<?php if($user['profile_image']): ?>
    <img src="../uploads/profile/<?php echo $user['profile_image']; ?>" width="150"><br><br>
<?php else: ?>
    <p>No Image</p>
<?php endif; ?>

<!-- 🔴 UPLOAD IMAGE -->
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <button name="upload">Upload Image</button>
</form>

<br>

<!-- 🔴 DELETE IMAGE -->
<form method="POST">
    <button name="delete">Delete Image</button>
</form>

<hr>

<!-- 🔴 USER INFO -->
<p><strong>Name:</strong> <?php echo $user['firstname']." ".$user['lastname']; ?></p>
<p><strong>Email:</strong> <?php echo $user['email']; ?></p>

<!-- 🔴 EDIT PROFILE -->
<form method="POST">

    <input type="text" name="address" placeholder="Address"
    value="<?php echo $user['address']; ?>"><br><br>

    <input type="text" name="phone" placeholder="Phone Number"
    value="<?php echo $user['phone']; ?>"><br><br>

    <textarea name="about" placeholder="About you"><?php echo $user['about']; ?></textarea><br><br>

    <button name="save">Save Profile</button>

</form>

</div>

<?php include('../includes/footer.php'); ?>