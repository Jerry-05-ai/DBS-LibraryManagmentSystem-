<?php
require_once '../config.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = sanitize_input($_GET['delete_id'], $conn);
    
    // Check if member has borrowed books
    $check_borrowed = mysqli_query($conn, "SELECT * FROM Borrow_Book WHERE MemberID = '$delete_id' AND BorrowStatus IN ('Borrowed', 'Overdue')");
    
    if (mysqli_num_rows($check_borrowed) > 0) {
        $error = "Cannot delete member. They have borrowed books.";
    } else {
        // Delete member
        $delete_query = "DELETE FROM Member WHERE MemberID = '$delete_id'";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Member deleted successfully!";
        } else {
            $error = "Error deleting member: " . mysqli_error($conn);
        }
    }
}

// Fetch all members
$members = mysqli_query($conn, "SELECT * FROM Member ORDER BY MemberName");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Members</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">👥 All Members</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="view_members.php">View Members</a></li>
                <li><a href="add_member.php">Add Member</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Member Management</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div style="margin-bottom: 1rem;">
            <a href="add_member.php" class="btn btn-primary">Add New Member</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Membership Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($member = mysqli_fetch_assoc($members)): ?>
                    <tr>
                        <td><?php echo $member['MemberID']; ?></td>
                        <td><?php echo htmlspecialchars($member['MemberName']); ?></td>
                        <td><?php echo htmlspecialchars($member['Phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($member['Email'] ?? 'N/A'); ?></td>
                        <td><?php echo $member['MembershipDate']; ?></td>
                        <td class="actions">
                            <a href="edit_member.php?id=<?php echo $member['MemberID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $member['MemberID']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>