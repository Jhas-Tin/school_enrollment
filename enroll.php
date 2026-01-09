<?php
session_start();
$siteName = "Smart School System";
require "Admin/db.php";

$schoolName = "Smart School System";
$stmtSchool = $conn->prepare("SELECT name FROM school_profile WHERE id = 1");
$stmtSchool->execute();
$result = $stmtSchool->fetch(PDO::FETCH_ASSOC);
if ($result && !empty($result['name'])) {
    $schoolName = $result['name'];
}

$course_stmt = $conn->prepare("SELECT * FROM courses ORDER BY course_name");
$course_stmt->execute();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

$year_stmt = $conn->prepare("SELECT * FROM year_levels ORDER BY id");
$year_stmt->execute();
$years = $year_stmt->fetchAll(PDO::FETCH_ASSOC);

$showToast = false; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_POST['schedule'])){
        echo "<script>alert('Please select an appointment schedule.');window.history.back();</script>";
        exit;
    }
    $appointment_schedule = $_POST['schedule'];

    $stmtCheckSlot = $conn->prepare("SELECT slots FROM enrollment_schedule WHERE id = ?");
    $stmtCheckSlot->execute([$appointment_schedule]);
    $slotData = $stmtCheckSlot->fetch(PDO::FETCH_ASSOC);
    if(!$slotData || $slotData['slots'] <= 0){
        echo "<script>alert('Selected schedule is full or invalid.');window.history.back();</script>";
        exit;
    }

    $usernameInput = trim($_POST['username']); 
    $emailSuffix = "@student";
    $username = $usernameInput . $emailSuffix; 
    $email = $username;

    $stmtCheck = $conn->prepare("SELECT * FROM studentportal WHERE email = ?");
    $stmtCheck->execute([$email]);
    if($stmtCheck->rowCount() > 0){
        echo "<script>alert('Email already exists!');window.history.back();</script>";
        exit;
    }

    $password = $_POST['password']; 

    $stmt = $conn->prepare("INSERT INTO studentportal (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
    $student_id = $conn->lastInsertId();

    $stmt2 = $conn->prepare("
        INSERT INTO educational_attainment 
        (student_id, elementary_school, elementary_year, junior_high_school, junior_high_year, senior_high_school, senior_high_year) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt2->execute([
        $student_id,
        $_POST['elementary_school'], $_POST['elementary_year'],
        $_POST['junior_high_school'], $_POST['junior_high_year'],
        $_POST['senior_high_school'], $_POST['senior_high_year']
    ]);

    $stmtSlot = $conn->prepare("UPDATE enrollment_schedule SET slots = slots - 1 WHERE id = ?");
    $stmtSlot->execute([$appointment_schedule]);

    $stmt3 = $conn->prepare("
        INSERT INTO enrollment_form
        (student_id, last_name, first_name, middle_name, sex, dob, contact_number, home_address, guardian_name, guardian_contact, guardian_address, course, year_level, appointment_schedule, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt3->execute([
        $student_id,
        $_POST['last_name'], $_POST['first_name'], $_POST['middle_name'],
        $_POST['sex'], $_POST['dob'], $_POST['contact'], $_POST['home_address'],
        $_POST['guardian_name'], $_POST['guardian_contact'], $_POST['guardian_address'],
        $_POST['course'], $_POST['year_level'], $appointment_schedule
    ]);

    $showToast = true; 
}

$schoolName = "Smart School System";
$schoolLogo = ""; // default empty

$stmtSchool = $conn->prepare("SELECT name, logo FROM school_profile WHERE id = 1");
$stmtSchool->execute();
$school = $stmtSchool->fetch(PDO::FETCH_ASSOC);

if ($school) {
    if (!empty($school['name'])) {
        $schoolName = $school['name'];
    }
    if (!empty($school['logo']) && file_exists("uploads/".$school['logo'])) {
        $schoolLogo = "uploads/".$school['logo'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($schoolName) ?> - Enrollment</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
:root {
    --primary-color: #6f42c1;
    --primary-dark: #563d7c;
    --bg-dark: #1f1f2e;
    --section-bg: #2a2a3f;
    --input-bg: #3a3a50;
    --text-light: #f1f1f1;
    --button-gradient: linear-gradient(135deg,#6f42c1,#8e44ad);
    --button-hover: linear-gradient(135deg,#8e44ad,#6f42c1);
}
body {
    font-family: system-ui,sans-serif;
    background: var(--bg-dark);
    color: var(--text-light);
    margin:0;
}
.navbar {
    background:#22223b;
    box-shadow:0 4px 12px rgba(0,0,0,.5);
    padding:1rem 2rem;
    border-radius:0 0 20px 20px;
}
.navbar-brand {
    font-weight:700;
    color:var(--primary-color);
    font-size:1.5rem;
}
.navbar-nav .nav-link {
    color:#ccc;
    font-weight:500;
    margin-right:20px;
    transition:0.3s;
}
.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.active {
    color:var(--primary-color);
}
#navbarLoginBtn {
    border-radius:50px;
    padding:8px 20px;
    font-weight:600;
    background: var(--button-gradient);
    border:none;
    color:#fff;
    transition:0.4s;
    box-shadow:0 4px 15px rgba(111,66,193,0.4);
}
#navbarLoginBtn:hover {
    background: var(--button-hover);
    box-shadow:0 6px 20px rgba(111,66,193,0.6);
}
.navbar-toggler { border: none; }
.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.8)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}
.form-card {
    background: var(--section-bg);
    border-radius:12px;
    padding:25px;
    margin-bottom:25px;
    box-shadow:0 4px 20px rgba(0,0,0,0.5);
}
.section-title {
    font-size:20px;
    font-weight:700;
    color: var(--primary-color);
    margin-bottom:18px;
}
label { font-weight:500;margin-bottom:4px;}
input.form-control, select.form-control, textarea.form-control {
    border-radius:12px;
    background:var(--input-bg);
    color: var(--text-light);
    border:none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);
}
input.form-control::placeholder, textarea.form-control::placeholder { color:#bbb; }
input.form-control:focus, select.form-control:focus, textarea.form-control:focus {
    border:2px solid var(--primary-color);
    box-shadow:0 4px 15px rgba(111,66,193,0.5);
}
button.btn-primary {
    border-radius:50px;
    padding:12px 0;
    width:100%;
    background: var(--button-gradient);
    border:none;
    color:#fff;
    font-weight:600;
    transition:0.4s;
    box-shadow:0 4px 15px rgba(111,66,193,0.4);
}
button.btn-primary:hover {
    background: var(--button-hover);
    box-shadow:0 6px 20px rgba(111,66,193,0.6);
}
.table {
    color: var(--text-light);
}
.table th, .table td { vertical-align: middle; text-align:center; }
.badge.bg-success { background:#28a745; }
.toast { background: #28a745; color: #fff; border-radius:12px; }
.modal-content {
    border-radius:20px;
    padding:40px 30px;
    border:none;
    background: var(--section-bg);
    box-shadow:0 8px 25px rgba(0,0,0,0.5);
}
.modal input, .modal select {
    border-radius:12px;
    padding:12px;
    background: var(--input-bg);
    color: var(--text-light);
    border:none;
    box-shadow:0 2px 10px rgba(0,0,0,0.5);
    transition:0.3s;
}
.modal input:focus, .modal select:focus {
    border:2px solid var(--primary-color);
    box-shadow:0 4px 15px rgba(111,66,193,0.5);
}
.modal button {
    border-radius:50px;
    padding:12px 0;
    width:100%;
    background: var(--button-gradient);
    border:none;
    color:#fff;
    font-weight:600;
    transition:0.4s;
    box-shadow:0 4px 15px rgba(111,66,193,0.4);
}
.modal button:hover {
    background: var(--button-hover);
    box-shadow:0 6px 20px rgba(111,66,193,0.6);
}
input[disabled] {
    background: var(--input-bg) !important;
    color: var(--text-light) !important;
    opacity: 1 !important; 
}
.input-group .form-control[disabled] {
    text-align: center;
}
@media (max-width: 768px){
    .form-card { padding:20px; margin-bottom:20px; }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
<a class="navbar-brand d-flex align-items-center gap-2" href="#">
    <?php if (!empty($school['logo']) && file_exists("uploads/".$school['logo'])): ?>
        <img src="uploads/<?= htmlspecialchars($school['logo']) ?>" 
             alt="School Logo"
             style="
                width:40px;
                height:40px;
                border-radius:50%;
                object-fit:cover;
                border:2px solid #6f42c1;
                background:#fff;
                box-shadow:0 0 10px rgba(111,66,193,0.6);
             ">
    <?php endif; ?>

    <span><?= htmlspecialchars($schoolName) ?></span>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
  <span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse justify-content-between" id="navbarNav">
<ul class="navbar-nav mb-2 mb-lg-0">
<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="contact_now.php">Contact</a></li>
<li class="nav-item"><a class="nav-link active" href="enroll.php">Enroll</a></li>
</ul>
<button id="navbarLoginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
</div>
</nav>

<div class="container py-4">

<form method="POST">

<div class="form-card">
<h5 class="section-title">I. Create Your Student Portal Account</h5>
<div class="row g-3">
<div class="col-md-12">
    <label>Username & Email Suffix</label>
    <div class="input-group">
        <input name="username" type="text" class="form-control" placeholder="Enter username" required>
        <input type="text" class="form-control" value="@student" disabled>
    </div>
</div>
<div class="col-12"><label>Email Address</label><input name="email" type="email" class="form-control" placeholder="name@example.com" required></div>
<div class="col-12"><label>Password</label><input name="password" type="text" class="form-control" required></div>
</div>
</div>

<div class="form-card">
<h5 class="section-title">II. Educational Attainment</h5>
<div class="row g-3 mb-2">
<div class="col-md-8"><label>Elementary</label><input name="elementary_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="elementary_year" class="form-control"></div>
</div>
<div class="row g-3 mb-2">
<div class="col-md-8"><label>Junior High School</label><input name="junior_high_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="junior_high_year" class="form-control"></div>
</div>
<div class="row g-3">
<div class="col-md-8"><label>Senior High School</label><input name="senior_high_school" class="form-control"></div>
<div class="col-md-4"><label>Graduation Year</label><input name="senior_high_year" class="form-control"></div>
</div>
</div>

<div class="form-card">
<h5 class="section-title">Select your appointment schedule</h5>
<table class="table table-bordered text-center align-middle">
<thead class="table-light">
<tr><th>Date</th><th>Time</th><th>Slots</th><th>Action</th></tr>
</thead>
<tbody>
<?php
$stmt = $conn->prepare("
    SELECT * FROM enrollment_schedule 
    WHERE slots > 0 
    AND CONCAT(schedule_date, ' ', start_time) > NOW()
    ORDER BY schedule_date ASC, start_time ASC
");
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
if(count($schedules) > 0):
foreach($schedules as $row):
?>
<tr>
<td><?= date("F d, Y | l", strtotime($row['schedule_date'])) ?></td>
<td><?= date("h:i A", strtotime($row['start_time'])) ?> – <?= date("h:i A", strtotime($row['end_time'])) ?></td>
<td><span class="badge bg-success"><?= $row['slots'] ?> Available</span></td>
<td><input type="radio" name="schedule" class="form-check-input" value="<?= $row['id'] ?>" required></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4">No schedules available</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<div class="form-card">
<h5 class="section-title">III. Enrollment Form</h5>
<div class="row g-3 mb-3">
<div class="col-md-4"><label>Last Name</label><input name="last_name" class="form-control"></div>
<div class="col-md-4"><label>First Name</label><input name="first_name" class="form-control"></div>
<div class="col-md-4"><label>Middle Name</label><input name="middle_name" class="form-control"></div>
<div class="col-md-6"><label>Sex</label><select name="sex" class="form-control"><option disabled selected>Select</option><option>Male</option><option>Female</option></select></div>
<div class="col-md-6"><label>Date of Birth</label><input name="dob" type="date" class="form-control"></div>
<div class="col-md-6"><label>Contact Number</label><input name="contact" class="form-control"></div>
<div class="col-md-6"><label>Home Address</label><input name="home_address" class="form-control"></div>
<div class="col-md-6"><label>Guardian Full Name</label><input name="guardian_name" class="form-control"></div>
<div class="col-md-6"><label>Guardian Contact Number</label><input name="guardian_contact" class="form-control"></div>
<div class="col-12"><label>Guardian Address</label><input name="guardian_address" class="form-control"></div>
<div class="col-md-6">
    <label>Course</label>
    <select name="course" class="form-control" required>
        <option value="">Select Course</option>
        <?php foreach ($courses as $c): ?>
            <option value="<?= htmlspecialchars($c['course_name']) ?>"><?= htmlspecialchars($c['course_name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-md-6">
    <label>Year Level</label>
    <select name="year_level" class="form-control" required>
        <option value="">Select Year Level</option>
        <?php foreach ($years as $y): ?>
            <option value="<?= htmlspecialchars($y['year_name']) ?>"><?= htmlspecialchars($y['year_name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
</div>
</div>

<div class="form-card">
<h5 class="fw-bold">Data Privacy Notice</h5>
<p>Before you submit any personal information to our website, please take a moment to read this data privacy notice.</p>
<h6 class="fw-bold">What personal information do we collect?</h6>
<p>We may collect personal information such as your name, email address, phone number, and educational history.</p>
<h6 class="fw-bold">How do we use your personal information?</h6>
<p>We use your information to provide services you request and improve system operations.</p>
<h6 class="fw-bold">Do we share your personal information?</h6>
<p>No information is sold or transferred unless required by law.</p>
<h6 class="fw-bold">How do we protect your data?</h6>
<p>We use encryption and security measures to ensure protection.</p>
<h6 class="fw-bold">Your rights</h6>
<p>You may request access, correction or deletion of your data anytime.</p>
<h6 class="fw-bold">Contact Us</h6>
<p>If you have any questions or concerns about our data privacy practices, please contact us by clicking this <a href="contact_now.php">link</a>.</p>
<div class="form-check mt-2">
    <input class="form-check-input" type="checkbox" id="agree" required>
    <label class="form-check-label" for="agree">I have read and agree to the Data Privacy Notice</label>
</div>
</div>

<button class="btn btn-primary mb-5">Submit</button>
</form>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="enrollToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">Enrollment submitted successfully!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<div class="modal fade" id="loginModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-4">
<div class="text-center mb-3">
<div style="width:80px;height:80px;background:var(--primary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;">
<i class="bi bi-person-fill text-white fs-2"></i>
</div>
</div>
<form method="POST" action="login.php">
<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
<input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($showToast): ?>
  const toastEl = document.getElementById('enrollToast');
  const toast = new bootstrap.Toast(toastEl);
  toast.show();
<?php endif; ?>
</script>

</body>
</html>
