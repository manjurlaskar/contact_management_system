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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_group_name']) && !$is_uncategorized) {
    $new_name = trim($_POST['new_group_name']);

    // Check if similar name exists with different case
    $existing_name_stmt = $con->prepare("SELECT DISTINCT group_name FROM contacts WHERE LOWER(group_name) = LOWER(?) LIMIT 1");
    $existing_name_stmt->bind_param("s", $new_name);
    $existing_name_stmt->execute();
    $existing_name_stmt->bind_result($matched_name);
    $matched_name = null;
    if ($existing_name_stmt->fetch()) {
        $new_name = $matched_name; // Use the existing group's case style
    }
    $existing_name_stmt->close();

    $rename_stmt = $con->prepare("UPDATE contacts SET group_name = ? WHERE group_name = ?");
    $rename_stmt->bind_param("ss", $new_name, $group);
    if ($rename_stmt->execute()) {
        echo "<script>alert('Group renamed successfully'); window.location.href='view_group.php?group=" . urlencode($new_name) . "';</script>";
        exit;
    } else {
        echo "<script>alert('Rename failed');</script>";
    }
    $rename_stmt->close();
}


// Fetch contacts
$rows = array();
if ($is_uncategorized) {
    $query = "SELECT id, name, email, phone, group_name FROM contacts WHERE $query_clause ORDER BY name";
    $result = mysqli_query($con, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
} else {
    $query = "SELECT id, name, email, phone, group_name FROM contacts WHERE $query_clause ORDER BY name";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $group);
    $stmt->execute();
    
    // Bind result variables to match the selected columns
    $stmt->bind_result($id, $name, $email, $phone, $group_name);
    while ($stmt->fetch()) {
        $rows[] = array(
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'group_name' => $group_name
        );
    }
    $stmt->close();
}

$contact_count = count($rows);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $display_group_name; ?> Group</title>
    <link rel="stylesheet" href="style.css">
<?php
$group_names = array("Family", "Friends", "Work"); // Default options

$result_groups = mysqli_query($con, "SELECT DISTINCT group_name FROM contacts WHERE group_name IS NOT NULL AND group_name != ''");
while ($row = mysqli_fetch_assoc($result_groups)) {
    $name = $row['group_name'];
    if (!in_array($name, $group_names, true)) {
        $group_names[] = $name;
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
            <h2>Contacts in "<?php echo $display_group_name; ?>"</h2>
            <div>
                <a href="groups.php" class="btn btn-secondary">Back to Groups</a>
                <a href="index.php" class="btn btn-primary">Home</a>
                <a href="add_contact.php" class="btn btn-success">Add Contact</a>
                <?php if (!$is_uncategorized): ?>
                    <button class="btn btn-primary" onclick="showRenameModal()">Rename</button>
                <?php endif; ?>
                <a href="javascript:void(0);" class="btn btn-danger"
                   onclick="if(confirm('Delete the entire group?')) location.href='view_group.php?delete_group=<?php echo urlencode($group); ?>'">
                    Delete
                </a>
            </div><br>
            <div class="search-container">
                <input type="text" id="contactSearch" class="search-input" placeholder="Search contacts...">
                <button onclick="searchContacts()" class="btn">Search</button>
            </div>
            <div id="contactCount"><?php echo $contact_count; ?> contact(s)</div>
        </div>
    </header>

   <?php if ($contact_count > 0): ?>
    <div id="contactsContainer">
    <table>
        <thead>
        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Group</th><th colspan="3">Actions</th></tr>
        </thead>
        <tbody>
<?php 
$current_letter = '';
foreach ($rows as $row):
    $first_letter = strtoupper(substr($row['name'], 0, 1));
    if ($first_letter !== $current_letter) {
        echo "<tr class='letter-header'><td colspan='7'>{$first_letter}</td></tr>";
        $current_letter = $first_letter;
    }
?>
    <tr class="contact-row">
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['phone']); ?></td>
        <td><?php echo htmlspecialchars($row['group_name']); ?></td>
        <td><a href="edit_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Edit</a></td>
        <td><a href="delete_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this contact?')">Delete</a></td>
        <td><a href="share.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Share</a></td>
    </tr>
<?php endforeach; ?>
</tbody>

        </table>
        </div>
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
        <form method="post">
    <input list="groupSuggestions" name="new_group_name" placeholder="New group name" required style="padding: 10px; width: 100%; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ccc;">
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

<script>
    function showRenameModal() {
        document.getElementById('renameModal').style.display = 'flex';
    }
    function hideRenameModal() {
        document.getElementById('renameModal').style.display = 'none';
    }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('contactSearch');
            const contactsContainer = document.getElementById('contactsContainer');
            const contactCount = document.getElementById('contactCount');
            const table = contactsContainer.querySelector('table');
            
            // Store initial state of the table
            const originalTableHTML = table ? table.outerHTML : '';
            const originalContactCount = <?php echo $contact_count; ?>;
            
            // Store all contact rows and headers
            const contactRows = Array.from(document.querySelectorAll('.contact-row'));
            const letterHeaders = Array.from(document.querySelectorAll('.letter-header'));
            
            let debounceTimer;

            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function restoreOriginalTable() {
                if (originalTableHTML) {
                    contactsContainer.innerHTML = originalTableHTML;
                } else {
                    contactsContainer.innerHTML = '<div class="empty-group"><p>No contacts in this group.</p></div>';
                }
            }

            function filterContacts(searchTerm) {
                // Restore original table before each search
                restoreOriginalTable();
                
                if (searchTerm.length === 0) {
                    contactCount.textContent = originalContactCount + " contact(s)";
                    return;
                }
                
                const term = searchTerm.toLowerCase();
                const rows = contactsContainer.querySelectorAll('.contact-row');
                const headers = contactsContainer.querySelectorAll('.letter-header');
                
                if (rows.length === 0) {
                    contactCount.textContent = "0 contact";
                    return;
                }

                let visibleCount = 0;
                const shownLetters = new Set();

                // First pass: process contact rows
                rows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const phone = row.cells[2].textContent;
                    
                    if (name.includes(term) || phone.includes(term)) {
                        visibleCount++;
                        row.cells[0].innerHTML = highlightText(row.cells[0].textContent, searchTerm);
                        row.cells[2].innerHTML = highlightText(row.cells[2].textContent, searchTerm);
                        
                        // Find corresponding letter header
                        let headerRow = row.previousElementSibling;
                        while (headerRow && !headerRow.classList.contains('letter-header')) {
                            headerRow = headerRow.previousElementSibling;
                        }
                        if (headerRow) {
                            shownLetters.add(headerRow.textContent);
                        }
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Second pass: hide unused headers
                headers.forEach(header => {
                    if (!shownLetters.has(header.textContent)) {
                        header.style.display = 'none';
                    }
                });

                contactCount.textContent = visibleCount + (visibleCount === 1 || visibleCount === 0 ? " contact" : " contacts");
                
                // Handle no results
                if (visibleCount === 0) {
                    contactsContainer.innerHTML = '<div class="no-results">No matches found</div>';
                }
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterContacts(this.value.trim());
                }, 300);
            });

            searchInput.focus();
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