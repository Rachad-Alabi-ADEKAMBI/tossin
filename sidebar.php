<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$pageTitles = [
    'index.php' => 'Tableau de bord',
    'sales.php' => 'Ventes',
    'orders.php' => 'Commandes',
    'claims.php' => 'Créances',
    'expenses.php' => 'Dépenses',
    'products.php' => 'Produits',
    'notifications.php' => 'Notifications',
    'financials.php' => 'États financiers',
    'settings.php' => 'Paramètres',
];
$pageTitle = $pageTitles[$currentPage] ?? 'TOBI LODA';
function navClass($page, $current) {
    return $page === $current
        ? 'flex items-center px-6 py-3 text-primary bg-blue-50 border-r-4 border-primary'
        : 'flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors';
}
?>
<!-- Sidebar -->
<div id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <div class="flex items-center justify-center">
        <div class="flex items-center space-x-2" style="height: 165px;">
            <img src="public/images/logo.png" alt="">
        </div>
    </div>

    <nav class="mt-8 flex flex-col h-[calc(100%-4rem)]">
        <div class="flex-1">
            <a href="index.php" class="<?= navClass('index.php', $currentPage) ?>">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="expenses.php" class="<?= navClass('expenses.php', $currentPage) ?>">
                <i class="fas fa-wallet mr-3"></i>
                <span>Dépenses</span>
            </a>

            <a href="orders.php" class="<?= navClass('orders.php', $currentPage) ?>">
                <i class="fas fa-shopping-cart mr-3"></i>
                <span>Commandes</span>
            </a>

            <a href="financials.php" class="<?= navClass('financials.php', $currentPage) ?>">
                <i class="fas fa-file-alt mr-3"></i>
                <span>États financiers</span>
            </a>

            <a href="notifications.php" class="<?= navClass('notifications.php', $currentPage) ?> border-t">
                <i class="fas fa-bell mr-3"></i>
                <span>Notifications</span>
            </a>

            <a href="settings.php" class="<?= navClass('settings.php', $currentPage) ?> border-t border-gray-200">
                <i class="fas fa-cog mr-3"></i>
                <span>Paramètres</span>
            </a>
        </div>
        <a href="#" onclick="confirmLogout(); return false;" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors border-t border-gray-200">
            <i class="fas fa-sign-out-alt mr-3"></i>
            <span>Déconnexion</span>
        </a>
    </nav>
</div>

<!-- Overlay pour mobile -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

<!-- Barre fixe mobile : hamburger + titre + user -->
<div class="lg:hidden fixed top-0 left-0 right-0 z-[60] bg-white shadow-sm px-4 py-3 flex items-center justify-between" style="border-bottom:1px solid #e5e7eb;">
    <div class="flex items-center space-x-3">
        <button onclick="toggleSidebar()" class="p-1 -ml-1">
            <i class="fas fa-bars text-gray-700 text-xl"></i>
        </button>
        <span class="font-semibold text-gray-800 text-base truncate max-w-[180px]"><?= htmlspecialchars($pageTitle) ?></span>
    </div>
    <div class="flex items-center space-x-1 text-sm text-gray-500">
        <i class="fas fa-user-circle"></i>
        <span class="font-medium truncate max-w-[100px]"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
</div>

<style>
    .lg\:ml-64.min-h-screen { padding-bottom: 5rem; }
    @media (min-width: 1024px) { .bottom-nav-lg { left: 16rem !important; } }
    @media (max-width: 1023px) { .lg\:ml-64.min-h-screen { padding-top: 3.5rem !important; } }
    @media (max-width: 1023px) { .fixed.inset-0.z-50:not(.z-40) { z-index: 100000 !important; } .fixed.inset-0.z-50 > .flex.items-center.justify-center.min-h-screen { padding: 4.5rem 0.75rem 5.5rem 0.75rem !important; } }
</style>

<!-- Barre de navigation du bas -->
<div class="bottom-nav-lg" style="position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#ffffff;border-top:3px solid #2563EB;box-shadow:0 -2px 10px rgba(0,0,0,0.1);height:64px;">
    <div style="display:flex;align-items:center;justify-content:space-around;height:100%;max-width:600px;margin:0 auto;padding:0 8px;">
        <a href="claims.php" style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:33%;text-decoration:none;color:<?= $currentPage === 'claims.php' ? '#2563EB' : '#6b7280' ?>;">
            <i class="fas fa-money-bill-wave" style="font-size:1.25rem"></i>
            <span style="font-size:0.75rem;margin-top:2px">Créances</span>
        </a>
        <a href="sales.php" style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:33%;text-decoration:none;color:<?= $currentPage === 'sales.php' ? '#2563EB' : '#6b7280' ?>;">
            <div style="background:#2563EB;color:white;width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-top:-18px;box-shadow:0 4px 12px rgba(37,99,235,0.3);">
                <i class="fas fa-file-invoice-dollar" style="font-size:1.25rem"></i>
            </div>
            <span style="font-size:0.75rem;margin-top:2px">Factures</span>
        </a>
        <a href="products.php" style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:33%;text-decoration:none;color:<?= $currentPage === 'products.php' ? '#2563EB' : '#6b7280' ?>;">
            <i class="fas fa-boxes" style="font-size:1.25rem"></i>
            <span style="font-size:0.75rem;margin-top:2px">Produits</span>
        </a>
    </div>
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

    function confirmLogout() {
        if (confirm('Confirmer la déconnexion ?')) {
            window.location.href = 'api/index.php?action=logout';
        }
    }
</script>
