<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'connection.php';

// Use ternary operator for PHP 5 compatibility
$group_param_value = isset($_GET['group']) ? $_GET['group'] : '';
$group = $group_param_value;

$query_clause = '';
$display_group_name = '';
$is_uncategorized = false;

if ($group === 'Uncategorized') {
    $query_clause = "(group_name = '' OR group_name IS NULL)";
    $display_group_name = 'Uncategorized';
    $is_uncategorized = true;
} else {
    $display_group_name = htmlspecialchars($group);
    $query_clause = "group_name = ?";
}

// Handle deletion
if (isset($_GET['delete_group'])) {
    $delete_group = $_GET['delete_group'];
    if ($delete_group === 'Uncategorized') {
        $delete_sql = "DELETE FROM contacts WHERE (group_name = '' OR group_name IS NULL)";
        mysqli_query($con, $delete_sql);
    } else {
        $delete_stmt = $con->prepare("DELETE FROM contacts WHERE group_name = ?");
        $delete_stmt->bind_param("s", $delete_group);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    echo "<script>alert('Group deleted successfully'); window.location.href='groups.php';</script>";
    exit;
}

// Handle rename
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_group_name_hidden']) && isset($_POST['new_group_name'])) {
    $old_group_name = $_POST['old_group_name_hidden'];
    $new_group_name = $_POST['new_group_name'];

    // If renaming "Uncategorized"
    if ($old_group_name === 'Uncategorized') {
        $update_sql = "UPDATE contacts SET group_name = ? WHERE group_name IS NULL OR group_name = ''";
        $stmt = $con->prepare($update_sql);
        $stmt->bind_param("s", $new_group_name);
    } else {
        $update_sql = "UPDATE contacts SET group_name = ? WHERE group_name = ?";
        $stmt = $con->prepare($update_sql);
        $stmt->bind_param("ss", $new_group_name, $old_group_name);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Group renamed successfully'); window.location.href='view_group.php?group=" . urlencode($new_group_name) . "';</script>";
        exit;
    } else {
        echo "<script>alert('Error renaming group: " . htmlspecialchars($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// Fetch contacts for the group
$sql = "SELECT id, name, email, phone FROM contacts WHERE {$query_clause} ORDER BY name ASC";
if ($is_uncategorized) {
    $stmt = $con->prepare($sql);
} else {
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $group);
}
$stmt->execute();

// Instead of get_result(), use bind_result() and fetch()
$rows = array();
$stmt->bind_result($id, $name, $email, $phone); // Bind variables to the columns in your SELECT query

while ($stmt->fetch()) {
    $rows[] = array(
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
    );
}
$stmt->close();

// Fetch existing group names for datalist
$group_names = array();
$result_groups = mysqli_query($con, "SELECT DISTINCT group_name FROM contacts WHERE group_name IS NOT NULL AND group_name != ''");
while ($row = mysqli_fetch_assoc($result_groups)) {
    $group_names[] = $row['group_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts in '<?php echo $display_group_name; ?>'</title>
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

        <header>
            <div class="header-content">
                <h2>Contacts in '<?php echo $display_group_name; ?>'</h2>
                <div>
                    <a href="groups.php" class="btn btn-secondary">Back to Groups</a>
                    <a href="add_contact.php" class="btn btn-success">Add Contact</a>
                    <?php if (!$is_uncategorized): ?>
                        <button onclick="showRenameModal()" class="btn btn-primary">Rename Group</button>
                    <?php endif; ?>
                </div>
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search contacts by name or phone..." autocomplete="off">
                    <button class="btn">Search</button>
                </div>
                    <div id="contactCount">
                    <?php
                    $count_query = "SELECT COUNT(*) as total FROM contacts";
                    $count_result = mysqli_query($con, $count_query);
                    $count_row = mysqli_fetch_assoc($count_result);
                    echo $count_row['total'] . " contact(s)";
                    ?>
                </div>
            </div>
        </header>

        <div id="contactsContainer">
            <?php if (!empty($rows)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_letter = '';
                        foreach ($rows as $row):
                            $first_letter = strtoupper(substr($row['name'], 0, 1));
                            if ($first_letter !== $current_letter) {
                                echo "<tr class='letter-header'><td colspan='4'>{$first_letter}</td></tr>"; // Changed colspan to 4 as there are 4 columns now
                                $current_letter = $first_letter;
                            }
                        ?>
                            <tr class="contact-row">
                                <td data-label="Name">
                                    <span class="ellipsis-cell" title="<?php echo htmlspecialchars($row['name']); ?>">
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </span>
                                </td>
                                <td data-label="Email">
                                    <span class="ellipsis-cell" title="<?php echo htmlspecialchars($row['email']); ?>">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </span>
                                </td>
                                <td data-label="Phone"><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td>
                                    <a href="edit_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Edit</a>
                                    <a href="delete_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this contact?')">Delete</a>
                                    <a href="share.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Share</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-group">
                    <p>No contacts in this group.</p>
                    <a href="add_contact.php" class="btn btn-success">Add Contact</a>
                </div>
            <?php endif; ?>
        </div>

        <div id="renameModal">
            <div class="modal-content">
                <h3>Rename Group</h3>
                <form method="POST">
                    <input type="hidden" id="oldGroupName" name="old_group_name_hidden">
                    <input list="groupSuggestions" name="new_group_name" id="newGroupName" placeholder="New group name" required style="padding: 10px; width: 100%; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc;">
                    <datalist id="groupSuggestions">
                        <?php foreach ($group_names as $gname): ?>
                            <option value="<?php echo htmlspecialchars($gname); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="submit" class="btn btn-primary">Rename</button>
                        <button type="button" class="btn btn-secondary" onclick="hideRenameModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>