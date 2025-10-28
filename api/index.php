<?php
header('Content-Type: application/json');
include 'functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'allClaims':
        getAllClaims();
        break;

    case 'allSales':
        getAllSales();
        break;

    case 'allPayments':
        getAllPayments();
        break;

    case 'allOrders':
        getAllOrders();
        break;

    case 'allOrdersProducts':
        getAllOrdersProducts();
        break;

    case 'allSalesProducts':
        getAllSalesProducts();
        break;

    case 'allOrdersPayments':
        getAllOrdersPayments();
        break;

    case 'newOrder':
        newOrder();
        break;

    case 'newSale':
        newSale();
        break;

    case 'newOrderPayment':
        newOrderPayment();
        break;

    case 'deleteOrderPayment':
        deleteOrderPayment();
        break;

    case 'updateOrderPayment':
        updateOrderPayment();
        break;

    case 'deleteOrderProduct':
        deleteOrderProduct();
        break;

    case 'deleteSaleProduct':
        deleteSaleProduct();
        break;

    case 'deleteOrder':
        deleteOrder();
        break;

    case 'deleteSale':
        deleteSale();
        break;

    case 'updateOrderProduct':
        updateOrderProduct();
        break;

    case 'updateSaleProduct':
        updateSaleProduct();
        break;

    case 'updateOrderStatus':
        updateOrderStatus();
        break;

    case 'newOrderProduct':
        newOrderProduct();
        break;

    case 'newSaleProduct':
        newSaleProduct();
        break;

    case 'deleteClaim':
        deleteClaim();
        break;

    case 'newClaim':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data) {
            addNewClaim($data);
        } else {
            echo json_encode(['error' => 'Aucune donnée reçue']);
        }
        break;

    case 'newClaimPayment':
        // FormData envoyé via POST
        $data = $_POST;
        $file = $_FILES['file'] ?? null;

        if ($data) {
            newClaimPayment($data, $file);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Aucune donnée reçue pour le paiement.'
            ]);
        }
        break;

    case 'updateClaimPayment':
        $data = $_POST;
        $file = $_FILES['file'] ?? null;
        updateClaimPayment($data, $file);
        break;

    case 'deleteClaimPayment':
        // Passer les données POST à la fonction
        deleteClaimPayment($_POST);
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
