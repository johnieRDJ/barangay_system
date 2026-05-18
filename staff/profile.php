<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

include('../config/database.php');
include('../includes/header.php');
include('../includes/sidebar.php');

$user_id = intval($_SESSION['user_id']);

/* ============================
    ENSURE PROFILE EXISTS
============================ */
$check = db_select_one($conn,
"SELECT profile_id FROM user_profiles WHERE user_id=? LIMIT 1",
'i',
[$user_id]);

if(!$check){
    db_execute($conn,
    "INSERT INTO user_profiles (user_id) VALUES (?)",
    'i',
    [$user_id]);
}

/* ============================
    HANDLE PROFILE UPDATE
============================ */
if(isset($_POST['save'])){

    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = ($_POST['age'] ?? '') !== '' ? intval($_POST['age']) : null;
    $gender = trim($_POST['gender'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $about = trim($_POST['about'] ?? '');

    db_execute($conn,
    "UPDATE user_profiles 
     SET address=?,
         phone=?,
         age=?,
         gender=?,
         civil_status=?,
         about=?
     WHERE user_id=?",
     'ssisssi',
     [$address, $phone, $age, $gender, $civil_status, $about, $user_id]);

    db_execute($conn,
    "INSERT INTO logs (user_id, action)
     VALUES (?, ?)",
     'is',
     [$user_id, 'Updated profile information']);
}

/* ============================
    HANDLE IMAGE UPLOAD
============================ */
if(isset($_POST['upload'])){

    if(!empty($_FILES['image']['name'])){

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['image']['name']));
        $image = time() . "_" . $safeName;
        $tmp = $_FILES['image']['tmp_name'];

        $path = "../uploads/profile/" . $image;

        move_uploaded_file($tmp, $path);

        db_execute($conn,
        "UPDATE user_profiles 
         SET profile_image=?
         WHERE user_id=?",
         'si',
         [$image, $user_id]);

        db_execute($conn,
        "INSERT INTO logs (user_id, action)
         VALUES (?, ?)",
         'is',
         [$user_id, 'Uploaded profile image']);
    }
}

/* ============================
    HANDLE DELETE IMAGE
============================ */
if(isset($_POST['delete'])){

    $get = db_select_one($conn,
    "SELECT profile_image FROM user_profiles WHERE user_id=? LIMIT 1",
    'i',
    [$user_id]);

    if($get['profile_image']){
        @unlink("../uploads/profile/".$get['profile_image']);
    }

    db_execute($conn,
    "UPDATE user_profiles 
     SET profile_image=NULL
     WHERE user_id=?",
     'i',
     [$user_id]);

    db_execute($conn,
    "INSERT INTO logs (user_id, action)
     VALUES (?, ?)",
     'is',
     [$user_id, 'Deleted profile image']);
}

/* ============================
    GET DATA (JOIN USERS + PROFILE)
============================ */
$user = db_select_one($conn,
"SELECT u.firstname, u.lastname, u.email,
        p.address, p.phone, p.age, p.gender, p.civil_status, p.about, p.profile_image
 FROM users u
 LEFT JOIN user_profiles p ON u.user_id = p.user_id
 WHERE u.user_id=?
 LIMIT 1",
 'i',
 [$user_id]);
?>

<h2>My Profile</h2>

<div class="profile-panel">

<!--  PROFILE IMAGE -->
<?php if(!empty($user['profile_image'])): ?>
    <img src="../uploads/profile/<?php echo htmlspecialchars($user['profile_image']); ?>" width="150"><br><br>
<?php else: ?>
    <p>No Image</p>
<?php endif; ?>

<!--  UPLOAD IMAGE -->
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <button name="upload">Upload Image</button>
</form>

<br>

<!--  DELETE IMAGE -->
<form method="POST">
    <button name="delete">Delete Image</button>
</form>

<hr>

<!--  USER INFO -->
<p><strong>Name:</strong> <?php echo htmlspecialchars($user['firstname']." ".$user['lastname']); ?></p>
<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

<!--  EDIT PROFILE -->
<form method="POST">

    <input type="text" name="address" placeholder="Address"
    value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"><br><br>

    <input type="text" name="phone" placeholder="Phone Number"
    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"><br><br>

    <input type="number" name="age" placeholder="Age" min="0"
    value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>"><br><br>

    <select name="gender">
        <option value="">Select Gender</option>
        <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
        <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
        <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
    </select><br><br>

    <select name="civil_status">
        <option value="">Select Civil Status</option>
        <option value="Single" <?php echo ($user['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
        <option value="Married" <?php echo ($user['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
        <option value="Widowed" <?php echo ($user['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
        <option value="Separated" <?php echo ($user['civil_status'] ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
    </select><br><br>

    <textarea name="about" placeholder="About you"><?php echo htmlspecialchars($user['about'] ?? ''); ?></textarea><br><br>

    <button name="save">Save Profile</button>

</form>

</div>

<?php include('../includes/footer.php'); ?>
