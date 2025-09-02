<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root"; // Usuario por defecto de XAMPP
$password = ""; // Contraseña por defecto de XAMPP (vacía)
$dbname = "MAGNATESTING_DB";

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar que se recibieron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Método no permitido.");
}

// Recibir y limpiar los datos del formulario
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validar que los campos no estén vacíos
if (empty($fullname) || empty($email) || empty($password)) {
    die("Todos los campos son obligatorios.");
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("El formato del email no es válido.");
}

// Verificar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("El email ya está registrado.");
}

$stmt->close();

// Hash de la contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar el nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, email, password) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmt->bind_param("sss", $fullname, $email, $hashed_password);

if ($stmt->execute()) {
    echo "Usuario registrado con éxito.";
} else {
    echo "Error al registrar el usuario: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>