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
  <title>SpendSync - Transactions</title>
  
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
            indigo: {
              50: '#eef2ff',
              100: '#e0e7ff',
              500: '#6366f1',
              600: '#4f46e5',
              700: '#4338ca',
            },
            emerald: {
              50: '#ecfdf5',
              500: '#10b981',
              600: '#059669',
            },
            amber: {
              50: '#fffbeb',
              500: '#f59e0b',
              700: '#b45309',
            },
            rose: {
              50: '#fff1f2',
              600: '#e11d48',
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <style type="text/tailwindcss">
    @layer base {
      body {
        @apply bg-zinc-50 text-foreground font-sans antialiased;
      }
    }
    
    ::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      @apply bg-zinc-300 rounded-full;
    }
    ::-webkit-scrollbar-thumb:hover {
      @apply bg-zinc-400;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-page-fade {
      animation: fadeIn 0.4s ease-out forwards;
    }

    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.95) translateY(-10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .animate-modal {
      animation: modalFadeIn 0.2s ease-out forwards;
    }

    .sidebar-link {
      @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-muted-foreground font-medium transition-all duration-200 ease-in-out;
    }
    .sidebar-link:hover {
      @apply bg-indigo-50 text-indigo-700;
    }
    .sidebar-link.active {
      @apply bg-indigo-50 text-indigo-600;
    }
  </style>
  
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="flex h-screen overflow-hidden bg-zinc-50">

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300 opacity-0"></div>

  <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 md:relative md:flex w-64 flex-col bg-white border-r border-border h-full transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="h-16 flex items-center justify-between px-6 border-b border-border">
      <div class="flex items-center gap-2 font-bold text-xl text-primary">
        <div class="p-1.5 bg-indigo-600 rounded-lg shadow-inner">
          <i data-feather="repeat" class="w-5 h-5 text-white"></i>
        </div>
        SpendSync
      </div>
      <button id="closeSidebar" class="md:hidden p-1.5 text-muted-foreground hover:bg-zinc-100 rounded-md">
        <i data-feather="x" class="w-5 h-5"></i>
      </button>
    </div>
    
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
      <a href="homepage.php" class="sidebar-link">
        <i data-feather="layout" class="w-5 h-5"></i>
        Dashboard
      </a>
      <a href="transactions.php" class="sidebar-link active">
        <i data-feather="credit-card" class="w-5 h-5"></i>
        Transactions
      </a>
      <a href="budgets.php" class="sidebar-link">
        <i data-feather="target" class="w-5 h-5"></i>
        Budgets
      </a>
      <a href="goals.php" class="sidebar-link">
        <i data-feather="award" class="w-5 h-5"></i>
        Goals
      </a>
      <a href="settings.php" class="sidebar-link">
        <i data-feather="settings" class="w-5 h-5"></i>
        Settings
      </a>
    </nav>
    
    <div class="p-4 border-t border-border">
      <a href="logout.php" class="sidebar-link hover:bg-rose-50 hover:text-rose-600">
        <i data-feather="log-out" class="w-5 h-5"></i>
        Logout
      </a>
    </div>
  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative">
    
    <header class="h-16 flex items-center justify-between px-4 sm:px-6 bg-white border-b border-border shrink-0 z-10 relative">
      <button id="menuButton" class="md:hidden p-2 text-muted-foreground hover:bg-zinc-100 rounded-md transition-colors">
        <i data-feather="menu" class="w-5 h-5"></i>
      </button>
      
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
      
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-foreground">Transactions</h1>
          <p class="text-muted-foreground text-sm mt-1">View and manage your recent financial activity.</p>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div class="relative w-full sm:w-72">
          <i data-feather="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
          <input type="text" placeholder="Search transactions..." class="w-full pl-9 pr-4 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
        </div>
        
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
          <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white border border-border text-foreground text-sm font-medium rounded-lg hover:bg-zinc-50 transition-colors shadow-sm">
            <i data-feather="filter" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Filter</span>
          </button>
          <button class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white border border-border text-foreground text-sm font-medium rounded-lg hover:bg-zinc-50 transition-colors shadow-sm">
            <i data-feather="download" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Export</span>
          </button>
          <button id="openModalBtn" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 active:scale-95">
            <i data-feather="plus" class="w-4 h-4"></i>
            Add Transaction
          </button>
        </div>
      </div>

      <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[700px]">
            <thead>
              <tr class="bg-zinc-50 text-muted-foreground text-xs uppercase tracking-wider">
                <th class="px-6 py-4 font-semibold">Transaction Details</th>
                <th class="px-6 py-4 font-semibold">Category</th>
                <th class="px-6 py-4 font-semibold">Date & Time</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                        <i data-feather="monitor" class="w-4 h-4 text-zinc-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">Apple Store</p>
                        <p class="text-xs text-muted-foreground">Credit Card</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Electronics</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-foreground">Oct 25, 2023</p>
                  <p class="text-xs text-muted-foreground">10:24 AM</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱1,299.00</td>
              </tr>
              
              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 rounded-full group-hover:bg-emerald-50 transition-colors">
                            <i data-feather="arrow-down-left" class="w-4 h-4 text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Upwork Escrow</p>
                            <p class="text-xs text-muted-foreground">Bank Transfer</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 text-xs font-medium text-emerald-700">Income</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-foreground">Oct 24, 2023</p>
                  <p class="text-xs text-muted-foreground">3:00 PM</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-emerald-600 whitespace-nowrap">+₱3,200.00</td>
              </tr>

              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                            <i data-feather="shopping-cart" class="w-4 h-4 text-zinc-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Whole Foods</p>
                            <p class="text-xs text-muted-foreground">Debit Card</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Groceries</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-foreground">Oct 24, 2023</p>
                  <p class="text-xs text-muted-foreground">6:45 PM</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱145.20</td>
              </tr>

              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                            <i data-feather="play" class="w-4 h-4 text-zinc-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Netflix Subscription</p>
                            <p class="text-xs text-muted-foreground">Credit Card</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Entertainment</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-foreground">Oct 22, 2023</p>
                  <p class="text-xs text-muted-foreground">1:00 AM</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Pending
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱15.99</td>
              </tr>

              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                            <i data-feather="truck" class="w-4 h-4 text-zinc-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Shell Station</p>
                            <p class="text-xs text-muted-foreground">Debit Card</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Transport</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-foreground">Oct 21, 2023</p>
                  <p class="text-xs text-muted-foreground">8:30 AM</p>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱45.00</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="px-6 py-4 border-t border-border flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Showing 1 to 5 of 24 transactions</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 text-sm border border-border rounded-md text-muted-foreground disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 text-sm border border-border rounded-md text-foreground hover:bg-zinc-50 transition-colors">Next</button>
            </div>
        </div>
      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div id="transactionModal" class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-5 border-b border-border">
                <h2 class="text-lg font-bold text-foreground">Add Transaction</h2>
                <button id="closeModalBtn" class="text-muted-foreground hover:bg-zinc-100 p-1.5 rounded-md transition-colors">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="p-5 space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">₱</span>
                        <input type="number" placeholder="0.00" class="w-full pl-8 pr-4 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                    </div>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Merchant / Name</label>
                    <input type="text" placeholder="e.g. Apple Store" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-foreground">Category</label>
                        <select class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                            <option>Electronics</option>
                            <option>Groceries</option>
                            <option>Food</option>
                            <option>Transport</option>
                            <option>Income</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-foreground">Date</label>
                        <input type="date" class="w-full px-3 py-2 text-sm border border-border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-border flex justify-end gap-3 bg-zinc-50">
                <button id="cancelModalBtn" class="px-4 py-2 text-sm font-medium text-muted-foreground bg-white border border-border hover:bg-zinc-50 rounded-lg transition-colors">
                    Cancel
                </button>
                <button id="saveTransactionBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                    Save Transaction
                </button>
            </div>
        </div>
    </div>
  </div>

  <script>
    // 1. Initialize Feather Icons
    feather.replace();

    // 2. Mobile Sidebar Logic
    const menuButton = document.getElementById('menuButton');
    const closeSidebar = document.getElementById('closeSidebar');
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

    menuButton.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // 3. Modal Logic
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const saveTransactionBtn = document.getElementById('saveTransactionBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const transactionModal = document.getElementById('transactionModal');

    function openModal() {
        modalBackdrop.classList.remove('hidden');
        transactionModal.classList.remove('hidden');
    }

    function closeModal() {
        modalBackdrop.classList.add('hidden');
        transactionModal.classList.add('hidden');
    }

    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelModalBtn.addEventListener('click', closeModal);
    saveTransactionBtn.addEventListener('click', closeModal);

    // Close modal kapag pinindot 'yung labas (backdrop)
    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
            closeModal();
        }
    });
  </script>
</body>
</html>