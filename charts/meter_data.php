<?php
session_start();

if (!isset($_SESSION['username'])) {
    // Če ni seje preusmerimo na login stran
    header("Location: /index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRIKAZ PODATKOV MERLINIKOV EL. ENERGIJE</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>    
<body class="bg-light">
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="buildingSelect" class="form-label">Izberi objekt:</label>
                <select id="buildingSelect" class="form-select"></select>
            </div>
            <div class="col-md-8 d-flex align-items-center">
                <div id="meterButtons" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <form id="dateRangeForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="startDate" class="form-label">Prikaži podatke od: </label>
                        <input type="datetime-local" id="startDate" name="startDate" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="endDate" class="form-label">Do: </label>
                        <input type="datetime-local" id="endDate" name="endDate" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Posodobi</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 col-lg-6 mb-4">
                <div id="grafMoci" ></div>
            </div>
            <div class="col-12 col-lg-6 mb-4">
                <div id="grafEnergije" ></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JS/meter_data.js" defer></script> 
</body>
</html>
