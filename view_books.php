<?php
require_once '../config.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = sanitize_input($_GET['delete_id'], $conn);
    
    // First check if book is borrowed
    $check_borrowed = mysqli_query($conn, "SELECT * FROM Borrow_Book WHERE BookID = '$delete_id' AND BorrowStatus IN ('Borrowed', 'Overdue')");
    
    if (mysqli_num_rows($check_borrowed) > 0) {
        $error = "Cannot delete book. It is currently borrowed.";
    } else {
        // Delete book
        $delete_query = "DELETE FROM Book WHERE BookID = '$delete_id'";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Book deleted successfully!";
        } else {
            $error = "Error deleting book: " . mysqli_error($conn);
        }
    }
}

// Fetch all books with publisher and category names using JOIN
$query = "SELECT b.*, p.PublisherName, c.CategoryName 
          FROM Book b 
          LEFT JOIN Publisher p ON b.PublisherID = p.PublisherID 
          LEFT JOIN Category c ON b.CategoryID = c.CategoryID 
          ORDER BY b.Title";
$books = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Books</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📚 All Books</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="view_books.php">View Books</a></li>
                <li><a href="add_book.php">Add Book</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Book Management</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div style="margin-bottom: 1rem;">
            <a href="add_book.php" class="btn btn-primary">Add New Book</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Publisher</th>
                        <th>Category</th>
                        <th>Total Copies</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($book = mysqli_fetch_assoc($books)): ?>
                    <tr>
                        <td><?php echo $book['BookID']; ?></td>
                        <td><?php echo htmlspecialchars($book['Title']); ?></td>
                        <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                        <td><?php echo htmlspecialchars($book['PublisherName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['CategoryName'] ?? 'N/A'); ?></td>
                        <td><?php echo $book['TotalCopies']; ?></td>
                        <td><?php echo $book['AvailableCopies']; ?></td>
                        <td class="actions">
                            <a href="edit_book.php?id=<?php echo $book['BookID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $book['BookID']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>