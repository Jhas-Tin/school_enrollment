<?php 
session_start(); 
$siteName = "Smart School System";
require_once "Admin/db.php";
$schoolName = "Smart School System";
$stmtSchool = $conn->prepare("SELECT name FROM school_profile WHERE id = 1");
$stmtSchool->execute();
$result = $stmtSchool->fetch(PDO::FETCH_ASSOC);
if ($result && !empty($result['name'])) {
    $schoolName = $result['name'];
}

$stmt = $conn->prepare("SELECT * FROM school_profile WHERE id = 1 LIMIT 1");
$stmt->execute();
$school = $stmt->fetch(PDO::FETCH_ASSOC);

$captcha = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$_SESSION['captcha_code'] = $captcha;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $siteName ?> - Contact Us</title>
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
.navbar-toggler {
    border: none;
}
.navbar-toggler-icon {
    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,0.8)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

.contact-container {
    display:flex;
    flex-wrap:wrap;
    max-width:1000px;
    margin:50px auto;
    background: var(--section-bg);
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 4px 20px rgba(0,0,0,0.5);
}
.contact-form, .contact-info {
    flex:1 1 400px;
    padding:30px;
}
.contact-form h2 {
    margin-bottom:20px;
    color:var(--primary-color);
    font-weight:700;
}
.contact-form .form-control, .contact-form textarea {
    border-radius:12px;
    background:var(--input-bg);
    color: var(--text-light);
    border:none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.5);
}
.contact-form .form-control::placeholder {
    color:#bbb;
}
.contact-form .form-control:focus, .contact-form textarea:focus {
    border:2px solid var(--primary-color);
    box-shadow:0 4px 15px rgba(111,66,193,0.5);
}
.contact-form button {
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
.contact-form button:hover {
    background: var(--button-hover);
    box-shadow:0 6px 20px rgba(111,66,193,0.6);
}
.contact-info {
    background:linear-gradient(135deg,var(--primary-dark),var(--primary-color));
    color:#fff;
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-width:280px;
}
.contact-info h3 {
    margin-bottom:20px;
    font-weight:700;
}
.contact-info p {
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
    font-weight:500;
}
.alert {
    border-radius:12px;
    font-weight:500;
}
.modal-content {
    border-radius:20px;
    padding:40px 30px;
    border:none;
    background: var(--section-bg);
    box-shadow:0 8px 25px rgba(0,0,0,0.5);
}
.modal .bi-person-fill {
    font-size:2.5rem;
}
.modal input {
    border-radius:12px;
    padding:12px;
    background: var(--input-bg);
    color: var(--text-light);
    border:none;
    box-shadow:0 2px 10px rgba(0,0,0,0.5);
    transition:0.3s;
}
.modal input::placeholder {
    color:#bbb;
}
.modal input:focus {
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
@media (max-width: 768px){
    .contact-container { flex-direction:column; margin:30px 15px; }
    .contact-info { padding:30px 20px; }
    .contact-form { padding:30px 20px; margin-bottom:20px; }
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
<a class="navbar-brand"><?= htmlspecialchars($schoolName) ?></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
  <span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse justify-content-between" id="navbarNav">
<ul class="navbar-nav mb-2 mb-lg-0">
<li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
<li class="nav-item"><a class="nav-link active" href="contact_now.php">Contact</a></li>
<li class="nav-item"><a class="nav-link" href="enroll.php">Enroll</a></li>
</ul>
<button id="navbarLoginBtn" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
</div>
</nav>

<div class="contact-container">
<div class="contact-form">
<h2>Contact Us</h2>
<?php if (!empty($_SESSION['contact_error'])): ?>
<div class="alert alert-danger auto-hide"><?= $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['contact_success'])): ?>
<div class="alert alert-success auto-hide">Message sent successfully!</div>
<?php unset($_SESSION['contact_success']); endif; ?>

<form action="send_contact.php" method="POST">
<input class="form-control mb-3" name="name" placeholder="Your Name" required>
<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
<textarea class="form-control mb-3" name="message" rows="5" placeholder="Message" required></textarea>

<div class="d-flex align-items-center gap-2 mb-3">
<strong class="fs-5 text-primary captcha-text"><?= $_SESSION['captcha_code'] ?></strong>
<input class="form-control" name="captcha_code" placeholder="Enter Captcha" required>
</div>
<button type="submit">Send</button>
</form>
</div>

<div class="contact-info" style="<?php if (!empty($school['logo']) && file_exists('uploads/'.$school['logo'])): ?> background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('uploads/<?= htmlspecialchars($school['logo']) ?>') center/cover no-repeat;
<?php else: ?>background: linear-gradient(135deg,#563d7c,#6f42c1,#8e44ad);<?php endif; ?> olor:#fff; display:flex; flex-direction:column; justify-content:center; min-width:280px; padding:30px;">
<h3>Contact Info</h3>
<p><i class="bi bi-building"></i><?= htmlspecialchars($school['name']) ?></p>
<p><i class="bi bi-geo-alt"></i><?= htmlspecialchars($school['location']) ?></p>
<p><i class="bi bi-envelope"></i><?= htmlspecialchars($school['email']) ?></p>
<p><i class="bi bi-phone"></i><?= htmlspecialchars($school['mobile']) ?></p>
<p><i class="bi bi-telephone"></i><?= htmlspecialchars($school['telephone']) ?></p>
</div>

</div>

<div class="modal fade" id="loginModal">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-4">
<div class="text-center mb-3">
<div style="width:80px;height:80px;background:var(--primary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto">
<i class="bi bi-person-fill text-white fs-2"></i>
</div>
</div>
<form action="login.php" method="POST">
<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
<input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
setTimeout(() => {
    document.querySelectorAll('.auto-hide').forEach(alert => {
        alert.classList.add('fade');
        setTimeout(() => alert.remove(), 500);
    });
}, 3000);
</script>

<script>
    document.querySelectorAll('.captcha-text').forEach(el => {
        el.style.userSelect = 'none';
        el.addEventListener('copy', e => e.preventDefault());
    });
    setTimeout(() => {
        document.querySelectorAll('.auto-hide').forEach(alert => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 500);
        });
    }, 3000);
</script>

</body>
</html>
