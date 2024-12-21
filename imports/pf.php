<?php
require_once(__DIR__ . '/../crest/crest.php');
require_once(__DIR__ . '/../crest/settings.php');
require_once(__DIR__ . '/../utils/index.php');

if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['csvFile']['tmp_name'];
    $separator = $_POST['separator'];

    $fileContent = file($fileTmpPath);
    if ($fileContent === false) {
        echo "Error reading the file.";
        exit;
    }

    $header = [];
    $dataRows = [];
    $isHeader = true;

    foreach ($fileContent as $line) {
        $data = str_getcsv($line, $separator);

        if ($isHeader) {
            $header = $data;
            $isHeader = false;
        } else {
            $dataRows[] = $data;
        }
    }

    foreach ($dataRows as $row) {
        $fullLocation = trim(implode(' - ', array_filter($row, fn($value) => $value !== '-')));
        $city = ($row[0] ?? '') === '-' ? '' : $row[0];
        $community = ($row[1] ?? '') === '-' ? '' : $row[1];
        $sub_community = ($row[2] ?? '') === '-' ? '' : $row[2];
        $building = ($row[3] ?? '') === '-' ? '' : $row[3];

        $fields = [
            'ufCrm26City' => $city,
            'ufCrm26Community' => $community,
            'ufCrm26SubCommunity' => $sub_community,
            'ufCrm26Building' => $building,
            'ufCrm26Location' => $fullLocation,
        ];


        if (!isDuplicatePfLocation('city', $city)) {
            $response = CRest::call('crm.item.add', [
                'entityTypeId' => CITIES_ENTITY_TYPE_ID,
                'fields' => [
                    'ufCrm34City' => $city,
                ]
            ]);
        }

        if (!isDuplicatePfLocation('community', $community)) {
            $response = CRest::call('crm.item.add', [
                'entityTypeId' => COMMUNITIES_ENTITY_TYPE_ID,
                'fields' => [
                    'ufCrm36Community' => $community,
                ]
            ]);
        }

        if (!isDuplicatePfLocation('sub_community', $sub_community)) {
            $response = CRest::call('crm.item.add', [
                'entityTypeId' => SUB_COMMUNITIES_ENTITY_TYPE_ID,
                'fields' => [
                    'ufCrm38SubCommunity' => $sub_community,
                ]
            ]);
        }

        if (!isDuplicatePfLocation('building', $building)) {
            $response = CRest::call('crm.item.add', [
                'entityTypeId' => BUILDINGS_ENTITY_TYPE_ID,
                'fields' => [
                    'ufCrm40Building' => $building,
                ]
            ]);
        }

        if (!isDuplicatePfLocation('location', $fullLocation)) {
            $response = CRest::call('crm.item.add', [
                'entityTypeId' => PF_LOCATIONS_ENTITY_TYPE_ID,
                'fields' => $fields
            ]);
        }
    }

    header('Location: ../index.php?page=pf-locations');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import for Bitrix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.3/tailwind.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="/styles/app.css">
    <style>
        /* Loading spinner styles */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3498db;
            animation: spin 1s ease infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <?php if (isset($_GET['error'])): ?>
        <div id="message" class="bg-red-500 text-white p-3 rounded mb-4 absolute top-2 right-2">
            Data import failed, please try again
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div id="message" class="bg-green-500 text-white p-3 rounded mb-4 absolute top-2 right-2">
            Data imported successfully
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md relative">
        <!-- back button -->
        <div class="d-flex justify-content-end">
            <a href="index.php" class="btn btn-outline-primary btn-sm mb-3">
                <i class="fa-solid fa-times me-2"></i> Close
            </a>
        </div>
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Import Locations</h2>

        <!-- Loading Spinner -->
        <div id="loading" class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="spinner"></div>
            <h4>Importing...</h4>
        </div>

        <!-- Form -->
        <form id="csvForm" action="./pf.php" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="csvFile" class="block text-gray-600 font-medium mb-2">Choose CSV File</label>
                <input type="file" name="csvFile" id="csvFile" accept=".csv" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            </div>
            <div class="mb-6">
                <label for="separator" class="block text-gray-600 font-medium mb-2">Select Separator</label>
                <select name="separator" id="separator" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    <option value=",">Comma (,)</option>
                    <option value="-">Hiphen (-)</option>
                    <option value=";">Semicolon (;)</option>
                    <option value="|">Pipe (|)</option>
                </select>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Upload and Process</button>
        </form>
    </div>

    <script>
        // JavaScript to show loading animation on form submit
        document.getElementById('csvForm').addEventListener('submit', function() {
            document.getElementById('loading').classList.remove('hidden');
        });

        // Automatically hide success or error message after a few seconds
        const messageDiv = document.getElementById('message');
        if (messageDiv) {
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000); // Hide after 3 seconds
        }
    </script>
</body>

</html>