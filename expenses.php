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
    <title>Gestion des Dépenses - Gbemiro</title>
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

            .print-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }

            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
                font-size: 12px;
            }

            .print-table th {
                background-color: #f0f0f0;
                font-weight: bold;
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
                                    <i class="fas fa-print mr-2"></i>Imprimer la liste
                                </button>
                                <button @click="openNewExpenseModal" class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle dépense
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
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Nom de la dépense..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="name">Nom</option>
                                    <option value="amount">Montant</option>
                                    <option value="date">Date</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
                                <select v-model="currencyFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Toutes</option>
                                    <option value="XOF">XOF</option>
                                    <option value="N">Naira</option>
                                    <option value="GHC">Ghana Cedis</option>
                                    <option value="EUR">Euro</option>
                                    <option value="USD">Dollar</option>
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Total des dépenses</p>
                                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalExpenses) }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Nombre de dépenses</p>
                                <p class="text-2xl font-bold text-blue-600">{{ filteredExpenses.length }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Dépense moyenne</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(averageExpense) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date'">
                                            {{ formatDate(expense.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600" :data-label="'Montant'">
                                            {{ formatCurrency(expense.amount, expense.currency) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500" :data-label="'Description'">
                                            {{ expense.description || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <button @click="editExpense(expense)" class="text-blue-600 hover:text-blue-800 mr-3" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteExpense(expense.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
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
                                        <i class="fas fa-calendar mr-1"></i>Date
                                    </label>
                                    <input v-model="expenseForm.date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Montant
                                    </label>
                                    <input v-model.number="expenseForm.amount" type="number" step="0.01" required min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins mr-1"></i>Devise
                                    </label>
                                    <select v-model="expenseForm.currency" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="XOF">XOF (Franc CFA)</option>
                                        <option value="N">N (Naira)</option>
                                        <option value="GHC">GHC (Ghana Cedis)</option>
                                        <option value="EUR">EUR (Euro)</option>
                                        <option value="USD">USD (Dollar)</option>
                                        <option value="GBP">GBP (Livre Sterling)</option>
                                    </select>
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
                                    class="flex-1 bg-accent hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
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
            baseURL: 'http://127.0.0.1/Gbemiro/api/index.php'
        });

        createApp({
            data() {
                return {
                    searchTerm: '',
                    sortBy: 'date',
                    currencyFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showExpenseModal: false,
                    editingExpense: null,
                    expenses: [],
                    expenseForm: {
                        name: '',
                        amount: '',
                        currency: 'XOF',
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
                            (expense.description && expense.description.toLowerCase().includes(this.searchTerm.toLowerCase()));

                        const matchesCurrency = this.currencyFilter === 'all' || expense.currency === this.currencyFilter;

                        return matchesSearch && matchesCurrency;
                    });

                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'name':
                                return a.name.localeCompare(b.name);
                            case 'amount':
                                return parseFloat(b.amount) - parseFloat(a.amount);
                            case 'date':
                                return new Date(b.date) - new Date(a.date);
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
                    api.get('?action=allExpenses')
                        .then(response => {
                            this.expenses = response.data;
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des dépenses:', error);
                        });
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
                                .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 12px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>LISTE DES DÉPENSES</h1>
                                <p>Date d'impression: ${currentDate}</p>
                                <p>Nombre total de dépenses: ${this.filteredExpenses.length}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Total des dépenses:</strong> ${this.formatCurrency(this.totalExpenses)}</p>
                                <p><strong>Dépense moyenne:</strong> ${this.formatCurrency(this.averageExpense)}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nom</th>
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
                                <td>${this.formatDate(expense.date)}</td>
                                <td>${this.formatCurrency(expense.amount, expense.currency)}</td>
                                <td>${expense.description || '-'}</td>
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

                openNewExpenseModal() {
                    this.editingExpense = null;
                    this.expenseForm = {
                        name: '',
                        amount: '',
                        currency: 'XOF',
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
                        currency: expense.currency,
                        date: expense.date,
                        description: expense.description || ''
                    };
                    this.showExpenseModal = true;
                },

                saveExpense() {
                    const action = this.editingExpense ? 'updateExpense' : 'newExpense';
                    const payload = {
                        ...this.expenseForm,
                        ...(this.editingExpense && {
                            id: this.editingExpense.id
                        })
                    };

                    api.post(`?action=${action}`, payload)
                        .then(response => {
                            if (response.data.error) {
                                alert('Erreur: ' + response.data.error);
                                return;
                            }

                            alert(this.editingExpense ? 'Dépense modifiée avec succès!' : 'Nouvelle dépense ajoutée avec succès!');
                            this.closeExpenseModal();
                            this.fetchExpenses();
                        })
                        .catch(error => {
                            console.error('Erreur lors de l\'enregistrement:', error);
                            alert('Erreur lors de l\'enregistrement de la dépense');
                        });
                },

                deleteExpense(expenseId) {
                    if (!confirm('⚠️ ATTENTION: Cette action est irréversible!\n\nÊtes-vous vraiment sûr de vouloir supprimer cette dépense?')) {
                        return;
                    }

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
                            console.error('Erreur lors de la suppression:', error);
                            alert('Erreur lors de la suppression de la dépense');
                        });
                }
            }
        }).mount('#app');
    </script>
</body>

</html>