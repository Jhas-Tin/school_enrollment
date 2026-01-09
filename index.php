<?php 
session_start(); 
require "db.php";

// Default school info
$schoolName = "Smart School System";
$logoPath = "assets/default-logo.png";

// Fetch school name and logo from database
$stmt = $conn->prepare("SELECT name, logo FROM school_profile WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();
if ($school = $result->fetch_assoc()) {
    if (!empty($school['name'])) {
        $schoolName = $school['name'];
    }
    if (!empty($school['logo']) && file_exists("uploads/" . $school['logo'])) {
        $logoPath = "uploads/" . $school['logo'];
    }
}
$stmt->close();
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($schoolName); ?></title>
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
        --button-gradient: linear-gradient(135deg, #6f42c1, #8e44ad);
        --button-hover: linear-gradient(135deg, #8e44ad, #6f42c1);
    }
    body {
        font-family: 'Segoe UI', system-ui, sans-serif;
        background: var(--bg-dark);
        margin: 0;
        color: var(--text-light);
    }
    .navbar {
        background: #22223b;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        padding: 1rem 2rem;
        border-radius: 0 0 20px 20px;
    }
    .navbar-brand {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.5rem;
    }
    .navbar-nav .nav-link {
        color: #ccc;
        font-weight: 500;
        margin-right: 20px;
        transition: 0.3s;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
        color: var(--primary-color);
    }
    
    .modal-content {
        border-radius: 20px;
        padding: 40px 30px;
        border: none;
        background: var(--section-bg);
        box-shadow: 0 8px 25px rgba(0,0,0,0.5);
    }
    .modal .bi-person-fill {
        font-size: 2.5rem;
    }
    .modal input {
        border-radius: 12px;
        padding: 12px;
        background: var(--input-bg);
        color: var(--text-light);
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        transition: 0.3s;
    }
    .modal input::placeholder {
        color: #bbb;
    }
    .modal input:focus {
        border: 2px solid var(--primary-color);
        box-shadow: 0 4px 15px rgba(111,66,193,0.5);
    }
    .modal button {
        border-radius: 50px;
        padding: 12px 0;
        background: var(--button-gradient);
        border: none;
        transition: 0.4s;
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(111,66,193,0.4);
    }
    .modal button:hover {
        background: var(--button-hover);
        box-shadow: 0 6px 20px rgba(111,66,193,0.6);
    }
    #navbarLoginBtn {
        border-radius: 50px;
        padding: 8px 20px;
        font-weight: 600;
        background: var(--button-gradient);
        border: none;
        color: #fff;
        transition: 0.4s;
        box-shadow: 0 4px 15px rgba(111,66,193,0.4);
    }
    #navbarLoginBtn:hover {
        background: var(--button-hover);
        box-shadow: 0 6px 20px rgba(111,66,193,0.6);
    }
    #toastContainer {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1080;
    }
    .toast {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        font-weight: 500;
    }
    .toast-body {
        font-weight: 500;
        color: #fff;
        background: #b00020;
    }

    .hero {
        position: relative;
        width: 100%;
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        text-align: center;
    }

    .hero-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 0;
        object-fit: cover;   

        image-rendering: auto;
        filter: brightness(90%) contrast(110%) saturate(115%);
        transform: scale(1.05);   
        transition: transform 2s ease-out;
    }

    .hero:hover .hero-bg {
        transform: scale(1.1);  
    }


    .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1;
    }

    .hero-content {
        z-index: 2;
        color: #fff;
    }

    .hero-content h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }

    .btn-get {
        padding: 10px 28px;
        border: 2px solid #fff;
        background: transparent;
        color: #fff;
        border-radius: 30px;
        transition: 0.3s;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-get:hover {
        background: white;
        color: black;
    }

    @media(max-width:768px){
        .hero { height: 50vh; }
        .hero-content h1 { font-size: 2rem; }
    }

    @media(max-width:480px){
        .hero { height: 40vh; }
        .hero-content h1 { font-size: 1.6rem; }
    }

    section.hero {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        color: var(--text-light);
        padding: 100px 20px;
        text-align: center;
        border-radius: 0 0 50% 50% / 15%;
        box-shadow: inset 0 0 50px rgba(0,0,0,0.3);
    }
    section.hero h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    section.hero p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        opacity: 0.9;
    }
    section.hero button {
        border: 2px solid var(--text-light);
        color: var(--text-light);
        background: transparent;
        padding: 12px 30px;
        font-size: 1rem;
        border-radius: 50px;
        font-weight: 600;
        transition: 0.4s;
    }
    section.hero button:hover {
        background: var(--text-light);
        color: var(--primary-dark);
    }

    </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
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

            <!-- <span><?= htmlspecialchars($schoolName) ?></span> -->

        </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="contact_now.php">Contact Us</a></li>
        <li class="nav-item"><a class="nav-link" href="enroll.php">Enroll</a></li>
        </ul>
        <button id="navbarLoginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
    </div>
    </nav>
    <section class="hero">
            <?php if (!empty($school['logo']) && file_exists("uploads/".$school['logo'])): ?>
                <img class="hero-bg"
                    src="uploads/<?= htmlspecialchars($school['logo']) ?>"
                    alt="School Background">
                <div class="overlay"></div>
            <?php else: ?>
                <div style="
                    position:absolute;
                    inset:0;
                    background:linear-gradient(135deg,#563d7c,#6f42c1,#8e44ad);
                    z-index:0;
                "></div>
            <?php endif; ?>
            <div class="hero-content">
                <?php if (empty($school['logo']) || !file_exists("uploads/".$school['logo'])): ?>
                    <h1>Welcome to <?= htmlspecialchars($schoolName) ?></h1>
                    <p style="font-size:1.2rem;opacity:0.95;max-width:600px;margin:auto;">
                        Your school portal is almost ready.  
                        Upload your school logo to personalize this system.
                    </p>
                    <div style="
                        margin-top:25px;
                        padding:18px 28px;
                        background:rgba(0,0,0,0.3);
                        border-radius:16px;
                        display:inline-block;
                        backdrop-filter:blur(8px);
                        box-shadow:0 0 20px rgba(0,0,0,0.4);
                    ">
                        <strong>Admin:</strong> Go to <em>School Profile</em> to upload your logo.
                    </div>
                <?php else: ?>
                    <h1>Welcome to <?= htmlspecialchars($schoolName) ?></h1>
                <?php endif; ?>
                <button class="btn-get mt-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Get Started
                </button>
            </div>
        </section>

    <div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
        <div class="text-center mb-4">
            <div style="width:90px;height:90px;background:var(--primary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;">
                <i class="bi bi-person-fill text-white"></i>
            </div>
        </div>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <input type="text" name="login_id" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn w-100">Login</button>
        </form>
        </div>
    </div>
    </div>
    <div id="toastContainer">
    <?php
    if (isset($_SESSION['login_error'])) {
        $msg = $_SESSION['login_error'];
        echo '
        <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">' . htmlspecialchars($msg) . '</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        </div>';
        unset($_SESSION['login_error']);
    }
    ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var toastElList = [].slice.call(document.querySelectorAll('.toast'))
    toastElList.map(function(toastEl) {
    return new bootstrap.Toast(toastEl, { delay: 5000 }).show()
    });
    </script>
    </body>
    </html>
