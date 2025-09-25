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

            /* Adding horizontal table layout for print to save space */
            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000 !important;
                padding: 8px !important;
                text-align: left !important;
                font-size: 12px !important;
            }

            .print-table th {
                background-color: #f0f0f0 !important;
                font-weight: bold !important;
            }

            .print-header {
                margin-bottom: 20px !important;
            }

            .print-summary {
                margin-top: 20px !important;
                border-top: 2px solid #000 !important;
                padding-top: 10px !important;
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
                                    <option value="en-attente">En attente</option>
                                    <option value="en-cours">En cours</option>
                                    <option value="livree">Livrée</option>
                                    <option value="annulee">Annulée</option>
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
                                            {{ formatCurrency(order.total) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-label="Statut">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusInfo(order.status).class]">
                                                {{ getStatusInfo(order.status).label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                        <i class="fas fa-info-circle mr-1"></i>Statut
                                    </label>
                                    <select v-model="newOrder.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="en-attente">En attente</option>
                                        <option value="en-cours">En cours</option>
                                        <option value="livree">Livrée</option>
                                        <option value="annulee">Annulée</option>
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
                                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                                            </label>
                                            <input v-model.number="line.quantity" type="number" min="1" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire (XOF)
                                            </label>
                                            <input v-model.number="line.price" type="number" step="100" min="0" required
                                                @input="updateLineTotal(index)"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                <i class="fas fa-calculator mr-1"></i>Total
                                            </label>
                                            <input :value="formatCurrency(line.total)" type="text" readonly
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-sm bg-gray-100">
                                        </div>
                                        <div class="flex items-end space-x-2">
                                            <button type="button" @click="editOrderLineInModal(index)"
                                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-2 py-2 rounded text-sm transition-colors" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
                                        <span class="text-2xl font-bold text-primary">{{ formatCurrency(orderTotal) }}</span>
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
                                    <i class="fas fa-money-bill-wave mr-1"></i>Prix unitaire (XOF)
                                </label>
                                <input v-model.number="editingLine.price" type="number" required min="0" step="100"
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
                                    <option value="en-attente">En attente</option>
                                    <option value="en-cours">En cours</option>
                                    <option value="livree">Livrée</option>
                                    <option value="annulee">Annulée</option>
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
                                <!-- Adding print button -->
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
                                    <!-- Adding no-print class to hide action buttons during printing -->
                                    <div class="flex space-x-2 no-print">
                                        <button @click="addNewProductLine" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-plus mr-1"></i>Ajouter ligne
                                        </button>
                                        <button @click="editOrderStatus(selectedOrder)" class="bg-accent hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Modifier statut
                                        </button>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <!-- Adding print-table class for better print formatting -->
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg responsive-table print-table">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                                <!-- Adding no-print class to hide Actions column during printing -->
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="(line, index) in selectedOrder.lines" :key="index" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900" data-label="Produit">
                                                    <input v-if="editingDetailLineIndex === index"
                                                        v-model="editingDetailLine.product"
                                                        type="text"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary focus:border-transparent no-print"
                                                        @keyup.enter="saveDetailLineEdit(index)"
                                                        @keyup.escape="cancelDetailLineEdit()">
                                                    <span v-else>{{ line.product }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600" data-label="Quantité">
                                                    <input v-if="editingDetailLineIndex === index"
                                                        v-model.number="editingDetailLine.quantity"
                                                        type="number"
                                                        min="1"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary focus:border-transparent no-print"
                                                        @keyup.enter="saveDetailLineEdit(index)"
                                                        @keyup.escape="cancelDetailLineEdit()">
                                                    <span v-else>{{ line.quantity }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600" data-label="Prix unitaire">
                                                    <input v-if="editingDetailLineIndex === index"
                                                        v-model.number="editingDetailLine.price"
                                                        type="number"
                                                        min="0"
                                                        step="100"
                                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-primary focus:border-transparent no-print"
                                                        @keyup.enter="saveDetailLineEdit(index)"
                                                        @keyup.escape="cancelDetailLineEdit()">
                                                    <span v-else>{{ formatCurrency(line.price) }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900" data-label="Total">
                                                    <span v-if="editingDetailLineIndex === index">
                                                        {{ formatCurrency(editingDetailLine.quantity * editingDetailLine.price) }}
                                                    </span>
                                                    <span v-else>{{ formatCurrency(line.total) }}</span>
                                                </td>
                                                <!-- Adding no-print class to hide action buttons during printing -->
                                                <td class="px-4 py-3 text-sm font-medium no-print" data-label="Actions">
                                                    <div v-if="editingDetailLineIndex === index" class="flex space-x-2">
                                                        <button @click="saveDetailLineEdit(index)"
                                                            class="text-green-600 hover:text-green-800"
                                                            title="Valider">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button @click="cancelDetailLineEdit()"
                                                            class="text-red-600 hover:text-red-800"
                                                            title="Annuler">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div v-else class="flex space-x-2">
                                                        <button @click="startDetailLineEdit(index)"
                                                            class="text-blue-600 hover:text-blue-800"
                                                            title="Modifier">
                                                            <i class="fas fa-pen"></i>
                                                        </button>
                                                        <button @click="deleteProductLine(index)"
                                                            class="text-red-600 hover:text-red-800"
                                                            title="Supprimer le produit">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Adding print-summary class for better print formatting -->
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg print-summary">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-semibold text-gray-900">Total de la commande:</span>
                                        <span class="text-2xl font-bold text-primary">{{ formatCurrency(selectedOrderTotal) }}</span>
                                    </div>
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

        createApp({
            data() {
                return {
                    searchTerm: '',
                    statusFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showNewOrderModal: false,
                    showEditLineModal: false,
                    showEditStatusModal: false,
                    showOrderDetailsModal: false,
                    selectedOrder: null,
                    editingLineIndex: -1,
                    editingOrder: null,
                    editingDetailLineIndex: -1,
                    editingDetailLine: {
                        product: '',
                        quantity: 0,
                        price: 0
                    },
                    loading: false,

                    orders: [],
                    allProducts: [],

                    newOrder: {
                        supplier: '',
                        date: new Date().toISOString().split('T')[0],
                        status: 'en-attente',
                        lines: []
                    },

                    editingLine: {
                        product: '',
                        quantity: 0,
                        price: 0
                    }
                };
            },

            async mounted() {
                // Fermer la sidebar sur mobile
                document.addEventListener('click', (e) => {
                    if (window.innerWidth < 1024 && !e.target.closest('#sidebar') && !e.target.closest('button')) {
                        this.sidebarOpen = false;
                    }
                });

                await this.loadOrders();
                await this.loadProducts();
            },

            computed: {
                filteredOrders() {
                    return this.orders.filter(order => {
                        const matchesSearch = order.number.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            order.supplier.toLowerCase().includes(this.searchTerm.toLowerCase());
                        const matchesStatus = this.statusFilter === 'all' || order.status.toLowerCase().replace(' ', '-') === this.statusFilter;

                        return matchesSearch && matchesStatus;
                    });
                },

                totalItems() {
                    return this.filteredOrders.length;
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

                paginatedOrders() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredOrders.slice(start, start + this.itemsPerPage);
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

                orderTotal() {
                    return this.newOrder.lines.reduce((sum, line) => sum + (line.total || 0), 0);
                },

                selectedOrderTotal() {
                    if (!this.selectedOrder || !this.selectedOrder.lines) return 0;
                    return this.selectedOrder.lines.reduce((sum, line) => sum + (line.total || 0), 0);
                }
            },

            methods: {
                async loadOrders() {
                    try {
                        this.loading = true;
                        const response = await axios.get('http://127.0.0.1/tossin/api/index.php?action=allOrders');
                        this.orders = response.data.map(order => ({
                            ...order,
                            number: `CMD-${String(order.id).padStart(3, '0')}`,
                            date: order.date_of_insertion,
                            supplier: order.seller,
                            status: order.status.toLowerCase().replace(' ', '-'), // Ensure consistent status format
                            total: parseFloat(order.total),
                            lines: [] // Will be populated after loading products
                        }));

                        // Load products for each order
                        for (let order of this.orders) {
                            order.lines = this.allProducts.filter(product => product.order_id === order.id)
                                .map(product => ({
                                    id: product.id,
                                    product: product.name,
                                    quantity: product.quantity,
                                    price: parseFloat(product.price),
                                    total: product.quantity * parseFloat(product.price)
                                }));
                        }
                    } catch (error) {
                        console.error('Erreur lors du chargement des commandes:', error);
                        alert('Erreur lors du chargement des commandes');
                    } finally {
                        this.loading = false;
                    }
                },

                async loadProducts() {
                    try {
                        const response = await axios.get('http://127.0.0.1/tossin/api/index.php?action=allProducts');
                        this.allProducts = response.data;
                        // After loading products, update the lines for existing orders
                        this.orders.forEach(order => {
                            order.lines = this.allProducts.filter(product => product.order_id === order.id)
                                .map(product => ({
                                    id: product.id,
                                    product: product.name,
                                    quantity: product.quantity,
                                    price: parseFloat(product.price),
                                    total: product.quantity * parseFloat(product.price)
                                }));
                        });
                    } catch (error) {
                        console.error('Erreur lors du chargement des produits:', error);
                    }
                },

                async createNewOrder() {
                    try {
                        this.loading = true;
                        const orderData = {
                            seller: this.newOrder.supplier,
                            total: this.orderTotal,
                            status: this.newOrder.status,
                            lines: this.newOrder.lines.map(line => ({
                                product: line.product,
                                quantity: line.quantity,
                                price: line.price
                            }))
                        };

                        const response = await axios.post('http://127.0.0.1/tossin/api/index.php?action=newOrder', orderData);

                        if (response.data.success) {
                            await this.loadOrders();
                            await this.loadProducts(); // Reload products to ensure new lines are associated
                            this.closeNewOrderModal();
                            alert('Commande créée avec succès !');
                        } else {
                            alert('Erreur lors de la création de la commande: ' + (response.data.message || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de la création de la commande:', error);
                        alert('Erreur lors de la création de la commande');
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteProductFromBackend(productId) {
                    try {
                        const response = await axios.delete(`http://127.0.0.1/tossin/api/index.php?action=deleteProduct&id=${productId}`);

                        if (response.data.success) {
                            // Reload data to reflect changes
                            await this.loadOrders();
                            await this.loadProducts();
                        } else {
                            alert('Erreur lors de la suppression du produit');
                        }
                    } catch (error) {
                        console.error('Erreur lors de la suppression du produit:', error);
                        alert('Erreur lors de la suppression du produit');
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    return new Date(dateString).toLocaleDateString('fr-FR');
                },

                formatCurrency(amount) {
                    if (typeof amount !== 'number') return '0 XOF';
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'XOF',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount);
                },

                getStatusInfo(status) {
                    const statusMap = {
                        'en-attente': {
                            label: 'En attente',
                            class: 'text-yellow-600 bg-yellow-100'
                        },
                        'en-cours': {
                            label: 'En cours',
                            class: 'text-blue-600 bg-blue-100'
                        },
                        'livree': {
                            label: 'Livrée',
                            class: 'text-green-600 bg-green-100'
                        },
                        'annulee': {
                            label: 'Annulée',
                            class: 'text-red-600 bg-red-100'
                        }
                    };
                    return statusMap[status] || {
                        label: status,
                        class: 'text-gray-600 bg-gray-100'
                    };
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

                openNewOrderModal() {
                    this.showNewOrderModal = true;
                    this.newOrder.date = new Date().toISOString().split('T')[0];
                    this.newOrder.lines = [];
                    this.addOrderLine();
                },

                closeNewOrderModal() {
                    this.showNewOrderModal = false;
                    this.newOrder = {
                        supplier: '',
                        date: new Date().toISOString().split('T')[0],
                        status: 'en-attente',
                        lines: []
                    };
                },

                addOrderLine() {
                    this.newOrder.lines.push({
                        product: '',
                        quantity: 1,
                        price: 0,
                        total: 0
                    });
                },

                removeOrderLine(index) {
                    this.newOrder.lines.splice(index, 1);
                },

                updateLineTotal(index) {
                    const line = this.newOrder.lines[index];
                    line.total = (line.quantity || 0) * (line.price || 0);
                },

                editOrderLineInModal(index) {
                    this.editingLineIndex = index;
                    const line = this.newOrder.lines[index];
                    this.editingLine = {
                        ...line
                    };
                    this.showEditLineModal = true;
                },

                closeEditLineModal() {
                    this.showEditLineModal = false;
                    this.editingLineIndex = -1;
                },

                saveEditLine() {
                    if (this.editingLineIndex >= 0) {
                        const line = this.editingLine;
                        line.total = line.quantity * line.price;
                        this.newOrder.lines[this.editingLineIndex] = {
                            ...line
                        };
                        this.closeEditLineModal();
                    }
                },

                startDetailLineEdit(index) {
                    this.editingDetailLineIndex = index;
                    const line = this.selectedOrder.lines[index];
                    this.editingDetailLine = {
                        product: line.product,
                        quantity: line.quantity,
                        price: line.price
                    };
                },

                async saveDetailLineEdit(index) {
                    if (this.editingDetailLineIndex === index) {
                        const line = this.selectedOrder.lines[index];
                        const updatedData = {
                            product: this.editingDetailLine.product,
                            quantity: this.editingDetailLine.quantity,
                            price: this.editingDetailLine.price
                        };

                        // If the line has an ID, it's an existing product - update it
                        if (line.id) {
                            try {
                                const response = await axios.put(`http://127.0.0.1/tossin/api/index.php?action=updateProduct&id=${line.id}`, updatedData);

                                if (response.data.success) {
                                    // Update the line in selectedOrder
                                    line.product = this.editingDetailLine.product;
                                    line.quantity = this.editingDetailLine.quantity;
                                    line.price = this.editingDetailLine.price;
                                    line.total = this.editingDetailLine.quantity * this.editingDetailLine.price;

                                    // Update the line in the main orders array
                                    const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                                    if (orderIndex >= 0) {
                                        this.orders[orderIndex].lines[index] = {
                                            ...line
                                        };
                                        // Update the order total
                                        this.orders[orderIndex].total = this.orders[orderIndex].lines.reduce((sum, l) => sum + l.total, 0);
                                        this.selectedOrder.total = this.orders[orderIndex].total;
                                    }
                                } else {
                                    alert('Erreur lors de la mise à jour du produit');
                                    return;
                                }
                            } catch (error) {
                                console.error('Erreur lors de la mise à jour du produit:', error);
                                alert('Erreur lors de la mise à jour du produit');
                                return;
                            }
                        } else {
                            try {
                                const newProductData = {
                                    order_id: this.selectedOrder.id,
                                    name: this.editingDetailLine.product,
                                    quantity: this.editingDetailLine.quantity,
                                    price: this.editingDetailLine.price
                                };

                                const response = await axios.post('http://127.0.0.1/tossin/api/index.php?action=newProduct', newProductData);

                                if (response.data.success) {
                                    // Update the line with the new ID from backend
                                    line.id = response.data.product_id;
                                    line.product = this.editingDetailLine.product;
                                    line.quantity = this.editingDetailLine.quantity;
                                    line.price = this.editingDetailLine.price;
                                    line.total = this.editingDetailLine.quantity * this.editingDetailLine.price;

                                    // Update the line in the main orders array
                                    const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                                    if (orderIndex >= 0) {
                                        this.orders[orderIndex].lines[index] = {
                                            ...line
                                        };
                                        // Update the order total
                                        this.orders[orderIndex].total = this.orders[orderIndex].lines.reduce((sum, l) => sum + l.total, 0);
                                        this.selectedOrder.total = this.orders[orderIndex].total;
                                    }
                                } else {
                                    alert('Erreur lors de la création du produit');
                                    return;
                                }
                            } catch (error) {
                                console.error('Erreur lors de la création du produit:', error);
                                alert('Erreur lors de la création du produit');
                                return;
                            }
                        }

                        this.cancelDetailLineEdit();
                    }
                },

                async addNewOrder() {
                    if (this.newOrder.lines.length === 0 || this.newOrder.lines.some(line => !line.product || line.quantity <= 0 || line.price <= 0)) {
                        alert('Veuillez ajouter au moins une ligne de commande valide');
                        return;
                    }

                    await this.createNewOrder();
                },

                showOrderDetails(order) {
                    this.selectedOrder = {
                        ...order
                    };
                    // Ensure lines are loaded correctly if they were not initially
                    if (this.selectedOrder.lines.length === 0) {
                        this.selectedOrder.lines = this.allProducts.filter(product => product.order_id === this.selectedOrder.id)
                            .map(product => ({
                                id: product.id,
                                product: product.name,
                                quantity: product.quantity,
                                price: parseFloat(product.price),
                                total: product.quantity * parseFloat(product.price)
                            }));
                    }
                    this.showOrderDetailsModal = true;
                },

                closeOrderDetailsModal() {
                    this.showOrderDetailsModal = false;
                    this.selectedOrder = null;
                    this.cancelDetailLineEdit();
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

                saveEditStatus() {
                    if (this.editingOrder) {
                        const order = this.orders.find(o => o.id === this.editingOrder.id);
                        if (order) {
                            order.status = this.editingOrder.status;
                        }
                        this.closeEditStatusModal();
                        if (this.selectedOrder && this.selectedOrder.id === this.editingOrder.id) {
                            this.selectedOrder.status = this.editingOrder.status;
                        }
                    }
                },

                deleteOrder(orderId) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
                        axios.delete(`http://127.0.0.1/tossin/api/index.php?action=deleteOrder&id=${orderId}`)
                            .then(response => {
                                if (response.data.success) {
                                    this.orders = this.orders.filter(o => o.id !== orderId);
                                    this.applyFilters();
                                    alert('Commande supprimée avec succès !');
                                } else {
                                    alert('Erreur lors de la suppression de la commande');
                                }
                            })
                            .catch(error => {
                                console.error('Erreur lors de la suppression de la commande:', error);
                                alert('Erreur lors de la suppression de la commande');
                            });
                    }
                },

                async deleteProductLine(index) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit de la commande ?')) {
                        const productId = this.selectedOrder.lines[index].id;

                        if (productId) {
                            // Delete from backend
                            await this.deleteProductFromBackend(productId);

                            // Update local data
                            this.selectedOrder.lines.splice(index, 1);

                            // Update the order in the main orders array
                            const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (orderIndex >= 0) {
                                this.orders[orderIndex].lines.splice(index, 1);
                                // Update the order total
                                this.orders[orderIndex].total = this.orders[orderIndex].lines.reduce((sum, line) => sum + line.total, 0);
                                this.selectedOrder.total = this.orders[orderIndex].total;
                            }
                        } else {
                            // For new lines without ID, just remove locally
                            this.selectedOrder.lines.splice(index, 1);

                            const orderIndex = this.orders.findIndex(o => o.id === this.selectedOrder.id);
                            if (orderIndex >= 0) {
                                this.orders[orderIndex].lines.splice(index, 1);
                                this.orders[orderIndex].total = this.orders[orderIndex].lines.reduce((sum, line) => sum + line.total, 0);
                                this.selectedOrder.total = this.orders[orderIndex].total;
                            }
                        }

                        // Cancel any ongoing edit
                        this.cancelDetailLineEdit();
                    }
                },
                cancelDetailLineEdit() {
                    this.editingDetailLineIndex = -1;
                    this.editingDetailLine = {
                        product: '',
                        quantity: 0,
                        price: 0
                    };
                },

                addNewProductLine() {
                    if (this.selectedOrder) {
                        const newLine = {
                            product: '',
                            quantity: 1,
                            price: 0,
                            total: 0
                        };

                        // Only add to selectedOrder.lines, not to the main orders array yet
                        this.selectedOrder.lines.push(newLine);

                        // Start editing the new line immediately
                        const newIndex = this.selectedOrder.lines.length - 1;
                        this.startDetailLineEdit(newIndex);
                    }
                },

                printOrderDetails() {
                    // Cancel any ongoing edit before printing
                    this.cancelDetailLineEdit();

                    // Small delay to ensure the edit is cancelled
                    setTimeout(() => {
                        window.print();
                    }, 100);
                },

                logout() {
                    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                        localStorage.removeItem('tossin_user');
                        window.location.href = 'login.html';
                    }
                }
            }
        }).mount('#app');
    </script>
</body>

</html>