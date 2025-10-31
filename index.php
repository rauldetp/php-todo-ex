<?php
// Configuration principale
define('BASE_URL', getenv('TODOLIST_BASE_URL') ?: '/php-todo-ex/');
define('DB_USER', getenv('TODOLIST_DB_USER') ?: 'root');
define('DB_PASS', getenv('TODOLIST_DB_PASS') ?: 'root');
define('DB_NAME', getenv('TODOLIST_DB_NAME') ?: 'todolist');
define('DB_HOST', getenv('TODOLIST_DB_HOST') ?: '127.0.0.1'); // important : pas localhost
define('DB_PORT', getenv('TODOLIST_DB_PORT') ?: '8889'); // port MySQL MAMP

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

$items = array();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {

        case 'new':
            $title = $_POST['title'];
            if ($title && $title !== '') {
                $insertQuery = 'INSERT INTO todo VALUES(NULL, :title, FALSE, CURRENT_TIMESTAMP)';
                $stmt = $db->prepare($insertQuery);
                $stmt->execute(['title' => $title]);
            }
            header('Location: ' . BASE_URL);
            exit;

        case 'toggle':
            $id = $_POST['id'];
            if (is_numeric($id)) {
                $updateQuery = 'UPDATE todo SET done = NOT done WHERE id = :id';
                $stmt = $db->prepare($updateQuery);
                $stmt->execute(['id' => $id]);
            }
            header('Location: ' . BASE_URL);
            exit;

        case 'delete':
            $id = $_POST['id'];
            if (is_numeric($id)) {
                $deleteQuery = 'DELETE FROM todo WHERE id = :id';
                $stmt = $db->prepare($deleteQuery);
                $stmt->execute(['id' => $id]);
            }
            header('Location: ' . BASE_URL);
            exit;
    }
}

$selectQuery = 'SELECT id, title, done FROM todo ORDER BY id DESC';
$items = $db->query($selectQuery);
?>

<html>
<head>
    <title>TodoList</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <style>
        button { cursor: pointer; }
        form { margin: 0; }
    </style>
</head>
<body>
<header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="#" class="navbar-brand d-flex align-items-center">
                <strong>TodoList</strong>
            </a>
        </div>
    </div>
</header>

<main role="main" class='offset-3 col-6 mt-3'>
    <form action='<?= BASE_URL ?>' method='post' class='form-inline justify-content-center'>
        <input type='hidden' name='action' value='new' />
        <div class='form-group'>
            <label for='task-title' class='sr-only'>Title</label>
            <input id='task-title' class='form-control' name='title' type='text' placeholder='Task Title' maxlength='1000' required />
        </div>
        <button type='submit' class='btn btn-primary ml-2'>Add</button>
    </form>

    <div class='list-group mt-3'>
        <?php foreach ($items as $item): ?>
            <div class='list-group-item d-flex justify-content-between align-items-center <?= $item['done'] ? "list-group-item-success" : "list-group-item-warning" ?>'>
                <div class='title'><?= htmlspecialchars($item['title']) ?></div>
                <form action='<?= BASE_URL ?>' method='post'>
                    <input type='hidden' name='id' value='<?= $item['id'] ?>' />
                    <div class='btn-group btn-group-sm'>
                        <button type='submit' name='action' value='toggle' class='btn btn-primary'>
                            <?= $item['done'] ? "Undo" : "Done" ?>
                        </button>
                        <button type='submit' name='action' value='delete' class='btn btn-danger'>X</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>