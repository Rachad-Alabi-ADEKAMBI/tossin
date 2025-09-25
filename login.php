<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tossin - Connexion</title>
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

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="bg-primary rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-building text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tossin</h1>
            <p class="text-gray-600">Gestion d'entreprise moderne</p>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2"></i>Nom d'utilisateur
                </label>
                <input type="text" id="username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i>Mot de passe
                </label>
                <div class="relative">
                    <input type="password" id="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all pr-10">
                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-secondary text-white py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">© 2025 Tossin. Tous droits réservés.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        async function login(username, password) {
            if (!username || !password) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            try {
                const response = await axios.post('http://127.0.0.1/tossin/api/index.php?action=login', {
                    username: username,
                    password: password
                });

                if (response.data.success) {
                    window.location.href = 'index.php';
                } else {
                    alert(response.data.message);
                }
            } catch (error) {
                console.error('Erreur lors de la connexion:', error);
                alert('Erreur lors de la connexion');
            }
        }

        function logout() {
            axios.post('http://127.0.0.1/tossin/api/index.php?action=logout')
                .then(res => {
                    if (res.data.success) window.location.href = res.data.redirect;
                });
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            login(username, password);
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>