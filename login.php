<?php
session_start(); 

$servername = "localhost";
$dbusername = "web_login"; 
$dbpassword = "-2YFqU.oK8[C_7Sn"; 
$dbname = "em";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Ni povezave z podatkovno bazo: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape vhodnih podatkov da preprečimo SQL injection
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // PW je hashiran v bazi, zato ga ne rabimo escapat

    // najdi uporabnika v bazi
    $sql = "SELECT username, password FROM users WHERE username = ?";
    
    // Pripravimo SQL statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // Fetch associated array from result
            $user = $result->fetch_assoc();
            
            // Preveri če je geslo pravilno
            if (password_verify($password, $user['password'])) {
                // Geslo je pravilno, ustvari sejo
                $_SESSION['username'] = $username;
                header("Location: /charts/meter_data.php");  // Preusmeri na drugo stran
                exit();
            } else {
                echo "<p>Geslo ni pravilno.</p>";
            }
        } else {
            echo "<p>Uporabnik ne obstaja.</p>";
        }
        
        $stmt->close();
    } else {
        echo "Napaka v pripravi SQL statement-a " . $conn->error;
    }
}

$conn->close();
?>