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
