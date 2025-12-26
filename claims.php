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
    <title>Gestion des Créances - Gbemiro</title>
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
                            <h1 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Gestion des Créances
                            </h1>
                            <div class="flex space-x-3">
                                <button @click="showClientsTab = !showClientsTab" :class="showClientsTab ? 'bg-primary' : 'bg-gray-500'" class="hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i :class="showClientsTab ? 'fas fa-file-invoice-dollar' : 'fas fa-users'" class="mr-2"></i>
                                    {{ showClientsTab ? 'Voir Créances' : 'Voir Clients' }}
                                </button>
                                <button @click="printClaimsList" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button v-if="!showClientsTab" @click="openNewClaimModal" class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Nouvelle créance
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-6">
                    <!-- Section des clients -->
                    <div v-if="showClientsTab">
                        <!-- Filtres pour clients -->
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-filter mr-2"></i>Filtres
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher client</label>
                                    <input v-model="clientSearchTerm" @input="filterClients" type="text" placeholder="Nom du client..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                                    <select v-model="clientSortBy" @change="filterClients" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="name">Nom</option>
                                        <option value="debt">Dette totale</option>
                                        <option value="purchases">Nombre d'achats</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button @click="clearClientFilters" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-times mr-2"></i>Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques clients -->
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Total clients</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ clientsWithData.length }}</p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Créances totales</p>
                                    <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalClientsDebt) }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Total ventes clients</p>
                                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalClientsSales) }}</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Clients avec créances</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ clientsWithDebt }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des clients -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créances actives</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dette totale</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Achats totaux</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nb. achats</th>
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
                                                        <div class="text-sm text-gray-500">{{ client.phone }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Créances actives'">
                                                {{ client.activeClaims }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Dette totale'" :class="client.totalDebt > 0 ? 'text-red-600' : 'text-gray-500'">
                                                {{ formatCurrency(client.totalDebt) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" :data-label="'Achats totaux'">
                                                {{ formatCurrency(client.totalSales) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Nb. achats'">
                                                {{ client.salesCount }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                                <button @click="showClientDetails(client)" class="text-primary hover:text-secondary text-xl" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination clients -->
                            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <button @click="previousClientPage" :disabled="currentClientPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                        Précédent
                                    </button>
                                    <button @click="nextClientPage" :disabled="currentClientPage === totalClientPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                                        Suivant
                                    </button>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Affichage de <span class="font-medium">{{ clientStartItem }}</span> à <span class="font-medium">{{ clientEndItem }}</span> sur <span class="font-medium">{{ totalClientsItems }}</span> résultats
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <button @click="previousClientPage" :disabled="currentClientPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            <button v-for="page in visibleClientPages" :key="page" @click="goToClientPage(page)" :class="['relative inline-flex items-center px-4 py-2 border text-sm font-medium', currentClientPage === page ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50']">
                                                {{ page }}
                                            </button>
                                            <button @click="nextClientPage" :disabled="currentClientPage === totalClientPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section des créances (existante) -->
                    <div v-else>
                        <!-- Filtres -->
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-filter mr-2"></i>Filtres
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                    <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Nom du client..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date exacte</label>
                                    <input v-model="exactDate" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
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
                                        <option value="client">Client</option>
                                        <option value="amount">Montant</option>
                                        <option value="remaining">Restant</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button @click="clearFilters" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-times mr-2"></i>Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Total créances</p>
                                    <p class="text-2xl font-bold text-red-600">{{ formatCurrency(totalClaims) }}</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Total payé</p>
                                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalPaid) }}</p>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Reste à payer</p>
                                    <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(totalRemaining) }}</p>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-1">Nombre de créances</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ filteredClaims.length }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Table des créances -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date dette</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant initial</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restant</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="claim in paginatedClaims" :key="claim.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap" :data-label="'Client'">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ getClientName(claim.client_id) }}</div>
                                                        <div class="text-sm text-gray-500">{{ getClientPhone(claim.client_id) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" :data-label="'Date dette'">
                                                {{ formatDate(claim.date_of_claim) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm" :data-label="'Échéance'" :class="isOverdue(claim) ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                                {{ formatDate(claim.due_date) }}
                                                <div v-if="isOverdue(claim)" class="text-xs text-red-500 mt-1">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>En retard
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" :data-label="'Montant initial'">
                                                {{ formatCurrency(claim.amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" :data-label="'Payé'">
                                                {{ formatCurrency(getClaimPaid(claim.id)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600" :data-label="'Restant'">
                                                {{ formatCurrency(getClaimRemaining(claim.id)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap" :data-label="'Statut'">
                                                <span :class="['px-3 py-1 rounded-full font-semibold text-xs', getStatusInfo(claim).class]">
                                                    <i :class="getStatusInfo(claim).icon" class="mr-1"></i>{{ getStatusInfo(claim).label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :data-label="'Actions'">
                                                <div class="flex space-x-3">
                                                    <button @click="showPaymentHistory(claim)" class="text-primary hover:text-secondary text-xl" title="Historique & Paiements">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <button @click="printClaimHistory(claim)" class="text-green-600 hover:text-green-800 text-xl" title="Imprimer">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button @click="deleteClaim(claim.id)" class="text-red-600 hover:text-red-800 text-xl" title="Supprimer">
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
            </div>

            <!-- Modal nouvelle créance -->
            <div v-if="showNewClaimModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-plus-circle mr-2"></i>Nouvelle Créance
                            </h3>
                            <button @click="closeNewClaimModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="addNewClaim" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                                    <input
                                        v-model="newClaimClientSearch"
                                        @input="showNewClaimClientDropdown = true; filterNewClaimClients()"
                                        @focus="showNewClaimClientDropdown = true"
                                        type="text"
                                        placeholder="Rechercher un client..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        required>
                                    <div v-if="showNewClaimClientDropdown && filteredNewClaimClients.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        <div
                                            v-for="client in filteredNewClaimClients"
                                            :key="client.id"
                                            @click="selectNewClaimClient(client)"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
                                            <div class="font-medium">{{ client.name }}</div>
                                            <div class="text-sm text-gray-500">{{ client.phone }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant *</label>
                                    <input v-model.number="newClaim.amount" type="number" step="0.01" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de la créance *</label>
                                    <input v-model="newClaim.date_of_claim" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance *</label>
                                    <input v-model="newClaim.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea v-model="newClaim.notes" rows="3" placeholder="Notes supplémentaires..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" @click="closeNewClaimModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    Annuler
                                </button>
                                <button type="submit" class="px-6 py-2 bg-accent hover:bg-green-600 text-white rounded-lg transition-colors">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal historique des paiements -->
            <div v-if="showPaymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>Historique des Paiements
                            </h3>
                            <div class="flex space-x-2">
                                <button @click="printPaymentHistory" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closePaymentHistoryModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedClaim">
                            <div class="mb-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">{{ getClientName(selectedClaim.client_id) }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="bg-blue-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant initial</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ formatCurrency(selectedClaim.amount) }}</p>
                                    </div>
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant payé</p>
                                        <p class="text-lg font-semibold text-green-600">{{ formatCurrency(selectedClaimPaid) }}</p>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded-lg">
                                        <p class="text-sm text-gray-600">Montant restant</p>
                                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(selectedClaimRemaining) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <button v-if="selectedClaimRemaining > 0" @click="openNewPaymentModal" class="bg-accent hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Nouveau paiement
                                </button>
                                <div v-else class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
                                    <i class="fas fa-check-circle mr-2 text-xl"></i>
                                    <span class="font-medium">Cette créance est entièrement soldée</span>
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
                                            <!-- CHANGE: Ajout de la colonne Actions dans le header -->
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-if="claimPayments.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                                <p>Aucun paiement enregistré</p>
                                            </td>
                                        </tr>
                                        <tr v-for="payment in claimPayments" :key="payment.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900" :data-label="'Date'">{{ formatDate(payment.date) }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-green-600" :data-label="'Montant'">{{ formatCurrency(payment.amount) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500" :data-label="'Moyen'">{{ payment.payment_method || '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500" :data-label="'Notes'">{{ payment.notes || '-' }}</td>
                                            <td class="px-4 py-3 text-sm font-medium" :data-label="'Actions'">
                                                <button @click="editPayment(payment)" class="text-orange-600 hover:text-orange-800 text-xl" title="Éditer">
                                                    <i class="fas fa-edit"></i>
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

            <!-- Modal nouveau paiement -->
            <div v-if="showNewPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Montant *</label>
                                <input v-model.number="newPayment.amount" type="number" step="0.01" :max="selectedClaimRemaining" placeholder="0.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                <p class="text-xs text-gray-500 mt-1">Maximum: {{ formatCurrency(selectedClaimRemaining) }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                <input v-model="newPayment.date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Moyen de paiement *</label>
                                <select v-model="newPayment.payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="Espèces">Espèces</option>
                                    <option value="Chèque">Chèque</option>
                                    <option value="Virement">Virement</option>
                                    <option value="Carte">Carte bancaire</option>
                                    <option value="Mobile Money">Mobile Money</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea v-model="newPayment.notes" rows="3" placeholder="Notes..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" @click="closeNewPaymentModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                    Annuler
                                </button>
                                <button type="submit" class="px-6 py-2 bg-accent hover:bg-green-600 text-white rounded-lg transition-colors">
                                    <i class="fas fa-save mr-2"></i>Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal détails client -->
            <div v-if="showClientDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-6xl w-full p-6 max-h-screen overflow-y-auto">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-user-circle mr-2"></i>Détails du Client
                            </h3>
                            <div class="flex space-x-2">
                                <!-- CHANGE: Ajout d'un bouton imprimer dans le modal détails client -->
                                <button @click="printClientDetails" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="closeClientDetailsModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedClient">
                            <!-- Infos client -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Nom</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ selectedClient.name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Téléphone</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ selectedClient.phone }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Dette totale</p>
                                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(selectedClient.totalDebt) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Créances du client -->
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i>Créances ({{ selectedClient.activeClaims }})
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payé</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Restant</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="claim in clientClaims" :key="claim.id" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900" :data-label="'Date'">{{ formatDate(claim.date_of_claim) }}</td>
                                                <td class="px-4 py-3 text-sm" :data-label="'Échéance'" :class="isOverdue(claim) ? 'text-red-600 font-semibold' : 'text-gray-900'">{{ formatDate(claim.due_date) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900" :data-label="'Montant'">{{ formatCurrency(claim.amount) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-green-600" :data-label="'Payé'">{{ formatCurrency(getClaimPaid(claim.id)) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-red-600" :data-label="'Restant'">{{ formatCurrency(getClaimRemaining(claim.id)) }}</td>
                                                <td class="px-4 py-3 text-sm" :data-label="'Statut'">
                                                    <span :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusInfo(claim).class]">
                                                        {{ getStatusInfo(claim).label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Achats du client -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">
                                    <i class="fas fa-shopping-cart mr-2"></i>Historique des achats ({{ clientSales.length }})
                                </h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Facture</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-if="clientSales.length === 0">
                                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                                    <p>Aucun achat enregistré</p>
                                                </td>
                                            </tr>
                                            <tr v-for="sale in clientSales" :key="sale.id" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900" :data-label="'N° Facture'">#{{ sale.id }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900" :data-label="'Date'">{{ formatDate(sale.date_of_insertion) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-green-600" :data-label="'Montant'">{{ formatCurrency(sale.total) }}</td>
                                                <td class="px-4 py-3 text-sm" :data-label="'Statut'">
                                                    <span v-if="sale.status === 'Fait'" class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold text-xs">
                                                        <i class="fas fa-check-circle mr-1"></i>Fait
                                                    </span>
                                                    <span v-else-if="sale.status === 'Annulé'" class="px-3 py-1 bg-red-100 text-red-800 rounded-full font-semibold text-xs">
                                                        <i class="fas fa-times-circle mr-1"></i>Annulé
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal modifier paiement -->
            <!-- CHANGE: Ajout du modal d'édition de paiement -->
            <div v-if="showEditPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2"></i>Modifier Paiement
                            </h3>
                            <button @click="closeEditPaymentModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="updatePayment" class="space-y-4">
                            <div v-if="editingPayment">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant (FCFA)</label>
                                    <input v-model="editingPayment.amount" type="number" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                    <input v-model="editingPayment.date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Moyen de paiement</label>
                                    <select v-model="editingPayment.payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Sélectionner...</option>
                                        <option value="Espèces">Espèces</option>
                                        <option value="Chèque">Chèque</option>
                                        <option value="Virement">Virement</option>
                                        <option value="Carte">Carte bancaire</option>
                                        <option value="Mobile Money">Mobile Money</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea v-model="editingPayment.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                </div>

                                <div class="flex space-x-3 pt-4">
                                    <button type="submit" class="flex-1 bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-save mr-2"></i>Enregistrer
                                    </button>
                                    <button type="button" @click="closeEditPaymentModal" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                                        Annuler
                                    </button>
                                </div>
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

        createApp({
            data() {
                return {
                    showClientsTab: false,
                    clients: [],
                    sales: [],
                    claimsPayments: [],
                    clientSearchTerm: '',
                    clientSortBy: 'name',
                    currentClientPage: 1,
                    itemsPerClientPage: 10,
                    selectedClient: null,
                    showClientDetailsModal: false,
                    clientClaims: [],
                    clientSales: [],

                    searchTerm: '',
                    sortBy: 'date',
                    exactDate: '',
                    startDate: '',
                    endDate: '',
                    currentPage: 1,
                    itemsPerPage: 10,
                    showNewClaimModal: false,
                    showPaymentHistoryModal: false,
                    showNewPaymentModal: false,
                    selectedClaim: null,
                    claimPayments: [],
                    claims: [],
                    newClaim: {
                        client_id: null,
                        amount: '',
                        date_of_claim: new Date().toISOString().split('T')[0],
                        due_date: '',
                        notes: ''
                    },
                    newPayment: {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        payment_method: '',
                        notes: ''
                    },
                    newClaimClientSearch: '',
                    showNewClaimClientDropdown: false,
                    filteredNewClaimClients: [],

                    // CHANGE: Ajout des variables pour l'édition de paiement
                    showEditPaymentModal: false,
                    editingPayment: null,
                };
            },

            mounted() {
                this.fetchClaims();
                this.fetchClients();
                this.fetchSales();
                this.fetchClaimsPayments();

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.relative')) {
                        this.showNewClaimClientDropdown = false;
                    }
                });
            },

            computed: {
                clientsWithData() {
                    return this.clients.map(client => {
                        const clientClaims = this.claims.filter(c => c.client_id === client.id);
                        const clientSales = this.sales.filter(s => s.client_id === client.id);

                        const totalDebt = clientClaims.reduce((sum, claim) => {
                            const remaining = this.getClaimRemaining(claim.id);
                            return sum + remaining;
                        }, 0);

                        const totalSales = clientSales.reduce((sum, sale) => {
                            return sum + parseFloat(sale.total || 0);
                        }, 0);

                        return {
                            ...client,
                            activeClaims: clientClaims.length,
                            totalDebt: totalDebt,
                            totalSales: totalSales,
                            salesCount: clientSales.length
                        };
                    });
                },

                filteredClients() {
                    let filtered = this.clientsWithData;

                    if (this.clientSearchTerm) {
                        const term = this.clientSearchTerm.toLowerCase();
                        filtered = filtered.filter(client =>
                            client.name.toLowerCase().includes(term) ||
                            client.phone.includes(term)
                        );
                    }

                    filtered.sort((a, b) => {
                        switch (this.clientSortBy) {
                            case 'name':
                                return a.name.localeCompare(b.name);
                            case 'debt':
                                return b.totalDebt - a.totalDebt;
                            case 'purchases':
                                return b.salesCount - a.salesCount;
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },

                paginatedClients() {
                    const start = (this.currentClientPage - 1) * this.itemsPerClientPage;
                    const end = start + this.itemsPerClientPage;
                    return this.filteredClients.slice(start, end);
                },

                totalClientPages() {
                    return Math.ceil(this.filteredClients.length / this.itemsPerClientPage);
                },

                visibleClientPages() {
                    const pages = [];
                    const maxVisible = 5;
                    let start = Math.max(1, this.currentClientPage - Math.floor(maxVisible / 2));
                    let end = Math.min(this.totalClientPages, start + maxVisible - 1);

                    if (end - start < maxVisible - 1) {
                        start = Math.max(1, end - maxVisible + 1);
                    }

                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                clientStartItem() {
                    return (this.currentClientPage - 1) * this.itemsPerClientPage + 1;
                },

                clientEndItem() {
                    return Math.min(this.currentClientPage * this.itemsPerClientPage, this.filteredClients.length);
                },

                totalClientsItems() {
                    return this.filteredClients.length;
                },

                totalClientsDebt() {
                    return this.clientsWithData.reduce((sum, client) => sum + client.totalDebt, 0);
                },

                totalClientsSales() {
                    return this.clientsWithData.reduce((sum, client) => sum + client.totalSales, 0);
                },

                clientsWithDebt() {
                    return this.clientsWithData.filter(client => client.totalDebt > 0).length;
                },

                filteredClaims() {
                    let filtered = this.claims;

                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(claim => {
                            const clientName = this.getClientName(claim.client_id).toLowerCase();
                            return clientName.includes(term);
                        });
                    }

                    if (this.exactDate) {
                        filtered = filtered.filter(claim => {
                            const claimDate = claim.date_of_claim.split(' ')[0];
                            return claimDate === this.exactDate;
                        });
                    }

                    if (this.startDate && this.endDate) {
                        filtered = filtered.filter(claim => {
                            const claimDate = claim.date_of_claim.split(' ')[0];
                            return claimDate >= this.startDate && claimDate <= this.endDate;
                        });
                    } else if (this.startDate) {
                        filtered = filtered.filter(claim => {
                            const claimDate = claim.date_of_claim.split(' ')[0];
                            return claimDate >= this.startDate;
                        });
                    } else if (this.endDate) {
                        filtered = filtered.filter(claim => {
                            const claimDate = claim.date_of_claim.split(' ')[0];
                            return claimDate <= this.endDate;
                        });
                    }

                    filtered.sort((a, b) => {
                        switch (this.sortBy) {
                            case 'date':
                                return new Date(b.date_of_claim) - new Date(a.date_of_claim);
                            case 'client':
                                return this.getClientName(a.client_id).localeCompare(this.getClientName(b.client_id));
                            case 'amount':
                                return parseFloat(b.amount) - parseFloat(a.amount);
                            case 'remaining':
                                return this.getClaimRemaining(b.id) - this.getClaimRemaining(a.id);
                            default:
                                return 0;
                        }
                    });

                    return filtered;
                },

                paginatedClaims() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredClaims.slice(start, end);
                },

                totalPages() {
                    return Math.ceil(this.filteredClaims.length / this.itemsPerPage);
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
                    return Math.min(this.currentPage * this.itemsPerPage, this.filteredClaims.length);
                },

                totalItems() {
                    return this.filteredClaims.length;
                },

                totalClaims() {
                    return this.filteredClaims.reduce((sum, claim) => sum + parseFloat(claim.amount), 0);
                },

                totalPaid() {
                    return this.filteredClaims.reduce((sum, claim) => {
                        return sum + this.getClaimPaid(claim.id);
                    }, 0);
                },

                totalRemaining() {
                    return this.filteredClaims.reduce((sum, claim) => {
                        return sum + this.getClaimRemaining(claim.id);
                    }, 0);
                },

                selectedClaimPaid() {
                    if (!this.selectedClaim) return 0;
                    return this.getClaimPaid(this.selectedClaim.id);
                },

                selectedClaimRemaining() {
                    if (!this.selectedClaim) return 0;
                    return this.getClaimRemaining(this.selectedClaim.id);
                }
            },

            methods: {
                async fetchClients() {
                    try {
                        const response = await api.get('?action=allClients');
                        this.clients = response.data;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des clients:', error);
                    }
                },

                async fetchSales() {
                    try {
                        const response = await api.get('?action=allSales');
                        this.sales = response.data;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des ventes:', error);
                    }
                },

                async fetchClaimsPayments() {
                    try {
                        const response = await api.get('?action=allClaimsPayments');
                        this.claimsPayments = response.data;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des paiements:', error);
                    }
                },

                filterClients() {
                    // La filtration est gérée par computed
                },

                clearClientFilters() {
                    this.clientSearchTerm = '';
                    this.clientSortBy = 'name';
                },

                showClientDetails(client) {
                    this.selectedClient = client;
                    this.clientClaims = this.claims.filter(c => c.client_id === client.id);
                    this.clientSales = this.sales.filter(s => s.client_id === client.id);
                    this.showClientDetailsModal = true;
                },

                closeClientDetailsModal() {
                    this.showClientDetailsModal = false;
                    this.selectedClient = null;
                },

                previousClientPage() {
                    if (this.currentClientPage > 1) {
                        this.currentClientPage--;
                    }
                },

                nextClientPage() {
                    if (this.currentClientPage < this.totalClientPages) {
                        this.currentClientPage++;
                    }
                },

                goToClientPage(page) {
                    this.currentClientPage = page;
                },

                async fetchClaims() {
                    try {
                        const response = await api.get('?action=allClaims');
                        this.claims = response.data;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des créances:', error);
                    }
                },

                getClientName(clientId) {
                    const client = this.clients.find(c => c.id === clientId);
                    return client ? client.name : 'N/A';
                },

                getClientPhone(clientId) {
                    const client = this.clients.find(c => c.id === clientId);
                    return client ? client.phone : 'N/A';
                },

                getClaimPaid(claimId) {
                    const payments = this.claimsPayments.filter(p => p.claim_id === claimId);
                    return payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
                },

                getClaimRemaining(claimId) {
                    const claim = this.claims.find(c => c.id === claimId);
                    if (!claim) return 0;
                    const paid = this.getClaimPaid(claimId);
                    return parseFloat(claim.amount) - paid;
                },

                getStatusInfo(claim) {
                    const remaining = this.getClaimRemaining(claim.id);

                    if (remaining === 0) {
                        return {
                            label: 'Soldé',
                            class: 'bg-green-100 text-green-800 border border-green-200',
                            icon: 'fas fa-check-circle'
                        };
                    } else if (this.isOverdue(claim)) {
                        return {
                            label: 'En retard',
                            class: 'bg-red-100 text-red-800 border border-red-200',
                            icon: 'fas fa-exclamation-triangle'
                        };
                    } else {
                        return {
                            label: 'Actif',
                            class: 'bg-blue-100 text-blue-800 border border-blue-200',
                            icon: 'fas fa-clock'
                        };
                    }
                },

                isOverdue(claim) {
                    const remaining = this.getClaimRemaining(claim.id);
                    return new Date(claim.due_date) < new Date() && remaining > 0;
                },

                applyFilters() {
                    this.currentPage = 1;
                },

                clearFilters() {
                    this.searchTerm = '';
                    this.sortBy = 'date';
                    this.exactDate = '';
                    this.startDate = '';
                    this.endDate = '';
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

                openNewClaimModal() {
                    this.showNewClaimModal = true;
                    this.newClaim = {
                        client_id: null,
                        amount: '',
                        date_of_claim: new Date().toISOString().split('T')[0],
                        due_date: '',
                        notes: ''
                    };
                    this.newClaimClientSearch = '';
                    this.filteredNewClaimClients = [];
                },

                closeNewClaimModal() {
                    this.showNewClaimModal = false;
                    this.showNewClaimClientDropdown = false;
                },

                filterNewClaimClients() {
                    if (!this.newClaimClientSearch) {
                        this.filteredNewClaimClients = this.clients;
                        return;
                    }
                    const search = this.newClaimClientSearch.toLowerCase();
                    this.filteredNewClaimClients = this.clients.filter(client =>
                        client.name.toLowerCase().includes(search) ||
                        client.phone.includes(search)
                    );
                },

                selectNewClaimClient(client) {
                    this.newClaim.client_id = client.id;
                    this.newClaimClientSearch = client.name;
                    this.showNewClaimClientDropdown = false;
                },

                async addNewClaim() {
                    if (!this.newClaim.client_id) {
                        alert('Veuillez sélectionner un client');
                        return;
                    }

                    const url = '?action=newClaim';

                    // LOGS
                    console.log('URL appelée :', url);
                    console.log('Données envoyées :', JSON.stringify(this.newClaim));

                    try {
                        const response = await api.post(url, this.newClaim);

                        console.log('Réponse API :', response.data);

                        if (response.data.success) {
                            alert('Créance ajoutée avec succès!');
                            this.closeNewClaimModal();
                            await this.fetchClaims();
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'ajout de la créance:', error);
                        console.error('Détails requête:', {
                            url,
                            payload: this.newClaim
                        });
                        alert('Erreur lors de l\'ajout de la créance');
                    }
                },

                async showPaymentHistory(claim) {
                    this.selectedClaim = claim;

                    try {
                        const response = await api.get('?action=allClaimsPayments');
                        this.claimPayments = response.data.filter(p => p.claim_id === claim.id);
                        this.showPaymentHistoryModal = true;
                    } catch (error) {
                        console.error('Erreur lors de la récupération des paiements:', error);
                        this.claimPayments = [];
                        this.showPaymentHistoryModal = true;
                    }
                },

                closePaymentHistoryModal() {
                    this.showPaymentHistoryModal = false;
                    this.selectedClaim = null;
                    this.claimPayments = [];
                },

                openNewPaymentModal() {
                    this.showNewPaymentModal = true;
                    this.newPayment = {
                        amount: '',
                        date: new Date().toISOString().split('T')[0],
                        payment_method: '',
                        notes: ''
                    };
                },

                closeNewPaymentModal() {
                    this.showNewPaymentModal = false;
                },

                async addNewPayment() {
                    if (this.newPayment.amount > this.selectedClaimRemaining) {
                        alert('Le montant du paiement ne peut pas dépasser le montant restant');
                        return;
                    }

                    if (this.newPayment.amount <= 0) {
                        alert('Le montant doit être supérieur à 0');
                        return;
                    }

                    const url = '?action=newClaimPayment';

                    const payload = {
                        claim_id: this.selectedClaim.id,
                        amount: this.newPayment.amount,
                        date: this.newPayment.date,
                        payment_method: this.newPayment.payment_method,
                        notes: this.newPayment.notes
                    };

                    // LOGS
                    console.log('URL appelée :', url);
                    console.log('Données envoyées :', JSON.stringify(payload));

                    try {
                        const response = await api.post(url, payload);

                        console.log('Réponse API :', response.data);

                        if (response.data.success) {
                            alert('Paiement ajouté avec succès!');
                            this.closeNewPaymentModal();
                            await this.fetchClaimsPayments();
                            await this.showPaymentHistory(this.selectedClaim);
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue'));
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'ajout du paiement:', error);
                        console.error('Détails requête :', {
                            url,
                            payload
                        });
                        alert('Erreur lors de l\'ajout du paiement');
                    }
                },

                async deleteClaim(claimId) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette créance?')) {
                        return;
                    }

                    try {
                        const response = await api.post('?action=deleteClaim', {
                            id: claimId
                        });
                        if (response.data.success) {
                            alert('Créance supprimée avec succès!');
                            await this.fetchClaims();
                            await this.fetchClaimsPayments();
                        } else {
                            alert('Erreur lors de la suppression');
                        }
                    } catch (error) {
                        console.error('Erreur lors de la suppression:', error);
                        alert('Erreur lors de la suppression de la créance');
                    }
                },

                printPaymentHistory() {
                    if (!this.selectedClaim) return;

                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Historique des Paiements</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                .client-info { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #e8f4fd; border: 1px solid #bee5eb; }
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
                                Lokossa, Quinji carrefour Abo, <br>
                                téléphone 01 49 91 65 66</p>
                                <h2>Historique des paiements</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="client-info">
                                <h3>Informations du client:</h3>
                                <p><strong>Nom:</strong> ${this.getClientName(this.selectedClaim.client_id)}</p>
                                <p><strong>Téléphone:</strong> ${this.getClientPhone(this.selectedClaim.client_id)}</p>
                                <p><strong>Date de la créance:</strong> ${this.formatDate(this.selectedClaim.date_of_claim)}</p>
                                <p><strong>Date d'échéance:</strong> ${this.formatDate(this.selectedClaim.due_date)}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé financier:</h3>
                                <p><strong>Montant initial:</strong> ${this.formatCurrency(this.selectedClaim.amount)}</p>
                                <p><strong>Montant payé:</strong> ${this.formatCurrency(this.selectedClaimPaid)}</p>
                                <p><strong>Montant restant:</strong> ${this.formatCurrency(this.selectedClaimRemaining)}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Moyen de paiement</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    if (this.claimPayments.length === 0) {
                        printContent += `
                            <tr>
                                <td colspan="4" style="text-align: center; font-style: italic;">Aucun paiement enregistré</td>
                            </tr>`;
                    } else {
                        this.claimPayments.forEach(payment => {
                            printContent += `
                                <tr>
                                    <td>${this.formatDate(payment.date)}</td>
                                    <td>${this.formatCurrency(payment.amount)}</td>
                                    <td>${payment.payment_method || '-'}</td>
                                    <td>${payment.notes || '-'}</td>
                                </tr>`;
                        });
                    }

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

                printClaimHistory(claim) {
                    this.selectedClaim = claim;
                    api.get('?action=allClaimsPayments')
                        .then(response => {
                            this.claimPayments = response.data.filter(p => p.claim_id === claim.id);
                            this.printPaymentHistory();
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            this.claimPayments = [];
                            this.printPaymentHistory();
                        });
                },

                printClaimsList() {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Liste des Créances</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #e8f4fd; border: 1px solid #bee5eb; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, <br>
                                téléphone 01 49 91 65 66</p>
                                <h2>LISTE DES CRÉANCES</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Nombre de créances:</strong> ${this.filteredClaims.length}</p>
                                <p><strong>Total créances:</strong> ${this.formatCurrency(this.totalClaims)}</p>
                                <p><strong>Total payé:</strong> ${this.formatCurrency(this.totalPaid)}</p>
                                <p><strong>Total restant:</strong> ${this.formatCurrency(this.totalRemaining)}</p>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th>Montant initial</th>
                                        <th>Payé</th>
                                        <th>Restant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredClaims.forEach(claim => {
                        printContent += `
                            <tr>
                                <td>${this.getClientName(claim.client_id)}</td>
                                <td>${this.formatDate(claim.date_of_claim)}</td>
                                <td>${this.formatDate(claim.due_date)}</td>
                                <td>${this.formatCurrency(claim.amount)}</td>
                                <td>${this.formatCurrency(this.getClaimPaid(claim.id))}</td>
                                <td>${this.formatCurrency(this.getClaimRemaining(claim.id))}</td>
                                <td>${this.getStatusInfo(claim).label}</td>
                            </tr>`;
                    });

                    printContent += `
                                </tbody>
                            </table>
                            <div class="footer">
                                <p>Merci pour votre confiance!</p>
                                <p>GBEMIRO - Lokossa, Quinji carrefour Abo - Tél: 01 49 91 65 66</p>
                                <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                            </div>
                        </body>
                        </html>`;

                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                },

                printClientDetails() {
                    if (!this.selectedClient) return;

                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Détails Client - ${this.selectedClient.name}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                                .client-info { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd; }
                                .summary { margin: 20px 0; padding: 15px; background-color: #e8f4fd; border: 1px solid #bee5eb; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
                                th { background-color: #f0f0f0; font-weight: bold; }
                                .section-title { margin-top: 30px; margin-bottom: 15px; font-size: 16px; font-weight: bold; color: #333; }
                                .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #ddd; padding-top: 20px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>GBEMIRO</h1>
                                <p>Commerçialisation de boissons en gros et en détail<br>
                                Lokossa, Quinji carrefour Abo, <br>
                                téléphone 01 49 91 65 66</p>
                                <h2>DÉTAILS DU CLIENT</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="client-info">
                                <h3>Informations du client:</h3>
                                <p><strong>Nom:</strong> ${this.selectedClient.name}</p>
                                <p><strong>Téléphone:</strong> ${this.selectedClient.phone}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé financier:</h3>
                                <p><strong>Dette totale:</strong> ${this.formatCurrency(this.selectedClient.totalDebt)}</p>
                                <p><strong>Total achats:</strong> ${this.formatCurrency(this.selectedClient.totalSales)}</p>
                                <p><strong>Nombre d'achats:</strong> ${this.selectedClient.salesCount}</p>
                                <p><strong>Créances actives:</strong> ${this.selectedClient.activeClaims}</p>
                            </div>
                            
                            <h3 class="section-title">CRÉANCES (${this.clientClaims.length})</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Échéance</th>
                                        <th>Montant</th>
                                        <th>Payé</th>
                                        <th>Restant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    if (this.clientClaims.length === 0) {
                        printContent += `
                            <tr>
                                <td colspan="6" style="text-align: center; font-style: italic;">Aucune créance</td>
                            </tr>`;
                    } else {
                        this.clientClaims.forEach(claim => {
                            printContent += `
                                <tr>
                                    <td>${this.formatDate(claim.date_of_claim)}</td>
                                    <td>${this.formatDate(claim.due_date)}</td>
                                    <td>${this.formatCurrency(claim.amount)}</td>
                                    <td>${this.formatCurrency(this.getClaimPaid(claim.id))}</td>
                                    <td>${this.formatCurrency(this.getClaimRemaining(claim.id))}</td>
                                    <td>${this.getStatusInfo(claim).label}</td>
                                </tr>`;
                        });
                    }

                    printContent += `
                                </tbody>
                            </table>
                            
                            <h3 class="section-title">HISTORIQUE DES ACHATS (${this.clientSales.length})</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>N° Facture</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    if (this.clientSales.length === 0) {
                        printContent += `
                            <tr>
                                <td colspan="4" style="text-align: center; font-style: italic;">Aucun achat</td>
                            </tr>`;
                    } else {
                        this.clientSales.forEach(sale => {
                            printContent += `
                                <tr>
                                    <td>#${sale.id}</td>
                                    <td>${this.formatDate(sale.date_of_insertion)}</td>
                                    <td>${this.formatCurrency(sale.total)}</td>
                                    <td>${sale.status}</td>
                                </tr>`;
                        });
                    }

                    printContent += `
                                </tbody>
                            </table>
                            
                            <div class="footer">
                                <p>Merci pour votre confiance!</p>
                                <p>GBEMIRO - Lokossa, Quinji carrefour Abo - Tél: 01 49 91 65 66</p>
                                <p>Document généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
                            </div>
                        </body>
                        </html>`;

                    printWindow.document.write(printContent);
                    printWindow.document.close();
                    printWindow.print();
                },

                editPayment(payment) {
                    this.editingPayment = {
                        ...payment
                    };
                    this.showEditPaymentModal = true;
                },

                closeEditPaymentModal() {
                    this.showEditPaymentModal = false;
                    this.editingPayment = null;
                },

                async updatePayment() {
                    const {
                        id,
                        amount,
                        date,
                        payment_method,
                        notes
                    } = this.editingPayment;

                    if (amount <= 0) {
                        alert('Le montant doit être supérieur à 0');
                        return;
                    }

                    const route = '?action=updateClaimPayment';
                    const payload = {
                        id,
                        amount,
                        date,
                        payment_method,
                        notes
                    };

                    // LOG AVANT REQUÊTE
                    console.log('➡️ Route API :', route);
                    console.log('📤 Données envoyées :', payload);

                    try {
                        const response = await api.post(route, payload);

                        // LOG RÉPONSE
                        console.log('📥 Réponse API :', response.data);

                        if (response.data.success) {
                            alert('Paiement modifié avec succès!');
                            this.closeEditPaymentModal();
                            await Promise.all([
                                this.fetchClaimsPayments(),
                                this.showPaymentHistory(this.selectedClaim)
                            ]);
                        } else {
                            alert('Erreur: ' + (response.data.message || 'Une erreur est survenue'));
                        }
                    } catch (error) {
                        // LOG ERREUR
                        console.error('❌ Erreur API updateClaimPayment', {
                            route,
                            payload,
                            error: error.response?.data || error
                        });

                        alert('Erreur lors de la modification du paiement');
                    }
                },

                formatDate(date) {
                    if (!date) return '';
                    const d = new Date(date);
                    if (isNaN(d)) return '';
                    return d.toLocaleDateString('fr-FR');
                },

                formatCurrency(amount) {
                    const number = parseFloat(amount);
                    const roundedAmount = Math.round(isNaN(number) ? 0 : number);
                    return `${roundedAmount.toLocaleString('fr-FR')} FCFA`;
                }
            }
        }).mount('#app');
    </script>
</body>

</html>