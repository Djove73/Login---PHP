<!DOCTYPE html>
<html lang="en">
<head>
    <h1>Formulari</h1>
    <style>
        body {
            background-color: lightblue;
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .box {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px; 
            text-align: center; 
            border: 5px solid black; 
        }
        input[type="submit"] {
            width: 100%; 
            padding: 10px; 
            border: none; 
            background-color: #4CAF50; 
            color: white; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        input[type="submit"]:hover {
            background-color: #45a049; 
        }
    </style>

</head>
<body bgcolor="lightblue">

<div class="box">
  
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <p>Nom: <input type="text" name="nom" required></p>
    <p>Apellido: <input type="text" name="cognom" required></p>
    <p>Email: <input type="email" name="email" required></p>
    <p>Contrasenya: <input type="password" name="contrasenya" required></p>
    <p>Validador: <input type="password" name="contrasenya_confirm" required></p>
    <p>Direccion: <input type="text" name="direccion"></p>
    <p>Numero tarjeta: <input type="text" name="tarjeta"></p>
    <p>Fecha de caducidad: <input type="date" name="fecha"></p>
    <p>Codigo seguridad: <input type="text" name="codigo"></p>
    
    <input type="submit" value="Enviar">
</form>
</div>

<?php

$Nom = $Apellido = $Email = $Contrasenya = $Validador = $Direccion = $Tarjeta = $Fecha = $Codigo = "";
$errors = [];

$servername = "db";
$username = "myuser";
$password = "mypassword";
$dbname = "mydatabase";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  echo "Connected successfully";

  $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
  if ($conn->query($sql) !== TRUE) {
      die("Error creando base de datos: " . $conn->error);
  }

  $conn->select_db($dbname);

  $sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    cognom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    direccion VARCHAR(255),
    tarjeta VARCHAR(16),
    fecha DATE,
    codigo VARCHAR(3),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Error creando tabla: " . $conn->error);
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $Nom = test_input($_POST["nom"]);
  $Apellido = test_input($_POST["cognom"]);
  $Email = test_input($_POST["email"]);
  $Contrasenya = test_input($_POST["contrasenya"]);
  $Validador = test_input($_POST["contrasenya_confirm"]);
  $Direccion = test_input($_POST["direccion"]);
  $Tarjeta = test_input($_POST["tarjeta"]);
  $Fecha = test_input($_POST["fecha"]);
  $Codigo = test_input($_POST["codigo"]);

  if (preg_match('/\d/', $Nom) || preg_match('/\d/', $Apellido)) {
      $errors[] = "El nom i el cognom no poden contenir numeros.";
  }

  if (empty($Email) || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "El correu és obligatori i ha de ser vàlid.";
  }

  if ($Validador != $Contrasenya) {
      $errors[] = "El validador ha de ser igual que la contrasenya.";
  }

  if (strlen($Tarjeta) != 16) {
      $errors[] = "El número de la tarjeta ha de contenir 16 digits.";
  } 

  if (!empty($Tarjeta) && empty($Fecha)) {
      $errors[] = "Es obligatori omplir la fecha si hi ha un numero de tarjeta.";
  }

  if (!empty($Fecha) && strtotime($Fecha) <= time()) {
      $errors[] = "La fecha ha de ser futura.";
  } 

  if (!empty($Tarjeta) && empty($Codigo)) {
      $errors[] = "Es obligatori omplir el codi si hi ha un numero de tarjeta.";
  }

  if (strlen($Codigo) != 3) {
      $errors[] = "Es obligatori que el codi contingui 3 digits.";
  }

  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p style='color:red;'>$error</p>";
      }
  } else {
    echo "Enviat";
    $hashedPassword = password_hash($Contrasenya, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nom, cognom, email, contrasenya, direccion, tarjeta, fecha, codigo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("ssssssss", $Nom, $Apellido, $Email, $hashedPassword, $Direccion, $Tarjeta, $Fecha, $Codigo);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id; 
        echo "Registro exitoso.";
    } else {
        echo "Error al guardar en la base de datos: " . $stmt->error;
    }
  }
} 

?>
</body>
</html>
