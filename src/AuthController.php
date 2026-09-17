<?php
class AuthController {
    private $conn;
    private $jwt_secret = 'replace_this_with_secure_random_secret';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($name, $email, $password, $role = 'user') {
        $stmt = $this->conn->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) return false;
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        return $stmt->execute(['name'=>$name,'email'=>$email,'password_hash'=>$hash,'role'=>$role]);
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = ['sub' => $user['id'], 'name' => $user['name'], 'role' => $user['role'], 'iat' => time(), 'exp' => time()+3600];
            $token = $this->generateJWT($payload);
            return ['token'=>$token,'role'=>$user['role']];
        }
        return false;
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function generateJWT($payload) {
        $header = $this->base64UrlEncode(json_encode(['alg'=>'HS256','typ'=>'JWT']));
        $body = $this->base64UrlEncode(json_encode($payload));
        $sig = hash_hmac('sha256', "$header.$body", $this->jwt_secret, true);
        $sig = $this->base64UrlEncode($sig);
        return "$header.$body.$sig";
    }

    public function verifyJWT($token) {
        $parts = explode('.', $token);
        if (count($parts)!==3) return false;
        [$header,$body,$sig] = $parts;
        $validSig = $this->base64UrlEncode(hash_hmac('sha256', "$header.$body", $this->jwt_secret, true));
        if (!hash_equals($validSig, $sig)) return false;
        $payload = json_decode(base64_decode(strtr($body, '-_', '+/')), true);
        if (!$payload) return false;
        if (isset($payload['exp']) && time() > $payload['exp']) return false;
        return $payload;
    }
}
