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
        <hr>
        <div class="form">
            <form action="edit_contact.php?id=<?php echo $id; ?>" method="POST">
                <label for="name">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" placeholder="Enter Name" required>

                <label for="email">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" placeholder="Enter Email" required>

                <label for="phone">Phone No.</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" placeholder="Enter Phone Number" required>

                <label for="group">Group</label>
                <select name="group_select" id="groupSelect" onchange="toggleCustomGroup()" required>
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
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>

    <script>
        function toggleCustomGroup() {
            const groupSelect = document.getElementById('groupSelect');
            const customGroupContainer = document.getElementById('custom-group-container');
            const customGroupInput = document.getElementById('customGroupInput');

            if (groupSelect.value === 'Other') {
                customGroupContainer.style.display = 'block';
                customGroupInput.setAttribute('required', 'required');
            } else {
                customGroupContainer.style.display = 'none';
                customGroupInput.removeAttribute('required');
                customGroupInput.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', toggleCustomGroup);

                //ToggleMode
                const themeToggle = document.getElementById('themeToggle');
        const themeLabel = document.querySelector('.theme-label');
        const body = document.body;
        
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            body.classList.add('dark-mode');
            themeLabel.textContent = 'Dark Mode';
        } else {
            themeLabel.textContent = 'Light Mode';
        }
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            themeLabel.textContent = isDarkMode ? 'Dark Mode' : 'Light Mode';
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        });
    </script>
</body>
</html>