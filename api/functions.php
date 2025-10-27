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
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY id DESC");
    $sales = $stmt->fetchAll();
    echo json_encode($sales);
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


// Ajouter une nouvelle commande
function newSale()
{
    global $pdo;

    // Lire les données JSON envoyées par Axios
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data ||
        empty($data['buyer']) ||
        !isset($data['total']) ||
        empty($data['lines']) ||
        empty($data['currency']) ||
        empty($data['status'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => 'Champs obligatoires manquants : buyer, total, status, currency ou lines'
        ]);
        return;
    }

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Insérer la commande dans la table orders
        $stmt = $pdo->prepare("
            INSERT INTO orders (date_of_insertion, buyer, total, status, currency) 
            VALUES (NOW(), :buyer, :total, :status, :currency)
        ");
        $stmt->execute([
            ':buyer'   => $data['buyer'],
            ':total'    => $data['total'],
            ':status'   => $data['status'],
            ':currency' => $data['currency']
        ]);

        // Récupérer l'ID de la commande nouvellement insérée
        $saleId = $pdo->lastInsertId();

        // Insérer chaque produit de la commande dans la table products
        $stmtProduct = $pdo->prepare("
            INSERT INTO sales_products (date_of_insertion, name, quantity, price, sale_id) 
            VALUES (NOW(), :name, :quantity, :price, :order_id)
        ");

        foreach ($data['lines'] as $line) {
            $stmtProduct->execute([
                ':name'     => $line['product'],
                ':quantity' => $line['quantity'],
                ':price'    => $line['price'],
                ':sale_id' => $saleId
            ]);
        }

        // Valider la transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Commande et produits insérés avec succès',
            'order_id' => $saleId
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
function newSaleProduct()
{
    global $pdo;

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['sale _id']) || empty($data['name']) || !isset($data['quantity']) || !isset($data['price'])) {
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
            ':name'     => $data['name'],
            ':quantity' => $data['quantity'],
            ':price'    => $data['price']
        ]);

        $productId = $pdo->lastInsertId();

        // Recalculer le total de la commande
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(price * quantity), 0) AS total
            FROM sales_products
            WHERE sale_id = :sale_id
        ");
        $stmt->execute([':sale_id' => $data['sale_id']]);
        $newTotal = $stmt->fetchColumn();

        // Mettre à jour la commande
        $stmt = $pdo->prepare("UPDATE sales SET total = :total WHERE id = :sale_id");
        $stmt->execute([
            ':total'    => $newTotal,
            ':sale_id' => $data['sale_id']
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
