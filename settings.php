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
    <title>Paramètres - TOBI LODA</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="public/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .text-primary { color: #2563EB; }
        .bg-primary { background-color: #2563EB; }
        .hover\:bg-primary-dark:hover { background-color: #1D4ED8; }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="lg:ml-64 min-h-screen bg-gray-100" style="padding-bottom: 5rem;">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-cog text-primary mr-2"></i>
                Paramètres
            </h1>
            <div class="hidden lg:flex items-center space-x-1 text-sm text-gray-500 border-l pl-3 ml-3">
                <i class="fas fa-user-circle"></i>
                <span class="font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                <span class="text-xs text-gray-400">· Admin</span>
            </div>
        </header>

        <main class="p-6" v-cloak>
            <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                <i class="fas fa-tools text-5xl text-gray-300 mb-4"></i>
                <h2 class="text-lg font-semibold text-gray-600">Page en cours de développement</h2>
                <p class="text-gray-400 mt-2">Les paramètres de l'application seront bientôt disponibles.</p>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const { createApp } = Vue;

        createApp({
            data() {
                return {
                    //
                }
            }
        }).mount('main');
    </script>
</body>

</html>
