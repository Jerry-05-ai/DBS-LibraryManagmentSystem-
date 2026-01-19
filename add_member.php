<?php
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name'], $conn);
    $phone = sanitize_input($_POST['phone'], $conn);
    $email = sanitize_input($_POST['email'], $conn);
    $membership_date = sanitize_input($_POST['membership_date'], $conn);
    
    // Validate inputs
    if (empty($name)) {
        $error = "Member name is required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        if (!empty($email)) {
            $check_email = mysqli_query($conn, "SELECT * FROM Member WHERE Email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email already registered!";
            }
        }
        
        if (!$error) {
            // 🔴 FIX: Calculate next MemberID
            $max_id_query = mysqli_query($conn, "SELECT MAX(MemberID) as max_id FROM Member");
            $max_id_result = $max_id_query->fetch_assoc();
            $next_member_id = ($max_id_result['max_id'] ?? 0) + 1;
            
            // 🔴 FIXED INSERT QUERY: Use calculated ID
            $insert_query = "INSERT INTO Member (MemberID, MemberName, Phone, Email, MembershipDate) 
                            VALUES ('$next_member_id', '$name', '$phone', '$email', '$membership_date')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "✅ Member added successfully! Member ID: $next_member_id";
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
    <title>Add New Member</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">👥 Add New Member</div>
            <ul class="nav-links">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="view_members.php">View Members</a></li>
                <li><a href="add_member.php">Add Member</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h1>Add New Member</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Member Name *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo $_POST['name'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo $_POST['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="membership_date">Membership Date</label>
                    <input type="date" id="membership_date" name="membership_date" class="form-control" 
                           value="<?php echo $_POST['membership_date'] ?? date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Member</button>
                    <a href="view_members.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>