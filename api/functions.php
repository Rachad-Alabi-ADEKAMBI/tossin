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

//create Client
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

// Créer une nouvelle dépense
function newExpense()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['name']) || empty($data['amount']) || empty($data['currency'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs requis : name, amount, currency'
        ]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO expenses (name, amount, currency, notes, created_at)
            VALUES (:name, :amount, :currency, :notes, NOW())
        ");
        $stmt->execute([
            ':name'     => $data['name'],
            ':amount'   => $data['amount'],
            ':currency' => $data['currency'],
            ':notes'    => $data['notes'] ?? null
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Dépense ajoutée avec succès',
            'id'      => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l’ajout : ' . $e->getMessage()
        ]);
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


// Ajouter une nouvelle claim
function addNewClaim($data)
{
    global $pdo;

    try {
        $sql = "INSERT INTO claims 
            (client_name, client_phone, amount, date_of_claim, due_date, notes, status, currency)
            VALUES (:client_name, :client_phone, :amount, :date_of_claim, :due_date, :notes, :status, :currency)";

        $stmt = $pdo->prepare($sql);

        // Vérification et assignation des valeurs par défaut si nécessaire
        $client_name = $data['client_name'] ?? '';
        $client_phone = $data['client_phone'] ?? '';
        $amount = $data['amount'] ?? 0;
        $date_of_claim = $data['date_of_claim'] ?? date('Y-m-d');
        $due_date = $data['due_date'] ?? null;
        $notes = $data['notes'] ?? '';
        $status = 'Actif';
        $currency = $data['currency'] ?? 'XOF'; // valeur par défaut

        // Exécuter la requête
        $stmt->execute([
            ':client_name' => $client_name,
            ':client_phone' => $client_phone,
            ':amount' => $amount,
            ':date_of_claim' => $date_of_claim,
            ':due_date' => $due_date,
            ':notes' => $notes,
            ':status' => $status,
            ':currency' => $currency
        ]);

        // Récupérer l'id de la nouvelle claim et renvoyer le tout
        $newClaim = [
            'id' => $pdo->lastInsertId(),
            'client_name' => $client_name,
            'client_phone' => $client_phone,
            'amount' => $amount,
            'date_of_claim' => $date_of_claim,
            'due_date' => $due_date,
            'notes' => $notes,
            'status' => $status,
            'currency' => $currency
        ];

        echo json_encode(['success' => true, 'claim' => $newClaim]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}


// Ajouter un nouveau paiement pour creance
function newClaimPayment($data, $file = null)
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
        $claimStmt = $pdo->prepare("SELECT amount, client_name FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new Exception("Aucune créance trouvée pour l'ID fourni.");
        }

        // Calcul du total déjà payé
        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total_paid FROM payments WHERE claim_id = :claim_id");
        $paidStmt->execute([':claim_id' => $claim_id]);
        $paid = $paidStmt->fetch(PDO::FETCH_ASSOC)['total_paid'];

        $reste = $claim['amount'] - $paid;

        if ($amount > $reste) {
            throw new Exception("Le montant du paiement ({$amount}) dépasse le montant restant dû ({$reste}).");
        }

        $client_name = $claim['client_name'];

        // Insertion du paiement
        $sql = "INSERT INTO payments 
                (date_of_insertion, amount, claim_id, payment_method, notes, file, client_name)
                VALUES (:date_of_insertion, :amount, :claim_id, :payment_method, :notes, :file, :client_name)";
        $stmt = $pdo->prepare($sql);

        if (!$stmt->execute([
            ':date_of_insertion' => $date_of_insertion,
            ':amount' => $amount,
            ':claim_id' => $claim_id,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName,
            ':client_name' => $client_name
        ])) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Erreur PDO lors de l'insertion du paiement : " . $errorInfo[2]);
        }

        $pdo->commit();

        // Recalcul du restant après insertion
        $newPaid = $paid + $amount;
        $newRemaining = $claim['amount'] - $newPaid;

        $newPayment = [
            'id' => $pdo->lastInsertId(),
            'date_of_insertion' => $date_of_insertion,
            'amount' => $amount,
            'claim_id' => $claim_id,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'file' => $fileName,
            'client_name' => $client_name,
            'remaining' => $newRemaining
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

    $id = intval($data['id']);
    $amount = floatval($data['amount']);
    $payment_method = $data['payment_method'];
    $notes = $data['notes'] ?? '';
    $date_of_insertion = $data['date_of_insertion'] ?? date('Y-m-d H:i:s');

    try {
        $pdo->beginTransaction();

        // Vérifier si le paiement existe
        $stmt = $pdo->prepare("SELECT claim_id, amount, file FROM payments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Aucun paiement trouvé pour l'ID fourni.");
        }

        $claim_id = $payment['claim_id'];
        $originalAmount = $payment['amount'];
        $oldFile = $payment['file'];

        // Récupérer la créance
        $claimStmt = $pdo->prepare("SELECT amount, client_name FROM claims WHERE id = :id");
        $claimStmt->execute([':id' => $claim_id]);
        $claim = $claimStmt->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new Exception("Aucune créance trouvée pour cet ID.");
        }

        // Calcul du total payé hors ce paiement
        $paidStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) AS total_paid 
                                   FROM payments 
                                   WHERE claim_id = :claim_id AND id != :id");
        $paidStmt->execute([':claim_id' => $claim_id, ':id' => $id]);
        $paidWithoutCurrent = $paidStmt->fetch(PDO::FETCH_ASSOC)['total_paid'];

        $maxAllowed = $claim['amount'] - $paidWithoutCurrent;

        if ($amount > $maxAllowed) {
            throw new Exception("Le montant du paiement ({$amount}) dépasse le montant restant autorisé ({$maxAllowed}).");
        }

        // Gestion fichier
        $fileName = $oldFile;
        if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/payments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = time() . '_' . basename($file['name']);
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                throw new Exception("Erreur lors de l'upload du fichier.");
            }
        }

        // Update paiement
        $updateSql = "UPDATE payments 
                      SET amount = :amount,
                          date_of_insertion = :date_of_insertion,
                          payment_method = :payment_method,
                          notes = :notes,
                          file = :file
                      WHERE id = :id";

        $updateStmt = $pdo->prepare($updateSql);
        if (!$updateStmt->execute([
            ':amount' => $amount,
            ':date_of_insertion' => $date_of_insertion,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName,
            ':id' => $id
        ])) {
            $errorInfo = $updateStmt->errorInfo();
            throw new Exception("Erreur PDO lors de la mise à jour : " . $errorInfo[2]);
        }

        // Nouveau restant
        $newRemaining = $claim['amount'] - ($paidWithoutCurrent + $amount);

        // Mettre à jour le statut de la créance
        $newStatus = ($newRemaining == 0) ? 'Soldé' : 'En cours';
        $statusStmt = $pdo->prepare("UPDATE claims SET status = :status WHERE id = :id");
        $statusStmt->execute([':status' => $newStatus, ':id' => $claim_id]);

        $pdo->commit();

        // Retourner le paiement mis à jour + nouveau restant + statut
        $updatedPayment = [
            'id' => $id,
            'date_of_insertion' => $date_of_insertion,
            'amount' => $amount,
            'claim_id' => $claim_id,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'file' => $fileName,
            'client_name' => $claim['client_name'],
            'remaining' => $newRemaining,
            'status' => $newStatus
        ];

        echo json_encode(['success' => true, 'payment' => $updatedPayment]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la mise à jour du paiement : ' . $e->getMessage()
        ]);
    }
}


// Supprimer un paiement pour créance
function deleteClaimPayment($data)
{
    global $pdo;

    if (empty($data['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'ID du paiement manquant'
        ]);
        return;
    }

    $id = intval($data['id']);

    try {
        $pdo->beginTransaction();

        // Vérifier si le paiement existe
        $stmt = $pdo->prepare("SELECT file FROM payments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Aucun paiement trouvé pour l'ID fourni.");
        }

        // Supprimer le fichier associé si présent
        if (!empty($payment['file'])) {
            $filePath = __DIR__ . '/uploads/payments/' . $payment['file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Supprimer le paiement
        $deleteStmt = $pdo->prepare("DELETE FROM payments WHERE id = :id");
        if (!$deleteStmt->execute([':id' => $id])) {
            $errorInfo = $deleteStmt->errorInfo();
            throw new Exception("Erreur PDO lors de la suppression : " . $errorInfo[2]);
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Paiement supprimé avec succès']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de la suppression du paiement : ' . $e->getMessage()
        ]);
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

//nouvelle vente
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
        echo json_encode([
            'success' => false,
            'message' => 'Données manquantes ou invalides'
        ]);
        return;
    }

    // Récupérer l'ID du client
    $stmtClient = $pdo->prepare("SELECT id FROM clients WHERE name = :name LIMIT 1");
    $stmtClient->execute([':name' => $data['buyer']]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode([
            'success' => false,
            'message' => 'Client introuvable'
        ]);
        return;
    }

    $clientId = $client['id'];

    // Calculs
    $totalQuantity = 0;
    $totalProducts = count($data['lines']);

    foreach ($data['lines'] as $line) {
        if (
            empty($line['product']) ||
            !isset($line['quantity']) ||
            !isset($line['price'])
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Ligne produit invalide'
            ]);
            return;
        }

        $totalQuantity += (int)$line['quantity'];
    }

    try {
        $pdo->beginTransaction();

        // Insertion vente
        $stmtSale = $pdo->prepare("
            INSERT INTO sales (
                client_id,
                total_quantity,
                total_products,
                total,
                comment,
                payment_method,
                date_of_insertion,
                buyer,
                status
            ) VALUES (
                :client_id,
                :total_quantity,
                :total_products,
                :total,
                NULL,
                NULL,
                NOW(),
                :buyer,
                'fait'
            )
        ");

        $stmtSale->execute([
            ':client_id'      => $clientId,
            ':total_quantity' => $totalQuantity,
            ':total_products' => $totalProducts,
            ':total'          => (float)$data['total'],
            ':buyer'          => $data['buyer']
        ]);

        $saleId = $pdo->lastInsertId();

        // Statements réutilisables
        $stmtProductSale = $pdo->prepare("
            INSERT INTO sales_products (date_of_insertion, name, quantity, price, sale_id)
            VALUES (NOW(), :name, :quantity, :price, :sale_id)
        ");

        $stmtGetProduct = $pdo->prepare("
            SELECT id, quantity FROM products WHERE name = :name LIMIT 1
        ");

        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET quantity = quantity - :qty WHERE id = :id
        ");

        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, NOW())
        ");

        $stmtNotification = $pdo->prepare("
            INSERT INTO notifications (comment, created_at)
            VALUES (:comment, NOW())
        ");

        foreach ($data['lines'] as $line) {

            // Enregistrer produit vendu
            $stmtProductSale->execute([
                ':name'     => $line['product'],
                ':quantity' => (int)$line['quantity'],
                ':price'    => (float)$line['price'],
                ':sale_id'  => $saleId
            ]);

            // Récupérer le produit
            $stmtGetProduct->execute([':name' => $line['product']]);
            $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('Produit introuvable : ' . $line['product']);
            }

            // Mise à jour du stock
            $stmtUpdateStock->execute([
                ':qty' => (int)$line['quantity'],
                ':id'  => $product['id']
            ]);

            // Historique produit (avec client)
            $stmtHistory->execute([
                ':product_id' => $product['id'],
                ':quantity'   => -(int)$line['quantity'],
                ':comment'    => 'Vente au client ' . $data['buyer'] . ' (commande #' . $saleId . ')'
            ]);

            // Notification (avec client)
            $stmtNotification->execute([
                ':comment' => 'Vente de ' . $line['quantity'] . ' unité(s) de ' . $line['product'] . ' au client ' . $data['buyer']
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'sale_id' => $saleId
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
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
        empty($data['lines'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        return;
    }

    $saleId = (int)$data['id'];

    try {
        $pdo->beginTransaction();

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
           MAJ VENTE
        =============================== */
        $totalQty = array_sum($newProducts);

        $pdo->prepare("
            UPDATE sales SET
                total_quantity = :qty,
                total_products = :products,
                total = :total,
                buyer = :buyer
            WHERE id = :id
        ")->execute([
            ':qty'      => $totalQty,
            ':products' => count($newProducts),
            ':total'    => $data['total'],
            ':buyer'    => $data['buyer'],
            ':id'       => $saleId
        ]);

        /* ===============================
           STATEMENTS
        =============================== */
        $stmtInsertSaleProduct = $pdo->prepare("
            INSERT INTO sales_products (date_of_insertion, name, quantity, price, sale_id)
            VALUES (NOW(), :name, :qty, :price, :sale_id)
        ");

        $stmtGetProduct = $pdo->prepare("
            SELECT id FROM products WHERE name = :name LIMIT 1
        ");

        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET quantity = quantity + :qty WHERE id = :id
        ");

        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, NOW())
        ");

        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (comment, created_at)
            VALUES (:comment, NOW())
        ");

        /* ===============================
           1️⃣ PRODUITS SUPPRIMÉS
        =============================== */
        foreach ($oldProducts as $name => $oldQty) {
            if (!isset($newProducts[$name])) {

                $stmtGetProduct->execute([':name' => $name]);
                $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);

                if (!$product) continue;

                // RESTITUTION STOCK
                $stmtUpdateStock->execute([
                    ':qty' => $oldQty,
                    ':id'  => $product['id']
                ]);

                $stmtHistory->execute([
                    ':product_id' => $product['id'],
                    ':quantity'   => $oldQty,
                    ':comment'    => "Ajout stock suite à la modification de la facture #{$saleId}"
                ]);

                $stmtNotif->execute([
                    ':comment' => "AJOUT STOCK : {$oldQty} {$name} (facture #{$saleId} modifiée)"
                ]);
            }
        }

        /* ===============================
           2️⃣ PRODUITS AJOUTÉS / MODIFIÉS
        =============================== */
        foreach ($newProducts as $name => $newQty) {

            $oldQty = $oldProducts[$name] ?? 0;
            $diff   = $newQty - $oldQty;

            // Réinsertion ligne vente
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

            if ($diff === 0) continue;

            $stmtGetProduct->execute([':name' => $name]);
            $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Produit introuvable : $name");
            }

            // DIFF STOCK
            $stmtUpdateStock->execute([
                ':qty' => -$diff,
                ':id'  => $product['id']
            ]);

            if ($diff > 0) {
                // RETRAIT
                $stmtHistory->execute([
                    ':product_id' => $product['id'],
                    ':quantity'   => -$diff,
                    ':comment'    => "Retrait stock suite à la modification de la facture #{$saleId}"
                ]);

                $stmtNotif->execute([
                    ':comment' => "RETRAIT STOCK : {$diff} {$name} (facture #{$saleId} modifiée)"
                ]);
            } else {
                // AJOUT
                $stmtHistory->execute([
                    ':product_id' => $product['id'],
                    ':quantity'   => abs($diff),
                    ':comment'    => "Ajout stock suite à la modification de la facture #{$saleId}"
                ]);

                $stmtNotif->execute([
                    ':comment' => "AJOUT STOCK : " . abs($diff) . " {$name} (facture #{$saleId} modifiée)"
                ]);
            }
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
           VÉRIFIER LA VENTE
        =============================== */
        $stmtSale = $pdo->prepare("
            SELECT status, buyer
            FROM sales
            WHERE id = :id
            LIMIT 1
        ");
        $stmtSale->execute([':id' => $saleId]);
        $sale = $stmtSale->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            throw new Exception("Vente introuvable");
        }

        if ($sale['status'] === 'annulé') {
            throw new Exception("Cette facture est déjà annulée");
        }

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

        if (empty($lines)) {
            throw new Exception("Aucun produit lié à cette vente");
        }

        /* ===============================
           STATEMENTS
        =============================== */
        $stmtGetProduct = $pdo->prepare("
            SELECT id FROM products WHERE name = :name LIMIT 1
        ");

        $stmtUpdateStock = $pdo->prepare("
            UPDATE products SET quantity = quantity + :qty WHERE id = :id
        ");

        $stmtHistory = $pdo->prepare("
            INSERT INTO products_history (product_id, quantity, comment, created_at)
            VALUES (:product_id, :quantity, :comment, NOW())
        ");

        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (comment, created_at)
            VALUES (:comment, NOW())
        ");

        /* ===============================
           RESTITUTION DU STOCK
        =============================== */
        foreach ($lines as $line) {

            $stmtGetProduct->execute([':name' => $line['name']]);
            $product = $stmtGetProduct->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Produit introuvable : " . $line['name']);
            }

            // AJOUT AU STOCK
            $stmtUpdateStock->execute([
                ':qty' => (int)$line['quantity'],
                ':id'  => $product['id']
            ]);

            // HISTORIQUE
            $stmtHistory->execute([
                ':product_id' => $product['id'],
                ':quantity'   => (int)$line['quantity'],
                ':comment'    => "Ajout stock suite à l'annulation de la facture #{$saleId}"
            ]);

            // NOTIFICATION
            $stmtNotif->execute([
                ':comment' => "ANNULATION FACTURE #{$saleId} : restitution de {$line['quantity']} {$line['name']}"
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

    // Lire les données JSON envoyées par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de la créance manquant'
        ]);
        return;
    }

    $claimId = $data['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM claims WHERE id = :id");
        $stmt->execute([':id' => $claimId]);

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

        echo json_encode([
            'success' => true,
            'redirect' => 'http://127.0.0.1/Gbemiro/index.php'
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

    header('Location: ../login.php');
    exit; // Important pour arrêter le script après la redirection
}
