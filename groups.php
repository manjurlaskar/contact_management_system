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
    <title>Contact Groups</title>
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
            <h2>Contact Groups</h2>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
            <a href="view_contacts.php" class="btn">View All Contacts</a>
            
            <div class="search-container">
                <input type="text" id="groupSearch" class="search-input" placeholder="Search groups...">
                <button onclick="searchGroups()" class="btn">Search</button>
            </div>
            <div id="groupCount"></div>
        </header>
        
        <div class="groups-container">
            <?php if(mysqli_num_rows($groups_result) > 0): ?>
                <?php while($group = mysqli_fetch_assoc($groups_result)): ?>
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
                            <?php if($contact_count > 0): ?>
                                <?php foreach ($contact_names as $name): ?>
                                    <li class="contact-item"><?php echo htmlspecialchars($name); ?></li>
                                <?php endforeach; ?>
                                <?php if($contact_count > 5): ?>
                                    <li class="contact-item">+<?php echo ($contact_count - 5); ?> more...</li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li class="contact-item">No contacts</li>
                            <?php endif; ?>
                        </ul>
                        <div class="group-actions">
                            <a href="view_group.php?group=<?php echo urlencode($group_name); ?>" class="btn">View Group</a>
                            <a href="javascript:void(0)"
                               onclick="if(confirm('Delete this group and all its contacts?')) {
                                   window.location.href='groups.php?delete_group=<?php echo urlencode($group_name); ?>'
                               }"
                               class="btn btn-secondary">Delete</a>
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

    <script>
      document.addEventListener('DOMContentLoaded', function() {
            const groupSearchInput = document.getElementById('groupSearch');
            const groupCards = document.querySelectorAll('.group-card');
            const groupCount = document.getElementById('groupCount');
            const groupsContainer = document.querySelector('.groups-container');
            
            // Store original state of group cards and container
            const originalGroupsHTML = groupsContainer.innerHTML;
            const originalGroupCount = groupCards.length;
            
            // Set initial group count
            groupCount.textContent = originalGroupCount + (originalGroupCount === 1 || originalGroupCount === 0 ? " group" : " groups");
            
            let debounceTimer;

            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function highlightMatch(text, term) {
                if (!term) return text;
                const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function restoreOriginalGroups() {
                groupsContainer.innerHTML = originalGroupsHTML;
            }

            function filterGroups(term) {
                // Restore original groups before each search
                restoreOriginalGroups();
                
                const groupCards = document.querySelectorAll('.group-card');
                const searchTerm = term.trim().toLowerCase();
                let visibleCount = 0;
                
                if (searchTerm.length === 0) {
                    groupCount.textContent = originalGroupCount + (originalGroupCount === 1 || originalGroupCount === 0 ? " group" : " groups");
                    return;
                }
                
                groupCards.forEach(card => {
                    const groupNameElement = card.querySelector('.group-name');
                    const originalName = groupNameElement.textContent;
                    const lowerName = originalName.toLowerCase();
                    
                    if (lowerName.includes(searchTerm)) {
                        groupNameElement.innerHTML = highlightMatch(originalName, searchTerm);
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                groupCount.textContent = visibleCount + (visibleCount === 1 || visibleCount === 0 ? " group" : " groups");
                
                if (visibleCount === 0) {
                    groupCount.innerHTML += `<div class="no-results">No matching groups found!</div>`;
                }
            }

            groupSearchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterGroups(this.value);
                }, 300);
            });
            
            groupSearchInput.focus();
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