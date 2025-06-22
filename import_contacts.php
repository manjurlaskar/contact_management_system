<?php
include 'connection.php';

if (isset($_POST['import'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if ($_FILES['csv_file']['size'] > 0) {
        $handle = fopen($file, "r");
        fgetcsv($handle); // Skip header row
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 4) continue;
            
            $name = mysqli_real_escape_string($con, $data[0]);
            $email = mysqli_real_escape_string($con, $data[1]);
            $phone = mysqli_real_escape_string($con, $data[2]);
            $group = mysqli_real_escape_string($con, $data[3]);
            
            $query = "INSERT INTO contacts (name, email, phone, group_name)
                      VALUES ('$name', '$email', '$phone', '$group')";
            mysqli_query($con, $query);
        }
        
        fclose($handle);
        echo "<script>
                alert('Contacts imported successfully!');
                window.location.href='view_contacts.php';
              </script>";
    } else {
        echo "<script>
                alert('Please select a CSV file to import.');
                window.location.href='import_contacts.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Contacts</title>
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
        <h2>Import Contacts</h2>
        <a href="index.php" class="btn btn-secondary">Back to Home</a>
        <br><br><hr><br>
        <form action="import_contacts.php" method="POST" enctype="multipart/form-data">
            
            <input type="file" name="csv_file" accept=".csv" required>
            <input type="submit" name="import" value="Import Contacts" class="btn">
        </form>
    </div>

    <script src="script.js"></script>
</body>
</html>