<?php
include 'session_manager.php';
include 'config.php';
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
            border: 'rgba(255, 255, 255, 0.8)', 
            indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
            emerald: { 50: '#ecfdf5', 100: '#d1fae5', 500: '#10b981', 600: '#059669' },
            blue: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb' },
            amber: { 50: '#fffbeb', 100: '#fef3c7', 500: '#f59e0b', 600: '#d97706' },
            fuchsia: { 50: '#fdf4ff', 100: '#fae8ff', 500: '#d946ef', 600: '#c026d3' },
            rose: { 50: '#fff1f2', 100: '#ffe4e6', 500: '#f43f5e', 600: '#e11d48' }
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

    .bg-blobs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; overflow: hidden; z-index: -2; pointer-events: none; }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: floatBlob 15s infinite alternate; }
    .blob-blue { background: #2563eb; width: 500px; height: 500px; top: -10%; left: -10%; }
    .blob-emerald { background: #10b981; width: 400px; height: 400px; bottom: -10%; right: -10%; animation-delay: -5s; }
    
    @keyframes floatBlob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }
    
    .delay-100 { animation-delay: 100ms; opacity: 0; }
    .delay-200 { animation-delay: 200ms; opacity: 0; }
    .delay-300 { animation-delay: 300ms; opacity: 0; }
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
      <a href="budgets.php" class="sidebar-link"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
      <a href="categories.php" class="sidebar-link"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>  
      <a href="goals.php" class="sidebar-link active"><i data-feather="target" class="w-5 h-5"></i> Goals</a>
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
          <h1 class="text-3xl font-bold font-title text-slate-800">Financial Goals</h1>
          <p class="text-muted-foreground text-sm font-medium mt-1">Track your progress towards your dreams.</p>
        </div>
        <button id="openModalBtn" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-emerald-500 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 text-white text-sm font-bold rounded-xl shadow-md transition-all duration-300">
          <i data-feather="plus" class="w-4 h-4"></i> Create Goal
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        
        <div class="dashboard-card animate-fade-in-up delay-100">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl shadow-sm border border-white"><i data-feather="shield" class="w-5 h-5"></i></div>
              <div>
                <h3 class="font-bold text-slate-800 text-lg font-title">Emergency Fund</h3>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Dec 2024</p>
              </div>
            </div>
            <button class="text-slate-400 hover:text-blue-600 hover:bg-white/60 p-2 rounded-xl transition-all"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-3 flex justify-between items-end">
            <div>
              <p class="text-3xl font-bold font-title text-slate-800">₱350k</p>
              <p class="text-sm font-medium text-slate-500">of ₱500k</p>
            </div>
            <div class="px-3 py-1 bg-white/80 border border-white text-emerald-600 font-bold text-sm rounded-lg shadow-sm">
              70%
            </div>
          </div>
          
          <div class="h-3 w-full bg-white/60 shadow-inner border border-white rounded-full overflow-hidden mt-4 relative">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 relative" style="width: 70%;">
              <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
            </div>
          </div>
        </div>

        <div class="dashboard-card animate-fade-in-up delay-200">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-blue-100 text-blue-600 rounded-xl shadow-sm border border-white"><i data-feather="truck" class="w-5 h-5"></i></div>
              <div>
                <h3 class="font-bold text-slate-800 text-lg font-title">Car Downpayment</h3>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Jun 2025</p>
              </div>
            </div>
            <button class="text-slate-400 hover:text-blue-600 hover:bg-white/60 p-2 rounded-xl transition-all"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-3 flex justify-between items-end">
            <div>
              <p class="text-3xl font-bold font-title text-slate-800">₱45k</p>
              <p class="text-sm font-medium text-slate-500">of ₱200k</p>
            </div>
            <div class="px-3 py-1 bg-white/80 border border-white text-blue-600 font-bold text-sm rounded-lg shadow-sm">
              22.5%
            </div>
          </div>
          
          <div class="h-3 w-full bg-white/60 shadow-inner border border-white rounded-full overflow-hidden mt-4 relative">
            <div class="h-full bg-blue-600 rounded-full transition-all duration-1000 relative" style="width: 22.5%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

        <div class="dashboard-card animate-fade-in-up delay-300">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-amber-100 text-amber-600 rounded-xl shadow-sm border border-white"><i data-feather="briefcase" class="w-5 h-5"></i></div>
              <div>
                <h3 class="font-bold text-slate-800 text-lg font-title">Business Capital</h3>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Jan 2026</p>
              </div>
            </div>
            <button class="text-slate-400 hover:text-blue-600 hover:bg-white/60 p-2 rounded-xl transition-all"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-3 flex justify-between items-end">
            <div>
              <p class="text-3xl font-bold font-title text-slate-800">₱150k</p>
              <p class="text-sm font-medium text-slate-500">of ₱1M</p>
            </div>
            <div class="px-3 py-1 bg-white/80 border border-white text-amber-600 font-bold text-sm rounded-lg shadow-sm">
              15%
            </div>
          </div>
          
          <div class="h-3 w-full bg-white/60 shadow-inner border border-white rounded-full overflow-hidden mt-4 relative">
            <div class="h-full bg-amber-500 rounded-full transition-all duration-1000 relative" style="width: 15%;">
              <div class="absolute inset-0 bg-white/20"></div>
            </div>
          </div>
        </div>

        <div class="dashboard-card animate-fade-in-up delay-100">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-fuchsia-100 text-fuchsia-600 rounded-xl shadow-sm border border-white"><i data-feather="map" class="w-5 h-5"></i></div>
              <div>
                <h3 class="font-bold text-slate-800 text-lg font-title">Vacation Trip</h3>
                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider flex items-center gap-1 mt-0.5"><i data-feather="calendar" class="w-3 h-3"></i> Target: Aug 2024</p>
              </div>
            </div>
            <button class="text-slate-400 hover:text-blue-600 hover:bg-white/60 p-2 rounded-xl transition-all"><i data-feather="more-horizontal" class="w-5 h-5"></i></button>
          </div>
          
          <div class="mb-3 flex justify-between items-end">
            <div>
              <p class="text-3xl font-bold font-title text-slate-800">₱60k</p>
              <p class="text-sm font-medium text-slate-500">of ₱80k</p>
            </div>
            <div class="px-3 py-1 bg-white/80 border border-white text-fuchsia-600 font-bold text-sm rounded-lg shadow-sm">
              75%
            </div>
          </div>
          
          <div class="h-3 w-full bg-white/60 shadow-inner border border-white rounded-full overflow-hidden mt-4 relative">
            <div class="h-full bg-fuchsia-600 rounded-full transition-all duration-1000 relative" style="width: 75%;">
              <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
            </div>
          </div>
        </div>

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
            
            <div class="p-6 space-y-5">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Goal Name</label>
                    <input type="text" placeholder="e.g. New Laptop" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Amount</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                            <input type="number" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Date</label>
                        <input type="month" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Initial Saved Amount (Optional)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                        <input type="number" placeholder="0.00" class="w-full pl-9 pr-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm">
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                <button id="cancelModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">
                    Cancel
                </button>
                <button id="saveBtn" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">
                    Save Goal
                </button>
            </div>
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

    if(menuButton) menuButton.addEventListener('click', toggleSidebar);
    if(closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);

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

    if(openModalBtn) openModalBtn.addEventListener('click', openModal);
    if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if(cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
    if(saveBtn) saveBtn.addEventListener('click', closeModal);

    if(modalBackdrop) {
        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) closeModal();
        });
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
