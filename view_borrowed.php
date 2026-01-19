<?php
require_once '../config.php';

// Update overdue status
$update_overdue = mysqli_query($conn, "UPDATE Borrow_Book SET BorrowStatus = 'Overdue' 
                                       WHERE DueDate < CURDATE() AND BorrowStatus = 'Borrowed'");

// Fetch borrowed books with member and book details using JOIN
$query = "SELECT bb.*, m.MemberName, b.Title, b.ISBN 
          FROM Borrow_Book bb 
          JOIN Member m ON bb.MemberID = m.MemberID 
          JOIN Book b ON bb.BookID = b.BookID 
          WHERE bb.BorrowStatus IN ('Borrowed', 'Overdue')
          ORDER BY bb.DueDate";
$borrowed = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Borrowed Books</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📖 Borrowed Books</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="borrow_book.php">Borrow Book</a></li>
                <li><a href="view_borrowed.php">View Borrowed</a></li>
                <li><a href="return_book.php">Return Book</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Currently Borrowed Books</h1>
        
        <div style="margin-bottom: 1rem;">
            <a href="borrow_book.php" class="btn btn-primary">Borrow New Book</a>
            <a href="return_book.php" class="btn btn-success">Return Book</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Borrow ID</th>
                        <th>Member</th>
                        <th>Book Title</th>
                        <th>ISBN</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($borrow = mysqli_fetch_assoc($borrowed)): 
                        $overdue_days = $borrow['BorrowStatus'] == 'Overdue' ? 
                            floor((time() - strtotime($borrow['DueDate'])) / (60 * 60 * 24)) : 0;
                    ?>
                    <tr>
                        <td><?php echo $borrow['BorrowID']; ?></td>
                        <td><?php echo htmlspecialchars($borrow['MemberName']); ?></td>
                        <td><?php echo htmlspecialchars($borrow['Title']); ?></td>
                        <td><?php echo htmlspecialchars($borrow['ISBN']); ?></td>
                        <td><?php echo $borrow['BorrowDate']; ?></td>
                        <td><?php echo $borrow['DueDate']; ?></td>
                        <td>
                            <span style="color: <?php echo $borrow['BorrowStatus'] == 'Overdue' ? '#e74c3c' : '#27ae60'; ?>; 
                                        font-weight: bold;">
                                <?php echo $borrow['BorrowStatus']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($overdue_days > 0): ?>
                                <span style="color: #e74c3c; font-weight: bold;">
                                    <?php echo $overdue_days; ?> days
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (mysqli_num_rows($borrowed) == 0): ?>
            <div class="alert alert-info">No books are currently borrowed.</div>
        <?php endif; ?>
    </div>
</body>
</html>