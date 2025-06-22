// script.js

document.addEventListener('DOMContentLoaded', function() {

    // --- Theme Toggle Functionality (Consolidated) ---
    const themeToggle = document.getElementById('themeToggle');
    const themeLabel = document.querySelector('.theme-label');
    const body = document.body;

    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Apply saved theme or system preference on load
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        body.classList.add('dark-mode');
        if (themeLabel) themeLabel.textContent = 'Dark Mode';
    } else {
        if (themeLabel) themeLabel.textContent = 'Light Mode';
    }

    // Add event listener for theme toggle button
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            if (themeLabel) themeLabel.textContent = isDarkMode ? 'Dark Mode' : 'Light Mode';
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        });
    }

    // --- Custom Group Input Toggle Functionality (Consolidated) ---
    // Handles logic for both add_contact.php and edit_contact.php
    function toggleCustomGroupVisibility() {
        // For Add Contact Page
        const groupSelectAdd = document.getElementById('groupSelect');
        const customGroupContainerAdd = document.getElementById('custom-group-container');
        const customGroupInputAdd = document.getElementById('customGroupInput');

        if (groupSelectAdd && customGroupContainerAdd && customGroupInputAdd) {
            if (groupSelectAdd.value === 'Other') {
                customGroupContainerAdd.style.display = 'block';
                customGroupInputAdd.setAttribute('required', 'required');
            } else {
                customGroupContainerAdd.style.display = 'none';
                customGroupInputAdd.removeAttribute('required');
                customGroupInputAdd.value = '';
            }
        }
    }
    // Initial call for add/edit contact page forms
    toggleCustomGroupVisibility();
    // Attach event listener to change for add/edit contact page forms
    const groupSelectElements = document.querySelectorAll('#groupSelect');
    groupSelectElements.forEach(selectElement => {
        selectElement.addEventListener('change', toggleCustomGroupVisibility);
    });

    // --- Phone Number Validation (Consolidated) ---
    const contactForm = document.querySelector('form');
    if (contactForm && (contactForm.action.includes('add_contact.php') || contactForm.action.includes('edit_contact.php'))) {
        contactForm.addEventListener('submit', function(e) {
            const phoneInput = contactForm.querySelector('input[name="phone"]');
            if (phoneInput) {
                const phone = phoneInput.value;
                // Original regex: /^\\d{10}$/
                if (!/^\d{10}$/.test(phone)) {
                    alert('Phone number must be exactly 10 digits.');
                    e.preventDefault();
                }
            }
        });
    }

    // --- Export Contacts Confirmation (index.php) ---
    const exportLink = document.getElementById('exportLink');
    if (exportLink) {
        exportLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to export all contacts?")) {
                window.location.href = 'export_contacts.php';
            }
        });
    }

    // --- Generic Search & Filter (for view_contacts.php and view_group.php) ---
    const searchInput = document.getElementById('searchInput') || document.getElementById('contactSearch');
    if (searchInput) {
        const contactsContainer = document.getElementById('contactsContainer');
        const contactCountDisplay = document.getElementById('contactCount');

        // Store original text content for highlighting reset
        const originalNameContents = new Map();
        const originalPhoneContents = new Map();
        
        // Store references to contact rows and letter headers
        let allContactRows = [];
        let allLetterHeaders = [];

        function captureOriginalContent() {
            if (contactsContainer) {
                allContactRows = Array.from(contactsContainer.querySelectorAll('.contact-row'));
                allLetterHeaders = Array.from(contactsContainer.querySelectorAll('.letter-header'));

                allContactRows.forEach(row => {
                    const nameCell = row.querySelector('td[data-label="Name"] span') || row.cells[0];
                    const phoneCell = row.querySelector('td[data-label="Phone"]') || row.cells[2];
                    if (nameCell) originalNameContents.set(row, nameCell.textContent);
                    if (phoneCell) originalPhoneContents.set(row, phoneCell.textContent);
                });
            }
        }

        // Capture original content once on load
        captureOriginalContent();

        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightText(text, searchTerm) {
            if (!searchTerm) return text;
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        function filterContacts(searchTerm) {
            const term = searchTerm.toLowerCase();
            let visibleCount = 0;
            const letterHeaderVisibility = new Map();

            // Reset all highlighting and show all rows/headers if search term is empty
            if (!term) {
                allContactRows.forEach(row => {
                    row.style.display = '';
                    const nameCell = row.querySelector('td[data-label="Name"] span') || row.cells[0];
                    const phoneCell = row.querySelector('td[data-label="Phone"]') || row.cells[2];
                    if (nameCell) nameCell.textContent = originalNameContents.get(row) || nameCell.textContent;
                    if (phoneCell) phoneCell.textContent = originalPhoneContents.get(row) || phoneCell.textContent;
                });
                allLetterHeaders.forEach(header => {
                    header.style.display = '';
                });
                if (contactCountDisplay) {
                    contactCountDisplay.textContent = allContactRows.length + (allContactRows.length === 1 ? " contact" : " contacts") + (window.location.href.includes('view_group.php') ? " in this group" : "");
                }
                // Clear "No matches found" message if it exists
                const noResultsDiv = contactsContainer.querySelector('.no-results');
                if (noResultsDiv) {
                    noResultsDiv.remove();
                }
                return;
            }

            // Initialize all letter headers as hidden
            allLetterHeaders.forEach(header => {
                header.style.display = 'none';
                letterHeaderVisibility.set(header.textContent.trim(), false);
            });

            allContactRows.forEach(row => {
                // Restore original text before filtering/highlighting for current row
                const nameCell = row.querySelector('td[data-label="Name"] span') || row.cells[0];
                const phoneCell = row.querySelector('td[data-label="Phone"]') || row.cells[2];

                if (nameCell) nameCell.textContent = originalNameContents.get(row);
                if (phoneCell) phoneCell.textContent = originalPhoneContents.get(row);
                
                const nameText = (nameCell ? nameCell.textContent : '').toLowerCase();
                const phoneText = (phoneCell ? phoneCell.textContent : '').toLowerCase();

                if (nameText.includes(term) || phoneText.includes(term)) {
                    row.style.display = ''; // Show row
                    visibleCount++;

                    // Apply highlighting to the current content
                    if (nameCell) nameCell.innerHTML = highlightText(nameCell.textContent, searchTerm);
                    if (phoneCell) phoneCell.innerHTML = highlightText(phoneCell.textContent, searchTerm);

                    // Mark associated letter header as visible
                    let headerFound = false;
                    let prevSibling = row.previousElementSibling;
                    while (prevSibling) {
                        if (prevSibling.classList.contains('letter-header')) {
                            letterHeaderVisibility.set(prevSibling.textContent.trim(), true);
                            headerFound = true;
                            break;
                        }
                        prevSibling = prevSibling.previousElementSibling;
                    }
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });

            // Apply visibility to letter headers based on the map
            allLetterHeaders.forEach(header => {
                if (letterHeaderVisibility.get(header.textContent.trim())) {
                    header.style.display = ''; // Show header
                } else {
                    header.style.display = 'none'; // Ensure hidden if no contacts match under it
                }
            });

            if (contactCountDisplay) {
                contactCountDisplay.textContent = visibleCount + (visibleCount === 1 ? " contact" : " contacts") + (window.location.href.includes('view_group.php') ? " in this group" : "");
            }

            // Handle "No matches found" message
            const existingNoResultsDiv = contactsContainer.querySelector('.no-results');
            if (visibleCount === 0) {
                if (!existingNoResultsDiv) {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.classList.add('no-results');
                    noResultsDiv.textContent = 'No matches found';
                    contactsContainer.appendChild(noResultsDiv);
                }
            } else {
                if (existingNoResultsDiv) {
                    existingNoResultsDiv.remove();
                }
            }
        }

        searchInput.addEventListener('input', function() {
            filterContacts(this.value.trim());
        });

        // Initial focus and filter if already some text in search box (e.g., after back button)
        searchInput.focus();
        if (searchInput.value.trim() !== '') {
            filterContacts(searchInput.value.trim());
        }
    }


    // --- Group Search & Filter (groups.php) ---
    const groupSearchInput = document.getElementById('groupSearch');
    if (groupSearchInput) {
        const groupsContainer = document.querySelector('.groups-container');
        const groupCountDisplay = document.getElementById('groupCount');

        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightText(text, searchTerm) {
            if (!searchTerm) return text;
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            return text.replace(regex, '<span class="highlight">$1</span>');
        }

        // Store original group names to restore for highlighting
        const originalGroupNames = new Map();
        if (groupsContainer) {
            groupsContainer.querySelectorAll('.group-card').forEach(card => {
                const groupNameElement = card.querySelector('.group-name');
                if (groupNameElement) {
                    originalGroupNames.set(card, groupNameElement.textContent);
                }
            });
        }


        function filterGroups(searchTerm) {
            const term = searchTerm.toLowerCase();
            const groupCards = groupsContainer ? Array.from(groupsContainer.querySelectorAll('.group-card')) : [];
            let visibleCount = 0;

            groupCards.forEach(card => {
                const groupNameElement = card.querySelector('.group-name');
                if (!groupNameElement) return;

                // Always use the original text content for filtering and highlighting
                const originalName = originalGroupNames.get(card) || groupNameElement.textContent; // Fallback to current text if map fails
                const lowerName = originalName.toLowerCase();

                if (lowerName.includes(term)) {
                    card.style.display = ''; // Show card
                    groupNameElement.innerHTML = highlightText(originalName, searchTerm); // Apply highlighting
                    visibleCount++;
                } else {
                    card.style.display = 'none'; // Hide card
                }
            });

            if (groupCountDisplay) {
                groupCountDisplay.textContent = visibleCount + (visibleCount === 1 ? " group" : " groups");
                if (visibleCount === 0) {
                    groupCountDisplay.textContent += " - No matching groups found!";
                }
            }

            // If no search term, ensure all highlighting is removed
            if (!searchTerm.trim() && groupsContainer) {
                groupsContainer.querySelectorAll('.group-card').forEach(card => {
                    const groupNameElement = card.querySelector('.group-name');
                    if (groupNameElement) {
                        groupNameElement.textContent = originalGroupNames.get(card) || groupNameElement.textContent;
                    }
                });
            }
        }

        groupSearchInput.addEventListener('input', function() {
            // Instant filtering, removed debounce
            filterGroups(this.value);
        });

        // Set initial group count on load and perform initial filter if text exists
        if (groupCountDisplay && groupsContainer) {
            const initialGroupCards = groupsContainer.querySelectorAll('.group-card');
            groupCountDisplay.textContent = initialGroupCards.length + (initialGroupCards.length === 1 ? " group" : " groups");
        }
        groupSearchInput.focus();
        if (groupSearchInput.value.trim() !== '') {
            filterGroups(groupSearchInput.value.trim());
        }
    }


    // --- Rename Group Modal Functionality (view_group.php) ---
    const renameModal = document.getElementById('renameModal');
    if (renameModal) {
        // Function to show the rename modal
        window.showRenameModal = function() {
            const currentGroupDisplayedElement = document.querySelector('h2');
            let currentGroupDisplayed = '';
            if (currentGroupDisplayedElement) {
                // Extract group name from "Contacts in 'Group Name'"
                const match = currentGroupDisplayedElement.textContent.match(/Contacts in '(.*?)'/);
                if (match && match[1]) {
                    currentGroupDisplayed = match[1];
                } else {
                    currentGroupDisplayed = currentGroupDisplayedElement.textContent; // Fallback
                }
            }
            
            const oldGroupNameInput = document.getElementById('oldGroupName');
            const newGroupNameInput = document.getElementById('newGroupName');

            if (oldGroupNameInput) {
                oldGroupNameInput.value = currentGroupDisplayed;
            }
            if (newGroupNameInput) {
                newGroupNameInput.value = ''; // Clear new group name input as requested
            }
            
            renameModal.style.display = 'flex';
        };

        // Function to hide the rename modal
        window.hideRenameModal = function() {
            renameModal.style.display = 'none';
        };
    }
});