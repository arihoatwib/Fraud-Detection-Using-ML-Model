<?php
require_once 'class.php';

$db = new db_class();
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        // File details
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_type = $_FILES['file']['type'];

        // Check if uploaded file is a CSV
        $allowed_types = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if (in_array($file_type, $allowed_types) && $ext == 'csv') {
            // Read CSV file and process data
            $handle = fopen($file_tmp, "r");
            if ($handle !== FALSE) {
                // Assuming the first row contains column names
                $header = fgetcsv($handle, 1000, ",");

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Combine header with row data to create an associative array
                    $transaction_data = array_combine($header, $data);

                    // Process each transaction for fraud detection
                    $db->process_csv_transaction($transaction_data);
                }
                fclose($handle);

                $message = "Data processed and inserted successfully!";
            } else {
                $message = "Error opening the file.";
            }
        } else {
            $message = "Please upload a valid CSV file.";
        }
    } else {
        $message = "No file uploaded or there was an error uploading the file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV File</title>
</head>
<body>
    <h2>Upload CSV File</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".csv" required>
        <button type="submit" name="submit">Upload</button>
    </form>
    <?php
    if ($message != "") {
        echo "<p>$message</p>";
    }
    ?>
</body>
</html>
