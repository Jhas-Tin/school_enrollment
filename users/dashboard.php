    <?php
    session_start();
    require "../db.php";

    if (!isset($_SESSION['student_id'])) {
        header("Location: ../index.php");
        exit();
    }

    $student_id = $_SESSION['student_id'];

    $stmt = $conn->prepare("SELECT last_name, first_name, middle_name FROM enrollment_form WHERE student_id = ? LIMIT 1");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($last_name, $first_name, $middle_name);
    $stmt->fetch();
    $stmt->close();

    $full_name = htmlspecialchars($last_name . ", " . $first_name . " " . $middle_name);

    $schoolName = "Smart School System";
    $stmt = $conn->prepare("SELECT name FROM school_profile WHERE id = 1");
    $stmt->execute();
    $stmt->bind_result($dbSchoolName);
    if ($stmt->fetch() && !empty($dbSchoolName)) {
        $schoolName = $dbSchoolName;
    }
    $stmt->close();
     $school = [
        'name' => 'Smart School System',
        'logo' => ''
    ];

    $stmt = $conn->prepare("SELECT name, logo FROM school_profile WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($dbSchoolName, $dbLogo);
    if ($stmt->fetch()) {
        if (!empty($dbSchoolName)) $school['name'] = $dbSchoolName;
        if (!empty($dbLogo)) $school['logo'] = $dbLogo;
    }
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($schoolName) ?> - Student Dashboard</title>
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
        .welcome-card{
            border:none;
            border-radius:20px;
            box-shadow:0 10px 30px rgba(0,0,0,.05);
            padding:50px 30px;
            text-align:center;
            background:linear-gradient(145deg,#ffffff,#e0f2fe);
            transition:0.3s;
        }
        .welcome-card h3{
            font-weight:700;
            font-size:2rem;
            color:#0369a1;
        }
        .dashboard-display{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
            margin-top:40px;
        }
        .display-card{
            background:linear-gradient(135deg,#ffffff,#e0f2fe);
            border-radius:20px;
            padding:25px 15px;
            text-align:center;
            box-shadow:0 8px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .display-card:hover{
            transform:translateY(-5px);
            box-shadow:0 15px 40px rgba(0,0,0,0.12);
        }
        .display-icon{
            font-size:2.5rem;
            color:#0ea5e9;
            margin-bottom:12px;
        }
        .display-card h5{
            font-weight:600;
            margin-bottom:5px;
            color:#1e293b;
            font-size:1.1rem;
        }
        .display-card p{
            font-size:0.9rem;
            color:#64748b;
            margin:0;
        }
        .modal-content{
            border-radius:20px;
        }
        .modal-header{
            background:#0ea5e9;
            color:#fff;
            border-bottom:none;
            border-radius:20px 20px 0 0;
        }
        .modal-body{
            text-align:center;
            font-size:1rem;
            color:#1e293b;
        }
        .modal-footer{
            border-top:none;
            justify-content:center;
        }
        .modal-footer .btn-danger{
            border-radius:50px;
            padding:10px 25px;
        }
        .modal-footer .btn-secondary{
            border-radius:50px;
            padding:10px 25px;
        }
        footer{
            text-align:center;
            padding:20px;
            margin-top:50px;
            color:#64748b;
            font-size:0.9rem;
        }
        @media (max-width:768px){
            .welcome-card{
                padding:35px 20px;
            }
            .display-card{
                padding:20px 15px;
            }
            .display-icon{
                font-size:2.2rem;
                margin-bottom:10px;
            }
            .display-card h5{
                font-size:1rem;
            }
            .display-card p{
                font-size:0.85rem;
            }
        }
        @media (max-width:480px){
            .dashboard-display{
                grid-template-columns:1fr;
                gap:15px;
                margin-top:25px;
            }
            .display-card{
                padding:15px 10px;
            }
            .display-icon{
                font-size:2rem;
                margin-bottom:8px;
            }
            .display-card h5{
                font-size:0.95rem;
            }
            .display-card p{
                font-size:0.8rem;
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
        body.dark-mode .welcome-card{
            background:#1e293b;
            color:#e5e7eb;
        }
        body.dark-mode .display-card{
            background:#1e293b;
            color:#e5e7eb;
        }
        body.dark-mode .display-icon{
            color:#38bdf8;
        }
        body.dark-mode .display-card p{
            color:#cbd5e1;
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
        body.dark-mode .modal-body h5,
        body.dark-mode .modal-body p{
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
        body.dark-mode footer{
            color:#cbd5e1;
        }
        .modal-icon-top{
            background:#dc2626;
            color:#ffffff;
            padding:1.5rem 0;
            font-size:2.5rem;
        }
        body.dark-mode .modal-icon-top{
            background:#ef4444;
            color:#ffffff;
        }
    </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <?php if (!empty($school['logo']) && file_exists('../uploads/'.$school['logo'])): ?>
                <img src="../uploads/<?= htmlspecialchars($school['logo']) ?>" 
                    alt="School Logo" 
                    style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #1e3a8a;">
            <?php endif; ?>
            <span><?= htmlspecialchars($school['name']) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between" id="navMenu">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-house"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="egrades.php"><i class="bi bi-journal-text"></i> E-Grades</a></li>
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
        <div class="welcome-card">
            <h3>Welcome, <?= $full_name ?>!</h3>
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

    <footer>&copy; <?= date("Y") ?> <?= htmlspecialchars($schoolName) ?>. All rights reserved.</footer>

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
