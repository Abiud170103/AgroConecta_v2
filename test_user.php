<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=agroconecta_db;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE correo = ?");
    $checkStmt->execute(['test@agroconecta.com']);
    
    if ($checkStmt->fetchColumn() > 0) {
        echo "Usuario ya existe - Email: test@agroconecta.com, Password: 123456789\n";
        exit;
    }
    
    $hashedPassword = password_hash('123456789', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO Usuario (nombre, apellido, correo, contraseña, tipo_usuario, activo, verificado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute(['Test', 'User', 'test@agroconecta.com', $hashedPassword, 'cliente', 1, 1]);
    
    if ($result) {
        echo "Usuario creado - Email: test@agroconecta.com, Password: 123456789\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>