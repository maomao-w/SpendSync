<?php
include 'session_manager.php';
include 'config.php';

$user_id = $_SESSION['user_id'];

$category_query = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(t.transaction_date, '%M %Y') as month_label,
        c.category_name, 
        SUM(t.amount) as total 
    FROM transactions t
    LEFT JOIN categories c ON t.category_id = c.category_id
    WHERE t.user_id = '$user_id' AND t.type = 'Expense'
    GROUP BY YEAR(t.transaction_date), MONTH(t.transaction_date), t.category_id
    ORDER BY YEAR(t.transaction_date) DESC, MONTH(t.transaction_date) DESC
");

$monthly_expenses = [];
if ($category_query && mysqli_num_rows($category_query) > 0) {
    while($row = mysqli_fetch_assoc($category_query)) {
        $month_label = $row['month_label'];
        $monthly_expenses[$month_label][] = $row;
    }
}
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpendSync - Detailed Reports</title>
  
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
            primary: '#2563eb'
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            title: ['Poppins', 'sans-serif'],
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

    .glass-panel { 
        @apply bg-white/60 backdrop-blur-md border border-white rounded-[2rem] p-6 shadow-sm transition-all duration-500 ease-out; 
        box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.05), inset 0 1px 0 rgba(255,255,255,0.9);
    }
    .glass-panel:hover {
        @apply shadow-xl -translate-y-1;
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

    .sidebar-link { @apply flex items-center gap-3 px-5 py-3.5 mx-4 rounded-2xl text-slate-500 font-medium transition-all duration-300; }
    .sidebar-link:hover { @apply bg-white/70 text-primary shadow-sm border border-white translate-x-2; }
    .sidebar-link.active { @apply bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-bold shadow-lg shadow-blue-500/30 border border-white/20; }

    .bg-blobs { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; overflow: hidden; z-index: -2; pointer-events: none; }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4; animation: floatBlob 15s infinite alternate; }
    .blob-blue { background: #2563eb; width: 500px; height: 500px; top: -10%; left: -10%; }
    .blob-emerald { background: #10b981; width: 400px; height: 400px; bottom: -10%; right: -10%; animation-delay: -5s; }
    
    @keyframes floatBlob { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="flex h-screen overflow-hidden relative">

  <div class="bg-blobs">
    <div class="blob blob-blue"></div>
    <div class="blob blob-emerald"></div>
  </div>

  <div id="webgl-container" class="absolute inset-0 z-[-1]"></div>

  <aside class="w-64 flex-col glass-sidebar h-full hidden md:flex z-50 relative">
    <div class="h-24 flex items-center px-8 border-b border-white/60">
      <div class="flex items-center gap-3 font-bold text-2xl font-title tracking-tight text-blue-600 hover:scale-105 transition-transform cursor-pointer">
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">SpendSync</span>
      </div>
    </div>
    <nav class="flex-1 py-8 space-y-3 overflow-y-auto overflow-x-hidden">
      <a href="homepage.php" class="sidebar-link"><i data-feather="layout" class="w-5 h-5"></i> Dashboard</a>
      <a href="transactions.php" class="sidebar-link"><i data-feather="activity" class="w-5 h-5"></i> Transactions</a>
      <a href="budgets.php" class="sidebar-link"><i data-feather="pie-chart" class="w-5 h-5"></i> Budgets</a>
      <a href="categories.php" class="sidebar-link"><i data-feather="grid" class="w-5 h-5"></i> Categories</a>  
      <a href="goals.php" class="sidebar-link"><i data-feather="target" class="w-5 h-5"></i> Goals</a>
      <a href="reports.php" class="sidebar-link active"><i data-feather="file-text" class="w-5 h-5"></i> Reports</a>
      
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

  <div class="flex-1 flex flex-col h-full overflow-hidden p-6 sm:p-8 z-10 relative">
    
    <header class="h-24 flex items-center justify-center px-8 bg-white/60 backdrop-blur-md rounded-[2rem] shadow-sm mb-6 border border-white shrink-0 relative">
      <div class="absolute left-8 md:hidden">
        <button id="menuButton" class="p-2.5 bg-white/60 backdrop-blur-md rounded-xl border border-white text-slate-700">
          <i data-feather="menu" class="w-6 h-6"></i>
        </button>
      </div>
      <div class="text-center">
        <h1 class="text-3xl font-bold font-title text-slate-800">Detailed Reports</h1>
        <p class="text-slate-500 text-sm font-medium mt-1">View your overall expense breakdown.</p>
      </div>
    </header>

    <main class="flex-1 overflow-auto space-y-6 pb-10">
      
      <?php if (!empty($monthly_expenses)): ?>
          <?php foreach ($monthly_expenses as $month_name => $expenses_list): ?>
              <div class="glass-panel max-w-4xl mx-auto">
                <h2 class="text-lg font-bold font-title mb-6 text-slate-800">Expense Breakdown (<?php echo htmlspecialchars($month_name); ?>)</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-xs uppercase font-bold tracking-wider">
                                <th class="py-4 px-4">Category</th>
                                <th class="py-4 px-4 text-right">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        <?php 
                            $grand_total = 0;
                            foreach ($expenses_list as $row) {
                                
                                $cat_name = htmlspecialchars($row['category_name'] ?? 'Uncategorized');
                                $amt = $row['total'];
                                $grand_total += $amt;
                                echo "<tr class='border-b border-slate-100 hover:bg-white/50 transition-colors'>
                                        <td class='py-4 px-4 font-bold text-slate-700'>{$cat_name}</td>
                                        <td class='py-4 px-4 text-right font-bold text-rose-500'>₱ " . number_format($amt, 2) . "</td>
                                      </tr>";
                            }
                            ?>

                            <tr class="bg-white/40">
                                <td class="py-5 px-4 font-bold text-slate-800 uppercase text-xs tracking-wider">Grand Total</td>
                                <td class="py-5 px-4 text-right font-bold text-slate-800 text-xl">₱ <?php echo number_format($grand_total, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
              </div>
          <?php endforeach; ?>
      <?php else: ?>
          <div class="glass-panel max-w-4xl mx-auto">
            <h2 class="text-lg font-bold font-title mb-6 text-slate-800">Overall Expense Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 text-xs uppercase font-bold tracking-wider">
                            <th class="py-4 px-4">Category</th>
                            <th class="py-4 px-4 text-right">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan='2' class='py-8 text-center text-slate-500'>No expenses recorded yet.</td></tr>
                    </tbody>
                </table>
            </div>
          </div>
      <?php endif; ?>

    </main>
  </div>

  <script>
    feather.replace();

    // THREE.JS BACKGROUND
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
