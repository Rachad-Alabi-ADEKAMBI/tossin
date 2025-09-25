<?php
require 'db.php';

// Récupérer toutes les claims
function getAllClaims()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM claims ORDER BY id DESC");
    $claims = $stmt->fetchAll();
    echo json_encode($claims);
}

// Ajouter une nouvelle claim
function addNewClaim($data)
{
    global $pdo;

    try {
        $sql = "INSERT INTO claims 
            (client_name, client_phone, amount, remaining_amount, date_of_claim, due_date, notes, status)
            VALUES (:client_name, :client_phone, :amount, :remaining_amount, :date_of_claim, :due_date, :notes, :status)";

        $stmt = $pdo->prepare($sql);

        // Vérification et assignation des valeurs par défaut si nécessaire
        $client_name = $data['client_name'] ?? '';
        $client_phone = $data['client_phone'] ?? '';
        $amount = $data['amount'] ?? 0;
        $remaining_amount = $amount; // initialement égal au montant
        $date_of_claim = $data['date_of_claim'] ?? date('Y-m-d');
        $due_date = $data['due_date'] ?? null;
        $notes = $data['notes'] ?? '';
        $status = 'Actif';

        // Exécuter la requête
        $stmt->execute([
            ':client_name' => $client_name,
            ':client_phone' => $client_phone,
            ':amount' => $amount,
            ':remaining_amount' => $remaining_amount,
            ':date_of_claim' => $date_of_claim,
            ':due_date' => $due_date,
            ':notes' => $notes,
            ':status' => $status
        ]);

        // Récupérer l'id de la nouvelle claim et renvoyer le tout
        $newClaim = [
            'id' => $pdo->lastInsertId(),
            'client_name' => $client_name,
            'client_phone' => $client_phone,
            'amount' => $amount,
            'remaining_amount' => $remaining_amount,
            'date_of_claim' => $date_of_claim,
            'due_date' => $due_date,
            'notes' => $notes,
            'status' => $status
        ];

        echo json_encode(['success' => true, 'claim' => $newClaim]);
    } catch (PDOException $e) {
        // En cas d'erreur, renvoyer un message clair exploitable côté frontend
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Ajouter un nouveau paiement
function newPayment($data, $file = null)
{
    global $pdo;

    // Vérification des champs obligatoires
    if (empty($data['claim_id']) || !isset($data['amount']) || empty($data['payment_method'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Champs obligatoires manquants : claim_id, amount ou payment_method'
        ]);
        return;
    }

    $amount = floatval($data['amount']);
    $claim_id = intval($data['claim_id']);
    $payment_method = $data['payment_method'];
    $notes = $data['notes'] ?? '';
    $date_of_insertion = $data['date_of_insertion'] ?? date('Y-m-d H:i:s');

    // Gestion du fichier si fourni
    $fileName = null;
    if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/payments/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Impossible de créer le dossier d\'upload.']);
            return;
        }
        $fileName = time() . '_' . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'upload du fichier.']);
            return;
        }
    }

    try {
        $pdo->beginTransaction();

        // Récupérer infos de la claim
        $claimStmt = $pdo->prepare("SELECT amount, remaining_amount, client_name FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new Exception("Aucune créance trouvée pour l'ID fourni.");
        }

        if ($amount > $claim['remaining_amount']) {
            throw new Exception("Le montant du paiement ({$amount}) ne peut pas dépasser le remaining_amount ({$claim['remaining_amount']}).");
        }

        $initial_amount = $claim['amount'];
        $remaining_amount = $claim['remaining_amount'] - $amount;
        $client_name = $claim['client_name'];

        // Insertion du paiement
        $sql = "INSERT INTO payments 
                (date_of_insertion, amount, claim_id, payment_method, notes, file, initial_amount, remaining_amount, client_name)
                VALUES (:date_of_insertion, :amount, :claim_id, :payment_method, :notes, :file, :initial_amount, :remaining_amount, :client_name)";
        $stmt = $pdo->prepare($sql);

        if (!$stmt->execute([
            ':date_of_insertion' => $date_of_insertion,
            ':amount' => $amount,
            ':claim_id' => $claim_id,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName,
            ':initial_amount' => $initial_amount,
            ':remaining_amount' => $remaining_amount,
            ':client_name' => $client_name
        ])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Erreur PDO lors de l'insertion du paiement : " . $errorInfo[2]);
        }

        // Mise à jour du remaining_amount dans claims
        $update = $pdo->prepare("UPDATE claims SET remaining_amount = :remaining_amount WHERE id = :claim_id");
        if (!$update->execute([
            ':remaining_amount' => $remaining_amount,
            ':claim_id' => $claim_id
        ])) {
            $errorInfo = $update->errorInfo();
            throw new Exception("Erreur PDO lors de la mise à jour du remaining_amount : " . $errorInfo[2]);
        }

        $pdo->commit();

        $newPayment = [
            'id' => $pdo->lastInsertId(),
            'date_of_insertion' => $date_of_insertion,
            'amount' => $amount,
            'claim_id' => $claim_id,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'file' => $fileName,
            'initial_amount' => $initial_amount,
            'remaining_amount' => $remaining_amount,
            'client_name' => $client_name
        ];

        echo json_encode(['success' => true, 'payment' => $newPayment]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'insertion du paiement : ' . $e->getMessage()
        ]);
    }
}

// ajouter une nouvelle commande
function newOrder()
{
    global $pdo;

    // Lire les données JSON envoyées par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['seller']) || !isset($data['total']) || empty($data['lines'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : seller, total ou lines'
        ]);
        return;
    }

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Insérer la commande dans la table orders
        $stmt = $pdo->prepare("
            INSERT INTO orders (date_of_insertion, seller, total, status) 
            VALUES (NOW(), :seller, :total, :status)
        ");
        $stmt->execute([
            ':seller' => $data['seller'],
            ':total'  => $data['total'],
            ':status' => $data['status']
        ]);

        // Récupérer l'ID de la commande nouvellement insérée
        $orderId = $pdo->lastInsertId();

        // Insérer chaque produit de la commande dans la table products
        $stmtProduct = $pdo->prepare("
            INSERT INTO products (date_of_insertion, name, quantity, price, order_id) 
            VALUES (NOW(), :name, :quantity, :price, :order_id)
        ");

        foreach ($data['lines'] as $line) {
            $stmtProduct->execute([
                ':name'     => $line['product'],
                ':quantity' => $line['quantity'],
                ':price'    => $line['price'],
                ':order_id' => $orderId
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Commande et produits insérés avec succès',
            'order_id' => $orderId
        ]);
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'insertion: ' . $e->getMessage()
        ]);
    }
}

// supprimer un produit grâce à son id
function deleteProduct()
{
    global $pdo;

    // Récupérer l'ID envoyé (DELETE ne transmet pas de body avec Axios par défaut, donc utiliser query param ou raw input)
    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $data['id'] ?? null;

    if (!$productId) {
        echo json_encode([
            'success' => false,
            'message' => 'ID du produit manquant'
        ]);
        return;
    }

    try {
        // Démarrer transaction
        $pdo->beginTransaction();

        // Récupérer l'order_id du produit avant suppression
        $stmt = $pdo->prepare("SELECT order_id FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
            return;
        }

        $orderId = $order['order_id'];

        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);

        // Recalculer le total de la commande
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM products
            WHERE order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour le total dans orders
        $stmt = $pdo->prepare("UPDATE orders SET total = :total WHERE id = :order_id");
        $stmt->execute([
            ':total'    => $newTotal,
            ':order_id' => $orderId
        ]);

        // Commit
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Produit supprimé et commande mise à jour',
            'order_id' => $orderId,
            'new_total' => $newTotal
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
        ]);
    }
}

// Supprimer une commande
function deleteOrder()
{
    global $pdo;

    // Récupérer le body JSON envoyé par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la commande manquant'
        ]);
        return;
    }

    $orderId = intval($data['id']);

    try {
        // Supprimer d'abord les produits associés à la commande (clé étrangère)
        $stmtProducts = $pdo->prepare("DELETE FROM products WHERE order_id = ?");
        $stmtProducts->execute([$orderId]);

        // Supprimer ensuite la commande
        $stmtOrder = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmtOrder->execute([$orderId]);

        if ($stmtOrder->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Commande supprimée avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Commande introuvable'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage()
        ]);
    }
}

//mise à jour du statut de la commande
function updateOrderStatus()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);
    $orderId = $data['id'] ?? null;
    $status  = $data['status'] ?? null;

    if (!$orderId || !$status) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : id, status'
        ]);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id'     => $orderId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'order_id' => $orderId,
            'new_status' => $status
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
        ]);
    }
}



// mettre à jour un produit existant
function updateProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $_GET['id'] ?? null;

    if (!$productId || empty($data['name']) || !isset($data['quantity']) || !isset($data['price'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : id, name, quantity, price'
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Récupérer l'order_id du produit avant mise à jour
        $stmt = $pdo->prepare("SELECT order_id FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
            return;
        }

        $orderId = $product['order_id'];

        // Mettre à jour le produit
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = :name,
                quantity = :quantity,
                price = :price
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'     => $data['name'],
            ':quantity' => $data['quantity'],
            ':price'    => $data['price'],
            ':id'       => $productId
        ]);

        // Recalculer le total de la commande
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM products
            WHERE order_id = :order_id
        ");
        $stmt->execute([':order_id' => $orderId]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour le total dans orders
        $stmt = $pdo->prepare("UPDATE orders SET total = :total WHERE id = :order_id");
        $stmt->execute([
            ':total'    => $newTotal,
            ':order_id' => $orderId
        ]);

        $pdo->commit();

        echo json_encode([
            'success'    => true,
            'message'    => 'Produit mis à jour et commande recalculée',
            'product_id' => $productId,
            'new_total'  => $newTotal
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du produit : ' . $e->getMessage()
        ]);
    }
}


//ajouter un nouveau produit
function newProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['order_id']) || empty($data['name']) || !isset($data['quantity']) || !isset($data['price'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : order_id, name, quantity, price'
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Insérer le produit
        $stmt = $pdo->prepare("
            INSERT INTO products (date_of_insertion, order_id, name, quantity, price)
            VALUES (NOW(), :order_id, :name, :quantity, :price)
        ");
        $stmt->execute([
            ':order_id' => $data['order_id'],
            ':name'     => $data['name'],
            ':quantity' => $data['quantity'],
            ':price'    => $data['price']
        ]);

        $productId = $pdo->lastInsertId();

        // Recalculer le total de la commande
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM products
            WHERE order_id = :order_id
        ");
        $stmt->execute([':order_id' => $data['order_id']]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour la commande
        $stmt = $pdo->prepare("UPDATE orders SET total = :total WHERE id = :order_id");
        $stmt->execute([
            ':total'    => $newTotal,
            ':order_id' => $data['order_id']
        ]);

        $pdo->commit();

        echo json_encode([
            'success'    => true,
            'message'    => 'Produit ajouté et commande mise à jour',
            'product_id' => $productId,
            'new_total'  => $newTotal
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'ajout du produit : ' . $e->getMessage()
        ]);
    }
}




// Récupérer toutes les claims
function getAllPayments()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM payments ORDER BY id DESC");
    $payments = $stmt->fetchAll();
    echo json_encode($payments);
}

// Récupérer toutes les orders
function getAllOrders()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll();
    echo json_encode($orders);
}

// Récupérer toutes les produits
function getAllProducts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    echo json_encode($products);
}

// Connexion d'un utilisateur

function login($data)
{
    global $pdo;

    if (empty($data['username']) || empty($data['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Nom d\'utilisateur et mot de passe requis'
        ]);
        return;
    }

    $username = $data['username'];
    $password = $data['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode([
            'success' => true,
            'redirect' => 'http://127.0.0.1/tossin/index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Nom d’utilisateur ou mot de passe incorrect'
        ]);
    }
}

// logout
function logout()
{
    session_start();
    session_unset();
    session_destroy();

    echo json_encode([
        'success' => true,
        'redirect' => 'http://127.0.0.1/tossin/login.php'
    ]);
}
