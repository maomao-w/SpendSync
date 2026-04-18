<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_goal'])) {
    $goal_name = mysqli_real_escape_string($conn, $_POST['goal_name']);
    $target_amount = (float)$_POST['target_amount'];
    $target_date = $_POST['target_date']; 
    $current_amount = !empty($_POST['current_amount']) ? (float)$_POST['current_amount'] : 0;

    $sql = "INSERT INTO goals (user_id, goal_name, target_amount, current_amount, target_date) 
            VALUES ('$user_id', '$goal_name', '$target_amount', '$current_amount', '$target_date')";

    if (mysqli_query($conn, $sql)) {
        header("Location: goals.php?status=added");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href='goals.php';</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_funds'])) {
    $fund_goal_id = mysqli_real_escape_string($conn, $_POST['fund_goal_id']);
    $add_amount = (float)$_POST['add_amount'];

    $sql = "UPDATE goals SET current_amount = current_amount + $add_amount WHERE id = '$fund_goal_id' AND user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: goals.php?status=updated");
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location.href='goals.php';</script>";
    }
}

if (isset($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_sql = "DELETE FROM goals WHERE id='$delete_id' AND user_id='$user_id'";
    mysqli_query($conn, $delete_sql);
    header("Location: goals.php?status=deleted");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Goals</title>

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
            border: 'rgba(255, 255, 255, 0.8)'
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            title: ['Poppins', 'sans-serif'],
          },
          animation: {
            'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            'modal': 'modalFadeIn 0.3s ease-out forwards',
          },
          keyframes: {
            fadeInUp: {
              '0%': { opacity: 0, transform: 'translateY(20px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
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
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
      </div>
    </div>

    <nav class="flex-1 py-8 space-y-3 overflow-y-auto overflow-x-hidden">
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
      <a href="categories.php" class="sidebar-link"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>  
      <a href="goals.php" class="sidebar-link active"><i data-feather="target" class="w-5 h-5"></i> Goals</a>
      <a href="reports.php" class="sidebar-link"><i data-feather="file-text" class="w-5 h-5"></i> Reports</a>

      <a href="export_csv.php" class="sidebar-link"><i data-feather="download" class="w-5 h-5"></i> Download CSV</a>

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

    <header class="h-24 flex items-center justify-between px-8 sm:px-10 shrink-0 z-10 relative border-b border-white/40">
      <div class="flex items-center gap-4 z-20">
        <button id="menuButton" class="md:hidden p-2.5 bg-white/60 backdrop-blur-md rounded-xl border border-white text-slate-700">
          <i data-feather="menu" class="w-6 h-6"></i>
        </button>
      </div>

      <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none animate-fade-in-up z-10">
        <h1 class="text-3xl font-bold font-title text-slate-800">Financial Goals</h1>
        <p class="text-muted-foreground text-sm font-medium mt-1">Track your progress and achieve your dreams.</p>
      </div>

      <button type="button" id="openModalBtn" class="flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-emerald-500 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 text-white text-sm font-bold rounded-xl shadow-md transition-all duration-300 z-20">
        <i data-feather="plus" class="w-4 h-4"></i> New Goal
      </button>
    </header>

    <main class="flex-1 overflow-auto p-6 sm:p-8">

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <?php
        $goals_query = mysqli_query($conn, "SELECT * FROM goals WHERE user_id = '$user_id' ORDER BY target_date ASC");

        if (mysqli_num_rows($goals_query) > 0) {
            $delay = 100;
            while ($goal = mysqli_fetch_assoc($goals_query)) {
                $goal_id = $goal['id'];
                $target = $goal['target_amount'];
                $current = $goal['current_amount'];
                $percent = ($target > 0) ? min(100, round(($current / $target) * 100)) : 0;

                $formatted_date = date("M Y", strtotime($goal['target_date'] . "-01"));

                $target_date_obj = new DateTime($goal['target_date'] . '-01');
                $current_date_obj = new DateTime(date('Y-m-01')); 
                $interval = $current_date_obj->diff($target_date_obj);
                $months_left = ($interval->y * 12) + $interval->m;
                
                if ($current_date_obj > $target_date_obj) {
                     $months_left = 0;
                }

                if ($target <= $current) {
                    $insight_text = "Goal achieved! Excellent financial discipline.";
                    $insight_color = "text-emerald-500";
                } elseif ($months_left > 0) {
                    $monthly_needed = ($target - $current) / $months_left;
                    $insight_text = "Insight: Save ₱" . number_format($monthly_needed, 2) . " / month to stay on track.";
                    $insight_color = "text-blue-500";
                } else {
                    $insight_text = "Target passed. Allocate remaining ₱" . number_format($target - $current, 2) . " now.";
                    $insight_color = "text-rose-500";
                }

                echo "
                <div class='dashboard-card animate-fade-in-up' style='animation-delay: {$delay}ms;'>
                  <div class='flex justify-between items-start mb-6'>
                    <div class='flex items-center gap-4'>
                      <div class='p-3 bg-blue-100 text-blue-600 rounded-xl shadow-sm border border-white'><i data-feather='target' class='w-5 h-5'></i></div>
                      <div>
                        <h3 class='font-bold text-slate-800 text-lg font-title'>" . htmlspecialchars($goal['goal_name']) . "</h3>
                        <p class='text-[11px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5'><i data-feather='calendar' class='w-3 h-3'></i> Target: {$formatted_date}</p>
                      </div>
                    </div>
                    <div class='flex items-center gap-1'>
                        <button onclick='openAddFundsModal({$goal_id})' class='text-slate-400 hover:text-emerald-500 transition-colors p-2' title='Add Funds'>
                            <i data-feather='plus-circle' class='w-4 h-4'></i>
                        </button>
                        <a href='goals.php?delete={$goal_id}' onclick='return confirm(\"Are you sure you want to delete this goal?\")' class='text-slate-400 hover:text-rose-500 transition-colors p-2' title='Delete Goal'>
                            <i data-feather='trash-2' class='w-4 h-4'></i>
                        </a>
                    </div>
                  </div>
                  
                  <div class='mb-3 flex justify-between items-end'>
                    <div>
                      <p class='text-3xl font-bold font-title text-slate-800'>₱" . number_format($current, 2) . "</p>
                      <p class='text-sm font-medium text-slate-500'>of ₱" . number_format($target, 2) . "</p>
                    </div>
                    <div class='px-3 py-1 bg-white/80 border border-white text-blue-600 font-bold text-sm rounded-lg shadow-sm'>
                      {$percent}%
                    </div>
                  </div>
                  
                  <div class='h-3 w-full bg-white/60 shadow-inner border border-white rounded-full overflow-hidden mt-4 relative'>
                    <div class='h-full bg-blue-500 rounded-full transition-all duration-1000' style='width: {$percent}%;'></div>
                  </div>

                  <div class='mt-4 pt-4 border-t border-slate-100/50'>
                     <p class='text-[11px] font-bold uppercase tracking-wider {$insight_color} flex items-center gap-1.5'>
                        <i data-feather='info' class='w-3.5 h-3.5'></i> {$insight_text}
                     </p>
                  </div>
                </div>";

                $delay += 100;
            }
        } else {
            echo "<div class='col-span-full text-center p-8 text-slate-500 font-medium bg-white/40 border border-white rounded-[2rem]'>No financial goals set yet. Start dreaming big!</div>";
        }
        ?>
      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="goalModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Create New Goal</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="">
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Goal Name</label>
                        <input type="text" name="goal_name" placeholder="e.g. New Laptop" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                                <input type="number" step="0.01" name="target_amount" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Date</label>
                            <input type="month" name="target_date" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Initial Saved Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                            <input type="number" step="0.01" name="current_amount" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                    <button type="button" id="cancelModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="submit" name="add_goal" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">
                        Save Goal
                    </button>
                </div>
            </form>
        </div>

        <div id="addFundsModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Add Funds</h2>
                <button onclick="closeAddFundsModal()" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="fund_goal_id" id="fund_goal_id">
                <div class="p-6 space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Amount to Add</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                            <input type="number" step="0.01" name="add_amount" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                    <button type="button" onclick="closeAddFundsModal()" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="add_funds" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-400 hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['status']) && in_array($_GET['status'], ['added', 'deleted', 'updated'])): ?>
    <div id="successStatusModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[70] flex items-center justify-center p-4 transition-opacity duration-300">
       <div class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-sm p-6 text-center animate-modal">
         <div class="w-16 h-16 bg-emerald-100 text-emerald-500 border-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner border">
           <i data-feather="check-circle" class="w-8 h-8"></i>
         </div>
         <h3 class="text-xl font-bold font-title text-slate-800 mb-2">Success!</h3>
         <p class="text-slate-500 font-medium mb-6">Goal has been updated successfully!</p>
         <button onclick="closeSuccessStatusModal()" class="w-full px-5 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-400 hover:shadow-lg hover:-translate-y-0.5 rounded-xl transition-all">Continue</button>
       </div>
    </div>
    <?php endif; ?>

  </div>

  <script>
    feather.replace();

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

    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const goalModal = document.getElementById('goalModal');
    const addFundsModal = document.getElementById('addFundsModal');

    function openModal() { 
        modalBackdrop.classList.remove('hidden'); 
        goalModal.classList.remove('hidden'); 
        addFundsModal.classList.add('hidden');
    }
    
    function closeModal() { 
        modalBackdrop.classList.add('hidden'); 
        goalModal.classList.add('hidden'); 
    }

    function openAddFundsModal(id) {
        document.getElementById('fund_goal_id').value = id;
        modalBackdrop.classList.remove('hidden');
        goalModal.classList.add('hidden');
        addFundsModal.classList.remove('hidden');
    }

    function closeAddFundsModal() {
        modalBackdrop.classList.add('hidden');
        addFundsModal.classList.add('hidden');
    }

    openModalBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    cancelModalBtn?.addEventListener('click', closeModal);
    modalBackdrop?.addEventListener('click', (e) => { 
        if (e.target === modalBackdrop) {
            closeModal(); 
            closeAddFundsModal();
        }
    });

    function closeSuccessStatusModal() {
        const modal = document.getElementById('successStatusModal');
        if(modal) {
            modal.style.display = 'none';
            const url = new URL(window.location);
            url.searchParams.delete('status');
            window.history.replaceState({}, '', url);
        }
    }

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
