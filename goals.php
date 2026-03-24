<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?show=form");
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

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            background: '#ffffff',
            foreground: '#111827',
            card: '#ffffff',
            primary: '#030213',
            muted: '#ececf0',
            'muted-foreground': '#717182',
            border: 'rgba(0, 0, 0, 0.1)',
            indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
            emerald: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669' },
            blue: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb' },
            amber: { 50: '#fffbeb', 100: '#fef3c7', 500: '#f59e0b', 600: '#d97706' },
            fuchsia: { 50: '#fdf4ff', 100: '#fae8ff', 500: '#d946ef', 600: '#c026d3' },
            rose: { 50: '#fff1f2', 600: '#e11d48' }
          },
          fontFamily: { sans: ['Inter', 'sans-serif'] }
        }
      }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base {
      body { @apply bg-zinc-50 text-foreground font-sans antialiased; }
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-zinc-300 rounded-full; }
    ::-webkit-scrollbar-thumb:hover { @apply bg-zinc-400; }
    
    .animate-page-fade { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .animate-modal { animation: modalFadeIn 0.2s ease-out forwards; }
    @keyframes modalFadeIn { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    .sidebar-link { @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-muted-foreground font-medium transition-all duration-200 ease-in-out; }
    .sidebar-link:hover { @apply bg-indigo-50 text-indigo-700; }
    .sidebar-link.active { @apply bg-indigo-50 text-indigo-600; }
    
    .goal-card { @apply bg-card border border-border rounded-xl p-6 shadow-sm transition-all duration-300 hover:shadow-md hover:border-indigo-100 hover:-translate-y-1 relative overflow-hidden; }
  </style>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="flex h-screen overflow-hidden bg-zinc-50">

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 md:relative md:flex w-64 flex-col bg-white border-r border-border h-full transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center justify-between px-6 border-b border-border">
      <div class="flex items-center gap-2 font-bold text-xl text-primary">
        <div class="p-1.5 bg-indigo-600 rounded-lg shadow-inner"><i data-feather="repeat" class="w-5 h-5 text-white"></i></div>
        SpendSync
      </div>
      <button id="closeSidebar" class="md:hidden p-1.5 text-muted-foreground hover:bg-zinc-100 rounded-md"><i data-feather="x" class="w-5 h-5"></i></button>
    </div>
    
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="credit-card" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="target" class="w-5 h-5"></i> Budgets</a>
      <a href="goals.php" class="sidebar-link active"><i data-feather="award" class="w-5 h-5"></i> Goals</a>
      <a href="settings.php" class="sidebar-link"><i data-feather="settings" class="w-5 h-5"></i> Settings</a>
    </nav>
    
    <div class="p-4 border-t border-border">
      <a href="logout.php" class="sidebar-link hover:bg-rose-50 hover:text-rose-600"><i data-feather="log-out" class="w-5 h-5"></i> Logout</a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative">
    
    <header class="h-16 flex items-center justify-between px-4 sm:px-6 bg-white border-b border-border shrink-0 z-10 relative">
      <button id="menuButton" class="md:hidden p-2 text-muted-foreground hover:bg-zinc-100 rounded-md transition-colors"><i data-feather="menu" class="w-5 h-5"></i></button>
      
      <div class="flex items-center gap-4 ml-auto">
        <button class="p-2 text-muted-foreground hover:bg-zinc-100 rounded-full relative transition-colors">
          <i data-feather="bell" class="w-5 h-5"></i>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-600 rounded-full border border-white"></span>
        </button>
        <a href="profile.php" class="h-8 w-8 rounded-full bg-indigo-100 border border-indigo-200 overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-300 transition-all">
          <img src="https://i.pravatar.cc/150?u=spendSyncUser" alt="Profile" class="w-full h-full object-cover">
        </a>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-4 sm:p-6 lg:p-8 animate-page-fade">
      
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-foreground">Financial Goals</h1>
          <p class="text-muted-foreground text-sm mt-1">Track your progress towards your dreams.</p>
        </div>
        <button id="openModalBtn" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 active:scale-95">
          <i data-feather="plus" class="w-4 h-4"></i> Create Goal
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        
        <div class="goal-card">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl"><i data-feather="shield" class="w-6 h-6"></i></div>
              <div>
                <h3 class="font-bold text-foreground text-lg">Emergency Fund</h3>
                <p class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Dec 2024</p>
              </div>
            </div>
            <button class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-2 flex justify-between items-end">
            <div>
              <p class="text-2xl font-bold text-foreground">₱350,000</p>
              <p class="text-sm text-muted-foreground">of ₱500,000</p>
            </div>
            <div class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-semibold text-sm rounded-lg border border-emerald-100">
              70%
            </div>
          </div>
          
          <div class="h-2.5 w-full bg-zinc-100 rounded-full overflow-hidden mt-4">
            <div class="h-full bg-emerald-500 rounded-full relative" style="width: 70%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

        <div class="goal-card">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-blue-100 text-blue-600 rounded-xl"><i data-feather="truck" class="w-6 h-6"></i></div>
              <div>
                <h3 class="font-bold text-foreground text-lg">New Car Downpayment</h3>
                <p class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Jun 2025</p>
              </div>
            </div>
            <button class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-2 flex justify-between items-end">
            <div>
              <p class="text-2xl font-bold text-foreground">₱45,000</p>
              <p class="text-sm text-muted-foreground">of ₱200,000</p>
            </div>
            <div class="px-2.5 py-1 bg-blue-50 text-blue-700 font-semibold text-sm rounded-lg border border-blue-100">
              22.5%
            </div>
          </div>
          
          <div class="h-2.5 w-full bg-zinc-100 rounded-full overflow-hidden mt-4">
            <div class="h-full bg-blue-600 rounded-full relative" style="width: 22.5%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

        <div class="goal-card">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-amber-100 text-amber-600 rounded-xl"><i data-feather="briefcase" class="w-6 h-6"></i></div>
              <div>
                <h3 class="font-bold text-foreground text-lg">Business Capital</h3>
                <p class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Jan 2026</p>
              </div>
            </div>
            <button class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-2 flex justify-between items-end">
            <div>
              <p class="text-2xl font-bold text-foreground">₱150,000</p>
              <p class="text-sm text-muted-foreground">of ₱1,000,000</p>
            </div>
            <div class="px-2.5 py-1 bg-amber-50 text-amber-700 font-semibold text-sm rounded-lg border border-amber-100">
              15%
            </div>
          </div>
          
          <div class="h-2.5 w-full bg-zinc-100 rounded-full overflow-hidden mt-4">
            <div class="h-full bg-amber-500 rounded-full relative" style="width: 15%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

        <div class="goal-card">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-fuchsia-100 text-fuchsia-600 rounded-xl"><i data-feather="map" class="w-6 h-6"></i></div>
              <div>
                <h3 class="font-bold text-foreground text-lg">Vacation Trip</h3>
                <p class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Aug 2024</p>
              </div>
            </div>
            <button class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-2 flex justify-between items-end">
            <div>
              <p class="text-2xl font-bold text-foreground">₱60,000</p>
              <p class="text-sm text-muted-foreground">of ₱80,000</p>
            </div>
            <div class="px-2.5 py-1 bg-fuchsia-50 text-fuchsia-700 font-semibold text-sm rounded-lg border border-fuchsia-100">
              75%
            </div>
          </div>
          
          <div class="h-2.5 w-full bg-zinc-100 rounded-full overflow-hidden mt-4">
            <div class="h-full bg-fuchsia-600 rounded-full relative" style="width: 75%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div id="goalModal" class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-5 border-b border-border">
                <h2 class="text-lg font-bold text-foreground">Create New Goal</h2>
                <button id="closeModalBtn" class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-5 space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Goal Name</label>
                    <input type="text" placeholder="e.g. New Laptop" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-foreground">Target Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">₱</span>
                            <input type="number" placeholder="0.00" class="w-full pl-8 pr-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-foreground">Target Date</label>
                        <input type="month" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Initial Saved Amount (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">₱</span>
                        <input type="number" placeholder="0.00" class="w-full pl-8 pr-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-border flex justify-end gap-3 bg-zinc-50">
                <button id="cancelModalBtn" class="px-4 py-2 text-sm font-medium text-muted-foreground bg-white border border-border hover:bg-zinc-50 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="saveBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                    Save Goal
                </button>
            </div>
        </div>
    </div>
  </div>

  <script>
    feather.replace();

    // Mobile Sidebar Logic
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
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

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Modal Logic
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const saveBtn = document.getElementById('saveBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const goalModal = document.getElementById('goalModal');

    function openModal() {
        modalBackdrop.classList.remove('hidden');
        goalModal.classList.remove('hidden');
    }

    function closeModal() {
        modalBackdrop.classList.add('hidden');
        goalModal.classList.add('hidden');
    }

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelModalBtn.addEventListener('click', closeModal);
    saveBtn.addEventListener('click', closeModal);

    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) closeModal();
    });
  </script>
</body>
</html>