<?php
include "header.php";

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// // Define the 15 functions
// function db_insert($conn, $table, $data) {
//     $columns = implode("`, `", array_keys($data));
//     $values = implode("', '", array_map(function($v) use ($conn) { return mysqli_real_escape_string($conn, $v); }, $data));
//     $sql = "INSERT INTO `$table` (`$columns`) VALUES ('$values')";
//     if (!mysqli_query($conn, $sql)) {
//         throw new Exception("Insert failed: " . mysqli_error($conn));
//     }
//     return true;
// }

// function db_update($conn, $table, $data, $condition) {
//     $set = implode(", ", array_map(function($k, $v) use ($conn) { return "`$k`='" . mysqli_real_escape_string($conn, $v) . "'"; }, array_keys($data), $data));
//     $sql = "UPDATE `$table` SET $set WHERE $condition";
//     if (!mysqli_query($conn, $sql)) {
//         throw new Exception("Update failed: " . mysqli_error($conn));
//     }
//     return true;
// }

// function db_delete($conn, $table, $condition) {
//     $sql = "DELETE FROM `$table` WHERE $condition";
//     if (!mysqli_query($conn, $sql)) {
//         throw new Exception("Delete failed: " . mysqli_error($conn));
//     }
//     return true;
// }

// function db_select($conn, $table, $columns = "*", $condition = "1") {
//     $sql = "SELECT $columns FROM `$table` WHERE $condition";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         throw new Exception("Select failed: " . mysqli_error($conn));
//     }
//     return $result;
// }

// function db_fetch_all($conn, $sql) {
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         throw new Exception("Fetch all failed: " . mysqli_error($conn));
//     }
//     $rows = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $rows[] = $row;
//     }
//     mysqli_free_result($result);
//     return $rows;
// }

// function db_get_tables($conn) {
//     $tables = [];
//     $result = mysqli_query($conn, "SHOW TABLES");
//     if (!$result) {
//         throw new Exception("Get tables failed: " . mysqli_error($conn));
//     }
//     while ($row = mysqli_fetch_array($result)) {
//         $tables[] = $row[0];
//     }
//     mysqli_free_result($result);
//     return $tables;
// }

// function db_get_columns($conn, $table) {
//     $columns = [];
//     $table = mysqli_real_escape_string($conn, $table);
//     $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
//     if (!$result) {
//         throw new Exception("Get columns failed: " . mysqli_error($conn));
//     }
//     while ($row = mysqli_fetch_assoc($result)) {
//         $columns[] = $row;
//     }
//     mysqli_free_result($result);
//     return $columns;
// }

// function db_count($conn, $table, $condition = "1") {
//     $table = mysqli_real_escape_string($conn, $table);
//     $sql = "SELECT COUNT(*) AS total FROM `$table` WHERE $condition";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         throw new Exception("Count failed: " . mysqli_error($conn));
//     }
//     $data = mysqli_fetch_assoc($result);
//     mysqli_free_result($result);
//     return (int)$data['total'];
// }

// function db_truncate($conn, $table) {
//     $table = mysqli_real_escape_string($conn, $table);
//     $result = mysqli_query($conn, "TRUNCATE TABLE `$table`");
//     if (!mysqli_query($conn, $sql)) {
//         throw new Exception("Truncate failed: " . mysqli_error($conn));
//     }
//     return true;
// }

// function db_optimize($conn) {
//     $result = mysqli_query($conn, "OPTIMIZE TABLE `users`");
//     if (!$result) {
//         throw new Exception("Optimize failed: " . mysqli_error($conn));
//     }
//     mysqli_free_result($result);
//     return true;
// }

// function db_backup($conn, $database) {
//     $database = mysqli_real_escape_string($conn, $database);
//     $backupFile = "backup_" . $database . "_" . date("Y-m-d") . ".sql";
//     $cmd = "mysqldump --user=root --password= --host=localhost " . escapeshellarg($database) . " > " . escapeshellarg($backupFile);
//     system($cmd, $returnVar);
//     if ($returnVar !== 0) {
//         throw new Exception("Backup failed: Check mysqldump availability or permissions.");
//     }
//     return $backupFile;
// }

// function db_fetch_first($conn, $sql) {
//     $sql .= " LIMIT 1";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         throw new Exception("Fetch first failed: " . mysqli_error($conn));
//     }
//     $row = mysqli_fetch_assoc($result);
//     mysqli_free_result($result);
//     return $row ?: null;
// }

// function db_custom_query($conn, $sql) {
//     return db_fetch_all($conn, $sql);
// }

// function db_exists($conn, $table, $condition) {
//     $table = mysqli_real_escape_string($conn, $table);
//     $sql = "SELECT COUNT(*) AS count FROM `$table` WHERE $condition";
//     $result = mysqli_query($conn, $sql);
//     if (!$result) {
//         throw new Exception("Exists check failed: " . mysqli_error($conn));
//     }
//     $data = mysqli_fetch_assoc($result);
//     mysqli_free_result($result);
//     return ($data['count'] > 0);
// }

// function db_close($conn) {
//     mysqli_close($conn);
// }

// Handle form submission
$result = null;
$error = null;
$functionCall = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $function = $_POST["function"] ?? "";
    $table = $_POST["table"] ?? "";
    $condition = $_POST["condition"] ?? "1";
    $data = $_POST["data"] ?? "";
    $sql = $_POST["sql"] ?? "";
    $database = $_POST["database"] ?? "test_db";

    try {
        switch ($function) {
            case "db_insert":
                parse_str($data, $dataArray);
                $functionCall = "db_insert(\$conn, \"$table\", " . json_encode($dataArray) . ")";
                $result = db_insert($conn, $table, $dataArray);
                $result = ["Success" => $result, "Inserted Data" => $dataArray];
                break;
            case "db_update":
                parse_str($data, $dataArray);
                $functionCall = "db_update(\$conn, \"$table\", " . json_encode($dataArray) . ", \"$condition\")";
                $result = db_update($conn, $table, $dataArray, $condition);
                $result = ["Success" => $result, "Updated Data" => $dataArray, "Condition" => $condition];
                break;
            case "db_delete":
                $functionCall = "db_delete(\$conn, \"$table\", \"$condition\")";
                $result = db_delete($conn, $table, $condition);
                $result = ["Success" => $result, "Condition" => $condition];
                break;
            case "db_select":
                $functionCall = "db_select(\$conn, \"$table\", \"*\", \"$condition\")";
                $result = db_select($conn, $table, "*", $condition);
                $rows = [];
                while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
                $result = ["Rows" => $rows, "Condition" => $condition];
                break;
            case "db_fetch_all":
                $functionCall = "db_fetch_all(\$conn, \"$sql\")";
                $result = db_fetch_all($conn, $sql);
                $result = ["Rows" => $result, "SQL" => $sql];
                break;
            case "db_get_tables":
                $functionCall = "db_get_tables(\$conn)";
                $result = db_get_tables($conn);
                $result = ["Tables" => $result];
                break;
            case "db_get_columns":
                $functionCall = "db_get_columns(\$conn, \"$table\")";
                $result = db_get_columns($conn, $table);
                $result = ["Columns" => $result];
                break;
            case "db_count":
                $functionCall = "db_count(\$conn, \"$table\", \"$condition\")";
                $result = db_count($conn, $table, $condition);
                $result = ["Count" => $result, "Condition" => $condition];
                break;
            case "db_truncate":
                $functionCall = "db_truncate(\$conn, \"$table\")";
                $result = db_truncate($conn, $table);
                $result = ["Success" => $result];
                break;
            case "db_optimize":
                $functionCall = "db_optimize(\$conn)";
                $result = db_optimize($conn);
                $result = ["Success" => $result];
                break;
            case "db_backup":
                $functionCall = "db_backup(\$conn, \"$database\")";
                $result = db_backup($conn, $database);
                $result = ["Backup File" => $result];
                break;
            case "db_fetch_first":
                $functionCall = "db_fetch_first(\$conn, \"$sql\")";
                $result = db_fetch_first($conn, $sql);
                $result = ["Row" => $result, "SQL" => $sql];
                break;
            case "db_custom_query":
                $functionCall = "db_custom_query(\$conn, \"$sql\")";
                $result = db_custom_query($conn, $sql);
                $result = ["Rows" => $result, "SQL" => $sql];
                break;
            case "db_exists":
                $functionCall = "db_exists(\$conn, \"$table\", \"$condition\")";
                $result = db_exists($conn, $table, $condition);
                $result = ["Exists" => $result, "Condition" => $condition];
                break;
            case "db_close":
                $functionCall = "db_close(\$conn)";
                db_close($conn);
                $result = ["Message" => "Connection closed"];
                break;
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<?php if($userdata["role"] == 'Admin'){  ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PHP MySQLi Functions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #6ee7b7, #3b82f6); }
        .input-field { display: none; }
        .input-field.active { display: block; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header -->
        <header class="gradient-bg text-white p-6 rounded-lg shadow-lg text-center mb-8">
            <h1 class="text-3xl font-bold">Test PHP MySQLi Database Functions</h1>
            <p class="mt-2">Select a function to see only its required inputs!</p>
        </header>

        <!-- Form -->
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md mb-8" id="test-form">
            <div class="mb-4">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-cogs mr-2"></i>Select Function</label>
                <select name="function" id="function-select" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">-- Choose a Function --</option>
                    <?php foreach (["db_insert", "db_update", "db_delete", "db_select", "db_fetch_all", "db_get_tables", "db_get_columns", "db_count", "db_truncate", "db_optimize", "db_backup", "db_fetch_first", "db_custom_query", "db_exists", "db_close"] as $func) : ?>
                        <option value="<?php echo $func; ?>" <?php echo ($function ?? '') === $func ? 'selected' : ''; ?>><?php echo $func; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-field" id="table-field">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-table mr-2"></i>Table Name</label>
                <input type="text" name="table" value="<?php echo htmlspecialchars($table ?? ''); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., users">
            </div>
            <div class="input-field" id="condition-field">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-filter mr-2"></i>Condition</label>
                <input type="text" name="condition" value="<?php echo htmlspecialchars($condition ?? ''); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., id=1">
            </div>
            <div class="input-field" id="data-field">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-database mr-2"></i>Data (format: key1=value1&key2=value2)</label>
                <input type="text" name="data" value="<?php echo htmlspecialchars($data ?? ''); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., name=John&email=john@example.com">
            </div>
            <div class="input-field" id="sql-field">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-code mr-2"></i>SQL Query</label>
                <input type="text" name="sql" value="<?php echo htmlspecialchars($sql ?? ''); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="e.g., SELECT * FROM users">
            </div>
            <div class="input-field" id="database-field">
                <label class="block text-indigo-600 font-semibold mb-2"><i class="fas fa-server mr-2"></i>Database Name</label>
                <input type="text" name="database" value="<?php echo htmlspecialchars($database ?? 'test_db'); ?>" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600"><i class="fas fa-play mr-2"></i>Test Function</button>
        </form>

        <!-- Result Section with Function Call -->
        <?php if ($result || $error) : ?>
            <section class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-file-alt mr-2"></i>Result</h2>
                <?php if ($functionCall) : ?>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-terminal mr-2"></i>Function Call</h3>
                        <pre class="bg-gray-100 p-4 rounded-md overflow-auto text-sm"><?php echo htmlspecialchars($functionCall); ?></pre>
                    </div>
                <?php endif; ?>
                <?php if ($error) : ?>
                    <p class="text-red-600"><?php echo htmlspecialchars($error); ?></p>
                <?php else : ?>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800"><i class="fas fa-check-circle mr-2"></i>Output</h3>
                        <pre class="bg-gray-100 p-4 rounded-md overflow-auto"><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></pre>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>

    <script>
        const functionInputs = {
            "db_insert": ["table", "data"],
            "db_update": ["table", "data", "condition"],
            "db_delete": ["table", "condition"],
            "db_select": ["table", "condition"],
            "db_fetch_all": ["sql"],
            "db_get_tables": [],
            "db_get_columns": ["table"],
            "db_count": ["table", "condition"],
            "db_truncate": ["table"],
            "db_optimize": [],
            "db_backup": ["database"],
            "db_fetch_first": ["sql"],
            "db_custom_query": ["sql"],
            "db_exists": ["table", "condition"],
            "db_close": []
        };

        const functionSelect = document.getElementById("function-select");
        const inputFields = {
            "table": document.getElementById("table-field"),
            "condition": document.getElementById("condition-field"),
            "data": document.getElementById("data-field"),
            "sql": document.getElementById("sql-field"),
            "database": document.getElementById("database-field")
        };

        function updateInputs() {
            const selectedFunction = functionSelect.value;
            const requiredInputs = functionInputs[selectedFunction] || [];

            Object.values(inputFields).forEach(field => field.classList.remove("active"));
            requiredInputs.forEach(input => {
                if (inputFields[input]) {
                    inputFields[input].classList.add("active");
                }
            });
        }

        functionSelect.addEventListener("change", updateInputs);
        window.addEventListener("load", updateInputs);
    </script>
</body>
</html>

<?PHP }?>

<?php
// Close connection unless db_close was explicitly called
if (!isset($_POST["function"]) || $_POST["function"] !== "db_close") {
    mysqli_close($conn);
}
?>