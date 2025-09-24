<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Créances - Tossin</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid #ccc; margin-bottom: 10px; padding: 10px; border-radius: 8px; background: white; }
            td { border: none; position: relative; padding-left: 50% !important; padding-top: 10px; padding-bottom: 10px; }
            td:before { content: attr(data-label) ": "; position: absolute; left: 6px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; color: #374151; }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="bg-gray-50 min-h-screen">
            <div :class="['fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">
                <div class="flex items-center justify-center h-16 bg-primary">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-building text-white text-2xl"></i>
                        <span class="text-white text-xl font-bold">Tossin</span>
                    </div>
                </div>
                
                <nav class="mt-8">
                    <a href="dashboard.html" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span>Tableau de bord</span>
                    </a>
                    <a href="creances.html" class="flex items-center px-6 py-3 text-primary bg-blue-50 border-r-4 border-primary">
                        <i class="fas fa-money-bill-wave mr-3"></i>
                        <span>Créances</span>
                    </a>
                    <a href="commandes.html" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
                        <i class="fas fa-shopping-cart mr-3"></i>
                        <span>Commandes</span>
                    </a>
                </nav>
                
                <div class="absolute bottom-0 w-full p-4">
                    <button @click="logout" class="w-full flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span>Déconnexion</span>
                    </button>
                </div>
            </div>

            <div class="lg:hidden fixed top-4 left-4 z-50">
                <button @click="toggleSidebar" class="bg-white p-2 rounded-lg shadow-lg">
                    <i class="fas fa-bars text-gray-700"></i>
                </button>
            </div>

            <div class="lg:ml-64 min-h-screen">
                <header class="bg-white shadow-sm border-b">
                    <div class="px-6 py-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                            <h1 class="text-2xl font-bold text-gray-900">Gestion des Créances</h1>
                            <button @click="openNewClientModal" class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                <i class="fas fa-plus mr-2"></i>Nouveau client
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date dette</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant initial</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant restant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="client in paginatedClients" :key="client.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ client.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ client.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" :data-label="'Contact'">
                                            <i class="fas fa-phone mr-1"></i>{{ client.phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date dette'">
                                            {{ formatDate(client.debtDate) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'Montant initial'">
                                            {{ formatCurrency(client.initialAmount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Montant restant'">
                                            <span :class="client.remainingAmount > 0 ? 'text-red-600' : 'text-green-600'">
                                                {{ formatCurrency(client.remainingAmount) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" :data-label="'Statut'">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getStatusInfo(client.status).class]">
                                                {{ getStatusInfo(client.status).label }}
                                            </span>
                                            <div v-if="isOverdue(client)" class="text-xs text-red-500 mt-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Échéance dépassée
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                            <button @click="showPaymentHistory(client)" class="text-primary hover:text-secondary mr-3" title="Historique">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button @click="openNewPaymentModal(client)" class="text-green-600 hover:text-green-800 mr-3" title="Nouveau paiement">
                                                <i class="fas fa-plus-circle"></i>
                                            </button>
                                            <button @click="deleteClient(client.id)" class="text-red-600 hover:text-red-800" title="Supprimer">
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
                                        Affichage de {{ startItem }} à {{ endItem }} sur {{ totalItems }} résultats
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <button @click="previousPage" :disabled="currentPage === 1" :class="['relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50', currentPage === 1 ? 'cursor-not-allowed' : '']">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium', page === currentPage ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50']">
                                            {{ page }}
                                        </button>
                                        <button @click="nextPage" :disabled="currentPage === totalPages" :class="['relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50', currentPage === totalPages ? 'cursor-not-allowed' : '']">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showNewClientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-user-plus mr-2"></i>Nouveau Client
                            </h3>
                            <button @click="closeNewClientModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <form @submit.prevent="addNewClient" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-1"></i>Nom du client
                                    </label>
                                    <input v-model="newClient.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1"></i>Téléphone
                                    </label>
                                    <input v-model="newClient.phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-envelope mr-1"></i>Email
                                    </label>
                                    <input v-model="newClient.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar mr-1"></i>Date de la dette
                                    </label>
                                    <input v-model="newClient.debtDate" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Montant initial (XOF)
                                    </label>
                                    <input v-model.number="newClient.initialAmount" type="number" required min="0" step="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-calendar-alt mr-1"></i>Date d'échéance
                                    </label>
                                    <input v-model="newClient.dueDate" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="newClient.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>

                            <div class="flex space-x-3 pt-4">
                                <button type="submit" class="flex-1 bg-accent hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                                <button type="button" @click="closeNewClientModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
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
                        
                        <div v-if="selectedClient">
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">{{ selectedClient.name }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant initial</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ formatCurrency(selectedClient.initialAmount) }}</p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant payé</p>
                                        <p class="text-lg font-semibold text-green-600">{{ formatCurrency(selectedClient.initialAmount - selectedClient.remainingAmount) }}</p>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant restant</p>
                                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(selectedClient.remainingAmount) }}</p>
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
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-if="selectedClient.payments.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                                <p>Aucun paiement enregistré</p>
                                            </td>
                                        </tr>
                                        <tr v-for="payment in selectedClient.payments" :key="payment.date + payment.amount" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(payment.date) }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-green-600">{{ formatCurrency(payment.amount) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ payment.method }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ payment.notes || '-' }}</td>
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
                        
                        <form @submit.prevent="addNewPayment" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-money-bill-wave mr-1"></i>Montant (XOF)
                                </label>
                                <input v-model.number="newPayment.amount" type="number" required min="0" step="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                                <select v-model="newPayment.method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Sélectionner...</option>
                                    <option value="especes">Espèces</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement bancaire</option>
                                    <option value="mobile">Mobile Money</option>
                                    <option value="carte">Carte bancaire</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notes
                                </label>
                                <textarea v-model="newPayment.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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

   <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<div id="app">
    <!-- Ton HTML ici -->
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            sidebarOpen: false,
            searchTerm: '',
            sortBy: 'name',
            statusFilter: 'all',
            currentPage: 1,
            itemsPerPage: 10,

            showNewClientModal: false,
            showPaymentHistoryModal: false,
            showNewPaymentModal: false,
            selectedClient: null,

            clients: [
                {
                    id: 1,
                    name: "Entreprise Alpha",
                    phone: "+221 77 123 45 67",
                    email: "contact@alpha.sn",
                    debtDate: "2024-01-15",
                    initialAmount: 500000,
                    remainingAmount: 350000,
                    dueDate: "2024-03-15",
                    status: "overdue",
                    notes: "Client régulier, bon payeur",
                    payments: [
                        { date: "2024-02-10", amount: 150000, method: "virement", notes: "Paiement partiel" }
                    ]
                },
                // autres clients...
            ],

            newClient: {
                name: '',
                phone: '',
                email: '',
                debtDate: new Date().toISOString().split('T')[0],
                initialAmount: 0,
                dueDate: '',
                notes: ''
            },

            newPayment: {
                amount: 0,
                date: new Date().toISOString().split('T')[0],
                method: '',
                notes: ''
            }
        };
    },

    computed: {
        filteredClients() {
            let filtered = this.clients.filter(client => {
                const matchesSearch = client.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                                      client.phone.includes(this.searchTerm) ||
                                      client.email.toLowerCase().includes(this.searchTerm.toLowerCase());
                const matchesStatus = this.statusFilter === 'all' || client.status === this.statusFilter;
                return matchesSearch && matchesStatus;
            });

            filtered.sort((a, b) => {
                switch (this.sortBy) {
                    case 'name': return a.name.localeCompare(b.name);
                    case 'amount': return b.remainingAmount - a.remainingAmount;
                    case 'date': return new Date(a.debtDate) - new Date(b.debtDate);
                    case 'overdue':
                        const aOverdue = new Date(a.dueDate) < new Date() && a.remainingAmount > 0;
                        const bOverdue = new Date(b.dueDate) < new Date() && b.remainingAmount > 0;
                        return bOverdue - aOverdue;
                    default: return 0;
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
        getStatusInfo(status) {
            const statusMap = {
                'active': { label: 'Actif', class: 'text-blue-600 bg-blue-100' },
                'overdue': { label: 'En retard', class: 'text-red-600 bg-red-100' },
                'paid': { label: 'Soldé', class: 'text-green-600 bg-green-100' }
            };
            return statusMap[status] || { label: status, class: 'text-gray-600 bg-gray-100' };
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' XOF';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR');
        },

        isOverdue(client) {
            return new Date(client.dueDate) < new Date() && client.remainingAmount > 0;
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

        openNewClientModal() {
            this.showNewClientModal = true;
            this.newClient.debtDate = new Date().toISOString().split('T')[0];
        },

        closeNewClientModal() {
            this.showNewClientModal = false;
            this.newClient = {
                name: '',
                phone: '',
                email: '',
                debtDate: new Date().toISOString().split('T')[0],
                initialAmount: 0,
                dueDate: '',
                notes: ''
            };
        },

        addNewClient() {
            const client = {
                id: this.clients.length + 1,
                name: this.newClient.name,
                phone: this.newClient.phone,
                email: this.newClient.email,
                debtDate: this.newClient.debtDate,
                initialAmount: this.newClient.initialAmount,
                remainingAmount: this.newClient.initialAmount,
                dueDate: this.newClient.dueDate,
                status: 'active',
                notes: this.newClient.notes,
                payments: []
            };
            this.clients.push(client);
            this.closeNewClientModal();
        },

        showPaymentHistory(client) {
            this.selectedClient = client;
            this.showPaymentHistoryModal = true;
        },

        closePaymentHistoryModal() {
            this.showPaymentHistoryModal = false;
            this.selectedClient = null;
        },

        openNewPaymentModal(client) {
            this.selectedClient = client;
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

        addNewPayment() {
            if (this.newPayment.amount > this.selectedClient.remainingAmount) {
                alert('Le montant du paiement ne peut pas dépasser le montant restant');
                return;
            }

            const payment = {
                date: this.newPayment.date,
                amount: this.newPayment.amount,
                method: this.newPayment.method,
                notes: this.newPayment.notes
            };

            this.selectedClient.payments.push(payment);
            this.selectedClient.remainingAmount -= this.newPayment.amount;

            if (this.selectedClient.remainingAmount === 0) {
                this.selectedClient.status = 'paid';
            }

            this.closeNewPaymentModal();
        },

        deleteClient(clientId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                const index = this.clients.findIndex(c => c.id === clientId);
                if (index !== -1) this.clients.splice(index, 1);
            }
        },

        logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'login.html';
            }
        }
    },

    mounted() {
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 1024 && !e.target.closest('#sidebar') && !e.target.closest('button')) {
                this.sidebarOpen = false;
            }
        });
    }
}).mount('#app');
</script>

</body>
</html>
