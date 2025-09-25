<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Créances - Tossin</title>
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
                            <h1 class="text-2xl font-bold text-gray-900">Gestion des Créances</h1>
                            <button @click="openNewClaimModal" class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                <i class="fas fa-plus mr-2"></i>Nouvelle créance
                            </button>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Nom du client..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                <select v-model="sortBy" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="name">Nom</option>
                                    <option value="amount">Montant de la dette</option>
                                    <option value="date">Date de la dette</option>
                                    <option value="overdue">Échéance dépassée</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous</option>
                                    <option value="active">Actif</option>
                                    <option value="overdue">En retard</option>
                                    <option value="paid">Soldé</option>
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
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date dette</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date remboursement</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant initial</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant restant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="claim in claims" :key="claim.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ claim.client_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ claim.client_phone }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date dette'">
                                            {{ formatDate(claim.date_of_claim) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date remboursement'">
                                            {{ formatDate(claim.due_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'Montant initial'">
                                            {{ formatCurrency(claim.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Montant restant'">
                                            <span :class="claim.remaining_amount > 0 ? 'text-red-600' : 'text-green-600'">
                                                {{ formatCurrency(claim.remaining_amount) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Statut'">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusInfo(claim.status).class]">
                                                {{ getStatusInfo(claim.status).label }}
                                            </span>
                                            <div v-if="isOverdue(claim)" class="text-xs text-red-500 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Échéance dépassée
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <button @click="showPaymentHistory(claim)" class="text-primary hover:text-secondary mr-3" title="Historique">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button @click="openNewPaymentModal(claim)" class="text-green-600 hover:text-green-800 mr-3" title="Nouveau paiement">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                            <button @click="deleteClient(claim.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
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

            <div v-if="showNewClaimModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-user-plus mr-2"></i>Nouvelle Créance
                            </h3>
                            <button @click="closeNewClaimModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="addNewClaim" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1"></i>Nom du client
                                    </label>
                                    <input v-model="newClaim.client_name" type="text" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1"></i>Téléphone
                                    </label>
                                    <input v-model="newClaim.client_phone" type="tel" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date de la dette
                                    </label>
                                    <input v-model="newClaim.date_of_claim" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Montant (XOF)
                                    </label>
                                    <input v-model.number="newClaim.amount" type="number" required min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-alt mr-1"></i>Date d'échéance
                                    </label>
                                    <input v-model="newClaim.due_date" type="date" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2
                                         focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="newClaim.notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit"
                                    class="flex-1 bg-accent hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                                <button type="button" @click="closeNewClaimModal"
                                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-times mr-2"></i>Annuler
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div v-if="showPaymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Historique des Paiements
                            </h3>
                            <button @click="closePaymentHistoryModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div v-if="selectedClaim">
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">{{ selectedClaim.client_name }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant initial</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ formatCurrency(selectedClaim.amount) }}</p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant payé</p>
                                        <p class="text-lg font-semibold text-green-600">{{ formatCurrency(selectedClaim.amount - selectedClaim.remaining_amount) }}</p>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant restant</p>
                                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(selectedClaim.remaining_amount) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Moyen</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Justificatif</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-if="payments.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                                <p>Aucun paiement enregistré</p>
                                            </td>
                                        </tr>
                                        <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900" data-label="Date">{{ formatDate(payment.date_of_insertion) }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-green-600" data-label="Montant">{{ formatCurrency(payment.amount) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600" data-label="Moyen">{{ payment.payment_method }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600" data-label="Notes">{{ payment.notes || '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600" data-label="Justificatif">
                                                <div v-if="payment.file && payment.file !== ''">
                                                    <a :href="getImgUrl(payment.file)" target="_blank">
                                                        <img :src="getImgUrl(payment.file)" alt="Justificatif" style="width: 80px; height: 80px; object-fit: cover;">
                                                    </a>
                                                </div>
                                                <p v-else>Aucun justificatif disponible</p>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showNewPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
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
                                    <i class="fas fa-money-bill-wave mr-1"></i>Montant (XOF)
                                </label>
                                <input v-model.number="newPayment.amount"
                                    type="number"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">

                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>Date de paiement
                                </label>
                                <input v-model="newPayment.date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-credit-card mr-1"></i>Moyen de paiement
                                </label>
                                <select v-model="newPayment.payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Sélectionner...</option>
                                    <option value="Especes">Espèces</option>
                                    <option value="Mobile_money">Mobile Money</option>
                                    <option value="Cheque">Chèque</option>
                                    <option value="Virement">Virement bancaire</option>
                                    <option value="Carte_bancaire">Carte bancaire</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="newPayment.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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
        </div>
    </div>

    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    sidebarOpen: false,
                    searchTerm: '',
                    sortBy: 'client_name',
                    statusFilter: 'all',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showNewClaimModal: false,
                    showPaymentHistoryModal: false,
                    showNewPaymentModal: false,
                    selectedClaim: '',
                    payments: [],
                    claim: {
                        id: null,
                        client_name: '',
                        client_phone: '',
                        amount: 0,
                        date_of_claim: '',
                        due_date: '',
                        notes: '',
                        status: ''
                    },

                    claims: [],

                    newClaim: {
                        client_name: '',
                        client_phone: '',
                        amount: '',
                        date_of_claim: new Date().toISOString().split('T')[0],
                        due_date: '',
                        notes: ''
                    },

                    newPayment: {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        method: '',
                        notes: ''
                    }
                };
            },

            mounted() {
                // fermer la sidebar sur mobile
                document.addEventListener('click', (e) => {
                    if (window.innerWidth < 1024 && !e.target.closest('#sidebar') && !e.target.closest('button')) {
                        this.sidebarOpen = false;
                    }
                });

                // récupérer les clients depuis l'API
                this.fetchClaims();
            },

            computed: {
                filteredClaims() {
                    let filtered = this.claims.filter(claim => {
                        const matchesSearch =
                            claim.client_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            claim.client_phone.toString().includes(this.searchTerm) ||
                            (claim.notes && claim.notes.toLowerCase().includes(this.searchTerm.toLowerCase()));

                        const matchesStatus =
                            this.statusFilter === 'all' || claim.status === this.statusFilter;

                        return matchesSearch && matchesStatus;
                    });

                    // tri
                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'name':
                                return a.client_name.localeCompare(b.client_name);
                            case 'amount':
                                return parseFloat(b.remaining_amount) - parseFloat(a.remaining_amount);
                            case 'date':
                                return new Date(a.date_of_claim) - new Date(b.date_of_claim);
                            case 'overdue':
                                const aOverdue = new Date(a.due_date) < new Date() && parseFloat(a.remaining_amount) > 0;
                                const bOverdue = new Date(b.due_date) < new Date() && parseFloat(b.remaining_amount) > 0;
                                return bOverdue - aOverdue;
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },


                totalItems() {
                    return this.filteredClients.length;
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

                paginatedClients() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredClients.slice(start, start + this.itemsPerPage);
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
                }
            },

            methods: {
                fetchClaims() {
                    axios.get('http://127.0.0.1/tossin/api/index.php?action=allClaims')
                        .then(response => {
                            // si l'API renvoie un tableau directement
                            this.claims = response.data;

                            // debug
                            console.log('Response data:', response.data);
                            console.log('Reactive claims:', JSON.parse(JSON.stringify(this.claims)));
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des claims :', error);
                        });
                },

                formatDate(dateString) {
                    return new Date(dateString).toLocaleDateString('fr-FR');
                },
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR').format(amount) + ' XOF';
                },
                getStatusInfo(status) {
                    const statusMap = {
                        'pending': {
                            label: 'Actif',
                            class: 'text-blue-600 bg-blue-100'
                        },
                        'overdue': {
                            label: 'En retard',
                            class: 'text-red-600 bg-red-100'
                        },
                        'paid': {
                            label: 'Soldé',
                            class: 'text-green-600 bg-green-100'
                        }
                    };
                    return statusMap[status] || {
                        label: status,
                        class: 'text-gray-600 bg-gray-100'
                    };
                },
                isOverdue(claim) {
                    return new Date(claim.dueDate) < new Date() && claim.remaining_amount > 0;
                },

                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
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

                openNewClaimModal() {
                    this.showNewClaimModal = true;
                    this.newClaim.date_of_claim = new Date().toISOString().split('T')[0];
                },

                closeNewClaimModal() {
                    this.showNewClaimModal = false;
                    this.newClaim = {
                        client_name: '',
                        client_phone: '',
                        amount: 0,
                        date_of_claim: new Date().toISOString().split('T')[0],
                        due_date: '',
                        notes: ''
                    };
                },

                addNewClaim() {
                    const payload = {
                        client_name: this.newClaim.client_name,
                        client_phone: this.newClaim.client_phone,
                        amount: this.newClaim.amount,
                        remaining_amount: this.newClaim.amount,
                        date_of_claim: this.newClaim.date_of_claim,
                        due_date: this.newClaim.due_date,
                        notes: this.newClaim.notes,
                        status: 'pending'
                    };

                    axios.post('http://127.0.0.1/tossin/api/index.php?action=newClaim', payload)
                        .then(response => {
                            if (response.data.error) {
                                // Si le backend renvoie une erreur
                                console.error('Erreur backend:', response.data.error);
                                alert('Erreur backend : ' + response.data.error);
                                return;
                            }

                            const newClaim = response.data;
                            this.claims.push(newClaim);

                            this.newClaim = {
                                client_name: '',
                                client_phone: '',
                                amount: 0,
                                date_of_claim: new Date().toISOString().split('T')[0],
                                due_date: '',
                                notes: ''
                            };

                            this.closeNewClaimModal();
                            alert("Nouvelle créance ajoutée avec succès !");
                            this.fetchClaims();
                        })
                        .catch(error => {
                            // Si erreur réseau ou PHP fatal
                            console.error('Erreur lors de l’ajout du claim :', error.response?.data || error.message);
                            alert('Erreur lors de l’ajout du claim : ' + (error.response?.data?.error || error.message));
                        });

                },

                getImgUrl(fileName) {
                    if (!fileName || fileName === '') return '';
                    return `http://127.0.0.1/tossin/api/uploads/payments/${fileName}`;
                },

                showPaymentHistory(claim) {
                    this.selectedClaim = claim;

                    // Filtrer les paiements déjà chargés par claim_id
                    axios.get('http://127.0.0.1/tossin/api/index.php?action=allPayments')
                        .then(response => {
                            if (!response.data || !Array.isArray(response.data)) {
                                console.error('Données de paiement invalides :', response.data);
                                this.payments = [];
                                return;
                            }

                            // Filtrage des paiements correspondant à la créance sélectionnée
                            this.payments = response.data.filter(payment => payment.claim_id === claim.id);

                            console.log('Paiements filtrés :', this.payments);
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des paiements :', error.response?.data || error.message);
                            this.payments = [];
                        });

                    this.showPaymentHistoryModal = true;
                },


                closePaymentHistoryModal() {
                    this.showPaymentHistoryModal = false;
                    this.selectedClient = null;
                },

                openNewPaymentModal(claim) {
                    this.selectedClaim = claim;
                    this.showNewPaymentModal = true;
                    this.newPayment.date = new Date().toISOString().split('T')[0];
                },

                closeNewPaymentModal() {
                    this.showNewPaymentModal = false;
                    this.selectedClient = null;
                    this.newPayment = {
                        amount: 0,
                        date: new Date().toISOString().split('T')[0],
                        method: '',
                        notes: ''
                    };
                },

                handleFileUpload(event) {
                    this.newPayment.file = event.target.files[0] || null;
                },

                addNewPayment() {
                    if (this.newPayment.amount > this.selectedClaim.remaining_amount) {
                        alert('Le montant du paiement ne peut pas dépasser le montant restant');
                        return;
                    }

                    if (this.newPayment.amount < 0) {
                        alert("Le montant ne peut pas être négatif.");
                        return;
                    }

                    const formData = new FormData();
                    formData.append('claim_id', this.selectedClaim.id);
                    formData.append('amount', this.newPayment.amount);
                    formData.append('date_of_insertion', this.newPayment.date);
                    formData.append('payment_method', this.newPayment.payment_method);
                    formData.append('notes', this.newPayment.notes);
                    if (this.newPayment.file) {
                        formData.append('file', this.newPayment.file);
                    }

                    axios.post('http://127.0.0.1/tossin/api/index.php?action=newPayment', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then(response => {
                            // Vérifie le succès du backend
                            if (!response.data.success) {
                                console.error('Erreur backend:', response.data.error);
                                alert('Erreur backend : ' + response.data.error);
                                return;
                            }

                            // Réinitialiser le formulaire
                            this.newPayment = {
                                amount: 0,
                                date: new Date().toISOString().split('T')[0],
                                payment_method: '',
                                notes: '',
                                file: null
                            };

                            this.closeNewPaymentModal();
                            alert("Nouveau paiement ajouté avec succès !");
                            this.fetchClaims(); // Ajout des parenthèses
                        })
                        .catch(error => {
                            console.error('Erreur lors de l’ajout du paiement :', error.response?.data || error.message);
                            alert('Une erreur est survenue lors de l’ajout du paiement : ' + (error.response?.data?.error || error.message));
                        });
                },

                deleteClaim(claimId) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette créance ?')) {
                        const index = this.claim.findIndex(c => c.id === clsimId);
                        if (index !== -1) this.claims.splice(index, 1);
                    }
                },

                logout() {
                    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                        window.location.href = 'login.html';
                    }
                }
            },
        }).mount('#app');
    </script>

</body>

</html>