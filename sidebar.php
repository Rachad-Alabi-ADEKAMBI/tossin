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
    // 'settings.php' => 'Paramètres',
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
    class="fixed inset-y-0 left-0 z-[100001] w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
    <!-- Bouton fermer (mobile uniquement) -->
    <button onclick="toggleSidebar()" aria-label="Fermer le menu"
        class="lg:hidden absolute top-4 right-4 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600 text-white shadow-md transition-colors">
        <i class="fas fa-times text-lg"></i>
    </button>
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

            <!-- Paramètres removed -->
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

<!-- Statistiques rapides pour mobile (affichées dans le menu hamburger) -->
<div id="mobile-stats" class="hidden lg:hidden bg-gray-50 p-4 border-t border-gray-200">
    <h3 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Résumé financier</h3>
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-chart-pie text-blue-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-500 uppercase">Chiffre d'affaires</p>
                    <p class="text-lg font-bold text-gray-900">0 FCFA</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <div class="flex items-center">
                <div class="bg-emerald-100 p-2 rounded-lg">
                    <i class="fas fa-wallet text-emerald-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-500 uppercase">Résultat net</p>
                    <p class="text-lg font-bold text-gray-900">0 FCFA</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i class="fas fa-file-invoice-dollar text-purple-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-500 uppercase">Ventes</p>
                    <p class="text-lg font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <div class="flex items-center">
                <div class="bg-red-100 p-2 rounded-lg">
                    <i class="fas fa-wallet text-red-600 text-sm"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-500 uppercase">Dépenses</p>
                    <p class="text-lg font-bold text-gray-900">0</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Contenu principal : éviter d'être caché par le header mobile et le bottom nav */
    .lg\:ml-64.min-h-screen,
    #app.lg\:ml-64.min-h-screen {
        padding-bottom: 6rem !important;
    }
    @media (max-width: 1023px) {
        .lg\:ml-64.min-h-screen,
        #app.lg\:ml-64.min-h-screen {
            padding-top: 4.5rem !important;
            padding-bottom: 5.5rem !important;
        }
    }
    @media (min-width: 1024px) {
        .lg\:ml-64.min-h-screen,
        #app.lg\:ml-64.min-h-screen {
            padding-bottom: 1.5rem !important;
        }
        .bottom-nav-lg { left: 16rem !important; }
    }

    /* Empêcher la bottom nav de cacher le bas des formulaires */
    form, .modal-content, [class*="modal"], .form-section {
        scroll-margin-bottom: 100px;
    }

    @media (max-width: 1023px) {
        /* Filet de sécurité : tout overlay de modal passe AU-DESSUS du header
           (99990) et du menu du bas (99999) afin de ne jamais être masqué par
           ces barres fixes. Le centrage + my-8 + scroll de l'overlay (comme sur
           le formulaire de nouvelle vente) garantissent l'espace haut/bas. */
        .fixed.inset-0.bg-gray-600,
        .fixed.inset-0.bg-black,
        .fixed.inset-0.bg-gray-900,
        .fixed.inset-0[class*="bg-opacity"] {
            z-index: 100000 !important;
            /* espace réservé en haut (header ~60px) et en bas (menu ~64px) pour
               que les bords du formulaire ne soient jamais sous les barres fixes */
            padding-top: 4rem !important;
            padding-bottom: 5rem !important;
            box-sizing: border-box !important;
        }

        /* Espacement supplémentaire pour les formulaires en mobile */
        .p-6 > form,
        .p-6 > .form-container,
        .p-6 > div > form,
        [class*="p-"] form {
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }
    }
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
        const mobileStats = document.getElementById('mobile-stats');

        if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            mobileStats.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            mobileStats.classList.add('hidden');
        }
    }

    function confirmLogout() {
        if (confirm('Confirmer la déconnexion ?')) {
            window.location.href = 'api/index.php?action=logout';
        }
    }
</script>
