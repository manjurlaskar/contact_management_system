<?php
include 'connection.php';

if (isset($_POST['save_btn'])) {
    // Sanitize and prepare form data
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $group = ($_POST['group'] === 'Other') 
        ? mysqli_real_escape_string($con, $_POST['custom_group']) 
        : mysqli_real_escape_string($con, $_POST['group']);

    // Insert contact into database
    $query = "INSERT INTO contacts (name, email, phone, group_name) 
              VALUES ('$name', '$email', '$phone', '$group')";
    
    if (mysqli_query($con, $query)) {
        echo "<script>
                alert('Contact added successfully');
                window.location.href='view_contacts.php';
              </script>";
    } else {
        $error = addslashes(mysqli_error($con));
        echo "<script>alert('Failed to add contact: $error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Contact</title>
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
        <h2>Add New Contact</h2>
        <hr>
        <div class="form">
            <form action="add_contact.php" method="POST">
                <label for="name">Name</label>
                <input type="text" name="name" placeholder="Enter Name" required>

                <label for="email">Email</label>
                <input type="email" name="email" placeholder="Enter Email" required>

                <label for="phone">Phone No.</label>
                <input type="text" name="phone" placeholder="Enter Phone Number" required>

                <label for="group">Group</label>
                <select name="group" id="groupSelect" onchange="toggleCustomGroup()" >
                    <option value="">-- Select Group --</option>
                    <option value="Family">Family</option>
                    <option value="Friends">Friends</option>
                    <option value="Work">Work</option>
                    <option value="Other">Other</option>
                </select>

                <div id="custom-group-container">
                    <label for="custom_group">Custom Group</label>
                    <input type="text" name="custom_group" id="customGroupInput" placeholder="Enter Custom Group">
                </div>

                <input type="submit" name="save_btn" value="Add Contact" class="btn">
            </form>
            <hr>
        </div>
        <div class="form-actions">
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