<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tossin - Gestion des Créances</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
</head>
<body class="bg-gray-50 min-h-screen">
     Sidebar 
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
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
            <button onclick="logout()" class="w-full flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                <span>Déconnexion</span>
            </button>
        </div>
    </div>

     Mobile menu button 
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button onclick="toggleSidebar()" class="bg-white p-2 rounded-lg shadow-lg">
            <i class="fas fa-bars text-gray-700"></i>
        </button>
    </div>

     Main content 
    <div class="lg:ml-64 min-h-screen">
         Header 
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                    <h1 class="text-2xl font-bold text-gray-900">Gestion des Créances</h1>
                    <button onclick="openNewClientModal()" 
                            class="bg-accent hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>Nouveau client
                    </button>
                </div>
            </div>
        </header>

         Filters and search 
        <div class="p-6">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                        <input type="text" id="searchInput" placeholder="Nom du client..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                        <select id="sortSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="name">Nom</option>
                            <option value="amount">Montant de la dette</option>
                            <option value="date">Date de la dette</option>
                            <option value="overdue">Échéance dépassée</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="all">Tous</option>
                            <option value="active">Actif</option>
                            <option value="overdue">En retard</option>
                            <option value="paid">Soldé</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="applyFilters()" class="w-full bg-primary hover:bg-secondary text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-filter mr-2"></i>Filtrer
                        </button>
                    </div>
                </div>
            </div>

             Clients table 
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="clientsTable">
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
                        <tbody id="clientsTableBody" class="bg-white divide-y divide-gray-200">
                             Data will be populated by JavaScript 
                        </tbody>
                    </table>
                </div>

                 Pagination 
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button onclick="previousPage()" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Précédent
                        </button>
                        <button onclick="nextPage()" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Suivant
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Affichage de <span id="startItem">1</span> à <span id="endItem">10</span> sur <span id="totalItems">0</span> résultats
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="pagination">
                                 Pagination buttons will be generated by JavaScript 
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     Modal Nouveau Client 
    <div id="newClientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-user-plus mr-2"></i>Nouveau Client
                    </h3>
                    <button onclick="closeNewClientModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="newClientForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user mr-1"></i>Nom du client
                            </label>
                            <input type="text" id="clientName" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-1"></i>Téléphone
                            </label>
                            <input type="tel" id="clientPhone" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </label>
                            <input type="email" id="clientEmail" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-1"></i>Date de la dette
                            </label>
                            <input type="date" id="debtDate" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-money-bill-wave mr-1"></i>Montant initial (XOF)
                            </label>
                            <input type="number" id="initialAmount" required min="0" step="1000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i>Date d'échéance
                            </label>
                            <input type="date" id="dueDate" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note mr-1"></i>Notes
                        </label>
                        <textarea id="clientNotes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-accent hover:bg-yellow-600 text-white py-3 px-4 rounded-lg transition-colors font-medium">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                        <button type="button" onclick="closeNewClientModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-3 px-4 rounded-lg transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     Modal Historique des Paiements 
    <div id="paymentHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-history mr-2"></i>Historique des Paiements
                    </h3>
                    <button onclick="closePaymentHistoryModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="paymentHistoryContent">
                     Content will be populated by JavaScript 
                </div>
            </div>
        </div>
    </div>

     Modal Nouveau Paiement 
    <div id="newPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-money-bill-wave mr-2"></i>Nouveau Paiement
                    </h3>
                    <button onclick="closeNewPaymentModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="newPaymentForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave mr-1"></i>Montant (XOF)
                        </label>
                        <input type="number" id="paymentAmount" required min="0" step="1000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-1"></i>Date de paiement
                        </label>
                        <input type="date" id="paymentDate" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-credit-card mr-1"></i>Moyen de paiement
                        </label>
                        <select id="paymentMethod" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
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
                        <textarea id="paymentNotes" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors font-medium">
                            <i class="fas fa-save mr-2"></i>Enregistrer
                        </button>
                        <button type="button" onclick="closeNewPaymentModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let clients = [
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
            {
                id: 2,
                name: "SARL Beta",
                phone: "+221 78 987 65 43",
                email: "admin@beta.sn",
                debtDate: "2024-02-20",
                initialAmount: 750000,
                remainingAmount: 750000,
                dueDate: "2024-04-20",
                status: "active",
                notes: "Nouveau client",
                payments: []
            },
            {
                id: 3,
                name: "Gamma Industries",
                phone: "+221 76 555 44 33",
                email: "finance@gamma.sn",
                debtDate: "2024-01-05",
                initialAmount: 1200000,
                remainingAmount: 0,
                dueDate: "2024-02-05",
                status: "paid",
                notes: "Payé intégralement",
                payments: [
                    { date: "2024-01-25", amount: 600000, method: "cheque", notes: "Premier versement" },
                    { date: "2024-02-03", amount: 600000, method: "especes", notes: "Solde final" }
                ]
            },
            {
                id: 4,
                name: "Delta Corp",
                phone: "+221 77 888 99 00",
                email: "comptabilite@delta.sn",
                debtDate: "2023-12-10",
                initialAmount: 300000,
                remainingAmount: 300000,
                dueDate: "2024-01-10",
                status: "overdue",
                notes: "En retard de paiement",
                payments: []
            },
            {
                id: 5,
                name: "Epsilon SUARL",
                phone: "+221 78 111 22 33",
                email: "direction@epsilon.sn",
                debtDate: "2024-03-01",
                initialAmount: 450000,
                remainingAmount: 225000,
                dueDate: "2024-05-01",
                status: "active",
                notes: "Paiement en cours",
                payments: [
                    { date: "2024-03-15", amount: 225000, method: "mobile", notes: "Acompte 50%" }
                ]
            }
        ];

        let currentPage = 1;
        let itemsPerPage = 10;
        let filteredClients = [...clients];
        let currentClientId = null;

        function getStatusInfo(status) {
            const statusMap = {
                'active': { label: 'Actif', class: 'text-blue-600 bg-blue-100' },
                'overdue': { label: 'En retard', class: 'text-red-600 bg-red-100' },
                'paid': { label: 'Soldé', class: 'text-green-600 bg-green-100' }
            };
            return statusMap[status] || { label: status, class: 'text-gray-600 bg-gray-100' };
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR').format(amount) + ' XOF';
        }

        function renderClients() {
            const tbody = document.getElementById('clientsTableBody');
            tbody.innerHTML = '';
            
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageClients = filteredClients.slice(startIndex, endIndex);
            
            pageClients.forEach(client => {
                const row = document.createElement('tr');
                const statusInfo = getStatusInfo(client.status);
                const isOverdue = new Date(client.dueDate) < new Date() && client.remainingAmount > 0;
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap" data-label="Client">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${client.name}</div>
                                <div class="text-sm text-gray-500">${client.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Contact">
                        <i class="fas fa-phone mr-1"></i>${client.phone}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Date dette">
                        ${new Date(client.debtDate).toLocaleDateString('fr-FR')}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" data-label="Montant initial">
                        ${formatCurrency(client.initialAmount)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Montant restant">
                        <span class="${client.remainingAmount > 0 ? 'text-red-600' : 'text-green-600'}">
                            ${formatCurrency(client.remainingAmount)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap" data-label="Statut">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusInfo.class}">
                            ${statusInfo.label}
                        </span>
                        ${isOverdue ? '<div class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>Échéance dépassée</div>' : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-label="Actions">
                        <button onclick="showPaymentHistory(${client.id})" class="text-primary hover:text-secondary mr-3" title="Historique">
                            <i class="fas fa-history"></i>
                        </button>
                        <button onclick="openNewPaymentModal(${client.id})" class="text-green-600 hover:text-green-800 mr-3" title="Nouveau paiement">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                        <button onclick="deleteClient(${client.id})" class="text-red-600 hover:text-red-800" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            updatePagination();
        }

        function updatePagination() {
            const totalItems = filteredClients.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const startItem = (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalItems);
            
            document.getElementById('startItem').textContent = startItem;
            document.getElementById('endItem').textContent = endItem;
            document.getElementById('totalItems').textContent = totalItems;
            
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            
            const prevButton = document.createElement('button');
            prevButton.onclick = () => previousPage();
            prevButton.disabled = currentPage === 1;
            prevButton.className = `relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${currentPage === 1 ? 'cursor-not-allowed' : ''}`;
            prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
            pagination.appendChild(prevButton);
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    const pageButton = document.createElement('button');
                    pageButton.onclick = () => goToPage(i);
                    pageButton.className = `relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                        i === currentPage 
                            ? 'z-10 bg-primary border-primary text-white' 
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    }`;
                    pageButton.textContent = i;
                    pagination.appendChild(pageButton);
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    const dots = document.createElement('span');
                    dots.className = 'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700';
                    dots.textContent = '...';
                    pagination.appendChild(dots);
                }
            }
            
            const nextButton = document.createElement('button');
            nextButton.onclick = () => nextPage();
            nextButton.disabled = currentPage === totalPages;
            nextButton.className = `relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${currentPage === totalPages ? 'cursor-not-allowed' : ''}`;
            nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
            pagination.appendChild(nextButton);
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                renderClients();
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(filteredClients.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderClients();
            }
        }

        function goToPage(page) {
            currentPage = page;
            renderClients();
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const sortBy = document.getElementById('sortSelect').value;
            
            filteredClients = clients.filter(client => {
                const matchesSearch = client.name.toLowerCase().includes(searchTerm) || 
                                    client.phone.includes(searchTerm) ||
                                    client.email.toLowerCase().includes(searchTerm);
                const matchesStatus = statusFilter === 'all' || client.status === statusFilter;
                
                return matchesSearch && matchesStatus;
            });
            
            filteredClients.sort((a, b) => {
                switch (sortBy) {
                    case 'name':
                        return a.name.localeCompare(b.name);
                    case 'amount':
                        return b.remainingAmount - a.remainingAmount;
                    case 'date':
                        return new Date(a.debtDate) - new Date(b.debtDate);
                    case 'overdue':
                        const aOverdue = new Date(a.dueDate) < new Date() && a.remainingAmount > 0;
                        const bOverdue = new Date(b.dueDate) < new Date() && b.remainingAmount > 0;
                        return bOverdue - aOverdue;
                    default:
                        return 0;
                }
            });
            
            currentPage = 1;
            renderClients();
        }

        function openNewClientModal() {
            document.getElementById('newClientModal').classList.remove('hidden');
            document.getElementById('debtDate').value = new Date().toISOString().split('T')[0];
        }

        function closeNewClientModal() {
            document.getElementById('newClientModal').classList.add('hidden');
            document.getElementById('newClientForm').reset();
        }

        function showPaymentHistory(clientId) {
            const client = clients.find(c => c.id === clientId);
            const content = document.getElementById('paymentHistoryContent');
            
            content.innerHTML = `
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-2">${client.name}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">Montant initial</p>
                            <p class="text-lg font-semibold text-blue-600">${formatCurrency(client.initialAmount)}</p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">Montant payé</p>
                            <p class="text-lg font-semibold text-green-600">${formatCurrency(client.initialAmount - client.remainingAmount)}</p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-600">Montant restant</p>
                            <p class="text-lg font-semibold text-red-600">${formatCurrency(client.remainingAmount)}</p>
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
                            ${client.payments.length > 0 ? client.payments.map(payment => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">${new Date(payment.date).toLocaleDateString('fr-FR')}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-green-600">${formatCurrency(payment.amount)}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">${payment.method}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">${payment.notes || '-'}</td>
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Aucun paiement enregistré</p>
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('paymentHistoryModal').classList.remove('hidden');
        }

        function closePaymentHistoryModal() {
            document.getElementById('paymentHistoryModal').classList.add('hidden');
        }

        function openNewPaymentModal(clientId) {
            currentClientId = clientId;
            document.getElementById('newPaymentModal').classList.remove('hidden');
            document.getElementById('paymentDate').value = new Date().toISOString().split('T')[0];
        }

        function closeNewPaymentModal() {
            document.getElementById('newPaymentModal').classList.add('hidden');
            document.getElementById('newPaymentForm').reset();
            currentClientId = null;
        }

        function deleteClient(clientId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                clients = clients.filter(c => c.id !== clientId);
                applyFilters();
            }
        }

        document.getElementById('newClientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newClient = {
                id: clients.length + 1,
                name: document.getElementById('clientName').value,
                phone: document.getElementById('clientPhone').value,
                email: document.getElementById('clientEmail').value,
                debtDate: document.getElementById('debtDate').value,
                initialAmount: parseFloat(document.getElementById('initialAmount').value),
                remainingAmount: parseFloat(document.getElementById('initialAmount').value),
                dueDate: document.getElementById('dueDate').value,
                status: 'active',
                notes: document.getElementById('clientNotes').value,
                payments: []
            };
            
            clients.push(newClient);
            applyFilters();
            closeNewClientModal();
        });

        document.getElementById('newPaymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const client = clients.find(c => c.id === currentClientId);
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value);
            
            if (paymentAmount > client.remainingAmount) {
                alert('Le montant du paiement ne peut pas dépasser le montant restant');
                return;
            }
            
            const payment = {
                date: document.getElementById('paymentDate').value,
                amount: paymentAmount,
                method: document.getElementById('paymentMethod').value,
                notes: document.getElementById('paymentNotes').value
            };
            
            client.payments.push(payment);
            client.remainingAmount -= paymentAmount;
            
            if (client.remainingAmount === 0) {
                client.status = 'paid';
            }
            
            applyFilters();
            closeNewPaymentModal();
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                localStorage.removeItem('tossin_user');
                window.location.href = 'login.html';
            }
        }

        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('sortSelect').addEventListener('change', applyFilters);

        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuButton = e.target.closest('button');
            
            if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !menuButton) {
                sidebar.classList.add('-translate-x-full');
            }
        });

        renderClients();
    </script>
</body>
</html>
