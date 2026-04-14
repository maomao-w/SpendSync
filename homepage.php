<?php
include 'session_manager.php';
include 'config.php';

mysqli_report(MYSQLI_REPORT_OFF);

$user_id = $_SESSION['user_id'];
$current_month = date('m');
$current_year = date('Y');

$user_query = @mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
$user_data = $user_query ? mysqli_fetch_assoc($user_query) : [];

$display_name = $user_data['full_name'] ?? $_SESSION['full_name'] ?? 'User';
$avatar_url = !empty($user_data['avatar']) ? $user_data['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($display_name).'&background=random';


$currency = $user_data['currency'] ?? 'PHP';
$sym = '₱';
$fx_rate = 1; // Base currency natin ay PHP

if ($currency != 'PHP') {
    if ($currency == 'USD') { $sym = '$'; $fx_rate = 0.0178; }
    elseif ($currency == 'EUR') { $sym = '€'; $fx_rate = 0.0166; }
    elseif ($currency == 'JPY') { $sym = '¥'; $fx_rate = 2.66; }
    
    $api_url = "https://api.exchangerate-api.com/v4/latest/PHP";
    
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = @curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http'=>['timeout'=>2]]);
        $response = @file_get_contents($api_url, false, $ctx);
    }
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['rates'][$currency])) {
            $fx_rate = $data['rates'][$currency]; 
        }
    }
}

$df_pref = $user_data['date_format'] ?? 'MM/DD/YYYY';
$php_df = 'm/d/Y';
if ($df_pref == 'DD/MM/YYYY') $php_df = 'd/m/Y';
elseif ($df_pref == 'MMM DD, YYYY') $php_df = 'M d, Y';

$pref_push = isset($user_data['pref_push']) ? $user_data['pref_push'] : 1;

if (isset($_GET['ajax_mark_read'])) {
    $total_tx_query = mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE user_id = '$user_id'");
    if ($total_tx_query) {
        $_SESSION['seen_tx_count'] = mysqli_fetch_assoc($total_tx_query)['c'];
    }
    @mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    $input_amount = (float)$_POST['amount'];
    
    $base_amount = $input_amount / $fx_rate; 

    $merchant = $_POST['merchant'] ?? ''; 
    $description = mysqli_real_escape_string($conn, $merchant); 
    $category_id = (int)$_POST['category_id']; 
    $date = $_POST['date'];
    $type = ($category_id == 5) ? 'Income' : 'Expense';
    
    $sql = "INSERT INTO transactions (user_id, category_id, type, amount, transaction_date, description, status) 
            VALUES ('$user_id', '$category_id', '$type', '$base_amount', '$date', '$description', 'Completed')";
    
    try {
        if (mysqli_query($conn, $sql)) {
            if ($pref_push == 1) { // Gagawa lang notif kung naka-ON sa settings
                try {
                    $formatted_amt = number_format($input_amount, 2);
                    $clean_desc = mysqli_real_escape_string($conn, $merchant);
                    $notif_msg = ($type == 'Income') ? "Income received: {$sym}{$formatted_amt} for $clean_desc" : "New expense added: {$sym}{$formatted_amt} for $clean_desc";
                    
                    $table_name = 'notifications';
                    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'notification'");
                    if ($check_table && mysqli_num_rows($check_table) > 0) $table_name = 'notification';
                    
                    mysqli_query($conn, "INSERT INTO $table_name (user_id, message, is_read) VALUES ('$user_id', '$notif_msg', 0)");
                } catch (Exception $e) { }
            }
            echo "<script>alert('Transaction saved successfully!'); window.location.href='homepage.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Database Error!'); window.location.href='homepage.php';</script>";
    }
}

$totals_query = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as total_expense
    FROM transactions 
    WHERE user_id = '$user_id' AND MONTH(transaction_date) = '$current_month' AND YEAR(transaction_date) = '$current_year'
");

if ($totals_query) {
    $totals = mysqli_fetch_assoc($totals_query);
    $total_income = (float)($totals['total_income'] ?? 0);
    $total_expense = (float)($totals['total_expense'] ?? 0);
} else {
    $total_income = 0;
    $total_expense = 0;
}

$monthly_budget_limit = 20000; 
$budget_used_percent = ($monthly_budget_limit > 0) ? round(($total_expense / $monthly_budget_limit) * 100) : 0;
$budget_remaining_percent = 100 - $budget_used_percent;
if ($budget_remaining_percent < 0) $budget_remaining_percent = 0;

$top_expense_query = mysqli_query($conn, "
    SELECT category_id, SUM(amount) as total_spent FROM transactions 
    WHERE user_id = '$user_id' AND type = 'Expense' AND MONTH(transaction_date) = '$current_month' AND YEAR(transaction_date) = '$current_year'
    GROUP BY category_id ORDER BY total_spent DESC LIMIT 1
");
$top_expense = mysqli_fetch_assoc($top_expense_query);
$cat_names = [1 => 'Electronics', 2 => 'Groceries', 3 => 'Food', 4 => 'Transport', 6 => 'Housing', 7 => 'Entertainment', 8 => 'Utilities'];

if ($top_expense) {
    $top_cat_name = $cat_names[$top_expense['category_id']] ?? 'Other';
    // CONVERT EXPENSE PARA SA INSIGHTS
    $top_cat_amount = number_format($top_expense['total_spent'] * $fx_rate, 2);
    $insight_1 = "You spent the most on <span class='font-bold text-rose-500'>{$top_cat_name} ({$sym}{$top_cat_amount})</span> this month.";
} else {
    $insight_1 = "No expenses recorded yet this month. Keep it up!";
}

if ($budget_used_percent >= 80) {
    $insight_2 = "You've used <span class='font-bold text-amber-500'>{$budget_used_percent}%</span> of your budget. Watch your spending!";
    $insight_2_icon = "alert-triangle";
    $insight_2_color = "text-amber-500";
    $insight_2_bg = "bg-amber-100";
} else {
    $insight_2 = "You've used <span class='font-bold text-emerald-500'>{$budget_used_percent}%</span> of your budget. Looking good!";
    $insight_2_icon = "check-circle";
    $insight_2_color = "text-emerald-500";
    $insight_2_bg = "bg-emerald-100";
}

$chart_labels = [];
$income_data = [];
$expense_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D', strtotime($date)); 
    $day_query = mysqli_query($conn, "SELECT SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END) as daily_income, SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END) as daily_expense FROM transactions WHERE user_id = '$user_id' AND DATE(transaction_date) = '$date'");
    if ($day_query) {
        $day_result = mysqli_fetch_assoc($day_query);
        // CONVERT CHART DATA SA SELECTED CURRENCY
        $income_data[] = ((float)($day_result['daily_income'] ?? 0)) * $fx_rate;
        $expense_data[] = ((float)($day_result['daily_expense'] ?? 0)) * $fx_rate;
    } else {
        $income_data[] = 0;
        $expense_data[] = 0;
    }
}

$notifications = [];
$total_tx_query = mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE user_id = '$user_id'");
$total_tx = $total_tx_query ? mysqli_fetch_assoc($total_tx_query)['c'] : 0;
if (!isset($_SESSION['seen_tx_count'])) { $_SESSION['seen_tx_count'] = $total_tx; }
$unread_count = max(0, $total_tx - $_SESSION['seen_tx_count']);

// IF PUSH NOTIF IS OFF, DISABLE RED DOTS
if ($pref_push == 0) {
    $unread_count = 0; 
} else {
    if ($budget_used_percent >= 80) {
        $notifications[] = [
            'icon' => 'alert-triangle',
            'color' => 'text-amber-500',
            'bg' => 'bg-amber-100',
            'message' => "Warning: You've reached <span class='font-bold text-amber-500'>{$budget_used_percent}%</span> of your monthly budget!",
            'time' => 'System Alert',
            'is_unread' => false 
        ];
    }
}

$recent_trans_query = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY transaction_date DESC, transaction_id DESC LIMIT 15");
if ($recent_trans_query) {
    $item_index = 0;
    while ($row = mysqli_fetch_assoc($recent_trans_query)) {
        $is_income = $row['type'] == 'Income';
        // CONVERT AMOUNT PARA SA RECENT TRANSACTIONS LIST
        $amount_formatted = $sym . number_format((float)$row['amount'] * $fx_rate, 2);
        $action_text = $is_income ? 'Income received:' : 'New expense added:';
        $color_class = $is_income ? 'text-emerald-500' : 'text-rose-500';
        
        $is_unread = ($pref_push == 1 && $item_index < $unread_count); 
        
        $notifications[] = [
            'icon' => $is_income ? 'arrow-down-left' : 'shopping-bag',
            'color' => $color_class,
            'bg' => $is_income ? 'bg-emerald-100' : 'bg-rose-100',
            'message' => "{$action_text} <span class='font-bold {$color_class}'>{$amount_formatted}</span> for " . htmlspecialchars($row['description']),
            'time' => date($php_df, strtotime($row['transaction_date'])),
            'is_unread' => $is_unread
        ];
        $item_index++;
    }
}
$notif_count = count($notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Analytics Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <script>
    tailwind.config = {
      theme: { extend: { colors: { background: '#f4f7f9', foreground: '#1e293b', card: 'rgba(255, 255, 255, 0.55)', primary: '#2563eb', secondary: '#10b981', muted: '#94a3b8', 'muted-foreground': '#64748b', border: 'rgba(255, 255, 255, 0.8)', }, fontFamily: { sans: ['Inter', 'sans-serif'], title: ['Poppins', 'sans-serif'], }, animation: { 'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards', 'wiggle': 'wiggle 1s ease-in-out infinite', 'modal': 'modalFadeIn 0.3s ease-out forwards', }, keyframes: { fadeInUp: { '0%': { opacity: 0, transform: 'translateY(20px)' }, '100%': { opacity: 1, transform: 'translateY(0)' }, }, wiggle: { '0%, 100%': { transform: 'rotate(-10deg)' }, '50%': { transform: 'rotate(10deg)' }, }, modalFadeIn: { '0%': { opacity: 0, transform: 'scale(0.95) translateY(-20px)' }, '100%': { opacity: 1, transform: 'scale(1) translateY(0)' }, } } } }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base { body { @apply bg-background text-foreground font-sans antialiased; } }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-blue-200 rounded-full; border: 1px solid rgba(0,0,0,0.02); }
    .dashboard-card { @apply bg-card border border-border rounded-[2rem] p-6 transition-all duration-500 ease-out; backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.05), inset 0 1px 0 rgba(255,255,255,0.9); }
    .dashboard-card:hover { @apply shadow-2xl -translate-y-2 scale-[1.01]; box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.15), inset 0 1px 0 rgba(255,255,255,1); }
    body { background-color: #f4f7f9; }
    .glass-sidebar { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(30px); -webkit-backdrop-filter: blur(30px); margin: 1rem 0 1rem 1rem; border-radius: 2rem; height: calc(100% - 2rem) !important; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 40px rgba(37,99,235,0.05); }
    body > div.flex-1 { background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); margin: 1rem 1rem 1rem 1rem; border-radius: 2rem; height: calc(100% - 2rem) !important; border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 10px 40px rgba(37,99,235,0.05); }
    @media (max-width: 768px) { .glass-sidebar { margin: 0; border-radius: 0; height: 100% !important; } body > div.flex-1 { margin: 0; border-radius: 0; height: 100% !important; } }
    .sidebar-link { @apply flex items-center gap-3 px-5 py-3.5 mx-4 rounded-2xl text-muted-foreground font-medium transition-all duration-300 ease-in-out; }
    .sidebar-link:hover { @apply bg-white/70 text-primary translate-x-2 shadow-sm border border-white; }
    .sidebar-link.active { @apply bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-bold shadow-lg shadow-blue-500/30 border border-white/20; }
    .gradient-card-1 { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.3); }
    .gradient-card-2 { background: linear-gradient(135deg, #34d399 0%, #10b981 100%); box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.3); }
    .bg-blobs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; overflow: hidden; z-index: -2; pointer-events: none; }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: floatBlob 15s infinite alternate; }
    .blob-blue { background: #2563eb; width: 500px; height: 500px; top: -10%; left: -10%; }
    .blob-emerald { background: #10b981; width: 400px; height: 400px; bottom: -10%; right: -10%; animation-delay: -5s; }
    @keyframes floatBlob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }
    .calendar-day { @apply w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-colors cursor-pointer; }
    .calendar-day:hover { @apply bg-blue-100 text-blue-700 scale-110; }
    .calendar-day.active { @apply bg-gradient-to-r from-blue-600 to-emerald-500 text-white shadow-md font-bold hover:scale-110; }
    .calendar-day.muted { @apply text-slate-300; }
  </style>
</head>
<body class="flex h-screen overflow-hidden bg-background relative">

  <div class="bg-blobs"><div class="blob blob-blue"></div><div class="blob blob-emerald"></div></div>
  <div id="webgl-container" class="absolute inset-0 z-[-1]"></div>
  <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 md:relative md:flex w-64 flex-col glass-sidebar transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-24 flex items-center px-8 border-b border-white/60">
      <div class="flex items-center gap-3 font-bold text-2xl font-title tracking-tight hover:scale-105 transition-transform cursor-pointer">
        <img src="https://drive.google.com/thumbnail?id=13ytZRfqfaNqIRB3-pU4ATaSt_dJFUEGH&sz=w1000" alt="SpendSync Logo" class="h-8 w-auto object-contain">
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
      </div>
    </div>
    
    <nav class="flex-1 py-8 space-y-3 overflow-y-auto overflow-x-hidden">
      <a href="homepage.php" class="sidebar-link active"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
      <a href="categories.php" class="sidebar-link"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>
      <a href="goals.php" class="sidebar-link"><i data-feather="target" class="w-5 h-5"></i> Goals</a>
      <a href="reports.php" class="sidebar-link"><i data-feather="file-text" class="w-5 h-5"></i> Reports</a>
      <a href="export_csv.php" class="sidebar-link"><i data-feather="download" class="w-5 h-5"></i> Download Records CSV</a>
      
      <form action="import_csv.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
        <label class="sidebar-link cursor-pointer w-full flex items-center gap-3 m-0">
          <i data-feather="upload" class="w-5 h-5"></i> <span class="flex-1">Import CSV</span>
          <input type="file" name="csv_file" accept=".csv" class="hidden" onchange="this.form.submit()">
          <input type="hidden" name="import" value="1">
        </label>
      </form>
      
      <div class="pt-6 mt-6 border-t border-white/60">
        <a href="settings.php" class="sidebar-link"><i data-feather="settings" class="w-5 h-5"></i> Settings</a>
      </div>
    </nav>
    
    <div class="p-4 mb-4">
      <a href="logout.php" class="sidebar-link hover:!bg-rose-50 hover:!text-rose-600 text-rose-500 font-bold">
        <i data-feather="log-out" class="w-5 h-5"></i> Logout
      </a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative z-10">
    <header class="h-24 flex items-center justify-between px-8 sm:px-10 shrink-0 z-10 border-b border-white/40">
      <div class="flex items-center gap-4">
        <button id="menuButton" class="md:hidden p-2.5 bg-white/60 backdrop-blur-md rounded-xl border border-white text-slate-700">
          <i data-feather="menu" class="w-6 h-6"></i>
        </button>
        <div class="hidden sm:block animate-fade-in-up">
          <h1 class="text-2xl font-bold font-title text-slate-800">Hello, <?php echo htmlspecialchars($display_name); ?></h1>
          <p class="text-muted-foreground text-sm font-medium">Welcome back to your dashboard!</p>
        </div>
      </div>
      
      <div class="flex items-center gap-4 sm:gap-5 ml-auto">
        <button type="button" id="openAddTxModalBtn" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300 text-sm font-bold">
          <i data-feather="plus" class="w-4 h-4"></i>
          <span class="hidden sm:inline">Add Transaction</span>
        </button>

        <div class="relative">
            <button id="notifButton" class="p-2.5 bg-white/70 backdrop-blur-md border border-white rounded-xl relative shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 text-slate-600 hover:text-blue-600">
                <i data-feather="bell" class="w-5 h-5 group-hover:animate-wiggle"></i>
                <?php if ($unread_count > 0 && $pref_push == 1): ?>
                <span class="absolute top-1.5 right-1.5 flex h-3 w-3 notif-red-dot">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border-2 border-white"></span>
                </span>
                <?php endif; ?>
            </button>

            <div id="notifDropdown" class="hidden absolute right-0 mt-4 w-80 bg-white/90 backdrop-blur-2xl border border-white rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] z-50 overflow-hidden transform transition-all duration-300 opacity-0 translate-y-4">
                <div class="p-4 border-b border-slate-200/50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 font-title">Notifications</h3>
                    <div class="flex items-center gap-3">
                        <?php if ($unread_count > 0 && $pref_push == 1): ?>
                            <span class="text-[10px] font-bold bg-blue-100 text-blue-600 px-2 py-1 rounded-full uppercase tracking-wider notif-count-badge"><?php echo $unread_count; ?> New</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="notifListContainer" class="max-h-[300px] overflow-y-auto transition-all duration-500 relative">
                    <?php if ($notif_count > 0): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="p-4 hover:bg-white transition-colors border-b border-slate-100 cursor-pointer group relative">
                                <div class="flex gap-3 pr-4">
                                    <div class="w-8 h-8 rounded-full <?php echo $notif['bg']; ?> flex items-center justify-center <?php echo $notif['color']; ?> shrink-0 group-hover:scale-110 transition-transform">
                                        <i data-feather="<?php echo $notif['icon']; ?>" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-700 font-medium"><?php echo $notif['message']; ?></p>
                                        <p class="text-xs text-slate-400 mt-1"><?php echo $notif['time']; ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($notif['is_unread'])): ?>
                                <div class="notif-item-red-dot absolute right-4 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-rose-500 shadow-sm"></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-sm text-slate-500">No new notifications.</div>
                    <?php endif; ?>
                </div>
                <div id="viewAllWrapper" class="p-3 border-t border-slate-200/50 text-center bg-slate-50/50">
                    <button type="button" id="viewAllNotifsBtn" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View All Notifications</button>
                </div>
            </div>
        </div>

        <a href="profile.php" class="h-11 w-11 rounded-xl bg-white/80 border-2 border-white overflow-hidden shadow-sm hover:shadow-md hover:scale-110 hover:-translate-y-1 transition-all duration-300">
          <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Profile" class="w-full h-full object-cover">
        </a>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-6 sm:p-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="dashboard-card gradient-card-1 text-white relative overflow-hidden flex flex-col justify-center border-0 animate-fade-in-up">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
                <div class="relative z-10 group">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-4 backdrop-blur-sm border border-white/30 group-hover:rotate-12 transition-transform duration-300">
                        <i data-feather="arrow-down-left" class="w-5 h-5 text-white"></i>
                    </div>
                    <p class="text-blue-100 font-medium mb-1">Total Income (Month)</p>
                    <h3 class="text-4xl font-bold font-title mb-2 tracking-tight"><?php echo $sym; ?> <?php echo number_format($total_income * $fx_rate, 2); ?></h3>
                </div>
            </div>

            <div class="dashboard-card gradient-card-2 text-white relative overflow-hidden flex flex-col justify-center border-0 animate-fade-in-up delay-100">
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
                <div class="relative z-10 group">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-4 backdrop-blur-sm border border-white/30 group-hover:-rotate-12 transition-transform duration-300">
                        <i data-feather="arrow-up-right" class="w-5 h-5 text-white"></i>
                    </div>
                    <p class="text-emerald-100 font-medium mb-1">Total Expenses (Month)</p>
                    <h3 class="text-4xl font-bold font-title mb-2 tracking-tight"><?php echo $sym; ?> <?php echo number_format($total_expense * $fx_rate, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="dashboard-card flex flex-col justify-center items-center relative animate-fade-in-up delay-200">
          <div class="w-full flex justify-between items-center mb-2 absolute top-6 left-6 right-6">
            <h3 class="text-base font-bold font-title text-slate-800">Overall Budget</h3>
            <i data-feather="more-horizontal" class="w-5 h-5 text-slate-400 cursor-pointer hover:text-slate-600 transition-colors"></i>
          </div>
          <div class="absolute w-32 h-32 bg-gradient-to-tr from-blue-400 to-emerald-300 rounded-full blur-2xl opacity-30 mt-6 animate-pulse"></div>
          
          <div class="relative w-44 h-44 mt-8 hover:scale-105 transition-transform duration-500">
            <canvas id="budgetProgressChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-3xl font-bold font-title text-slate-800"><?php echo $budget_used_percent; ?>%</span>
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Used</span>
            </div>
          </div>
        </div>
      </div>

      <div class="dashboard-card mb-6 animate-fade-in-up delay-200">
          <div class="flex justify-between items-center mb-6">
              <h3 class="text-base font-bold font-title text-slate-800 flex items-center gap-2">
                  <i data-feather="lightbulb" class="w-5 h-5 text-amber-500"></i> Financial Insights
              </h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex gap-4 items-start p-4 bg-white/40 border border-white rounded-2xl shadow-sm hover:bg-white/60 transition-colors">
                  <div class="p-2 bg-rose-100 rounded-xl text-rose-600 shadow-sm shrink-0">
                      <i data-feather="trending-down" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <p class="text-sm font-bold text-slate-700">Top Expense</p>
                      <p class="text-xs font-medium text-slate-500 mt-1"><?php echo $insight_1; ?></p>
                  </div>
              </div>
              <div class="flex gap-4 items-start p-4 bg-white/40 border border-white rounded-2xl shadow-sm hover:bg-white/60 transition-colors">
                  <div class="p-2 <?php echo $insight_2_bg; ?> rounded-xl <?php echo $insight_2_color; ?> shadow-sm shrink-0">
                      <i data-feather="<?php echo $insight_2_icon; ?>" class="w-5 h-5"></i>
                  </div>
                  <div>
                      <p class="text-sm font-bold text-slate-700">Budget Health</p>
                      <p class="text-xs font-medium text-slate-500 mt-1"><?php echo $insight_2; ?></p>
                  </div>
              </div>
          </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
          
        <div class="xl:col-span-2 dashboard-card flex flex-col animate-fade-in-up delay-300">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 cursor-pointer group">
                        <div class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)] group-hover:scale-150 transition-transform"></div>
                        <span class="text-sm font-bold text-slate-700">Income</span>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer group">
                        <div class="w-3 h-3 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.6)] group-hover:scale-150 transition-transform"></div>
                        <span class="text-sm font-bold text-slate-700">Expenses</span>
                    </div>
                </div>
                <div class="bg-white/60 border border-white px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 flex items-center gap-2 hover:bg-white hover:shadow-sm transition-all cursor-pointer">
                    <i data-feather="calendar" class="w-3 h-3"></i> Last 7 Days
                </div>
            </div>
            <div class="flex-1 w-full h-[250px] relative">
                <canvas id="smoothAreaChart"></canvas>
            </div>
        </div>

        <div class="dashboard-card flex flex-col animate-fade-in-up delay-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-bold font-title text-slate-800">Calendar</h3>
                <div class="flex gap-2 text-slate-400">
                    <div class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-blue-50 cursor-pointer hover:text-blue-600 transition-colors"><i data-feather="chevron-left" class="w-4 h-4"></i></div>
                    <div class="w-6 h-6 flex items-center justify-center rounded-md hover:bg-blue-50 cursor-pointer hover:text-blue-600 transition-colors"><i data-feather="chevron-right" class="w-4 h-4"></i></div>
                </div>
            </div>
            <p class="text-xs font-bold text-blue-600 mb-4"><?php echo date('F, Y'); ?></p>
            
            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 mb-3">
                <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
            </div>
            <div class="grid grid-cols-7 gap-y-3 gap-x-1 text-center text-sm font-bold text-slate-700">
                <?php
                $days_in_month = date('t');
                $start_day = date('w', strtotime(date('Y-m-01')));
                $prev_month_days = date('t', strtotime('-1 month'));
                $today = date('j');
                for ($x = $start_day - 1; $x >= 0; $x--) {
                    $d = $prev_month_days - $x;
                    echo "<div class='calendar-day muted'>$d</div>";
                }
                for ($d = 1; $d <= $days_in_month; $d++) {
                    $active_class = ($d == $today) ? 'active' : '';
                    echo "<div class='calendar-day $active_class'>$d</div>";
                }
                ?>
            </div>
        </div>
      </div>

    </main>

    <div id="addTxModalBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="addTxModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Add Transaction</h2>
                <button id="closeAddTxModalBtn" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white"><i data-feather="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold"><?php echo $sym; ?></span>
                            <input type="number" step="0.01" name="amount" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Merchant / Description</label>
                        <input type="text" name="merchant" placeholder="e.g. Apple Store" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Category</label>
                            <select name="category_id" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
    <option value="" disabled selected>Select a category</option>
    <?php
    // Fetch system defaults AND the logged-in user's custom categories
    $cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE user_id IS NULL OR user_id = '$user_id' ORDER BY type DESC, category_name ASC");
    while($cat = mysqli_fetch_assoc($cat_query)) {
        echo "<option value='" . $cat['category_id'] . "'>" . htmlspecialchars($cat['category_name']) . " (" . $cat['type'] . ")</option>";
    }
    ?>
</select>

                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Date</label>
                            <input type="date" name="date" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                    <button type="button" id="cancelAddTxModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="add_transaction" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>

  </div>

  <script>
    feather.replace();

    const openAddTxModalBtn = document.getElementById('openAddTxModalBtn');
    const closeAddTxModalBtn = document.getElementById('closeAddTxModalBtn');
    const cancelAddTxModalBtn = document.getElementById('cancelAddTxModalBtn');
    const addTxModalBackdrop = document.getElementById('addTxModalBackdrop');
    const addTxModal = document.getElementById('addTxModal');

    function openAddTxModal() { addTxModalBackdrop.classList.remove('hidden'); addTxModal.classList.remove('hidden'); }
    function closeAddTxModal() { addTxModalBackdrop.classList.add('hidden'); addTxModal.classList.add('hidden'); }

    openAddTxModalBtn?.addEventListener('click', openAddTxModal);
    closeAddTxModalBtn?.addEventListener('click', closeAddTxModal);
    cancelAddTxModalBtn?.addEventListener('click', closeAddTxModal);
    addTxModalBackdrop?.addEventListener('click', (e) => { if (e.target === addTxModalBackdrop) closeAddTxModal(); });

    const notifButton = document.getElementById('notifButton');
    const notifDropdown = document.getElementById('notifDropdown');
    const viewAllNotifsBtn = document.getElementById('viewAllNotifsBtn');
    const notifListContainer = document.getElementById('notifListContainer');
    const viewAllWrapper = document.getElementById('viewAllWrapper');

    notifButton.addEventListener('click', (e) => {
        e.stopPropagation(); 
        document.querySelectorAll('.notif-red-dot, .notif-item-red-dot, .notif-count-badge').forEach(el => { el.style.display = 'none'; });
        fetch('homepage.php?ajax_mark_read=1');

        if (notifDropdown.classList.contains('hidden')) {
            notifDropdown.classList.remove('hidden');
            setTimeout(() => {
                notifDropdown.classList.remove('opacity-0', 'translate-y-4');
                notifDropdown.classList.add('opacity-100', 'translate-y-0');
            }, 10);
        } else {
            notifDropdown.classList.remove('opacity-100', 'translate-y-0');
            notifDropdown.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => {
                notifDropdown.classList.add('hidden');
                notifListContainer.classList.remove('max-h-[60vh]');
                notifListContainer.classList.add('max-h-[300px]');
                if(viewAllWrapper) viewAllWrapper.style.display = 'block';
            }, 300); 
        }
    });

    document.addEventListener('click', (e) => {
        if (!notifButton.contains(e.target) && !notifDropdown.contains(e.target)) {
            if (!notifDropdown.classList.contains('hidden')) {
                notifDropdown.classList.remove('opacity-100', 'translate-y-0');
                notifDropdown.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => {
                    notifDropdown.classList.add('hidden');
                    notifListContainer.classList.remove('max-h-[60vh]');
                    notifListContainer.classList.add('max-h-[300px]');
                    if(viewAllWrapper) viewAllWrapper.style.display = 'block';
                }, 300);
            }
        }
    });

    if (viewAllNotifsBtn) {
        viewAllNotifsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifListContainer.classList.remove('max-h-[300px]');
            notifListContainer.classList.add('max-h-[60vh]');
            viewAllWrapper.style.display = 'none'; 
        });
    }

    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar.classList.contains('-translate-x-full')) {
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            sidebar.classList.remove('-translate-x-full');
        } else {
            overlay.classList.add('opacity-0');
            sidebar.classList.add('-translate-x-full');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    }
    menuButton?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', toggleSidebar);

    const chartLabels = <?php echo json_encode($chart_labels); ?>;
    const incomeData = <?php echo json_encode($income_data); ?>;
    const expenseData = <?php echo json_encode($expense_data); ?>;
    const budgetUsed = <?php echo $budget_used_percent; ?>;
    const budgetRemaining = <?php echo $budget_remaining_percent; ?>;

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8'; 
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(255, 255, 255, 0.95)'; 
    Chart.defaults.plugins.tooltip.titleColor = '#1e293b';
    Chart.defaults.plugins.tooltip.bodyColor = '#1e293b';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(37, 99, 235, 0.2)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 12;

    const ctxArea = document.getElementById('smoothAreaChart').getContext('2d');
    let gradIncome = ctxArea.createLinearGradient(0, 0, 0, 250);
    gradIncome.addColorStop(0, 'rgba(37, 99, 235, 0.5)'); 
    gradIncome.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    let gradExpense = ctxArea.createLinearGradient(0, 0, 0, 250);
    gradExpense.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); 
    gradExpense.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctxArea, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
            {
                label: 'Income',
                data: incomeData,
                borderColor: '#2563eb',
                backgroundColor: gradIncome,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Expenses',
                data: expenseData,
                borderColor: '#34d399',
                backgroundColor: gradExpense,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#34d399',
                pointBorderWidth: 2,
                pointRadius: 0, 
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }, 
            scales: {
                y: { display: false, min: 0 }, 
                x: { 
                    grid: { display: false, drawBorder: false }, 
                    ticks: { font: { weight: '600' }, color: '#94a3b8' }
                }
            },
            interaction: { mode: 'index', intersect: false },
            animation: { duration: 2000, easing: 'easeOutQuart' }
        }
    });

    const ctxBudget = document.getElementById('budgetProgressChart').getContext('2d');
    new Chart(ctxBudget, {
        type: 'doughnut', 
        data: {
            labels: ['Used', 'Remaining'],
            datasets: [{
                data: [budgetUsed, budgetRemaining],
                backgroundColor: ['#2563eb', 'rgba(37, 99, 235, 0.1)'],
                borderWidth: 0,
                borderRadius: 20, 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '82%', 
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            animation: { animateScale: true, duration: 1500 }
        }
    });

    try {
        const container = document.getElementById('webgl-container');
        const scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0xf4f7f9, 0.035);

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); 
        container.appendChild(renderer.domElement);

        scene.add(new THREE.AmbientLight(0xffffff, 1.2));
        const mainLight = new THREE.DirectionalLight(0xffffff, 1.5);
        mainLight.position.set(10, 20, 15);
        scene.add(mainLight);

        const blueDataMat = new THREE.MeshStandardMaterial({ color: 0x2563eb, roughness: 0.2, metalness: 0.5 });
        const emeraldCashMat = new THREE.MeshStandardMaterial({ color: 0x10b981, roughness: 0.5, metalness: 0.1 });
        const goldCoinMat = new THREE.MeshStandardMaterial({ color: 0xffd700, roughness: 0.2, metalness: 0.9 });

        const bgObjects = [];
        function spawnObject(geo, mat, yPosRange) {
            const mesh = new THREE.Mesh(geo, mat);
            mesh.position.set((Math.random() - 0.5) * 24, yPosRange, (Math.random() - 0.5) * 12 - 5);
            mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
            scene.add(mesh);
            bgObjects.push({
                mesh: mesh, rotX: (Math.random() - 0.5) * 0.02, rotY: (Math.random() - 0.5) * 0.02,
                floatSpeed: Math.random() * 0.01 + 0.005, initialY: mesh.position.y
            });
            return mesh;
        }

        for(let i = 0; i < 40; i++) {
            const randomType = Math.random();
            const yPos = 10 - (Math.random() * 25); 
            
            if(randomType < 0.33) {
                const coin = spawnObject(new THREE.CylinderGeometry(0.5, 0.5, 0.1, 32), goldCoinMat, yPos);
                coin.rotation.x = Math.PI / 2;
            } else if (randomType < 0.66) {
                spawnObject(new THREE.BoxGeometry(1.2, 0.6, 0.1), emeraldCashMat, yPos);
            } else {
                spawnObject(new THREE.OctahedronGeometry(0.6, 0), blueDataMat, yPos);
            }
        }

        camera.position.z = 9; camera.position.y = 2; 

        const clock = new THREE.Clock();
        let mouseX = 0, mouseY = 0;
        const windowHalfX = window.innerWidth / 2, windowHalfY = window.innerHeight / 2;

        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX - windowHalfX) * 0.001;
            mouseY = (event.clientY - windowHalfY) * 0.001;
        });

        function animate() {
            requestAnimationFrame(animate);
            const elapsedTime = clock.getElapsedTime();
            camera.position.x += (mouseX * 2 - camera.position.x) * 0.05;
            camera.position.y += (2 + -mouseY * 2 - camera.position.y) * 0.05;
            camera.lookAt(0, 0, 0);
            
            bgObjects.forEach((obj) => {
                obj.mesh.rotation.x += obj.rotX;
                obj.mesh.rotation.y += obj.rotY;
                obj.mesh.position.y = obj.initialY + Math.sin(elapsedTime * obj.floatSpeed * 50) * 0.8;
            });
            renderer.render(scene, camera);
        }
        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    } catch(e) { console.error("Three.js Error:", e); }
  </script>
</body>
</html>