
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=agroconecta_db;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Verificar si ya existe el usuario
    $checkSql = "SELECT COUNT(*) FROM Usuario WHERE correo = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['test@agroconecta.com']);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo "Usuario de prueba ya existe!\n";
        echo "Email: test@agroconecta.com\n";
        echo "Password: 123456789\n";
        exit;
    }