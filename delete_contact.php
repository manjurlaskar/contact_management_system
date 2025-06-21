<?php
include 'connection.php';

// Validate and sanitize contact ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $query = "DELETE FROM contacts WHERE id='$id'";
    
    if (mysqli_query($con, $query)) {
        echo "<script>
                alert('Contact deleted successfully');
                window.location.href = 'view_contacts.php';
              </script>";
    } else {
        $error = addslashes(mysqli_error($con));
        echo "<script>alert('Failed to delete contact: $error');</script>";
    }
} else {
    echo "<script>
            alert('Invalid contact ID');
            window.location.href = 'view_contacts.php';
          </script>";
}
?>