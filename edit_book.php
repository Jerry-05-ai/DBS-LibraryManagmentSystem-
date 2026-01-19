<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header("Location: view_books.php");
    exit();
}

$book_id = sanitize_input($_GET['id'], $conn);
$error = '';
$success = '';

// Get publishers and categories for dropdown
$publishers = mysqli_query($conn, "SELECT * FROM Publisher");
$categories = mysqli_query($conn, "SELECT * FROM Category");

// Fetch book details
$book_query = "SELECT * FROM Book WHERE BookID = '$book_id'";
$book_result = mysqli_query($conn, $book_query);
$book = mysqli_fetch_assoc($book_result);

if (!$book) {
    header("Location: view_books.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize_input($_POST['title'], $conn);
    $isbn = sanitize_input($_POST['isbn'], $conn);
    $publisher_id = sanitize_input($_POST['publisher_id'], $conn);
    $category_id = sanitize_input($_POST['category_id'], $conn);
    $total_copies = sanitize_input($_POST['total_copies'], $conn);
    
    // Check if ISBN exists for other books
    $check_isbn = mysqli_query($conn, "SELECT * FROM Book WHERE ISBN = '$isbn' AND BookID != '$book_id'");
    if (mysqli_num_rows($check_isbn) > 0) {
        $error = "ISBN already exists for another book!";
    } else {
        // Update book
        $update_query = "UPDATE Book SET 
                        Title = '$title',
                        ISBN = '$isbn',
                        PublisherID = '$publisher_id',
                        CategoryID = '$category_id',
                        TotalCopies = '$total_copies'
                        WHERE BookID = '$book_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $success = "Book updated successfully!";
            // Refresh book data
            $book_result = mysqli_query($conn, $book_query);
            $book = mysqli_fetch_assoc($book_result);
        } else {
            $error = "Error updating book: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📚 Edit Book</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="view_books.php">View Books</a></li>
                <li><a href="add_book.php">Add Book</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h1>Edit Book</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Book Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($book['Title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="isbn">ISBN *</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" 
                           value="<?php echo htmlspecialchars($book['ISBN']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="publisher_id">Publisher</label>
                    <select id="publisher_id" name="publisher_id" class="form-control">
                        <option value="">Select Publisher</option>
                        <?php while($publisher = mysqli_fetch_assoc($publishers)): ?>
                            <option value="<?php echo $publisher['PublisherID']; ?>"
                                <?php echo ($book['PublisherID'] == $publisher['PublisherID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($publisher['PublisherName']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Select Category</option>
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while($category = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $category['CategoryID']; ?>"
                                <?php echo ($book['CategoryID'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="total_copies">Total Copies</label>
                    <input type="number" id="total_copies" name="total_copies" class="form-control" 
                           value="<?php echo $book['TotalCopies']; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Available Copies</label>
                    <input type="text" class="form-control" value="<?php echo $book['AvailableCopies']; ?>" readonly>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Book</button>
                    <a href="view_books.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>