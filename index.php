<?php include 'connection.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Management System</title>
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

        <h2>Welcome to Contact Management System</h2>
        <p>This system allows you to manage your contacts, including adding, editing, deleting, importing, exporting, and sharing them.</p>

        <div class="links">
            <a href="view_contacts.php" class="btn">View All Contacts</a>
            <a href="import_contacts.php" class="btn">Import Contacts</a>
            <a href="export_contacts.php" class="btn" id="exportLink">Export Contacts</a>
            <a href="add_contact.php" class="btn">Add New Contact</a>
            <a href="groups.php" class="btn">View Groups</a>
        </div>
    </div>

    <script>
        document.getElementById('exportLink').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to export all contacts?")) {
                window.location.href = 'export_contacts.php';
            }
        });
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