<?php
session_start(); // <-- à mettre en tout premier

if (!isset($_SESSION['user_id'])) {
    // Redirection vers la page de login
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOBI LODA - Tableau de Bord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="public/images/logo.png">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563EB',
                        secondary: '#1E40AF',
                        accent: '#F59E0B'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen">
    <?php include 'sidebar.php'; ?>

    <div id="app" v-cloak class="lg:ml-64 min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Tableau de Bord</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ todayDate }}</p>
                    </div>
                    <div class="hidden lg:flex items-center text-sm text-gray-500 space-x-2">
                        <i class="fas fa-user-circle"></i>
                        <span class="font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                        <span class="text-xs text-gray-400">· Administrateur</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Ventes du jour</p>
                            <p class="text-base md:text-2xl font-bold text-gray-900">{{ todaySalesCount }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ formatCurrency(todaySalesTotal) }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-receipt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Créances échéance ≤ 3 jours</p>
                            <p class="text-base md:text-2xl font-bold text-gray-900">{{ claimsDueSoon }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Produits stock faible</p>
                            <p class="text-base md:text-2xl font-bold text-gray-900">{{ lowStockCount }}</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-boxes text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Chiffre d'affaires (hier)</p>
                            <p class="text-base md:text-2xl font-bold text-gray-900">{{ formatCurrency(yesterdayTotal) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-chart-line text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Added loading indicator -->
            <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <span>Chargement des données...</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const { createApp } = Vue;

        const api = axios.create({
            baseURL: 'api/index.php'
        });

        createApp({
            data() {
                return {
                    loading: true,
                    todaySalesCount: 0,
                    todaySalesTotal: 0,
                    yesterdayTotal: 0,
                    claimsDueSoon: 0,
                    lowStockCount: 0
                }
            },
            computed: {
                todayDate() {
                    return new Date().toLocaleDateString('fr-FR', {
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                    });
                }
            },
            methods: {
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'decimal',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount) + ' XOF';
                },

                async fetchDashboardStats() {
                    try {
                        const response = await api.get('?action=dashboardStats');
                        if (response.data.success) {
                            this.todaySalesCount = response.data.todaySalesCount;
                            this.todaySalesTotal = response.data.todaySalesTotal;
                            this.yesterdayTotal = response.data.yesterdayTotal;
                            this.claimsDueSoon = response.data.claimsDueSoon;
                            this.lowStockCount = response.data.lowStockCount;
                        }
                    } catch (error) {
                        console.error('Erreur tableau de bord:', error);
                    }
                },

                async initDashboard() {
                    this.loading = true;
                    await this.fetchDashboardStats();
                    this.loading = false;
                }
            },

            mounted() {
                this.initDashboard();
            }
        }).mount('#app');
    </script>
</body>

</html>