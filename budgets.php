<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];
$month_year = date('Y-m-01'); 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_budget'])) {
    $amount = $_POST['amount'];
    $category_id = (int)$_POST['category_id'];

    $sql = "INSERT INTO budgets (user_id, category_id, amount, month_year) 
            VALUES ('$user_id', '$category_id', '$amount', '$month_year')";
            
    if (mysqli_query($conn, $sql)) {
        header("Location: budgets.php");
        exit();
    }
}

$total_budget_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM budgets WHERE user_id = '$user_id' AND month_year = '$month_year'");
$total_budget = mysqli_fetch_assoc($total_budget_q)['total'] ?? 0;

$total_spent_q = mysqli_query($conn, "SELECT SUM(t.amount) as total FROM transactions t INNER JOIN budgets b ON t.category_id = b.category_id WHERE t.user_id = '$user_id' AND t.type = 'Expense' AND DATE_FORMAT(t.transaction_date, '%Y-%m-01') = '$month_year' AND b.month_year = '$month_year'");
$total_spent = mysqli_fetch_assoc($total_spent_q)['total'] ?? 0;

$remaining_budget = $total_budget - $total_spent;
$spent_percent = ($total_budget > 0) ? min(100, round(($total_spent / $total_budget) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Budgets</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            background: '#f4f7f9', 
            foreground: '#1e293b', 
            card: 'rgba(255, 255, 255, 0.55)', 
            primary: '#2563eb',    
            secondary: '#10b981',  
            muted: '#94a3b8',      
            'muted-foreground': '#64748b', 
            border: 'rgba(255, 255, 255, 0.8)', 
            indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
            emerald: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669' },
            amber: { 50: '#fffbeb', 100: '#fef3c7', 500: '#f59e0b', 600: '#d97706' },
            rose: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48' },
            cyan: { 50: '#ecfeff', 100: '#cffafe', 500: '#06b6d4', 600: '#0891b2' },
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            title: ['Poppins', 'sans-serif'],
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            'wiggle': 'wiggle 1s ease-in-out infinite',
            'modal': 'modalFadeIn 0.3s ease-out forwards',
          },
          keyframes: {
            fadeInUp: {
              '0%': { opacity: 0, transform: 'translateY(20px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
            },
            wiggle: {
              '0%, 100%': { transform: 'rotate(-10deg)' },
              '50%': { transform: 'rotate(10deg)' },
            },
            modalFadeIn: {
              '0%': { opacity: 0, transform: 'scale(0.95) translateY(-20px)' },
              '100%': { opacity: 1, transform: 'scale(1) translateY(0)' },
            }
          }
        }
      }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base {
      body { @apply bg-background text-foreground font-sans antialiased; }
    }
    
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-blue-200 rounded-full; border: 1px solid rgba(0,0,0,0.02); }

    .dashboard-card {
      @apply bg-card border border-border rounded-[2rem] p-6 transition-all duration-500 ease-out;
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.05), inset 0 1px 0 rgba(255,255,255,0.9);
    }
    .dashboard-card:hover {
      @apply shadow-2xl -translate-y-2 scale-[1.01];
      box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.15), inset 0 1px 0 rgba(255,255,255,1);
    }

    body { background-color: #f4f7f9; }
    
    .glass-sidebar {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        margin: 1rem 0 1rem 1rem;
        border-radius: 2rem;
        height: calc(100% - 2rem) !important;
        border: 1px solid rgba(255,255,255,0.8);
        box-shadow: 0 10px 40px rgba(37,99,235,0.05);
    }
    
    body > div.flex-1 {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        margin: 1rem 1rem 1rem 1rem;
        border-radius: 2rem;
        height: calc(100% - 2rem) !important;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 10px 40px rgba(37,99,235,0.05);
    }

    @media (max-width: 768px) {
        .glass-sidebar { margin: 0; border-radius: 0; height: 100% !important; }
        body > div.flex-1 { margin: 0; border-radius: 0; height: 100% !important; }
    }

    .sidebar-link { @apply flex items-center gap-3 px-5 py-3.5 mx-4 rounded-2xl text-muted-foreground font-medium transition-all duration-300 ease-in-out; }
    .sidebar-link:hover { @apply bg-white/70 text-primary translate-x-2 shadow-sm border border-white; }
    .sidebar-link.active { @apply bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-bold shadow-lg shadow-blue-500/30 border border-white/20; }

    .gradient-card-1 { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.3); }

    .bg-blobs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; overflow: hidden; z-index: -2; pointer-events: none; }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: floatBlob 15s infinite alternate; }
    .blob-blue { background: #2563eb; width: 500px; height: 500px; top: -10%; left: -10%; }
    .blob-emerald { background: #10b981; width: 400px; height: 400px; bottom: -10%; right: -10%; animation-delay: -5s; }
    
    @keyframes floatBlob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }
    
    .delay-100 { animation-delay: 100ms; opacity: 0; }
    .delay-200 { animation-delay: 200ms; opacity: 0; }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="flex h-screen overflow-hidden bg-background relative">

  <div class="bg-blobs">
    <div class="blob blob-blue"></div>
    <div class="blob blob-emerald"></div>
  </div>

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
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link active"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
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
      </div>
      
      <div class="flex items-center gap-5 ml-auto">
        <div class="relative">
            <button id="notifButton" class="p-2.5 bg-white/70 backdrop-blur-md border border-white rounded-xl relative shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 text-slate-600 hover:text-blue-600">
                <i data-feather="bell" class="w-5 h-5 group-hover:animate-wiggle"></i>
                <span class="absolute top-1.5 right-1.5 flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border-2 border-white"></span>
                </span>
            </button>

            <div id="notifDropdown" class="hidden absolute right-0 mt-4 w-80 bg-white/90 backdrop-blur-2xl border border-white rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] z-50 overflow-hidden transform transition-all duration-300 opacity-0 translate-y-4">
                <div class="p-4 border-b border-white/60 flex justify-between items-center bg-white/40">
                    <h3 class="font-bold text-slate-800 font-title">Notifications</h3>
                    <span class="text-[10px] font-bold bg-blue-100 text-blue-600 px-2 py-1 rounded-full uppercase tracking-wider">3 New</span>
                </div>
                <div class="max-h-[300px] overflow-y-auto">
                    <div class="p-4 hover:bg-white/80 transition-colors border-b border-white/60 cursor-pointer group">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-500 shrink-0 group-hover:scale-110 transition-transform">
                                <i data-feather="shopping-bag" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-700 font-medium">New expense added: <span class="font-bold text-rose-500">₱1,500</span></p>
                                <p class="text-xs text-slate-400 mt-1">10 mins ago</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 border-t border-white/60 text-center bg-white/40">
                    <a href="#" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View All Notifications</a>
                </div>
            </div>
        </div>

        <a href="profile.php" class="h-11 w-11 rounded-xl bg-white/80 border-2 border-white overflow-hidden shadow-sm hover:shadow-md hover:scale-110 hover:-translate-y-1 transition-all duration-300">
          <img src="https://i.pravatar.cc/150?u=User" alt="Profile" class="w-full h-full object-cover">
        </a>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-6 sm:p-8">
      
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 animate-fade-in-up">
        <div>
          <h1 class="text-3xl font-bold font-title text-slate-800">Budgets</h1>
          <p class="text-muted-foreground text-sm font-medium mt-1">Manage your spending limits for this month.</p>
        </div>
        <button id="openModalBtn" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-emerald-500 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 text-white text-sm font-bold rounded-xl shadow-md transition-all duration-300">
          <i data-feather="plus" class="w-4 h-4"></i> Create Budget
        </button>
      </div>

      <div class="dashboard-card gradient-card-1 rounded-[2rem] p-8 text-white mb-8 shadow-xl relative overflow-hidden animate-fade-in-up delay-100 border-0">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/20 rounded-full blur-3xl"></div>
        <div class="relative z-10">
          <h2 class="text-blue-100 font-bold tracking-wider uppercase text-xs mb-2">Total Monthly Budget</h2>
          <div class="flex items-end gap-3 mb-6">
            <span class="text-5xl font-bold font-title tracking-tight">₱<?php echo number_format(max(0, $remaining_budget), 2); ?></span>
            <span class="text-blue-200 font-medium mb-1.5">/ ₱<?php echo number_format($total_budget, 2); ?></span>
          </div>
          <div class="space-y-3">
            <div class="flex justify-between text-sm font-bold text-white">
              <span><?php echo $spent_percent; ?>% Spent</span>
              <span>₱<?php echo number_format(max(0, $remaining_budget), 2); ?> Remaining</span>
            </div>
            <div class="h-4 bg-black/20 backdrop-blur-sm rounded-full overflow-hidden border border-white/20 shadow-inner">
              <div class="h-full bg-gradient-to-r from-emerald-400 to-white rounded-full transition-all duration-1000 relative" style="width: <?php echo $spent_percent; ?>%;">
                 <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <?php
        $budgets_q = mysqli_query($conn, "SELECT b.*, c.category_name FROM budgets b JOIN categories c ON b.category_id = c.category_id WHERE b.user_id = '$user_id' AND b.month_year = '$month_year'");
        $themes = [
    ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'bar' => 'bg-indigo-500', 'icon' => 'grid'],
    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'bar' => 'bg-emerald-500', 'icon' => 'shopping-cart'],
    ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'bar' => 'bg-amber-500', 'icon' => 'coffee'],
    ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'bar' => 'bg-rose-500', 'icon' => 'truck'],
    ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'bar' => 'bg-cyan-500', 'icon' => 'home'],
    ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'bar' => 'bg-blue-500', 'icon' => 'film'],
    ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'bar' => 'bg-purple-500', 'icon' => 'zap']
];

$delay_counter = 200; 

        while ($b = mysqli_fetch_assoc($budgets_q)) {
            $cid = $b['category_id'];
            $limit = $b['amount'];
            $cat_name = $b['category_name'] ?? 'Custom Category';
            $theme_index = $cid % count($themes);
            $theme = $themes[$theme_index];
            
            $cat_spent_q = mysqli_query($conn, "SELECT SUM(amount) as cat_spent FROM transactions WHERE user_id = '$user_id' AND category_id = '$cid' AND type = 'Expense' AND DATE_FORMAT(transaction_date, '%Y-%m-01') = '$month_year'");
            $cat_spent = mysqli_fetch_assoc($cat_spent_q)['cat_spent'] ?? 0;
            
            $cat_left = $limit - $cat_spent;
            $cat_percent = ($limit > 0) ? min(100, round(($cat_spent / $limit) * 100)) : 0;
            $over = ($cat_left < 0);
            
            $card_class = $over ? "dashboard-card border-rose-300 bg-rose-50/70" : "dashboard-card";
            $text_class = $over ? "text-rose-700" : "text-slate-800";
            $amount_class = $over ? "text-rose-600" : "text-slate-800";
            $status_msg = $over ? "₱" . number_format(abs($cat_left), 2) . " over budget" : "₱" . number_format($cat_left, 2) . " left • {$cat_percent}% used";
            $status_color = $over ? "text-rose-600 font-bold" : "text-slate-500 font-bold";
            $bar_color = $over ? "bg-rose-500" : $theme['bar'];
            $bar_bg = $over ? "bg-rose-200" : "bg-white/60 shadow-inner border border-white";

            echo "
            <div class='$card_class animate-fade-in-up' style='animation-delay: {$delay_counter}ms;'>
              <div class='flex justify-between items-start mb-5'>
                <div class='flex items-center gap-4'>
                  <div class='p-3 {$theme['bg']} {$theme['text']} rounded-xl shadow-sm border border-white'>
                    <i data-feather='{$theme['icon']}' class='w-5 h-5'></i>
                  </div>
                  <h3 class='font-bold font-title text-lg $text_class'>$cat_name</h3>
                </div>
              </div>
              <div class='flex items-end gap-2 mb-4'>
                <span class='text-2xl font-bold font-title $amount_class'>₱" . number_format($cat_spent, 2) . "</span>
                <span class='text-sm text-slate-500 font-medium mb-1'>/ ₱" . number_format($limit, 2) . "</span>
              </div>
              <div class='h-3 w-full $bar_bg rounded-full overflow-hidden mb-3 relative'>
                <div class='h-full $bar_color rounded-full transition-all duration-1000' style='width: {$cat_percent}%;'></div>
              </div>
              <p class='text-[11px] uppercase tracking-wider $status_color'>$status_msg</p>
            </div>";
            
            $delay_counter += 100;
        }
        ?>
      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="budgetModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Create New Budget</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white"><i data-feather="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Category Name</label>
                        <select name="category_id" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm cursor-pointer" required>
    <?php
    $cat_query = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE user_id = '$user_id' ORDER BY category_name ASC");
    while($cat = mysqli_fetch_assoc($cat_query)) {
        echo "<option value='{$cat['category_id']}'>{$cat['category_name']}</option>";
    }
    ?>
</select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Monthly Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                            <input type="number" step="0.01" name="amount" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                    <button type="button" id="cancelModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="add_budget" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Save Budget</button>
                </div>
            </form>
        </div>
    </div>
  </div>

  <script>
    feather.replace();
    
    const notifButton = document.getElementById('notifButton');
    const notifDropdown = document.getElementById('notifDropdown');

    notifButton.addEventListener('click', (e) => {
        e.stopPropagation(); 
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
                }, 300);
            }
        }
    });

    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        const isHidden = sidebar.classList.contains('-translate-x-full');
        if (isHidden) {
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

    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const budgetModal = document.getElementById('budgetModal');
    
    function openModal() { modalBackdrop.classList.remove('hidden'); budgetModal.classList.remove('hidden'); }
    function closeModal() { modalBackdrop.classList.add('hidden'); budgetModal.classList.add('hidden'); }
    
    openModalBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    cancelModalBtn?.addEventListener('click', closeModal);
    modalBackdrop?.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });

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
