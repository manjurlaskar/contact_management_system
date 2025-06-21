<?php
include 'connection.php';

// Query contact data
$query = "SELECT name, email, phone, group_name FROM contacts";
$data = mysqli_query($con, $query);

if (mysqli_num_rows($data) > 0) {
    // Set CSV headers
    $filename = "contacts_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output CSV data
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Name', 'Email', 'Phone', 'Group'));
    
    while ($row = mysqli_fetch_assoc($data)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
} else {
    echo "<script>
            alert('No contacts to export');
            window.location.href='index.php';
          </script>";
}
?>