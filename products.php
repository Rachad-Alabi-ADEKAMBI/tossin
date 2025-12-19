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
    <title>Gestion des Produits - Gbemiro</title>
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

        .stock-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stock-low {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .stock-medium {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .stock-good {
            background-color: #D1FAE5;
            color: #065F46;
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
                                <i class="fas fa-box-open mr-2"></i>Gestion des Produits
                            </h1>
                            <div class="flex space-x-3">
                                <!-- Modified print button to open settings modal -->
                                <button @click="printProductsList" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer la liste
                                </button>
                                <button @click="openNewProductModal" class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nouveau produit
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <!-- Statistiques -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total produits</p>
                                <p class="text-2xl font-bold text-blue-600">{{ products.length }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Valeur du stock</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalStockValue) }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Stock faible</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ lowStockCount }}</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total quantité</p>
                                <p class="text-2xl font-bold text-purple-600">{{ formatNumber(totalQuantity) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Nom du produit, ID..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="name">Nom</option>
                                    <option value="quantity">Quantité</option>
                                    <option value="price">Prix</option>
                                    <option value="date">Date</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">État du stock</label>
                                <select v-model="stockFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous</option>
                                    <option value="low">Stock faible (&lt;20)</option>
                                    <option value="medium">Stock moyen (20-100)</option>
                                    <option value="good">Stock bon (&gt;100)</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button @click="applyFilters" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-filter mr-2"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des produits -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix d'achat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix de vente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in paginatedProducts" :key="product.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'ID'">
                                            #{{ product.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Produit'">
                                            <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Stock'">
                                            <span :class="['stock-badge', getStockClass(product.quantity)]">
                                                {{ formatNumber(product.quantity) }} unités
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Prix d\'achat'">
                                            {{ formatCurrency(product.buying_price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Prix de vente'">
                                            <div class="text-sm">
                                                <div class="font-medium text-gray-900">Gros: {{ formatCurrency(product.bulk_price) }}</div>
                                                <div class="text-xs text-gray-500">Demi-gros: {{ formatCurrency(product.semi_bulk_price) }}</div>
                                                <div class="text-xs text-gray-500">Détail: {{ formatCurrency(product.retail_price) }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <div class="flex space-x-2">
                                                <!-- Enlarged action icons -->
                                                <button @click="openStockModal(product, 'add')" class="text-green-600 hover:text-green-800 text-xl" title="Ajouter stock">
                                                    <i class="fas fa-plus-circle"></i>
                                                </button>
                                                <button @click="openStockModal(product, 'remove')" class="text-orange-600 hover:text-orange-800 text-xl" title="Retirer stock">
                                                    <i class="fas fa-minus-circle"></i>
                                                </button>
                                                <button @click="openEditModal(product)" class="text-blue-600 hover:text-blue-800 text-xl" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button @click="viewHistory(product)" class="text-purple-600 hover:text-purple-800 text-xl" title="Historique">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <!-- Added print button for each product -->
                                                <button @click="printProductDetails(product)" class="text-blue-600 hover:text-blue-800 text-xl" title="Imprimer">
                                                    <i class="fas fa-print"></i>
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

            <!-- Modal Nouveau/Éditer Produit -->
            <div v-if="showProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i :class="['fas', isEditMode ? 'fa-edit' : 'fa-plus', 'mr-2']"></i>
                                {{ isEditMode ? 'Modifier le produit' : 'Nouveau produit' }}
                            </h3>
                            <button @click="closeProductModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveProduct" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag mr-1"></i>Nom du produit *
                                </label>
                                <input v-model="productForm.name" type="text" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Disable quantity field in edit mode -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-boxes mr-1"></i>Quantité initiale *
                                    </label>
                                    <input v-model.number="productForm.quantity" type="number" min="0" required
                                        :disabled="isEditMode"
                                        :class="['w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent', isEditMode ? 'bg-gray-100 cursor-not-allowed' : '']">
                                    <p v-if="isEditMode" class="text-xs text-gray-500 mt-1">Utilisez les boutons +/- pour ajuster le stock</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-shopping-cart mr-1"></i>Prix d'achat *
                                    </label>
                                    <input v-model.number="productForm.buying_price" type="number" min="0" step="0.01" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-dollar-sign mr-1"></i>Prix de vente
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-2">Prix en gros *</label>
                                        <input v-model.number="productForm.bulk_price" type="number" min="0" step="0.01" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-2">Prix demi-gros *</label>
                                        <input v-model.number="productForm.semi_bulk_price" type="number" min="0" step="0.01" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-2">Prix détail *</label>
                                        <input v-model.number="productForm.retail_price" type="number" min="0" step="0.01" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" @click="closeProductModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" class="bg-primary hover:bg-secondary text-white px-6 py-2 rounded-lg">
                                    <i class="fas fa-save mr-2"></i>{{ isEditMode ? 'Mettre à jour' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Ajuster Stock -->
            <div v-if="showStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i :class="['fas', stockAction === 'add' ? 'fa-plus-circle text-green-600' : 'fa-minus-circle text-orange-600', 'mr-2']"></i>
                                {{ stockAction === 'add' ? 'Ajouter au stock' : 'Retirer du stock' }}
                            </h3>
                            <button @click="closeStockModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="adjustStock">
                            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600">Produit</p>
                                <p class="font-semibold text-gray-900">{{ selectedProduct?.name }}</p>
                                <p class="text-sm text-gray-600 mt-2">Stock actuel</p>
                                <p class="text-2xl font-bold text-blue-600">{{ formatNumber(selectedProduct?.quantity) }}</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-hashtag mr-1"></i>Quantité *
                                </label>
                                <input v-model.number="stockForm.quantity" type="number" min="1" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-comment mr-1"></i>Commentaire
                                </label>
                                <textarea v-model="stockForm.comment" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    placeholder="Raison de l'ajustement..."></textarea>
                            </div>

                            <div v-if="stockAction === 'add'" class="mb-4 p-3 bg-green-50 rounded-lg">
                                <p class="text-sm text-gray-600">Nouveau stock</p>
                                <p class="text-xl font-bold text-green-600">
                                    {{ formatNumber((selectedProduct?.quantity || 0) + (stockForm.quantity || 0)) }}
                                </p>
                            </div>

                            <div v-if="stockAction === 'remove'" class="mb-4 p-3 bg-orange-50 rounded-lg">
                                <p class="text-sm text-gray-600">Nouveau stock</p>
                                <p class="text-xl font-bold text-orange-600">
                                    {{ formatNumber(Math.max(0, (selectedProduct?.quantity || 0) - (stockForm.quantity || 0))) }}
                                </p>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="closeStockModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" :class="['text-white px-6 py-2 rounded-lg', stockAction === 'add' ? 'bg-green-500 hover:bg-green-600' : 'bg-orange-500 hover:bg-orange-600']">
                                    <i :class="['fas', stockAction === 'add' ? 'fa-check' : 'fa-minus', 'mr-2']"></i>
                                    Confirmer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Historique -->
            <div v-if="showHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Historique du produit
                            </h3>
                            <!-- Added print button in history modal -->
                            <div class="flex space-x-2">
                                <button @click="printProductHistory(selectedProduct)" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-600">Produit</p>
                            <p class="font-semibold text-gray-900 text-lg">{{ selectedProduct?.name }}</p>
                            <p class="text-sm text-gray-600 mt-2">Stock actuel</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatNumber(selectedProduct?.quantity) }} unités</p>
                        </div>

                        <div v-if="history.length === 0" class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>Aucun historique disponible</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="entry in history" :key="entry.id" class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span :class="['px-3 py-1 rounded-full text-xs font-semibold', entry.quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800']">
                                                <i :class="['fas', entry.quantity > 0 ? 'fa-plus' : 'fa-minus', 'mr-1']"></i>
                                                {{ entry.quantity > 0 ? '+' : '' }}{{ formatNumber(entry.quantity) }} unités
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                {{ formatDate(entry.created_at) }}
                                            </span>
                                        </div>
                                        <p v-if="entry.comment" class="text-sm text-gray-700">
                                            <i class="fas fa-comment-dots mr-1"></i>{{ entry.comment }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Added print settings modal -->
            <!-- Modal Paramètres d'impression -->
            <div v-if="showPrintSettingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-cog mr-2"></i>Paramètres d'impression
                            </h3>
                            <button @click="showPrintSettingsModal = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="showPrices" v-model="printSettings.showPrices" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="showPrices" class="ml-2 block text-sm text-gray-900">
                                    Afficher les prix
                                </label>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button @click="showPrintSettingsModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button @click="confirmPrint" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Zone d'impression cachée -->
            <div class="print-area hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Liste des Produits</h1>
                        <p class="text-gray-600">{{ new Date().toLocaleDateString('fr-FR') }}</p>
                    </div>

                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Produit</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Stock</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Prix achat</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Prix gros</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Prix demi-gros</th>
                                <th class="border border-gray-300 px-4 py-2 text-right">Prix détail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in filteredProducts" :key="product.id">
                                <td class="border border-gray-300 px-4 py-2">#{{ product.id }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ product.name }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatNumber(product.quantity) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(product.buying_price) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(product.bulk_price) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(product.semi_bulk_price) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ formatCurrency(product.retail_price) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-8 pt-4 border-t border-gray-300">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Total produits: {{ filteredProducts.length }}</p>
                                <p class="font-semibold">Total quantité: {{ formatNumber(totalQuantity) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Valeur totale du stock:</p>
                                <p class="text-xl font-bold">{{ formatCurrency(totalStockValue) }}</p>
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

        // Crée une instance Axios avec une baseURL
        const api = axios.create({
            baseURL: 'api/index.php'
        });


        createApp({
            data() {
                return {
                    products: [],
                    filteredProducts: [],
                    history: [],
                    selectedProduct: null,
                    showProductModal: false,
                    showStockModal: false,
                    showHistoryModal: false,
                    showPrintSettingsModal: false,
                    printSettings: {
                        showPrices: true
                    },
                    isEditMode: false,
                    stockAction: 'add',
                    searchTerm: '',
                    sortBy: 'name',
                    stockFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    productForm: {
                        id: null,
                        name: '',
                        quantity: 0,
                        buying_price: 0,
                        bulk_price: 0,
                        semi_bulk_price: 0,
                        retail_price: 0
                    },
                    stockForm: {
                        quantity: 0,
                        comment: ''
                    }
                }
            },
            computed: {
                paginatedProducts() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredProducts.slice(start, end);
                },
                totalPages() {
                    return Math.ceil(this.filteredProducts.length / this.itemsPerPage);
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
                    return Math.min(this.currentPage * this.itemsPerPage, this.filteredProducts.length);
                },
                totalItems() {
                    return this.filteredProducts.length;
                },
                totalStockValue() {
                    return this.products.reduce((sum, p) => sum + (p.quantity * p.buying_price), 0);
                },
                lowStockCount() {
                    return this.products.filter(p => p.quantity < 20).length;
                },
                totalQuantity() {
                    return this.products.reduce((sum, p) => sum + parseInt(p.quantity), 0);
                }
            },
            mounted() {
                this.loadProducts();
            },
            methods: {
                loadProducts() {
                    api.get('?action=allProducts')
                        .then(response => {
                            this.products = response.data;
                            this.applyFilters();
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des produits :', error);
                            this.products = [];
                        });
                },
                applyFilters() {
                    let filtered = [...this.products];

                    // Recherche
                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(p =>
                            p.name?.toLowerCase().includes(term) ||
                            p.id?.toString().includes(term)
                        );
                    }

                    // Filtre stock
                    if (this.stockFilter !== 'all') {
                        filtered = filtered.filter(p => {
                            const qty = parseInt(p.quantity);
                            if (this.stockFilter === 'low') return qty < 20;
                            if (this.stockFilter === 'medium') return qty >= 20 && qty <= 100;
                            if (this.stockFilter === 'good') return qty > 100;
                            return true;
                        });
                    }

                    // Tri
                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'name':
                                return (a.name || '').localeCompare(b.name || '');
                            case 'quantity':
                                return parseInt(b.quantity) - parseInt(a.quantity);
                            case 'price':
                                return parseFloat(b.retail_price) - parseFloat(a.retail_price);
                            case 'date':
                                return new Date(b.created_at) - new Date(a.created_at);
                            default:
                                return 0;
                        }
                    });

                    this.filteredProducts = filtered;
                    this.currentPage = 1;
                },
                openNewProductModal() {
                    this.isEditMode = false;
                    this.productForm = {
                        id: null,
                        name: '',
                        quantity: '',
                        buying_price: '',
                        bulk_price: '',
                        semi_bulk_price: '',
                        retail_price: ''
                    };
                    this.showProductModal = true;
                },
                openEditModal(product) {
                    this.isEditMode = true;
                    this.productForm = {
                        ...product
                    };
                    this.showProductModal = true;
                },
                closeProductModal() {
                    this.showProductModal = false;
                },
                saveProduct() {
                    const action = this.isEditMode ? 'update' : 'create';
                    const formData = new FormData();

                    formData.append('id', this.productForm.id);
                    formData.append('name', this.productForm.name);

                    // Only send quantity when creating, not when editing
                    if (!this.isEditMode) {
                        formData.append('quantity', this.productForm.quantity);
                    }

                    formData.append('buying_price', this.productForm.buying_price);
                    formData.append('bulk_price', this.productForm.bulk_price);
                    formData.append('semi_bulk_price', this.productForm.semi_bulk_price);
                    formData.append('retail_price', this.productForm.retail_price);

                    const route = `api/index.php?action=${action}`;

                    // Debug : afficher la route et les données envoyées
                    console.log('Route:', route);
                    console.log('Données envoyées :');
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }

                    api.post(route, formData)
                        .then(response => {
                            if (response.data.success) {
                                alert(this.isEditMode ?
                                    'Produit modifié avec succès' :
                                    'Produit créé avec succès'
                                );
                                this.closeProductModal();
                                this.loadProducts();
                            } else {
                                alert('Erreur: ' + (response.data.message || 'Erreur inconnue'));
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors de l’enregistrement du produit :', error);
                            alert('Erreur lors de l’enregistrement du produit');
                        });
                },

                openStockModal(product, action) {
                    this.selectedProduct = product;
                    this.stockAction = action;
                    this.stockForm = {
                        quantity: 0,
                        comment: ''
                    };
                    this.showStockModal = true;
                },
                closeStockModal() {
                    this.showStockModal = false;
                },
                async adjustStock() {
                    if (!this.stockForm.quantity || this.stockForm.quantity <= 0) {
                        alert('Veuillez entrer une quantité valide');
                        return;
                    }

                    const quantity = this.stockAction === 'add' ? this.stockForm.quantity : -this.stockForm.quantity;
                    const formData = new FormData();
                    formData.append('product_id', this.selectedProduct.id);
                    formData.append('quantity', quantity);
                    formData.append('comment', this.stockForm.comment);

                    const route = `api/index.php?action=adjust_stock`;

                    // Debug : afficher la route et les données envoyées
                    console.log('Route:', route);
                    console.log('Données envoyées :');
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }

                    try {
                        const response = await axios.post(route, formData);

                        if (response.data.success) {
                            alert('Stock ajusté avec succès');
                            this.closeStockModal();
                            this.loadProducts();
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'ajustement du stock :', error);
                        alert('Erreur lors de l\'ajustement du stock');
                    }
                },

                async viewHistory(product) {
                    this.selectedProduct = product;
                    this.showHistoryModal = true;

                    const route = `api/index.php?action=history&product_id=${product.id}`;

                    // Debug : afficher la route
                    console.log('Route:', route);

                    try {
                        const response = await axios.get(route);
                        this.history = response.data;

                        // Debug : afficher les données reçues
                        console.log('Historique reçu :', JSON.parse(JSON.stringify(this.history)));
                    } catch (error) {
                        console.error('Erreur lors du chargement de l\'historique :', error);
                        this.history = [];
                    }
                },

                closeHistoryModal() {
                    this.showHistoryModal = false;
                },
                async deleteProduct(id) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) return;

                    const route = `api/index.php?action=deleteProduct`;
                    const payload = {
                        id
                    };

                    // Debug
                    console.log('Route:', route);
                    console.log('Données envoyées :', payload);

                    try {
                        const response = await axios.post(route, payload, {
                            headers: {
                                'Content-Type': 'application/json' // très important pour que php://input contienne du JSON
                            }
                        });

                        if (response.data.success) {
                            alert('Produit supprimé avec succès');
                            this.loadProducts();
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Erreur inconnue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de la suppression du produit :', error);
                        alert('Erreur lors de la suppression du produit');
                    }
                },

                printProductsList() {
                    this.showPrintSettingsModal = true;
                },

                confirmPrint() {
                    this.showPrintSettingsModal = false;
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Liste des Produits</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 12px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, téléphone 0149916566</p>
                                <h2>LISTE DES PRODUITS</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Nombre total de produits:</strong> ${this.products.length}</p>
                                ${this.printSettings.showPrices ? `<p><strong>Valeur totale du stock:</strong> ${this.formatCurrency(this.totalStockValue)}</p>` : ''}
                                <p><strong>Quantité totale:</strong> ${this.formatNumber(this.totalQuantity)} unités</p>
                                <p><strong>Produits en stock faible:</strong> ${this.lowStockCount}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Produit</th>
                                        <th>Stock</th>
                                        ${this.printSettings.showPrices ? `
                                        <th>Prix d'achat</th>
                                        <th>Prix gros</th>
                                        <th>Prix demi-gros</th>
                                        <th>Prix détail</th>
                                        ` : ''}
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredProducts.forEach(product => {
                        printContent += `
                            <tr>
                                <td>#${product.id}</td>
                                <td>${product.name}</td>
                                <td>${this.formatNumber(product.quantity)} unités</td>
                                ${this.printSettings.showPrices ? `
                                <td>${this.formatCurrency(product.buying_price)}</td>
                                <td>${this.formatCurrency(product.bulk_price)}</td>
                                <td>${this.formatCurrency(product.semi_bulk_price)}</td>
                                <td>${this.formatCurrency(product.retail_price)}</td>
                                ` : ''}
                            </tr>`;
                    });

                    printContent += `
                                </tbody>
                            </table>
                            
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

                printProductDetails(product) {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Fiche Produit #${product.id}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 40px; }
                                .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2563EB; padding-bottom: 20px; }
                                .details { margin: 30px 0; }
                                .detail-row { display: grid; grid-template-columns: 200px 1fr; margin: 15px 0; padding: 10px; background-color: #f9fafb; border-radius: 8px; }
                                .label { font-weight: bold; color: #374151; }
                                .value { color: #1F2937; }
                                .price-section { margin-top: 30px; padding: 20px; background-color: #f0f9ff; border: 2px solid #2563EB; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, téléphone 0149916566</p>
                                <h2>FICHE PRODUIT #${product.id}</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="details">
                                <div class="detail-row">
                                    <div class="label">Nom du produit:</div>
                                    <div class="value">${product.name}</div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="label">Stock actuel:</div>
                                    <div class="value" style="font-size: 20px; font-weight: bold; color: #2563EB;">${this.formatNumber(product.quantity)} unités</div>
                                </div>
                                
                                <div class="detail-row">
                                    <div class="label">Prix d'achat:</div>
                                    <div class="value">${this.formatCurrency(product.buying_price)}</div>
                                </div>
                            </div>
                            
                            <div class="price-section">
                                <h3 style="margin-top: 0;">Prix de vente</h3>
                                <div class="detail-row">
                                    <div class="label">Prix en gros:</div>
                                    <div class="value" style="font-size: 18px; font-weight: bold;">${this.formatCurrency(product.bulk_price)}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="label">Prix demi-gros:</div>
                                    <div class="value" style="font-size: 18px; font-weight: bold;">${this.formatCurrency(product.semi_bulk_price)}</div>
                                </div>
                                <div class="detail-row">
                                    <div class="label">Prix détail:</div>
                                    <div class="value" style="font-size: 18px; font-weight: bold;">${this.formatCurrency(product.retail_price)}</div>
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

                printProductHistory(product) {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let historyRows = '';
                    this.history.forEach(entry => {
                        historyRows += `
                            <tr>
                                <td>${this.formatDate(entry.created_at)}</td>
                                <td style="text-align: center; color: ${entry.quantity > 0 ? '#059669' : '#F59E0B'}; font-weight: bold;">
                                    ${entry.quantity > 0 ? '+' : ''}${this.formatNumber(entry.quantity)}
                                </td>
                                <td>${entry.comment || '-'}</td>
                            </tr>`;
                    });

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Historique Produit #${product.id}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 40px; }
                                .header { text-align: center; margin-bottom: 40px; border-bottom: 3px solid #2563EB; padding-bottom: 20px; }
                                .product-info { margin: 20px 0; padding: 15px; background-color: #f0f9ff; border-radius: 8px; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                                th { background-color: #2563EB; color: white; }
                                tr:nth-child(even) { background-color: #f9fafb; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, téléphone 0149916566</p>
                                <h2>HISTORIQUE DU PRODUIT</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="product-info">
                                <h3 style="margin-top: 0;">Informations produit</h3>
                                <p><strong>Nom:</strong> ${product.name}</p>
                                <p><strong>ID:</strong> #${product.id}</p>
                                <p><strong>Stock actuel:</strong> <span style="font-size: 20px; color: #2563EB; font-weight: bold;">${this.formatNumber(product.quantity)} unités</span></p>
                            </div>
                            
                            <h3>Historique des mouvements</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th style="text-align: center;">Quantité</th>
                                        <th>Commentaire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${historyRows}
                                </tbody>
                            </table>
                            
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

                getStockClass(quantity) {
                    const qty = parseInt(quantity);
                    if (qty < 20) return 'stock-low';
                    if (qty <= 100) return 'stock-medium';
                    return 'stock-good';
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat('fr-FR', {
                        style: 'currency',
                        currency: 'XOF',
                        minimumFractionDigits: 0
                    }).format(value || 0);
                },
                formatNumber(value) {
                    return new Intl.NumberFormat('fr-FR').format(value || 0);
                },
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    return new Date(dateString).toLocaleDateString('fr-FR', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                previousPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                goToPage(page) {
                    this.currentPage = page;
                }
            }
        }).mount('#app');
    </script>
</body>

</html>