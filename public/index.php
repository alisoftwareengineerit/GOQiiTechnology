<?php
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/AuthController.php';
require_once __DIR__ . '/../src/TaskController.php';

header('Content-Type: application/json');

function sendCorsHeaders() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    } else {
        header('Access-Control-Allow-Origin: *');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400');
}

sendCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = (new Database())->getConnection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['error' => 'database_unavailable']);
    exit;
}

$auth = new AuthController($db);
$tasks = new TaskController($db);

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$path = $uri;
if ($scriptDir !== '' && $scriptDir !== '/') {
    if (strpos($uri, $scriptDir) === 0) {
        $path = substr($uri, strlen($scriptDir));
    }
}
// If index.php is present in the remaining path, remove it (handles PATH_INFO)
$path = preg_replace('#^/index\.php#', '', $path);
if ($path === '') $path = '/';

function body() {
    return json_decode(file_get_contents('php://input'), true);
}

function bearerToken() {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (!isset($headers['Authorization'])) {
        return null;
    }
    if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
        return $matches[1];
    }
    return null;
}

$token = bearerToken();
$user = $token ? $auth->verifyJWT($token) : null;

if ($path === '/register' && $method === 'POST') {
    $d = body();
    if (empty($d['name']) || empty($d['email']) || empty($d['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_input']);
        exit;
    }
    $ok = $auth->register($d['name'], $d['email'], $d['password']);
    if ($ok) {
        echo json_encode(['message' => 'registered']);
    } else {
        http_response_code(409);
        echo json_encode(['error' => 'user_exists']);
    }
    exit;
}

if ($path === '/login' && $method === 'POST') {
    $d = body();
    if (empty($d['email']) || empty($d['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_input']);
        exit;
    }
    $res = $auth->login($d['email'], $d['password']);
    if ($res) {
        echo json_encode($res);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'invalid_credentials']);
    }
    exit;
}

if (strpos($path, '/tasks') === 0) {
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'unauthenticated']);
        exit;
    }
    if ($method === 'GET') {
        $qs = [];
        parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
        $status = $qs['status'] ?? null;
        $limit = isset($qs['limit']) ? (int) $qs['limit'] : 10;
        $offset = isset($qs['offset']) ? (int) $qs['offset'] : 0;
        $result = $user['role'] === 'admin'
            ? $tasks->getTasks(null, $status, $limit, $offset)
            : $tasks->getTasks($user['sub'], $status, $limit, $offset);
        echo json_encode($result);
        exit;
    }
    if ($method === 'POST') {
        $d = body();
        $d['owner_id'] = $user['sub'];
        $ok = $tasks->createTask($d);
        if ($ok) {
            http_response_code(201);
            echo json_encode(['message' => 'created']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'create_failed']);
        }
        exit;
    }
    if (($method === 'PUT' || $method === 'PATCH') && preg_match('#/tasks/(\d+)#', $path, $m)) {
        $id = (int) $m[1];
        $d = body();
        if ($user['role'] !== 'admin') {
            $stmt = $db->prepare('SELECT owner_id FROM tasks WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['owner_id'] != $user['sub']) {
                http_response_code(403);
                echo json_encode(['error' => 'forbidden']);
                exit;
            }
        }
        $ok = $tasks->updateTask($id, $d);
        if ($ok) {
            echo json_encode(['message' => 'updated']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'update_failed']);
        }
        exit;
    }
    if ($method === 'DELETE' && preg_match('#/tasks/(\d+)#', $path, $m)) {
        $id = (int) $m[1];
        if ($user['role'] !== 'admin') {
            $stmt = $db->prepare('SELECT owner_id FROM tasks WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['owner_id'] != $user['sub']) {
                http_response_code(403);
                echo json_encode(['error' => 'forbidden']);
                exit;
            }
        }
        $ok = $tasks->deleteTask($id);
        if ($ok) {
            echo json_encode(['message' => 'deleted']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'delete_failed']);
        }
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'not_found']);
