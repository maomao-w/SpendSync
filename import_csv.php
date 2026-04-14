<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];

if (isset($_POST["import"])) {
    $fileName = $_FILES["csv_file"]["tmp_name"];
    
    if ($_FILES["csv_file"]["size"] > 0) {
        $fileExt = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        
        if ($fileExt !== 'csv') {
            header("Location:homepage.php?import=invalid_format");
            exit();
        }

        $file = fopen($fileName, "r");
        
        fgetcsv($file, 10000, ",");

        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            
            if (count($column) < 8) {
                continue; 
            }

            $category_id = mysqli_real_escape_string($conn, $column[2]);
            $type = mysqli_real_escape_string($conn, $column[3]);
            $amount = mysqli_real_escape_string($conn, $column[4]);
            $transaction_date = mysqli_real_escape_string($conn, $column[5]);
            $description = mysqli_real_escape_string($conn, $column[6]);
            $status = mysqli_real_escape_string($conn, $column[7]);

            $sqlInsert = "INSERT INTO transactions (user_id, category_id, type, amount, transaction_date, description, status) 
                          VALUES ('$user_id', '$category_id', '$type', '$amount', '$transaction_date', '$description', '$status')";
            
            mysqli_query($conn, $sqlInsert);
        }
        
        fclose($file);
        
        header("Location: homepage.php?import=success");
        exit();
    }
}

header("Location: homepage.php?import=failed");
exit();
?>