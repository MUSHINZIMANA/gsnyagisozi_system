<?php
session_start();
require_once 'config.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search_query = trim($_GET['search'] ?? '');

$student_to_edit = null;
$staff_to_edit = null;
$book_to_edit = null;

// Edit records
if (isset($_GET['edit_student'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
    $stmt->execute([(int)$_GET['edit_student']]);
    $student_to_edit = $stmt->fetch();
}
if (isset($_GET['edit_staff'])) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE id=?");
    $stmt->execute([(int)$_GET['edit_staff']]);
    $staff_to_edit = $stmt->fetch();
}
if (isset($_GET['edit_book'])) {
    $stmt = $pdo->prepare("SELECT * FROM stock_books WHERE id=?");
    $stmt->execute([(int)$_GET['edit_book']]);
    $book_to_edit = $stmt->fetch();
}

// Login
if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user['username'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else $login_error = 'Username cyangwa password siyo!';
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['user'])) {
?>
<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GS Nyagisozi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-box{background:#fff;padding:50px;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-width:400px;width:90%;text-align:center;}
        input{width:100%;padding:15px;margin:10px 0;border-radius:12px;border:2px solid #e2e8f0;font-size:16px;}
        button{width:100%;background:#667eea;color:white;border:none;padding:16px;border-radius:12px;font-weight:700;cursor:pointer;font-size:16px;}
        button:hover{background:#5564c1;}
        .error{background:#fee2e2;color:#dc2626;padding:12px;border-radius:8px;margin:10px 0;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fas fa-school"></i> GS Nyagisozi</h2>
        <?php if(isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
    </div>
</body>
</html>
<?php exit; }

// CRUD OPERATIONS - COMPLETE
$message = '';
if (isset($_POST['add_student'])) {
    $stmt = $pdo->prepare("INSERT INTO students (fname, district, section, year, created_at) VALUES (?, ?, ?, ?, NOW())");
    $message = $stmt->execute([$_POST['fname'], $_POST['district'], $_POST['section'], $_POST['year'] ?? null]) 
        ? '<div class="success-php">✅ Umunyeshuri yongerwewe!</div>' : '<div class="error-php">❌ Error!</div>';
}
if (isset($_POST['update_student'])) {
    $stmt = $pdo->prepare("UPDATE students SET fname=?, district=?, section=?, year=? WHERE id=?");
    $message = $stmt->execute([$_POST['fname'], $_POST['district'], $_POST['section'], $_POST['year'] ?? null, $_POST['student_id']])
        ? '<div class="success-php">✅ Umunyeshuri yavuguruwe!</div>' : '<div class="error-php">❌ Error!</div>';
}
if (isset($_POST['delete_student'])) {
    $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$_POST['student_id']]);
    $message = '<div class="success-php">✅ Umunyeshuri yasibwe!</div>';
}

if (isset($_POST['add_staff'])) {
    $stmt = $pdo->prepare("INSERT INTO staff (name, email, phone, role, salary, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
    $message = $stmt->execute([$_POST['name'], $_POST['email'] ?? null, $_POST['phone'] ?? null, $_POST['role'], $_POST['salary'] ?? 0])
        ? '<div class="success-php">✅ Umukozi yongerwewe!</div>' : '<div class="error-php">❌ Error!</div>';
}
if (isset($_POST['update_staff'])) {
    $stmt = $pdo->prepare("UPDATE staff SET name=?, email=?, phone=?, role=?, salary=?, status=? WHERE id=?");
    $message = $stmt->execute([$_POST['name'], $_POST['email'] ?? null, $_POST['phone'] ?? null, $_POST['role'], $_POST['salary'] ?? 0, $_POST['status'], $_POST['staff_id']])
        ? '<div class="success-php">✅ Umukozi yavuguruwe!</div>' : '<div class="error-php">❌ Error!</div>';
}
if (isset($_POST['delete_staff'])) {
    $pdo->prepare("DELETE FROM staff WHERE id=?")->execute([$_POST['staff_id']]);
    $message = '<div class="success-php">✅ Umukozi yasibwe!</div>';
}

// Stock Library Operations
if (isset($_POST['add_stock_book'])) {
    $stmt = $pdo->prepare("INSERT INTO stock_books (title, author, isbn, category, total_stock, current_stock, min_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $success = $stmt->execute([$_POST['title'], $_POST['author'], $_POST['isbn']??null, $_POST['category'], $_POST['total_stock']??1, $_POST['total_stock']??1, $_POST['min_stock']??5]);
    $pdo->prepare("UPDATE stock_books SET status = CASE WHEN current_stock <= min_stock THEN 'low_stock' WHEN current_stock = 0 THEN 'out_of_stock' ELSE 'available' END WHERE id = LAST_INSERT_ID()")->execute();
    $message = $success ? '<div class="success-php">✅ Igice cyongerwewe muri stock!</div>' : '<div class="error-php">❌ Error!</div>';
}

// Data fetch
$where = $search_query ? "WHERE fname LIKE ? OR district LIKE ? OR section LIKE ?" : '';
$params = $search_query ? ["%$search_query%", "%$search_query%", "%$search_query%"] : [];
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$count_stmt->execute($params);
$total_students = $count_stmt->fetchColumn();
$total_pages = ceil($total_students / $limit);

$students_stmt = $pdo->prepare("SELECT * FROM students $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$students_stmt->execute($params);
$students = $students_stmt->fetchAll();

$staff_stmt = $pdo->prepare("SELECT * FROM staff WHERE status='active' ORDER BY name ASC");
$staff_stmt->execute();
$all_staff = $staff_stmt->fetchAll();

$stock_books = $pdo->query("SELECT * FROM stock_books ORDER BY status ASC, current_stock ASC")->fetchAll();

$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM students) students, 
    (SELECT COUNT(*) FROM staff WHERE status='active') staff,
    (SELECT COUNT(*) FROM stock_books WHERE status='available') available_books,
    (SELECT COUNT(*) FROM stock_books WHERE status='low_stock') low_stock,
    (SELECT COUNT(*) FROM stock_books WHERE status='out_of_stock') out_stock")->fetch();
?>

<!DOCTYPE html>
<html lang="rw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GS Nyagisozi - COMPLETE Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#f5f7fa 0%,#c3cfe2 100%);color:#1e293b;}
        .sidebar{position:fixed;left:0;top:0;height:100vh;width:280px;background:linear-gradient(180deg,rgba(44,123,229,0.95),rgba(26,93,219,0.95));backdrop-filter:blur(20px);color:white;padding:40px 25px;box-shadow:4px 0 40px rgba(44,123,229,0.4);}
        .sidebar h2{font-weight:900;font-size:28px;margin-bottom:50px;text-align:center;}
        .sidebar a{display:flex;align-items:center;padding:18px 20px;margin-bottom:15px;border-radius:20px;font-weight:600;transition:all 0.4s;color:white;text-decoration:none;border:1px solid rgba(255,255,255,0.2);}
        .sidebar a:hover{background:rgba(255,255,255,0.2);transform:translateX(10px);}
        .header{margin-left:280px;background:rgba(255,255,255,0.95);backdrop-filter:blur(25px);padding:25px 50px;box-shadow:0 8px 32px rgba(0,0,0,0.1);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100;}
        .main{margin-left:280px;padding:60px 50px;min-height:100vh;}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:25px;margin-bottom:40px;}
        .stat-card{background:linear-gradient(145deg,#fff,#f8fafc);border-radius:20px;padding:35px;text-align:center;transition:all 0.5s;box-shadow:0 15px 40px rgba(44,123,229,0.15);}
        .stat-card:hover{transform:translateY(-10px);}
        .stat-icon{font-size:50px;margin-bottom:20px;}.stat-students{color:#10b981;}.stat-staff{color:#2c7be5;}.stat-books{color:#8b5cf6;}.stat-low{color:#f59e0b;}.stat-out{color:#ef4444;}
        .management-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:30px;margin-bottom:50px;}
        .form-card{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border-radius:24px;padding:40px;border:2px solid rgba(44,123,229,0.2);}
        input,select{padding:16px;border:2px solid #e2e8f0;border-radius:12px;font-size:15px;width:100%;margin-bottom:15px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
        .btn-primary{background:linear-gradient(135deg,#2c7be5,#1a5ddb);border:none;padding:16px 32px;border-radius:12px;font-weight:900;font-size:16px;color:white;cursor:pointer;width:100%;box-shadow:0 8px 25px rgba(44,123,229,0.4);}
        .btn-primary:hover{transform:translateY(-2px);}
        .btn-success{background:linear-gradient(135deg,#10b981,#059669)!important;}
        .btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626)!important;}
        .excel-container{background:linear-gradient(145deg,#fff,#f8fafc);border-radius:20px;padding:35px;box-shadow:0 20px 60px rgba(0,0,0,0.12);overflow:hidden;margin-bottom:50px;}
        .excel-table{width:100%;border-collapse:collapse;}.excel-table th{background:linear-gradient(135deg,#2c7be5,#1a5ddb);color:white;padding:16px;font-weight:900;text-align:left;}
        .excel-table td{padding:16px;border-bottom:1px solid #e2e8f0;}
        .search-form{display:flex;gap:15px;margin-bottom:30px;}.search-input{flex:1;padding:20px;border-radius:15px;border:2px solid #e2e8f0;font-size:16px;}
        .search-btn{background:linear-gradient(135deg,#2c7be5,#1a5ddb);color:white;border:none;padding:20px 30px;border-radius:15px;font-weight:700;cursor:pointer;}
        .success-php{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46;padding:25px;border-radius:20px;margin-bottom:40px;border-left:6px solid #10b981;}
        .error-php{background:#fee2e2;color:#dc2626;padding:25px;border-radius:20px;margin-bottom:40px;border-left:6px solid #dc2626;}
        .modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;display:flex;align-items:center;justify-content:center;}
        @media(max-width:1200px){.sidebar{display:none;}.header,.main{margin-left:0;padding:25px;}}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2><i class="fas fa-graduation-cap"></i> GS Nyagisozi</h2>
        <a href="#dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="#students"><i class="fas fa-users"></i> Abanyeshuri</a>
        <a href="#staff"><i class="fas fa-chalkboard-teacher"></i> Abakozi</a>
        <a href="#stock"><i class="fas fa-boxes-stacked"></i> Stock Library</a>
    </div>

    <!-- Header -->
    <div class="header" id="dashboard">
        <h1><i class="fas fa-rocket"></i> COMPLETE Admin Dashboard</h1>
        <div><?= $_SESSION['user'] ?> | <a href="?logout=1" style="color:#dc3545;font-weight:700;">Logout</a></div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <?= $message ?? '' ?>

        <!-- Stats -->
        <section class="stats-grid">
            <div class="stat-card"><i class="fas fa-users stat-icon stat-students"></i><h3><?= number_format($stats['students']) ?></h3><p>Abanyeshuri</p></div>
            <div class="stat-card"><i class="fas fa-chalkboard-teacher stat-icon stat-staff"></i><h3><?= number_format($stats['staff']) ?></h3><p>Abakozi</p></div>
            <div class="stat-card"><i class="fas fa-book stat-icon stat-books"></i><h3><?= number_format($stats['available_books']) ?></h3><p>Available Books</p></div>
            <div class="stat-card"><i class="fas fa-exclamation-triangle stat-icon stat-low"></i><h3><?= number_format($stats['low_stock']) ?></h3><p>Low Stock</p></div>
        </section>

        <!-- Management Forms -->
        <section class="management-grid">
            <!-- Add Student -->
            <div class="form-card">
                <h2><i class="fas fa-user-plus"></i> Ongeraho Umunyeshuri</h2>
                <form method="POST">
                    <input name="fname" placeholder="Izina *" required>
                    <input name="district" placeholder="Komine *" required>
                    <input name="section" placeholder="Section *" required>
                    <select name="year"><option value="">Year</option><?php for($y=2020;$y<=2026;$y++):?><option><?= $y ?></option><?php endfor;?></select>
                    <button type="submit" name="add_student" class="btn-primary btn-success">✅ Ongeraho</button>
                </form>
            </div>

            <!-- Add Staff -->
            <div class="form-card">
                <h2><i class="fas fa-user-tie"></i> Ongeraho Umukozi</h2>
                <form method="POST">
                    <input name="name" placeholder="Izina *" required>
                    <input name="email" type="email" placeholder="Email">
                    <input name="phone" placeholder="Phone">
                    <select name="role" required>
                        <option value="">Role</option>
                        <option>Umwarimu</option><option>Umuyobozi</option><option>Accountant</option>
                        <option>Secretary</option><option>Guard</option>
                    </select>
                    <input name="salary" type="number" placeholder="Salary RWF">
                    <button type="submit" name="add_staff" class="btn-primary btn-success">✅ Ongeraho</button>
                </form>
            </div>

            <!-- Add Stock Book -->
            <div class="form-card">
                <h2><i class="fas fa-plus-circle"></i> Ongeraho Igice</h2>
                <form method="POST">
                    <input name="title" placeholder="Izina ry'igice *" required>
                    <input name="author" placeholder="Umwanditsi *" required>
                    <select name="category" required>
                        <option value="">Category</option>
                        <option>Mathematics</option><option>Science</option><option>History</option>
                        <option>Literature</option><option>English</option>
                    </select>
                    <input name="total_stock" type="number" value="10" min="1" placeholder="Total Stock">
                    <input name="min_stock" type="number" value="5" min="1" placeholder="Min Stock">
                    <button type="submit" name="add_stock_book" class="btn-primary btn-success">✅ Ongeraho Stock</button>
                </form>
            </div>
        </section>

        <!-- Students Table -->
        <section id="students">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
                <h2><i class="fas fa-users"></i> Abanyeshuri (<?= $total_students ?>)</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" class="search-input" placeholder="🔍 Shakisha...">
                    <button type="submit" class="search-btn">Shakisha</button>
                    <?php if($search_query): ?><a href="?" class="search-btn" style="background:#6b7280;">Clear</a><?php endif; ?>
                </form>
            </div>
            <div class="excel-container">
                <table class="excel-table">
                    <thead><tr><th>#</th><th>Izina</th><th>Komine</th><th>Section</th><th>Year</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($students as $i => $s): ?>
                        <tr>
                            <td><?= (($page-1)*10)+$i+1 ?></td>
                            <td style="font-weight:700;"><?= htmlspecialchars($s['fname']) ?></td>
                            <td><?= htmlspecialchars($s['district']) ?></td>
                            <td style="background:rgba(253,224,71,0.2);padding:8px;border-radius:6px;"><?= htmlspecialchars($s['section']) ?></td>
                            <td><?= $s['year'] ?: '-' ?></td>
                            <td>
                                <a href="?edit_student=<?= $s['id'] ?>" class="btn-primary btn-success" style="padding:8px 12px;font-size:14px;margin-right:5px;">Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="delete_student" class="btn-primary btn-danger" onclick="return confirm('Gusiba?')" style="padding:8px 12px;font-size:14px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Staff Table -->
        <section id="staff">
            <h2><i class="fas fa-chalkboard-teacher"></i> Abakozi (<?= count($all_staff) ?>)</h2>
            <div class="excel-container">
                <table class="excel-table">
                    <thead><tr><th>#</th><th>Izina</th><th>Role</th><th>Email</th><th>Phone</th><th>Salary</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($all_staff as $i => $s): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td style="font-weight:700;"><?= htmlspecialchars($s['name']) ?></td>
                            <td style="background:linear-gradient(135deg,#10b981,#059669);color:white;padding:8px;border-radius:6px;"><?= htmlspecialchars($s['role']) ?></td>
                            <td><?= htmlspecialchars($s['email'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($s['phone'] ?: '-') ?></td>
                            <td style="color:#059669;font-weight:900;"><?= $s['salary'] ? number_format($s['salary']) . ' RWF' : '-' ?></td>
                            <td>
                                <a href="?edit_staff=<?= $s['id'] ?>" class="btn-primary btn-success" style="padding:8px 12px;font-size:14px;margin-right:5px;">Edit</a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="staff_id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="delete_staff" class="btn-primary btn-danger" onclick="return confirm('Gusiba?')" style="padding:8px 12px;font-size:14px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Stock Library Table -->
        <section id="stock">
            <h2><i class="fas fa-boxes-stacked"></i> Stock Library (<?= count($stock_books) ?>)</h2>
            <div class="excel-container">
                <table class="excel-table">
                    <thead><tr><th>#</th><th>Igice</th><th>Umwanditsi</th><th>Category</th><th>Total</th><th>Current</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($stock_books as $i => $b): ?>
                        <tr style="<?= $b['status']=='low_stock' ? 'background:#fef3c7;' : ($b['status']=='out_of_stock' ? 'background:#fee2e2;' : '') ?>">
                            <td><?= $i+1 ?></td>
                            <td style="font-weight:700;"><?= htmlspecialchars($b['title']) ?></td>
                            <td><?= htmlspecialchars($b['author']) ?></td>
                            <td style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:white;padding:8px;border-radius:6px;"><?= htmlspecialchars($b['category']) ?></td>
                            <td style="color:#3b82f6;"><?= $b['total_stock'] ?></td>
                            <td style="<?= $b['current_stock'] <= $b['min_stock'] ? 'color:#ef4444;font-weight:900;' : 'color:#10b981;font-weight:900;' ?>"><?= $b['current_stock'] ?></td>
                            <td style="<?= $b['status']=='available' ? 'color:#10b981' : ($b['status']=='low_stock' ? 'color:#f59e0b' : 'color:#ef4444') ?>;font-weight:900;"><?= ucwords(str_replace('_', ' ', $b['status'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Edit Modals -->
        <?php if($student_to_edit): ?>
        <div class="modal">
            <div class="form-card" style="width:90%;max-width:500px;">
                <h2><i class="fas fa-edit"></i> Vugurura Umunyeshuri</h2>
                <form method="POST">
                    <input type="hidden" name="student_id" value="<?= $student_to_edit['id'] ?>">
                    <input name="fname" value="<?= htmlspecialchars($student_to_edit['fname']) ?>" required>
                    <input name="district" value="<?= htmlspecialchars($student_to_edit['district']) ?>" required>
                    <input name="section" value="<?= htmlspecialchars($student_to_edit['section']) ?>" required>
                    <select name="year"><?php for($y=2020;$y<=2026;$y++):?><option <?= $student_to_edit['year']==$y ? 'selected' : '' ?>><?= $y ?></option><?php endfor;?></select>
                    <div style="display:flex;gap:15px;">
                        <button type="submit" name="update_student" class="btn-primary btn-success" style="flex:1;">✅ Vugurura</button>
                        <a href="?" class="btn-primary btn-danger" style="flex:1;text-align:center;padding:16px 0;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if($staff_to_edit): ?>
        <div class="modal">
            <div class="form-card" style="width:90%;max-width:500px;">
                <h2><i class="fas fa-edit"></i> Vugurura Umukozi</h2>
                <form method="POST">
                    <input type="hidden" name="staff_id" value="<?= $staff_to_edit['id'] ?>">
                    <input name="name" value="<?= htmlspecialchars($staff_to_edit['name']) ?>" required>
                    <input name="email" value="<?= htmlspecialchars($staff_to_edit['email']) ?>">
                    <input name="phone" value="<?= htmlspecialchars($staff_to_edit['phone']) ?>">
                    <select name="role">
                        <option <?= $staff_to_edit['role']=='Umwarimu' ? 'selected' : '' ?>>Umwarimu</option>
                        <option <?= $staff_to_edit['role']=='Umuyobozi' ? 'selected' : '' ?>>Umuyobozi</option>
                        <option <?= $staff_to_edit['role']=='Accountant' ? 'selected' : '' ?>>Accountant</option>
                    </select>
                    <input name="salary" type="number" value="<?= $staff_to_edit['salary'] ?>">
                    <div style="display:flex;gap:15px;">
                        <button type="submit" name="update_staff" class="btn-primary btn-success" style="flex:1;">✅ Vugurura</button>
                        <a href="?" class="btn-primary btn-danger" style="flex:1;text-align:center;padding:16px 0;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.sidebar a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                document.querySelector(a.getAttribute('href')).scrollIntoView({behavior:'smooth'});
            });
        });
    </script>
</body>
</html>
