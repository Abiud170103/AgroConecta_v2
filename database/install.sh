#!/bin/bash
# =====================================================
# AgroConecta - Script de Instalación de Base de Datos
# Sistema de apoyo a agricultores locales
# Equipo: 6CV1 - ESCOM IPN
# =====================================================

echo "🌱 Instalando Base de Datos de AgroConecta..."

# Configuración (modificar según tu entorno)
DB_HOST="localhost"
DB_USER="root"
DB_PASS=""
DB_NAME="agroconecta_db"

# Verificar si MySQL está disponible
echo "📡 Verificando conexión a MySQL..."
if ! mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -e "SELECT 1" > /dev/null 2>&1; then
    echo "❌ Error: No se puede conectar a MySQL"
    echo "   Verifica que MySQL esté ejecutándose y las credenciales sean correctas"
    exit 1
fi

echo "✅ Conexión a MySQL exitosa"

# Ejecutar schema
echo "🗄️  Creando esquema de base de datos..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS < schema.sql

if [ $? -eq 0 ]; then
    echo "✅ Esquema creado exitosamente"
else
    echo "❌ Error al crear el esquema"
    exit 1
fi

# Ejecutar seeders
echo "🌱 Insertando datos de prueba..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS < seeders.sql

if [ $? -eq 0 ]; then
    echo "✅ Datos de prueba insertados exitosamente"
else
    echo "❌ Error al insertar datos de prueba"
    exit 1
fi

echo ""
echo "🎉 ¡Instalación completada!"
echo ""
echo "📋 Información de la base de datos:"
echo "   • Base de datos: $DB_NAME"
echo "   • Tablas creadas: 9"
echo "   • Usuarios de prueba: 11"
echo "   • Productos de prueba: 16"
echo "   • Pedidos de prueba: 3"
echo ""
echo "🔐 Cuentas de prueba:"
echo "   • Admin: admin@agroconecta.com / password123"
echo "   • Vendedor: juan.mendoza@gmail.com / password123"
echo "   • Cliente: carlos.lopez@cliente.com / password123"
echo ""
echo "🚀 ¡Ya puedes comenzar a desarrollar AgroConecta!"