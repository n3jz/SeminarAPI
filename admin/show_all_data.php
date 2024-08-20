<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pregled podatkov</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f4f4f9;
            color: #333;
        }
        h1 {
            color: #5D1049;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 10px 0;
            padding: 8px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        li a {
            text-decoration: none;
            color: #5D1049;
            font-weight: bold;
        }
        li a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #5D1049;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Seznam tabel</h1>
    <?php
    $servername = "localhost";
    $username = "web_login";
    $password = "-2YFqU.oK8[C_7Sn";
    $dbname = "em";


    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch all tables from the database
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_array()) {
            $table_name = $row[0];
            echo "<li><a href='?table=$table_name'>$table_name</a></li>";
        }
        echo "</ul>";
    } else {
        echo "No tables found in the database.";
    }

    // Check if a table name is passed and show its content
    if (isset($_GET['table'])) {
        $table = $_GET['table'];
        $query = "SELECT * FROM " . $table;
        $data = $conn->query($query);

        if ($data->num_rows > 0) {
            echo "<h2>Contents of $table</h2>";
            echo "<table>";
            $firstRow = true;
            while ($row = $data->fetch_assoc()) {
                if ($firstRow) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        echo "<th>$key</th>";
                    }
                    echo "</tr>";
                    $firstRow = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No data found in $table.";
        }
    }

    $conn->close();
    ?>
</body>
</html>