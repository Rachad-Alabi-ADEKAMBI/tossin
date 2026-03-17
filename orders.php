<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOBI LODA - Gestion des Commandes</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="icon" type="image/x-icon" href="public/images/logo.png">
    <style>
        .primary { color: #2563EB; }
        .secondary { color: #1E40AF; }
        .accent { color: #F59E0B; }
        .bg-primary { background-color: #2563EB; }
        .bg-secondary { background-color: #1E40AF; }
        .bg-accent { background-color: #F59E0B; }
        .hover\:bg-accent:hover { background-color: #D97706; }
        .hover\:bg-secondary:hover { background-color: #1E40AF; }
        .text-primary { color: #2563EB; }
        .text-secondary { color: #1E40AF; }
        .text-accent { color: #F59E0B; }
        .border-primary { border-color: #2563EB; }
        .focus\:ring-primary:focus { --tw-ring-color: #2563EB; }

        /* Product dropdown */
        .product-dropdown {
            max-height: 250px;
            overflow-y: auto;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .product-option:hover { background-color: #F3F4F6; }

        /* Mobile filter toggle */
        .filter-collapsed { display: none; }
        .filter-expanded { display: block; }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr {
                border: 1px solid #e5e7eb;
                margin-bottom: 1rem;
                padding: 1rem;
                border-radius: 0.75rem;
                background: white;
                box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            }
            td {
                border: none;
                position: relative;
                padding-left: 50% !important;
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
                text-align: right;
                border-bottom: 1px solid #f3f4f6;
            }
            td:last-child { border-bottom: none; }
            td:before {
                content: attr(data-label) ": ";
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: 600;
                color: #4b5563;
                text-align: left;
            }
        }

        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; }
            .modal-content { box-shadow: none !important; border: 1px solid #000 !important; }
            .bg-gray-50, .bg-blue-50, .bg-green-50, .bg-yellow-50, .bg-purple-50 {
                background: white !important;
                border: 1px solid #ccc !important;
            }
            .print-products { display: block !important; }
            .print-product-item {
                display: block !important;
                border: 1px solid #000 !important;
                margin-bottom: 10px !important;
                padding: 10px !important;
                page-break-inside: avoid !important;
            }
            .print-product-row { display: flex !important; justify-content: space-between !important; margin-bottom: 5px !important; }
            .print-product-label { font-weight: bold !important; width: 40% !important; }
            .print-product-value { width: 60% !important; text-align: right !important; }
            .print-header { margin-bottom: 20px !important; }
            .print-summary { margin-top: 20px !important; border-top: 2px solid #000 !important; padding-top: 10px !important; }
            .print-table { display: none !important; }
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="bg-gray-50 min-h-screen">
            <?php include 'sidebar.php'; ?>

            <div class="lg:ml-64 min-h-screen">
                <!-- Header -->
                <header class="bg-white shadow-sm border-b">
                    <div class="px-6 py-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-shopping-cart mr-2"></i>Gestion des Commandes
                            </h1>
                            <div class="flex flex-wrap gap-2">
                                <button @click="window.location.reload()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm">
                                    <i class="fas fa-rotate-right mr-2"></i>Recharger
                                </button>
                                <button @click="openNewOrderModal" class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center shadow-md font-bold">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle commande
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <!-- Filtres -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-filter mr-2"></i>Filtres
                            </h2>
                            <button @click="toggleFilters" class="md:hidden text-primary font-medium flex items-center">
                                <span>{{ showAllFilters ? 'Masquer' : 'Plus d\'options' }}</span>
                                <i :class="['fas ml-2', showAllFilters ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text"
                                    placeholder="N° commande ou fournisseur..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div :class="['md:block', showAllFilters ? 'block' : 'hidden']">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                <select v-model="statusFilter" @change="applyFilters"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous</option>
                                    <option value="En_attente">En attente</option>
                                    <option value="En_cours">En cours</option>
                                    <option value="Livrée">Livrée</option>
                                </select>
                            </div>
                            <div :class="['md:flex items-end', showAllFilters ? 'flex' : 'hidden']">
                                <button @click="clearFilters" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div v-if="loading" class="flex justify-center items-center h-64">
                            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fournisseur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="paginatedOrders.length === 0">
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                            Aucune commande trouvée
                                        </td>
                                    </tr>
                                    <tr v-for="order in paginatedOrders" :key="order.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="N° Commande">
                                            {{ order.number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Date">
                                            {{ formatDate(order.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Fournisseur">
                                            {{ order.supplier }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Quantité">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-boxes mr-1"></i>{{ order.totalQuantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="Total">
                                            {{ formatCurrency(order.total, order.currency) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-label="Statut">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusInfo(order.status).class]">
                                                {{ getStatusInfo(order.status).label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
                                            <div class="flex space-x-3">
                                                <button @click="showPaymentHistory(order)" class="text-green-600 hover:text-green-800 text-xl" title="Historique des paiements">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <button @click="showOrderDetails(order)" class="text-primary hover:text-secondary text-xl" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button @click="editOrderStatus(order)" class="text-accent hover:text-yellow-600 text-xl" title="Modifier statut">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="deleteOrder(order.id)" class="text-red-600 hover:text-red-800 text-xl" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
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
                                <button @click="previousPage" :disabled="currentPage === 1"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                    Précédent
                                </button>
                                <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                    Suivant
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Affichage de <span class="font-medium">{{ startItem }}</span> à
                                        <span class="font-medium">{{ endItem }}</span> sur
                                        <span class="font-medium">{{ totalItems }}</span> résultats
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <button @click="previousPage" :disabled="currentPage === 1"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                                            :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                page === currentPage ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50']">
                                            {{ page }}
                                        </button>
                                        <button @click="nextPage" :disabled="currentPage === totalPages"
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL NOUVELLE COMMANDE ===================== -->
            <div v-if="showNewOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-shopping-cart mr-2"></i>Nouvelle Commande
                            </h3>
                            <button @click="closeNewOrderModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="addNewOrder" class="space-y-6">
                            <!-- Infos générales -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-truck mr-1"></i>Fournisseur *
                                    </label>
                                    <input v-model="newOrder.supplier" type="text" required
                                        placeholder="Nom du fournisseur"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date *
                                    </label>
                                    <input v-model="newOrder.date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill mr-1"></i>Devise
                                    </label>
                                    <select v-model="newOrder.currency"
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
                                    <select v-model="newOrder.status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="En_attente">En attente</option>
                                        <option value="En_cours">En cours</option>
                                        <option value="Livrée">Livrée</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Lignes de produits -->
                            <div class="border-t pt-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">
                                    <i class="fas fa-list mr-2"></i>Produits de la commande
                                </h4>

                                <div class="space-y-3">
                                    <div v-for="(line, index) in newOrder.lines" :key="index"
                                        class="grid grid-cols-1 md:grid-cols-7 gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">

                                        <!-- Produit (searchable + saisie libre) -->
                                        <div class="md:col-span-2 relative">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-box mr-1"></i>Produit
                                            </label>
                                            <input
                                                v-model="line.productSearchTerm"
                                                @focus="line.showProductDropdown = true; filterOrderProducts(index);"
                                                @input="filterOrderProducts(index)"
                                                type="text"
                                                placeholder="Rechercher ou saisir un produit..."
                                                required
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                            <!-- Dropdown produits -->
                                            <div v-if="line.showProductDropdown && line.filteredProducts && line.filteredProducts.length > 0"
                                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg product-dropdown">
                                                <div v-for="product in line.filteredProducts"
                                                    :key="product.id"
                                                    @click="selectOrderProduct(index, product)"
                                                    class="px-3 py-2 cursor-pointer product-option border-b border-gray-100">
                                                    <div class="font-medium text-gray-900 text-sm">{{ product.name }}</div>
                                                    <div class="text-xs text-gray-500">Stock: {{ Math.round(product.quantity) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quantité -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                                            </label>
                                            <input v-model.number="line.quantity" type="number" min="0.01" step="0.01" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>

                                        <!-- Prix unitaire (saisie libre) -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire
                                            </label>
                                            <input v-model.number="line.price" type="number" step="1" min="0" required
                                                @input="updateLineTotal(index)"
                                                placeholder="Saisir le prix"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-white">
                                        </div>

                                        <!-- Total ligne -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-calculator mr-1"></i>Total
                                            </label>
                                            <input :value="formatCurrency(line.total, newOrder.currency)" type="text" readonly
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-gray-100">
                                        </div>

                                        <!-- Bouton supprimer -->
                                        <div class="flex items-end">
                                            <button type="button" @click="removeOrderLine(index)"
                                                class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-2 rounded text-sm transition-colors">
                                                <i class="fas fa-trash mr-1"></i>Retirer
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton ajouter produit – en bas, pleine largeur (visible et accessible mobile) -->
                                <div class="mt-4">
                                    <button type="button" @click="addOrderLine"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition-colors flex items-center justify-center font-medium">
                                        <i class="fas fa-plus-circle mr-2 text-lg"></i>Ajouter un produit
                                    </button>
                                </div>

                                <!-- Récapitulatif -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            <i class="fas fa-boxes mr-2"></i>Quantité totale:
                                        </span>
                                        <span class="text-lg font-semibold text-blue-600">{{ orderTotalQuantity }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total commande:</span>
                                        <span class="text-2xl font-bold text-primary">{{ formatCurrency(orderTotal, newOrder.currency) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" @click="closeNewOrderModal"
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="button" @click="showOrderConfirmModal = true"
                                    class="bg-accent hover:bg-yellow-600 text-white px-8 py-3 rounded-lg shadow-lg font-bold transform transition-all hover:scale-105">
                                    <i class="fas fa-check-circle mr-2 text-lg"></i>CRÉER LA COMMANDE
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL CONFIRMATION COMMANDE ===================== -->
            <div v-if="showOrderConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-[100] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 border-t-8 border-yellow-500 max-h-[90vh] overflow-y-auto">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shopping-cart text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Confirmer la commande</h3>
                        <p class="text-gray-600 mt-2">Vérifiez les détails avant de valider</p>
                    </div>

                    <!-- Récapitulatif fournisseur -->
                    <div class="bg-blue-50 p-4 rounded-xl mb-4">
                        <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                            <i class="fas fa-truck mr-2 text-blue-600"></i>Fournisseur
                        </h4>
                        <p class="font-medium text-gray-900">{{ newOrder.supplier }}</p>
                        <p class="text-sm text-gray-600">Date: {{ formatDate(newOrder.date) }} | Devise: {{ newOrder.currency }}</p>
                    </div>

                    <!-- Articles -->
                    <div class="bg-gray-50 p-4 rounded-xl mb-6">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-list mr-2 text-blue-600"></i>Produits commandés
                        </h4>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="(line, index) in newOrder.lines" :key="index"
                                class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ line.productSearchTerm }}</p>
                                    <p class="text-sm text-gray-600">{{ line.quantity }} x {{ formatCurrency(line.price, newOrder.currency) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-primary">{{ formatCurrency(line.total, newOrder.currency) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t-2 border-gray-300 mt-4 pt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700">Quantité totale</span>
                                <span class="font-semibold text-blue-600">{{ orderTotalQuantity }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                                <span class="text-lg font-bold text-gray-900">Montant total</span>
                                <span class="text-2xl font-bold text-accent">{{ formatCurrency(orderTotal, newOrder.currency) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3">
                        <button @click="addNewOrder"
                            class="w-full bg-accent hover:bg-yellow-600 text-white py-4 rounded-xl font-bold text-lg shadow-xl transform transition-all active:scale-95 flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i>OUI, CRÉER LA COMMANDE
                        </button>
                        <button @click="showOrderConfirmModal = false" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-semibold">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL MODIFIER STATUT ===================== -->
            <div v-if="showEditStatusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-60">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2"></i>Modifier le statut
                            </h3>
                            <button @click="closeEditStatusModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <form @submit.prevent="saveEditStatus" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>Nouveau statut
                                </label>
                                <select v-model="editingOrder.status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="En_attente">En attente</option>
                                    <option value="En_cours">En cours</option>
                                    <option value="Livrée">Livrée</option>
                                </select>
                            </div>
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-accent hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Modifier
                                </button>
                                <button type="button" @click="closeEditStatusModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL DÉTAILS COMMANDE ===================== -->
            <div v-if="showOrderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 no-print">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-5xl w-full p-6 max-h-screen overflow-y-auto modal-content">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-file-alt mr-2"></i>Détails de la commande
                            </h3>
                            <div class="flex gap-2">
                                <button @click="openPrintOptionsModal" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors no-print">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closeOrderDetailsModal" class="text-gray-400 hover:text-gray-600 no-print">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedOrder">
                            <!-- Infos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 print-header">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-700">N° Commande</p>
                                    <p class="text-lg font-semibold text-blue-600">{{ selectedOrder.number }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-700">Fournisseur</p>
                                    <p class="text-lg font-semibold text-green-600">{{ selectedOrder.supplier }}</p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-700">Date</p>
                                    <p class="text-lg font-semibold text-yellow-600">{{ formatDate(selectedOrder.date) }}</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm font-medium text-gray-700">Statut</p>
                                    <span :class="['inline-block px-3 py-1 text-sm font-semibold rounded-full', getStatusInfo(selectedOrder.status).class]">
                                        {{ getStatusInfo(selectedOrder.status).label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Lignes -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-list mr-2"></i>Lignes de commande
                                    </h4>
                                    <div class="flex space-x-2 no-print" v-if="selectedOrder.status !== 'Livrée'">
                                        <button @click="addNewProductLine" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-plus mr-1"></i>Ajouter ligne
                                        </button>
                                        <button @click="editOrderStatus(selectedOrder)" class="bg-accent hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Modifier statut
                                        </button>
                                    </div>
                                    <div v-else class="text-sm text-gray-500 italic no-print">
                                        <i class="fas fa-lock mr-1"></i>Commande livrée - Modification désactivée
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg print-table">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                                <th v-if="selectedOrder.status !== 'Livrée'" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="(line, index) in selectedOrder.lines" :key="index" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm" data-label="Produit">
                                                    <div v-if="line.editing && selectedOrder.status !== 'Livrée'">
                                                        <input v-model="line.product" type="text" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                    </div>
                                                    <div v-else>{{ line.product }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Quantité">
                                                    <div v-if="line.editing && selectedOrder.status !== 'Livrée'">
                                                        <input v-model.number="line.quantity" type="number" min="1" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                    </div>
                                                    <div v-else>{{ line.quantity }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Prix unitaire">
                                                    <div v-if="line.editing && selectedOrder.status !== 'Livrée'">
                                                        <input v-model.number="line.price" type="number" step="0.01" min="0" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                    </div>
                                                    <div v-else>{{ formatCurrency(line.price, selectedOrder.currency) }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm font-medium" data-label="Total">{{ formatCurrency(line.quantity * line.price, selectedOrder.currency) }}</td>
                                                <td v-if="selectedOrder.status !== 'Livrée'" class="px-4 py-3 text-sm no-print" data-label="Actions">
                                                    <div v-if="line.editing" class="flex space-x-2">
                                                        <button @click="validateProductEdit(index)" class="px-3 py-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-colors" title="Valider">
                                                            <i class="fas fa-check fa-lg"></i>
                                                        </button>
                                                        <button @click="cancelProductEdit(index)" class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors" title="Annuler">
                                                            <i class="fas fa-times fa-lg"></i>
                                                        </button>
                                                    </div>
                                                    <div v-else class="flex space-x-2">
                                                        <button @click="editProductLine(index)" class="px-3 py-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" title="Modifier">
                                                            <i class="fas fa-edit fa-lg"></i>
                                                        </button>
                                                        <button @click="deleteOrderItem(line.id)" class="px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors" title="Supprimer">
                                                            <i class="fas fa-trash fa-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Nouvelle ligne produit dans détail -->
                                            <tr v-if="newProductLine.visible && selectedOrder.status !== 'Livrée'" class="bg-green-50">
                                                <td class="px-4 py-3 text-sm relative" data-label="Produit">
                                                    <input v-model="newProductLine.productSearchTerm"
                                                        @focus="newProductLine.showDropdown = true; filterNewLineProducts();"
                                                        @input="filterNewLineProducts()"
                                                        type="text"
                                                        placeholder="Rechercher ou saisir un produit..."
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                    <div v-if="newProductLine.showDropdown && newProductLine.filteredProducts && newProductLine.filteredProducts.length > 0"
                                                        class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg product-dropdown" style="min-width:200px">
                                                        <div v-for="product in newProductLine.filteredProducts"
                                                            :key="product.id"
                                                            @click="selectNewLineProduct(product)"
                                                            class="px-3 py-2 cursor-pointer product-option border-b border-gray-100 text-sm">
                                                            <div class="font-medium text-gray-900">{{ product.name }}</div>
                                                            <div class="text-xs text-gray-500">Stock: {{ Math.round(product.quantity) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Quantité">
                                                    <input v-model.number="newProductLine.quantity" type="number" min="1" placeholder="Quantité"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Prix unitaire">
                                                    <input v-model.number="newProductLine.price" type="number" step="0.01" min="0" placeholder="Prix"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                                <td class="px-4 py-3 text-sm font-medium" data-label="Total">{{ formatCurrency(newProductLine.quantity * newProductLine.price || 0, selectedOrder.currency) }}</td>
                                                <td class="px-4 py-3 text-sm no-print" data-label="Actions">
                                                    <div class="flex space-x-2">
                                                        <button @click="validateNewLine" class="px-3 py-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-colors" title="Valider">
                                                            <i class="fas fa-check fa-lg"></i>
                                                        </button>
                                                        <button @click="cancelNewLine" class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors" title="Annuler">
                                                            <i class="fas fa-times fa-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Version print produits -->
                                    <div class="print-products hidden">
                                        <div v-for="(line, index) in selectedOrder.lines" :key="index" class="print-product-item">
                                            <div class="print-product-row"><span class="print-product-label">Produit:</span><span class="print-product-value">{{ line.product }}</span></div>
                                            <div class="print-product-row"><span class="print-product-label">Quantité:</span><span class="print-product-value">{{ line.quantity }}</span></div>
                                            <div class="print-product-row"><span class="print-product-label">Prix unitaire:</span><span class="print-product-value">{{ formatCurrency(line.price, selectedOrder.currency) }}</span></div>
                                            <div class="print-product-row"><span class="print-product-label">Total:</span><span class="print-product-value">{{ formatCurrency(line.quantity * line.price, selectedOrder.currency) }}</span></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Récap total -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg print-summary">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            <i class="fas fa-boxes mr-2"></i>Quantité totale:
                                        </span>
                                        <span class="text-lg font-semibold text-blue-600">{{ selectedOrderTotalQuantity }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total de la commande:</span>
                                        <span class="text-2xl font-bold text-primary">{{ formatCurrency(selectedOrderTotal, selectedOrder.currency) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL OPTIONS IMPRESSION ===================== -->
            <div v-if="showPrintOptionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 no-print" style="z-index: 9999;">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-print mr-2"></i>Options d'impression
                            </h3>
                            <button @click="closePrintOptionsModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Afficher les prix?</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="printOptions.withPrices" :value="true" class="mr-2">
                                        <span>Avec les prix</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" v-model="printOptions.withPrices" :value="false" class="mr-2">
                                        <span>Sans les prix</span>
                                    </label>
                                </div>
                            </div>
                            <div v-if="printOptions.withPrices">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill mr-1"></i>Devise d'affichage
                                </label>
                                <select v-model="printOptions.currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="XOF">XOF (Franc CFA)</option>
                                    <option value="N">N (Naira)</option>
                                    <option value="GHC">GHC (Ghana Cedis)</option>
                                    <option value="EUR">EUR (Euro)</option>
                                    <option value="USD">USD (Dollar)</option>
                                    <option value="GBP">GBP (Livre Sterling)</option>
                                </select>
                            </div>
                            <div v-if="printOptions.withPrices && printOptions.currency !== selectedOrder.currency">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-exchange-alt mr-1"></i>Taux de conversion
                                    <span class="text-xs text-gray-500">(1 {{ selectedOrder.currency }} = ? {{ printOptions.currency }})</span>
                                </label>
                                <input v-model.number="printOptions.conversionRate" type="number" step="0.0001" min="0" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button @click="executePrint" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closePrintOptionsModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL HISTORIQUE PAIEMENTS ===================== -->
            <div v-if="showPaymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50" style="z-index: 9997;">
                <div class="flex items-center justify-center min-h-screen p-4 overflow-auto">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Historique des Paiements
                            </h3>
                            <div class="flex space-x-2">
                                <button @click="printPaymentHistory" class="no-print bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-print mr-1"></i>Imprimer
                                </button>
                                <button @click="closePaymentHistoryModal" class="no-print text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedOrder">
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Commande {{ selectedOrder.number }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant total</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ formatCurrency(selectedOrder.total, selectedOrder.currency) }}</p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant payé</p>
                                        <p class="text-lg font-semibold text-green-600">{{ formatCurrency(totalPaid, selectedOrder.currency) }}</p>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Solde restant</p>
                                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(remainingBalance, selectedOrder.currency) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 no-print">
                                <button @click="openNewPaymentModal" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Nouveau paiement
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Justificatif</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase no-print">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-if="orderPayments.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                                <p>Aucun paiement enregistré</p>
                                            </td>
                                        </tr>
                                        <tr v-for="payment in orderPayments" :key="payment.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900" data-label="Date">{{ formatDate(payment.date_of_insertion) }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-green-600" data-label="Montant">{{ formatCurrency(payment.amount, selectedOrder.currency) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600" data-label="Notes">{{ payment.notes || '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600" data-label="Justificatif">
                                                <div v-if="payment.file && payment.file !== ''">
                                                    <a :href="getImgUrl(payment.file)" target="_blank">
                                                        <img :src="getImgUrl(payment.file)" alt="Justificatif" class="w-20 h-20 object-cover rounded">
                                                    </a>
                                                </div>
                                                <p v-else>Aucun justificatif</p>
                                            </td>
                                            <td class="px-4 py-3 text-sm no-print" data-label="Actions">
                                                <button @click="editPayment(payment)" class="text-blue-600 hover:text-blue-800 mr-3" title="Modifier">
                                                    <i class="fas fa-edit fa-lg"></i>
                                                </button>
                                                <button @click="deletePayment(payment.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                    <i class="fas fa-trash fa-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL NOUVEAU PAIEMENT ===================== -->
            <div v-if="showNewPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50" style="z-index: 9999;">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-money-bill-wave mr-2"></i>Nouveau Paiement
                            </h3>
                            <button @click="closeNewPaymentModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <form @submit.prevent="addNewPayment" class="space-y-4" enctype="multipart/form-data">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Montant
                                </label>
                                <input v-model.number="newPayment.amount" type="number" step="0.01" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Solde restant: {{ formatCurrency(remainingBalance, selectedOrder.currency) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>Date de paiement
                                </label>
                                <input v-model="newPayment.date" type="date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="newPayment.notes" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-image mr-1"></i>Photo (facultatif)
                                </label>
                                <input type="file" @change="handleFileUpload" accept="image/*" class="w-full">
                            </div>
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                                <button type="button" @click="closeNewPaymentModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ===================== MODAL MODIFIER PAIEMENT ===================== -->
            <div v-if="showEditPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50" style="z-index: 9998;">
                <div class="flex items-center justify-center min-h-screen p-4 overflow-auto">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2"></i>Modifier le Paiement
                            </h3>
                            <button @click="closeEditPaymentModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <form @submit.prevent="saveEditPayment" class="space-y-4" enctype="multipart/form-data">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Montant
                                </label>
                                <input v-model.number="editingPayment.amount" type="number" step="0.01" required min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Solde restant (sans ce paiement): {{ formatCurrency(remainingBalance + parseFloat(editingPayment.originalAmount), selectedOrder.currency) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>Date de paiement
                                </label>
                                <input v-model="editingPayment.date" type="date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="editingPayment.notes" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-image mr-1"></i>Photo (facultatif)
                                </label>
                                <div v-if="editingPayment.existingFile" class="mb-2">
                                    <p class="text-xs text-gray-600 mb-1">Fichier actuel:</p>
                                    <img :src="getImgUrl(editingPayment.existingFile)" alt="Justificatif actuel" class="w-20 h-20 object-cover rounded">
                                </div>
                                <input type="file" @change="handleEditFileUpload" accept="image/*" class="w-full">
                                <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver le fichier actuel</p>
                            </div>
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                                <button type="button" @click="closeEditPaymentModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;

        const api = axios.create({ baseURL: 'api/index.php' });
        const imgBaseUrl = 'api/uploads/order_payments/';

        createApp({
            data() {
                return {
                    loading: false,
                    orders: [],
                    filteredOrders: [],
                    allProducts: [], // produits depuis la base pour le dropdown
                    searchTerm: '',
                    statusFilter: 'all',
                    showAllFilters: false,
                    currentPage: 1,
                    itemsPerPage: 10,
                    // Modals
                    showNewOrderModal: false,
                    showOrderConfirmModal: false,
                    showEditStatusModal: false,
                    showOrderDetailsModal: false,
                    showPaymentHistoryModal: false,
                    showNewPaymentModal: false,
                    showEditPaymentModal: false,
                    showPrintOptionsModal: false,
                    printOptions: { withPrices: true, currency: 'N', conversionRate: 1 },
                    // Données
                    orderPayments: [],
                    selectedOrder: null,
                    editingOrder: null,
                    editingPayment: null,
                    newOrder: {
                        supplier: '',
                        date: new Date().toISOString().split('T')[0],
                        status: 'En_attente',
                        currency: 'N',
                        lines: []
                    },
                    newProductLine: {
                        visible: false,
                        productSearchTerm: '',
                        product: '',
                        quantity: '',
                        price: '',
                        showDropdown: false,
                        filteredProducts: []
                    },
                    newPayment: {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        notes: '',
                        file: null
                    }
                };
            },
            computed: {
                paginatedOrders() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredOrders.slice(start, start + this.itemsPerPage);
                },
                totalPages() {
                    return Math.ceil(this.filteredOrders.length / this.itemsPerPage) || 1;
                },
                totalItems() { return this.filteredOrders.length; },
                startItem() { return this.totalItems === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1; },
                endItem() {
                    const end = this.currentPage * this.itemsPerPage;
                    return end > this.totalItems ? this.totalItems : end;
                },
                visiblePages() {
                    const pages = [];
                    const start = Math.max(1, this.currentPage - 2);
                    const end = Math.min(this.totalPages, this.currentPage + 2);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },
                orderTotal() {
                    return this.newOrder.lines.reduce((t, l) => t + (l.total || 0), 0);
                },
                orderTotalQuantity() {
                    return this.newOrder.lines.reduce((t, l) => t + (parseFloat(l.quantity) || 0), 0);
                },
                selectedOrderTotal() {
                    if (!this.selectedOrder?.lines) return 0;
                    return this.selectedOrder.lines.reduce((t, l) => t + (l.quantity * l.price), 0);
                },
                selectedOrderTotalQuantity() {
                    if (!this.selectedOrder?.lines) return 0;
                    return this.selectedOrder.lines.reduce((t, l) => t + (parseFloat(l.quantity) || 0), 0);
                },
                totalPaid() {
                    return this.orderPayments.reduce((t, p) => t + parseFloat(p.amount), 0);
                },
                remainingBalance() {
                    if (!this.selectedOrder) return 0;
                    return parseFloat(this.selectedOrder.total) - this.totalPaid;
                }
            },
            methods: {
                // ---- CHARGEMENT ----
                async loadOrders() {
                    this.loading = true;
                    try {
                        const response = await api.get('?action=allOrders');
                        this.orders = response.data.map(order => ({
                            id: order.id,
                            number: `CMD-${order.id.toString().padStart(4, '0')}`,
                            date: order.date_of_insertion,
                            supplier: order.seller,
                            total: parseFloat(order.total),
                            status: order.status,
                            currency: order.currency || 'N',
                            totalQuantity: 0,
                            lines: []
                        }));
                        await this.loadOrderProducts();
                        this.applyFilters();
                    } catch (error) {
                        console.error('Erreur chargement commandes:', error);
                        alert('Erreur lors du chargement des commandes');
                    } finally {
                        this.loading = false;
                    }
                },
                async loadOrderProducts() {
                    try {
                        const response = await api.get('?action=allOrdersProducts');
                        const products = response.data;
                        this.orders.forEach(order => {
                            order.lines = products
                                .filter(p => p.order_id == order.id)
                                .map(p => ({
                                    id: p.id,
                                    product: p.name,
                                    quantity: p.quantity,
                                    price: parseFloat(p.price),
                                    editing: false,
                                    originalData: null
                                }));
                            order.totalQuantity = order.lines.reduce((t, l) => t + (parseFloat(l.quantity) || 0), 0);
                        });
                    } catch (error) {
                        console.error('Erreur chargement produits commandes:', error);
                    }
                },
                async loadAllProducts() {
                    try {
                        const response = await api.get('?action=allProducts');
                        this.allProducts = Array.isArray(response.data) ? response.data : [];
                    } catch (error) {
                        console.error('Erreur chargement produits:', error);
                        this.allProducts = [];
                    }
                },

                // ---- FILTRES ----
                applyFilters() {
                    let filtered = [...this.orders];
                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(o =>
                            o.number.toLowerCase().includes(term) ||
                            o.supplier.toLowerCase().includes(term)
                        );
                    }
                    if (this.statusFilter !== 'all') {
                        filtered = filtered.filter(o => o.status === this.statusFilter);
                    }
                    this.filteredOrders = filtered;
                    this.currentPage = 1;
                },
                clearFilters() {
                    this.searchTerm = '';
                    this.statusFilter = 'all';
                    this.applyFilters();
                },
                toggleFilters() {
                    this.showAllFilters = !this.showAllFilters;
                },

                // ---- FORMATAGE ----
                formatDate(d) {
                    if (!d) return '-';
                    return new Date(d).toLocaleDateString('fr-FR');
                },
                formatCurrency(amount, currency = 'N') {
                    const formatted = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }).format(amount || 0);
                    return `${formatted} ${currency}`;
                },
                getStatusInfo(status) {
                    const map = {
                        'En_attente': { label: 'En attente', class: 'bg-yellow-100 text-yellow-800' },
                        'En_cours':   { label: 'En cours',   class: 'bg-blue-100 text-blue-800' },
                        'Livrée':     { label: 'Livrée',     class: 'bg-green-100 text-green-800' }
                    };
                    return map[status] || { label: status, class: 'bg-gray-100 text-gray-800' };
                },

                // ---- PAGINATION ----
                previousPage() { if (this.currentPage > 1) this.currentPage--; },
                nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
                goToPage(page) { this.currentPage = page; },

                // ---- MODAL NOUVELLE COMMANDE ----
                openNewOrderModal() {
                    this.newOrder = {
                        supplier: '',
                        date: new Date().toISOString().split('T')[0],
                        status: 'En_attente',
                        currency: 'N',
                        lines: []
                    };
                    this.showNewOrderModal = true;
                    this.loadAllProducts();
                },
                closeNewOrderModal() {
                    this.showNewOrderModal = false;
                    this.showOrderConfirmModal = false;
                },
                addOrderLine() {
                    this.newOrder.lines.push({
                        product: '',
                        productSearchTerm: '',
                        quantity: '',
                        price: '',
                        total: 0,
                        showProductDropdown: false,
                        filteredProducts: []
                    });
                    // Fermer tous les autres dropdowns
                    this.newOrder.lines.forEach((l, i) => {
                        if (i < this.newOrder.lines.length - 1) l.showProductDropdown = false;
                    });
                },
                removeOrderLine(index) {
                    this.newOrder.lines.splice(index, 1);
                },
                updateLineTotal(index) {
                    const line = this.newOrder.lines[index];
                    line.total = (parseFloat(line.quantity) || 0) * (parseFloat(line.price) || 0);
                },

                // Dropdown produits dans nouvelle commande
                filterOrderProducts(index) {
                    const line = this.newOrder.lines[index];
                    const term = (line.productSearchTerm || '').toLowerCase();
                    // Assigner le nom saisi comme nom de produit (saisie libre)
                    line.product = line.productSearchTerm;
                    if (term.length === 0) {
                        line.filteredProducts = this.allProducts.slice(0, 10);
                    } else {
                        line.filteredProducts = this.allProducts.filter(p =>
                            p.name.toLowerCase().includes(term)
                        ).slice(0, 10);
                    }
                    line.showProductDropdown = true;
                },
                selectOrderProduct(index, product) {
                    const line = this.newOrder.lines[index];
                    line.productSearchTerm = product.name;
                    line.product = product.name;
                    line.showProductDropdown = false;
                    // Ne pas pré-remplir le prix, l'utilisateur le saisit
                },

               async addNewOrder() {
    if (!this.newOrder.supplier) {
        alert('Veuillez indiquer le fournisseur');
        return;
    }

    if (this.newOrder.lines.length === 0) {
        alert('Ajoutez au moins un produit');
        return;
    }

    for (const l of this.newOrder.lines) {
        if (!l.product || !l.quantity || !l.price) {
            alert('Veuillez compléter tous les champs des produits');
            return;
        }
    }

    this.showOrderConfirmModal = false;

    try {
        const orderData = {
            seller: this.newOrder.supplier,
            date: this.newOrder.date,
            total: this.orderTotal,
            status: this.newOrder.status,
            currency: this.newOrder.currency,
            lines: this.newOrder.lines.map(l => ({
                name: l.product,
                quantity: l.quantity,
                price: l.price
            }))
        };

        // 🔵 LOG PAYLOAD
        console.log('📤 Payload envoyé :', orderData);

        const response = await api.post('?action=newOrder', orderData);

        // 🔵 LOG REPONSE COMPLETE
        console.log('📥 Réponse complète :', response);

        // 🔵 LOG DATA SEULE
        console.log('📦 Response.data :', response.data);

        if (response.data.success) {
            alert('Commande créée avec succès!');
            this.closeNewOrderModal();
            await this.loadOrders();
        } else {
            console.error('❌ Erreur backend :', response.data);
            alert('Erreur lors de la création de la commande');
        }

    } catch (error) {
        // 🔴 LOG ERREUR COMPLETE
        console.error('❌ Erreur création commande (full) :', error);

        // 🔴 LOG DETAILS AXIOS
        if (error.response) {
            console.error('📥 error.response :', error.response);
            console.error('📦 error.response.data :', error.response.data);
            console.error('📊 error.response.status :', error.response.status);
        }

        if (error.request) {
            console.error('📡 error.request :', error.request);
        }

        alert('Erreur lors de la création de la commande');
    }
},

                // ---- DÉTAILS COMMANDE ----
                showOrderDetails(order) {
                    this.selectedOrder = { ...order, lines: order.lines.map(l => ({ ...l })) };
                    this.showOrderDetailsModal = true;
                    this.loadAllProducts();
                },
                closeOrderDetailsModal() {
                    this.showOrderDetailsModal = false;
                    this.selectedOrder = null;
                },

                // ---- MODIFIER STATUT ----
                editOrderStatus(order) {
                    this.editingOrder = { ...order };
                    this.showEditStatusModal = true;
                },
                closeEditStatusModal() {
                    this.showEditStatusModal = false;
                    this.editingOrder = null;
                },
                async saveEditStatus() {
                    try {
                        const response = await api.post('?action=updateOrderStatus', {
                            id: this.editingOrder.id,
                            status: this.editingOrder.status
                        });
                        if (response.data.success) {
                            const idx = this.orders.findIndex(o => o.id === this.editingOrder.id);
                            if (idx !== -1) this.orders[idx].status = this.editingOrder.status;
                            if (this.selectedOrder?.id === this.editingOrder.id) this.selectedOrder.status = this.editingOrder.status;
                            this.applyFilters();
                            this.closeEditStatusModal();
                            alert('Statut modifié avec succès!');
                        } else {
                            alert('Erreur lors de la modification du statut');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur lors de la modification du statut');
                    }
                },

                // ---- SUPPRIMER COMMANDE ----
                deleteOrder(orderId) {
                    if (!confirm('⚠️ ATTENTION: Action irréversible!\n\nSupprimer cette commande?')) return;
                    api.post('?action=deleteOrder', { id: orderId })
                        .then(r => {
                            if (r.data.success) { alert('Commande supprimée!'); this.loadOrders(); }
                            else alert('Erreur: ' + r.data.error);
                        })
                        .catch(e => { console.error(e); alert('Erreur suppression'); });
                },

                // ---- LIGNES DÉTAIL (ajout nouvelle ligne) ----
                addNewProductLine() {
                    this.newProductLine = {
                        visible: true,
                        productSearchTerm: '',
                        product: '',
                        quantity: '',
                        price: '',
                        showDropdown: false,
                        filteredProducts: []
                    };
                },
                filterNewLineProducts() {
                    const term = (this.newProductLine.productSearchTerm || '').toLowerCase();
                    this.newProductLine.product = this.newProductLine.productSearchTerm;
                    if (term.length === 0) {
                        this.newProductLine.filteredProducts = this.allProducts.slice(0, 10);
                    } else {
                        this.newProductLine.filteredProducts = this.allProducts.filter(p =>
                            p.name.toLowerCase().includes(term)
                        ).slice(0, 10);
                    }
                    this.newProductLine.showDropdown = true;
                },
                selectNewLineProduct(product) {
                    this.newProductLine.productSearchTerm = product.name;
                    this.newProductLine.product = product.name;
                    this.newProductLine.showDropdown = false;
                },
                async validateNewLine() {
                    if (!this.newProductLine.product || !this.newProductLine.quantity || !this.newProductLine.price) {
                        alert('Veuillez remplir tous les champs');
                        return;
                    }
                    try {
                        const response = await api.post('?action=newOrderProduct', {
                            order_id: this.selectedOrder.id,
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
                            this.selectedOrder.lines.push(newLine);
                            const idx = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (idx !== -1) {
                                this.orders[idx].lines.push(newLine);
                                this.orders[idx].total = this.selectedOrderTotal;
                                this.orders[idx].totalQuantity = this.selectedOrderTotalQuantity;
                            }
                            this.cancelNewLine();
                            alert('Produit ajouté avec succès!');
                        } else {
                            alert('Erreur lors de l\'ajout du produit');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur lors de l\'ajout du produit');
                    }
                },
                cancelNewLine() {
                    this.newProductLine = { visible: false, productSearchTerm: '', product: '', quantity: '', price: '', showDropdown: false, filteredProducts: [] };
                },

                // ---- ÉDITION LIGNE EXISTANTE ----
                editProductLine(index) {
                    const line = this.selectedOrder.lines[index];
                    line.originalData = { product: line.product, quantity: line.quantity, price: line.price };
                    line.editing = true;
                },
                async validateProductEdit(index) {
                    const line = this.selectedOrder.lines[index];
                    if (!line.id) { alert('Erreur: ID du produit manquant'); return; }
                    if (!line.product?.trim()) { alert('Le nom du produit est obligatoire'); return; }
                    if (!line.quantity || line.quantity <= 0) { alert('La quantité doit être > 0'); return; }
                    if (line.price === null || line.price < 0) { alert('Le prix doit être positif'); return; }
                    try {
                        const response = await api.post('?action=updateOrderProduct', {
                            id: line.id, name: line.product, quantity: line.quantity, price: line.price
                        });
                        if (response.data.success) {
                            line.editing = false;
                            line.originalData = null;
                            const idx = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (idx !== -1) {
                                const pIdx = this.orders[idx].lines.findIndex(l => l.id === line.id);
                                if (pIdx !== -1) this.orders[idx].lines[pIdx] = { ...line };
                                this.orders[idx].total = this.selectedOrderTotal;
                                this.orders[idx].totalQuantity = this.selectedOrderTotalQuantity;
                            }
                            alert('Produit modifié avec succès!');
                        } else {
                            alert('Erreur: ' + (response.data.message || response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur: ' + error.message);
                    }
                },
                cancelProductEdit(index) {
                    const line = this.selectedOrder.lines[index];
                    if (line.originalData) {
                        line.product = line.originalData.product;
                        line.quantity = line.originalData.quantity;
                        line.price = line.originalData.price;
                        line.originalData = null;
                    }
                    line.editing = false;
                },
                deleteOrderItem(itemId) {
                    if (!confirm('⚠️ Supprimer cet article?')) return;
                    api.post('?action=deleteOrderProduct', { id: itemId })
                        .then(r => {
                            if (r.data.success) {
                                const delIdx = this.selectedOrder.lines.findIndex(l => l.id === itemId);
                                if (delIdx !== -1) this.selectedOrder.lines.splice(delIdx, 1);
                                const oIdx = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                                if (oIdx !== -1) {
                                    this.orders[oIdx].lines = [...this.selectedOrder.lines];
                                    this.orders[oIdx].total = this.selectedOrderTotal;
                                    this.orders[oIdx].totalQuantity = this.selectedOrderTotalQuantity;
                                }
                                this.applyFilters();
                                alert('Article supprimé!');
                            } else {
                                alert('Erreur: ' + r.data.error);
                            }
                        })
                        .catch(e => { console.error(e); alert('Erreur suppression article'); });
                },

                // ---- IMPRESSION ----
                openPrintOptionsModal() {
                    this.printOptions = { withPrices: true, currency: this.selectedOrder.currency, conversionRate: 1 };
                    this.showPrintOptionsModal = true;
                },
                closePrintOptionsModal() { this.showPrintOptionsModal = false; },
                executePrint() {
                    this.closePrintOptionsModal();
                    const printWindow = window.open('', '_blank');
                    const order = this.selectedOrder;
                    const rate = this.printOptions.currency === order.currency ? 1 : this.printOptions.conversionRate;
                    const displayCurrency = this.printOptions.withPrices ? this.printOptions.currency : order.currency;
                    const totalQuantity = order.lines.reduce((s, l) => s + parseFloat(l.quantity), 0);
                    const productCount = order.lines.length;
                    const totalAmount = order.lines.reduce((s, l) => s + (l.quantity * l.price * rate), 0);
                    let productsRows = '';
                    order.lines.forEach((line, i) => {
                        productsRows += `<tr>
                            <td style="border:1px solid #ddd;padding:10px;text-align:center;">${i + 1}</td>
                            <td style="border:1px solid #ddd;padding:10px;">${line.product}</td>
                            <td style="border:1px solid #ddd;padding:10px;text-align:center;">${line.quantity}</td>`;
                        if (this.printOptions.withPrices) {
                            productsRows += `<td style="border:1px solid #ddd;padding:10px;text-align:right;">${this.formatCurrency(line.price * rate, displayCurrency)}</td>
                            <td style="border:1px solid #ddd;padding:10px;text-align:right;font-weight:bold;">${this.formatCurrency(line.quantity * line.price * rate, displayCurrency)}</td>`;
                        }
                        productsRows += `</tr>`;
                    });
                    const printContent = `<!DOCTYPE html><html><head>
                        <title>Bon de commande ${order.number}</title>
                        <style>
                            @page{margin:1.5cm;size:A4;}
                            body{font-family:Arial,sans-serif;margin:0;padding:20px;font-size:12px;}
                            .header{text-align:center;margin-bottom:30px;border-bottom:3px solid #2563EB;padding-bottom:15px;}
                            .header h1{margin:0;color:#2563EB;font-size:28px;}
                            .order-info{margin:20px 0;display:grid;grid-template-columns:1fr 1fr;gap:15px;}
                            .info-box{background:#f9fafb;padding:10px;border-radius:5px;}
                            .label{font-weight:bold;color:#374151;font-size:11px;text-transform:uppercase;}
                            .value{color:#1F2937;font-size:14px;margin-top:3px;}
                            .products-table{width:100%;border-collapse:collapse;margin:20px 0;}
                            .products-table th{background:#2563EB;color:white;padding:10px;text-align:left;font-size:11px;text-transform:uppercase;}
                            .products-table td{border:1px solid #ddd;padding:10px;font-size:12px;}
                            .products-table tr:nth-child(even){background:#f9fafb;}
                            .summary{margin-top:20px;padding:15px;background:#f0f9ff;border:2px solid #2563EB;border-radius:5px;}
                            .summary-row{display:flex;justify-content:space-between;margin:8px 0;font-size:13px;}
                            .total-amount{font-size:22px;font-weight:bold;color:#2563EB;}
                            .footer{margin-top:40px;text-align:center;font-size:10px;color:#6B7280;border-top:1px solid #ddd;padding-top:15px;}
                        </style></head><body>
                        <div class="header">
                            <h1>ETS TOBI LODA ET FILS</h1>
                            <p>Commerçialisation de boissons<br>IFU 0202371384670<br>Lokossa, Quinji carrefour Abo, téléphone 01 49 91 65 66</p>
                            <div style="font-size:14px;color:#6B7280;margin-top:5px;">N° ${order.number}</div>
                        </div>
                        <div class="order-info">
                            <div class="info-box"><div class="label">Fournisseur</div><div class="value">${order.supplier}</div></div>
                            <div class="info-box"><div class="label">Date</div><div class="value">${this.formatDate(order.date)}</div></div>
                            <div class="info-box"><div class="label">Statut</div><div class="value">${this.getStatusInfo(order.status).label}</div></div>
                            ${this.printOptions.withPrices && displayCurrency !== order.currency ? `<div class="info-box"><div class="label">Taux</div><div class="value">1 ${order.currency} = ${rate} ${displayCurrency}</div></div>` : ''}
                        </div>
                        <h3 style="margin-top:30px;color:#1F2937;">Liste des produits</h3>
                        <table class="products-table"><thead><tr>
                            <th style="width:5%;text-align:center;">N°</th>
                            <th style="width:${this.printOptions.withPrices ? '45%' : '80%'};">Produit</th>
                            <th style="width:15%;text-align:center;">Quantité</th>
                            ${this.printOptions.withPrices ? '<th style="width:17.5%;text-align:right;">Prix unitaire</th><th style="width:17.5%;text-align:right;">Total</th>' : ''}
                        </tr></thead><tbody>${productsRows}</tbody></table>
                        <div class="summary">
                            <div class="summary-row"><span style="font-weight:bold;">Produits différents:</span><span style="font-weight:bold;color:#059669;">${productCount}</span></div>
                            <div class="summary-row"><span style="font-weight:bold;">Quantité totale:</span><span style="font-weight:bold;color:#0ea5e9;">${totalQuantity}</span></div>
                            ${this.printOptions.withPrices ? `<div class="summary-row" style="border-top:2px solid #2563EB;padding-top:10px;margin-top:10px;"><span style="font-size:16px;font-weight:bold;">MONTANT TOTAL:</span><span class="total-amount">${this.formatCurrency(totalAmount, displayCurrency)}</span></div>` : ''}
                        </div>
                        <div class="footer"><p>Merci pour votre collaboration!</p><p>Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p></div>
                        </body></html>`;
                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                },

                // ---- HISTORIQUE PAIEMENTS ----
                async showPaymentHistory(order) {
                    this.selectedOrder = order;
                    try {
                        const response = await api.get('?action=allOrdersPayments');
                        this.orderPayments = Array.isArray(response.data)
                            ? response.data.filter(p => p.order_id == order.id)
                            : [];
                    } catch (error) {
                        console.error('Erreur paiements:', error);
                        this.orderPayments = [];
                    }
                    this.showPaymentHistoryModal = true;
                },
                closePaymentHistoryModal() {
                    this.showPaymentHistoryModal = false;
                    this.orderPayments = [];
                },
                openNewPaymentModal() {
                    this.newPayment = { amount: '', date: new Date().toISOString().split('T')[0], notes: '', file: null };
                    this.showNewPaymentModal = true;
                },
                closeNewPaymentModal() {
                    this.showNewPaymentModal = false;
                    this.newPayment = { amount: '', date: new Date().toISOString().split('T')[0], notes: '', file: null };
                },
                handleFileUpload(event) { this.newPayment.file = event.target.files[0] || null; },
                async addNewPayment() {
                    if (this.newPayment.amount > this.remainingBalance) { alert('Le montant dépasse le solde restant'); return; }
                    if (this.newPayment.amount <= 0) { alert('Le montant doit être > 0'); return; }
                    const formData = new FormData();
                    formData.append('order_id', this.selectedOrder.id);
                    formData.append('amount', this.newPayment.amount);
                    formData.append('date_of_insertion', this.newPayment.date);
                    formData.append('notes', this.newPayment.notes);
                    if (this.newPayment.file) formData.append('file', this.newPayment.file);
                    try {
                        const response = await api.post('?action=newOrderPayment', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
                        if (response.data.success || response.data.id) {
                            alert('Paiement ajouté!');
                            this.closeNewPaymentModal();
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur: ' + error.message);
                    }
                },
                getImgUrl(fileName) {
                    if (!fileName) return '';
                    return `${imgBaseUrl}${fileName}`;
                },
                editPayment(payment) {
                    this.editingPayment = {
                        id: payment.id,
                        amount: payment.amount,
                        originalAmount: payment.amount,
                        date: payment.date_of_insertion.split(' ')[0],
                        notes: payment.notes || '',
                        existingFile: payment.file || '',
                        file: null
                    };
                    this.showEditPaymentModal = true;
                },
                closeEditPaymentModal() { this.showEditPaymentModal = false; this.editingPayment = null; },
                handleEditFileUpload(event) { this.editingPayment.file = event.target.files[0] || null; },
                async saveEditPayment() {
                    const maxAllowed = this.remainingBalance + parseFloat(this.editingPayment.originalAmount);
                    if (this.editingPayment.amount > maxAllowed) { alert(`Montant max: ${this.formatCurrency(maxAllowed, this.selectedOrder.currency)}`); return; }
                    if (this.editingPayment.amount <= 0) { alert('Le montant doit être > 0'); return; }
                    const formData = new FormData();
                    formData.append('id', this.editingPayment.id);
                    formData.append('amount', this.editingPayment.amount);
                    formData.append('date_of_insertion', this.editingPayment.date);
                    formData.append('notes', this.editingPayment.notes);
                    if (this.editingPayment.file) formData.append('file', this.editingPayment.file);
                    try {
                        const response = await api.post('?action=updateOrderPayment', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
                        if (response.data.success) {
                            alert('Paiement modifié!');
                            this.closeEditPaymentModal();
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur: ' + error.message);
                    }
                },
                async deletePayment(paymentId) {
                    if (!confirm('Supprimer ce paiement?')) return;
                    try {
                        const response = await api.post('?action=deleteOrderPayment', { id: paymentId });
                        if (response.data.success) {
                            alert('Paiement supprimé!');
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Erreur: ' + error.message);
                    }
                },
                printPaymentHistory() {
                    const printWindow = window.open('', '_blank');
                    const paymentsRows = this.orderPayments.map(p => `
                        <tr>
                            <td style="border:1px solid #ddd;padding:8px;">${this.formatDate(p.date_of_insertion)}</td>
                            <td style="border:1px solid #ddd;padding:8px;font-weight:bold;color:#059669;">${this.formatCurrency(p.amount, this.selectedOrder.currency)}</td>
                            <td style="border:1px solid #ddd;padding:8px;">${p.notes || '-'}</td>
                            <td style="border:1px solid #ddd;padding:8px;">${p.file && p.file !== '' ? 'Oui' : 'Non'}</td>
                        </tr>`).join('');
                    const html = `<!DOCTYPE html><html><head><title>Historique Paiements - ${this.selectedOrder.number}</title>
                        <style>body{font-family:Arial,sans-serif;margin:20px;}h1{color:#1f2937;border-bottom:2px solid #2563EB;padding-bottom:10px;}
                        .info-section{margin:20px 0;display:grid;grid-template-columns:repeat(3,1fr);gap:15px;}
                        .info-box{border:1px solid #ddd;padding:15px;border-radius:8px;}.info-box .label{font-size:12px;color:#6b7280;margin-bottom:5px;}
                        .info-box .value{font-size:18px;font-weight:bold;}.blue{color:#2563EB;}.green{color:#059669;}.red{color:#DC2626;}
                        table{width:100%;border-collapse:collapse;margin-top:20px;}
                        th{background:#f3f4f6;border:1px solid #ddd;padding:12px;text-align:left;font-weight:bold;color:#374151;}
                        td{border:1px solid #ddd;padding:8px;}tr:nth-child(even){background:#f9fafb;}
                        .footer{margin-top:30px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body>
                        <h1>Historique des Paiements</h1><h2>Commande ${this.selectedOrder.number}</h2>
                        <div class="info-section">
                            <div class="info-box"><div class="label">Montant total</div><div class="value blue">${this.formatCurrency(this.selectedOrder.total, this.selectedOrder.currency)}</div></div>
                            <div class="info-box"><div class="label">Montant payé</div><div class="value green">${this.formatCurrency(this.totalPaid, this.selectedOrder.currency)}</div></div>
                            <div class="info-box"><div class="label">Solde restant</div><div class="value red">${this.formatCurrency(this.remainingBalance, this.selectedOrder.currency)}</div></div>
                        </div>
                        <table><thead><tr>
                            <th>Date</th><th>Montant</th><th>Notes</th><th>Justificatif</th>
                        </tr></thead><tbody>${paymentsRows}</tbody></table>
                        <div class="footer"><p>Généré le ${new Date().toLocaleDateString('fr-FR')}</p></div>
                        </body></html>`;
                    printWindow.document.write(html);
                    printWindow.document.close();
                    printWindow.print();
                }
            },
            mounted() {
                this.loadOrders();
                // Fermer les dropdowns en cliquant ailleurs
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.relative')) {
                        this.newOrder.lines.forEach(l => l.showProductDropdown = false);
                        if (this.newProductLine) this.newProductLine.showDropdown = false;
                    }
                });
            }
        }).mount('#app');
    </script>
</body>
</html>
