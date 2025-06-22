<?php
include 'connection.php';

// Handle group deletion
if (isset($_GET['delete_group'])) {
    $group_to_delete = mysqli_real_escape_string($con, $_GET['delete_group']);

    if ($group_to_delete === 'Uncategorized') {
        $delete_query = "DELETE FROM contacts WHERE group_name IS NULL OR group_name = ''";
    } else {
        $delete_query = "DELETE FROM contacts WHERE group_name = '$group_to_delete'";
    }

    if (mysqli_query($con, $delete_query)) {
        echo "<script>
                alert('Group deleted successfully');
                window.location.href='groups.php';
              </script>";
        exit;
    }
}

// Fetch contact groups
$groups_query = "
    SELECT 
        CASE 
            WHEN group_name IS NULL OR group_name = '' THEN 'Uncategorized'
            ELSE group_name
        END AS group_name
    FROM contacts
    GROUP BY group_name
    ORDER BY group_name
";
$groups_result = mysqli_query($con, $groups_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Groups</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:,"> <!-- Disables favicon request -->
</head>

<body>
    <div class="container">
        <div class="theme-toggle" id="themeToggle">
            <div class="toggle-handle">
                <i class="fas fa-sun"></i>
            </div>
        </div>
        <span class="theme-label">Light Mode</span>
        <header>
            <h2>Contact Groups</h2>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
            <a href="view_contacts.php" class="btn">View All Contacts</a>

            <div class="search-container">
                <input type="text" id="groupSearch" class="search-input" placeholder="Search groups...">
                <button class="btn">Search</button>
            </div>
            <div id="groupCount"></div>
        </header>

        <div class="groups-container">
            <?php if (mysqli_num_rows($groups_result) > 0): ?>
                <?php while ($group = mysqli_fetch_assoc($groups_result)): ?>
                    <?php
                    $group_name = $group['group_name'];

                    if ($group_name === 'Uncategorized') {
                        $count_query = "SELECT COUNT(*) AS total FROM contacts WHERE group_name IS NULL OR group_name = ''";
                        $contacts_query = "SELECT name FROM contacts WHERE group_name IS NULL OR group_name = '' ORDER BY name LIMIT 5";
                    } else {
                        $count_query = "SELECT COUNT(*) AS total FROM contacts WHERE group_name = '$group_name'";
                        $contacts_query = "SELECT name FROM contacts WHERE group_name = '$group_name' ORDER BY name LIMIT 5";
                    }

                    $count_result = mysqli_query($con, $count_query);
                    $count_row = mysqli_fetch_assoc($count_result);
                    $contact_count = $count_row['total'];

                    $contacts_result = mysqli_query($con, $contacts_query);
                    $contact_names = array();
                    while ($row = mysqli_fetch_assoc($contacts_result)) {
                        $contact_names[] = $row['name'];
                    }
                    ?>

                    <div class="group-card">
                        <div class="group-header">
                            <div class="group-name" title="<?php echo htmlspecialchars($group_name); ?>">
                                <?php echo htmlspecialchars($group_name); ?>
                            </div>

                            <div class="contact-count"><?php echo $contact_count; ?></div>
                        </div>
                        <ul class="contact-list">
                            <?php if ($contact_count > 0): ?>
                                <?php foreach ($contact_names as $name): ?>
                                    <li class="contact-item"><?php echo htmlspecialchars($name); ?></li>
                                <?php endforeach; ?>
                                <?php if ($contact_count > 5): ?>
                                    <li class="contact-item">+<?php echo ($contact_count - 5); ?> more...</li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li class="contact-item">No contacts</li>
                            <?php endif; ?>
                        </ul>
                        <div class="group-actions">
                            <a href="view_group.php?group=<?php echo urlencode($group_name); ?>" class="btn">View Group</a>
                            <a href="javascript:void(0)" onclick="if(confirm('Delete this group and all its contacts?')) {
                                   window.location.href='groups.php?delete_group=<?php echo urlencode($group_name); ?>'
                               }" class="btn btn-secondary">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-groups">
                    <p>No groups found. Start by adding contacts to groups.</p>
                    <a href="add_contact.php" class="btn">Add New Contact</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>