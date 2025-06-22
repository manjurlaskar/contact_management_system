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

    <script src="script.js"></script>
</body>
</html>