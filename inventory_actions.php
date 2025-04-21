<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventory";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action == "add" || $action == "update") {
        $productName = $_POST["productName"];
        $quantity = $_POST["quantity"];
        $unit = $_POST["unit"];
        $expirationDate = $_POST["expirationDate"];

        if ($action == "add") {
            $sql = "INSERT INTO inventory (product_name, quantity, unit, expiration_date) VALUES (?, ?, ?, ?)";
        } else { // update
            $id = $_POST["id"];
            $sql = "UPDATE inventory SET product_name = ?, quantity = ?, unit = ?, expiration_date = ? WHERE id = ?";
        }

        $stmt = $conn->prepare($sql);

        if ($action == "add") {
            $stmt->bind_param("siss", $productName, $quantity, $unit, $expirationDate);
        } else { // update
            $stmt->bind_param("sissi", $productName, $quantity, $unit, $expirationDate, $id);
        }

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action == "delete") {
        $id = $_POST["id"];
        $sql = "DELETE FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["action"]) && $_GET["action"] == "get") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM inventory WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        echo json_encode($products);
        $stmt->close();
    } else {
        $sql = "SELECT * FROM inventory ORDER BY product_name ASC";
        $result = $conn->query($sql);

        $products = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        echo json_encode($products);
    }
}

$conn->close();
?>