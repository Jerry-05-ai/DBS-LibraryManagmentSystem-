<?php
require_once 'config.php';

// Get counts for dashboard
$books_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM Book")->fetch_assoc()['count'];
$members_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM Member")->fetch_assoc()['count'];
$borrowed_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM Borrow_Book WHERE BorrowStatus = 'Borrowed'")->fetch_assoc()['count'];
$overdue_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM Borrow_Book WHERE BorrowStatus = 'Overdue'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo">📚 Library Management System</div>
            <ul class="nav-links">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="books/view_books.php">Books</a></li>
                <li><a href="members/view_members.php">Members</a></li>
                <li><a href="borrow/view_borrowed.php">Borrow/Return</a></li>
                <li><a href="reports/reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container">
        <h1>Dashboard</h1>
        
        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <h3>📚 Total Books</h3>
                <p class="stat-number"><?php echo $books_count; ?></p>
                <a href="books/view_books.php" class="btn btn-info">View All Books</a>
            </div>
            
            <div class="card">
                <h3>👥 Total Members</h3>
                <p class="stat-number"><?php echo $members_count; ?></p>
                <a href="members/view_members.php" class="btn btn-info">View All Members</a>
            </div>
            
            <div class="card">
                <h3>📖 Currently Borrowed</h3>
                <p class="stat-number"><?php echo $borrowed_count; ?></p>
                <a href="borrow/view_borrowed.php" class="btn btn-info">View Borrowed Books</a>
            </div>
            
            <div class="card">
                <h3>⏰ Overdue Books</h3>
                <p class="stat-number"><?php echo $overdue_count; ?></p>
                <a href="borrow/view_borrowed.php" class="btn btn-info">Check Overdue</a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-top: 2rem;">
            <h3>Quick Actions</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="books/add_book.php" class="btn btn-primary">Add New Book</a>
                <a href="members/add_member.php" class="btn btn-success">Add New Member</a>
                <a href="borrow/borrow_book.php" class="btn btn-warning">Borrow a Book</a>
                <a href="borrow/return_book.php" class="btn btn-danger">Return a Book</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>Library Management System &copy; <?php echo date('Y'); ?></p>
    </footer>
</body>
</html>