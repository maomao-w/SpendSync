<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transactions_export.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array('Transaction ID', 'User ID', 'Category ID', 'Type', 'Amount', 'Transaction Date', 'Description', 'Status'));

$query = mysqli_query($conn, "SELECT transaction_id, user_id, category_id, type, amount, transaction_date, description, status FROM transactions WHERE user_id = '$user_id' ORDER BY transaction_date DESC");

while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>