<?php
session_start();
require "../db.php";

if (!isset($_SESSION['student_id'], $_SESSION['student_username'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$username   = $_SESSION['student_username'];

$schoolName = "Smart School System";
$stmt = $conn->prepare("SELECT name FROM school_profile WHERE id = 1");
$stmt->execute();
$stmt->bind_result($dbSchoolName);
if ($stmt->fetch() && !empty($dbSchoolName)) { $schoolName = $dbSchoolName; }
$stmt->close();

$stmt = $conn->prepare("
    SELECT last_name, first_name, middle_name, sex, dob, contact_number, home_address,
           guardian_name, guardian_contact, guardian_address, course, year_level, section
    FROM enrollment_form
    WHERE student_id = ? LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result(
    $last_name, $first_name, $middle_name, $sex, $dob, $contact_number, $home_address,
    $guardian_name, $guardian_contact, $guardian_address, $course, $year_level, $section
);
$stmt->fetch();
$stmt->close();

$full_name = htmlspecialchars($last_name . ", " . $first_name . " " . $middle_name);

$elem_school = $elem_year = $junior_school = $junior_year = $senior_school = $senior_year = "";
$stmt = $conn->prepare("
    SELECT elementary_school, elementary_year, junior_high_school, junior_high_year,
           senior_high_school, senior_high_year
    FROM educational_attainment
    WHERE student_id = ? LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($elem_school, $elem_year, $junior_school, $junior_year, $senior_school, $senior_year);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $stmt = $conn->prepare("SELECT password FROM studentportal WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();
    if (!$db_password) {
        $_SESSION['password_toast'] = ["msg"=>"Account not found.","type"=>"danger"];
    } elseif ($current_password !== $db_password) {
        $_SESSION['password_toast'] = ["msg"=>"Current password is incorrect.","type"=>"danger"];
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['password_toast'] = ["msg"=>"New passwords do not match.","type"=>"danger"];
    } else {
        $stmt = $conn->prepare("UPDATE studentportal SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $new_password, $username);
        $stmt->execute();
        $stmt->close();
        $_SESSION['password_toast'] = ["msg"=>"Password updated successfully.","type"=>"success"];
    }
    header("Location: profile.php");
    exit();
}

$password_toast = "";
if (isset($_SESSION['password_toast'])) {
    $password_toast = json_encode($_SESSION['password_toast']);
    unset($_SESSION['password_toast']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Profile</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { 
        background:#f1f5f9; 
        font-family:system-ui,sans-serif; 
        transition:.3s; 
    }
    .navbar { 
        background:#fff; 
        box-shadow:0 2px 10px 
        rgba(0,0,0,.08); 
    }
    .navbar a.nav-link, .navbar a.navbar-brand { 
        color:#000; 
        font-weight:500; 
    }
    .navbar a.nav-link:hover { 
        color:#0d6efd; 
    }
    .profile-card { 
        background:#fff; 
        border-radius:16px; 
        box-shadow:0 10px 30px rgba(0,0,0,.08); 
        padding:30px; 
        transition:.3s; 
    }
    .avatar { 
        width:110px; 
        height:110px; 
        border-radius:50%; 
        background:#e5e7eb; 
        display:flex; 
        align-items:center; 
        justify-content:center; 
        font-size:60px; 
        margin-bottom:15px; 
        transition:.3s; 
    }
    .avatar i { 
        color:#64748b; 
        transition:.3s; 
    }
    .label { 
        font-size:.85rem; 
        font-weight:600; 
        color:#64748b; 
    }
    .profile-card .btn-link { 
        color:#64748b; 
        text-align:left; 
        transition:.2s; 
        text-decoration:none; 
        background:none; 
    }
    .profile-card .btn-link:hover { 
        color:#0d6efd; 
        background:rgba(13,110,253,0.1); 
        border-radius:6px; 
    }
    .profile-card .btn-link.active { 
        color:#fff; 
        background-color:#0d6efd; 
        border-radius:6px; 
    }
    .profile-section input { 
        background:#f8fafc; 
        color:#000; 
        border:1px solid #ced4da; 
    }

    @media (max-width: 768px) {
        .profile-card {
            padding: 1.5rem 1rem !important;
        }

        .profile-section p,
        .profile-section h5 {
            margin-top: 1rem !important;
            margin-bottom: 1rem !important;
            font-size: 1rem;
        }

        .profile-section .form-control {
            width: 100%;
            margin-bottom: 1rem !important;
            padding: 0.625rem 0.75rem;
            font-size: 0.95rem;
        }

        .profile-section .row > [class*="col-"] {
            margin-bottom: 1rem;
        }

        .profile-section .text-end {
            text-align: center !important;
            margin-top: 1.5rem !important;
        }

        .profile-section .text-end button {
            width: 100%;
            padding: 0.6rem 0;
            font-size: 1rem;
        }

        .navbar-toggler-icon {
            color: grey;
            border-color: transparent;
        }
    } 

    @media(max-width:480px){ 
        .profile-card .btn-link { 
            font-size:.85rem; 
        } 
    }

    
    body.dark-mode .navbar-toggler {
        border-color: #fff;
    	border:none;
    	outline:none;
    }

    .navbar-toggler-icon{
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28130,130,130,1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }
    body.dark-mode .navbar-toggler-icon{
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28229,231,235,1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }

    body.dark-mode { 
        background:#0f172a; 
        color:#fff !important; 
    }
    body.dark-mode .navbar { 
        background:#1e293b !important; 
    }
    body.dark-mode .navbar a.nav-link, body.dark-mode .navbar a.navbar-brand { 
        color:#fff !important; 
    }
    body.dark-mode .navbar a.nav-link:hover { 
        color:#38bdf8 !important; 
    }
    body.dark-mode .profile-card { 
        background:#1e293b !important; 
        color:#fff; 
    }
    body.dark-mode .avatar { 
        background:#334155; 
    } 
    body.dark-mode .avatar i { 
        color:#fff; 
    }
    body.dark-mode .profile-card .btn-link { 
        color:#fff !important; 
    }
    body.dark-mode .profile-card .btn-link:hover { 
        color:#0d6efd !important; 
        background:rgba(13,110,253,0.1); 
    }
    body.dark-mode input.form-control { 
        background:#1e293b; 
        color:#fff; 
        border:1px solid #334155; 
    }
    body.dark-mode input::placeholder { color:#fff; }
    body.dark-mode textarea, body.dark-mode select { 
        background:#1e293b; 
        color:#fff; 
        border:1px solid #334155; 
    }
    body.dark-mode .dropdown-menu { 
        background:#1e293b; 
        color:#fff; 
        border-color:#334155; 
    }
    body.dark-mode .dropdown-menu .dropdown-item:hover { 
        background:rgba(13,110,253,0.2); 
        color:#0d6efd; 
    }

    body.dark-mode .dropdown-menu {
        background-color: #1e293b !important; 
        border-color: #334155 !important;
    }

    body.dark-mode .dropdown-menu .dropdown-item {
        color: #fff !important;      
    }

    body.dark-mode .dropdown-menu .dropdown-item i {
        color: #fff !important;  
    }

    body.dark-mode .dropdown-menu .dropdown-item:hover {
        background-color: rgba(13,110,253,0.2) !important; 
        color: #0d6efd !important;            
    }

    body.dark-mode .dropdown-divider {
        border-color: #475569 !important;  
    }

    body.dark-mode .modal-content {
        background:#1e293b; 
        color:#fff; 
        border-radius:20px; 
    }
    body.dark-mode .modal-header { 
        background:#3b82f6; 
        color:#fff; 
        border-bottom:none; 
        border-radius:20px 20px 0 0; 
    }
    body.dark-mode .modal-body { 
        color:#e5e7eb; 
        text-align:center; 
        font-size:1rem; 
    }
    body.dark-mode .modal-footer .btn-secondary { 
        background:#475569; 
        color:#f8fafc; 
        border-radius:50px; 
        padding:10px 25px; 
    }
    body.dark-mode .modal-footer .btn-danger { 
        background:#dc2626; 
        color:#f8fafc; 
        border-radius:50px; 
        padding:10px 25px; 
    }


    .modal-icon-top {
        background: #dc2626;   
        color: #ffffff;
        padding: 1.5rem 0;
        font-size: 2.5rem;
    }

    body.dark-mode .modal-icon-top {
        background: #ef4444; 
        color: #ffffff;
    }

    .modal-content {
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .modal-body {
        text-align: center;
        font-size: 1rem;
        color: #1e293b;  
    }

    body.dark-mode .modal-body {
        color: #e5e7eb; 
    }

    .modal-footer .btn-secondary {
        border-radius: 50px;
        padding: 10px 25px;
    }

    .modal-footer .btn-danger {
        border-radius: 50px;
        padding: 10px 25px;
    }
</style>
</head>
<body>

    <nav class="navbar navbar-expand-lg px-4">
        <a class="navbar-brand" href="dashboard.php"><?= htmlspecialchars($schoolName) ?></a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse justify-content-between" id="navMenu">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="egrades.php"><i class="bi bi-journal-text"></i> E-Grades</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="bi bi-person-circle"></i> <?= $full_name ?></a>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:220px">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-2 d-flex justify-content-between align-items-center"><span><i class="bi bi-moon-stars"></i> Dark Mode</span>
                                <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" id="darkToggle"></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> Logout</button></li>
                        </ul>
                    </li>
                </ul>
            </div>
    </nav>

    <div class="container my-5">
        <div class="profile-card mx-auto" style="max-width:1000px">
            <h3 class="mb-4">Basic Profile</h3>
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="avatar mx-auto"><i class="bi bi-person"></i></div>
                    <div class="d-grid gap-2 text-center">
                        <button class="btn btn-link btn-sm" onclick="showSection('personal')">Personal Information</button>
                        <button class="btn btn-link btn-sm" onclick="showSection('guardian')">Guardian Information</button>
                        <button class="btn btn-link btn-sm" onclick="showSection('education')">Educational Background</button>
                        <button class="btn btn-link btn-sm" onclick="showSection('password')">Change Password</button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div id="personalSection" class="profile-section">
                        <p class="mt-3 mb-3"><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="label">First Name</label><input class="form-control" value="<?= $first_name ?>" readonly></div>
                            <div class="col-md-4"><label class="label">Middle Name</label><input class="form-control" value="<?= $middle_name ?>" readonly></div>
                            <div class="col-md-4"><label class="label">Last Name</label><input class="form-control" value="<?= $last_name ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Sex</label><input class="form-control" value="<?= $sex ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Birthdate</label><input class="form-control" value="<?= $dob ?>" readonly></div>
                            <div class="col-12"><label class="label">Home Address</label><input class="form-control" value="<?= $home_address ?>" readonly></div>
                        </div>
                    </div>

                    <div id="guardianSection" class="profile-section d-none">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="label">Guardian Name</label><input class="form-control" value="<?= $guardian_name ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Guardian Contact</label><input class="form-control" value="<?= $guardian_contact ?>" readonly></div>
                            <div class="col-12"><label class="label">Guardian Address</label><input class="form-control" value="<?= $guardian_address ?>" readonly></div>
                        </div>
                    </div>
                    <div id="educationSection" class="profile-section d-none">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="label">Elementary School</label><input class="form-control" value="<?= htmlspecialchars($elem_school) ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Year Graduated</label><input class="form-control" value="<?= htmlspecialchars($elem_year) ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Junior High School</label><input class="form-control" value="<?= htmlspecialchars($junior_school) ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Year Graduated</label><input class="form-control" value="<?= htmlspecialchars($junior_year) ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Senior High School</label><input class="form-control" value="<?= htmlspecialchars($senior_school) ?>" readonly></div>
                            <div class="col-md-6"><label class="label">Year Graduated</label><input class="form-control" value="<?= htmlspecialchars($senior_year) ?>" readonly></div>
                        </div>
                    </div>
                    <div id="passwordSection" class="profile-section d-none">
                        <h5 class="mb-3">Change Password</h5>
                        <form method="POST">
                            <div class="mb-3"><label class="label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                            <div class="mb-3"><label class="label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
                            <div class="mb-3"><label class="label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                            <div class="text-end"><button type="submit" name="change_password" class="btn btn-primary">Change Password</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-icon-top text-center">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <div class="modal-body text-center px-4 py-4">
                <h5 class="fw-bold mb-3">Confirm Logout</h5>
                <p class="mb-0">Are you sure you want to logout?</p>
            </div>
            <div class="modal-footer justify-content-center gap-3 py-3">
                <button class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" class="btn btn-danger px-4 rounded-pill">Logout</a>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(section){
            document.querySelectorAll('.profile-section').forEach(el=>el.classList.add('d-none'));
            document.getElementById(section+'Section').classList.remove('d-none');
            document.querySelectorAll('.profile-card .btn-link').forEach(btn=>btn.classList.remove('active'));
            document.querySelector(`.profile-card button[onclick="showSection('${section}')"]`).classList.add('active');
        }
        showSection('personal');
        const darkToggle=document.getElementById("darkToggle");
                if(localStorage.getItem("darkMode")==="enabled"){document.body.classList.add("dark-mode"); darkToggle.checked=true;}
                darkToggle.addEventListener("change",()=>{document.body.classList.toggle("dark-mode"); localStorage.setItem("darkMode", document.body.classList.contains("dark-mode")?"enabled":"disabled");});
                <?php if(!empty($password_toast)) : ?>
        const toastData = <?= $password_toast ?>;
        const toastEl = document.createElement('div');
                toastEl.className = `toast align-items-center text-white border-0 position-fixed top-0 start-50 translate-middle-x mt-3 ${toastData.type==='success'?'bg-success':'bg-danger'}`;
                toastEl.innerHTML = `<div class="d-flex"><div class="toast-body">${toastData.msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
                document.body.appendChild(toastEl);
                new bootstrap.Toast(toastEl).show();
            <?php endif; ?>
    </script>
</body>
</html>