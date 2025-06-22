<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'connection.php';

    //Fetch contacts
    $sql = "SELECT id, name, email, phone, group_name FROM contacts ORDER BY name ASC";
    $result = mysqli_query($con, $sql);

    $rows = array(); // Initialize as an empty array
    if ($result) { // Check if query was successful
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($rows, $row); // Use array_push instead of [] for PHP < 5.4 compatibility
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contacts</title>
    <link rel="stylesheet" href="style.css">
    <?php
    $group_names = array("Family", "Friends", "Work"); // Default options
    $result_groups = mysqli_query($con, "SELECT DISTINCT group_name FROM contacts WHERE group_name IS NOT NULL AND group_name != ''");
    if ($result_groups) {
        while ($row_group = mysqli_fetch_assoc($result_groups)) { // Renamed $row to $row_group to avoid conflicts
            $name = $row_group['group_name'];
            if (!in_array($name, $group_names, true)) {
                array_push($group_names, $name); // Changed from [] to array_push for PHP < 5.4 compatibility
            }
        }
    }
    ?>
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
                <h2>Contact List</h2>
                <div>
                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                    <a href="add_contact.php" class="btn btn-success">Add Contact</a>
                </div>
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input"
                        placeholder="Search contacts by name or phone..." autocomplete="off">
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
                            <th>Group</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_letter = '';
                        foreach ($rows as $row):
                            $first_letter = strtoupper(substr($row['name'], 0, 1));
                            if ($first_letter !== $current_letter) {
                                // Add letter header only if it's a new letter and contact name is not empty
                                if (!empty($row['name'])) {
                                    echo "<tr class='letter-header'><td colspan='5'>{$first_letter}</td></tr>"; // colspan adjusted to 5
                                }
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
                                <td data-label="Phone">
                                    <?php echo htmlspecialchars($row['phone']); ?>
                                </td>
                                <td data-label="Group">
                                    <span class="ellipsis-cell"
                                        title="<?php echo htmlspecialchars(!empty($row['group_name']) ? $row['group_name'] : 'Uncategorized'); ?>">
                                        <?php echo htmlspecialchars(!empty($row['group_name']) ? $row['group_name'] : 'Uncategorized'); ?>
                                    </span>
                                </td>
                               <td class="actions">
                                    <div class="actions-container">
                                        <a href="edit_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Edit</a>
                                        <a href="delete_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contact?');">Delete</a>
                                        <a href="share.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Share</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-group">
                    <p>No contacts found.</p>
                    <a href="add_contact.php" class="btn btn-success">Add Contact</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>