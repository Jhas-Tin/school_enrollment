<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

include "db.php";

$stmt = $conn->prepare("SELECT * FROM school_profile WHERE id = 1");
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$data) die("School profile not found.");

$course_stmt = $conn->prepare("SELECT * FROM courses ORDER BY course_name");
$course_stmt->execute();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

$year_stmt = $conn->prepare("SELECT * FROM year_levels ORDER BY id");
$year_stmt->execute();
$years = $year_stmt->fetchAll(PDO::FETCH_ASSOC);

$sections = ['A','B','C','D'];

$filterCourse  = $_GET['course']  ?? '';
$filterYear    = $_GET['year']    ?? '';
$filterSection = $_GET['section'] ?? '';

$sql = "
    SELECT 
        ef.student_id,
        sp.username,
        ef.last_name,
        ef.first_name,
        ef.middle_name,
        ef.course,
        ef.year_level,
        ef.section
    FROM enrollment_form ef
    LEFT JOIN studentportal sp ON ef.student_id = sp.id
    WHERE ef.status = 'Enrolled'
";

$params = [];
if ($filterCourse !== '') { $sql .= " AND ef.course = :course"; $params[':course'] = $filterCourse; }
if ($filterYear !== '') { $sql .= " AND ef.year_level = :year"; $params[':year'] = $filterYear; }
if ($filterSection !== '') { $sql .= " AND ef.section = :section"; $params[':section'] = $filterSection; }

$sql .= " ORDER BY ef.last_name ASC";

$classlist = $conn->prepare($sql);
$classlist->execute($params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Class List</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body {
        font-family: system-ui, sans-serif;
        background: #f4f6fb;
        margin: 0;
    }

    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }

    .sidebar {
        width: 90px;
        height: 100vh;
        background: #0f172a;
        position: fixed;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 20px;
        z-index: 1000;
    }

    .sidebar img {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #6366f1;
        margin-bottom: 25px;
    }

    .top-icons {
        display: flex;
        flex-direction: column;
        gap: 26px;
    }

    .sidebar a i {
        font-size: 26px;
        color: #c7d2fe;
        transition: 0.3s;
    }

    .sidebar a.active i,
    .sidebar a:hover i {
        color: #6366f1;
        transform: scale(1.15);
    }

    .spacer { flex-grow: 1; }

    .logout-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #fee2e2;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    .logout-icon i {
        font-size: 40px;
        color: #dc2626;
    }

    .logout i {
        color: #ef4444;
        font-size: 26px;
    }

    .content {
        margin-left: 110px;
        padding: 30px;
        min-height: 100vh;
        max-width: 100%;
    }

    .card {
        border-radius: 18px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    }

    .form-select, .form-control {
        border-radius: 14px;
        padding: 10px 12px;
    }

    .btn-primary {
        border-radius: 30px;
        padding: 10px 30px;
    }

    .table tbody tr:hover {
        background: #e8f0fe;
    }

    .btn-view {
        background: #6366f1;
        color: #fff;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.85rem;
    }

    .modal-content {
        border-radius: 22px;
        border: none;
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    }

    .modal-sm .modal-content {
        max-width: 320px;
    }

    .modal-body, .modal-footer {
        border: none;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #f1f3f5;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
    }

    .table td {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .table input,
    .table select {
        min-width: 80px;
    }

    .btn-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        padding: 0;
    }

    .table-responsive,
    table {
        max-width: 100%;
    }

    @media (max-width: 1024px){
        .section-card {
            padding: 20px;
        }
        .section-title {
            font-size: 22px;
        }
        .table th, .table td {
            font-size: 14px;
            padding: 6px 8px;
        }
        .btn-circle {
            width: 32px;
            height: 32px;
        }
    }

    @media (max-width:768px){
        .sidebar{
            width: 100%;
            height: 70px;
            flex-direction: row;
            bottom: 0;
            top: auto;
            padding: 0 10px;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar img { 
            margin-bottom: 0; 
        }
        .top-icons { 
            flex-direction: row; 
            gap: 15px; 
            align-items: center; 
        }
        .spacer { 
            display: none; 
        }
        .content { 
            margin-left: 0; 
            margin-bottom: 90px; 
            padding: 15px; 
        }
        .section-card { 
            padding: 15px; 
            margin-bottom: 20px; 
        }
        .section-title { 
            font-size: 20px; 
        }
        .table th, .table td { 
            font-size: 13px; 
            padding: 4px 6px; 
        }
        .btn-circle { 
            width: 28px; 
            height: 28px; 
        }
        .table { 
            display: block; 
            overflow-x: auto; 
            white-space: nowrap; 
        }
    }

    @media (max-width: 350px) {

    .sidebar {
        height: 64px;
        padding: 0 6px;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar img {
        width: 38px;
        height: 38px;
        border-width: 2px;
        margin-bottom: 0;
    }

    .sidebar a i {
        font-size: 22px;
    }

    .top-icons {
        flex-direction: row;
        gap: 10px;
        align-items: center;
    }

    .spacer {
        display: none;
    }

    .content {
        margin-left: 0;
        margin-bottom: 85px;
        padding: 10px;
    }

    h4 {
        font-size: 18px;
        text-align: center;
    }

    .btn {
        padding: 4px 12px;
        font-size: 13px;
    }

    /* TABLE RESPONSIVE */
    table,
    thead,
    tbody,
    th,
    td,
    tr {
        display: block;
        width: 100%;
    }

    thead {
        display: none;
    }

    tbody tr {
        background: #ffffff;
        margin-bottom: 14px;
        padding: 14px;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(15,23,42,0.12);
    }

    tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 13px;
        border: none;
        white-space: normal;
    }

    tbody td:nth-child(1)::before { content: "Name"; }
    tbody td:nth-child(2)::before { content: "Username"; }
    tbody td:nth-child(3)::before { content: "Course"; }
    tbody td:nth-child(4)::before { content: "Year"; }
    tbody td:nth-child(5)::before { content: "Section"; }
    tbody td:nth-child(6)::before { content: "Actions"; }

    tbody td::before {
        font-weight: 600;
        color: #475569;
        margin-right: 10px;
        text-align: left;
    }

    tbody td:last-child {
        justify-content: flex-end; 
    }

    tbody td:last-child .btn-view {
        margin-left: auto; 
    }

    form.row > div {
        width: 100%;
    }

    .form-select,
    .form-control {
        width: 100%;
        font-size: 13px;
    }
}


</style>
</head>
<body>

    <div class="sidebar">
        <?php if (!empty($data['logo']) && file_exists("../uploads/".$data['logo'])): ?>
            <img src="../uploads/<?= htmlspecialchars($data['logo']) ?>" alt="Logo">
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
        <div class="logout-icon mb-3"><i class="bi bi-box-arrow-right"></i></div>
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
            <h4 class="mb-4">Class List</h4>
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="course" class="form-select">
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= htmlspecialchars($c['course_name']) ?>" <?= $filterCourse==$c['course_name']?'selected':'' ?>>
                                <?= htmlspecialchars($c['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="year" class="form-select">
                        <option value="">Select Year</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= htmlspecialchars($y['year_name']) ?>" <?= $filterYear==$y['year_name']?'selected':'' ?>>
                                <?= htmlspecialchars($y['year_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="section" class="form-select">
                        <option value="">Select Section</option>
                        <?php foreach ($sections as $s): ?>
                            <option value="<?= $s ?>" <?= $filterSection==$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($classlist->rowCount()): ?>
                    <?php while ($row = $classlist->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['last_name'].", ".$row['first_name']." ".$row['middle_name']) ?></td>
                        <td><?= htmlspecialchars($row['username'] ?: $row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= htmlspecialchars($row['year_level']) ?></td>
                        <td><?= htmlspecialchars($row['section']) ?></td>
                        <td>
                            <a href="grades.php?student_id=<?= $row['student_id'] ?>" class="btn btn-view btn-sm">View Grades</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">End of Results.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
