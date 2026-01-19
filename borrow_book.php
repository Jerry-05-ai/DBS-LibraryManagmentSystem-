<?php
require_once '../config.php';

$error = '';
$success = '';

// Fetch available books (with copies > 0) and members
$books = mysqli_query($conn, "SELECT * FROM Book WHERE AvailableCopies > 0 ORDER BY Title");
$members = mysqli_query($conn, "SELECT * FROM Member ORDER BY MemberName");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = sanitize_input($_POST['member_id'], $conn);
    $book_id = sanitize_input($_POST['book_id'], $conn);
    $borrow_date = sanitize_input($_POST['borrow_date'], $conn);
    $due_date = sanitize_input($_POST['due_date'], $conn);
    
    // Validate
    if (empty($member_id) || empty($book_id) || empty($borrow_date) || empty($due_date)) {
        $error = "All fields are required!";
    } elseif ($due_date <= $borrow_date) {
        $error = "Due date must be after borrow date!";
    } else {
        // Check if book is available
        $check_book = mysqli_query($conn, "SELECT * FROM Book WHERE BookID = '$book_id' AND AvailableCopies > 0");
        if (mysqli_num_rows($check_book) == 0) {
            $error = "Book is not available for borrowing!";
        } else {
            // Insert borrow record
            $insert_query = "INSERT INTO Borrow_Book (MemberID, BookID, BorrowDate, DueDate, BorrowStatus) 
                            VALUES ('$member_id', '$book_id', '$borrow_date', '$due_date', 'Borrowed')";
            
            if (mysqli_query($conn, $insert_query)) {
                // Update available copies
                $update_query = "UPDATE Book SET AvailableCopies = AvailableCopies - 1 WHERE BookID = '$book_id'";
                mysqli_query($conn, $update_query);
                
                $success = "Book borrowed successfully!";
                $_POST = array();
            } else {
                $error = "Error: " . mysqli_error($conn);
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
    <title>Borrow Book</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📖 Borrow Book</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="borrow_book.php">Borrow Book</a></li>
                <li><a href="view_borrowed.php">View Borrowed</a></li>
                <li><a href="return_book.php">Return Book</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h1>Borrow a Book</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="member_id">Member *</label>
                    <select id="member_id" name="member_id" class="form-control" required>
                        <option value="">Select Member</option>
                        <?php while($member = mysqli_fetch_assoc($members)): ?>
                            <option value="<?php echo $member['MemberID']; ?>"
                                <?php echo (isset($_POST['member_id']) && $_POST['member_id'] == $member['MemberID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['MemberName']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="book_id">Book *</label>
                    <select id="book_id" name="book_id" class="form-control" required>
                        <option value="">Select Book</option>
                        <?php 
                        mysqli_data_seek($books, 0);
                        while($book = mysqli_fetch_assoc($books)): ?>
                            <option value="<?php echo $book['BookID']; ?>"
                                <?php echo (isset($_POST['book_id']) && $_POST['book_id'] == $book['BookID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($book['Title']); ?> (Available: <?php echo $book['AvailableCopies']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="borrow_date">Borrow Date *</label>
                    <input type="date" id="borrow_date" name="borrow_date" class="form-control" 
                           value="<?php echo $_POST['borrow_date'] ?? date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date *</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" 
                           value="<?php echo $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days')); ?>" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Borrow Book</button>
                    <a href="../index.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>