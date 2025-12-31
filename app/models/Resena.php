<?php
/**
 * Resena Model - Modelo para el manejo de reseñas de productos
 * Maneja las operaciones CRUD para las reseñas
 * 
 * @author Equipo AgroConecta 6CV1
 * @version 1.0
 */

require_once 'Model.php';

class Resena extends Model {
    protected $table = 'resenas';
    
    protected $fillable = [
        'producto_id',
        'usuario_id', 
        'calificacion',
        'comentario',
        'fecha_resena'
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtener reseñas por producto
     * @param int $producto_id ID del producto
     * @return array Lista de reseñas
     */
    public function getByProducto($producto_id) {
        try {
            $sql = "SELECT r.*, u.nombre_completo, u.email 
                    FROM {$this->table} r 
                    INNER JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.producto_id = ? 
                    ORDER BY r.fecha_resena DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getByProducto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener reseñas por usuario
     * @param int $usuario_id ID del usuario
     * @return array Lista de reseñas
     */
    public function getByUsuario($usuario_id) {
        try {
            $sql = "SELECT r.*, p.nombre_producto, p.imagen_url 
                    FROM {$this->table} r 
                    INNER JOIN productos p ON r.producto_id = p.id 
                    WHERE r.usuario_id = ? 
                    ORDER BY r.fecha_resena DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getByUsuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear nueva reseña
     * @param array $data Datos de la reseña
     * @return int|bool ID de la reseña creada o false en caso de error
     */
    public function create($data) {
        try {
            // Verificar que no exista una reseña del mismo usuario para el mismo producto
            if ($this->existeResena($data['producto_id'], $data['usuario_id'])) {
                return false;
            }
            
            $data['fecha_resena'] = date('Y-m-d H:i:s');
            return parent::create($data);
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si ya existe una reseña del usuario para el producto
     * @param int $producto_id ID del producto
     * @param int $usuario_id ID del usuario
     * @return bool
     */
    public function existeResena($producto_id, $usuario_id) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE producto_id = ? AND usuario_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id, $usuario_id]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en existeResena: " . $e->getMessage());
            return true; // Devolvemos true para prevenir duplicados en caso de error
        }
    }
    
    /**
     * Calcular promedio de calificaciones para un producto
     * @param int $producto_id ID del producto
     * @return float Promedio de calificaciones
     */
    public function getPromedioCalificacion($producto_id) {
        try {
            $sql = "SELECT AVG(calificacion) as promedio 
                    FROM {$this->table} 
                    WHERE producto_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? (float)$resultado['promedio'] : 0;
        } catch (PDOException $e) {
            error_log("Error en getPromedioCalificacion: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas de reseñas para un producto
     * @param int $producto_id ID del producto
     * @return array Estadísticas de reseñas
     */
    public function getEstadisticasProducto($producto_id) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_resenas,
                        AVG(calificacion) as promedio,
                        MAX(calificacion) as calificacion_max,
                        MIN(calificacion) as calificacion_min,
                        SUM(CASE WHEN calificacion = 5 THEN 1 ELSE 0 END) as cinco_estrellas,
                        SUM(CASE WHEN calificacion = 4 THEN 1 ELSE 0 END) as cuatro_estrellas,
                        SUM(CASE WHEN calificacion = 3 THEN 1 ELSE 0 END) as tres_estrellas,
                        SUM(CASE WHEN calificacion = 2 THEN 1 ELSE 0 END) as dos_estrellas,
                        SUM(CASE WHEN calificacion = 1 THEN 1 ELSE 0 END) as una_estrella
                    FROM {$this->table} 
                    WHERE producto_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$producto_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en getEstadisticasProducto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar datos de reseña
     * @param array $data Datos a validar
     * @return array Errores de validación
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['producto_id'])) {
            $errors[] = 'El ID del producto es requerido';
        }
        
        if (empty($data['usuario_id'])) {
            $errors[] = 'El ID del usuario es requerido';
        }
        
        if (empty($data['calificacion']) || !in_array($data['calificacion'], [1, 2, 3, 4, 5])) {
            $errors[] = 'La calificación debe ser entre 1 y 5 estrellas';
        }
        
        if (empty($data['comentario']) || strlen(trim($data['comentario'])) < 10) {
            $errors[] = 'El comentario debe tener al menos 10 caracteres';
        }
        
        if (strlen($data['comentario']) > 1000) {
            $errors[] = 'El comentario no puede exceder 1000 caracteres';
        }
        
        return $errors;
    }
}