<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP MySQLi Database Functions Guide</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #6ee7b7, #3b82f6); }
        .function-card { transition: transform 0.2s; }
        .function-card:hover { transform: scale(1.02); }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">
    <div class="max-w-5xl mx-auto p-6">
        <!-- Header -->
        <header class="gradient-bg text-white p-6 rounded-lg shadow-lg text-center mb-8">
            <h1 class="text-4xl font-bold mb-2">PHP MySQLi Database Functions Guide</h1>
            <p class="text-lg">A Comprehensive Toolkit for MySQLi Operations in PHP</p>
        </header>

        <!-- Introduction -->
        <section class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-info-circle mr-2"></i>Introduction</h2>
            <p class="text-gray-700 leading-relaxed">
                This guide showcases 15 powerful MySQLi database functions in PHP, designed for CRUD operations, table management, optimization, and backups. Interactive and user-friendly, this documentation is perfect for developers seeking efficient database solutions.
            </p>
        </section>

        <!-- Search Bar -->
        <div class="mb-6">
            <input type="text" id="search-bar" placeholder="Search functions..." class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Function List -->
        <section class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-list mr-2"></i>Function List (15 Total)</h2>
            <ul id="function-list" class="grid grid-cols-1 md:grid-cols-2 gap-4"></ul>
        </section>

        <!-- Function Details -->
        <section id="function-details" class="hidden bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-indigo-600 mb-4"><i class="fas fa-code mr-2"></i>Function Details</h2>
            <div class="border-l-4 border-indigo-500 pl-4">
                <h3 class="text-xl font-bold text-gray-800 mb-2" id="function-title"></h3>
                <p class="text-gray-700 mb-4 whitespace-pre-wrap" id="function-description"></p>
                <div class="relative">
                    <pre class="text-sm bg-gray-100 p-4 rounded-md overflow-auto" id="function-code"></pre>
                    <button onclick="copyCode()" class="absolute top-2 right-2 bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600"><i class="fas fa-copy mr-1"></i>Copy</button>
                </div>
            </div>
        </section>
    </div>

    <script>
        const functions = {
            db_insert: {
                title: "db_insert($conn, $table, $data)",
                description: `**Purpose**: Inserts a new record into a table.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$data\` (array): Associative array of column-value pairs.\n
**Returns**: Boolean (true on success).\n
**Throws**: Exception on query failure.\n
**Use Case**: Adding new users or logs.\n
**Best Practice**: Validate data before insertion.`,
                code: `$data = ["name" => "John", "email" => "john@example.com"];\nif (db_insert($conn, "users", $data)) {\n    echo "Record added!";\n}`
            },
            db_update: {
                title: "db_update($conn, $table, $data, $condition)",
                description: `**Purpose**: Updates existing records based on a condition.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$data\` (array): Column-value pairs to update.\n- \`$condition\` (string): SQL WHERE clause.\n
**Returns**: Boolean (true on success).\n
**Throws**: Exception on query failure.\n
**Use Case**: Updating user profiles.\n
**Best Practice**: Use specific conditions to avoid unintended updates.`,
                code: `$data = ["status" => "active"];\ndb_update($conn, "users", $data, "id=1");\necho "User updated!";`
            },
            db_delete: {
                title: "db_delete($conn, $table, $condition)",
                description: `**Purpose**: Deletes records matching a condition.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$condition\` (string): SQL WHERE clause.\n
**Returns**: Boolean (true on success).\n
**Throws**: Exception on query failure.\n
**Use Case**: Removing outdated records.\n
**Best Practice**: Confirm deletion to prevent accidental data loss.`,
                code: `db_delete($conn, "users", "id=5");\necho "User deleted!";`
            },
            db_select: {
                title: "db_select($conn, $table, $columns = '*', $condition = '1')",
                description: `**Purpose**: Retrieves records from a table.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$columns\` (string, optional): Columns to select (default: "*").\n- \`$condition\` (string, optional): SQL WHERE clause.\n
**Returns**: Result set (mysqli_result).\n
**Throws**: Exception on query failure.\n
**Use Case**: Fetching user lists.\n
**Best Practice**: Specify columns for performance.`,
                code: `$result = db_select($conn, "users", "name, email", "status='active'");\nwhile ($row = mysqli_fetch_assoc($result)) {\n    echo $row['name'] . "<br>";\n}`
            },
            db_fetch_all: {
                title: "db_fetch_all($conn, $sql)",
                description: `**Purpose**: Fetches all rows from a custom query as an array.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$sql\` (string): SQL query.\n
**Returns**: Array of rows (associative array[]).\n
**Throws**: Exception on query failure.\n
**Use Case**: Bulk data retrieval.\n
**Best Practice**: Use with specific queries to limit memory usage.`,
                code: `$rows = db_fetch_all($conn, "SELECT * FROM users");\nforeach ($rows as $row) {\n    echo $row['name'] . "<br>";\n}`
            },
            db_get_tables: {
                title: "db_get_tables($conn)",
                description: `**Purpose**: Retrieves all table names from the database.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n
**Returns**: Array of table names (string[]).\n
**Throws**: Exception on query failure.\n
**Use Case**: Schema exploration.\n
**Best Practice**: Check array length before iteration.`,
                code: `$tables = db_get_tables($conn);\nforeach ($tables as $table) {\n    echo "Table: " . $table . "<br>";\n}`
            },
            db_get_columns: {
                title: "db_get_columns($conn, $table)",
                description: `**Purpose**: Fetches column metadata for a table.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n
**Returns**: Array of column details.\n
**Throws**: Exception if table doesn’t exist.\n
**Use Case**: Dynamic form generation.\n
**Best Practice**: Validate table name.`,
                code: `$columns = db_get_columns($conn, "users");\nforeach ($columns as $col) {\n    echo $col['Field'] . " (" . $col['Type'] . ")<br>";\n}`
            },
            db_count: {
                title: "db_count($conn, $table, $condition = '1')",
                description: `**Purpose**: Counts records in a table with an optional condition.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$condition\` (string, optional): SQL WHERE clause.\n
**Returns**: Integer count.\n
**Throws**: Exception on query failure.\n
**Use Case**: Pagination.\n
**Best Practice**: Use specific conditions.`,
                code: `$count = db_count($conn, "users", "status='active'");\necho "Active Users: " . $count;`
            },
            db_truncate: {
                title: "db_truncate($conn, $table)",
                description: `**Purpose**: Deletes all data from a table, keeping structure.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n
**Returns**: Boolean (true on success).\n
**Throws**: Exception on failure.\n
**Use Case**: Resetting test data.\n
**Best Practice**: Confirm before execution.`,
                code: `db_truncate($conn, "logs");\necho "Logs cleared!";`
            },
            db_optimize: {
                title: "db_optimize($conn)",
                description: `**Purpose**: Optimizes the "users" table (hardcoded).\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n
**Returns**: Boolean (true on success).\n
**Throws**: Exception on failure.\n
**Use Case**: Performance tuning.\n
**Best Practice**: Extend with table parameter.`,
                code: `db_optimize($conn);\necho "Table optimized!";`
            },
            db_backup: {
                title: "db_backup($conn, $database)",
                description: `**Purpose**: Creates an SQL dump of the database.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$database\` (string): Database name.\n
**Returns**: String (backup file path).\n
**Throws**: Exception if mysqldump fails.\n
**Use Case**: Backups.\n
**Best Practice**: Automate backups.`,
                code: `$file = db_backup($conn, "mydb");\necho "Backup at: " . $file;`
            },
            db_fetch_first: {
                title: "db_fetch_first($conn, $sql)",
                description: `**Purpose**: Fetches the first row of a query.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$sql\` (string): SQL query.\n
**Returns**: Associative array or null.\n
**Throws**: Exception on query failure.\n
**Use Case**: Single record lookup.\n
**Best Practice**: Validate query.`,
                code: `$row = db_fetch_first($conn, "SELECT * FROM users WHERE id=1");\necho $row['name'];`
            },
            db_custom_query: {
                title: "db_custom_query($conn, $sql)",
                description: `**Purpose**: Executes a custom query, returns all results.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$sql\` (string): SQL query.\n
**Returns**: Array of rows.\n
**Throws**: Exception on query failure.\n
**Use Case**: Complex reports.\n
**Best Practice**: Prefer specific functions when possible.`,
                code: `$results = db_custom_query($conn, "SELECT * FROM users");\nforeach ($results as $row) {\n    echo $row['name'] . "<br>";\n}`
            },
            db_exists: {
                title: "db_exists($conn, $table, $condition)",
                description: `**Purpose**: Checks if a record exists.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n- \`$table\` (string): Target table.\n- \`$condition\` (string): SQL WHERE clause.\n
**Returns**: Boolean.\n
**Throws**: Exception on query failure.\n
**Use Case**: Uniqueness checks.\n
**Best Practice**: Optimize conditions.`,
                code: `if (db_exists($conn, "users", "email='test@example.com'")) {\n    echo "Email taken!";\n}`
            },
            db_close: {
                title: "db_close($conn)",
                description: `**Purpose**: Closes the database connection.\n
**Parameters**:\n- \`$conn\` (mysqli): Database connection.\n
**Returns**: Void.\n
**Use Case**: Resource cleanup.\n
**Best Practice**: Use in long scripts.`,
                code: `db_close($conn);\necho "Connection closed.";`
            }
        };

        const functionListElement = document.getElementById("function-list");
        const searchBar = document.getElementById("search-bar");

        function renderFunctions(filter = "") {
            functionListElement.innerHTML = "";
            Object.keys(functions).forEach(func => {
                if (functions[func].title.toLowerCase().includes(filter.toLowerCase())) {
                    const li = document.createElement("li");
                    li.classList.add("function-card", "bg-indigo-50", "p-4", "rounded-md", "cursor-pointer", "hover:bg-indigo-100");
                    li.innerHTML = `<span class="font-semibold text-indigo-600">${functions[func].title}</span>`;
                    li.onclick = () => showFunction(func);
                    functionListElement.appendChild(li);
                }
            });
        }

        function showFunction(func) {
            const details = document.getElementById("function-details");
            details.classList.remove("hidden");
            document.getElementById("function-title").innerText = functions[func].title;
            document.getElementById("function-description").innerText = functions[func].description;
            document.getElementById("function-code").innerText = functions[func].code;
            window.scrollTo({ top: details.offsetTop - 20, behavior: "smooth" });
        }

        function copyCode() {
            const code = document.getElementById("function-code").innerText;
            navigator.clipboard.writeText(code).then(() => {
                alert("Code copied to clipboard!");
            });
        }

        searchBar.addEventListener("input", (e) => renderFunctions(e.target.value));
        renderFunctions(); // Initial render
    </script>
</body>
</html>