<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_transaction'])) {
    $trans_id = (int)$_POST['transaction_id'];
    mysqli_query($conn, "DELETE FROM transactions WHERE transaction_id = '$trans_id' AND user_id = '$user_id'");
    // Binago ang alert at pinalitan ng redirect parameter para ma-trigger ang custom UI pop-up
    echo "<script>window.location.href='transactions.php?status=deleted';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    $amount = $_POST['amount'];
    $merchant = $_POST['merchant'] ?? ''; 
    $description = mysqli_real_escape_string($conn, $merchant); 
    $category_id = (int)$_POST['category_id']; 
    $date = $_POST['date'];
    $type = ($category_id == 5) ? 'Income' : 'Expense';

    $sql = "INSERT INTO transactions (user_id, category_id, type, amount, transaction_date, description, status) 
            VALUES ('$user_id', '$category_id', '$type', '$amount', '$date', '$description', 'Completed')";

    try {
        if (mysqli_query($conn, $sql)) {
            try {
                $amt_float = (float)$amount;
                $formatted_amt = number_format($amt_float, 2);
                $clean_desc = mysqli_real_escape_string($conn, $merchant);
                $notif_msg = ($type == 'Income') ? "Income received: ₱$formatted_amt for $clean_desc" : "New expense added: ₱$formatted_amt for $clean_desc";

                $table_name = 'notifications';
                $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
                if ($check_table && mysqli_num_rows($check_table) > 0) {
                    $table_name = 'notifications';
                }

                mysqli_query($conn, "INSERT INTO notifications (user_id, message, type) VALUES ('$user_id', '$notif_msg', '$type')");
            } catch (Exception $e) {
            }

            // Binago rin dito
            echo "<script>window.location.href='transactions.php?status=added';</script>";
            exit();
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } catch (Exception $e) {
        $error_message = addslashes($e->getMessage());
        echo "<script>alert('Database Error: " . $error_message . "'); window.location.href='transactions.php';</script>";
    }
}

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$where_clause = "WHERE user_id = '$user_id'";
$search_query = "";
$filter_query = "";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND description LIKE '%$search%'";
    $search_query = "&search=" . urlencode($_GET['search']);
}
if (!empty($_GET['filter'])) {
    $filter = (int)$_GET['filter'];
    $where_clause .= " AND category_id = '$filter'";
    $filter_query = "&filter=" . $_GET['filter'];
}

$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions $where_clause");
$total_row = mysqli_fetch_assoc($total_query);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Transactions</title>

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
      <a href="transactions.php" class="sidebar-link active"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
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

    <header class="h-24 flex items-center justify-between px-8 sm:px-10 shrink-0 z-10 relative">
      <div class="flex items-center gap-4 z-20">
        <button id="menuButton" class="md:hidden p-2.5 bg-white/60 backdrop-blur-md rounded-xl border border-white text-slate-700">
          <i data-feather="menu" class="w-6 h-6"></i>
        </button>

      </div>

      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none animate-fade-in-up z-10">
        <h1 class="text-3xl font-bold font-title text-slate-800">Transactions</h1>
        <p class="text-muted-foreground text-sm font-medium mt-1">View and manage your recent financial activity.</p>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-6 sm:p-8">

      <form method="GET" action="" id="filterForm" class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4 animate-fade-in-up delay-100">
        <div class="relative w-full lg:w-96 group">
          <i data-feather="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
          <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search transactions..." class="w-full pl-11 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 backdrop-blur-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-white transition-all text-slate-700 font-medium">
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
          <select name="filter" onchange="this.form.submit()" class="flex-1 lg:flex-none px-4 py-3 bg-white/60 backdrop-blur-md border border-white text-slate-700 text-sm font-medium rounded-xl hover:bg-white/80 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 cursor-pointer">
            <option value="">All Categories</option>
            <?php
            $cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE user_id IS NULL OR user_id = '$user_id' ORDER BY type DESC, category_name ASC");
            while($cat = mysqli_fetch_assoc($cat_query)) {
                $selected = (isset($_GET['filter']) && $_GET['filter'] == $cat['category_id']) ? 'selected' : '';
                echo "<option value='" . $cat['category_id'] . "' $selected>" . htmlspecialchars($cat['category_name']) . " (" . $cat['type'] . ")</option>";
            }
            ?>
          </select>

          <button type="button" id="openModalBtn" class="flex-1 lg:flex-none flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-emerald-500 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 text-white text-sm font-bold rounded-xl shadow-md transition-all duration-300">
            <i data-feather="plus" class="w-4 h-4"></i> Add Transaction
          </button>
        </div>
      </form>

      <div class="dashboard-card animate-fade-in-up delay-200 !p-0 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[700px]">
            <thead>
              <tr class="bg-white/40 border-b border-white/60 text-slate-500 text-xs uppercase tracking-widest font-bold">
                <th class="px-8 py-5">Transaction Details</th>
                <th class="px-8 py-5">Category</th>
                <th class="px-8 py-5">Date & Time</th>
                <th class="px-8 py-5 text-right">Amount</th>
                <th class="px-8 py-5 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/40 text-sm">
              <?php
              $history_query = mysqli_query($conn, "SELECT * FROM transactions $where_clause ORDER BY transaction_date DESC LIMIT $limit OFFSET $offset");

              $cat_names = [];
              $all_cats_query = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE user_id IS NULL OR user_id = '$user_id'");
              while($c = mysqli_fetch_assoc($all_cats_query)){
                  $cat_names[$c['category_id']] = $c['category_name'];
              }

              if (mysqli_num_rows($history_query) > 0) {
                  while ($row = mysqli_fetch_assoc($history_query)) {
                      $formatted_date = date("M d, Y", strtotime($row['transaction_date']));
                      $is_income = ($row['type'] == 'Income');
                      $amount_color = $is_income ? 'text-emerald-500' : 'text-rose-500';
                      $amount_sign = $is_income ? '+' : '-';
                      $icon_bg = $is_income ? 'bg-emerald-100' : 'bg-rose-100';
                      $icon_color = $is_income ? 'text-emerald-600' : 'text-rose-600';
                      $icon_name = $is_income ? 'arrow-down-left' : 'shopping-bag';

                      $category_name = isset($cat_names[$row['category_id']]) ? $cat_names[$row['category_id']] : 'Other';

                      // Modified delete button to trigger the new custom modal
                      echo "
                      <tr class='hover:bg-white/60 transition-colors group cursor-default'>
                        <td class='px-8 py-5'>
                          <div class='flex items-center gap-4'>
                            <div class='w-10 h-10 rounded-full {$icon_bg} flex items-center justify-center {$icon_color} group-hover:scale-110 transition-transform'>
                                <i data-feather='{$icon_name}' class='w-5 h-5'></i>
                            </div>
                            <div><p class='font-bold text-slate-800'>" . htmlspecialchars($row['description']) . "</p></div>
                          </div>
                        </td>
                        <td class='px-8 py-5'>
                          <span class='inline-flex items-center px-3 py-1 rounded-lg bg-white/80 border border-white shadow-sm text-xs font-bold text-slate-600'>{$category_name}</span>
                        </td>
                        <td class='px-8 py-5'><p class='text-slate-500 font-medium'>{$formatted_date}</p></td>
                        <td class='px-8 py-5 text-right font-bold {$amount_color} text-base whitespace-nowrap'>
                          {$amount_sign} ₱" . number_format((float)$row['amount'], 2) . "
                        </td>
                        <td class='px-8 py-5 text-center'>
                           <button type='button' onclick='openDeleteModal({$row['transaction_id']})' class='p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-100 hover:shadow-sm rounded-xl transition-all'>
                               <i data-feather='trash-2' class='w-5 h-5'></i>
                           </button>
                        </td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='5' class='px-8 py-10 text-center text-slate-500 font-medium'>No transactions found.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

        <div class="px-8 py-5 border-t border-white/60 flex items-center justify-between bg-white/20">
            <span class="text-sm font-medium text-slate-500">Showing <?php echo ($total_records > 0) ? $offset + 1 : 0; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?></span>
            <div class="flex gap-2">
                <a href="?page=<?php echo max(1, $page - 1) . $search_query . $filter_query; ?>" class="px-4 py-2 text-sm font-bold border border-white bg-white/50 rounded-xl <?php echo ($page <= 1) ? 'text-slate-400 opacity-50 pointer-events-none' : 'text-blue-600 hover:bg-white hover:shadow-sm transition-all'; ?>">Prev</a>
                <a href="?page=<?php echo min($total_pages, $page + 1) . $search_query . $filter_query; ?>" class="px-4 py-2 text-sm font-bold border border-white bg-white/50 rounded-xl <?php echo ($page >= $total_pages || $total_pages == 0) ? 'text-slate-400 opacity-50 pointer-events-none' : 'text-blue-600 hover:bg-white hover:shadow-sm transition-all'; ?>">Next</a>
            </div>
        </div>
      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="transactionModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Add Transaction</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white"><i data-feather="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
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
                    <button type="button" id="cancelModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="add_transaction" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteConfirmModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4 transition-opacity duration-300">
       <div class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-sm p-6 text-center animate-modal">
         <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4 text-rose-500 shadow-inner border border-rose-200">
           <i data-feather="alert-triangle" class="w-8 h-8"></i>
         </div>
         <h3 class="text-xl font-bold font-title text-slate-800 mb-2">Delete Transaction?</h3>
         <p class="text-slate-500 font-medium mb-6">Are you sure you want to delete this transaction? This action cannot be undone.</p>
         <form method="POST" action="">
            <input type="hidden" name="transaction_id" id="deleteTransactionId" value="">
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-5 py-3 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                <button type="submit" name="delete_transaction" class="flex-1 px-5 py-3 text-sm font-bold text-white bg-gradient-to-r from-rose-500 to-red-500 hover:shadow-lg hover:shadow-rose-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Delete</button>
            </div>
         </form>
       </div>
    </div>

    <?php if (isset($_GET['status']) && in_array($_GET['status'], ['added', 'deleted'])): ?>
    <div id="successStatusModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[70] flex items-center justify-center p-4 transition-opacity duration-300">
       <div class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-sm p-6 text-center animate-modal">
         <div class="w-16 h-16 <?php echo $_GET['status'] == 'deleted' ? 'bg-rose-100 text-rose-500 border-rose-200' : 'bg-emerald-100 text-emerald-500 border-emerald-200'; ?> rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner border">
           <i data-feather="<?php echo $_GET['status'] == 'deleted' ? 'trash-2' : 'check-circle'; ?>" class="w-8 h-8"></i>
         </div>
         <h3 class="text-xl font-bold font-title text-slate-800 mb-2">
             <?php echo $_GET['status'] == 'deleted' ? 'Deleted!' : 'Success!'; ?>
         </h3>
         <p class="text-slate-500 font-medium mb-6">
             <?php echo $_GET['status'] == 'deleted' ? 'Transaction has been successfully removed.' : 'Transaction saved successfully!'; ?>
         </p>
         <button onclick="closeSuccessStatusModal()" class="w-full px-5 py-3 text-sm font-bold text-white bg-gradient-to-r <?php echo $_GET['status'] == 'deleted' ? 'from-rose-500 to-red-400' : 'from-emerald-500 to-teal-400'; ?> hover:shadow-lg hover:-translate-y-0.5 rounded-xl transition-all">Continue</button>
       </div>
    </div>
    <?php endif; ?>

  </div>

  <script>
    feather.replace();

    // SIDEBAR LOGIC
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

    // ADD MODAL LOGIC
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const transactionModal = document.getElementById('transactionModal');
    function openModal() { modalBackdrop.classList.remove('hidden'); transactionModal.classList.remove('hidden'); }
    function closeModal() { modalBackdrop.classList.add('hidden'); transactionModal.classList.add('hidden'); }
    openModalBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    cancelModalBtn?.addEventListener('click', closeModal);
    modalBackdrop?.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });

    // NEW: DELETE MODAL LOGIC
    function openDeleteModal(id) {
        document.getElementById('deleteTransactionId').value = id;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    // NEW: SUCCESS / STATUS MODAL LOGIC
    function closeSuccessStatusModal() {
        const modal = document.getElementById('successStatusModal');
        if(modal) {
            modal.style.display = 'none';
            // Tinatanggal yung ?status=... sa URL para hindi mag-pop-up ulit pag ni-refresh
            const url = new URL(window.location);
            url.searchParams.delete('status');
            window.history.replaceState({}, '', url);
        }
    }

    // ==========================================
    // THREE.JS BACKGROUND
    // ==========================================
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
