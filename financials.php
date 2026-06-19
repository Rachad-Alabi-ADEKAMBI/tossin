<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>États Financiers - TOBI LODA</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="icon" type="image/x-icon" href="public/images/logo.png">
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
    <style>
        .bg-primary { background-color: #2563EB; }
        .bg-secondary { background-color: #1E40AF; }
        .bg-accent { background-color: #F59E0B; }
        .hover\:bg-accent:hover { background-color: #059669; }
        .hover\:bg-secondary:hover { background-color: #1E40AF; }
        .text-primary { color: #2563EB; }

        .tab-active {
            border-bottom: 3px solid #2563EB;
            color: #2563EB;
            font-weight: 600;
        }

        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 1.5cm; size: A4 portrait; }
        }

        .profit-positive { color: #059669; }
        .profit-negative { color: #DC2626; }

        .fade-enter-active, .fade-leave-active {
            transition: opacity 0.2s ease;
        }
        .fade-enter-from, .fade-leave-to {
            opacity: 0;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="app" v-cloak>
        <div class="bg-gray-50 min-h-screen">
            <?php include 'sidebar.php'; ?>

            <div class="lg:ml-64 min-h-screen">
                <header class="bg-white shadow-sm border-b no-print">
                    <div class="px-6 py-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>États Financiers
                                </h1>
                                <p class="text-sm text-gray-500 mt-1">Documents comptables selon le système OHADA</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button @click="printReport" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm">
                                    <i class="fas fa-print mr-2"></i>Imprimer le rapport
                                </button>
                                <button @click="refreshData" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm">
                                    <i class="fas fa-sync-alt mr-2"></i>Actualiser
                                </button>
                            </div>
                            <div class="hidden lg:flex items-center space-x-1 text-sm text-gray-500 border-l pl-3 ml-3">
                                <i class="fas fa-user-circle"></i>
                                <span class="font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                                <span class="text-xs text-gray-400">· Admin</span>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <!-- Période -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 no-print">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                                <select v-model="selectedPeriod" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="today">Aujourd'hui</option>
                                    <option value="yesterday">Hier</option>
                                    <option value="this_week">Cette semaine</option>
                                    <option value="this_month">Ce mois-ci</option>
                                    <option value="last_month">Le mois dernier</option>
                                    <option value="this_quarter">Ce trimestre</option>
                                    <option value="this_year">Cette année</option>
                                    <option value="custom">Personnalisé</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                                <input v-model="customStart" type="date" :disabled="selectedPeriod !== 'custom'" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                                <input v-model="customEnd" type="date" :disabled="selectedPeriod !== 'custom'" @change="fetchReport" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100">
                            </div>
                            <div class="flex items-end">
                                <p class="text-sm text-gray-500 py-2" v-if="report.period">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Du {{ report.period.start }} au {{ report.period.end }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Indicateurs clés -->
                    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Chiffre d'affaires</p>
                            <p class="text-base md:text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(report.revenue) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ report.salesCount }} vente(s)</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Coût des ventes (COGS)</p>
                            <p class="text-base md:text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(report.cogs) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Marge brute</p>
                            <p class="text-xl font-bold mt-1" :class="report.grossProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(report.grossProfit) }}</p>
                            <p class="text-xs text-gray-400 mt-1" v-if="report.revenue > 0">{{ ((report.grossProfit / report.revenue) * 100).toFixed(1) }} % de marge</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Dépenses</p>
                            <p class="text-base md:text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(report.expenses) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ report.expensesCount }} charge(s)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Résultat net</p>
                            <p class="text-xl font-bold mt-1" :class="report.netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(report.netProfit) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ report.netProfit >= 0 ? 'Bénéfice' : 'Perte' }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Valeur du stock</p>
                            <p class="text-base md:text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(report.stockValue) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Créances clients</p>
                            <p class="text-base md:text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(report.receivables) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-cyan-500">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Marge nette</p>
                            <p class="text-xl font-bold mt-1" :class="report.netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ report.revenue > 0 ? ((report.netProfit / report.revenue) * 100).toFixed(1) : '0.0' }} %</p>
                        </div>
                    </div>

                    <!-- Onglets -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                        <div class="border-b border-gray-200 no-print">
                            <nav class="flex overflow-x-auto">
                                <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key" class="px-5 py-3 text-sm whitespace-nowrap transition-colors" :class="activeTab === t.key ? 'tab-active' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    <i :class="t.icon + ' mr-2'"></i>{{ t.label }}
                                </button>
                            </nav>
                        </div>

                        <div class="print-area">
                            <!-- Onglet: Compte de résultat -->
                            <div v-if="activeTab === 'income'" class="p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-6"><i class="fas fa-chart-pie text-primary mr-2"></i>Compte de résultat</h2>
                                <p class="text-xs text-gray-500 mb-4">Période du {{ report.period.start }} au {{ report.period.end }}</p>

                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">Rubrique</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-1/4">Montant (FCFA)</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-1-4">% CA</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr class="bg-green-50">
                                            <td class="border border-gray-300 px-4 py-2 font-semibold">Chiffre d'affaires</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-semibold">{{ formatCurrency(report.revenue) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">100 %</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 text-gray-600">- Coût des ventes (COGS)</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right text-red-600">({{ formatCurrency(report.cogs) }})</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ report.revenue > 0 ? ((report.cogs / report.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr class="bg-purple-50">
                                            <td class="border border-gray-300 px-4 py-2 font-semibold">= Marge brute</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-semibold" :class="report.grossProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(report.grossProfit) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right" :class="report.grossProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ report.revenue > 0 ? ((report.grossProfit / report.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 text-gray-600">- Dépenses d'exploitation</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right text-red-600">({{ formatCurrency(report.expenses) }})</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ report.revenue > 0 ? ((report.expenses / report.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr class="bg-amber-50 font-bold">
                                            <td class="border border-gray-300 px-4 py-2">= Résultat net</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right" :class="report.netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(report.netProfit) }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right" :class="report.netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ report.revenue > 0 ? ((report.netProfit / report.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="mt-6 grid grid-cols-2 gap-4">
                                    <div class="bg-emerald-50 rounded-lg p-4 text-center">
                                        <p class="text-xs text-gray-500 uppercase">Total produits</p>
                                        <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(report.revenue) }}</p>
                                    </div>
                                    <div class="bg-red-50 rounded-lg p-4 text-center">
                                        <p class="text-xs text-gray-500 uppercase">Total charges</p>
                                        <p class="text-xl font-bold text-red-600">{{ formatCurrency(report.cogs + report.expenses) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet: Ventes -->
                            <div v-if="activeTab === 'sales'" class="p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-6"><i class="fas fa-receipt text-primary mr-2"></i>Journal des ventes</h2>
                                <p class="text-xs text-gray-500 mb-4">Période du {{ report.period.start }} au {{ report.period.end }} · {{ report.salesCount }} vente(s)</p>

                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Ventes par méthode de paiement</h3>
                                <table class="w-full border-collapse mb-6">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Méthode</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="m in report.salesByMethod" :key="m.method" class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2 capitalize">{{ m.method }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ m.count }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(m.total) }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Top produits vendus</h3>
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Produit</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Quantité</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="p in report.topProducts" :key="p.name" class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2">{{ p.name }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ p.qty }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(p.total) }}</td>
                                        </tr>
                                        <tr v-if="report.topProducts.length === 0">
                                            <td colspan="3" class="border border-gray-300 px-4 py-2 text-center text-gray-400">Aucune vente sur cette période</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Onglet: Dépenses -->
                            <div v-if="activeTab === 'expenses'" class="p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-6"><i class="fas fa-wallet text-primary mr-2"></i>Rapport des charges</h2>
                                <p class="text-xs text-gray-500 mb-4">Période du {{ report.period.start }} au {{ report.period.end }} · {{ report.expensesCount }} charge(s)</p>

                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Catégorie</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                            <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        <tr v-for="c in report.expensesByCategory" :key="c.category" class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-4 py-2 capitalize">{{ c.category }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right">{{ c.count }}</td>
                                            <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(c.total) }}</td>
                                        </tr>
                                        <tr v-if="report.expensesByCategory.length === 0">
                                            <td colspan="3" class="border border-gray-300 px-4 py-2 text-center text-gray-400">Aucune dépense sur cette période</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Onglet: Bilan -->
                            <div v-if="activeTab === 'balance'" class="p-6">
                                <h2 class="text-lg font-bold text-gray-900 mb-6"><i class="fas fa-balance-scale text-primary mr-2"></i>Bilan comptable simplifié</h2>
                                <p class="text-xs text-gray-500 mb-4">Arrêté au {{ report.period.end }}</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-sm font-bold text-emerald-700 mb-3 uppercase tracking-wider"><i class="fas fa-arrow-right mr-1"></i> Actif</h3>
                                        <table class="w-full border-collapse">
                                            <thead>
                                                <tr class="bg-emerald-50">
                                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rubrique</th>
                                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm">
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">Stock de marchandises</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(report.stockValue) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">Créances clients</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(report.receivables) }}</td>
                                                </tr>
                                                <tr class="bg-emerald-50 font-bold">
                                                    <td class="border border-gray-300 px-4 py-2">Total Actif</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(report.stockValue + report.receivables) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-blue-700 mb-3 uppercase tracking-wider"><i class="fas fa-arrow-left mr-1"></i> Passif & Capitaux</h3>
                                        <table class="w-full border-collapse">
                                            <thead>
                                                <tr class="bg-blue-50">
                                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rubrique</th>
                                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm">
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">Capitaux propres estimés</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right font-medium">{{ formatCurrency(Math.max(0, (report.stockValue + report.receivables))) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">Résultat de la période</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right font-medium" :class="report.netProfit >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatCurrency(report.netProfit) }}</td>
                                                </tr>
                                                <tr class="bg-blue-50 font-bold">
                                                    <td class="border border-gray-300 px-4 py-2">Total Passif</td>
                                                    <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(Math.max(0, (report.stockValue + report.receivables)) + report.netProfit) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 no-print">
            <div class="bg-white p-6 rounded-lg shadow-xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700">Génération du rapport...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        const api = axios.create({
            baseURL: 'api/index.php'
        });

        createApp({
            data() {
                return {
                    loading: false,
                    selectedPeriod: 'this_month',
                    customStart: '',
                    customEnd: '',
                    activeTab: 'income',
                    tabs: [
                        { key: 'income', label: 'Compte de résultat', icon: 'fas fa-chart-pie' },
                        { key: 'sales', label: 'Journal des ventes', icon: 'fas fa-receipt' },
                        { key: 'expenses', label: 'Rapport des charges', icon: 'fas fa-wallet' },
                        { key: 'balance', label: 'Bilan comptable', icon: 'fas fa-balance-scale' }
                    ],
                    report: {
                        period: { start: '', end: '', label: '' },
                        revenue: 0,
                        salesCount: 0,
                        cogs: 0,
                        grossProfit: 0,
                        expenses: 0,
                        expensesCount: 0,
                        netProfit: 0,
                        stockValue: 0,
                        receivables: 0,
                        salesByMethod: [],
                        expensesByCategory: [],
                        topProducts: []
                    }
                };
            },
            methods: {
                formatCurrency(amount) {
                    const num = parseFloat(amount) || 0;
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'decimal',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(num) + ' FCFA';
                },

                async fetchReport() {
                    this.loading = true;
                    try {
                        const params = { action: 'financialReport', period: this.selectedPeriod };
                        if (this.selectedPeriod === 'custom') {
                            params.start_date = this.customStart;
                            params.end_date = this.customEnd;
                        }
                        const response = await api.get('', { params });
                        if (response.data.success) {
                            this.report = response.data;
                        }
                    } catch (error) {
                        console.error('Erreur rapport financier:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                refreshData() {
                    this.fetchReport();
                },

                printReport() {
                    window.print();
                }
            },

            mounted() {
                const now = new Date();
                this.customEnd = now.toISOString().split('T')[0];
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                this.customStart = firstDay.toISOString().split('T')[0];
                this.fetchReport();
            }
        }).mount('#app');
    </script>
</body>
</html>
