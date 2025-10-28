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
    <title>Gestion des Ventes - Gbemiro</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .primary {
            color: #2563EB;
        }

        .secondary {
            color: #1E40AF;
        }

        .accent {
            color: #10B981;
        }

        .bg-primary {
            background-color: #2563EB;
        }

        .bg-secondary {
            background-color: #1E40AF;
        }

        .bg-accent {
            background-color: #10B981;
        }

        .hover\:bg-accent:hover {
            background-color: #059669;
        }

        .hover\:bg-secondary:hover {
            background-color: #1E40AF;
        }

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                padding: 10px;
                border-radius: 8px;
                background: white;
            }

            td {
                border: none;
                position: relative;
                padding-left: 50% !important;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            td:before {
                content: attr(data-label) ": ";
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                color: #374151;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }

            @page {
                margin: 1cm;
                size: A4;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="bg-gray-50 min-h-screen">
            <?php include 'sidebar.php'; ?>

            <div class="lg:ml-64 min-h-screen">
                <header class="bg-white shadow-sm border-b">
                    <div class="px-6 py-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                            <h1 class="text-2xl font-bold text-gray-900">Gestion des Ventes</h1>
                            <div class="flex space-x-3">
                                <button @click="printSalesList" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer la liste
                                </button>
                                <button @click="openNewSaleModal" class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle vente
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Client, numéro facture..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="date">Date</option>
                                    <option value="buyer">Client</option>
                                    <option value="total">Montant</option>
                                    <option value="invoice_number">N° Facture</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous</option>
                                    <option value="paid">Payé</option>
                                    <option value="pending">En attente</option>
                                    <option value="cancelled">Annulé</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button @click="applyFilters" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-filter mr-2"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total des ventes</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalSales) }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Nombre de ventes</p>
                                <p class="text-2xl font-bold text-blue-600">{{ filteredSales.length }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">En attente</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ pendingSalesCount }}</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Vente moyenne</p>
                                <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(averageSale) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Facture</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in paginatedSales" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'N° Facture'">
                                            {{ sale.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ sale.buyer }}</div>
                                                    <div class="text-sm text-gray-500">{{ sale.phone }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date'">
                                            {{ formatDate(sale.date_of_insertion) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" :data-label="'Montant'">
                                            {{ formatCurrency(sale.total, sale.currency) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Statut'">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusClass(sale.status)]">
                                                {{ getStatusLabel(sale.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <button @click="viewSaleDetails(sale)" class="text-blue-600 hover:text-blue-800 mr-3" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="printInvoice(sale)" class="text-green-600 hover:text-green-800 mr-3" title="Imprimer facture">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button @click="deleteSale(sale.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button @click="previousPage" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                    Précédent
                                </button>
                                <button @click="nextPage" :disabled="currentPage === totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                    Suivant
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Affichage de <span class="font-medium">{{ startItem }}</span> à <span class="font-medium">{{ endItem }}</span> sur <span class="font-medium">{{ totalItems }}</span> résultats
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <button @click="previousPage" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium', currentPage === page ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50']">
                                            {{ page }}
                                        </button>
                                        <button @click="nextPage" :disabled="currentPage === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Nouvelle Vente -->
            <div v-if="showSaleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-invoice mr-2"></i>Nouvelle Vente / Facture
                            </h3>
                            <button @click="closeSaleModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveSale" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1"></i>Acheteur
                                    </label>
                                    <input v-model="saleForm.buyer" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1"></i>Téléphone
                                    </label>
                                    <input v-model="saleForm.phone" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins mr-1"></i>Devise
                                    </label>
                                    <select v-model="saleForm.currency" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="XOF">XOF (Franc CFA)</option>
                                        <option value="N">N (Naira)</option>
                                        <option value="GHC">GHC (Ghana Cedis)</option>
                                        <option value="EUR">EUR (Euro)</option>
                                        <option value="USD">USD (Dollar)</option>
                                        <option value="GBP">GBP (Livre Sterling)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>Statut
                                    </label>
                                    <select v-model="saleForm.status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="pending">En attente</option>
                                        <option value="paid">Payée</option>
                                        <option value="cancelled">Annulée</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Section produits -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-list mr-2"></i>Produits
                                    </h4>
                                    <button type="button" @click="addProductLine"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Ajouter produit
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="(line, index) in saleForm.lines" :key="index"
                                        class="grid grid-cols-1 md:grid-cols-5 gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-box mr-1"></i>Produit
                                            </label>
                                            <input v-model="line.product" type="text" required
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                                            </label>
                                            <input v-model.number="line.quantity" type="number" min="1" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire
                                            </label>
                                            <input v-model.number="line.price" type="number" step="0.01" min="0" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-calculator mr-1"></i>Total
                                            </label>
                                            <input :value="formatCurrency(line.total, saleForm.currency)" type="text" readonly
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-gray-100">
                                        </div>

                                        <div class="flex items-end">
                                            <button type="button" @click="removeProductLine(index)"
                                                class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-2 rounded text-sm transition-colors">
                                                <i class="fas fa-trash mr-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Résumé -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            <i class="fas fa-boxes mr-2"></i>Quantité totale:
                                        </span>
                                        <span class="text-lg font-semibold text-blue-600">{{ saleTotalQuantity }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Montant total:</span>
                                        <span class="text-2xl font-bold text-green-600">{{ formatCurrency(saleTotal, saleForm.currency) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit"
                                    class="flex-1 bg-accent hover:bg-green-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                                <button type="button" @click="closeSaleModal"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- Modal Détails Vente -->
            <div v-if="showDetailsModal && selectedSale" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-invoice mr-2"></i>Détails de la vente
                            </h3>
                            <div class="flex space-x-2">
                                <button @click="printInvoice(selectedSale)" class="no-print bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-print mr-1"></i>Imprimer
                                </button>
                                <button @click="closeDetailsModal" class="no-print text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm font-medium text-gray-700">N° Facture</p>
                                <p class="text-lg font-semibold text-blue-600">{{ selectedSale.id }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm font-medium text-gray-700">Client</p>
                                <p class="text-lg font-semibold text-green-600">{{ selectedSale.buyer }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm font-medium text-gray-700">Date</p>
                                <p class="text-lg font-semibold text-yellow-600">{{ formatDate(selectedSale.date_of_insertion) }}</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm font-medium text-gray-700">Statut</p>
                                <span :class="['inline-block px-3 py-1 text-sm font-semibold rounded-full', getStatusClass(selectedSale.status)]">
                                    {{ getStatusLabel(selectedSale.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Added product lines section with edit functionality -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-medium text-gray-900">
                                    <i class="fas fa-list mr-2"></i>Produits
                                </h4>
                                <div class="flex space-x-2 no-print">
                                    <button @click="addNewProductLine" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Ajouter produit
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(line, index) in selectedSale.lines" :key="index" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm">
                                                <div v-if="line.editing">
                                                    <input v-model="line.product" type="text" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </div>
                                                <div v-else>{{ line.product }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div v-if="line.editing">
                                                    <input v-model.number="line.quantity" type="number" min="1" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </div>
                                                <div v-else>{{ line.quantity }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div v-if="line.editing">
                                                    <input v-model.number="line.price" type="number" step="0.01" min="0" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </div>
                                                <div v-else>{{ formatCurrency(line.price, selectedSale.currency) }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium">{{ formatCurrency(line.quantity * line.price, selectedSale.currency) }}</td>
                                            <td class="px-4 py-3 text-sm no-print">
                                                <div v-if="line.editing" class="flex space-x-3">
                                                    <button @click="validateProductEdit(index)" class="text-green-600 hover:text-green-800" title="Valider">
                                                        <i class="fas fa-check fa-lg"></i>
                                                    </button>
                                                    <button @click="cancelProductEdit(index)" class="text-gray-600 hover:text-gray-800" title="Annuler">
                                                        <i class="fas fa-times fa-lg"></i>
                                                    </button>
                                                </div>
                                                <div v-else class="flex space-x-3">
                                                    <button @click="editProductLine(index)" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                                        <i class="fas fa-edit fa-lg"></i>
                                                    </button>
                                                    <button @click="deleteSaleItem(line.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                        <i class="fas fa-trash fa-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- New product line row -->
                                        <tr v-if="newProductLine.visible" class="bg-green-50">
                                            <td class="px-4 py-3 text-sm">
                                                <input v-model="newProductLine.product" type="text" placeholder="Nom du produit" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <input v-model.number="newProductLine.quantity" type="number" min="1" placeholder="Quantité" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <input v-model.number="newProductLine.price" type="number" step="0.01" min="0" placeholder="Prix" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium">{{ formatCurrency(newProductLine.quantity * newProductLine.price || 0, selectedSale.currency) }}</td>
                                            <td class="px-4 py-3 text-sm no-print">
                                                <div class="flex space-x-3">
                                                    <button @click="validateNewLine" class="text-green-600 hover:text-green-800" title="Valider">
                                                        <i class="fas fa-check fa-lg"></i>
                                                    </button>
                                                    <button @click="cancelNewLine" class="text-gray-600 hover:text-gray-800" title="Annuler">
                                                        <i class="fas fa-times fa-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Added totals summary -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-boxes mr-2"></i>Quantité totale:
                                    </span>
                                    <span class="text-lg font-semibold text-blue-600">{{ selectedSaleTotalQuantity }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">Total de la vente:</span>
                                    <span class="text-2xl font-bold text-green-600">{{ formatCurrency(selectedSaleTotal, selectedSale.currency) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue;

        const api = axios.create({
            baseURL: 'api/index.php'
        });

        createApp({
            data() {
                return {
                    searchTerm: '',
                    sortBy: 'date',
                    statusFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showSaleModal: false,
                    showDetailsModal: false,
                    selectedSale: null,
                    sales: [],
                    saleForm: {
                        buyer: '',
                        phone: '',
                        currency: 'XOF',
                        status: 'pending',
                        lines: []
                    },
                    newProductLine: {
                        visible: false,
                        product: '',
                        quantity: '',
                        price: ''
                    }
                };
            },

            mounted() {
                this.fetchSales();
            },

            computed: {
                filteredSales() {
                    let filtered = this.sales.filter(sale => {
                        const search = this.searchTerm.toLowerCase();

                        const matchesSearch =
                            sale.buyer.toLowerCase().includes(search) ||
                            (sale.phone && sale.phone.includes(this.searchTerm)) ||
                            sale.invoice_number.toLowerCase().includes(search);

                        const matchesStatus =
                            this.statusFilter === 'all' || sale.status === this.statusFilter;

                        return matchesSearch && matchesStatus;
                    });

                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'buyer':
                                return a.buyer.localeCompare(b.buyer);
                            case 'total':
                                return parseFloat(b.total) - parseFloat(a.total);
                            case 'date':
                                return new Date(b.date_of_insertion) - new Date(a.date_of_insertion);
                            case 'invoice_number':
                                return a.invoice_number.localeCompare(b.invoice_number);
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },
                totalItems() {
                    return this.filteredSales.length;
                },

                totalPages() {
                    return Math.ceil(this.totalItems / this.itemsPerPage);
                },

                startItem() {
                    return (this.currentPage - 1) * this.itemsPerPage + 1;
                },

                endItem() {
                    return Math.min(this.currentPage * this.itemsPerPage, this.totalItems);
                },

                paginatedSales() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredSales.slice(start, start + this.itemsPerPage);
                },

                visiblePages() {
                    const pages = [];
                    const total = this.totalPages;
                    const current = this.currentPage;
                    for (let i = 1; i <= total; i++) {
                        if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
                            pages.push(i);
                        }
                    }
                    return pages;
                },

                totalSales() {
                    return this.filteredSales
                        .filter(sale => sale.status === 'paid')
                        .reduce((sum, sale) => sum + parseFloat(sale.total), 0);
                },

                pendingSalesCount() {
                    return this.filteredSales.filter(sale => sale.status === 'pending').length;
                },

                averageSale() {
                    const paidSales = this.filteredSales.filter(sale => sale.status === 'paid');
                    return paidSales.length > 0 ? this.totalSales / paidSales.length : 0;
                },

                saleTotal() {
                    return this.saleForm.lines.reduce((total, line) => total + (line.total || 0), 0);
                },

                saleTotalQuantity() {
                    return this.saleForm.lines.reduce((total, line) => total + (parseFloat(line.quantity) || 0), 0);
                },

                selectedSaleTotal() {
                    if (!this.selectedSale || !this.selectedSale.lines) return 0;
                    return this.selectedSale.lines.reduce((total, line) => total + (line.quantity * line.price), 0);
                },

                selectedSaleTotalQuantity() {
                    if (!this.selectedSale || !this.selectedSale.lines) return 0;
                    return this.selectedSale.lines.reduce((total, line) => total + (parseFloat(line.quantity) || 0), 0);
                }
            },

            methods: {
                async fetchSales() {
                    try {
                        const response = await api.get('?action=allSales');
                        this.sales = response.data.map(sale => ({
                            ...sale,
                            lines: []
                        }));
                        await this.loadSalesProducts();
                    } catch (error) {
                        console.error('Erreur lors de la récupération des ventes:', error);
                    }
                },

                async loadSalesProducts() {
                    try {
                        const response = await api.get('?action=allSalesProducts');
                        const products = response.data;

                        this.sales.forEach(sale => {
                            sale.lines = products.filter(product => product.sale_id == sale.id).map(product => ({
                                id: product.id,
                                product: product.name,
                                quantity: product.quantity,
                                price: parseFloat(product.price),
                                editing: false,
                                originalData: null
                            }));
                        });
                    } catch (error) {
                        console.error('Erreur lors du chargement des produits:', error);
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    try {
                        return new Date(dateString).toLocaleDateString('fr-FR');
                    } catch (e) {
                        console.error("Invalid date string:", dateString, e);
                        return '';
                    }
                },

                formatCurrency(amount, currency = 'XOF') {
                    if (amount === null || amount === undefined || isNaN(amount)) {
                        return '0.00 ' + currency;
                    }
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount) + ' ' + currency;
                },

                getStatusClass(status) {
                    switch (status) {
                        case 'paid':
                            return 'text-green-800 bg-green-100 border border-green-200';
                        case 'pending':
                            return 'text-yellow-800 bg-yellow-100 border border-yellow-200';
                        case 'cancelled':
                            return 'text-red-800 bg-red-100 border border-red-200';
                        default:
                            return 'text-gray-800 bg-gray-100 border border-gray-200';
                    }
                },

                getStatusLabel(status) {
                    switch (status) {
                        case 'paid':
                            return 'Payé';
                        case 'pending':
                            return 'En attente';
                        case 'cancelled':
                            return 'Annulé';
                        default:
                            return status;
                    }
                },

                applyFilters() {
                    this.currentPage = 1;
                },

                previousPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },

                goToPage(page) {
                    this.currentPage = page;
                },

                printSalesList() {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Liste des Ventes</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 12px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>LISTE DES VENTES</h1>
                                <p>Date d'impression: ${currentDate}</p>
                                <p>Nombre total de ventes: ${this.filteredSales.length}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Total des ventes (payées):</strong> ${this.formatCurrency(this.totalSales)}</p>
                                <p><strong>Ventes en attente:</strong> ${this.pendingSalesCount}</p>
                                <p><strong>Vente moyenne:</strong> ${this.formatCurrency(this.averageSale)}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredSales.forEach(sale => {
                        printContent += `
                            <tr>
                                <td>${sale.id}</td>
                                <td>${sale.buyer}</td>
                                <td>${this.formatDate(sale.date_of_insertion)}</td>
                                <td>${this.formatCurrency(sale.total, sale.currency)}</td>
                                <td>${this.getStatusLabel(sale.status)}</td>
                            </tr>`;
                    });

                    printContent += `
                                </tbody>
                            </table>
                        </body>
                        </html>`;

                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                },

                printInvoice(sale) {
                    const printWindow = window.open('', '_blank');

                    const productsRows = sale.lines.map(line => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">${line.product}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${line.quantity}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">${this.formatCurrency(line.price, sale.currency)}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">${this.formatCurrency(line.quantity * line.price, sale.currency)}</td>
                        </tr>
                    `).join('');

                    const totalQuantity = sale.lines.reduce((sum, line) => sum + parseFloat(line.quantity), 0);
                    const totalAmount = sale.lines.reduce((sum, line) => sum + (line.quantity * line.price), 0);

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Facture ${sale.id}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 40px; }
                                .invoice-header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2563EB; padding-bottom: 20px; }
                                .invoice-details { margin: 30px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
                                .detail-box { padding: 15px; background-color: #f9fafb; border-radius: 8px; }
                                .label { font-weight: bold; color: #374151; margin-bottom: 5px; }
                                .value { color: #1F2937; font-size: 16px; }
                                .products-section { margin: 30px 0; }
                                .products-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                .products-table th { background-color: #2563EB; color: white; padding: 12px; text-align: left; }
                                .products-table td { border: 1px solid #ddd; padding: 8px; }
                                .products-table tr:nth-child(even) { background-color: #f9fafb; }
                                .totals-section { margin-top: 30px; padding: 20px; background-color: #f0f9ff; border: 2px solid #2563EB; }
                                .total-row { display: flex; justify-content: space-between; margin: 10px 0; }
                                .total-amount { font-size: 28px; font-weight: bold; color: #2563EB; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="invoice-header">
                                <h1>GBEMIRO</h1>
                                <h2>Facture ${sale.id}</h2>
                            </div>
                            
                            <div class="invoice-details">
                                <div class="detail-box">
                                    <div class="label">Client:</div>
                                    <div class="value">${sale.buyer}</div>
                                </div>
                                <div class="detail-box">
                                    <div class="label">Téléphone:</div>
                                    <div class="value">${sale.phone || '-'}</div>
                                </div>
                                <div class="detail-box">
                                    <div class="label">Date:</div>
                                    <div class="value">${this.formatDate(sale.date_of_insertion)}</div>
                                </div>
                                <div class="detail-box">
                                    <div class="label">Statut:</div>
                                    <div class="value">${this.getStatusLabel(sale.status)}</div>
                                </div>
                            </div>
                            
                            <div class="products-section">
                                <h3>Détails des produits</h3>
                                <table class="products-table">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th style="text-align: center;">Quantité</th>
                                            <th style="text-align: right;">Prix unitaire</th>
                                            <th style="text-align: right;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${productsRows}
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="totals-section">
                                <div class="total-row">
                                    <span style="font-size: 16px; font-weight: bold;">Quantité totale:</span>
                                    <span style="font-size: 18px; font-weight: bold; color: #059669;">${totalQuantity}</span>
                                </div>
                                <div class="total-row" style="border-top: 2px solid #2563EB; padding-top: 15px; margin-top: 15px;">
                                    <span style="font-size: 20px; font-weight: bold;">MONTANT TOTAL:</span>
                                    <span class="total-amount">${this.formatCurrency(totalAmount, sale.currency)}</span>
                                </div>
                            </div>
                            
                            <div class="footer">
                                <p>Merci pour votre confiance!</p>
                                <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                            </div>
                        </body>
                        </html>`;

                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                },

                openNewSaleModal() {
                    this.saleForm = {
                        buyer: '',
                        phone: '',
                        currency: 'XOF',
                        status: 'pending',
                        lines: []
                    };
                    this.showSaleModal = true;
                },

                closeSaleModal() {
                    this.showSaleModal = false;
                },

                viewSaleDetails(sale) {
                    this.selectedSale = {
                        ...sale,
                        lines: [...sale.lines]
                    };
                    this.showDetailsModal = true;
                },

                closeDetailsModal() {
                    this.showDetailsModal = false;
                    this.selectedSale = null;
                },

                addProductLine() {
                    this.saleForm.lines.push({
                        product: '',
                        quantity: '',
                        price: 0,
                        total: 0
                    });
                },
                removeProductLine(index) {
                    this.saleForm.lines.splice(index, 1);
                },
                updateLineTotal(index) {
                    const line = this.saleForm.lines[index];
                    line.total = line.quantity * line.price;
                },
                saveSale() {
                    if (this.saleForm.lines.length === 0) {
                        alert('Veuillez ajouter au moins un produit');
                        return;
                    }

                    for (let line of this.saleForm.lines) {
                        if (!line.product || !line.quantity || line.price === null || line.price === undefined) {
                            alert('Veuillez remplir tous les champs des produits');
                            return;
                        }
                    }

                    const saleData = {
                        buyer: this.saleForm.buyer,
                        phone: this.saleForm.phone,
                        total: this.saleTotal,
                        currency: this.saleForm.currency,
                        status: this.saleForm.status,
                        lines: this.saleForm.lines
                    };

                    api.post('?action=newSale', saleData)
                        .then(response => {
                            const data = response.data;

                            if (!data.success) {
                                alert('Erreur: ' + (data.message || 'Une erreur est survenue.'));
                                return;
                            }

                            alert('Nouvelle vente ajoutée avec succès!');
                            this.closeSaleModal();
                            this.fetchSales();
                        })
                        .catch(error => {
                            console.error('Erreur lors de l\'enregistrement:', error);
                            alert('Erreur lors de l\'enregistrement de la vente');
                        });
                },
                deleteSale(saleId) {
                    if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous vraiment sûr de vouloir supprimer cette vente?')) {
                        return;
                    }
                    api.post('?action=deleteSale', {
                            id: saleId
                        })
                        .then(response => {
                            if (response.data.success) {
                                alert('Vente supprimée avec succès!');
                                this.fetchSales();
                            } else {
                                alert('Erreur lors de la suppression: ' + response.data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors de la suppression:', error);
                            alert('Erreur lors de la suppression de la vente');
                        });
                },

                addNewProductLine() {
                    this.newProductLine = {
                        visible: true,
                        product: '',
                        quantity: '',
                        price: 0
                    };
                },

                async validateNewLine() {
                    if (!this.newProductLine.product || !this.newProductLine.quantity || !this.newProductLine.price) {
                        alert('Veuillez remplir tous les champs');
                        return;
                    }

                    try {
                        const response = await api.post('?action=newSaleProduct', {
                            sale_id: this.selectedSale.id,
                            name: this.newProductLine.product,
                            quantity: this.newProductLine.quantity,
                            price: this.newProductLine.price
                        });

                        if (response.data.success) {
                            const newLine = {
                                id: response.data.product_id,
                                product: this.newProductLine.product,
                                quantity: this.newProductLine.quantity,
                                price: this.newProductLine.price,
                                editing: false,
                                originalData: null
                            };

                            this.selectedSale.lines.push(newLine);

                            // Update the main sales array
                            const saleIndex = this.sales.findIndex(s => s.id === this.selectedSale.id);
                            if (saleIndex !== -1) {
                                this.sales[saleIndex].lines.push(newLine);
                                this.sales[saleIndex].total = this.selectedSaleTotal;
                            }

                            this.cancelNewLine();
                            alert('Produit ajouté avec succès!');
                        } else {
                            alert('Erreur lors de l\'ajout du produit');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de l\'ajout du produit');
                    }
                },

                cancelNewLine() {
                    this.newProductLine = {
                        visible: false,
                        product: '',
                        quantity: '',
                        price: ''
                    };
                },

                editProductLine(index) {
                    const line = this.selectedSale.lines[index];
                    line.originalData = {
                        product: line.product,
                        quantity: line.quantity,
                        price: line.price
                    };
                    line.editing = true;
                },

                async validateProductEdit(index) {
                    const line = this.selectedSale.lines[index];

                    if (!line.product || line.quantity <= 0 || line.price < 0) {
                        alert('Veuillez remplir correctement tous les champs');
                        return;
                    }

                    try {
                        const response = await api.post('?action=updateSaleProduct', {
                            id: line.id,
                            name: line.product,
                            quantity: line.quantity,
                            price: line.price
                        });

                        if (response.data.success) {
                            line.editing = false;
                            line.originalData = null;

                            // Update the main sales array
                            const saleIndex = this.sales.findIndex(s => s.id === this.selectedSale.id);
                            if (saleIndex !== -1) {
                                const productIndex = this.sales[saleIndex].lines.findIndex(l => l.id === line.id);
                                if (productIndex !== -1) {
                                    this.sales[saleIndex].lines[productIndex] = {
                                        ...line
                                    };
                                    this.sales[saleIndex].total = this.selectedSaleTotal;
                                }
                            }

                            alert('Produit modifié avec succès!');
                        } else {
                            alert('Erreur lors de la modification du produit');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la modification du produit');
                    }
                },

                cancelProductEdit(index) {
                    const line = this.selectedSale.lines[index];
                    if (line.originalData) {
                        line.product = line.originalData.product;
                        line.quantity = line.originalData.quantity;
                        line.price = line.originalData.price;
                        line.originalData = null;
                    }
                    line.editing = false;
                },

                deleteSaleItem(itemId) {
                    if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous vraiment sûr de vouloir supprimer cet article?')) {
                        return;
                    }

                    api.post('?action=deleteSaleProduct', {
                            id: itemId
                        })
                        .then(response => {
                            if (response.data.success) {
                                // Update the selectedSale.lines immediately to reflect deletion
                                const deletedLineIndex = this.selectedSale.lines.findIndex(line => line.id === itemId);
                                if (deletedLineIndex !== -1) {
                                    this.selectedSale.lines.splice(deletedLineIndex, 1);
                                }

                                // Update the main sales array to reflect the new total
                                const saleIndex = this.sales.findIndex(s => s.id === this.selectedSale.id);
                                if (saleIndex !== -1) {
                                    this.sales[saleIndex].lines = [...this.selectedSale.lines];
                                    this.sales[saleIndex].total = this.selectedSaleTotal;
                                }

                                alert('Article supprimé avec succès!');
                            } else {
                                alert('Erreur lors de la suppression: ' + response.data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors de la suppression:', error);
                            alert('Erreur lors de la suppression de l\'article');
                        });
                }
            }
        }).mount('#app');
    </script>
</body>

</html>