<?php
header('Content-Type: application/json');
require_once "../includes/db.php";

try {
    $stmt = $pdo->query("SELECT ProgrammeID, ProgrammeName, Description FROM Programmes ORDER BY ProgrammeName");
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($programmes);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
