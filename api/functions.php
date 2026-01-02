<?php
require 'db.php';

// Récupérer toutes les claims
function getAllClaims()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM claims ORDER BY id DESC");
    $claims = $stmt->fetchAll();
    echo json_encode($claims, JSON_UNESCAPED_UNICODE);
}

// Récupérer toutes les clients
function getAllClients()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY id DESC");
    $clients = $stmt->fetchAll();
    echo json_encode($clients);
}

function createProduct()
{
    global $pdo;

    $requiredFields = ['name', 'quantity', 'buying_price', 'bulk_price', 'semi_bulk_price', 'retail_price'];
    $missingFields = [];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Données manquantes: ' . implode(', ', $missingFields)
        ]);
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO products 
        (name, quantity, buying_price, bulk_price, semi_bulk_price, retail_price)
        VALUES 
        (:name, :quantity, :buying_price, :bulk_price, :semi_bulk_price, :retail_price)
    ");

    $stmt->execute([
        ':name' => $_POST['name'],
        ':quantity' => $_POST['quantity'],
        ':buying_price' => $_POST['buying_price'],
        ':bulk_price' => $_POST['bulk_price'],
        ':semi_bulk_price' => $_POST['semi_bulk_price'],
        ':retail_price' => $_POST['retail_price'],
    ]);

    $productId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO products_history (product_id, quantity, comment)
        VALUES (:product_id, :quantity, :comment)
    ");
    $stmt->execute([
        ':product_id' => $productId,
        ':quantity' => $_POST['quantity'],
        ':comment' => 'Création du produit'
    ]);

    echo json_encode(['success' => true]);
}

// Créer un nouveau client
function createClient()
{
    global $pdo;

    // Lecture des données JSON envoyées par Axios
    $data = json_decode(file_get_contents('php://input'), true);

    $name  = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');

    if ($name === '' || $phone === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Nom ou téléphone manquant'
        ]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO clients (name, phone, created_at)
            VALUES (:name, :phone, NOW())
        ");

        $stmt->execute([
            ':name'  => $name,
            ':phone' => $phone
        ]);

        echo json_encode([
            'success'   => true,
            'client_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création du client'
        ]);
    }
}


// Récupérer tous les produits
function getAllProducts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    echo json_encode($products);
}



// Mettre à jour un produit
function updateProduct()
{
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE products SET
            name = :name,
            buying_price = :buying_price,
            bulk_price = :bulk_price,
            semi_bulk_price = :semi_bulk_price,
            retail_price = :retail_price
        WHERE id = :id
    ");
    $stmt->execute([
        ':id' => $_POST['id'],
        ':name' => $_POST['name'],
        ':buying_price' => $_POST['buying_price'],
        ':bulk_price' => $_POST['bulk_price'],
        ':semi_bulk_price' => $_POST['semi_bulk_price'],
        ':retail_price' => $_POST['retail_price'],
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO products_history (product_id, quantity, comment)
        VALUES (:product_id, NULL, :comment)
    ");
    $stmt->execute([
        ':product_id' => $_POST['id'],
        ':comment' => 'Modification du produit'
    ]);

    echo json_encode(['success' => true]);
}

// Ajuster le stock
function adjustProductStock()
{
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE products SET quantity = quantity + :quantity WHERE id = :id
    ");
    $stmt->execute([
        ':quantity' => $_POST['quantity'],
        ':id' => $_POST['product_id']
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO products_history (product_id, quantity, comment)
        VALUES (:product_id, :quantity, :comment)
    ");
    $stmt->execute([
        ':product_id' => $_POST['product_id'],
        ':quantity' => $_POST['quantity'],
        ':comment' => $_POST['comment']
    ]);

    echo json_encode(['success' => true]);
}

// Supprimer un produit
function deleteProduct()
{
    global $pdo;

    // Lire les données depuis le body JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
        return;
    }

    // Supprimer le produit
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => true]);
}

// Historique d’un produit
function getProductHistory()
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM products_history
        WHERE product_id = :product_id
        ORDER BY id DESC
    ");
    $stmt->execute([':product_id' => $_GET['product_id']]);

    echo json_encode($stmt->fetchAll());
}


// Récupérer toutes les depenses
function getAllExpenses()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM expenses ORDER BY id DESC");
    $expenses = $stmt->fetchAll();
    echo json_encode($expenses);
}

// Ajouter une nouvelle dépense
function newExpense()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    // Vérifier les champs obligatoires
    if (empty($data['name']) || empty($data['category']) || !isset($data['amount'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Champs requis : name, category, amount'
        ]);
        return;
    }

    $name = $data['name'];
    $category = $data['category'];
    $amount = floatval($data['amount']);
    $comment = $data['comment'] ?? '';
    $created_at = $data['created_at'] ?? date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (name, category, amount, comment, created_at)
            VALUES (:name, :category, :amount, :comment, :created_at)
        ");
        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':amount' => $amount,
            ':comment' => $comment,
            ':created_at' => $created_at
        ]);

        $expenseId = $pdo->lastInsertId();

        // Notification
        addNotification("Nouvelle dépense ajoutée : $name | Catégorie: $category | Montant: $amount");

        echo json_encode([
            'success' => true,
            'message' => 'Dépense ajoutée avec succès',
            'id' => $expenseId
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l’ajout : ' . $e->getMessage()
        ]);
    }
}


// Récupérer toutes les notifications
function getAllNotifications()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT id, comment, created_at
            FROM notifications
            ORDER BY created_at DESC
        ");
        $stmt->execute();

        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur récupération notifications : ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}



// Mettre à jour une dépense
function updateExpense()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id']) || empty($data['name']) || empty($data['amount']) || empty($data['currency'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs requis : id, name, amount, currency'
        ]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE expenses
            SET name = :name,
                amount = :amount,
                currency = :currency,
                notes = :notes
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'     => $data['name'],
            ':amount'   => $data['amount'],
            ':currency' => $data['currency'],
            ':notes'    => $data['notes'] ?? null,
            ':id'       => $data['id']
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Dépense mise à jour avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune modification ou dépense introuvable'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
        ]);
    }
}


// Supprimer une dépense
function deleteExpense()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);
    $expenseId = $data['id'] ?? null;

    if (!$expenseId) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la dépense manquant'
        ]);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$expenseId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Dépense supprimée avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Dépense introuvable'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
        ]);
    }
}


// Récupérer toutes les commandes
function getAllOrdersPayments()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM orders_payments ORDER BY id DESC");
    $claims = $stmt->fetchAll();
    echo json_encode($claims);
}


// Récupérer toutes les ventes
function getAllSales()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT 
            id,
            client_id,
            total_quantity,
            total_products,
            total,
            comment,
            payment_method,
            date_of_insertion,
            buyer,
            CASE 
                WHEN status = 'annulé' THEN 'Annulé'
                WHEN status = 'fait' THEN 'Fait'
                ELSE status
            END AS status
        FROM sales
        ORDER BY id DESC
    ");

    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($sales, JSON_UNESCAPED_UNICODE);
}

//add notifications
function addNotification($comment)
{
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO notifications (comment, created_at) VALUES (:comment, :created_at)");
    $stmt->execute([
        ':comment' => $comment,
        ':created_at' => date('Y-m-d H:i:s')
    ]);
}


// Ajouter une nouvelle claim
function addNewClaim($data)
{
    global $pdo;

    if (empty($data['client_id']) || empty($data['amount'])) {
        echo json_encode([
            'success' => false,
            'message' => 'client_id et amount sont requis'
        ]);
        return;
    }

    try {
        // Récupérer le nom du client pour la notification
        $clientStmt = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $clientStmt->execute([':id' => $data['client_id']]);
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        $clientName = $client['name'] ?? 'Client inconnu';

        $sql = "INSERT INTO claims 
            (client_id, amount, date_of_claim, due_date, notes, status)
            VALUES (:client_id, :amount, :date_of_claim, :due_date, :notes, :status)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':client_id'     => $data['client_id'],
            ':amount'        => $data['amount'],
            ':date_of_claim' => $data['date_of_claim'] ?? date('Y-m-d'),
            ':due_date'      => $data['due_date'] ?? null,
            ':notes'         => $data['notes'] ?? '',
            ':status'        => 'Actif'
        ]);

        $claimId = $pdo->lastInsertId();

        // Notification
        addNotification("Nouvelle créance ajoutée pour $clientName (ID: $claimId, Montant: {$data['amount']})");

        echo json_encode([
            'success' => true,
            'claim' => [
                'id' => $claimId,
                'client_id' => $data['client_id'],
                'amount' => $data['amount']
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


// Ajouter un nouveau paiement pour creance
function newClaimPayment($data, $file = null)
{
    global $pdo;

    if (empty($data['claim_id']) || !isset($data['amount']) || empty($data['payment_method'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Champs obligatoires manquants'
        ]);
        return;
    }

    $claim_id = (int) $data['claim_id'];
    $amount = (float) $data['amount'];
    $payment_method = $data['payment_method'];
    $notes = $data['notes'] ?? '';
    $date = $data['date'] ?? date('Y-m-d H:i:s');

    // Gestion fichier
    $fileName = null;
    if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/payments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
    }

    try {
        $pdo->beginTransaction();

        // Récupérer la créance + client
        $claimStmt = $pdo->prepare("SELECT amount, client_id FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) throw new Exception('Créance introuvable');

        $client_id = (int) $claim['client_id'];

        // Récupérer nom du client
        $clientStmt = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $clientStmt->execute([':id' => $client_id]);
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        $clientName = $client['name'] ?? 'Client inconnu';

        // Total déjà payé
        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM claims_payments WHERE claim_id = :claim_id");
        $paidStmt->execute([':claim_id' => $claim_id]);
        $paid = (float) $paidStmt->fetchColumn();

        if ($amount > ($claim['amount'] - $paid)) {
            throw new Exception('Montant supérieur au reste à payer');
        }

        // Insertion paiement
        $stmt = $pdo->prepare("INSERT INTO claims_payments
            (date, amount, claim_id, client_id, payment_method, notes, file)
            VALUES (:date, :amount, :claim_id, :client_id, :payment_method, :notes, :file)");
        $stmt->execute([
            ':date' => $date,
            ':amount' => $amount,
            ':claim_id' => $claim_id,
            ':client_id' => $client_id,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName
        ]);

        $pdo->commit();

        // Notification
        addNotification("Nouveau paiement pour $clientName (Claim ID: $claim_id, Montant: $amount, Méthode: $payment_method)");

        echo json_encode([
            'success' => true,
            'payment' => [
                'id' => $pdo->lastInsertId(),
                'date' => $date,
                'amount' => $amount,
                'claim_id' => $claim_id,
                'client_id' => $client_id,
                'payment_method' => $payment_method,
                'notes' => $notes,
                'file' => $fileName,
                'remaining' => $claim['amount'] - ($paid + $amount)
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


// Modifier un paiement pour créance
function updateClaimPayment($data, $file = null)
{
    global $pdo;

    if (empty($data['id']) || !isset($data['amount']) || empty($data['payment_method'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Champs obligatoires manquants : id, amount ou payment_method'
        ]);
        return;
    }

    $id = (int)$data['id'];
    $newAmount = (float)$data['amount'];
    $newDate = $data['date'] ?? date('Y-m-d');
    $payment_method = $data['payment_method'];
    $notes = $data['notes'] ?? '';

    try {
        $pdo->beginTransaction();

        // Paiement existant
        $stmt = $pdo->prepare("
            SELECT claim_id, amount, date, file 
            FROM claims_payments 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Paiement introuvable");
        }

        $claim_id = (int)$payment['claim_id'];
        $oldAmount = (float)$payment['amount'];
        $oldDate = $payment['date'];
        $oldFile = $payment['file'];

        // Créance + client
        $claimStmt = $pdo->prepare("SELECT amount, client_id FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);
        if (!$claim) throw new Exception("Créance introuvable");

        $clientStmt = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $clientStmt->execute([':id' => $claim['client_id']]);
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        $clientName = $client['name'] ?? 'Client inconnu';

        // Total payé hors paiement courant
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount),0)
            FROM claims_payments
            WHERE claim_id = :claim_id AND id != :id
        ");
        $paidStmt->execute([':claim_id' => $claim_id, ':id' => $id]);
        $paidWithoutCurrent = (float)$paidStmt->fetchColumn();

        if ($newAmount > ($claim['amount'] - $paidWithoutCurrent)) {
            throw new Exception("Montant supérieur au reste à payer");
        }

        // Fichier
        $fileName = $oldFile;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $dir = __DIR__ . '/uploads/payments/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fileName = time() . '_' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $dir . $fileName);
        }

        // Update
        $update = $pdo->prepare("
            UPDATE claims_payments
            SET amount = :amount,
                date = :date,
                payment_method = :payment_method,
                notes = :notes,
                file = :file
            WHERE id = :id
        ");
        $update->execute([
            ':amount' => $newAmount,
            ':date' => $newDate,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName,
            ':id' => $id
        ]);

        $pdo->commit();

        // Notification intelligente
        $changes = [];
        if ($oldAmount != $newAmount) {
            $changes[] = "montant de $oldAmount à $newAmount";
        }
        if ($oldDate != $newDate) {
            $changes[] = "date de $oldDate à $newDate";
        }

        if (!empty($changes)) {
            addNotification(
                "Paiement modifié pour $clientName (Payment ID: $id, " . implode(', ', $changes) . ")"
            );
        }

        echo json_encode([
            'success' => true,
            'payment' => [
                'id' => $id,
                'date' => $newDate,
                'amount' => $newAmount,
                'claim_id' => $claim_id,
                'payment_method' => $payment_method,
                'notes' => $notes,
                'file' => $fileName,
                'remaining' => $claim['amount'] - ($paidWithoutCurrent + $newAmount)
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Erreur mise à jour paiement: ' . $e->getMessage()
        ]);
    }
}


// Supprimer un paiement pour créance
function deleteClaimPayment($data)
{
    global $pdo;

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID du paiement manquant']);
        return;
    }

    $id = intval($data['id']);

    try {
        $pdo->beginTransaction();

        // Vérifier si le paiement existe
        $stmt = $pdo->prepare("SELECT claim_id, file FROM claims_payments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Aucun paiement trouvé pour l'ID fourni.");
        }

        $claim_id = $payment['claim_id'];
        $file = $payment['file'];

        // Récupérer le client lié à la créance
        $claimStmt = $pdo->prepare("SELECT client_id FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);
        $client_id = (int)($claim['client_id'] ?? 0);

        $clientStmt = $pdo->prepare("SELECT name FROM clients WHERE id = :id");
        $clientStmt->execute([':id' => $client_id]);
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
        $clientName = $client['name'] ?? 'Client inconnu';

        // Supprimer le fichier associé si présent
        if (!empty($file)) {
            $filePath = __DIR__ . '/uploads/payments/' . $file;
            if (file_exists($filePath)) unlink($filePath);
        }

        // Supprimer le paiement
        $deleteStmt = $pdo->prepare("DELETE FROM claims_payments WHERE id = :id");
        $deleteStmt->execute([':id' => $id]);

        $pdo->commit();

        // Notification
        addNotification("Paiement supprimé pour $clientName (Payment ID: $id, Claim ID: $claim_id)");

        echo json_encode(['success' => true, 'message' => 'Paiement supprimé avec succès']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression du paiement : ' . $e->getMessage()]);
    }
}



//ajouter un paiement pour commande
function newOrderPayment()
{
    global $pdo;

    // Récupérer les données envoyées
    $data = $_POST ?? [];
    $file = $_FILES['file'] ?? null;

    // Vérification des champs obligatoires
    if (empty($data['order_id']) || !isset($data['amount'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Aucune donnée reçue ou champs obligatoires manquants : order_id ou amount.'
        ]);
        return;
    }

    $amount = floatval($data['amount']);
    $order_id = intval($data['order_id']);
    $notes = $data['notes'] ?? '';
    $date_of_insertion = $data['date_of_insertion'] ?? date('Y-m-d H:i:s');

    // Gestion du fichier si fourni
    $fileName = null;
    if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/order_payments/';
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
        $stmt = $pdo->prepare("INSERT INTO orders_payments 
            (date_of_insertion, amount, order_id, notes, file) 
            VALUES (:date_of_insertion, :amount, :order_id, :notes, :file)");

        $stmt->execute([
            ':date_of_insertion' => $date_of_insertion,
            ':amount' => $amount,
            ':order_id' => $order_id,
            ':notes' => $notes,
            ':file' => $fileName
        ]);

        $newPayment = [
            'id' => $pdo->lastInsertId(),
            'date_of_insertion' => $date_of_insertion,
            'amount' => $amount,
            'order_id' => $order_id,
            'notes' => $notes,
            'file' => $fileName
        ];

        echo json_encode(['success' => true, 'payment' => $newPayment]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'insertion du paiement : ' . $e->getMessage()
        ]);
    }
}

// Modifier un paiement pour commande
function updateOrderPayment()
{
    global $pdo;

    // Récupérer les données envoyées
    $data = $_POST ?? [];
    $file = $_FILES['file'] ?? null;

    // Vérification des champs obligatoires
    if (empty($data['id']) || !isset($data['amount'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Aucune donnée reçue ou champs obligatoires manquants : id ou amount.'
        ]);
        return;
    }

    $payment_id = intval($data['id']);
    $amount = floatval($data['amount']);
    $notes = $data['notes'] ?? '';
    $date_of_insertion = $data['date_of_insertion'] ?? date('Y-m-d H:i:s');

    try {
        // Vérifier si le paiement existe
        $stmt = $pdo->prepare("SELECT * FROM orders_payments WHERE id = :id");
        $stmt->execute([':id' => $payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            echo json_encode(['success' => false, 'error' => 'Paiement introuvable.']);
            return;
        }

        $fileName = $payment['file']; // garder le fichier actuel par défaut

        // Gestion du nouveau fichier si fourni
        if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/order_payments/';
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

        // Mise à jour du paiement
        $update = $pdo->prepare("UPDATE orders_payments SET 
            amount = :amount,
            date_of_insertion = :date_of_insertion,
            notes = :notes,
            file = :file
            WHERE id = :id");

        $update->execute([
            ':amount' => $amount,
            ':date_of_insertion' => $date_of_insertion,
            ':notes' => $notes,
            ':file' => $fileName,
            ':id' => $payment_id
        ]);

        // Retour JSON
        $updatedPayment = [
            'id' => $payment_id,
            'amount' => $amount,
            'date_of_insertion' => $date_of_insertion,
            'notes' => $notes,
            'file' => $fileName
        ];

        echo json_encode(['success' => true, 'payment' => $updatedPayment]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la modification du paiement : ' . $e->getMessage()
        ]);
    }
}

// Supprimer un paiement pour une commande
function deleteOrderPayment()
{
    global $pdo;

    // Récupérer l'ID du paiement à supprimer depuis POST ou JSON
    $data = $_POST ?? [];
    if (empty($data['id'])) {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) $data = $input;
    }

    if (empty($data['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Aucun ID de paiement fourni.'
        ]);
        return;
    }

    $payment_id = intval($data['id']);

    try {
        // Vérifier si le paiement existe et récupérer le fichier associé
        $stmt = $pdo->prepare("SELECT file FROM orders_payments WHERE id = :id");
        $stmt->execute([':id' => $payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            echo json_encode([
                'success' => false,
                'error' => 'Paiement introuvable.'
            ]);
            return;
        }

        // Supprimer le fichier associé si présent
        if (!empty($payment['file'])) {
            $filePath = __DIR__ . '/uploads/order_payments/' . $payment['file'];
            if (file_exists($filePath)) {
                @unlink($filePath); // supprimer sans générer d'erreur
            }
        }

        // Supprimer le paiement dans la base
        $stmt = $pdo->prepare("DELETE FROM orders_payments WHERE id = :id");
        $stmt->execute([':id' => $payment_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Paiement supprimé avec succès.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la suppression du paiement : ' . $e->getMessage()
        ]);
    }
}

// Ajouter une nouvelle commande
function newOrder()
{
    global $pdo;

    // Lire les données JSON envoyées par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data ||
        empty($data['seller']) ||
        !isset($data['total']) ||
        empty($data['lines']) ||
        empty($data['currency']) ||
        empty($data['status'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : seller, total, status, currency ou lines'
        ]);
        return;
    }

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Insérer la commande dans la table orders
        $stmt = $pdo->prepare("
            INSERT INTO orders (date_of_insertion, seller, total, status, currency) 
            VALUES (NOW(), :seller, :total, :status, :currency)
        ");
        $stmt->execute([
            ':seller'   => $data['seller'],
            ':total'    => $data['total'],
            ':status'   => $data['status'],
            ':currency' => $data['currency']
        ]);

        // Récupérer l'ID de la commande nouvellement insérée
        $orderId = $pdo->lastInsertId();

        // Insérer chaque produit de la commande dans la table products
        $stmtProduct = $pdo->prepare("
            INSERT INTO orders_products (date_of_insertion, name, quantity, price, order_id) 
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


// Nouvelle vente
function newSale()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        empty($data['buyer']) ||
        !isset($data['total']) ||
        empty($data['lines']) ||
        !is_array($data['lines'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        return;
    }

    /* ===============================
       DATES FRONT
    =============================== */
    $createdAt = !empty($data['created_at'])
        ? date('Y-m-d H:i:s', strtotime($data['created_at']))
        : date('Y-m-d H:i:s');

    $dateOfOperation = !empty($data['date_of_operation'])
        ? date('Y-m-d', strtotime($data['date_of_operation']))
        : date('Y-m-d');

    $paymentMethod = $data['payment_method'] ?? null;

    /* ===============================
       CLIENT
    =============================== */
    $stmtClient = $pdo->prepare("SELECT id FROM clients WHERE name = :name LIMIT 1");
    $stmtClient->execute([':name' => $data['buyer']]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Client introuvable']);
        return;
    }

    $clientId = $client['id'];

    /* ===============================
       CALCULS
    =============================== */
    $totalQuantity = 0;
    foreach ($data['lines'] as $line) {
        if (empty($line['product']) || !isset($line['quantity'], $line['price'])) {
            echo json_encode(['success' => false, 'message' => 'Ligne invalide']);
            return;
        }
        $totalQuantity += (int)$line['quantity'];
    }

    try {
        $pdo->beginTransaction();

        /* ===============================
           SALES
        =============================== */
        $stmtSale = $pdo->prepare("
            INSERT INTO sales (
                client_id,
                total_quantity,
                total_products,
                total,
                payment_method,
                date_of_operation,
                date_of_insertion,
                buyer,
                status
            ) VALUES (
                :client_id,
                :total_quantity,
                :total_products,
                :total,
                :payment_method,
                :date_of_operation,
                :date_of_insertion,
                :buyer,
                'fait'
            )
        ");

        $stmtSale->execute([
            ':client_id'          => $clientId,
            ':total_quantity'     => $totalQuantity,
            ':total_products'     => count($data['lines']),
            ':total'              => (float)$data['total'],
            ':payment_method'     => $paymentMethod,
            ':date_of_operation'  => $dateOfOperation,
            ':date_of_insertion'  => $createdAt,
            ':buyer'              => $data['buyer']
        ]);

        $saleId = $pdo->lastInsertId();

        /* ===============================
           PREPARED STATEMENTS PRODUITS
        =============================== */
        $stmtSaleProduct = $pdo->prepare("
            INSERT INTO sales_products (
                date_of_insertion,
                name,
                quantity,
                price,
                sale_id
            ) VALUES (
                :date_of_insertion,
                :name,
                :quantity,
                :price,
                :sale_id
            )
        ");

        $stmtGetProduct = $pdo->prepare("SELECT id FROM products WHERE name = :name LIMIT 1");

        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET quantity = quantity - :qty WHERE id = :id
        ");

        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, :created_at)
        ");

        /* ===============================
           TRAITEMENT DES PRODUITS
        =============================== */
        foreach ($data['lines'] as $line) {

            // sales_products.date_of_insertion = date_of_operation
            $stmtSaleProduct->execute([
                ':date_of_insertion' => $dateOfOperation,
                ':name'              => $line['product'],
                ':quantity'          => (int)$line['quantity'],
                ':price'             => (float)$line['price'],
                ':sale_id'           => $saleId
            ]);

            $stmtGetProduct->execute([':name' => $line['product']]);
            $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Produit introuvable : ' . $line['product']);
            }

            $stmtUpdateStock->execute([
                ':qty' => (int)$line['quantity'],
                ':id'  => $product['id']
            ]);

            $stmtHistory->execute([
                ':product_id' => $product['id'],
                ':quantity'   => -(int)$line['quantity'],
                ':comment'    => 'Vente — Client ' . $data['buyer'] . ' — Facture #' . $saleId,
                ':created_at' => $createdAt
            ]);
        }

        /* ===============================
           CLAIMS (CRÉDIT)
        =============================== */
        if ($paymentMethod === 'crédit') {

            // Calcul de la date d'échéance en PHP
            $dueDate = date('Y-m-d', strtotime($dateOfOperation . ' +7 days'));

            $stmtClaim = $pdo->prepare("
                INSERT INTO claims (
                    client_id,
                    amount,
                    date_of_insertion,
                    date_of_claim,
                    due_date,
                    notes,
                    status
                ) VALUES (
                    :client_id,
                    :amount,
                    :date_of_insertion,
                    :date_of_claim,
                    :due_date,
                    :notes,
                    'actif'
                )
            ");

            $stmtClaim->execute([
                ':client_id'         => $clientId,
                ':amount'            => (float)$data['total'],
                ':date_of_insertion' => $createdAt,
                ':date_of_claim'     => $dateOfOperation,
                ':due_date'          => $dueDate,
                ':notes'             => 'Créance facture #' . $saleId
            ]);
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'sale_id' => $saleId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}


//mettre a jour une vente
function updateSale()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        empty($data['id']) ||
        empty($data['buyer']) ||
        !isset($data['total']) ||
        empty($data['lines']) ||
        empty($data['payment_method'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        return;
    }

    $saleId = (int)$data['id'];

    try {
        $pdo->beginTransaction();

        /* ===============================
           CLIENT
        =============================== */
        $stmtClient = $pdo->prepare("SELECT id FROM clients WHERE name = :name LIMIT 1");
        $stmtClient->execute([':name' => $data['buyer']]);
        $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            throw new Exception('Client introuvable');
        }

        $clientId = $client['id'];

        /* ===============================
           INFO VENTE
        =============================== */
        $stmtSaleInfo = $pdo->prepare("
            SELECT payment_method
            FROM sales
            WHERE id = :id
            LIMIT 1
        ");
        $stmtSaleInfo->execute([':id' => $saleId]);
        $saleInfo = $stmtSaleInfo->fetch(PDO::FETCH_ASSOC);

        if (!$saleInfo) {
            throw new Exception('Vente introuvable');
        }

        $oldPaymentMethod = $saleInfo['payment_method'];

        /* ===============================
           ANCIENNES LIGNES
        =============================== */
        $stmtOld = $pdo->prepare("
            SELECT name, quantity
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmtOld->execute([':sale_id' => $saleId]);

        $oldProducts = [];
        foreach ($stmtOld->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $oldProducts[$row['name']] = (int)$row['quantity'];
        }

        /* ===============================
           NOUVELLES LIGNES
        =============================== */
        $newProducts = [];
        foreach ($data['lines'] as $line) {
            $newProducts[$line['product']] = (int)$line['quantity'];
        }

        /* ===============================
           SUPPRESSION ANCIENNES LIGNES
        =============================== */
        $pdo->prepare("DELETE FROM sales_products WHERE sale_id = :id")
            ->execute([':id' => $saleId]);

        /* ===============================
           MAJ VENTE (payment_method uniquement)
        =============================== */
        $totalQty = array_sum($newProducts);

        $pdo->prepare("
            UPDATE sales SET
                total_quantity = :qty,
                total_products = :products,
                total = :total,
                buyer = :buyer,
                payment_method = :payment_method
            WHERE id = :id
        ")->execute([
            ':qty'            => $totalQty,
            ':products'       => count($newProducts),
            ':total'          => (float)$data['total'],
            ':buyer'          => $data['buyer'],
            ':payment_method' => $data['payment_method'],
            ':id'             => $saleId
        ]);

        /* ===============================
           STATEMENTS
        =============================== */
        $stmtInsertSaleProduct = $pdo->prepare("
            INSERT INTO sales_products (date_of_insertion, name, quantity, price, sale_id)
            VALUES (NOW(), :name, :qty, :price, :sale_id)
        ");

        $stmtGetProduct = $pdo->prepare("SELECT id FROM products WHERE name = :name LIMIT 1");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :id");
        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, NOW())
        ");

        /* ===============================
           AJUSTEMENT DES STOCKS
        =============================== */
        // Produits supprimés
        foreach ($oldProducts as $name => $oldQty) {
            if (!isset($newProducts[$name])) {
                $stmtGetProduct->execute([':name' => $name]);
                $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);
                if (!$product) continue;

                $stmtUpdateStock->execute([
                    ':qty' => $oldQty,
                    ':id'  => $product['id']
                ]);

                $stmtHistory->execute([
                    ':product_id' => $product['id'],
                    ':quantity'   => $oldQty,
                    ':comment'    => "Restitution stock — modification facture #{$saleId}"
                ]);
            }
        }

        // Produits ajoutés ou modifiés
        foreach ($newProducts as $name => $newQty) {
            $oldQty = $oldProducts[$name] ?? 0;
            $diff = $newQty - $oldQty;

            $price = 0;
            foreach ($data['lines'] as $l) {
                if ($l['product'] === $name) {
                    $price = (float)$l['price'];
                    break;
                }
            }

            $stmtInsertSaleProduct->execute([
                ':name'    => $name,
                ':qty'     => $newQty,
                ':price'   => $price,
                ':sale_id' => $saleId
            ]);

            if ($diff !== 0) {
                $stmtGetProduct->execute([':name' => $name]);
                $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);
                if (!$product) continue;

                $stmtUpdateStock->execute([
                    ':qty' => -$diff,
                    ':id'  => $product['id']
                ]);

                $stmtHistory->execute([
                    ':product_id' => $product['id'],
                    ':quantity'   => -$diff,
                    ':comment'    => "Ajustement stock — modification facture #{$saleId}"
                ]);
            }
        }

        /* ===============================
           CREANCE
        =============================== */
        $claimNote = 'Créance facture #' . $saleId;

        if ($data['payment_method'] === 'crédit') {

            $stmtCheckClaim = $pdo->prepare("SELECT id FROM claims WHERE notes = :notes LIMIT 1");
            $stmtCheckClaim->execute([':notes' => $claimNote]);
            $claim = $stmtCheckClaim->fetch(PDO::FETCH_ASSOC);

            $dueDate = date('Y-m-d', strtotime('+7 days'));

            if ($claim) {
                $pdo->prepare("
                    UPDATE claims SET
                        amount = :amount,
                        date_of_claim = CURDATE(),
                        due_date = :due_date
                    WHERE id = :id
                ")->execute([
                    ':amount'   => (float)$data['total'],
                    ':due_date' => $dueDate,
                    ':id'       => $claim['id']
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO claims (client_id, amount, date_of_insertion, date_of_claim, due_date, notes, status)
                    VALUES (:client_id, :amount, NOW(), CURDATE(), :due_date, :notes, 'actif')
                ")->execute([
                    ':client_id' => $clientId,
                    ':amount'    => (float)$data['total'],
                    ':due_date'  => $dueDate,
                    ':notes'     => $claimNote
                ]);
            }
        } else {
            $pdo->prepare("DELETE FROM claims WHERE notes = :notes")
                ->execute([':notes' => $claimNote]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}



//annuler une vente
function cancelSale()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la vente manquant'
        ]);
        return;
    }

    $saleId = (int)$data['id'];

    try {
        $pdo->beginTransaction();

        /* ===============================
           VÉRIFIER LA VENTE + CLIENT
        =============================== */
        $stmtSale = $pdo->prepare("
            SELECT s.status, s.buyer, s.payment_method, c.name AS client_name
            FROM sales s
            LEFT JOIN clients c ON c.id = s.client_id
            WHERE s.id = :id
            LIMIT 1
        ");
        $stmtSale->execute([':id' => $saleId]);
        $sale = $stmtSale->fetch(PDO::FETCH_ASSOC);

        if (!$sale) throw new Exception("Vente introuvable");
        if ($sale['status'] === 'annulé') throw new Exception("Cette facture est déjà annulée");

        $clientName = $sale['client_name'] ?? 'Client inconnu';
        $paymentMethod = $sale['payment_method'];

        /* ===============================
           PRODUITS DE LA VENTE
        =============================== */
        $stmtProducts = $pdo->prepare("
            SELECT name, quantity
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmtProducts->execute([':sale_id' => $saleId]);
        $lines = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

        if (empty($lines)) throw new Exception("Aucun produit lié à cette vente");

        /* ===============================
           STATEMENTS
        =============================== */
        $stmtGetProduct = $pdo->prepare("SELECT id FROM products WHERE name = :name LIMIT 1");
        $stmtUpdateStock = $pdo->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :id");
        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, NOW())
        ");
        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (comment, created_at)
            VALUES (:comment, NOW())
        ");

        /* ===============================
           RESTITUTION DU STOCK ET NOTIFICATIONS
        =============================== */
        foreach ($lines as $line) {
            $stmtGetProduct->execute([':name' => $line['name']]);
            $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);
            if (!$product) throw new Exception("Produit introuvable : " . $line['name']);

            $qty = (int)$line['quantity'];

            $stmtUpdateStock->execute([
                ':qty' => $qty,
                ':id'  => $product['id']
            ]);

            $stmtHistory->execute([
                ':product_id' => $product['id'],
                ':quantity'   => $qty,
                ':comment'    => "Ajout stock suite à l'annulation de la facture #{$saleId} pour le client {$clientName}"
            ]);

            $stmtNotif->execute([
                ':comment' => "ANNULATION FACTURE #{$saleId} – Client {$clientName} : restitution de {$qty} {$line['name']}"
            ]);
        }

        /* ===============================
           SUPPRESSION DE LA CRÉANCE SI CRÉDIT
        =============================== */
        if ($paymentMethod === 'crédit') {
            $claimNote = 'Créance facture #' . $saleId;

            // Supprimer la créance
            $stmtDeleteClaim = $pdo->prepare("DELETE FROM claims WHERE notes = :notes");
            $stmtDeleteClaim->execute([':notes' => $claimNote]);

            // Notification créance supprimée
            $stmtNotif->execute([
                ':comment' => "SUPPRESSION CRÉANCE facture #{$saleId} pour le client {$clientName}"
            ]);
        }

        /* ===============================
           MISE À JOUR STATUT FACTURE
        =============================== */
        $pdo->prepare("
            UPDATE sales SET status = 'annulé'
            WHERE id = :id
        ")->execute([':id' => $saleId]);

        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Supprimer une créance
function deleteClaim()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la créance manquant'
        ]);
        return;
    }

    $claimId = (int) $data['id'];

    try {
        // Récupérer créance + client
        $claimStmt = $pdo->prepare("
            SELECT c.amount AS total_amount, c.client_id, cl.name
            FROM claims c
            LEFT JOIN clients cl ON cl.id = c.client_id
            WHERE c.id = :id
        ");
        $claimStmt->execute([':id' => $claimId]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new Exception('Créance introuvable');
        }

        $clientName   = $claim['name'] ?? 'Client inconnu';
        $totalAmount  = (float) $claim['total_amount'];

        // Montant total payé
        $paidStmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM claims_payments
            WHERE claim_id = :claim_id
        ");
        $paidStmt->execute([':claim_id' => $claimId]);
        $totalPaid = (float) $paidStmt->fetchColumn();

        // Suppression créance
        $stmt = $pdo->prepare("DELETE FROM claims WHERE id = :id");
        $stmt->execute([':id' => $claimId]);

        // Notification détaillée
        addNotification(
            "Créance supprimée pour $clientName (Claim ID: $claimId, "
                . "Montant total: $totalAmount, Montant payé: $totalPaid)"
        );

        echo json_encode([
            'success' => true,
            'message' => 'Créance supprimée avec succès'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
        ]);
    }
}




// supprimer un produit grâce à son id
function deleteOrderProduct()
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
        $stmt = $pdo->prepare("SELECT order_id FROM orders_products WHERE id = :id");
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
        $stmt = $pdo->prepare("DELETE FROM orders_products WHERE id = :id");
        $stmt->execute([':id' => $productId]);

        // Recalculer le total de la commande
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM orders_products
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

//supprimer une ligne de produit de ventes
// Supprimer un produit d'une vente grâce à son id
function deleteSaleProduct()
{
    global $pdo;

    // Récupérer l'ID envoyé (DELETE ne transmet pas de body avec Axios par défaut)
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

        // Récupérer l'id de la vente avant suppression
        $stmt = $pdo->prepare("SELECT sale_id FROM sales_products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            echo json_encode([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
            return;
        }

        $saleId = $sale['sale_id'];

        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM sales_products WHERE id = :id");
        $stmt->execute([':id' => $productId]);

        // Recalculer le total de la vente
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmt->execute([':sale_id' => $saleId]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour le total dans sales
        $stmt = $pdo->prepare("UPDATE sales SET total = :total WHERE id = :sale_id");
        $stmt->execute([
            ':total'    => $newTotal,
            ':sale_id'  => $saleId
        ]);

        // Commit
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Produit supprimé et vente mise à jour',
            'sale_id' => $saleId,
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
        $stmtProducts = $pdo->prepare("DELETE FROM orders_products WHERE order_id = ?");
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

// Supprimer une vente
function deleteSale()
{
    global $pdo;

    // Récupérer le body JSON envoyé par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la vente manquant'
        ]);
        return;
    }

    $saleId = intval($data['id']);

    try {
        // Supprimer d'abord les produits associés à la vente (si applicable)
        $stmtProducts = $pdo->prepare("DELETE FROM sales_products WHERE sale_id = ?");
        $stmtProducts->execute([$saleId]);

        // Supprimer ensuite la vente
        $stmtSale = $pdo->prepare("DELETE FROM sales WHERE id = ?");
        $stmtSale->execute([$saleId]);

        if ($stmtSale->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Vente supprimée avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Vente introuvable'
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
function updateOrderProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $data['id'] ?? null; // <-- corrige ici

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
        $stmt = $pdo->prepare("SELECT order_id FROM orders_products WHERE id = :id");
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
            UPDATE orders_products
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
            FROM orders_products
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

// Mettre à jour un produit existant d'une vente
function updateSaleProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);
    $productId = $data['id'] ?? null;

    if (!$productId || empty($data['name']) || !isset($data['quantity']) || !isset($data['price'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : id, name, quantity, price'
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Récupérer le sale_id du produit avant mise à jour
        $stmt = $pdo->prepare("SELECT sale_id FROM sales_products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode([
                'success' => false,
                'message' => 'Produit introuvable'
            ]);
            return;
        }

        $saleId = $product['sale_id'];

        // Mettre à jour le produit
        $stmt = $pdo->prepare("
            UPDATE sales_products
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

        // Recalculer le total de la vente
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmt->execute([':sale_id' => $saleId]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour le total dans sales
        $stmt = $pdo->prepare("UPDATE sales SET total = :total WHERE id = :sale_id");
        $stmt->execute([
            ':total'   => $newTotal,
            ':sale_id' => $saleId
        ]);

        $pdo->commit();

        echo json_encode([
            'success'    => true,
            'message'    => 'Produit mis à jour et vente recalculée',
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


//ajouter un nouveau produit pour les commandes
function newOrderProduct()
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
            INSERT INTO orders_products (date_of_insertion, order_id, name, quantity, price)
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
            FROM orders_products
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

//ajouter un nouveau produit pour les ventes
// Ajouter un nouveau produit à une vente
function newSaleProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data ||
        empty($data['sale_id']) ||
        empty($data['name']) ||
        !isset($data['quantity']) ||
        !isset($data['price'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : sale_id, name, quantity, price'
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Insérer le produit
        $stmt = $pdo->prepare("
            INSERT INTO sales_products (date_of_insertion, sale_id, name, quantity, price)
            VALUES (NOW(), :sale_id, :name, :quantity, :price)
        ");
        $stmt->execute([
            ':sale_id' => $data['sale_id'],
            ':name'    => $data['name'],
            ':quantity' => $data['quantity'],
            ':price'   => $data['price']
        ]);

        $productId = $pdo->lastInsertId();

        // Recalculer le total de la vente
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmt->execute([':sale_id' => $data['sale_id']]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour le total dans la table sales
        $stmt = $pdo->prepare("UPDATE sales SET total = :total WHERE id = :sale_id");
        $stmt->execute([
            ':total'   => $newTotal,
            ':sale_id' => $data['sale_id']
        ]);

        $pdo->commit();

        echo json_encode([
            'success'    => true,
            'message'    => 'Produit ajouté et vente mise à jour',
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
function getAllClaimsPayments()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM claims_payments ORDER BY id DESC");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($payments, JSON_UNESCAPED_UNICODE);
}


// Récupérer toutes les orders
function getAllOrders()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
    $orders = $stmt->fetchAll();
    echo json_encode($orders);
}

// Récupérer toutes les produits d'une commande
function getAllOrdersProducts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM orders_products ORDER BY id DESC");
    $products = $stmt->fetchAll();
    echo json_encode($products);
}

// Récupérer toutes les produits d'une vente
function getAllSalesProducts()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM sales_products ORDER BY id DESC");
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

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];

        // Chemin racine du projet (ex: /gbemiro)
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/api');

        echo json_encode([
            'success' => true,
            'redirect' => $scheme . '://' . $host . $basePath . '/index.php'
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

    header('Location: /login.php');
    exit; // Important pour arrêter le script après la redirection
}
