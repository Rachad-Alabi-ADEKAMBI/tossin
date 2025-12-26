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
    <title>Gbemiro - Tableau de Bord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Added Vue.js and axios for dynamic dashboard -->
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

    <div id="app" class="lg:ml-64 min-h-screen">
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">Tableau de Bord</h1>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500 flex items-center space-x-2">
                            <i class="fas fa-user"></i>
                            <span id="currentUser">Admin</span>
                            <!-- Icône déconnexion -->
                            <a id="logoutBtn" class="text-gray-500 hover:text-red-500" href="api/index.php?action=logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            <!-- Replaced total amount cards with count cards for sales, claims, expenses, and orders -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nombre de Ventes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ nombreVentes }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-receipt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nombre de Créances</p>
                            <p class="text-2xl font-bold text-gray-900">{{ nombreCreances }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nombre de Dépenses</p>
                            <p class="text-2xl font-bold text-gray-900">{{ nombreDepenses }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-wallet text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nombre de Commandes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ nombreCommandes }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Made recent activity dynamic with latest orders and claims
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activité récente</h3>
                    <div class="space-y-3">
                        <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center p-3 border-l-4 bg-blue-50" :class="activity.borderColor">
                            <i :class="activity.icon" class="mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                                <p class="text-xs text-gray-500">{{ activity.description }}</p>
                            </div>
                        </div>
                        <div v-if="recentActivities.length === 0" class="text-center text-gray-500 py-4">
                            Aucune activité récente
                        </div>
                    </div>
                </div> -->
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

    <!-- Added Vue.js application with dynamic data fetching -->
    <script>
        const {
            createApp
        } = Vue;

        // Crée une instance Axios avec une baseURL
        const api = axios.create({
            baseURL: 'api/index.php'
        });

        createApp({
            data() {
                return {
                    loading: true,
                    orders: [],
                    claims: [],
                    sales: [],
                    expenses: [],
                    recentActivities: []
                }
            },
            computed: {
                nombreVentes() {
                    return this.sales.length;
                },

                nombreCreances() {
                    return this.claims.length;
                },

                nombreDepenses() {
                    return this.expenses.length;
                },

                nombreCommandes() {
                    return this.orders.length;
                }
            },
            methods: {
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'decimal',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount) + ' XOF';
                },


                async fetchOrders() {
                    try {
                        const response = await api.get('?action=allOrders');
                        this.orders = response.data || [];
                    } catch (error) {
                        console.error('Erreur lors du chargement des commandes:', error);
                        this.orders = [];
                    }
                },

                async fetchClaims() {
                    try {
                        const response = await api.get('?action=allClaims');
                        this.claims = response.data || [];
                    } catch (error) {
                        console.error('Erreur lors du chargement des créances:', error);
                        this.claims = [];
                    }
                },

                async fetchSales() {
                    try {
                        const response = await api.get('?action=allSales');
                        this.sales = response.data || [];
                    } catch (error) {
                        console.error('Erreur lors du chargement des ventes:', error);
                        this.sales = [];
                    }
                },

                async fetchExpenses() {
                    try {
                        const response = await api.get('?action=allExpenses');
                        this.expenses = response.data || [];
                    } catch (error) {
                        console.error('Erreur lors du chargement des dépenses:', error);
                        this.expenses = [];
                    }
                },

                generateRecentActivities() {
                    const activities = [];

                    // Add recent orders
                    const recentOrders = [...this.orders]
                        .sort((a, b) => new Date(b.date_of_insertion) - new Date(a.date_of_insertion))
                        .slice(0, 3);

                    recentOrders.forEach(order => {
                        activities.push({
                            id: `order-${order.id}`,
                            title: 'Nouvelle commande',
                            description: `${order.seller} - ${this.formatCurrency(order.total)}`,
                            icon: 'fas fa-shopping-cart text-green-600',
                            borderColor: 'border-green-500'
                        });
                    });

                    // Add recent claims
                    const recentClaims = [...this.claims]
                        .sort((a, b) => new Date(b.date_of_insertion) - new Date(a.date_of_insertion))
                        .slice(0, 2);

                    recentClaims.forEach(claim => {
                        activities.push({
                            id: `claim-${claim.id}`,
                            title: 'Nouvelle créance',
                            description: `${claim.seller} - ${this.formatCurrency(claim.total)}`,
                            icon: 'fas fa-money-bill-wave text-blue-600',
                            borderColor: 'border-blue-500'
                        });
                    });

                    // Sort by date and limit to 5 most recent
                    this.recentActivities = activities.slice(0, 5);
                },

                async initDashboard() {
                    this.loading = true;
                    try {
                        await Promise.all([
                            this.fetchOrders(),
                            this.fetchClaims(),
                            this.fetchSales(),
                            this.fetchExpenses()
                        ]);
                        this.generateRecentActivities();
                    } catch (error) {
                        console.error('Erreur lors de l\'initialisation du tableau de bord:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            },

            mounted() {
                this.initDashboard();
            }
        }).mount('#app');
    </script>
</body>

</html>