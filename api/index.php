<?php
header('Content-Type: application/json');
include 'functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'allClaims':
        getAllClaims();
        break;

    case 'newClaim':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            addNewClaim($data);
        } else {
            echo json_encode(['error' => 'Aucune donnée reçue']);
        }
        break;

    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}
