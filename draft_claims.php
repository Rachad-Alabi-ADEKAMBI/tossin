case 'newClaim':
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        addNewClaim($data);
    } else {
        echo json_encode(['error' => 'Aucune donnée reçue']);
    }
    break;
