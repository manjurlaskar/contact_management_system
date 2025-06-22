<?php
include 'connection.php';

// Get contact ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die("Invalid contact ID");
}

// Fetch contact data
$select = "SELECT * FROM contacts WHERE id='$id'";
$data = mysqli_query($con, $select);
$row = mysqli_fetch_assoc($data);

if (isset($_POST['update_btn'])) {
    // Sanitize form data
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $group = ($_POST['group_select'] === 'Other')
        ? mysqli_real_escape_string($con, $_POST['custom_group_input'])
        : mysqli_real_escape_string($con, $_POST['group_select']);

    // Update contact in database
    $update = "UPDATE contacts 
               SET name='$name', email='$email', phone='$phone', group_name='$group' 
               WHERE id='$id'";
    
    if (mysqli_query($con, $update)) {
        echo "<script>
                alert('Contact updated successfully');
                window.location.href='view_contacts.php';
              </script>";
    } else {
        $error = addslashes(mysqli_error($con));
        echo "<script>alert('Failed to update contact: $error');</script>";
    }
}

// Check if current group is custom
$predefinedGroups = array('Family', 'Friends', 'Work');
$isCustomGroup = !in_array($row['group_name'], $predefinedGroups);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contact</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="theme-toggle" id="themeToggle">
  <div class="toggle-handle">
    <i class="fas fa-sun"></i>
  </div>
</div>
<span class="theme-label">Light Mode</span>
        <h2>Edit Contact</h2>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
        <hr>
        <div class="form">
            <form action="edit_contact.php?id=<?php echo $id; ?>" method="POST">
                <label for="name">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" placeholder="Enter Name" required>

                <label for="email">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" placeholder="Enter Email">

                <label for="phone">Phone No.</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" placeholder="Enter Phone Number" required>

                <label for="group">Group</label>
                <select name="group_select" id="groupSelect">
                    <option value="">-- Select Group --</option>
                    <?php foreach ($predefinedGroups as $pg): ?>
                        <option value="<?php echo $pg; ?>" <?php echo ($row['group_name'] === $pg) ? 'selected' : ''; ?>>
                            <?php echo $pg; ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Other" <?php echo $isCustomGroup ? 'selected' : ''; ?>>Other</option>
                </select>

                <div id="custom-group-container">
                    <label for="custom_group_input">Custom Group</label>
                    <input type="text" name="custom_group_input" id="customGroupInput" 
                           placeholder="Enter Custom Group"
                           value="<?php echo $isCustomGroup ? htmlspecialchars($row['group_name']) : ''; ?>">
                </div>

                <input type="submit" name="update_btn" value="EDIT Contact" class="btn">
            </form>
            <hr>
        </div>
        <div class="form-actions">
            <a href="view_contacts.php" class="btn btn-secondary">Back to Contacts</a>
            
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>