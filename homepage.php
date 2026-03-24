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
  <title>SpendSync - Dashboard</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  
  <script src="https://unpkg.com/feather-icons"></script>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    
    /* Custom Scrollbar */
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

    /* Page Fade-in Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-page-fade {
      animation: fadeIn 0.4s ease-out forwards;
    }
    
    /* Card Hover Effect */
    .dashboard-card {
      @apply bg-card border border-border rounded-xl p-6 shadow-sm transition-all duration-300 ease-in-out;
    }
    .dashboard-card:hover {
      @apply shadow-lg border-indigo-100 -translate-y-1;
    }

    /* Sidebar Link Transition */
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
      <a href="homepage.php" class="sidebar-link active">
        <i data-feather="layout" class="w-5 h-5"></i>
        Dashboard
      </a>
      <a href="transactions.php" class="sidebar-link">
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

  <div class="flex-1 flex flex-col h-full overflow-hidden">
    
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
      
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-foreground">DashboardOverview</h1>
          <p class="text-muted-foreground text-sm mt-1">Kamusta beh! Here's your financial pulse at a glance.</p>
        </div>
        <button class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 ease-in-out hover:shadow-md active:scale-95">
          <i data-feather="plus" class="w-4 h-4"></i>
          Add New Transaction
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="dashboard-card">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Total Balance</p>
              <h3 class="text-2xl font-bold mt-2">₱24,562.00</h3>
            </div>
            <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg shadow-inner">
              <i data-feather="pocket" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm">
            <span class="text-emerald-600 font-medium flex items-center"><i data-feather="trending-up" class="w-3.5 h-3.5 mr-1"></i> +2.5%</span>
            <span class="text-muted-foreground ml-2">vs last month</span>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Total Income</p>
              <h3 class="text-2xl font-bold mt-2">₱8,240.50</h3>
            </div>
            <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg shadow-inner">
              <i data-feather="arrow-up-right" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm">
            <span class="text-emerald-600 font-medium flex items-center"><i data-feather="trending-up" class="w-3.5 h-3.5 mr-1"></i> +12.4%</span>
            <span class="text-muted-foreground ml-2">vs last month</span>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Total Expenses</p>
              <h3 class="text-2xl font-bold mt-2">₱3,124.20</h3>
            </div>
            <div class="p-2.5 bg-rose-50 text-rose-600 rounded-lg shadow-inner">
              <i data-feather="arrow-down-right" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm">
            <span class="text-rose-600 font-medium flex items-center"><i data-feather="trending-down" class="w-3.5 h-3.5 mr-1"></i> -4.2%</span>
            <span class="text-muted-foreground ml-2">vs last month</span>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Savings Rate</p>
              <h3 class="text-2xl font-bold mt-2">62.1%</h3>
            </div>
            <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-lg shadow-inner">
              <i data-feather="activity" class="w-5 h-5"></i>
            </div>
          </div>
          <div class="mt-4 flex items-center text-sm">
            <span class="text-emerald-600 font-medium flex items-center"><i data-feather="trending-up" class="w-3.5 h-3.5 mr-1"></i> +4.1%</span>
            <span class="text-muted-foreground ml-2">vs last month</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 dashboard-card flex flex-col">
          <div class="flex justify-between items-center mb-5">
            <h3 class="text-base font-semibold text-foreground">Income vs Expenses (Last 6 Months)</h3>
            <div class="flex items-center gap-1.5 text-xs text-muted-foreground border border-border rounded-md px-2 py-1 bg-zinc-50">
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Income</span>
              <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-indigo-500"></span> Expenses</span>
            </div>
          </div>
          <div class="flex-1 min-h-[250px] relative">
            <canvas id="incomeExpensesChart"></canvas>
          </div>
        </div>
        
        <div class="dashboard-card flex flex-col">
          <h3 class="text-base font-semibold text-foreground mb-5">Expense Breakdowns</h3>
          <div class="flex-1 min-h-[250px] relative flex items-center justify-center">
            <canvas id="expensePieChart"></canvas>
          </div>
        </div>
      </div>

      <div class="dashboard-card !p-0 overflow-hidden">
        <div class="px-6 py-5 border-b border-border flex justify-between items-center">
          <h3 class="text-base font-semibold text-foreground">Recent Activity</h3>
          <a href="transactions.php" class="flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
            View All History
            <i data-feather="chevron-right" class="w-4 h-4"></i>
          </a>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[600px]">
            <thead>
              <tr class="bg-zinc-50 text-muted-foreground text-xs uppercase tracking-wider">
                <th class="px-6 py-3.5 font-semibold">Transaction</th>
                <th class="px-6 py-3.5 font-semibold">Category</th>
                <th class="px-6 py-3.5 font-semibold hidden md:table-cell">Date</th>
                <th class="px-6 py-3.5 font-semibold">Status</th>
                <th class="px-6 py-3.5 font-semibold text-right">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                        <i data-feather="shopping-bag" class="w-4 h-4 text-zinc-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-foreground">Apple Store PH</p>
                        <p class="text-xs text-muted-foreground md:hidden">Electronics • Oct 25</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Electronics</span>
                </td>
                <td class="px-6 py-4 text-muted-foreground hidden md:table-cell">Oct 25, 2023</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱1,299.00</td>
              </tr>
              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                            <i data-feather="dollar-sign" class="w-4 h-4 text-zinc-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Upwork Global Inc.</p>
                            <p class="text-xs text-muted-foreground md:hidden">Freelance • Oct 24</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Freelance</span>
                </td>
                <td class="px-6 py-4 text-muted-foreground hidden md:table-cell">Oct 24, 2023</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Completed
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-emerald-600 whitespace-nowrap">+₱3,200.00</td>
              </tr>
              <tr class="hover:bg-indigo-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 rounded-full group-hover:bg-white transition-colors">
                            <i data-feather="coffee" class="w-4 h-4 text-zinc-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">SM Supermarket</p>
                            <p class="text-xs text-muted-foreground md:hidden">Groceries • Oct 24</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-1 rounded-md bg-zinc-100 text-xs font-medium text-zinc-700">Groceries</span>
                </td>
                <td class="px-6 py-4 text-muted-foreground hidden md:table-cell">Oct 24, 2023</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>Pending
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-foreground whitespace-nowrap">-₱145.20</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <script>
    // 1. Initialize Feather Icons
    feather.replace();

    // 2. Mobile Sidebar Logic with Animations
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


    // 3. ACTUAL CHARTS CONFIGURATION (Chart.js)
    
    // Custom Chart.js Defaults para magmukhang modern
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#717182'; // muted-foreground
    Chart.defaults.plugins.tooltip.backgroundColor = '#030213'; // primary

    // --- Bar Chart: Income vs Expenses ---
    const ctxBar = document.getElementById('incomeExpensesChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
                label: 'Income',
                data: [6500, 7200, 5800, 8100, 7500, 8240],
                backgroundColor: '#10b981', // emerald-500
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7
            }, {
                label: 'Expenses',
                data: [4100, 3900, 4500, 3200, 4000, 3124],
                backgroundColor: '#6366f1', // indigo-500
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false } // Custom legend sa HTML na lang
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false },
                    ticks: {
                        callback: (value) => '₱' + value.toLocaleString()
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeOutQuart'
            }
        }
    });

    // --- Pie Chart: Expense Breakdown ---
    const ctxPie = document.getElementById('expensePieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut', // Doughnut mas modern kaysa plain pie
        data: {
            labels: ['Groceries', 'Food', 'Electronics', 'Transport'],
            datasets: [{
                data: [35, 25, 25, 15],
                backgroundColor: [
                    '#4f46e5', // indigo-600
                    '#a5b4fc', // indigo-200
                    '#030213', // primary
                    '#ececf0'  // muted
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Mas manipis na ring
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1800
            }
        }
    });
  </script>
</body>
</html>