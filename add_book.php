<?php
require_once '../config.php';

$error = '';
$success = '';

// Get publishers and categories for dropdown
$publishers = mysqli_query($conn, "SELECT * FROM Publisher");
$categories = mysqli_query($conn, "SELECT * FROM Category");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $title = sanitize_input($_POST['title'], $conn);
    $isbn = sanitize_input($_POST['isbn'], $conn);
    $publisher_id = sanitize_input($_POST['publisher_id'], $conn);
    $category_id = sanitize_input($_POST['category_id'], $conn);
    $total_copies = sanitize_input($_POST['total_copies'], $conn);
    
    // Validate inputs
    if (empty($title) || empty($isbn)) {
        $error = "Title and ISBN are required!";
    } else {
        // Check if ISBN already exists
        $check_isbn = mysqli_query($conn, "SELECT * FROM Book WHERE ISBN = '$isbn'");
        if (mysqli_num_rows($check_isbn) > 0) {
            $error = "ISBN already exists!";
        } else {
            // FIRST, let's find the next available BookID
            $max_id_query = mysqli_query($conn, "SELECT MAX(BookID) as max_id FROM Book");
            $max_id_result = $max_id_query->fetch_assoc();
            $next_book_id = ($max_id_result['max_id'] ?? 0) + 1;
            
            // Insert book WITH the calculated ID
            $available_copies = $total_copies;
            
            // 🔴 LINE 35 - THIS IS THE FIXED VERSION:
            $insert_query = "INSERT INTO Book (BookID, Title, ISBN, PublisherID, CategoryID, TotalCopies, AvailableCopies) 
                            VALUES ('$next_book_id', '$title', '$isbn', '$publisher_id', '$category_id', '$total_copies', '$available_copies')";
            
            // Debug: Show the query
            // echo "Query: $insert_query<br>";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "✅ Book added successfully! Book ID: $next_book_id";
                // Clear form
                $_POST = array();
            } else {
                $error = "❌ Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📚 Add New Book</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="view_books.php">View Books</a></li>
                <li><a href="add_book.php">Add Book</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h1>Add New Book</h1>
            
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
                           value="<?php echo $_POST['title'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="isbn">ISBN *</label>
                    <input type="text" id="isbn" name="isbn" class="form-control" 
                           value="<?php echo $_POST['isbn'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="publisher_id">Publisher</label>
                    <select id="publisher_id" name="publisher_id" class="form-control">
                        <option value="">Select Publisher</option>
                        <?php while($publisher = mysqli_fetch_assoc($publishers)): ?>
                            <option value="<?php echo $publisher['PublisherID']; ?>"
                                <?php echo (isset($_POST['publisher_id']) && $_POST['publisher_id'] == $publisher['PublisherID']) ? 'selected' : ''; ?>>
                                <?php echo $publisher['PublisherName']; ?>
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
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                <?php echo $category['CategoryName']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="total_copies">Total Copies</label>
                    <input type="number" id="total_copies" name="total_copies" class="form-control" 
                           value="<?php echo $_POST['total_copies'] ?? 1; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Book</button>
                    <a href="view_books.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>