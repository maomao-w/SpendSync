<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Handle Add Custom Category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']); // 'Income' or 'Expense'
    
    $insert_sql = "INSERT INTO categories (user_id, category_name, type, is_default) VALUES ('$user_id', '$category_name', '$type', FALSE)";
    if (mysqli_query($conn, $insert_sql)) {
        $message = "<div class='mb-6 p-4 rounded-2xl bg-emerald-100/80 backdrop-blur-md text-emerald-700 font-bold border border-emerald-200 shadow-sm animate-fade-in-up'>Category added successfully!</div>";
    } else {
        $message = "<div class='mb-6 p-4 rounded-2xl bg-rose-100/80 backdrop-blur-md text-rose-700 font-bold border border-rose-200 shadow-sm animate-fade-in-up'>Error adding category.</div>";
    }
}

// Handle Delete Custom Category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_category'])) {
    $cat_id = (int)$_POST['category_id'];
    
    // Only delete if it belongs to the user and is NOT a default category
    $delete_sql = "DELETE FROM categories WHERE category_id = '$cat_id' AND user_id = '$user_id' AND is_default = FALSE";
    if (mysqli_query($conn, $delete_sql)) {
        $message = "<div class='mb-6 p-4 rounded-2xl bg-emerald-100/80 backdrop-blur-md text-emerald-700 font-bold border border-emerald-200 shadow-sm animate-fade-in-up'>Category deleted successfully!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Categories</title>
  
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
    @layer base { body { @apply bg-background text-foreground font-sans antialiased; } }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { @apply bg-blue-200 rounded-full; border: 1px solid rgba(0,0,0,0.02); }

    .dashboard-card {
      @apply bg-card border border-white rounded-[2rem] p-6 transition-all duration-500 ease-out;
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.05), inset 0 1px 0 rgba(255,255,255,0.9);
    }
    
    .glass-sidebar {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(30px);
        margin: 1rem 0 1rem 1rem;
        border-radius: 2rem;
        height: calc(100% - 2rem) !important;
        border: 1px solid rgba(255,255,255,0.8);
        box-shadow: 0 10px 40px rgba(37,99,235,0.05);
    }
    
    body > div.flex-1 {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(25px);
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

    .sidebar-link { @apply flex items-center gap-3 px-5 py-3.5 mx-4 rounded-2xl text-slate-500 font-medium transition-all duration-300 ease-in-out; }
    .sidebar-link:hover { @apply bg-white/70 text-blue-600 translate-x-2 shadow-sm border border-white; }
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
      <a href="categories.php" class="sidebar-link active"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>
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
          <h1 class="text-2xl font-bold font-title text-slate-800">Categories</h1>
          <p class="text-slate-500 text-sm font-medium mt-1">Manage your custom income and expense labels.</p>
        </div>
      </div>
    </header>

    <main class="flex-1 overflow-auto p-6 sm:p-8">
      
      <div class="flex justify-between items-center mb-6 animate-fade-in-up">
          <a href="homepage.php" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white rounded-xl transition-all">
              <i data-feather="arrow-left" class="w-4 h-4"></i> Back
          </a>
          <button id="openModalBtn" class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white text-sm font-bold rounded-xl shadow-md hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 transition-all">
              <i data-feather="plus" class="w-4 h-4"></i> Add Category
          </button>
      </div>

      <?php echo $message; ?>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="dashboard-card animate-fade-in-up delay-100">
            <h3 class="text-lg font-bold font-title text-slate-800 mb-4 flex items-center gap-2 border-b border-white pb-4">
                <div class="p-2 bg-rose-100 text-rose-600 rounded-lg"><i data-feather="trending-down" class="w-4 h-4"></i></div>
                Expense Categories
            </h3>
            <ul class="space-y-3">
                <?php
                $exp_query = mysqli_query($conn, "SELECT * FROM categories WHERE type='Expense' AND (user_id IS NULL OR user_id='$user_id') ORDER BY is_default DESC, category_name ASC");
                while($row = mysqli_fetch_assoc($exp_query)) {
                    $is_default = $row['is_default'];
                    echo "<li class='flex justify-between items-center p-3.5 bg-white/40 hover:bg-white/60 rounded-xl border border-white shadow-sm transition-colors'>";
                    echo "<span class='font-bold text-slate-700 flex items-center gap-3'>" . htmlspecialchars($row['category_name']) . "</span>";
                    
                    if (!$is_default) {
                        echo "<form method='POST' class='m-0' onsubmit='return confirm(\"Delete this category?\");'>
                                <input type='hidden' name='category_id' value='{$row['category_id']}'>
                                <button type='submit' name='delete_category' class='text-slate-400 hover:text-rose-500 p-1.5 rounded-md hover:bg-rose-50 transition-colors'><i data-feather='trash-2' class='w-4 h-4'></i></button>
                              </form>";
                    } else {
                        echo "<span class='text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-100/50 px-2 py-1 rounded-md'>System</span>";
                    }
                    echo "</li>";
                }
                ?>
            </ul>
        </div>

        <div class="dashboard-card animate-fade-in-up delay-200">
            <h3 class="text-lg font-bold font-title text-slate-800 mb-4 flex items-center gap-2 border-b border-white pb-4">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><i data-feather="trending-up" class="w-4 h-4"></i></div>
                Income Categories
            </h3>
            <ul class="space-y-3">
                <?php
                $inc_query = mysqli_query($conn, "SELECT * FROM categories WHERE type='Income' AND (user_id IS NULL OR user_id='$user_id') ORDER BY is_default DESC, category_name ASC");
                while($row = mysqli_fetch_assoc($inc_query)) {
                    $is_default = $row['is_default'];
                    echo "<li class='flex justify-between items-center p-3.5 bg-white/40 hover:bg-white/60 rounded-xl border border-white shadow-sm transition-colors'>";
                    echo "<span class='font-bold text-slate-700 flex items-center gap-3'>" . htmlspecialchars($row['category_name']) . "</span>";
                    
                    if (!$is_default) {
                        echo "<form method='POST' class='m-0' onsubmit='return confirm(\"Delete this category?\");'>
                                <input type='hidden' name='category_id' value='{$row['category_id']}'>
                                <button type='submit' name='delete_category' class='text-slate-400 hover:text-rose-500 p-1.5 rounded-md hover:bg-rose-50 transition-colors'><i data-feather='trash-2' class='w-4 h-4'></i></button>
                              </form>";
                    } else {
                        echo "<span class='text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-100/50 px-2 py-1 rounded-md'>System</span>";
                    }
                    echo "</li>";
                }
                ?>
            </ul>
        </div>

      </div>
    </main>

    <div id="modalBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div id="categoryModal" class="bg-white/80 backdrop-blur-2xl border border-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden hidden animate-modal">
            <div class="flex justify-between items-center p-6 border-b border-white/60 bg-white/40">
                <h2 class="text-xl font-bold font-title text-slate-800">Add New Category</h2>
                <button id="closeModalBtn" class="text-slate-400 hover:bg-white hover:text-rose-500 p-2 rounded-xl transition-all shadow-sm border border-transparent hover:border-white"><i data-feather="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="">
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g. Freelance, Subscriptions..." class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-bold text-slate-800 transition-colors shadow-sm" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Type</label>
                        <select name="type" class="w-full px-4 py-3 text-sm border border-white rounded-xl bg-white/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 font-medium text-slate-800 transition-colors shadow-sm" required>
                            <option value="Expense">Expense</option>
                            <option value="Income">Income</option>
                        </select>
                    </div>
                </div>
                <div class="p-6 border-t border-white/60 flex justify-end gap-3 bg-white/40">
                    <button type="button" id="cancelModalBtn" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-white/60 border border-white hover:bg-white hover:shadow-sm rounded-xl transition-all">Cancel</button>
                    <button type="submit" name="add_category" class="px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-emerald-500 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 rounded-xl transition-all">Save Category</button>
                </div>
            </form>
        </div>
    </div>
  </div>

  <script>
    feather.replace();

    // Sidebar Toggle
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

    // Modal Logic
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const categoryModal = document.getElementById('categoryModal');
    
    function openModal() { modalBackdrop.classList.remove('hidden'); categoryModal.classList.remove('hidden'); }
    function closeModal() { modalBackdrop.classList.add('hidden'); categoryModal.classList.add('hidden'); }
    
    openModalBtn?.addEventListener('click', openModal);
    closeModalBtn?.addEventListener('click', closeModal);
    cancelModalBtn?.addEventListener('click', closeModal);
    modalBackdrop?.addEventListener('click', (e) => { if (e.target === modalBackdrop) closeModal(); });

    // Three.js Background
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
