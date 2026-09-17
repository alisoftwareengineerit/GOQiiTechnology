<?php
class TaskController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createTask($data) {
        $fields = ['title','description','status','priority','due_date','owner_id'];
        $params = [];
        foreach ($fields as $f) {
            $params[$f] = isset($data[$f]) ? $data[$f] : null;
        }
        $stmt = $this->conn->prepare('INSERT INTO tasks (title, description, status, priority, due_date, owner_id) VALUES (:title, :description, :status, :priority, :due_date, :owner_id)');
        return $stmt->execute($params);
    }

    public function getTasks($owner_id=null, $status=null, $limit=10, $offset=0) {
        $query = 'SELECT id, title, description, status, priority, due_date, owner_id FROM tasks';
        $conds = [];
        $params = [];
        if ($owner_id !== null) { $conds[] = 'owner_id = :owner_id'; $params['owner_id'] = $owner_id; }
        if ($status !== null) { $conds[] = 'status = :status'; $params['status'] = $status; }
        if ($conds) $query .= ' WHERE '.implode(' AND ', $conds);
        $query .= ' ORDER BY due_date IS NULL, due_date ASC, priority DESC';
        $query .= ' LIMIT :limit OFFSET :offset';
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k=>$v) { $stmt->bindValue(':' . $k, $v); }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateTask($id, $data) {
        $fields = ['title','description','status','priority','due_date'];
        $set = [];
        $params = ['id'=>$id];
        foreach ($fields as $f) {
            if (isset($data[$f])) { $set[] = "$f = :$f"; $params[$f] = $data[$f]; }
        }
        if (!$set) return false;
        $stmt = $this->conn->prepare('UPDATE tasks SET '.implode(', ', $set).' WHERE id = :id');
        return $stmt->execute($params);
    }

    public function deleteTask($id) {
        $stmt = $this->conn->prepare('DELETE FROM tasks WHERE id = :id');
        return $stmt->execute(['id'=>$id]);
    }
}
