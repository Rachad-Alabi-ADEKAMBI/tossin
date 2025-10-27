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
                                    <option value="client_name">Client</option>
                                    <option value="total_amount">Montant</option>
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
                                            #{{ sale.invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ sale.client_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ sale.client_phone }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date'">
                                            {{ formatDate(sale.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" :data-label="'Montant'">
                                            {{ formatCurrency(sale.total_amount, sale.currency) }}
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1"></i>Nom du client
                                    </label>
                                    <input v-model="saleForm.client_name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1"></i>Téléphone
                                    </label>
                                    <input v-model="saleForm.client_phone" type="tel" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date
                                    </label>
                                    <input v-model="saleForm.date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-hashtag mr-1"></i>N° Facture
                                    </label>
                                    <input v-model="saleForm.invoice_number" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <!-- Removed total_amount input field -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins mr-1"></i>Devise
                                    </label>
                                    <select v-model="saleForm.currency" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                                    <select v-model="saleForm.status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="paid">Payé</option>
                                        <option value="pending">En attente</option>
                                        <option value="cancelled">Annulé</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Added product lines section -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-list mr-2"></i>Produits
                                    </h4>
                                    <button type="button" @click="addProductLine" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Ajouter produit
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="(line, index) in saleForm.lines" :key="index" class="grid grid-cols-1 md:grid-cols-5 gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
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

                                <!-- Added total summary section -->
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes / Description
                                </label>
                                <textarea v-model="saleForm.notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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

            <div v-if="showDetailsModal && selectedSale" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-invoice mr-2"></i>Détails de la vente
                            </h3>
                            <button @click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">N° Facture</p>
                                    <p class="text-lg font-semibold">#{{ selectedSale.invoice_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date</p>
                                    <p class="text-lg font-semibold">{{ formatDate(selectedSale.date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Client</p>
                                    <p class="text-lg font-semibold">{{ selectedSale.client_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Téléphone</p>
                                    <p class="text-lg font-semibold">{{ selectedSale.client_phone }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Montant</p>
                                    <p class="text-lg font-semibold text-green-600">{{ formatCurrency(selectedSale.total_amount, selectedSale.currency) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Statut</p>
                                    <span :class="['px-3 py-1 text-sm font-semibold rounded-full', getStatusClass(selectedSale.status)]">
                                        {{ getStatusLabel(selectedSale.status) }}
                                    </span>
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
                        client_name: '',
                        client_phone: '',
                        date: new Date().toISOString().split('T')[0],
                        invoice_number: '',
                        currency: 'XOF',
                        status: 'paid',
                        lines: []
                    }
                };
            },

            mounted() {
                this.fetchSales();
                this.generateInvoiceNumber();
            },

            computed: {
                filteredSales() {
                    let filtered = this.sales.filter(sale => {
                        const matchesSearch =
                            sale.client_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            sale.client_phone.includes(this.searchTerm) ||
                            sale.invoice_number.toLowerCase().includes(this.searchTerm.toLowerCase());

                        const matchesStatus = this.statusFilter === 'all' || sale.status === this.statusFilter;

                        return matchesSearch && matchesStatus;
                    });

                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'client_name':
                                return a.client_name.localeCompare(b.client_name);
                            case 'total_amount':
                                return parseFloat(b.total_amount) - parseFloat(a.total_amount);
                            case 'date':
                                return new Date(b.date) - new Date(a.date);
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
                        .reduce((sum, sale) => sum + parseFloat(sale.total_amount), 0);
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
                }
            },

            methods: {
                fetchSales() {
                    api.get('?action=allSales')
                        .then(response => {
                            this.sales = response.data;
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des ventes:', error);
                        });
                },

                generateInvoiceNumber() {
                    const date = new Date();
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                    this.saleForm.invoice_number = `INV-${year}${month}-${random}`;
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR');
                },

                formatCurrency(amount, currency = 'XOF') {
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
                                <td>#${sale.invoice_number}</td>
                                <td>${sale.client_name}</td>
                                <td>${this.formatDate(sale.date)}</td>
                                <td>${this.formatCurrency(sale.total_amount, sale.currency)}</td>
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

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Facture ${sale.invoice_number}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 40px; }
                                .invoice-header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2563EB; padding-bottom: 20px; }
                                .invoice-details { margin: 30px 0; }
                                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; }
                                .label { font-weight: bold; color: #374151; }
                                .value { color: #1F2937; }
                                .amount-section { margin-top: 40px; padding: 20px; background-color: #f0f9ff; border: 2px solid #2563EB; text-align: center; }
                                .total-amount { font-size: 32px; font-weight: bold; color: #2563EB; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; }
                            </style>
                        </head>
                        <body>
                            <div class="invoice-header">
                                <h1>FACTURE</h1>
                                <h2>#${sale.invoice_number}</h2>
                            </div>
                            
                            <div class="invoice-details">
                                <div class="detail-row">
                                    <span class="label">Client:</span>
                                    <span class="value">${sale.client_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Téléphone:</span>
                                    <span class="value">${sale.client_phone}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Date:</span>
                                    <span class="value">${this.formatDate(sale.date)}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Statut:</span>
                                    <span class="value">${this.getStatusLabel(sale.status)}</span>
                                </div>
                                ${sale.notes ? `
                                <div class="detail-row">
                                    <span class="label">Notes:</span>
                                    <span class="value">${sale.notes}</span>
                                </div>
                                ` : ''}
                            </div>
                            
                            <div class="amount-section">
                                <p style="margin: 0; font-size: 18px; color: #374151;">MONTANT TOTAL</p>
                                <p class="total-amount">${this.formatCurrency(sale.total_amount, sale.currency)}</p>
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
                    this.generateInvoiceNumber();
                    this.saleForm = {
                        client_name: '',
                        client_phone: '',
                        date: new Date().toISOString().split('T')[0],
                        invoice_number: this.saleForm.invoice_number,
                        currency: 'XOF',
                        status: 'paid',
                        notes: '',
                        lines: []
                    };
                    this.showSaleModal = true;
                },

                closeSaleModal() {
                    this.showSaleModal = false;
                },

                viewSaleDetails(sale) {
                    this.selectedSale = sale;
                    this.showDetailsModal = true;
                },

                closeDetailsModal() {
                    this.showDetailsModal = false;
                    this.selectedSale = null;
                },

                addProductLine() {
                    this.saleForm.lines.push({
                        product: '',
                        quantity: 1,
                        price: 0,
                        total: 0
                    });
                },

                removeProductLine(index) {
                    if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette ligne?')) {
                        this.saleForm.lines.splice(index, 1);
                    }
                },

                updateLineTotal(index) {
                    const line = this.saleForm.lines[index];
                    line.total = (parseFloat(line.quantity) || 0) * (parseFloat(line.price) || 0);
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
                        ...this.saleForm,
                        total_amount: this.saleTotal
                    };

                    api.post('?action=newSale', saleData)
                        .then(response => {
                            if (response.data.error) {
                                alert('Erreur: ' + response.data.error);
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
                }
            }
        }).mount('#app');
    </script>
</body>

</html>