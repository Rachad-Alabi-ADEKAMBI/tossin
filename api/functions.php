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
        $status = 'pending';

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
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/payments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de l\'upload du fichier.'
            ]);
            return;
        }
    }

    // Préparer la requête d'insertion du paiement
    $sql = "INSERT INTO payments (date_of_insertion, amount, claim_id, payment_method, notes, file)
            VALUES (:date_of_insertion, :amount, :claim_id, :payment_method, :notes, :file)";
    $stmt = $pdo->prepare($sql);

    try {
        $pdo->beginTransaction();

        // Insertion du paiement
        $stmt->execute([
            ':date_of_insertion' => $date_of_insertion,
            ':amount' => $amount,
            ':claim_id' => $claim_id,
            ':payment_method' => $payment_method,
            ':notes' => $notes,
            ':file' => $fileName
        ]);

        // Mise à jour du remaining_amount dans claims
        $update = $pdo->prepare("UPDATE claims 
                                 SET remaining_amount = remaining_amount - :amount 
                                 WHERE id = :claim_id");
        $update->execute([
            ':amount' => $amount,
            ':claim_id' => $claim_id
        ]);

        $pdo->commit();

        $newPayment = [
            'id' => $pdo->lastInsertId(),
            'date_of_insertion' => $date_of_insertion,
            'amount' => $amount,
            'claim_id' => $claim_id,
            'payment_method' => $payment_method,
            'notes' => $notes,
            'file' => $fileName
        ];

        echo json_encode([
            'success' => true,
            'payment' => $newPayment
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'insertion en base : ' . $e->getMessage()
        ]);
    }
}
