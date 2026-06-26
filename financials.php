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
    <title>Comptabilité - TOBI LODA</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="icon" type="image/x-icon" href="public/images/logo.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#2563EB', secondary: '#1E40AF', accent: '#F59E0B' }
                }
            }
        }
    </script>
    <style>
        .bg-primary { background-color: #2563EB; }
        .bg-secondary { background-color: #1E40AF; }
        .text-primary { color: #2563EB; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        .solde-pos { color: #059669; }
        .solde-neg { color: #DC2626; }

        @media print {
            body * { visibility: hidden; }
            .print-section, .print-section * { visibility: visible; }
            .print-section { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 1cm; }
            table { font-size: 9px !important; }
            th, td { padding: 3px 5px !important; }
        }

        .print-btn {
            transition: all 0.2s ease;
        }
        .print-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        @media (max-width: 640px) {
            table.r-table { display: block; }
            table.r-table thead { display: none; }
            table.r-table tbody, table.r-table tr { display: block; width: 100%; }
            table.r-table tr {
                margin-bottom: 8px; border: 1px solid #e5e7eb; border-radius: 8px;
                padding: 6px; background: white;
            }
            table.r-table td {
                display: flex; justify-content: space-between; align-items: center;
                padding: 4px 6px; border: none; border-bottom: 1px solid #f3f4f6;
                text-align: right !important; font-size: 0.7rem; gap: 4px;
                word-break: break-word; overflow-wrap: break-word;
            }
            table.r-table td:last-child { border-bottom: none; }
            table.r-table td:before {
                content: attr(data-label); font-weight: 600; font-size: 0.6rem;
                text-transform: uppercase; color: #6b7280; flex-shrink: 0;
                text-align: left; margin-right: 6px; min-width: 50px;
            }
            table.r-table td.num { font-size: 0.65rem !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="app" v-cloak>
        <div class="bg-gray-50 min-h-screen">
            <?php include 'sidebar.php'; ?>

            <div class="lg:ml-64 min-h-screen">
                <header class="bg-white shadow-sm border-b no-print">
                    <div class="px-4 sm:px-6 py-3 sm:py-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                                    <i class="fas fa-calculator text-primary mr-2"></i>Comptabilité
                                </h1>
                                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Documents comptables OHADA · Bénin</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button @click="refreshData" :disabled="submitting" class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-2 rounded-lg transition-colors flex items-center shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="p-3 sm:p-6 space-y-4 sm:space-y-6">
                    <!-- Période -->
                    <div class="bg-white rounded-xl shadow-sm p-3 sm:p-6 no-print">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Période</label>
                                <select v-model="selectedPeriod" @change="fetchData" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="today">Aujourd'hui</option>
                                    <option value="yesterday">Hier</option>
                                    <option value="this_week">Cette semaine</option>
                                    <option value="this_month">Ce mois-ci</option>
                                    <option value="last_month">Le mois dernier</option>
                                    <option value="this_quarter">Ce trimestre</option>
                                    <option value="this_year">Cette année</option>
                                    <option value="custom">Personnalisé</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Début</label>
                                <input v-model="customStart" type="date" :disabled="selectedPeriod !== 'custom'" @change="fetchData" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Fin</label>
                                <input v-model="customEnd" type="date" :disabled="selectedPeriod !== 'custom'" @change="fetchData" class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent disabled:bg-gray-100">
                            </div>
                            <div class="flex items-end">
                                <p class="text-xs sm:text-sm text-gray-500 py-2" v-if="period">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Du {{ period.start }} au {{ period.end }}
                                    <span class="ml-1 sm:ml-2 text-xs text-gray-400">· {{ journal.length }} écritures</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- RÉSUMÉ INDICATEURS -->
                    <!-- ================================================================ -->
                    <div v-if="bilan.revenue !== undefined" class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 stats-grid no-print">
                        <div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 border-l-4 border-blue-500">
                            <p class="text-xs font-medium text-gray-500 uppercase">CA</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900 mt-1 break-words">{{ formatCurrency(bilan.revenue) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 border-l-4 border-purple-500">
                            <p class="text-xs font-medium text-gray-500 uppercase">Stock</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900 mt-1 break-words">{{ formatCurrency(bilan.stockValue) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 border-l-4 border-amber-500">
                            <p class="text-xs font-medium text-gray-500 uppercase">Résultat</p>
                            <p class="text-lg sm:text-xl font-bold mt-1 break-words" :class="bilan.netProfit >= 0 ? 'solde-pos' : 'solde-neg'">{{ formatCurrency(bilan.netProfit) }}</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-3 sm:p-5 border-l-4 border-cyan-500">
                            <p class="text-xs font-medium text-gray-500 uppercase">Créances</p>
                            <p class="text-lg sm:text-xl font-bold text-gray-900 mt-1 break-words">{{ formatCurrency(bilan.receivables) }}</p>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : JOURNAL GÉNÉRAL -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section" v-if="journal.length > 0">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-list-alt text-primary mr-2"></i>Journal Général</h2>
                                <p class="text-xs text-gray-500">{{ journal.length }} écriture(s) du {{ period.start }} au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Date</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Pièce</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Libellé</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20 num">Débit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-28">Lib. Débit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20 num">Crédit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-28">Lib. Crédit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(e, i) in journal" :key="i" class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Date">{{ e.date }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Pièce">{{ e.ref }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Libellé">{{ e.label }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs font-medium num" data-label="Débit">{{ e.account_debit }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-xs text-gray-600 break-words" data-label="Lib. Débit">{{ e.label_debit }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs font-medium num" data-label="Crédit">{{ e.account_credit }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-xs text-gray-600 break-words" data-label="Lib. Crédit">{{ e.label_credit }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(e.montant) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : GRAND LIVRE -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section" v-if="grandLivre.length > 0">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-book-open text-primary mr-2"></i>Grand Livre</h2>
                                <p class="text-xs text-gray-500">{{ grandLivre.length }} compte(s) mouvementé(s)</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div v-for="l in grandLivre" :key="l.account" class="mb-3 border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-3 sm:px-4 py-2 flex items-center justify-between border-b flex-wrap gap-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono font-bold text-primary text-sm">{{ l.account }}</span>
                                        <span class="text-xs sm:text-sm font-medium text-gray-700 break-words">{{ l.label }}</span>
                                    </div>
                                    <div class="text-xs flex flex-wrap gap-2">
                                        <span class="text-gray-500">D: {{ formatCurrency(l.debit) }}</span>
                                        <span class="text-gray-500">C: {{ formatCurrency(l.credit) }}</span>
                                        <span class="font-bold" :class="(l.debit - l.credit) >= 0 ? 'solde-pos' : 'solde-neg'">S: {{ formatCurrency(l.debit - l.credit) }}</span>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-2 py-1.5 text-left text-xs font-semibold text-gray-600 w-20">Date</th>
                                                <th class="border border-gray-300 px-2 py-1.5 text-left text-xs font-semibold text-gray-600">Libellé</th>
                                                <th class="border border-gray-300 px-2 py-1.5 text-right text-xs font-semibold text-gray-600 w-24 num">Débit</th>
                                                <th class="border border-gray-300 px-2 py-1.5 text-right text-xs font-semibold text-gray-600 w-24 num">Crédit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(e, i) in l.entries" :key="i" class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-2 py-1.5 font-mono text-xs" data-label="Date">{{ e.date }}</td>
                                                <td class="border border-gray-300 px-2 py-1.5 break-words" data-label="Libellé">{{ e.label }}</td>
                                                <td class="border border-gray-300 px-2 py-1.5 text-right num" data-label="Débit">
                                                    <span v-if="e.account_debit === l.account">{{ formatCurrency(e.montant) }}</span>
                                                </td>
                                                <td class="border border-gray-300 px-2 py-1.5 text-right num" data-label="Crédit">
                                                    <span v-if="e.account_credit === l.account">{{ formatCurrency(e.montant) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : BALANCE GÉNÉRALE -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section" v-if="balance.rows.length > 0">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-scale-balanced text-primary mr-2"></i>Balance Générale</h2>
                                <p class="text-xs text-gray-500">{{ balance.rows.length }} comptes · Arrêtée au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Compte</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Libellé</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Total Débit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Total Crédit</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Solde</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Nature</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="r in balance.rows" :key="r.account" class="hover:bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono font-medium" data-label="Compte">{{ r.account }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Libellé">{{ r.label }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Total Débit">{{ formatCurrency(r.debit) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Total Crédit">{{ formatCurrency(r.credit) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-bold num" :class="r.solde >= 0 ? 'solde-pos' : 'solde-neg'" data-label="Solde">{{ formatCurrency(Math.abs(r.solde)) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-center" data-label="Nature">
                                                <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-xs" :class="r.solde >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ r.solde_type }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-100 font-bold">
                                            <td colspan="2" class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right text-xs uppercase">TOTAUX</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right text-xs num">{{ formatCurrency(balance.totalDebit) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right text-xs num">{{ formatCurrency(balance.totalCredit) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right text-xs num" :class="(balance.totalDebit - balance.totalCredit) === 0 ? 'solde-pos' : 'solde-neg'">{{ formatCurrency(Math.abs(balance.totalDebit - balance.totalCredit)) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-center text-xs">{{ (balance.totalDebit - balance.totalCredit) === 0 ? 'Équilibrée' : 'Déséquilibrée' }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 no-print">
                                <div class="bg-emerald-50 rounded-lg p-2 sm:p-3 text-center border border-emerald-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Débit</p>
                                    <p class="text-base sm:text-lg font-bold text-emerald-600 break-words">{{ formatCurrency(balance.totalDebit) }}</p>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-2 sm:p-3 text-center border border-blue-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Crédit</p>
                                    <p class="text-base sm:text-lg font-bold text-blue-600 break-words">{{ formatCurrency(balance.totalCredit) }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-2 sm:p-3 text-center border border-amber-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Différence</p>
                                    <p class="text-base sm:text-lg font-bold text-amber-600 break-words">{{ formatCurrency(Math.abs(balance.totalDebit - balance.totalCredit)) }}</p>
                                    <p class="text-xs" :class="(balance.totalDebit - balance.totalCredit) === 0 ? 'text-emerald-600' : 'text-red-600'">{{ (balance.totalDebit - balance.totalCredit) === 0 ? 'Équilibrée' : 'Non équilibrée' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : BILAN OHADA -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-balance-scale text-primary mr-2"></i>Bilan OHADA</h2>
                                <p class="text-xs text-gray-500">Arrêté au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 sm:p-3 mb-4 text-xs text-amber-800 flex items-start gap-2 no-print">
                                <i class="fas fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                                <p>Document de travail généré automatiquement. À faire vérifier par un comptable avant toute déclaration DGI Bénin.</p>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <h3 class="text-xs sm:text-sm font-bold text-emerald-700 mb-3 uppercase tracking-wider flex items-center">
                                        <i class="fas fa-arrow-right mr-2"></i>Actif
                                    </h3>
                                    <div class="overflow-x-auto">
                                        <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                            <thead>
                                                <tr class="bg-emerald-50">
                                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rubrique</th>
                                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="a in bilan.actif" :key="a.poste" class="hover:bg-gray-50">
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Rubrique">
                                                        <span v-if="a.montant === 0" class="text-gray-400">{{ a.poste }}</span>
                                                        <span v-else>{{ a.poste }}</span>
                                                    </td>
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">
                                                        <span v-if="a.montant > 0">{{ formatCurrency(a.montant) }}</span>
                                                        <span v-else class="text-gray-300">-</span>
                                                    </td>
                                                </tr>
                                                <tr class="bg-emerald-50 font-bold">
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 break-words">Total Actif</td>
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right num">{{ formatCurrency(bilan.totalActif) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-bold text-blue-700 mb-3 uppercase tracking-wider flex items-center">
                                        <i class="fas fa-arrow-left mr-2"></i>Passif & Capitaux Propres
                                    </h3>
                                    <div class="overflow-x-auto">
                                        <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                            <thead>
                                                <tr class="bg-blue-50">
                                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rubrique</th>
                                                    <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="p in bilan.passif" :key="p.poste" class="hover:bg-gray-50">
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Rubrique">{{ p.poste }}</td>
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(p.montant) }}</td>
                                                </tr>
                                                <tr class="bg-blue-50 font-bold">
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 break-words">Total Passif & Capitaux</td>
                                                    <td class="border border-gray-300 px-2 sm:px-4 py-2 sm:py-3 text-right num">{{ formatCurrency(bilan.totalPassif) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Détails ventes par paiement -->
                            <div class="mt-4 sm:mt-6" v-if="salesByMethod.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider"><i class="fas fa-credit-card text-primary mr-2"></i>Ventes par moyen de paiement</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Moyen</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-20 num">Nb</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="m in salesByMethod" :key="m.method" class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 capitalize break-words" data-label="Moyen">{{ m.method }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right num" data-label="Nb">{{ m.count }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Total">{{ formatCurrency(m.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Détails charges par catégorie -->
                            <div class="mt-4 sm:mt-6" v-if="expensesByCategory.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider"><i class="fas fa-tags text-primary mr-2"></i>Charges par catégorie</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Catégorie</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-20 num">Nb</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in expensesByCategory" :key="c.category" class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 capitalize break-words" data-label="Catégorie">{{ c.category }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right num" data-label="Nb">{{ c.count }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Total">{{ formatCurrency(c.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Top produits -->
                            <div class="mt-4 sm:mt-6" v-if="topProducts.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider"><i class="fas fa-cube text-primary mr-2"></i>Top produits vendus</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Produit</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-20 num">Qté</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="p in topProducts" :key="p.name" class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Produit">{{ p.name }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right num" data-label="Qté">{{ p.qty }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Total">{{ formatCurrency(p.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Échéancier créances -->
                            <div class="mt-4 sm:mt-6" v-if="claimsAging.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider"><i class="fas fa-clock text-primary mr-2"></i>Échéancier des créances</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Période</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-20 num">Nb</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-28 sm:w-32 num">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="c in claimsAging" :key="c.period" class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Période">{{ c.period }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right num" data-label="Nb">{{ c.count }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(c.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Indicateurs bilan -->
                            <div class="mt-4 sm:mt-6 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 stats-grid">
                                <div class="bg-emerald-50 rounded-lg p-3 sm:p-4 text-center border border-emerald-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Chiffre d'affaires</p>
                                    <p class="text-base sm:text-lg font-bold text-emerald-600 break-words">{{ formatCurrency(bilan.revenue) }}</p>
                                </div>
                                <div class="bg-red-50 rounded-lg p-3 sm:p-4 text-center border border-red-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Charges</p>
                                    <p class="text-base sm:text-lg font-bold text-red-600 break-words">{{ formatCurrency(bilan.expenses) }}</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-3 sm:p-4 text-center border border-purple-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">COGS</p>
                                    <p class="text-base sm:text-lg font-bold text-purple-600 break-words">{{ formatCurrency(bilan.cogs) }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-3 sm:p-4 text-center border border-amber-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Résultat net</p>
                                    <p class="text-base sm:text-lg font-bold break-words" :class="bilan.netProfit >= 0 ? 'solde-pos' : 'solde-neg'">{{ formatCurrency(bilan.netProfit) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : COMPTE DE RÉSULTAT -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-chart-pie text-primary mr-2"></i>Compte de Résultat</h2>
                                <p class="text-xs text-gray-500">Période du {{ period.start }} au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Rubrique</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">% CA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="bg-green-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-semibold break-words" data-label="Rubrique">Chiffre d'affaires</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-semibold num" data-label="Montant">{{ formatCurrency(bilan.revenue) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right" data-label="% CA">100 %</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-gray-600 break-words" data-label="Rubrique">- Coût des ventes (COGS)</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right text-red-600 num" data-label="Montant">({{ formatCurrency(bilan.cogs) }})</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right" data-label="% CA">{{ bilan.revenue > 0 ? ((bilan.cogs / bilan.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr class="bg-purple-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-semibold break-words" data-label="Rubrique">= Marge brute</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-semibold num" :class="(bilan.revenue - bilan.cogs) >= 0 ? 'solde-pos' : 'solde-neg'" data-label="Montant">{{ formatCurrency(bilan.revenue - bilan.cogs) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right" :class="(bilan.revenue - bilan.cogs) >= 0 ? 'solde-pos' : 'solde-neg'" data-label="% CA">{{ bilan.revenue > 0 ? (((bilan.revenue - bilan.cogs) / bilan.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-gray-600 break-words" data-label="Rubrique">- Charges d'exploitation</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right text-red-600 num" data-label="Montant">({{ formatCurrency(bilan.expenses) }})</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right" data-label="% CA">{{ bilan.revenue > 0 ? ((bilan.expenses / bilan.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                        <tr class="bg-amber-50 font-bold">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Rubrique">= Résultat net</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right num" :class="bilan.netProfit >= 0 ? 'solde-pos' : 'solde-neg'" data-label="Montant">{{ formatCurrency(bilan.netProfit) }}</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right" :class="bilan.netProfit >= 0 ? 'solde-pos' : 'solde-neg'" data-label="% CA">{{ bilan.revenue > 0 ? ((bilan.netProfit / bilan.revenue) * 100).toFixed(1) : '0' }} %</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 sm:mt-6 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 stats-grid">
                                <div class="bg-emerald-50 rounded-lg p-3 sm:p-4 text-center border border-emerald-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Chiffre d'affaires</p>
                                    <p class="text-base sm:text-lg font-bold text-emerald-600 break-words">{{ formatCurrency(bilan.revenue) }}</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-3 sm:p-4 text-center border border-purple-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Marge brute</p>
                                    <p class="text-base sm:text-lg font-bold break-words" :class="(bilan.revenue - bilan.cogs) >= 0 ? 'solde-pos' : 'solde-neg'">{{ formatCurrency(bilan.revenue - bilan.cogs) }}</p>
                                </div>
                                <div class="bg-red-50 rounded-lg p-3 sm:p-4 text-center border border-red-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Charges</p>
                                    <p class="text-base sm:text-lg font-bold text-red-600 break-words">{{ formatCurrency(bilan.expenses) }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-3 sm:p-4 text-center border border-amber-100">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Résultat net</p>
                                    <p class="text-base sm:text-lg font-bold break-words" :class="bilan.netProfit >= 0 ? 'solde-pos' : 'solde-neg'">{{ formatCurrency(bilan.netProfit) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : REGISTRE DES PIÈCES COMPTABLES -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section" v-if="documents.sales.length > 0 || documents.expenses.length > 0 || documents.claims.length > 0 || documents.claimPayments.length > 0">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-folder-open text-primary mr-2"></i>Registre des Pièces Comptables</h2>
                                <p class="text-xs text-gray-500">{{ documents.sales.length + documents.expenses.length + documents.claims.length + documents.claimPayments.length }} pièce(s) du {{ period.start }} au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <!-- Synthèse -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6 stats-grid no-print">
                                <div class="bg-blue-50 rounded-lg p-3 sm:p-4 text-center border border-blue-100">
                                    <i class="fas fa-receipt text-blue-500 text-lg sm:text-xl mb-1"></i>
                                    <p class="text-lg sm:text-2xl font-bold text-blue-700">{{ documents.sales.length }}</p>
                                    <p class="text-xs text-gray-500">Ventes</p>
                                </div>
                                <div class="bg-red-50 rounded-lg p-3 sm:p-4 text-center border border-red-100">
                                    <i class="fas fa-wallet text-red-500 text-lg sm:text-xl mb-1"></i>
                                    <p class="text-lg sm:text-2xl font-bold text-red-700">{{ documents.expenses.length }}</p>
                                    <p class="text-xs text-gray-500">Dépenses</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-3 sm:p-4 text-center border border-amber-100">
                                    <i class="fas fa-hand-holding-dollar text-amber-500 text-lg sm:text-xl mb-1"></i>
                                    <p class="text-lg sm:text-2xl font-bold text-amber-700">{{ documents.claims.length }}</p>
                                    <p class="text-xs text-gray-500">Créances</p>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-3 sm:p-4 text-center border border-emerald-100">
                                    <i class="fas fa-money-bill-transfer text-emerald-500 text-lg sm:text-xl mb-1"></i>
                                    <p class="text-lg sm:text-2xl font-bold text-emerald-700">{{ documents.claimPayments.length }}</p>
                                    <p class="text-xs text-gray-500">Encaissements</p>
                                </div>
                            </div>

                            <!-- Ventes -->
                            <div class="mb-6" v-if="documents.sales.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-blue-700 mb-3 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-receipt mr-2"></i>Factures de vente
                                    <span class="ml-2 text-xs font-normal text-gray-400">({{ documents.sales.length }})</span>
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-blue-50">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Pièce</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Date</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Vendeur</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Paiement</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="s in documents.sales" :key="'s'+s.id" class="hover:bg-blue-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono font-medium" data-label="Pièce">VTE-{{ s.id }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Date">{{ formatDate(s.date_of_insertion) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Vendeur">{{ s.seller || 'Comptoir' }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 capitalize" data-label="Paiement">{{ s.payment_method || 'Caisse' }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(s.total) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-center" data-label="Statut">
                                                    <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-xs" :class="(s.status === 'validé' || s.status === null) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ s.status || 'validé' }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Dépenses -->
                            <div class="mb-6" v-if="documents.expenses.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-red-700 mb-3 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-wallet mr-2"></i>Pièces de dépenses
                                    <span class="ml-2 text-xs font-normal text-gray-400">({{ documents.expenses.length }})</span>
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-red-50">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Pièce</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Date</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Libellé</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Catégorie</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="ex in documents.expenses" :key="'e'+ex.id" class="hover:bg-red-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono font-medium" data-label="Pièce">DEP-{{ ex.id }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Date">{{ formatDate(ex.created_at) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Libellé">{{ ex.name }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 capitalize" data-label="Catégorie">{{ ex.category }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(ex.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Créances -->
                            <div class="mb-6" v-if="documents.claims.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-amber-700 mb-3 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-hand-holding-dollar mr-2"></i>Céances clients
                                    <span class="ml-2 text-xs font-normal text-gray-400">({{ documents.claims.length }})</span>
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-amber-50">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Pièce</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Date</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Client</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Restant dû</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="cl in documents.claims" :key="'c'+cl.id" class="hover:bg-amber-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono font-medium" data-label="Pièce">CLT-{{ cl.id }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Date">{{ formatDate(cl.date_of_claim) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Client">{{ cl.client_name || 'N/A' }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(cl.amount) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" :class="parseFloat(cl.remaining) > 0 ? 'text-red-600' : 'text-emerald-600'" data-label="Restant dû">{{ formatCurrency(cl.remaining) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-center" data-label="Statut">
                                                    <span class="px-1.5 sm:px-2 py-0.5 rounded-full text-xs" :class="cl.status === 'actif' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'">{{ cl.status }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Encaissements -->
                            <div class="mb-6" v-if="documents.claimPayments.length > 0">
                                <h3 class="text-xs sm:text-sm font-bold text-emerald-700 mb-3 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-money-bill-transfer mr-2"></i>Encaissements sur créances
                                    <span class="ml-2 text-xs font-normal text-gray-400">({{ documents.claimPayments.length }})</span>
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse r-table text-xs sm:text-sm">
                                        <thead>
                                            <tr class="bg-emerald-50">
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Pièce</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-20 sm:w-24">Date</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Créance</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Client</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase w-16 sm:w-20">Mode</th>
                                                <th class="border border-gray-300 px-2 sm:px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24 sm:w-28 num">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="cp in documents.claimPayments" :key="'cp'+cp.id" class="hover:bg-emerald-50">
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono font-medium" data-label="Pièce">ENC-{{ cp.id }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Date">{{ formatDate(cp.date) }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 font-mono text-xs" data-label="Créance">CLT-{{ cp.claim_id }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 break-words" data-label="Client">{{ cp.client_name || 'N/A' }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 capitalize" data-label="Mode">{{ cp.payment_method || 'Caisse' }}</td>
                                                <td class="border border-gray-300 px-2 sm:px-4 py-1.5 sm:py-2 text-right font-medium num" data-label="Montant">{{ formatCurrency(cp.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div v-if="documents.sales.length === 0 && documents.expenses.length === 0 && documents.claims.length === 0 && documents.claimPayments.length === 0" class="text-center py-8 text-gray-400">
                                <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                                <p class="text-sm">Aucune pièce comptable sur cette période</p>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================================ -->
                    <!-- DOCUMENT : RATIOS FINANCIERS -->
                    <!-- ================================================================ -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden print-section" v-if="bilan.revenue > 0">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-gray-900"><i class="fas fa-chart-line text-primary mr-2"></i>Ratios Financiers</h2>
                                <p class="text-xs text-gray-500">Période du {{ period.start }} au {{ period.end }}</p>
                            </div>
                            <button @click="printEl()" class="print-btn no-print bg-primary hover:bg-secondary text-white px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm flex items-center shadow-sm">
                                <i class="fas fa-print mr-1 sm:mr-2"></i>Imprimer
                            </button>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Marge brute</p>
                                    <p class="text-lg sm:text-2xl font-bold text-primary mt-1" :class="margeBrute >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ margeBrute.toFixed(1) }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">(CA - COGS) / CA</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Marge nette</p>
                                    <p class="text-lg sm:text-2xl font-bold mt-1" :class="margeNette >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ margeNette.toFixed(1) }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">Résultat net / CA</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Ratio charges</p>
                                    <p class="text-lg sm:text-2xl font-bold text-orange-600 mt-1">{{ ratioCharges.toFixed(1) }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">Charges / CA</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Rotation stock</p>
                                    <p class="text-lg sm:text-2xl font-bold text-blue-600 mt-1">{{ bilan.stockValue > 0 ? (bilan.cogs / bilan.stockValue).toFixed(1) : 'N/A' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">COGS / Stock moyen</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Rentabilité</p>
                                    <p class="text-lg sm:text-2xl font-bold text-purple-600 mt-1">{{ bilan.revenue > 0 ? ((bilan.netProfit / bilan.revenue) * 100).toFixed(1) : '0.0' }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">Résultat net / CA</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Poids COGS</p>
                                    <p class="text-lg sm:text-2xl font-bold text-amber-600 mt-1">{{ bilan.revenue > 0 ? ((bilan.cogs / bilan.revenue) * 100).toFixed(1) : '0.0' }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">COGS / CA</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Liquidité</p>
                                    <p class="text-lg sm:text-2xl font-bold text-cyan-600 mt-1">{{ (bilan.receivables / Math.max(bilan.expenses, 1)).toFixed(1) }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Créances / Charges</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Taux encaissement</p>
                                    <p class="text-lg sm:text-2xl font-bold text-teal-600 mt-1">{{ tauxEncaissement.toFixed(1) }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">Encaissements / Total créances</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Efficacité op.</p>
                                    <p class="text-lg sm:text-2xl font-bold text-indigo-600 mt-1">{{ efficaciteOperationnelle.toFixed(1) }}%</p>
                                    <p class="text-xs text-gray-400 mt-1">Marge brute - Ratio charges</p>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-sm transition-shadow sm:col-span-2 lg:col-span-3">
                                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Seuil de rentabilité</p>
                                    <p class="text-lg sm:text-2xl font-bold text-gray-700 mt-1">{{ formatCurrency(seuilRentabilite) }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Charges / (1 - COGS/CA)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 no-print">
            <div class="bg-white mx-3 p-4 sm:p-6 rounded-lg shadow-xl">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-5 sm:h-6 w-5 sm:w-6 border-b-2 border-blue-600"></div>
                    <span class="text-sm sm:text-base text-gray-700">Génération des écritures...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        const api = axios.create({ baseURL: 'api/index.php' });

        createApp({
            data() {
                return {
                    loading: false,
                    selectedPeriod: 'this_month',
                    customStart: '',
                    customEnd: '',
                    period: { start: '', end: '' },
                    journal: [],
                    grandLivre: [],
                    balance: { rows: [], totalDebit: 0, totalCredit: 0 },
                    bilan: { actif: [], passif: [], totalActif: 0, totalPassif: 0, netProfit: 0, revenue: 0, expenses: 0, cogs: 0, stockValue: 0, receivables: 0 },
                    salesByMethod: [],
                    expensesByCategory: [],
                    topProducts: [],
                    claimsAging: [],
                    documents: { sales: [], expenses: [], claims: [], claimPayments: [] },
                    submitting: false
                }
            },
            computed: {
                margeBrute() {
                    return this.bilan.revenue > 0 ? ((this.bilan.revenue - this.bilan.cogs) / this.bilan.revenue) * 100 : 0;
                },
                margeNette() {
                    return this.bilan.revenue > 0 ? (this.bilan.netProfit / this.bilan.revenue) * 100 : 0;
                },
                ratioCharges() {
                    return this.bilan.revenue > 0 ? (this.bilan.expenses / this.bilan.revenue) * 100 : 0;
                },
                tauxEncaissement() {
                    const totalClaims = this.documents.claims.reduce((s, c) => s + parseFloat(c.amount || 0), 0);
                    const totalEnc = this.documents.claimPayments.reduce((s, c) => s + parseFloat(c.amount || 0), 0);
                    return totalClaims > 0 ? (totalEnc / totalClaims) * 100 : 0;
                },
                efficaciteOperationnelle() {
                    return this.margeBrute - this.ratioCharges;
                },
                seuilRentabilite() {
                    const ratioCogs = this.bilan.revenue > 0 ? this.bilan.cogs / this.bilan.revenue : 0;
                    return ratioCogs < 1 ? this.bilan.expenses / (1 - ratioCogs) : 0;
                },
            },
            methods: {
                formatCurrency(amount) {
                    const num = parseFloat(amount) || 0;
                    return new Intl.NumberFormat('fr-FR', { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num) + ' FCFA';
                },
                formatDate(d) {
                    if (!d) return '';
                    return new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                },
                async fetchData() {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.loading = true;
                    try {
                        const params = { action: 'accountingData', period: this.selectedPeriod };
                        if (this.selectedPeriod === 'custom') {
                            params.start_date = this.customStart;
                            params.end_date = this.customEnd;
                        }
                        const resp = await api.get('', { params });
                        if (resp.data.success) {
                            this.period = resp.data.period;
                            this.journal = resp.data.journal || [];
                            this.grandLivre = resp.data.grandLivre || [];
                            this.balance = resp.data.balance || { rows: [], totalDebit: 0, totalCredit: 0 };
                            this.bilan = resp.data.bilan || { actif: [], passif: [], totalActif: 0, totalPassif: 0, netProfit: 0, revenue: 0, expenses: 0, cogs: 0, stockValue: 0, receivables: 0 };
                            this.salesByMethod = resp.data.salesByMethod || [];
                            this.expensesByCategory = resp.data.expensesByCategory || [];
                            this.topProducts = resp.data.topProducts || [];
                            this.claimsAging = resp.data.claimsAging || [];
                            this.documents = resp.data.documents || { sales: [], expenses: [], claims: [], claimPayments: [] };
                        }
                    } catch (err) {
                        console.error('Erreur comptabilité:', err);
                    } finally {
                        this.loading = false;
                        this.submitting = false;
                    }
                },
                refreshData() { this.fetchData(); },
                printEl() {
                    const els = document.querySelectorAll('.print-section');
                    els.forEach(el => el.style.visibility = 'visible');
                    setTimeout(() => window.print(), 200);
                }
            },
            mounted() {
                const now = new Date();
                this.customEnd = now.toISOString().split('T')[0];
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                this.customStart = firstDay.toISOString().split('T')[0];
                this.fetchData();
            }
        }).mount('#app');
    </script>
</body>
</html>
