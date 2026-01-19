<?php
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Reports</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📊 Library Reports</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Library Reports & Analytics</h1>
        
        <div class="dashboard-grid">
            <!-- Report 1: Books by Category -->
            <div class="card">
                <h3>📚 Books by Category</h3>
                <?php
                $query1 = "SELECT c.CategoryName, COUNT(b.BookID) as BookCount 
                          FROM Book b 
                          JOIN Category c ON b.CategoryID = c.CategoryID 
                          GROUP BY c.CategoryID 
                          ORDER BY BookCount DESC";
                $result1 = mysqli_query($conn, $query1);
                ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Books</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result1)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                            <td><?php echo $row['BookCount']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Report 2: Most Borrowed Books -->
            <div class="card">
                <h3>📖 Most Borrowed Books</h3>
                <?php
                $query2 = "SELECT b.Title, COUNT(bb.BorrowID) as BorrowCount 
                          FROM Borrow_Book bb 
                          JOIN Book b ON bb.BookID = b.BookID 
                          GROUP BY b.BookID 
                          ORDER BY BorrowCount DESC 
                          LIMIT 5";
                $result2 = mysqli_query($conn, $query2);
                ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Times Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result2)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><?php echo $row['BorrowCount']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Report 3: Top Members -->
            <div class="card">
                <h3>👥 Active Members</h3>
                <?php
                $query3 = "SELECT m.MemberName, COUNT(bb.BorrowID) as BorrowCount 
                          FROM Borrow_Book bb 
                          JOIN Member m ON bb.MemberID = m.MemberID 
                          GROUP BY m.MemberID 
                          ORDER BY BorrowCount DESC 
                          LIMIT 5";
                $result3 = mysqli_query($conn, $query3);
                ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Books Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result3)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['MemberName']); ?></td>
                            <td><?php echo $row['BorrowCount']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Report 4: Monthly Borrowing Statistics -->
            <div class="card">
                <h3>📅 Monthly Borrowing Stats</h3>
                <?php
                $query4 = "SELECT DATE_FORMAT(BorrowDate, '%Y-%m') as Month, 
                          COUNT(*) as BorrowCount 
                          FROM Borrow_Book 
                          GROUP BY DATE_FORMAT(BorrowDate, '%Y-%m') 
                          ORDER BY Month DESC 
                          LIMIT 6";
                $result4 = mysqli_query($conn, $query4);
                ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Books Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result4)): ?>
                        <tr>
                            <td><?php echo $row['Month']; ?></td>
                            <td><?php echo $row['BorrowCount']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Report 5: Books Availability -->
            <div class="card">
                <h3>📚 Books Availability</h3>
                <?php
                $query5 = "SELECT 
                          SUM(TotalCopies) as TotalBooks,
                          SUM(AvailableCopies) as AvailableBooks,
                          SUM(TotalCopies) - SUM(AvailableCopies) as BorrowedBooks
                          FROM Book";
                $result5 = mysqli_query($conn, $query5);
                $stats = mysqli_fetch_assoc($result5);
                ?>
                <div style="padding: 1rem;">
                    <p><strong>Total Books:</strong> <?php echo $stats['TotalBooks']; ?></p>
                    <p><strong>Available Books:</strong> <?php echo $stats['AvailableBooks']; ?></p>
                    <p><strong>Currently Borrowed:</strong> <?php echo $stats['BorrowedBooks']; ?></p>
                </div>
            </div>
            
            <!-- Report 6: Overdue Books -->
            <div class="card">
                <h3>⏰ Overdue Books</h3>
                <?php
                $query6 = "SELECT b.Title, m.MemberName, bb.DueDate, 
                          DATEDIFF(CURDATE(), bb.DueDate) as DaysOverdue 
                          FROM Borrow_Book bb 
                          JOIN Book b ON bb.BookID = b.BookID 
                          JOIN Member m ON bb.MemberID = m.MemberID 
                          WHERE bb.BorrowStatus = 'Overdue' 
                          ORDER BY DaysOverdue DESC";
                $result6 = mysqli_query($conn, $query6);
                ?>
                <?php if (mysqli_num_rows($result6) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Member</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result6)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><?php echo htmlspecialchars($row['MemberName']); ?></td>
                            <td><?php echo $row['DueDate']; ?></td>
                            <td style="color: #e74c3c; font-weight: bold;">
                                <?php echo $row['DaysOverdue']; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="padding: 1rem;">No overdue books currently.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>