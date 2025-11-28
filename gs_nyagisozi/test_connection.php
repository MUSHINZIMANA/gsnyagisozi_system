<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
    $students = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $users = $stmt->fetch()['total'];
    
    echo "<h2>✅ GS Nyagisozi Database Connected!</h2>";
    echo "<p>Students: $students</p>";
    echo "<p>Users: $users</p>";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
