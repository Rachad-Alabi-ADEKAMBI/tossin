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
    <title>Gbemiro Gestion des Dépenses</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        /* Updating color scheme to match claims page (blue theme) */
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
                            <h1 class="text-2xl font-bold text-gray-900">Gestion des Dépenses</h1>
                            <div class="flex space-x-3">
                                <button @click="printExpensesList" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <!-- Changed button color to match claims accent color (green) -->
                                <button @click="openNewExpenseModal" class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle dépense
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <!-- Updated filters section: removed currency, added category and date filters -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Nom de la dépense..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                                <select v-model="categoryFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Toutes</option>
                                    <option value="Loyers">Loyers</option>
                                    <option value="Frais de transport et manutention">Frais de transport et manutention</option>
                                    <option value="Salaires et charges du personnel">Salaires et charges du personnel</option>
                                    <option value="Services extérieurs et prestations">Services extérieurs et prestations (comptabilité, sécurité, maintenance, sous-traitance)</option>
                                    <option value="Eau, électricité et internet">Eau, électricité et internet</option>
                                    <option value="Matériel et fournitures">Matériel et fournitures</option>
                                    <option value="Carburant et entretien des véhicules">Carburant et entretien des véhicules</option>
                                    <option value="Marketing et communication">Marketing et communication</option>
                                    <option value="Taxes, impôts et redevances">Taxes, impôts et redevances</option>
                                    <option value="Autres dépenses">Autres dépenses (imprévus, pénalités, frais divers non classés)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="date">Date</option>
                                    <option value="amount">Montant</option>
                                    <option value="name">Nom</option>
                                    <option value="category">Catégorie</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date précise</label>
                                <input v-model="specificDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                                <input v-model="startDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                                <input v-model="endDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-4">
                            <button @click="resetFilters" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-redo mr-2"></i>Réinitialiser
                            </button>
                            <button @click="applyFilters" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-filter mr-2"></i>Filtrer
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total des dépenses</p>
                                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalExpenses) }} XOF</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Nombre de dépenses</p>
                                <p class="text-2xl font-bold text-blue-600">{{ filteredExpenses.length }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Dépense moyenne</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(averageExpense) }} XOF</p>
                            </div>
                        </div>
                    </div>

                    <!-- Added category column to the table -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="expense in paginatedExpenses" :key="expense.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Nom'">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-red-500 flex items-center justify-center">
                                                        <i class="fas fa-receipt text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ expense.name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500" :data-label="'Catégorie'">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getCategoryColor(expense.category)">
                                                {{ getCategoryShortName(expense.category) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date'">
                                            {{ formatDate(expense.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600" :data-label="'Montant'">
                                            {{ formatCurrency(expense.amount) }} XOF
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500" :data-label="'Description'">
                                            {{ expense.notes || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <button @click="editExpense(expense)" class="text-blue-600 hover:text-blue-800 mr-3" title="Modifier">
                                                <i class="fas fa-edit fa-lg"></i>
                                            </button>
                                            <button @click="deleteExpense(expense.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
                                                <i class="fas fa-trash fa-lg"></i>
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

            <!-- Updated modal to use category instead of currency -->
            <div v-if="showExpenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-receipt mr-2"></i>{{ editingExpense ? 'Modifier la dépense' : 'Nouvelle Dépense' }}
                            </h3>
                            <button @click="closeExpenseModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveExpense" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-tag mr-1"></i>Nom de la dépense
                                    </label>
                                    <input v-model="expenseForm.name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-list mr-1"></i>Catégorie
                                    </label>
                                    <select v-model="expenseForm.category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Sélectionner une catégorie</option>
                                        <option value="Loyers">Loyers</option>
                                        <option value="Frais de transport et manutention">Frais de transport et manutention</option>
                                        <option value="Salaires et charges du personnel">Salaires et charges du personnel</option>
                                        <option value="Services extérieurs et prestations">Services extérieurs et prestations</option>
                                        <option value="Eau, électricité et internet">Eau, électricité et internet</option>
                                        <option value="Matériel et fournitures">Matériel et fournitures</option>
                                        <option value="Carburant et entretien des véhicules">Carburant et entretien des véhicules</option>
                                        <option value="Marketing et communication">Marketing et communication</option>
                                        <option value="Taxes, impôts et redevances">Taxes, impôts et redevances</option>
                                        <option value="Autres dépenses">Autres dépenses</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date
                                    </label>
                                    <input v-model="expenseForm.date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Montant (XOF)
                                    </label>
                                    <input v-model.number="expenseForm.amount" type="number" step="0.01" required min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Description (optionnel)
                                </label>
                                <textarea v-model="expenseForm.description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit"
                                    class="flex-1 bg-accent hover:bg-green-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                                <button type="button" @click="closeExpenseModal"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
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

        const api = axios.create({
            baseURL: 'api/index.php'
        });

        api.interceptors.request.use(request => {
            console.log('[v0] API Request:', request.method.toUpperCase(), request.baseURL + request.url, request.data || '');
            return request;
        });

        api.interceptors.response.use(response => {
            console.log('[v0] API Response:', response.config.url, response.data);
            return response;
        });

        createApp({
            data() {
                return {
                    searchTerm: '',
                    sortBy: 'date',
                    categoryFilter: 'all',
                    specificDate: '',
                    startDate: '',
                    endDate: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showExpenseModal: false,
                    editingExpense: null,
                    expenses: [],
                    expenseForm: {
                        name: '',
                        amount: '',
                        category: '',
                        date: new Date().toISOString().split('T')[0],
                        description: ''
                    }
                };
            },

            mounted() {
                this.fetchExpenses();
            },

            computed: {
                filteredExpenses() {
                    let filtered = this.expenses.filter(expense => {
                        const matchesSearch =
                            expense.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            (expense.notes && expense.notes.toLowerCase().includes(this.searchTerm.toLowerCase())) ||
                            (expense.category && expense.category.toLowerCase().includes(this.searchTerm.toLowerCase()));

                        const matchesCategory = this.categoryFilter === 'all' || expense.category === this.categoryFilter;

                        let matchesDate = true;
                        const expenseDate = new Date(expense.created_at).toISOString().split('T')[0];

                        if (this.specificDate) {
                            matchesDate = expenseDate === this.specificDate;
                        } else if (this.startDate && this.endDate) {
                            matchesDate = expenseDate >= this.startDate && expenseDate <= this.endDate;
                        } else if (this.startDate) {
                            matchesDate = expenseDate >= this.startDate;
                        } else if (this.endDate) {
                            matchesDate = expenseDate <= this.endDate;
                        }

                        return matchesSearch && matchesCategory && matchesDate;
                    });

                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'name':
                                return a.name.localeCompare(b.name);
                            case 'amount':
                                return parseFloat(b.amount) - parseFloat(a.amount);
                            case 'date':
                                return new Date(b.created_at) - new Date(a.created_at);
                            case 'category':
                                return (a.category || '').localeCompare(b.category || '');
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },

                totalItems() {
                    return this.filteredExpenses.length;
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

                paginatedExpenses() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredExpenses.slice(start, start + this.itemsPerPage);
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

                totalExpenses() {
                    return this.filteredExpenses.reduce((sum, expense) => sum + parseFloat(expense.amount), 0);
                },

                averageExpense() {
                    return this.filteredExpenses.length > 0 ? this.totalExpenses / this.filteredExpenses.length : 0;
                }
            },

            methods: {
                fetchExpenses() {
                    console.log('[v0] Fetching expenses from route: ?action=allExpenses');
                    api.get('?action=allExpenses')
                        .then(response => {
                            this.expenses = response.data;
                            console.log('[v0] Expenses loaded:', this.expenses.length, 'items');
                        })
                        .catch(error => {
                            console.error('[v0] Error fetching expenses:', error);
                        });
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR');
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);
                },

                getCategoryColor(category) {
                    const colors = {
                        'Loyers': 'bg-purple-100 text-purple-800',
                        'Frais de transport et manutention': 'bg-blue-100 text-blue-800',
                        'Salaires et charges du personnel': 'bg-green-100 text-green-800',
                        'Services extérieurs et prestations': 'bg-yellow-100 text-yellow-800',
                        'Eau, électricité et internet': 'bg-cyan-100 text-cyan-800',
                        'Matériel et fournitures': 'bg-orange-100 text-orange-800',
                        'Carburant et entretien des véhicules': 'bg-red-100 text-red-800',
                        'Marketing et communication': 'bg-pink-100 text-pink-800',
                        'Taxes, impôts et redevances': 'bg-indigo-100 text-indigo-800',
                        'Autres dépenses': 'bg-gray-100 text-gray-800'
                    };
                    return colors[category] || 'bg-gray-100 text-gray-800';
                },

                getCategoryShortName(category) {
                    const shortNames = {
                        'Loyers': 'Loyers',
                        'Frais de transport et manutention': 'Transport',
                        'Salaires et charges du personnel': 'Salaires',
                        'Services extérieurs et prestations': 'Services',
                        'Eau, électricité et internet': 'Utilitaires',
                        'Matériel et fournitures': 'Matériel',
                        'Carburant et entretien des véhicules': 'Véhicules',
                        'Marketing et communication': 'Marketing',
                        'Taxes, impôts et redevances': 'Taxes',
                        'Autres dépenses': 'Autres'
                    };
                    return shortNames[category] || category;
                },

                applyFilters() {
                    this.currentPage = 1;
                },

                resetFilters() {
                    this.searchTerm = '';
                    this.categoryFilter = 'all';
                    this.specificDate = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.sortBy = 'date';
                    this.applyFilters();
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

                printExpensesList() {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Liste des Dépenses</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #e8f4fd; border: 1px solid #bee5eb; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, <br>
                                téléphone 01 49 91 65 66</p>
                                <h2>LISTE DES DÉPENSES</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Nombre de dépenses:</strong> ${this.filteredExpenses.length}</p>
                                <p><strong>Total des dépenses:</strong> ${this.formatCurrency(this.totalExpenses)} XOF</p>
                                <p><strong>Dépense moyenne:</strong> ${this.formatCurrency(this.averageExpense)} XOF</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Catégorie</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredExpenses.forEach(expense => {
                        printContent += `
                            <tr>
                                <td>${expense.name}</td>
                                <td>${this.getCategoryShortName(expense.category)}</td>
                                <td>${this.formatDate(expense.created_at)}</td>
                                <td>${this.formatCurrency(expense.amount)} XOF</td>
                                <td>${expense.notes || '-'}</td>
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

                openNewExpenseModal() {
                    this.editingExpense = null;
                    this.expenseForm = {
                        name: '',
                        amount: '',
                        category: '',
                        date: new Date().toISOString().split('T')[0],
                        description: ''
                    };
                    this.showExpenseModal = true;
                },

                closeExpenseModal() {
                    this.showExpenseModal = false;
                    this.editingExpense = null;
                },

                editExpense(expense) {
                    this.editingExpense = expense;
                    this.expenseForm = {
                        name: expense.name,
                        amount: expense.amount,
                        category: expense.category,
                        date: expense.created_at.split(' ')[0],
                        description: expense.notes || ''
                    };
                    this.showExpenseModal = true;
                },

                async saveExpense() {
                    const action = this.editingExpense ? 'updateExpense' : 'newExpense';
                    const payload = {
                        name: this.expenseForm.name,
                        amount: this.expenseForm.amount,
                        category: this.expenseForm.category,
                        created_at: this.expenseForm.date,
                        notes: this.expenseForm.description,
                        ...(this.editingExpense && {
                            id: this.editingExpense.id
                        })
                    };

                    console.log('[DEBUG] Envoi de la requête vers:', action, 'avec payload:', payload);

                    try {
                        const response = await api.post(`?action=${action}`, payload);

                        console.log('[DEBUG] Réponse API:', response.data);

                        if (!response.data.success) {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue'));
                            return;
                        }

                        alert(this.editingExpense ? 'Dépense modifiée avec succès!' : 'Nouvelle dépense ajoutée avec succès!');
                        this.closeExpenseModal();
                        await this.fetchExpenses();
                    } catch (error) {
                        console.error('[DEBUG] Erreur lors de l\'enregistrement de la dépense:', error);
                        alert('Erreur lors de l\'enregistrement de la dépense');
                    }
                },

                deleteExpense(expenseId) {
                    if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous vraiment sûr de vouloir supprimer cette dépense?')) {
                        return;
                    }

                    console.log('[v0] Deleting expense:', expenseId, 'via route: ?action=deleteExpense');

                    api.post('?action=deleteExpense', {
                            id: expenseId
                        })
                        .then(response => {
                            if (response.data.success) {
                                alert('Dépense supprimée avec succès!');
                                this.fetchExpenses();
                            } else {
                                alert('Erreur lors de la suppression: ' + response.data.error);
                            }
                        })
                        .catch(error => {
                            console.error('[v0] Error deleting expense:', error);
                            alert('Erreur lors de la suppression de la dépense');
                        });
                }
            }
        }).mount('#app');
    </script>
</body>

</html>