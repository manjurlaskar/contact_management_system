<?php
include 'connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM contacts WHERE id = '$id'";
    $data = mysqli_query($con, $query);
    $contact = mysqli_fetch_assoc($data);

    if ($contact) {
        $contactDetails = "Name: " . $contact['name'] . "\nPhone: " . $contact['phone'] . "\nEmail: " . $contact['email'];
        $encodedContactDetails = urlencode($contactDetails);
        
        $whatsappLink = "https://wa.me/?text=" . $encodedContactDetails;
        $smsLink = "sms:?body=" . $encodedContactDetails;
        $emailSubject = "Contact Information: " . urlencode($contact['name']);
        $emailBody = "Here are the details of the contact:\n\n" . $encodedContactDetails;
        $emailLink = "mailto:?subject=" . $emailSubject . "&body=" . $emailBody;
    } else {
        echo "<script>
                alert('Contact not found.');
                window.location.href='view_contacts.php';
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('No contact ID provided for sharing.');
            window.location.href='view_contacts.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Contact</title>
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
        <h2>Share Contact: <?php echo htmlspecialchars($contact['name']); ?></h2>
        <a href="view_contacts.php" class="btn btn-secondary">Back to Contacts</a>
        <br><br><hr><br>
        <p>Select how you want to share the contact:</p>

        <div class="share-options">
            <a href="<?php echo $whatsappLink; ?>" class="btn btn-success" target="_blank">WhatsApp</a>
            <a href="<?php echo $smsLink; ?>" class="btn btn-secondary" target="_blank">SMS</a>
            <a href="<?php echo $emailLink; ?>" class="btn btn-info" target="_blank">Email</a>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>