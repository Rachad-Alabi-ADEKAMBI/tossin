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

    case 'allExpenses':
        getAllExpenses();
        break;

    case 'allClaimsPayments':
        getAllClaimsPayments();
        break;

    case 'allOrders':
        getAllOrders();
        break;

    case 'create':
        createProduct();
        break;


    case 'allProducts':
        getAllProducts();
        break;

    case 'update':
        updateProduct();
        break;

    case 'adjust_stock':
        adjustProductStock();
        break;

    case 'deleteProduct':
        deleteProduct();
        break;

    case 'allClients':
        getAllClients();
        break;

    case 'history':
        getProductHistory();
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

    case 'newExpense':
        newExpense();
        break;

    case 'updateExpense':
        updateExpense();
        break;

    case 'deleteExpense':
        deleteExpense();
        break;

    case 'newSale':
        newSale();
        break;

    case 'updateSale':
        updateSale();
        break;

    case 'cancelSale':
        cancelSale();
        break;

    case 'createClient':
        createClient();
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
        // Données JSON envoyées par Axios
        $data = json_decode(file_get_contents('php://input'), true);

        // Fichier éventuel (si FormData plus tard)
        $file = $_FILES['file'] ?? null;

        if (!empty($data)) {
            newClaimPayment($data, $file);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Aucune donnée reçue pour le paiement.'
            ]);
        }
        break;


    case 'updateClaimPayment':
        // Si FormData (avec fichier)
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            // Si JSON envoyé par Axios
            $data = json_decode(file_get_contents("php://input"), true);
        }

        $file = $_FILES['file'] ?? null;

        if (!$data) {
            echo json_encode([
                'success' => false,
                'error' => 'Aucune donnée reçue'
            ]);
            return;
        }

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
