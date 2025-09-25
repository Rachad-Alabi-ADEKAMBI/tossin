 <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
     <div class="flex items-center justify-center h-16 bg-primary">
         <div class="flex items-center space-x-2">
             <i class="fas fa-building text-white text-2xl"></i>
             <span class="text-white text-xl font-bold">Tossin</span>
         </div>
     </div>

     <nav class="mt-8">
         <a href="index.php" class="flex items-center px-6 py-3 text-primary bg-blue-50 border-r-4 border-primary">
             <i class="fas fa-tachometer-alt mr-3"></i>
             <span>Tableau de bord</span>
         </a>
         <a href="claims.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
             <i class="fas fa-money-bill-wave mr-3"></i>
             <span>Créances</span>
         </a>
         <a href="orders.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-colors">
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

 <div class="lg:hidden fixed top-4 left-4 z-50">
     <button onclick="toggleSidebar()" class="bg-white p-2 rounded-lg shadow-lg">
         <i class="fas fa-bars text-gray-700"></i>
     </button>
 </div>

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