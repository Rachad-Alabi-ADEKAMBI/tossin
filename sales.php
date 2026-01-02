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

        /* Custom dropdown styles */
        .client-dropdown {
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .client-option:hover {
            background-color: #F3F4F6;
        }

        /* Added product dropdown styles for searchable input */
        .product-dropdown {
            max-height: 250px;
            overflow-y: auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .product-option:hover {
            background-color: #F3F4F6;
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
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-file-invoice mr-2"></i>Gestion des Ventes
                            </h1>
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
                    <!-- Filters Section -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-filter mr-2"></i>Filtres
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Client, numéro facture..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- Added date exact filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date exacte</label>
                                <input v-model="exactDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <!-- Added date range filters -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                                <input v-model="startDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                                <input v-model="endDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                        </div>

                        <!-- Added clear filters button -->
                        <div class="mt-4 flex gap-2">
                            <button @click="clearFilters" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-times mr-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>

                    <!-- Updated statistics: removed average sale, added product count stats -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total des ventes</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalSales, 'FCFA') }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Nombre de ventes</p>
                                <p class="text-2xl font-bold text-blue-600">{{ filteredSales.length }}</p>
                            </div>
                            <!-- Added total products count -->
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total produits vendus</p>
                                <p class="text-2xl font-bold text-purple-600">{{ Math.round(totalProductsCount) }}</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Articles</p>
                                <p class="text-2xl font-bold text-orange-600">{{ totalUniqueProducts }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Table without phone column -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Facture</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <!-- Added Status column -->
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité totale</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Articles</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in paginatedSales" :key="sale.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'N° Facture'">
                                            #{{ sale.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                            <div class="text-sm font-medium text-gray-900">{{ sale.buyer }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date'">
                                            {{ formatDate(sale.date_of_insertion) }}
                                        </td>
                                        <!-- Added Status display with badges -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" :data-label="'Statut'">
                                            <span v-if="sale.status === 'Fait'" class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold text-xs">
                                                <i class="fas fa-check-circle mr-1"></i>Fait
                                            </span>
                                            <span v-else-if="sale.status === 'Annulé'" class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-semibold text-xs">
                                                <i class="fas fa-times-circle mr-1"></i>Annulé
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Quantité totale'">
                                            {{ calculateTotalProducts(sale) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Articles'">
                                            {{ calculateUniqueProducts(sale) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" :data-label="'Montant'">
                                            {{ formatCurrency(sale.total) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <!-- Disabled edit and cancel buttons if sale is already cancelled -->
                                            <div class="flex space-x-3">
                                                <button @click="editSale(sale)" :disabled="sale.status === 'Annulé'" :class="sale.status === 'Annulé' ? 'text-gray-400 cursor-not-allowed' : 'text-orange-600 hover:text-orange-800'" class="text-xl" title="Éditer">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="printInvoice(sale)" class="text-green-600 hover:text-green-800 text-xl" title="Imprimer facture">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <button @click="cancelSale(sale.id)" :disabled="sale.status === 'Annulé'" :class="sale.status === 'Annulé' ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-800'" class="text-xl" title="Annuler">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
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

            <!-- New optimized sale modal with client dropdown and searchable list -->
            <div v-if="showSaleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-invoice mr-2"></i>{{ isEditMode ? 'Modifier Vente' : 'Nouvelle Vente / Facture' }}
                            </h3>
                            <button @click="closeSaleModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="showConfirmationModal" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Client selection with searchable dropdown -->
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1"></i>Client
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="clientSearchTerm"
                                            @focus="showClientDropdown = true"
                                            @input="filterClients"
                                            type="text"
                                            placeholder="Rechercher ou créer un client..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>

                                        <!-- Dropdown list -->
                                        <div v-if="showClientDropdown && (filteredClients.length > 0 || clientSearchTerm)"
                                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg client-dropdown">
                                            <!-- Existing clients -->
                                            <div v-for="client in filteredClients"
                                                :key="client.id"
                                                @click="selectClient(client)"
                                                class="px-4 py-3 cursor-pointer client-option border-b border-gray-100">
                                                <div class="font-medium text-gray-900">{{ client.name }}</div>
                                                <div class="text-sm text-gray-500">{{ client.phone }}</div>
                                            </div>

                                            <!-- Create new client option -->
                                            <div v-if="clientSearchTerm && !filteredClients.some(c => c.name.toLowerCase() === clientSearchTerm.toLowerCase())"
                                                @click="showNewClientForm = true; showClientDropdown = false"
                                                class="px-4 py-3 cursor-pointer client-option bg-green-50 text-green-700 font-medium hover:bg-green-100">
                                                <i class="fas fa-plus-circle mr-2"></i>Créer "{{ clientSearchTerm }}"
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected client display -->
                                    <div v-if="saleForm.selectedClient" class="mt-2 p-3 bg-blue-50 rounded-lg flex justify-between items-center">
                                        <div>
                                            <div class="font-medium text-blue-900">{{ saleForm.selectedClient.name }}</div>
                                            <div class="text-sm text-blue-600">{{ saleForm.selectedClient.phone }}</div>
                                        </div>
                                        <button type="button" @click="clearClientSelection" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Added payment method selector -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-credit-card mr-1"></i>Mode de paiement
                                    </label>
                                    <select v-model="saleForm.payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="cash">Cash</option>
                                        <option value="crédit">Crédit</option>
                                    </select>
                                </div>

                                <!-- Added sale date selector with default to today -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date de la vente
                                    </label>
                                    <input v-model="saleForm.date_of_operation" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <!-- Products section with improved layout -->
                            <div class="border-t pt-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">
                                    <i class="fas fa-list mr-2"></i>Produits
                                </h4>

                                <div class="space-y-3">
                                    <!-- Added searchable product dropdown instead of plain select -->
                                    <div v-for="(line, index) in saleForm.lines" :key="index"
                                        class="grid grid-cols-1 md:grid-cols-7 gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div class="md:col-span-2 relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-box mr-1"></i>Produit
                                            </label>
                                            <input
                                                v-model="line.productSearchTerm"
                                                @focus="line.showProductDropdown = true; filterProducts(index);"
                                                @input="filterProducts(index)"
                                                type="text"
                                                placeholder="Rechercher un produit..."
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                            <i class="fas fa-search absolute right-3 top-8 text-gray-400 text-xs"></i>

                                            <!-- Product dropdown list -->
                                            <div v-if="line.showProductDropdown && line.filteredProducts && line.filteredProducts.length > 0"
                                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg product-dropdown">
                                                <div v-for="product in line.filteredProducts"
                                                    :key="product.id"
                                                    @click="selectProduct(index, product)"
                                                    class="px-3 py-2 cursor-pointer product-option border-b border-gray-100">
                                                    <div class="font-medium text-gray-900 text-sm">{{ product.name }}</div>
                                                    <div class="text-xs text-gray-500">Stock: {{ Math.round(product.quantity) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Added price type selector -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-tag mr-1"></i>Type Prix
                                            </label>
                                            <select v-model="line.priceType" @change="changePriceType(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                                <option value="retail_price">Détail</option>
                                                <option value="semi_bulk_price">Semi-gros</option>
                                                <option value="bulk_price">Gros</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                                            </label>
                                            <input v-model.number="line.quantity" type="number" min="1" required
                                                @input="validateQuantity(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>

                                        <!-- Made price input editable -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire
                                            </label>
                                            <input v-model.number="line.price" type="number" step="1" min="0" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-white">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-calculator mr-1"></i>Total
                                            </label>
                                            <input :value="Math.round(line.total) + ' FCFA'" type="text" readonly
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

                                <!-- Add product button moved below the last line -->
                                <div class="mt-3">
                                    <button type="button" @click="addProductLine"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center">
                                        <i class="fas fa-plus-circle mr-2 text-lg"></i>Ajouter un produit
                                    </button>
                                </div>

                                <!-- Summary -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            <i class="fas fa-boxes mr-2"></i>Quantité totale:
                                        </span>
                                        <span class="text-lg font-semibold text-blue-600">{{ saleTotalQuantity }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Montant total:</span>
                                        <span class="text-2xl font-bold text-green-600">{{ formatCurrency(saleTotal) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                                <div>
                                    <p class="text-sm text-gray-600">Total produits: <span class="font-semibold">{{ Math.round(totalProductsInForm) }}</span></p>
                                    <p class="text-sm text-gray-600">Articles: <span class="font-semibold">{{ uniqueProductsInForm }}</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Total général</p>
                                    <p class="text-2xl font-bold text-green-600">{{ Math.round(totalAmount) }} FCFA</p>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" @click="closeSaleModal"
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="button" @click="showConfirmationModal"
                                    class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg">
                                    <i class="fas fa-check mr-2"></i>Finaliser la vente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- New client creation modal -->
            <div v-if="showNewClientForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-[60]">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-user-plus mr-2"></i>Nouveau Client
                            </h3>
                            <button @click="showNewClientForm = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="createNewClient" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>Nom du client *
                                </label>
                                <input v-model="newClientForm.name" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-1"></i>Téléphone *
                                </label>
                                <input v-model="newClientForm.phone" type="tel" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit"
                                    class="flex-1 bg-accent hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-check mr-2"></i>Créer
                                </button>
                                <button type="button" @click="showNewClientForm = false"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Détails de Vente -->
            <div v-if="showDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Détails de la vente #{{ selectedSale?.id }}
                            </h3>
                            <button @click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div v-if="selectedSale" class="space-y-6">
                            <!-- Client Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-600">Client</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ selectedSale.buyer }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(selectedSale.date_of_insertion) }}</p>
                                </div>
                            </div>

                            <!-- Products Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="line in selectedSale.lines" :key="line.id">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ line.product }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ formatNumber(line.quantity) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ formatCurrency(line.price, selectedSale.currency) }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatCurrency(line.quantity * line.price, selectedSale.currency) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="p-4 bg-gray-50 rounded-lg">
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

                            <div class="flex justify-end space-x-3">
                                <button @click="printInvoice(selectedSale)" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closeDetailsModal" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Fermer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Added modal of summary/confirmation -->
            <div v-if="showRecapModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-receipt mr-2"></i>Récapitulatif de la vente
                            </h3>
                            <button @click="showRecapModal = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Client</p>
                                <p class="text-lg font-semibold">{{ saleForm.selectedClient ? saleForm.selectedClient.name : 'N/A' }}</p>
                            </div>

                            <!-- Added payment method display in recap -->
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Mode de paiement</p>
                                <p class="text-lg font-semibold">{{ saleForm.payment_method === 'cash' ? 'Cash' : 'Crédit' }}</p>
                            </div>

                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qté</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">P.U.</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(line, index) in saleForm.lines" :key="index">
                                            <td class="px-4 py-3 text-sm" data-label="Produit">{{ line.product_name }}</td>
                                            <td class="px-4 py-3 text-sm" data-label="Type de prix">
                                                <span v-if="line.priceType === 'retail_price'" class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Détail</span>
                                                <span v-if="line.priceType === 'semi_bulk_price'" class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Semi-gros</span>
                                                <span v-if="line.priceType === 'bulk_price'" class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Gros</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm" data-label="Quantité">{{ Math.round(line.quantity) }}</td>
                                            <td class="px-4 py-3 text-sm" data-label="Prix">{{ Math.round(line.price) }} FCFA</td>
                                            <td class="px-4 py-3 text-sm font-semibold" data-label="Total">{{ Math.round(line.total) }} FCFA</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total produits:</span>
                                    <span class="text-sm font-semibold">{{ Math.round(totalProductsInForm) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Articles:</span>
                                    <span class="text-sm font-semibold">{{ uniqueProductsInForm }}</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-300 pt-2 mt-2">
                                    <span class="text-lg font-bold">Total général:</span>
                                    <span class="text-lg font-bold text-green-600">{{ Math.round(totalAmount) }} FCFA</span>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" @click="showRecapModal = false" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Retour
                                </button>
                                <button type="button" @click="saveSale" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg">
                                    <i class="fas fa-check mr-2"></i>Confirmer la vente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Added new modal for sales history list print preview -->
            <div v-if="showPrintListModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-7xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6 no-print">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-list mr-2"></i>Historique des ventes
                            </h3>
                            <button @click="closePrintListModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="print-area">
                            <div class="text-center mb-6 hidden print:block">
                                <h1 class="text-2xl font-bold">GBEMIRO</h1>
                                <p class="text-sm">Historique des ventes</p>
                                <p class="text-xs text-gray-600">Généré le {{ new Date().toLocaleDateString('fr-FR') }}</p>
                            </div>

                            <div class="mb-4 bg-blue-50 p-4 rounded-lg grid grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Total des ventes</p>
                                    <p class="text-xl font-bold text-green-600">{{ formatCurrency(totalSales) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Nombre de ventes</p>
                                    <p class="text-xl font-bold text-blue-600">{{ filteredSales.length }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total produits vendus</p>
                                    <p class="text-xl font-bold text-purple-600">{{ Math.round(totalProductsCount) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Articles</p>
                                    <p class="text-xl font-bold text-orange-600">{{ totalUniqueProducts }}</p>
                                </div>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sale in filteredSales" :key="sale.id">
                                        <td class="px-4 py-3 text-sm">#{{ sale.id }}</td>
                                        <td class="px-4 py-3 text-sm">{{ sale.buyer }}</td>
                                        <td class="px-4 py-3 text-sm">{{ formatDate(sale.date_of_insertion) }}</td>
                                        <td class="px-4 py-3 text-sm">{{ calculateTotalProducts(sale) }}</td>
                                        <td class="px-4 py-3 text-sm">{{ calculateUniqueProducts(sale) }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold">{{ formatCurrency(sale.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6 no-print">
                            <button @click="printListNow" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-print mr-2"></i>Imprimer
                            </button>
                            <button @click="closePrintListModal" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-times mr-2"></i>Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const api = axios.create({
            baseURL: 'api/index.php'
        });

        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    sales: [],
                    salesProducts: [],
                    products: [],
                    clients: [],
                    currentSale: {
                        buyer: '',
                        phone: '',
                        items: []
                    },
                    filteredClients: [],
                    searchTerm: '',
                    sortBy: 'date',
                    exactDate: '',
                    startDate: '',
                    endDate: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showSaleModal: false,
                    showNewClientForm: false,
                    showClientDropdown: false,
                    clientSearchTerm: '',
                    saleForm: {
                        selectedClient: null,
                        currency: 'FCFA',
                        date_of_operation: new Date().toISOString().split('T')[0],
                        lines: [],
                        notes: '',
                        payment_method: 'cash', // Added default payment method
                    },
                    newClientForm: {
                        name: '',
                        phone: ''
                    },
                    showDetailsModal: false,
                    selectedSale: null,
                    showRecapModal: false,
                    productSearchTerm: '',
                    showProductDropdown: false,
                    filteredProducts: [],
                    isEditMode: false,
                    editingSaleId: null,
                    showPrintListModal: false,
                };
            },

            mounted() {
                this.fetchSales();
                this.fetchSalesProducts();
                this.fetchProducts();
                this.fetchClients();

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.relative')) {
                        this.showClientDropdown = false;
                    }
                });
            },

            computed: {
                filteredSales() {
                    let filtered = this.sales;

                    if (this.exactDate) {
                        filtered = filtered.filter(sale => {
                            const saleDate = sale.date_of_insertion.split(' ')[0];
                            return saleDate === this.exactDate;
                        });
                    }

                    if (this.startDate && this.endDate) {
                        filtered = filtered.filter(sale => {
                            const saleDate = sale.date_of_insertion.split(' ')[0];
                            return saleDate >= this.startDate && saleDate <= this.endDate;
                        });
                    } else if (this.startDate) {
                        filtered = filtered.filter(sale => {
                            const saleDate = sale.date_of_insertion.split(' ')[0];
                            return saleDate >= this.startDate;
                        });
                    } else if (this.endDate) {
                        filtered = filtered.filter(sale => {
                            const saleDate = sale.date_of_insertion.split(' ')[0];
                            return saleDate <= this.endDate;
                        });
                    }

                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(sale =>
                            sale.buyer.toLowerCase().includes(term) ||
                            sale.id.toString().includes(term)
                        );
                    }

                    // Sorting
                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'date':
                                return new Date(b.date_of_insertion) - new Date(a.date_of_insertion);
                            case 'buyer':
                                return a.buyer.localeCompare(b.buyer);
                            case 'total':
                                return b.total - a.total;
                            case 'invoice_number':
                                return b.id - a.id;
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },

                paginatedSales() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredSales.slice(start, end);
                },

                totalPages() {
                    return Math.ceil(this.filteredSales.length / this.itemsPerPage);
                },

                visiblePages() {
                    const pages = [];
                    const maxVisible = 5;
                    let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
                    let end = Math.min(this.totalPages, start + maxVisible - 1);

                    if (end - start < maxVisible - 1) {
                        start = Math.max(1, end - maxVisible + 1);
                    }

                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                startItem() {
                    return (this.currentPage - 1) * this.itemsPerPage + 1;
                },

                endItem() {
                    return Math.min(this.currentPage * this.itemsPerPage, this.filteredSales.length);
                },

                totalItems() {
                    return this.filteredSales.length;
                },

                totalSales() {
                    return this.filteredSales.reduce((sum, sale) => sum + parseFloat(sale.total), 0);
                },

                totalProductsCount() {
                    return this.filteredSales.reduce((sum, sale) => {
                        // Ensure sale.items is an array before calculating
                        const items = Array.isArray(sale.items) ? sale.items : [];
                        return sum + items.reduce((itemSum, item) => itemSum + parseFloat(item.quantity || 0), 0);
                    }, 0);
                },

                totalUniqueProducts() {
                    return this.filteredSales.reduce((sum, sale) => {
                        // Ensure sale.items is an array before calculating
                        const items = Array.isArray(sale.items) ? sale.items : [];
                        return sum + items.length;
                    }, 0);
                },

                currentSaleTotalProducts() {
                    return this.currentSale.items.reduce((sum, item) => {
                        return sum + parseFloat(item.quantity || 0);
                    }, 0);
                },

                currentSaleUniqueProducts() {
                    return this.currentSale.items.length;
                },

                filteredClients() {
                    if (!this.clientSearchTerm) return this.clients;
                    const search = this.clientSearchTerm.toLowerCase();
                    return this.clients.filter(client =>
                        client.name.toLowerCase().includes(search) ||
                        client.phone.includes(search)
                    );
                },


                saleTotal() {
                    return this.saleForm.lines.reduce((sum, line) => sum + (line.total || 0), 0);
                },

                saleTotalQuantity() {
                    return this.saleForm.lines.reduce((sum, line) => sum + (parseFloat(line.quantity) || 0), 0);
                },

                // Added computed for total products and unique products in the form
                totalProductsInForm() {
                    return this.saleForm.lines.reduce((sum, line) => sum + (line.quantity || 0), 0);
                },

                uniqueProductsInForm() {
                    return this.saleForm.lines.filter(line => line.product_id).length;
                },

                totalAmount() {
                    return this.saleForm.lines.reduce((sum, line) => sum + (line.total || 0), 0);
                },


                selectedSaleTotal() {
                    if (!this.selectedSale || !this.selectedSale.lines) return 0;
                    return this.selectedSale.lines.reduce((sum, line) => sum + (line.quantity * line.price), 0);
                },

                selectedSaleTotalQuantity() {
                    if (!this.selectedSale || !this.selectedSale.lines) return 0;
                    return this.selectedSale.lines.reduce((sum, line) => sum + parseFloat(line.quantity), 0);
                }
            },

            methods: {
                async fetchSalesProducts() {
                    try {
                        const response = await api.get('?action=allSalesProducts');
                        this.salesProducts = response.data;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des produits des ventes:', error);
                    }
                },

                async fetchSales() {
                    try {
                        const response = await api.get('?action=allSales');
                        this.sales = response.data.map(sale => {
                            // Ensure lines is always an array, parsing if it's a string
                            const lines = typeof sale.items === 'string' ? JSON.parse(sale.items) : (Array.isArray(sale.items) ? sale.items : []);
                            return {
                                ...sale,
                                lines: lines.map(item => ({
                                    // Adjust properties to match expected structure if necessary
                                    product: item.product_name || item.product, // Handle potential naming differences
                                    quantity: parseFloat(item.quantity),
                                    price: parseFloat(item.unit_price || item.price), // Handle potential naming differences
                                    // Add other relevant properties if needed
                                }))
                            };
                        });
                        // No need to call loadSalesProducts separately if items are already fetched/parsed in `fetchSales`
                    } catch (error) {
                        console.error('Erreur lors de la récupération des ventes:', error);
                    }
                },

                // This method might be redundant if `items` are correctly fetched and parsed in `fetchSales`
                // async loadSalesProducts() {
                //     try {
                //         const response = await api.get('?action=allSalesProducts');
                //         const products = response.data;

                //         this.sales.forEach(sale => {
                //             sale.lines = products.filter(product => product.sale_id == sale.id).map(product => ({
                //                 id: product.id,
                //                 product: product.name,
                //                 quantity: product.quantity,
                //                 price: parseFloat(product.price)
                //             }));
                //         });
                //     } catch (error) {
                //         console.error('Erreur lors du chargement des produits:', error);
                //     }
                // },

                async fetchProducts() {
                    try {
                        const response = await api.get('?action=allProducts');
                        this.products = response.data;
                        // Ensure filteredProducts is updated when products change
                        if (this.showSaleModal) { // Only update if modal is open to avoid unnecessary re-renders
                            this.saleForm.lines.forEach((line, index) => {
                                line.filteredProducts = this.products;
                                // Re-filter if the search term is still active
                                if (line.productSearchTerm) {
                                    this.filterProducts(index);
                                }
                            });
                        } else {
                            this.filteredProducts = this.products; // Initialize or update the general list
                        }
                    } catch (error) {
                        console.error('Erreur lors de la récupération des produits:', error);
                    }
                },

                async fetchClients() {
                    try {
                        const response = await api.get('?action=allClients');
                        this.clients = response.data;
                        this.filteredClients = this.clients;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des clients:', error);
                    }
                },

                async editSale(sale) {
                    if (sale.status === 'Annulé') {
                        alert('Impossible de modifier une vente annulée');
                        return;
                    }

                    try {
                        // Charger les produits de cette vente depuis l'API
                        const response = await api.get('?action=allSalesProducts');
                        const saleProducts = response.data.filter(product => product.sale_id === sale.id);

                        // Passer en mode édition
                        this.isEditMode = true;
                        this.editingSaleId = sale.id;

                        // Trouver le client
                        const client = this.clients.find(c => c.name === sale.buyer);
                        this.saleForm.selectedClient = client || {
                            name: sale.buyer,
                            phone: ''
                        };
                        this.clientSearchTerm = sale.buyer;

                        this.saleForm.payment_method = sale.payment_method || 'cash';
                        this.saleForm.date_of_operation = sale.date_of_insertion.split(' ')[0]; // Set the date

                        // Charger les lignes de produits
                        this.saleForm.lines = saleProducts.map(sp => {
                            const product = this.products.find(p => p.name === sp.name);

                            return {
                                product_id: product ? product.id : null,
                                product_name: sp.name,
                                productSearchTerm: sp.name,
                                showProductDropdown: false,
                                filteredProducts: this.products, // Ensure filteredProducts is available
                                priceType: 'retail_price', // Default to retail, will be set if available
                                retail_price: product ? parseFloat(product.retail_price) : 0,
                                semi_bulk_price: product ? parseFloat(product.semi_bulk_price) : 0,
                                bulk_price: product ? parseFloat(product.bulk_price) : 0,
                                quantity: parseFloat(sp.quantity),
                                price: parseFloat(sp.price),
                                total: parseFloat(sp.quantity) * parseFloat(sp.price),
                                // Available stock calculation needs to consider the quantity already in this sale
                                availableStock: product ? parseFloat(product.quantity) : parseFloat(sp.quantity), // Initial available stock from product list
                                originalQuantity: parseFloat(sp.quantity) // Garder la quantité originale pour le calcul du stock
                            };
                        });

                        this.showSaleModal = true;
                    } catch (error) {
                        console.error('Erreur lors du chargement de la vente:', error);
                        alert('Erreur lors du chargement de la vente');
                    }
                },

                openNewSaleModal() {
                    this.isEditMode = false;
                    this.editingSaleId = null;

                    this.currentSale = {
                        buyer: '',
                        phone: '',
                        items: []
                    };
                    this.clientSearchTerm = '';
                    this.productSearchTerm = '';
                    this.showProductDropdown = false;
                    this.showSaleModal = true;
                    this.saleForm = {
                        selectedClient: null,
                        currency: 'FCFA',
                        lines: [],
                        notes: '',
                        payment_method: 'cash', // Reset to default payment method
                        date_of_operation: new Date().toISOString().split('T')[0] // Reset to today's date
                    };
                },

                closeSaleModal() {
                    this.showSaleModal = false;
                    this.showNewClientForm = false;
                    this.isEditMode = false;
                    this.editingSaleId = null;

                    this.currentSale = {
                        buyer: '',
                        phone: '',
                        items: []
                    };
                    this.clientSearchTerm = '';
                    this.productSearchTerm = '';
                    this.showProductDropdown = false;
                    this.saleForm = {
                        selectedClient: null,
                        currency: 'FCFA',
                        lines: [],
                        notes: '',
                        payment_method: 'cash', // Reset payment method
                        date_of_operation: new Date().toISOString().split('T')[0] // Reset date
                    };
                    this.showRecapModal = false;
                },

                applyFilters() {
                    // The filters are reactive due to computed properties.
                    // Resetting to the first page ensures the user sees the first results after filtering.
                    this.currentPage = 1;
                },

                clearFilters() {
                    this.searchTerm = '';
                    this.exactDate = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.sortBy = 'date';
                    this.currentPage = 1;
                },

                filterClients() {
                    const term = this.clientSearchTerm.toLowerCase();
                    this.filteredClients = this.clients.filter(client =>
                        client.name.toLowerCase().includes(term) ||
                        client.phone.includes(term)
                    );
                },

                selectClient(client) {
                    // Update saleForm.selectedClient with selected client info
                    this.saleForm.selectedClient = client;
                    this.clientSearchTerm = client.name; // Display selected client name in search input
                    this.showClientDropdown = false;
                },

                clearClientSelection() {
                    this.saleForm.selectedClient = null;
                    this.clientSearchTerm = '';
                },

                async createNewClient() {
                    if (!this.newClientForm.name || !this.newClientForm.phone) {
                        alert('Veuillez remplir tous les champs');
                        return;
                    }

                    try {
                        const response = await api.post('?action=createClient', this.newClientForm);

                        if (response.data.success) {
                            alert('Client créé avec succès!');
                            await this.fetchClients();

                            // Automatically select the newly created client
                            const newClient = {
                                id: response.data.client_id,
                                name: this.newClientForm.name,
                                phone: this.newClientForm.phone
                            };
                            this.selectClient(newClient);

                            this.showNewClientForm = false;
                            this.newClientForm = {
                                name: '',
                                phone: ''
                            };
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue.'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de la création du client:', error);
                        alert('Erreur lors de la création du client');
                    }
                },

                // Method to filter products and exclude already selected ones
                filterProducts(index) {
                    const line = this.saleForm.lines[index];
                    const term = line.productSearchTerm.toLowerCase();

                    // Get IDs of products already selected in other lines
                    const selectedProductIds = this.saleForm.lines
                        .filter((l, i) => i !== index && l.product_id)
                        .map(l => l.product_id);

                    // Filter by search term AND exclude already selected products
                    line.filteredProducts = this.products.filter(product =>
                        product.name.toLowerCase().includes(term) &&
                        !selectedProductIds.includes(product.id)
                    );
                },

                // Method to validate quantity and check stock
                validateQuantity(index) {
                    const line = this.saleForm.lines[index];

                    if (line.quantity < 0) {
                        line.quantity = 0;
                    }

                    const product = this.products.find(p => p.id === line.product_id);

                    if (product) {
                        let availableStock = parseFloat(product.quantity);

                        // En mode édition, ajouter la quantité originale au stock disponible
                        if (this.isEditMode && line.originalQuantity) {
                            availableStock += line.originalQuantity;
                        }

                        if (line.quantity > availableStock) {
                            alert(`Stock insuffisant pour ${product.name}!\nStock disponible: ${Math.round(availableStock)}`);
                            line.quantity = Math.floor(availableStock);
                        }
                    }

                    this.updateLineTotal(index);
                },

                // Method to select a product for a line item in the sale form
                selectProduct(index, product) {
                    const line = this.saleForm.lines[index];
                    line.product_id = product.id;
                    line.product_name = product.name;
                    line.productSearchTerm = product.name;

                    // Calculer le stock disponible
                    let availableStock = parseFloat(product.quantity);

                    // En mode édition, si c'est un nouveau produit ajouté, le stock reste normal
                    // Si c'est un produit existant remplacé, garder la quantité actuelle
                    if (!this.isEditMode || !line.originalQuantity) {
                        line.originalQuantity = 0;
                    }

                    line.availableStock = availableStock;

                    line.retail_price = parseFloat(product.retail_price);
                    line.semi_bulk_price = parseFloat(product.semi_bulk_price);
                    line.bulk_price = parseFloat(product.bulk_price);

                    if (!line.priceType) {
                        line.priceType = 'retail_price';
                    }
                    line.price = parseFloat(product[line.priceType]);

                    line.showProductDropdown = false; // Hide dropdown after selection
                    this.updateLineTotal(index); // Recalculate total for this line
                },

                // Method to update the unit price based on the selected price type
                changePriceType(index) {
                    const line = this.saleForm.lines[index];
                    if (line.priceType && line[line.priceType]) {
                        line.price = line[line.priceType];
                        this.updateLineTotal(index);
                    }
                },

                // Method to add a product line to the saleForm.lines array
                addProductLine() {
                    this.saleForm.lines.push({
                        product_id: '',
                        product_name: '',
                        productSearchTerm: '',
                        showProductDropdown: false,
                        filteredProducts: this.products, // Ensure filteredProducts is available here
                        priceType: 'retail_price', // Default price type
                        retail_price: 0,
                        semi_bulk_price: 0,
                        bulk_price: 0,
                        quantity: 1,
                        price: 0, // Default price
                        total: 0,
                        availableStock: 0,
                        originalQuantity: 0
                    });
                },

                // Remove product line from saleForm.lines
                removeProductLine(index) {
                    this.saleForm.lines.splice(index, 1);
                },

                // Update product details based on selection in saleForm.lines
                updateProductDetails(index) {
                    const line = this.saleForm.lines[index];
                    const product = this.products.find(p => p.id == line.product_id);
                    if (product) {
                        line.product_name = product.name;
                        line.price = parseFloat(product.retail_price); // Default to retail price
                        this.updateLineTotal(index);
                    }
                },

                // Method to calculate the total for a single line item
                updateLineTotal(index) {
                    const line = this.saleForm.lines[index];
                    line.total = (line.quantity || 0) * (line.price || 0);
                },

                // Method to show the recap modal
                showConfirmationModal() {
                    // Check if all fields are filled
                    if (!this.saleForm.selectedClient) {
                        alert('Veuillez sélectionner un client');
                        return;
                    }

                    if (this.saleForm.lines.length === 0) {
                        alert('Veuillez ajouter au moins un produit');
                        return;
                    }

                    // Check that all lines have a product selected
                    for (let line of this.saleForm.lines) {
                        if (!line.product_id || !line.quantity || line.quantity <= 0) {
                            alert('Veuillez vérifier que tous les produits ont une quantité valide');
                            return;
                        }
                    }

                    this.showRecapModal = true;
                },

                async saveSale() {
                    const saleData = {
                        buyer: this.saleForm.selectedClient.name,
                        total: this.totalAmount,
                        currency: 'FCFA',
                        payment_method: this.saleForm.payment_method, // Added payment method to save
                        created_at: new Date().toISOString(),
                        date_of_operation: this.saleForm.date_of_operation,
                        lines: this.saleForm.lines.map(line => ({
                            product: line.product_name,
                            product_id: line.product_id, // Include product_id for backend
                            quantity: line.quantity,
                            price: line.price,
                            price_type: line.priceType // Store price type
                        }))
                    };

                    // En mode édition, ajouter l'ID de la vente
                    if (this.isEditMode) {
                        saleData.id = this.editingSaleId;
                    }

                    const action = this.isEditMode ? 'updateSale' : 'newSale';

                    console.log(`[v0] Requête de ${this.isEditMode ? 'mise à jour' : 'création'} de vente:`, {
                        route: `?action=${action}`,
                        data: saleData
                    });

                    try {
                        const response = await api.post(`?action=${action}`, saleData);

                        console.log('[v0] Réponse:', response.data);

                        if (!response.data.success) {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue.'));
                            return;
                        }

                        alert(this.isEditMode ? 'Vente modifiée avec succès!' : 'Nouvelle vente ajoutée avec succès!');
                        this.showRecapModal = false;
                        this.closeSaleModal();
                        await this.fetchSales();
                        await this.fetchSalesProducts();
                        await this.fetchProducts(); // Actualiser les produits pour mettre à jour les stocks
                    } catch (error) {
                        console.error('[v0] Erreur lors de l\'enregistrement:', error);
                        alert('Erreur lors de l\'enregistrement de la vente');
                    }
                },

                async cancelSale(saleId) {
                    const sale = this.sales.find(s => s.id === saleId);
                    if (sale && sale.status === 'Annulé') {
                        alert('Cette vente est déjà annulée');
                        return;
                    }

                    if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous vraiment sûr de vouloir annuler cette vente?')) {
                        return;
                    }

                    try {
                        const response = await api.post('?action=cancelSale', {
                            id: saleId
                        });

                        if (response.data.success) {
                            alert('Vente annulée avec succès!');
                            this.fetchSales(); // Refresh the list
                        } else {
                            alert("Erreur lors de l'annulation de la vente");
                        }
                    } catch (error) {
                        console.error("Erreur lors de l'annulation", error);
                        alert("Erreur lors de l'annulation de la vente");
                    }
                },

                async viewSaleDetails(sale) {
                    // Ensure sale.items is an array and properly formatted if needed
                    if (typeof sale.items === 'string') {
                        try {
                            sale.items = JSON.parse(sale.items);
                        } catch (e) {
                            console.error("Erreur lors du parsing des items pour la vente:", sale.id, e);
                            sale.items = []; // Set to empty array if parsing fails
                        }
                    } else if (!Array.isArray(sale.items)) {
                        sale.items = []; // Ensure it's an array
                    }

                    this.selectedSale = sale;
                    this.showDetailsModal = true;
                },

                closeDetailsModal() {
                    this.showDetailsModal = false;
                    this.selectedSale = null;
                },

                async printInvoice(sale) {
                    try {
                        const response = await api.get('?action=allSalesProducts');
                        const saleProducts = response.data.filter(product => product.sale_id === sale.id);

                        if (saleProducts.length === 0) {
                            alert('Aucun produit trouvé pour cette vente');
                            return;
                        }

                        // Trouver les informations du client
                        const client = this.clients.find(c => c.name === sale.buyer);
                        const clientPhone = client ? client.phone : 'Non disponible';

                        const cancelledWatermark = sale.status === 'Annulé' ? `
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 120px; color: rgba(255, 0, 0, 0.15); font-weight: bold; z-index: 1000; pointer-events: none;">
                                ANNULÉ
                            </div>
                        ` : '';

                        const printContent = `
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Facture #${sale.id}</title>
                                <style>
                                    body { font-family: Arial, sans-serif; margin: 20px; position: relative; }
                                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                                    th { background-color: #f0f0f0; font-weight: bold; }
                                    .total { font-weight: bold; font-size: 1.2em; margin-top: 20px; text-align: right; }
                                    .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                                    .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; margin: 10px 0; }
                                    .status-cancelled { background-color: #fee; color: #c00; border: 2px solid #c00; }
                                    .status-done { background-color: #efe; color: #0a0; border: 2px solid #0a0; }
                                </style>
                            </head>
                            <body>
                                ${cancelledWatermark}
                                <div class="header">
                                    <h1>GBEMIRO</h1>
                                    <p>Commerçialisation de boissons en gros et en détail<br>
                                    Lokossa, Quinji carrefour Abo, <br>
                                    téléphone 01 49 91 65 66</p>
                                    <h2>FACTURE #${sale.id}</h2>
                                    ${sale.status === 'Annulé' ? '<div class="status-badge status-cancelled">❌ VENTE ANNULÉE</div>' : '<div class="status-badge status-done">✓ VENTE EFFECTUÉE</div>'}
                                </div>
                                <p><strong>Client:</strong> ${sale.buyer}</p>
                                <p><strong>Téléphone:</strong> ${clientPhone}</p>
                                <p><strong>Date:</strong> ${this.formatDate(sale.date_of_insertion)}</p>
                                <p><strong>Mode de paiement:</strong> ${sale.payment_method === 'cash' ? 'Cash' : 'Crédit'}</p>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix unitaire</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${saleProducts.map(product => `
                                            <tr>
                                                <td>${product.name}</td>
                                                <td>${this.formatNumber(product.quantity)}</td>
                                                <td>${this.formatCurrency(product.price)}</td>
                                                <td>${this.formatCurrency(parseFloat(product.quantity) * parseFloat(product.price))}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                                <p class="total">Total: ${this.formatCurrency(sale.total)}</p>
                                
                                <div class="footer">
                                    <p>Merci pour votre confiance!</p>
                                    <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                                </div>
                            </body>
                            </html>
                        `;

                        const printWindow = window.open('', '', 'height=600,width=800');
                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        printWindow.print();
                    } catch (error) {
                        console.error('Erreur lors de l\'impression:', error);
                        alert('Erreur lors de l\'impression de la facture');
                    }
                },

                printSalesList() {
                    // Créer un modal pour afficher l'historique
                    const modalContent = `
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50" id="print-modal">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <div class="bg-white rounded-xl shadow-xl max-w-6xl w-full p-6 max-h-screen overflow-y-auto">
                                    <div class="flex justify-between items-center mb-6 no-print">
                                        <h3 class="text-xl font-semibold text-gray-900">
                                            <i class="fas fa-list mr-2"></i>Historique des Ventes
                                        </h3>
                                        <div class="flex space-x-3">
                                            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                                <i class="fas fa-print mr-2"></i>Imprimer
                                            </button>
                                            <button onclick="document.getElementById('print-modal').remove()" class="text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-times text-xl"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="print-area">
                                        <div class="text-center mb-6">
                                            <h1 class="text-2xl font-bold">GBEMIRO</h1>
                                            <p class="text-sm text-gray-600">Historique des Ventes</p>
                                            <p class="text-sm text-gray-600">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                                        </div>

                                        <table class="min-w-full divide-y divide-gray-200 border">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">N° Facture</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Client</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Date</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Qté Totale</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Articles</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase border">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                ${this.filteredSales.map(sale => `
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm border">#${sale.id}</td>
                                                        <td class="px-4 py-2 text-sm border">${sale.buyer}</td>
                                                        <td class="px-4 py-2 text-sm border">${this.formatDate(sale.date_of_insertion)}</td>
                                                        <td class="px-4 py-2 text-sm border">${this.calculateTotalProducts(sale)}</td>
                                                        <td class="px-4 py-2 text-sm border">${this.calculateUniqueProducts(sale)}</td>
                                                        <td class="px-4 py-2 text-sm border font-medium text-green-600">${this.formatCurrency(sale.total)}</td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr class="font-bold">
                                                    <td colspan="5" class="px-4 py-2 text-right border">Total:</td>
                                                    <td class="px-4 py-2 text-green-600 border">${this.formatCurrency(this.totalSales)}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    // Injecter le modal dans le DOM
                    const modalDiv = document.createElement('div');
                    modalDiv.innerHTML = modalContent;
                    document.body.appendChild(modalDiv);
                },

                closePrintListModal() {
                    this.showPrintListModal = false;
                },

                printListNow() {
                    window.print();
                },

                previousPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },

                goToPage(page) {
                    this.currentPage = page;
                },

                formatDate(date) {
                    if (!date) return '';
                    // Ensure date is a valid Date object
                    const d = new Date(date);
                    if (isNaN(d)) return ''; // Return empty string for invalid dates
                    return d.toLocaleDateString('fr-FR');
                },

                formatNumber(num) {
                    // Ensure num is a number, default to 0 if not
                    const number = parseFloat(num);
                    return (isNaN(number) ? 0 : number).toLocaleString('fr-FR');
                },

                formatCurrency(amount, currency = 'FCFA') {
                    // Display amounts without decimals (integers) in FCFA
                    const number = parseFloat(amount);
                    const roundedAmount = Math.round(isNaN(number) ? 0 : number);
                    return `${roundedAmount.toLocaleString('fr-FR')} ${currency}`;
                },

                calculateTotalProducts(sale) {
                    // Filtrer les produits qui appartiennent à cette vente
                    const saleProducts = this.salesProducts.filter(product => product.sale_id === sale.id);
                    // Calculer la somme totale des quantités
                    return saleProducts.reduce((sum, product) => sum + parseInt(product.quantity || 0), 0);
                },

                calculateUniqueProducts(sale) {
                    // Filtrer les produits qui appartiennent à cette vente
                    const saleProducts = this.salesProducts.filter(product => product.sale_id === sale.id);
                    // Retourner le nombre de produits
                    return saleProducts.length;
                },
            },
        }).mount('#app');
    </script>
</body>

</html>