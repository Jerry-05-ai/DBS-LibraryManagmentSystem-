<?php
require_once '../config.php';

$error = '';
$success = '';

// Fetch borrowed books for return
$borrowed_books = mysqli_query($conn, "SELECT bb.*, m.MemberName, b.Title 
                                       FROM Borrow_Book bb 
                                       JOIN Member m ON bb.MemberID = m.MemberID 
                                       JOIN Book b ON bb.BookID = b.BookID 
                                       WHERE bb.BorrowStatus IN ('Borrowed', 'Overdue')");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrow_id = sanitize_input($_POST['borrow_id'], $conn);
    $return_date = sanitize_input($_POST['return_date'], $conn);
    $return_status = sanitize_input($_POST['return_status'], $conn);
    
    if (empty($borrow_id) || empty($return_date)) {
        $error = "Borrow record and return date are required!";
    } else {
        // Get borrow details
        $borrow_details = mysqli_query($conn, "SELECT * FROM Borrow_Book WHERE BorrowID = '$borrow_id'");
        $borrow = mysqli_fetch_assoc($borrow_details);
        
        // Calculate fine if overdue
        $fine = 0;
        $due_date = strtotime($borrow['DueDate']);
        $return_timestamp = strtotime($return_date);
        
        if ($return_timestamp > $due_date) {
            $days_overdue = floor(($return_timestamp - $due_date) / (60 * 60 * 24));
            $fine = $days_overdue * 10; // ₹10 per day overdue
        }
        
        // Insert into Return_Book table
        $insert_query = "INSERT INTO Return_Book (BorrowID, ReturnDate, ReturnStatus, Fine) 
                        VALUES ('$borrow_id', '$return_date', '$return_status', '$fine')";
        
        if (mysqli_query($conn, $insert_query)) {
            // Update borrow status
            $update_borrow = "UPDATE Borrow_Book SET BorrowStatus = 'Returned' WHERE BorrowID = '$borrow_id'";
            mysqli_query($conn, $update_borrow);
            
            // Update book available copies
            $update_book = "UPDATE Book SET AvailableCopies = AvailableCopies + 1 WHERE BookID = '{$borrow['BookID']}'";
            mysqli_query($conn, $update_book);
            
            $success = "Book returned successfully!";
            if ($fine > 0) {
                $success .= " Fine amount: ₹" . $fine;
            }
            
            $_POST = array();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">📖 Return Book</div>
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
            <h1>Return a Book</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="borrow_id">Select Borrow Record *</label>
                    <select id="borrow_id" name="borrow_id" class="form-control" required>
                        <option value="">Select Borrow Record</option>
                        <?php while($borrow = mysqli_fetch_assoc($borrowed_books)): ?>
                            <option value="<?php echo $borrow['BorrowID']; ?>"
                                <?php echo (isset($_POST['borrow_id']) && $_POST['borrow_id'] == $borrow['BorrowID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($borrow['MemberName']); ?> - 
                                <?php echo htmlspecialchars($borrow['Title']); ?> 
                                (Due: <?php echo $borrow['DueDate']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="return_date">Return Date *</label>
                    <input type="date" id="return_date" name="return_date" class="form-control" 
                           value="<?php echo $_POST['return_date'] ?? date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="return_status">Return Status</label>
                    <select id="return_status" name="return_status" class="form-control">
                        <option value="Returned On Time" <?php echo (isset($_POST['return_status']) && $_POST['return_status'] == 'Returned On Time') ? 'selected' : ''; ?>>Returned On Time</option>
                        <option value="Returned Early" <?php echo (isset($_POST['return_status']) && $_POST['return_status'] == 'Returned Early') ? 'selected' : ''; ?>>Returned Early</option>
                        <option value="Returned Late" <?php echo (isset($_POST['return_status']) && $_POST['return_status'] == 'Returned Late') ? 'selected' : ''; ?>>Returned Late</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Return Book</button>
                    <a href="../index.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>