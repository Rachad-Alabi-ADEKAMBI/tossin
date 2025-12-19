<!-- Sidebar -->
<div id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="flex items-center justify-center h-16 bg-primary">
        <div class="flex items-center space-x-2">
            <i class="fas fa-building text-white text-2xl"></i>
            <span class="text-white text-xl font-bold">Gbemiro</span>
        </div>
    </div>

    <nav class="mt-8">
        <a href="index.php" class="flex items-center px-6 py-3 text-primary bg-blue-50 border-r-4 border-primary">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Tableau de bord</span>
        </a>

        <a href="sales.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
            <i class="fas fa-file-invoice-dollar mr-3"></i>
            <span>Factures</span>
        </a>

        <a href="products.php"
            class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
            <i class="fas fa-boxes mr-3"></i>
            <span>Produits</span>
        </a>

        <a href="orders.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
            <i class="fas fa-shopping-cart mr-3"></i>
            <span>Commandes</span>
        </a>

        <a href="claims.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
            <i class="fas fa-money-bill-wave mr-3"></i>
            <span>Créances</span>
        </a>

        <a href="expenses.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
            <i class="fas fa-wallet mr-3"></i>
            <span>Dépenses</span>
        </a>


    </nav>
</div>

<!-- Overlay pour mobile -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- Bouton mobile -->
<div class="lg:hidden fixed top-4 left-4 z-50">
    <button onclick="toggleSidebar()" class="bg-white p-2 rounded-lg shadow-lg">
        <i class="fas fa-bars text-gray-700"></i>
    </button>
</div>

<script>
    let sidebarOpen = false;

    function toggleSidebar() {
        sidebarOpen = !sidebarOpen;
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>