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
    <title>Notifications - Gbemiro</title>
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
            .notification-card {
                padding: 12px;
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

        .notification-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .notification-depense {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .notification-creance {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .notification-vente {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .notification-stock {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .notification-default {
            background-color: #F3F4F6;
            color: #374151;
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
                                <i class="fas fa-bell mr-2"></i>Notifications
                            </h1>
                            <div class="flex space-x-3">
                                <button @click="printNotificationsList" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-print mr-2"></i>Imprimer
                                </button>
                                <button @click="markAllAsRead" class="bg-accent hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                                    <i class="fas fa-check-double mr-2"></i>Tout marquer comme lu
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
                                <p class="text-sm text-gray-600 mb-1">Total notifications</p>
                                <p class="text-2xl font-bold text-blue-600">{{ notifications.length }}</p>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Dépenses</p>
                                <p class="text-2xl font-bold text-red-600">{{ depenseCount }}</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Créances</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ creanceCount }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Ventes</p>
                                <p class="text-2xl font-bold text-green-600">{{ venteCount }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input v-model="searchTerm" @input="applyFilters" type="text" placeholder="Rechercher dans les notifications..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                                <select v-model="typeFilter" @change="applyFilters" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="all">Tous les types</option>
                                    <option value="depense">Dépenses</option>
                                    <option value="creance">Créances</option>
                                    <option value="vente">Ventes</option>
                                    <option value="stock">Stock</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                                <input v-model="dateFrom" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                                <input v-model="dateTo" @change="applyFilters" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div class="flex items-end">
                                <button @click="resetFilters" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-redo mr-2"></i>Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des notifications -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div v-if="paginatedNotifications.length === 0" class="text-center py-12 text-gray-500">
                                <i class="fas fa-inbox text-6xl mb-4 opacity-50"></i>
                                <p class="text-lg">Aucune notification à afficher</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="notification in paginatedNotifications" :key="notification.id"
                                    class="notification-card border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span :class="['notification-badge', getNotificationClass(notification.comment)]">
                                                    <i :class="['fas', getNotificationIcon(notification.comment), 'mr-1']"></i>
                                                    {{ getNotificationType(notification.comment) }}
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ formatDate(notification.created_at) }}
                                                </span>
                                            </div>
                                            <p class="text-gray-900">{{ notification.comment }}</p>
                                        </div>
                                        <div class="flex space-x-2 ml-4">
                                            <button @click="deleteNotification(notification.id)"
                                                class="text-red-600 hover:text-red-800 text-lg"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

            <!-- Zone d'impression cachée -->
            <div class="print-area hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">GBEMIRO</h1>
                        <p class="text-gray-600">Commerçialisation de boissons en gros et en détail</p>
                        <p class="text-gray-600">Lokossa, Quinji carrefour Abo, téléphone 0149916566</p>
                        <h2 class="text-2xl font-bold text-gray-900 mt-6 mb-2">LISTE DES NOTIFICATIONS</h2>
                        <p class="text-gray-600">Date d'impression: {{ new Date().toLocaleDateString('fr-FR') }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-2">Résumé:</h3>
                        <p><strong>Nombre total de notifications:</strong> {{ filteredNotifications.length }}</p>
                        <p><strong>Période:</strong> {{ dateFrom || 'Début' }} - {{ dateTo || 'Aujourd\'hui' }}</p>
                    </div>

                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Type</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                                <th class="border border-gray-300 px-4 py-2 text-left">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="notification in filteredNotifications" :key="notification.id">
                                <td class="border border-gray-300 px-4 py-2">#{{ notification.id }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ getNotificationType(notification.comment) }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ formatDate(notification.created_at) }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ notification.comment }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-8 pt-4 border-t border-gray-300 text-center">
                        <p class="text-sm text-gray-600">Merci pour votre confiance!</p>
                        <p class="text-sm text-gray-600">Document généré le {{ new Date().toLocaleDateString('fr-FR') }} à {{ new Date().toLocaleTimeString('fr-FR') }}</p>
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
                    notifications: [],
                    filteredNotifications: [],
                    searchTerm: '',
                    typeFilter: 'all',
                    dateFrom: '',
                    dateTo: '',
                    currentPage: 1,
                    itemsPerPage: 10
                }
            },
            computed: {
                paginatedNotifications() {
                    const start = (this.currentPage - 1) * this.itemsPerPage;
                    const end = start + this.itemsPerPage;
                    return this.filteredNotifications.slice(start, end);
                },
                totalPages() {
                    return Math.ceil(this.filteredNotifications.length / this.itemsPerPage);
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
                    return Math.min(this.currentPage * this.itemsPerPage, this.filteredNotifications.length);
                },
                totalItems() {
                    return this.filteredNotifications.length;
                },
                depenseCount() {
                    return this.notifications.filter(n => n.comment.toLowerCase().includes('dépense')).length;
                },
                creanceCount() {
                    return this.notifications.filter(n => n.comment.toLowerCase().includes('créance')).length;
                },
                venteCount() {
                    return this.notifications.filter(n => n.comment.toLowerCase().includes('vente')).length;
                }
            },
            mounted() {
                this.loadNotifications();
            },
            methods: {
                loadNotifications() {
                    const route = '?action=allNotifications';

                    console.log('[API] GET →', api.defaults.baseURL + route);

                    api.get(route)
                        .then(response => {
                            console.log('[API] Réponse notifications :', response.data);

                            if (response.data.success) {
                                this.notifications = response.data.notifications || [];
                                this.applyFilters();
                            } else {
                                this.notifications = [];
                            }
                        })
                        .catch(error => {
                            console.error('[API] Erreur notifications :', error);
                            this.notifications = [];
                        });
                },

                applyFilters() {
                    let filtered = [...this.notifications];

                    // Recherche
                    if (this.searchTerm) {
                        const term = this.searchTerm.toLowerCase();
                        filtered = filtered.filter(n =>
                            n.comment?.toLowerCase().includes(term) ||
                            n.id?.toString().includes(term)
                        );
                    }

                    // Filtre par type
                    if (this.typeFilter !== 'all') {
                        filtered = filtered.filter(n => {
                            const comment = n.comment.toLowerCase();
                            switch (this.typeFilter) {
                                case 'depense':
                                    return comment.includes('dépense');
                                case 'creance':
                                    return comment.includes('créance');
                                case 'vente':
                                    return comment.includes('vente');
                                case 'stock':
                                    return comment.includes('stock') || comment.includes('produit');
                                default:
                                    return true;
                            }
                        });
                    }

                    // Filtre par date
                    if (this.dateFrom) {
                        const fromDate = new Date(this.dateFrom);
                        filtered = filtered.filter(n => new Date(n.created_at) >= fromDate);
                    }

                    if (this.dateTo) {
                        const toDate = new Date(this.dateTo);
                        toDate.setHours(23, 59, 59);
                        filtered = filtered.filter(n => new Date(n.created_at) <= toDate);
                    }

                    // Tri par date décroissante (plus récent en premier)
                    filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                    this.filteredNotifications = filtered;
                    this.currentPage = 1;
                },
                resetFilters() {
                    this.searchTerm = '';
                    this.typeFilter = 'all';
                    this.dateFrom = '';
                    this.dateTo = '';
                    this.applyFilters();
                },
                getNotificationType(comment) {
                    const lower = comment.toLowerCase();
                    if (lower.includes('dépense')) return 'Dépense';
                    if (lower.includes('créance')) return 'Créance';
                    if (lower.includes('vente')) return 'Vente';
                    if (lower.includes('stock') || lower.includes('produit')) return 'Stock';
                    return 'Général';
                },
                getNotificationClass(comment) {
                    const lower = comment.toLowerCase();
                    if (lower.includes('dépense')) return 'notification-depense';
                    if (lower.includes('créance')) return 'notification-creance';
                    if (lower.includes('vente')) return 'notification-vente';
                    if (lower.includes('stock') || lower.includes('produit')) return 'notification-stock';
                    return 'notification-default';
                },
                getNotificationIcon(comment) {
                    const lower = comment.toLowerCase();
                    if (lower.includes('dépense')) return 'fa-money-bill-wave';
                    if (lower.includes('créance')) return 'fa-hand-holding-usd';
                    if (lower.includes('vente')) return 'fa-shopping-cart';
                    if (lower.includes('stock') || lower.includes('produit')) return 'fa-boxes';
                    return 'fa-info-circle';
                },
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('fr-FR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                deleteNotification(id) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) return;

                    api.post('?action=deleteNotification', {
                            id
                        })
                        .then(response => {
                            if (response.data.success) {
                                alert('Notification supprimée avec succès');
                                this.loadNotifications();
                            } else {
                                alert('Erreur: ' + (response.data.message || 'Erreur inconnue'));
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors de la suppression de la notification :', error);
                            alert('Erreur lors de la suppression de la notification');
                        });
                },
                markAllAsRead() {
                    if (!confirm('Êtes-vous sûr de vouloir marquer toutes les notifications comme lues ?')) return;

                    api.post('?action=markAllNotificationsRead')
                        .then(response => {
                            if (response.data.success) {
                                alert('Toutes les notifications ont été marquées comme lues');
                                this.loadNotifications();
                            } else {
                                alert('Erreur: ' + (response.data.message || 'Erreur inconnue'));
                            }
                        })
                        .catch(error => {
                            console.error('Erreur lors du marquage des notifications :', error);
                            alert('Erreur lors du marquage des notifications');
                        });
                },
                printNotificationsList() {
                    const printWindow = window.open('', '_blank');
                    const currentDate = new Date().toLocaleDateString('fr-FR');

                    let printContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Liste des Notifications</title>
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
                                <h2>LISTE DES NOTIFICATIONS</h2>
                                <p>Date d'impression: ${currentDate}</p>
                            </div>
                            
                            <div class="summary">
                                <h3>Résumé:</h3>
                                <p><strong>Nombre total de notifications:</strong> ${this.filteredNotifications.length}</p>
                                <p><strong>Dépenses:</strong> ${this.depenseCount}</p>
                                <p><strong>Créances:</strong> ${this.creanceCount}</p>
                                <p><strong>Ventes:</strong> ${this.venteCount}</p>
                                ${this.dateFrom ? `<p><strong>Période:</strong> ${this.dateFrom} - ${this.dateTo || 'Aujourd\'hui'}</p>` : ''}
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th style="width: 100px;">Type</th>
                                        <th style="width: 150px;">Date</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    this.filteredNotifications.forEach(notification => {
                        printContent += `
                            <tr>
                                <td>#${notification.id}</td>
                                <td>${this.getNotificationType(notification.comment)}</td>
                                <td>${this.formatDate(notification.created_at)}</td>
                                <td>${notification.comment}</td>
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
                }
            }
        }).mount('#app');
    </script>
</body>

</html>