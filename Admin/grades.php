<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}
include "db.php";

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) die("Invalid student");

$profile = $conn->query("SELECT * FROM school_profile WHERE id=1")->fetch(PDO::FETCH_ASSOC);

$studentStmt = $conn->prepare("
    SELECT student_id,last_name,first_name,middle_name,course,year_level,section
    FROM enrollment_form
    WHERE student_id=?
");
$studentStmt->execute([$student_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);
if (!$student) die("Student not found");

$subjects = $conn->prepare("
    SELECT * FROM subjects
    WHERE course=? AND year_level=?
    ORDER BY subject_name
");
$subjects->execute([$student['course'], $student['year_level']]);

$gradeStmt = $conn->prepare("SELECT * FROM grades WHERE student_id=?");
$gradeStmt->execute([$student_id]);
$grades = [];
while ($g = $gradeStmt->fetch(PDO::FETCH_ASSOC)) {
    $grades[$g['subject']] = $g; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Grades</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { 
    background:#f4f6fb; 
    font-family: system-ui, sans-serif; 
}

.sidebar {
    width:90px; 
    height:100vh; 
    background:#0f172a; 
    position:fixed;
    display:flex; 
    flex-direction:column; 
    align-items:center; 
    padding-top:20px; 
    z-index:1000;
}
.sidebar img {
    width:56px; 
    height:56px; 
    border-radius:50%; 
    object-fit:cover;
    border:3px solid #6366f1; 
    margin-bottom:25px;
}
.top-icons { 
    display:flex; 
    flex-direction:column; 
    gap:26px; 
}
.sidebar a i { 
    font-size:26px; 
    color:#c7d2fe; 
    transition:.3s; 
}
.sidebar a.active i, .sidebar a:hover i { 
    color:#6366f1; 
    transform:scale(1.15); 
}
.spacer { 
    flex-grow:1; 
}
.logout i { 
    color:#ef4444; 
    font-size:26px; 
}

.content { 
    margin-left:110px; 
    padding:30px; 
    max-width:100%;
}
.card { 
    border-radius:18px; 
    border:none; 
    box-shadow:0 15px 35px rgba(0,0,0,.08); 
}
.table tbody tr:hover { 
    background:#e8f0fe; 
}
input[type=number] { 
    width:80px; 
    text-align:center; 
    border-radius:6px; 
}
.btn-save { 
    background:#0d6efd; 
    color:#fff; 
    border-radius:6px; 
    padding:5px 12px; 
}
.logout-icon {
    width:80px; 
    height:80px; 
    border-radius:50%;
    background:#fee2e2; 
    display:flex; 
    align-items:center; 
    justify-content:center;
    margin:0 auto 15px;
}
.logout-icon i { 
    font-size:40px; color:#dc2626; 
}
.modal-content { 
    border-radius:22px; 
    border:none; 
    box-shadow:0 25px 60px rgba(0,0,0,.25); 
}

.table-responsive {
    overflow-x:auto;
}

@media(max-width:768px){
    .sidebar { 
        width:100%; 
        height:70px; 
        flex-direction:row; 
        bottom:0; top:auto; 
        padding:0 10px; 
        justify-content:space-between; 
        align-items:center; 
    }
    .sidebar img{ margin-bottom:0; }
    .top-icons{ flex-direction:row; gap:15px; align-items:center; }
    .spacer{ display:none; }
    .content{ margin-left:0; margin-bottom:90px; padding:15px; }
}

@media (max-width: 350px) {
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }

    thead { display: none; }

    tbody tr {
        background: #fff;
        margin-bottom: 12px;
        padding: 12px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }

    tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        border: none;
        font-size: 13px;
        flex-wrap: wrap;
        white-space: normal;
    }

    tbody td label {
        font-weight: 600;
        color: #475569;
        margin-right: 6px;
        flex: 0 0 40%; 
    }

    tbody td span, tbody td input, tbody td button {
        flex: 1 1 55%; 
        margin-left: auto;
    }

    tbody td.subject::before   { content: "Subject"; }
    tbody td.instructor::before{ content: "Instructor"; }
    tbody td.prelim::before    { content: "Prelim"; }
    tbody td.midterm::before   { content: "Midterm"; }
    tbody td.finals::before    { content: "Finals"; }
    tbody td.average::before   { content: "Average"; }
    tbody td.remarks::before   { content: "Remarks"; }

    tbody td::before {
        display: inline-block;
        font-weight: 600;
        color: #475569;
        margin-right: 6px;
        width: 40%;
    }

    tbody td input {
        width: 100%;
    }

    tbody td button.btn-save {
        width: 100%;
        margin-top: 4px;
    }

    .row > div { width: 100%; margin-bottom: 10px; }
    .form-select, .form-control { width: 100%; font-size: 13px; }
}

</style>
</head>
<body>
    <div class="sidebar">
        <?php if (!empty($profile['logo']) && file_exists("../uploads/".$profile['logo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($profile['logo']) ?>" alt="Logo">
        <?php else: ?>
            <img src="../uploads/default-logo.png" alt="Logo">
        <?php endif; ?>

        <div class="top-icons">
            <a href="dashboard.php"><i class="bi bi-grid-fill"></i></a>
            <a href="users.php"><i class="bi bi-people-fill"></i></a>
            <a href="enrollment.php"><i class="bi bi-file-earmark-fill"></i></a>
            <a href="classlist.php" class="active"><i class="bi bi-person-square"></i></a>
            <a href="messages.php"><i class="bi bi-envelope-fill"></i></a>
        </div>

        <div class="spacer"></div>
        <a href="#" class="logout" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i></a>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content p-4 text-center">
        <div class="logout-icon mb-3">
            <i class="bi bi-box-arrow-right"></i>
        </div>
        <h5 class="fw-bold mb-2">Logout Confirmation</h5>
        <p class="text-muted mb-4">Are you sure you want to logout from your account?</p>
        <div class="d-flex flex-column gap-2">
            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
            <a href="logout.php" class="btn btn-danger rounded-pill">Logout</a>
        </div>
        </div>
    </div>
    </div>

    <div class="content">
    <div class="card p-4">
        <h4 class="mb-3">E-Grades</h4>

        <div class="row mb-3">
            <div class="col-md-6"><strong>Name:</strong> <?= $student['last_name'].", ".$student['first_name']." ".$student['middle_name'] ?></div>
            <div class="col-md-3"><strong>Year:</strong> <?= $student['year_level'] ?></div>
            <div class="col-md-3"><strong>Section:</strong> <?= $student['section'] ?></div>
        </div>

        <div class="mb-3"><strong>Course:</strong> <?= $student['course'] ?></div>

        <form id="gradeForm">
            <input type="hidden" name="student_id" value="<?= $student_id ?>">
            <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>Instructor</th>
                        <th>Prelim</th>
                        <th>Midterm</th>
                        <th>Finals</th>
                        <th>Average</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($s = $subjects->fetch(PDO::FETCH_ASSOC)):
                    $subject = $s['subject_name'];
                    $g = $grades[$subject] ?? ['prelim'=>'','midterm'=>'','finals'=>'','average'=>'','remarks'=>''];
                ?>
                <tr>
                    <td class="subject"><?= $subject ?></td>
                    <td class="instructor"><?= $s['instructor'] ?></td>

                    <input type="hidden" name="subject[]" value="<?= $subject ?>">
                    <input type="hidden" name="instructor[<?= $subject ?>]" value="<?= $s['instructor'] ?>">

                    <td class="prelim"><input type="number" step="0.01" name="prelim[<?= $subject ?>]" value="<?= $g['prelim'] ?>"></td>
                    <td class="midterm"><input type="number" step="0.01" name="midterm[<?= $subject ?>]" value="<?= $g['midterm'] ?>"></td>
                    <td class="finals"><input type="number" step="0.01" name="finals[<?= $subject ?>]" value="<?= $g['finals'] ?>"></td>

                    <td class="average"><?= $g['average'] ?></td>
                    <td class="remarks"><?= $g['remarks'] ?></td>

                    <td class="action"><button type="button" class="btn btn-save btn-sm saveBtn">Save</button></td>
                </tr>

                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </form>
    </div>
    </div>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1100;">
        <div id="saveToast" class="toast align-items-center text-bg-success border-0 shadow-lg">
            <div class="d-flex">
                <div class="toast-body fw-semibold text-center w-100">
                    Grades saved successfully
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll(".saveBtn").forEach(btn=>{
    btn.addEventListener("click",()=>{
        fetch("save_grades.php",{
            method:"POST",
            body:new FormData(document.getElementById("gradeForm"))
        })
        .then(res => res.json())
        .then(data => {
            if(data.status==="success"){
                const toastEl = document.getElementById("saveToast");
                const toast = new bootstrap.Toast(toastEl,{delay:1500});
                toast.show();
                setTimeout(()=>{ location.reload(); },1600);
            }
        });
    });
});
</script>

</body>
</html>
