<?php
header('Content-Type: application/json');
include 'functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'allClaims':
        getAllClaims();
        break;

    case 'allPayments':
        getAllPayments();
        break;

    case 'allOrders':
        getAllOrders();
        break;

    case 'allProducts':
        getAllProducts();
        break;

    case 'newOrder':
        newOrder();
        break;

    case 'deleteProduct':
        deleteProduct();
        break;

    case 'deleteOrder':
        deleteOrder();
        break;

    case 'updateProduct':
        updateProduct();
        break;

    case 'updateOrderStatus':
        updateOrderStatus();
        break;

    case 'newProduct':
        newProduct();
        break;

    case 'newClaim':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            addNewClaim($data);
        } else {
            echo json_encode(['error' => 'Aucune donnée reçue']);
        }
        break;

    case 'newPayment':
        // FormData envoyé via POST
        $data = $_POST;
        $file = $_FILES['file'] ?? null;

        if ($data) {
            newPayment($data, $file);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Aucune donnée reçue pour le paiement.'
            ]);
        }
        break;


    case 'login':
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"), true);
        login($data);
        break;

    case 'logout':
        logout();
        break;

    default:
        echo json_encode(['error' => 'Action non reconnue']);
        break;
}
