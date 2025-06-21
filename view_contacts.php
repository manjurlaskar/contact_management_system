<?php
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contacts</title>
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
            <h2>Contact List</h2>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>

                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search contacts by name or phone..." autocomplete="off">
                    <button class="btn" onclick="searchContact()">Search</button>
                </div>
                <div id="contactCount">
                    <?php
                    $count_query = "SELECT COUNT(*) as total FROM contacts";
                    $count_result = mysqli_query($con, $count_query);
                    $count_row = mysqli_fetch_assoc($count_result);
                    echo $count_row['total'] . " contact(s)";
                    ?>
                </div>
        </header>
        
        <div id="contactsContainer">
            <?php
            $query = "SELECT * FROM contacts ORDER BY name ASC";
            $result = mysqli_query($con, $query);
            
            if (mysqli_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Group</th>
                            <th colspan="3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_letter = '';
                        while ($row = mysqli_fetch_assoc($result)):
                            $first_letter = strtoupper(substr($row['name'], 0, 1));
                            
                            if ($first_letter !== $current_letter):
                                echo "<tr class='letter-header'><td colspan='7'>{$first_letter}</td></tr>";
                                $current_letter = $first_letter;
                            endif;
                        ?>
                            <tr class="contact-row">
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['group_name']); ?></td>
                                <td><a href="edit_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Edit</a></td>
                                <td><a href="delete_contact.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contact?');">Delete</a></td>
                                <td><a href="share.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Share</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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
            const searchInput = document.getElementById('searchInput');
            const contactsContainer = document.getElementById('contactsContainer');
            const contactCount = document.getElementById('contactCount');
            const originalHTML = contactsContainer.innerHTML;
            let debounceTimer;

            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            function filterContacts(searchTerm) {
                if (searchTerm.length === 0) {
                    contactsContainer.innerHTML = originalHTML;
                    contactCount.textContent = document.querySelectorAll('.contact-row').length + " contact(s)";
                    return;
                }

                const term = searchTerm.toLowerCase();
                const rows = document.querySelectorAll('.contact-row');
                let visibleCount = 0;
                let currentLetter = '';
                let newHTML = '<table><tbody>';

                rows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const phone = row.cells[2].textContent;

                    if (name.includes(term) || phone.includes(term)) {
                        const firstLetter = name.charAt(0).toUpperCase();

                        if (firstLetter !== currentLetter) {
                            newHTML += `<tr class='letter-header'><td colspan='7'>${firstLetter}</td></tr>`;
                            currentLetter = firstLetter;
                        }

                        const newRow = row.cloneNode(true);
                        newRow.cells[0].innerHTML = highlightText(row.cells[0].textContent, searchTerm);
                        newRow.cells[2].innerHTML = highlightText(row.cells[2].textContent, searchTerm);
                        newHTML += newRow.outerHTML;
                        visibleCount++;
                    }
                });

                newHTML += '</tbody></table>';

                if (visibleCount === 0) {
                    contactsContainer.innerHTML = '<div class="no-results">No matches found</div>';
                } else {
                    contactsContainer.innerHTML = newHTML;
                }

                contactCount.textContent = visibleCount + (visibleCount === 1 || visibleCount === 0 ? " contact" : " contacts");
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterContacts(this.value.trim());
                }, 300);
            });

            searchInput.focus();


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
        });
    </script>
</body>
</html>
