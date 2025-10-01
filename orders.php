<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tossin - Gestion des Commandes</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
    <style>
        .primary {
            color: #2563EB;
        }

        .secondary {
            color: #1E40AF;
        }

        .accent {
            color: #F59E0B;
        }

        .bg-primary {
            background-color: #2563EB;
        }

        .bg-secondary {
            background-color: #1E40AF;
        }

        .bg-accent {
            background-color: #F59E0B;
        }

        .hover\:bg-accent:hover {
            background-color: #D97706;
        }

        .hover\:bg-secondary:hover {
            background-color: #1E40AF;
        }

        .text-primary {
            color: #2563EB;
        }

        .text-secondary {
            color: #1E40AF;
        }

        .text-accent {
            color: #F59E0B;
        }

        .border-primary {
            border-color: #2563EB;
        }

        .focus\:ring-primary:focus {
            --tw-ring-color: #2563EB;
        }

        /* Adding print styles to hide action elements during printing */
        @media print {
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            body {
                background: white !important;
            }

            .modal-content {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            .bg-gray-50,
            .bg-blue-50,
            .bg-green-50,
            .bg-yellow-50,
            .bg-purple-50 {
                background: white !important;
                border: 1px solid #ccc !important;
            }

            /* Changing print layout to display products one by one instead of horizontal table */
            .print-products {
                display: block !important;
            }

            .print-product-item {
                display: block !important;
                border: 1px solid #000 !important;
                margin-bottom: 10px !important;
                padding: 10px !important;
                page-break-inside: avoid !important;
            }

            .print-product-row {
                display: flex !important;
                justify-content: space-between !important;
                margin-bottom: 5px !important;
            }

            .print-product-label {
                font-weight: bold !important;
                width: 40% !important;
            }

            .print-product-value {
                width: 60% !important;
                text-align: right !important;
            }

            .print-header {
                margin-bottom: 20px !important;
            }

            .print-summary {
                margin-top: 20px !important;
                border-top: 2px solid #000 !important;
                padding-top: 10px !important;
            }

            /* Hide the table for print and show the product list instead */
            .print-table {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .responsive-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .responsive-table thead {
                display: none;
            }

            .responsive-table tbody,
            .responsive-table tr,
            .responsive-table td {
                display: block;
            }

            .responsive-table tr {
                border: 1px solid #e5e7eb;
                margin-bottom: 1rem;
                padding: 1rem;
                border-radius: 0.5rem;
                background: white;
            }

            .responsive-table td {
                border: none;
                padding: 0.5rem 0;
                position: relative;
                padding-left: 50%;
            }

            .responsive-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: 600;
                color: #374151;
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
                            <h1 class="text-2xl font-bold text-gray-900">Gestion des Commandes</h1>
                            <button @click="openNewOrderModal"
                                class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                <i class="fas fa-plus mr-2"></i>Nouvelle commande
                            </button>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="N° commande ou fournisseur..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous</option>
                                    <option value="En_attente">En attente</option>
                                    <option value="En_cours">En cours</option>
                                    <option value="Livrée">Livrée</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button @click="applyFilters" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-filter mr-2"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div v-if="loading" class="flex justify-center items-center h-64">
                            <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 responsive-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Commande</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fournisseur</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="Total">
                                            {{ formatCurrency(order.total, order.currency) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-label="Statut">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusInfo(order.status).class]">
                                                {{ getStatusInfo(order.status).label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
                                            <button @click="showPaymentHistory(order)" class="text-green-600 hover:text-green-800 mr-3" title="Historique des paiements">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button @click="showOrderDetails(order)" class="text-primary hover:text-secondary mr-3" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="editOrderStatus(order)" class="text-accent hover:text-yellow-600 mr-3" title="Modifier statut">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteOrder(order.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button @click="previousPage" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Précédent
                                </button>
                                <button @click="nextPage" :disabled="currentPage === totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Suivant
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Affichage de <span>{{ startItem }}</span> à <span>{{ endItem }}</span> sur <span>{{ totalItems }}</span> résultats
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <button @click="previousPage" :disabled="currentPage === 1"
                                            :class="['relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50', currentPage === 1 ? 'cursor-not-allowed' : '']">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                                            :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium', 
                                                page === currentPage ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50']">
                                            {{ page }}
                                        </button>
                                        <button @click="nextPage" :disabled="currentPage === totalPages"
                                            :class="['relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50', currentPage === totalPages ? 'cursor-not-allowed' : '']">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Nouvelle Commande -->
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
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-truck mr-1"></i>Fournisseur
                                    </label>
                                    <input v-model="newOrder.supplier" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date
                                    </label>
                                    <input v-model="newOrder.date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill mr-1"></i>Devise
                                    </label>
                                    <select v-model="newOrder.currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                                    <select v-model="newOrder.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="En_attente">En attente</option>
                                        <option value="En_cours">En cours</option>
                                        <option value="Livrée">Livrée</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <i class="fas fa-list mr-2"></i>Lignes de commande
                                    </h4>
                                    <button type="button" @click="addOrderLine" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Ajouter ligne
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <div v-for="(line, index) in newOrder.lines" :key="index" class="grid grid-cols-1 md:grid-cols-6 gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-box mr-1"></i>Produit
                                            </label>
                                            <input v-model="line.product" type="text" required
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantiteé
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
                                            <input :value="formatCurrency(line.total, newOrder.currency)" type="text" readonly
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-gray-100">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" @click="removeOrderLine(index)"
                                                class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-2 rounded text-sm transition-colors">
                                                <i class="fas fa-trash mr-1"></i>Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                                        <span class="text-2xl font-bold text-primary">{{ formatCurrency(orderTotal, newOrder.currency) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-accent hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Créer la commande
                                </button>
                                <button type="button" @click="closeNewOrderModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Modifier Ligne -->
            <div v-if="showEditLineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-70">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2"></i>Modifier la ligne
                            </h3>
                            <button @click="closeEditLineModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveEditLine" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-box mr-1"></i>Nom du produit
                                </label>
                                <input v-model="editingLine.product" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                                </label>
                                <input v-model.number="editingLine.quantity" type="number" required min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire
                                </label>
                                <input v-model.number="editingLine.price" type="number" required min="0" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-primary hover:bg-secondary text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Sauvegarder
                                </button>
                                <button type="button" @click="closeEditLineModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Modifier Statut -->
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
                                <select v-model="editingOrder.status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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

            <!-- Modal Détails Commande -->
            <div v-if="showOrderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto modal-content">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-eye mr-2"></i>Détails de la commande
                            </h3>
                            <div class="flex space-x-2">
                                <button @click="printOrderDetails" class="no-print bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-print mr-1"></i>Imprimer
                                </button>
                                <button @click="closeOrderDetailsModal" class="no-print text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedOrder">
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
                                    <div v-else class="text-sm text-gray-500 italic">
                                        <i class="fas fa-lock mr-1"></i>Commande livrée - Modification désactivée
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <!-- Regular table view for screen -->
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg responsive-table print-table">
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
                                                    <div v-if="line.editing" class="flex space-x-1">
                                                        <button @click="validateProductEdit(index)" class="text-green-600 hover:text-green-800" title="Valider">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button @click="cancelProductEdit(index)" class="text-gray-600 hover:text-gray-800" title="Annuler">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div v-else class="flex space-x-1">
                                                        <button @click="editProductLine(index)" class="text-blue-600 hover:text-blue-800" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button @click="deleteProductLine(index)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- New product line row -->
                                            <tr v-if="newProductLine.visible && selectedOrder.status !== 'Livrée'" class="bg-green-50">
                                                <td class="px-4 py-3 text-sm" data-label="Produit">
                                                    <input v-model="newProductLine.product" type="text" placeholder="Nom du produit" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Quantité">
                                                    <input v-model.number="newProductLine.quantity" type="number" min="1" placeholder="Quantité" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                                <td class="px-4 py-3 text-sm" data-label="Prix unitaire">
                                                    <input v-model.number="newProductLine.price" type="number" step="0.01" min="0" placeholder="Prix" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                </td>
                                                <td class="px-4 py-3 text-sm font-medium" data-label="Total">{{ formatCurrency(newProductLine.quantity * newProductLine.price || 0, selectedOrder.currency) }}</td>
                                                <td class="px-4 py-3 text-sm no-print" data-label="Actions">
                                                    <div class="flex space-x-1">
                                                        <button @click="validateNewLine" class="text-green-600 hover:text-green-800" title="Valider">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button @click="cancelNewLine" class="text-gray-600 hover:text-gray-800" title="Annuler">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Print-only product list (displayed one by one) -->
                                    <div class="print-products hidden">
                                        <div v-for="(line, index) in selectedOrder.lines" :key="index" class="print-product-item">
                                            <div class="print-product-row">
                                                <span class="print-product-label">Produit:</span>
                                                <span class="print-product-value">{{ line.product }}</span>
                                            </div>
                                            <div class="print-product-row">
                                                <span class="print-product-label">Quantité:</span>
                                                <span class="print-product-value">{{ line.quantity }}</span>
                                            </div>
                                            <div class="print-product-row">
                                                <span class="print-product-label">Prix unitaire:</span>
                                                <span class="print-product-value">{{ formatCurrency(line.price, selectedOrder.currency) }}</span>
                                            </div>
                                            <div class="print-product-row">
                                                <span class="print-product-label">Total:</span>
                                                <span class="print-product-value">{{ formatCurrency(line.quantity * line.price, selectedOrder.currency) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 p-4 bg-gray-50 rounded-lg print-summary">
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

            <!-- Modal Payment History -->
            <div v-if="showPaymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50" style="z-index: 9997;">
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
                                <!-- Added responsive-table class and data-label attributes to make the payment history table responsive -->
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg responsive-table">
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
                                                <i class="fas fa-inbox text-4xl mb-2"></i>
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
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="deletePayment(payment.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
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


            <!-- Modal New Payment -->
            <div v-if="showNewPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-60" style="z-index: 9999;">
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

            <!-- Modal Edit Payment -->
            <div v-if="showEditPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-70" style="z-index: 9998;">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-60 flex items-center justify-center p-4 overflow-auto">
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
        const {
            createApp
        } = Vue;

        // Crée une instance Axios avec une baseURL
        const api = axios.create({
            baseURL: 'http://127.0.0.1/tossin/api/index.php'
        });

        // Définition de la base pour tes images
        const imgBaseUrl = 'http://127.0.0.1/tossin/api/uploads/order_payments/';


        createApp({
            data() {
                return {
                    loading: false,
                    orders: [],
                    filteredOrders: [],
                    searchTerm: '',
                    statusFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showNewOrderModal: false,
                    showEditLineModal: false,
                    showEditStatusModal: false,
                    showOrderDetailsModal: false,
                    showPaymentHistoryModal: false,
                    showNewPaymentModal: false,
                    showEditPaymentModal: false,
                    orderPayments: [],
                    selectedOrder: null,
                    editingOrder: null,
                    editingLine: null,
                    editingLineIndex: null,
                    editingPayment: null,
                    newOrder: {
                        supplier: '',
                        date: '',
                        status: 'En_attente',
                        currency: 'N', // Added default currency
                        lines: []
                    },
                    newProductLine: {
                        visible: false,
                        product: '',
                        quantity: '',
                        price: ''
                    },
                    newPayment: {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        notes: '',
                        file: null
                    }
                }
            },
            computed: {
                paginatedOrders() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredOrders.slice(start, end);
                },
                totalPages() {
                    return Math.ceil(this.filteredOrders.length / this.itemsPerPage);
                },
                totalItems() {
                    return this.filteredOrders.length;
                },
                startItem() {
                    return (this.currentPage - 1) * this.itemsPerPage + 1;
                },
                endItem() {
                    const end = this.currentPage * this.itemsPerPage;
                    return end > this.totalItems ? this.totalItems : end;
                },
                visiblePages() {
                    const pages = [];
                    const start = Math.max(1, this.currentPage - 2);
                    const end = Math.min(this.totalPages, this.currentPage + 2);

                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },
                orderTotal() {
                    return this.newOrder.lines.reduce((total, line) => total + (line.total || 0), 0);
                },
                selectedOrderTotal() {
                    if (!this.selectedOrder || !this.selectedOrder.lines) return 0;
                    return this.selectedOrder.lines.reduce((total, line) => total + (line.quantity * line.price), 0);
                },
                totalPaid() {
                    return this.orderPayments.reduce((total, payment) => total + parseFloat(payment.amount), 0);
                },
                remainingBalance() {
                    if (!this.selectedOrder) return 0;
                    return parseFloat(this.selectedOrder.total) - this.totalPaid;
                }
            },
            methods: {
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
                            currency: order.currency || 'N', // Added currency field
                            lines: []
                        }));

                        // Load products for each order
                        await this.loadProducts();
                        this.applyFilters();
                    } catch (error) {
                        console.error('Erreur lors du chargement des commandes:', error);
                        alert('Erreur lors du chargement des commandes');
                    } finally {
                        this.loading = false;
                    }
                },
                async loadProducts() {
                    try {
                        const response = await api.get('?action=allProducts');
                        const products = response.data;

                        // Group products by order_id
                        this.orders.forEach(order => {
                            order.lines = products.filter(product => product.order_id == order.id).map(product => ({
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
                applyFilters() {
                    let filtered = [...this.orders];

                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(order =>
                            order.number.toLowerCase().includes(term) ||
                            order.supplier.toLowerCase().includes(term)
                        );
                    }

                    if (this.statusFilter !== 'all') {
                        filtered = filtered.filter(order => order.status === this.statusFilter);
                    }

                    this.filteredOrders = filtered;
                    this.currentPage = 1;
                },
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('fr-FR');
                },
                formatCurrency(amount, currency = 'N') {
                    const formatted = new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);
                    return `${formatted} ${currency}`;
                },
                getStatusInfo(status) {
                    const statusMap = {
                        'En_attente': {
                            label: 'En attente',
                            class: 'bg-yellow-100 text-yellow-800'
                        },
                        'En_cours': {
                            label: 'En cours',
                            class: 'bg-blue-100 text-blue-800'
                        },
                        'Livrée': {
                            label: 'Livrée',
                            class: 'bg-green-100 text-green-800'
                        }
                    };
                    return statusMap[status] || {
                        label: status,
                        class: 'bg-gray-100 text-gray-800'
                    };
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
                openNewOrderModal() {
                    this.newOrder = {
                        supplier: '',
                        date: new Date().toISOString().split('T')[0],
                        status: 'En_attente',
                        currency: 'N', // Added default currency
                        lines: []
                    };
                    this.showNewOrderModal = true;
                },
                closeNewOrderModal() {
                    this.showNewOrderModal = false;
                },
                addOrderLine() {
                    this.newOrder.lines.push({
                        product: '',
                        quantity: '',
                        price: '',
                        total: 0
                    });
                },
                removeOrderLine(index) {
                    this.newOrder.lines.splice(index, 1);
                },
                updateLineTotal(index) {
                    const line = this.newOrder.lines[index];
                    line.total = line.quantity * line.price;
                },
                editOrderLineInModal(index) {
                    this.editingLine = {
                        ...this.newOrder.lines[index]
                    };
                    this.editingLineIndex = index;
                    this.showEditLineModal = true;
                },
                closeEditLineModal() {
                    this.showEditLineModal = false;
                    this.editingLine = null;
                    this.editingLineIndex = null;
                },
                saveEditLine() {
                    if (this.editingLineIndex !== null) {
                        this.newOrder.lines[this.editingLineIndex] = {
                            ...this.editingLine
                        };
                        this.updateLineTotal(this.editingLineIndex);
                        this.closeEditLineModal();
                    }
                },
                async addNewOrder() {
                    try {
                        const orderData = {
                            seller: this.newOrder.supplier,
                            total: this.orderTotal,
                            status: this.newOrder.status,
                            currency: this.newOrder.currency, // Added currency to order data
                            lines: this.newOrder.lines
                        };

                        const response = await api.post('?action=newOrder', orderData);

                        if (response.data.success) {
                            alert('Commande créée avec succès!');
                            this.closeNewOrderModal();
                            await this.loadOrders();
                        } else {
                            alert('Erreur lors de la création de la commande');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la création de la commande');
                    }
                },
                showOrderDetails(order) {
                    this.selectedOrder = {
                        ...order,
                        lines: [...order.lines]
                    };
                    this.showOrderDetailsModal = true;
                },
                closeOrderDetailsModal() {
                    this.showOrderDetailsModal = false;
                    this.selectedOrder = null;
                },
                editOrderStatus(order) {
                    this.editingOrder = {
                        ...order
                    };
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
                            const orderIndex = this.orders.findIndex(o => o.id === this.editingOrder.id);
                            if (orderIndex !== -1) {
                                this.orders[orderIndex].status = this.editingOrder.status;
                            }
                            if (this.selectedOrder && this.selectedOrder.id === this.editingOrder.id) {
                                this.selectedOrder.status = this.editingOrder.status;
                            }
                            this.applyFilters();
                            this.closeEditStatusModal();
                            alert('Statut modifié avec succès!');
                        } else {
                            alert('Erreur lors de la modification du statut');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la modification du statut');
                    }
                },
                async deleteOrder(orderId) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
                        try {
                            const response = await api.post('?action=deleteOrder', {
                                id: orderId
                            });

                            if (response.data.success) {
                                await this.loadOrders();
                                alert('Commande supprimée avec succès!');
                            } else {
                                alert('Erreur lors de la suppression de la commande');
                            }
                        } catch (error) {
                            console.error('Erreur:', error);
                            alert('Erreur lors de la suppression de la commande');
                        }
                    }
                },
                addNewProductLine() {
                    this.newProductLine = {
                        visible: true,
                        product: '',
                        quantity: '',
                        price: ''
                    };
                },
                async validateNewLine() {
                    if (!this.newProductLine.product || !this.newProductLine.quantity || !this.newProductLine.price) {
                        alert('Veuillez remplir tous les champs');
                        return;
                    }

                    try {
                        const response = await api.post('?action=newProduct', {
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

                            // Update the main orders array
                            const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (orderIndex !== -1) {
                                this.orders[orderIndex].lines.push(newLine);
                                this.orders[orderIndex].total = this.selectedOrderTotal;
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
                    const line = this.selectedOrder.lines[index];
                    line.originalData = {
                        product: line.product,
                        quantity: line.quantity,
                        price: line.price
                    };
                    line.editing = true;
                },
                async validateProductEdit(index) {
                    const line = this.selectedOrder.lines[index];

                    if (!line.id) {
                        alert('Erreur: ID du produit manquant');
                        console.error('[v0] Product ID missing:', line);
                        return;
                    }

                    if (!line.product || line.product.trim() === '') {
                        alert('Le nom du produit est obligatoire');
                        return;
                    }

                    if (!line.quantity || line.quantity <= 0) {
                        alert('La quantité doit être supérieure à 0');
                        return;
                    }

                    if (line.price === null || line.price === undefined || line.price < 0) {
                        alert('Le prix doit être un nombre positif');
                        return;
                    }

                    try {
                        console.log('[v0] Sending product update:', {
                            id: line.id,
                            name: line.product,
                            quantity: line.quantity,
                            price: line.price
                        });

                        const response = await api.post('?action=updateProduct', {
                            id: line.id,
                            name: line.product,
                            quantity: line.quantity,
                            price: line.price
                        });

                        console.log('[v0] API response:', response.data);

                        if (response.data.success) {
                            line.editing = false;
                            line.originalData = null;

                            // Update the main orders array
                            const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (orderIndex !== -1) {
                                const productIndex = this.orders[orderIndex].lines.findIndex(l => l.id === line.id);
                                if (productIndex !== -1) {
                                    this.orders[orderIndex].lines[productIndex] = {
                                        ...line
                                    };
                                    this.orders[orderIndex].total = this.selectedOrderTotal;
                                }
                            }

                            alert('Produit modifié avec succès!');
                        } else {
                            alert(`Erreur lors de la modification du produit: ${response.data.message || response.data.error || 'Erreur inconnue'}`);
                            console.error('[v0] Backend error:', response.data);
                        }
                    } catch (error) {
                        console.error('[v0] Request error:', error);
                        alert(`Erreur lors de la modification du produit: ${error.message}`);
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
                async deleteProductLine(index) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
                        const line = this.selectedOrder.lines[index];

                        try {
                            const response = await api.post('?action=deleteProduct', {
                                id: line.id
                            });

                            if (response.data.success) {
                                this.selectedOrder.lines.splice(index, 1);

                                // Update the main orders array
                                const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                                if (orderIndex !== -1) {
                                    const productIndex = this.orders[orderIndex].lines.findIndex(l => l.id === line.id);
                                    if (productIndex !== -1) {
                                        this.orders[orderIndex].lines.splice(productIndex, 1);
                                        this.orders[orderIndex].total = this.selectedOrderTotal;
                                    }
                                }

                                alert('Produit supprimé avec succès!');
                            } else {
                                alert('Erreur lors de la suppression du produit');
                            }
                        } catch (error) {
                            console.error('Erreur:', error);
                            alert('Erreur lors de la suppression du produit');
                        }
                    }
                },
                printOrderDetails() {
                    window.print();
                },
                async showPaymentHistory(order) {
                    this.selectedOrder = order;

                    try {
                        const response = await api.get('?action=allOrdersPayments');
                        if (!response.data || !Array.isArray(response.data)) {
                            console.error('Données de paiement invalides:', response.data);
                            this.orderPayments = [];
                            return;
                        }

                        // Filter payments for this specific order
                        this.orderPayments = response.data.filter(payment => payment.order_id == order.id);
                        console.log('[v0] Filtered order payments:', this.orderPayments);
                    } catch (error) {
                        console.error('Erreur lors de la récupération des paiements:', error);
                        this.orderPayments = [];
                    }

                    this.showPaymentHistoryModal = true;
                },
                closePaymentHistoryModal() {
                    this.showPaymentHistoryModal = false;
                    this.orderPayments = [];
                },
                openNewPaymentModal() {
                    this.newPayment = {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        notes: '',
                        file: null
                    };
                    this.showNewPaymentModal = true;
                },
                closeNewPaymentModal() {
                    this.showNewPaymentModal = false;
                    this.newPayment = {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        notes: '',
                        file: null
                    };
                },
                handleFileUpload(event) {
                    this.newPayment.file = event.target.files[0] || null;
                },
                async addNewPayment() {
                    if (this.newPayment.amount > this.remainingBalance) {
                        alert('Le montant du paiement ne peut pas dépasser le solde restant');
                        return;
                    }

                    if (this.newPayment.amount <= 0) {
                        alert('Le montant doit être supérieur à 0');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('order_id', this.selectedOrder.id);
                    formData.append('amount', this.newPayment.amount);
                    formData.append('date_of_insertion', this.newPayment.date);
                    formData.append('notes', this.newPayment.notes);
                    if (this.newPayment.file) {
                        formData.append('file', this.newPayment.file);
                    }

                    try {
                        const response = await api.post('?action=newOrderPayment', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        console.log('[v0] Payment response:', response.data);

                        if (response.data.success || response.data.id) {
                            alert('Paiement ajouté avec succès!');
                            this.closeNewPaymentModal();
                            // Reload payment history
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur lors de l\'ajout du paiement: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'ajout du paiement:', error);
                        alert('Erreur lors de l\'ajout du paiement: ' + error.message);
                    }
                },
                getImgUrl(fileName) {
                    if (!fileName || fileName === '') return '';
                    return `${imgBaseUrl}${fileName}`;
                },

                editPayment(payment) {
                    this.editingPayment = {
                        id: payment.id,
                        amount: payment.amount,
                        originalAmount: payment.amount,
                        date: payment.date_of_insertion.split(' ')[0], // Extract date only
                        notes: payment.notes || '',
                        existingFile: payment.file || '',
                        file: null
                    };
                    this.showEditPaymentModal = true;
                },
                closeEditPaymentModal() {
                    this.showEditPaymentModal = false;
                    this.editingPayment = null;
                },
                handleEditFileUpload(event) {
                    this.editingPayment.file = event.target.files[0] || null;
                },
                async saveEditPayment() {
                    const maxAllowed = this.remainingBalance + parseFloat(this.editingPayment.originalAmount);

                    if (this.editingPayment.amount > maxAllowed) {
                        alert(`Le montant du paiement ne peut pas dépasser ${this.formatCurrency(maxAllowed, this.selectedOrder.currency)}`);
                        return;
                    }

                    if (this.editingPayment.amount <= 0) {
                        alert('Le montant doit être supérieur à 0');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('id', this.editingPayment.id);
                    formData.append('amount', this.editingPayment.amount);
                    formData.append('date_of_insertion', this.editingPayment.date);
                    formData.append('notes', this.editingPayment.notes);

                    if (this.editingPayment.file) {
                        formData.append('file', this.editingPayment.file);
                    }

                    try {
                        const response = await api.post('?action=updateOrderPayment', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        console.log('[v0] Edit payment response:', response.data);

                        if (response.data.success) {
                            alert('Paiement modifié avec succès!');
                            this.closeEditPaymentModal();
                            // Reload payment history
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur lors de la modification du paiement: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de la modification du paiement:', error);
                        alert('Erreur lors de la modification du paiement: ' + error.message);
                    }
                },
                async deletePayment(paymentId) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
                        return;
                    }

                    try {
                        const response = await api.post('?action=deleteOrderPayment', {
                            id: paymentId
                        });

                        console.log('[v0] Delete payment response:', response.data);

                        if (response.data.success) {
                            alert('Paiement supprimé avec succès!');
                            // Reload payment history
                            await this.showPaymentHistory(this.selectedOrder);
                        } else {
                            alert('Erreur lors de la suppression du paiement: ' + (response.data.error || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de la suppression du paiement:', error);
                        alert('Erreur lors de la suppression du paiement: ' + error.message);
                    }
                },
                // CHANGE: Added printPaymentHistory method
                printPaymentHistory() {
                    const printWindow = window.open('', '_blank');

                    const paymentsRows = this.orderPayments.map(payment => `
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">${this.formatDate(payment.date_of_insertion)}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold; color: #059669;">${this.formatCurrency(payment.amount, this.selectedOrder.currency)}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${payment.notes || '-'}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${payment.file && payment.file !== '' ? 'Oui' : 'Non'}</td>
                        </tr>
                    `).join('');

                    const htmlContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Historique des Paiements - ${this.selectedOrder.number}</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    margin: 20px;
                                }
                                h1 {
                                    color: #1f2937;
                                    border-bottom: 2px solid #2563EB;
                                    padding-bottom: 10px;
                                }
                                .info-section {
                                    margin: 20px 0;
                                    display: grid;
                                    grid-template-columns: repeat(3, 1fr);
                                    gap: 15px;
                                }
                                .info-box {
                                    border: 1px solid #ddd;
                                    padding: 15px;
                                    border-radius: 8px;
                                }
                                .info-box .label {
                                    font-size: 12px;
                                    color: #6b7280;
                                    margin-bottom: 5px;
                                }
                                .info-box .value {
                                    font-size: 18px;
                                    font-weight: bold;
                                }
                                .blue { color: #2563EB; }
                                .green { color: #059669; }
                                .red { color: #DC2626; }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-top: 20px;
                                }
                                th {
                                    background-color: #f3f4f6;
                                    border: 1px solid #ddd;
                                    padding: 12px;
                                    text-align: left;
                                    font-weight: bold;
                                    color: #374151;
                                }
                                td {
                                    border: 1px solid #ddd;
                                    padding: 8px;
                                }
                                tr:nth-child(even) {
                                    background-color: #f9fafb;
                                }
                                .footer {
                                    margin-top: 30px;
                                    text-align: center;
                                    font-size: 12px;
                                    color: #6b7280;
                                }
                            </style>
                        </head>
                        <body>
                            <h1>Historique des Paiements</h1>
                            <h2>Commande ${this.selectedOrder.number}</h2>
                            
                            <div class="info-section">
                                <div class="info-box">
                                    <div class="label">Montant total</div>
                                    <div class="value blue">${this.formatCurrency(this.selectedOrder.total, this.selectedOrder.currency)}</div>
                                </div>
                                <div class="info-box">
                                    <div class="label">Montant payé</div>
                                    <div class="value green">${this.formatCurrency(this.totalPaid, this.selectedOrder.currency)}</div>
                                </div>
                                <div class="info-box">
                                    <div class="label">Solde restant</div>
                                    <div class="value red">${this.formatCurrency(this.remainingBalance, this.selectedOrder.currency)}</div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Notes</th>
                                        <th>Justificatif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${paymentsRows}
                                </tbody>
                            </table>

                            <div class="footer">
                                <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                            </div>
                        </body>
                        </html>
                    `;

                    printWindow.document.write(htmlContent);
                    printWindow.document.close();
                    printWindow.focus();

                    setTimeout(() => {
                        printWindow.print();
                    }, 250);
                }

            },
            mounted() {
                this.loadOrders();
            }
        }).mount('#app');
    </script>
</body>

</html>