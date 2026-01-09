<?php
session_start();
require "../db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("
    SELECT last_name, first_name, middle_name, course, year_level, section 
    FROM enrollment_form 
    WHERE student_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($last, $first, $middle, $course, $year, $section);
$stmt->fetch();
$stmt->close();

$full_name = htmlspecialchars("$last, $first $middle");


$schoolName = "Smart School System";
$stmt = $conn->prepare("SELECT name FROM school_profile WHERE id = 1");
$stmt->execute();
$stmt->bind_result($dbSchoolName);
if ($stmt->fetch() && !empty($dbSchoolName)) {
    $schoolName = $dbSchoolName;
}
$stmt->close();

$grades = [];
$stmt = $conn->prepare("
    SELECT 
        s.subject_name AS subject,
        s.instructor,
        COALESCE(g.prelim, 0)  AS prelim,
        COALESCE(g.midterm, 0) AS midterm,
        COALESCE(g.finals, 0)  AS finals,
        COALESCE(g.average, 0) AS average,
        COALESCE(g.remarks, 'No Grade') AS remarks
    FROM subjects s
    LEFT JOIN grades g 
        ON g.subject = s.subject_name 
        AND g.student_id = ?
    WHERE s.course = ?
      AND s.year_level = ?
    ORDER BY s.subject_name ASC
");
$stmt->bind_param("iss", $student_id, $course, $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($schoolName) ?> - E-Grades</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f0f4f8;
    font-family:'Segoe UI', sans-serif;
    transition: all 0.3s ease;
}

.navbar{
    background:#ffffff;
    box-shadow:0 4px 25px rgba(0,0,0,.06);
    padding:0.8rem 2rem;
    border-radius:0 0 20px 20px;
}
.navbar-brand{
    font-weight:700;
    font-size:1.4rem;
    letter-spacing:1px;
    color:#1e3a8a;
}
.nav-link{
    font-weight:500;
    transition:0.3s;
    color:#1e293b;
}
.nav-link:hover{
    color:#0ea5e9;
}
.navbar-toggler{
    border:none;
    outline:none;
}
.navbar-toggler-icon{
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28130,130,130,1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}
body.dark-mode .navbar-toggler-icon{
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28229,231,235,1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

.egrades-card{
    border:none;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
    padding:35px 25px;
    max-width:1000px;
    margin:auto;
    background:linear-gradient(145deg,#ffffff,#e0f2fe);
    transition:0.3s;
}
.egrades-card h4{
    font-weight:700;
    color:#0369a1;
    margin-bottom:20px;
}
.egrades-card .badge{
    font-weight:500;
}

.table thead th{
    font-weight:600;
    font-size:.95rem;
}
.table tbody td{
    font-size:.9rem;
}

.modal-content{
    border-radius:20px;
}
.modal-icon-top{
    background:#dc2626;
    color:#fff;
    padding:1.5rem 0;
    font-size:2.5rem;
}
body.dark-mode .modal-icon-top{
    background:#ef4444;
}
.modal-body{
    text-align:center;
    font-size:1rem;
}
.modal-footer{
    justify-content:center;
}
.modal-footer .btn-secondary,
.modal-footer .btn-danger{
    border-radius:50px;
    padding:10px 25px;
}

@media (max-width:768px){
    .egrades-card{
        padding:25px 20px;
    }
}
@media (max-width:480px){
    .egrades-card{
        padding:20px 15px;
    }
}

body.dark-mode{
    background:#0f172a;
    color:#e5e7eb;
}
body.dark-mode .navbar{
    background:#1e293b !important;
}
body.dark-mode .navbar-brand,
body.dark-mode .nav-link,
body.dark-mode .dropdown-toggle{
    color:#e5e7eb !important;
}
body.dark-mode .nav-link:hover,
body.dark-mode .dropdown-toggle:hover{
    color:#38bdf8 !important;
}
body.dark-mode .egrades-card{
    background:#1e293b;
    color:#e5e7eb;
}
body.dark-mode .table,
body.dark-mode .table thead th,
body.dark-mode .table tbody td{
    background:#1e293b !important;
    border-color:#334155 !important;
    color:#e5e7eb;
}
body.dark-mode .dropdown-menu{
    background:#1e293b;
    color:#e5e7eb;
}
body.dark-mode .dropdown-item{
    color:#e5e7eb;
}
body.dark-mode .dropdown-item:hover{
    background:#334155;
}
body.dark-mode .modal-content{
    background:#1e293b;
}
body.dark-mode .modal-header{
    background:#3b82f6;
    color:#fff;
}
body.dark-mode .modal-body{
    color:#e5e7eb;
}
body.dark-mode .modal-footer .btn-secondary{
    background:#475569 !important;
    color:#f8fafc !important;
}
body.dark-mode .modal-footer .btn-danger{
    background:#dc2626 !important;
    color:#f8fafc !important;
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="dashboard.php"><?= htmlspecialchars($schoolName) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navMenu">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link active" href="egrades.php"><i class="bi bi-journal-text"></i> E-Grades</a></li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#"><i class="bi bi-person-circle"></i> <?= $full_name ?></a>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:220px">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-moon-stars"></i> Dark Mode</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="darkToggle">
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> Logout</button></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <div class="egrades-card">
        <div class="d-flex justify-content-between mb-3">
            <h4>E-Grades</h4>
            <span class="badge bg-success">Enrolled</span>
        </div>

        <div class="row mb-3 small">
            <div class="col-md-6">
                <div><strong>Name:</strong> <?= $full_name ?></div>
                <div><strong>Course:</strong> <?= htmlspecialchars($course) ?></div>
            </div>
            <div class="col-md-6">
                <div><strong>Year:</strong> <?= htmlspecialchars($year) ?></div>
                <div><strong>Section:</strong> <?= htmlspecialchars($section) ?></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm text-center align-middle">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Instructor</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Finals</th>
                        <th>Average</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($grades): ?>
                    <?php foreach ($grades as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['subject']) ?></td>
                        <td><?= htmlspecialchars($g['instructor'] ?? 'TBA') ?></td>
                        <td><?= $g['prelim'] ?></td>
                        <td><?= $g['midterm'] ?></td>
                        <td><?= $g['finals'] ?></td>
                        <td><?= $g['average'] ?></td>
                        <td><?= htmlspecialchars($g['remarks']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No subjects found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-icon-top text-center"><i class="bi bi-box-arrow-right"></i></div>
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
const darkToggle = document.getElementById("darkToggle");
if(localStorage.getItem("darkMode")==="enabled"){
    document.body.classList.add("dark-mode");
    darkToggle.checked = true;
}
darkToggle.addEventListener("change", ()=>{
    if(darkToggle.checked){
        document.body.classList.add("dark-mode");
        localStorage.setItem("darkMode","enabled");
    }else{
        document.body.classList.remove("dark-mode");
        localStorage.setItem("darkMode","disabled");
    }
});
</script>
</body>
</html>
