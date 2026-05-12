<?php
/**
 * User Model
 * Food Safety & Premises Grading Management System
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new user
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, password, phone, role, nic, district, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $hashedPassword = hashPassword($data['password']);

        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? null,
            $data['role'],
            $data['nic'] ?? null,
            $data['district'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, nic, district, status, profile_image, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'password') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = hashPassword($data['password']);
        }

        $values[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all users with pagination
     */
    public function getAll($role = null, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        if ($role) {
            $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, district, status, created_at FROM users WHERE role = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$role, $perPage, $offset]);
        } else {
            $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, district, status, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$perPage, $offset]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Count users
     */
    public function count($role = null) {
        if ($role) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->execute([$role]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        }

        return $stmt->fetch()['count'];
    }

    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Update password reset token
     */
    public function setResetToken($email, $token) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        return $stmt->execute([$token, $email]);
    }

    /**
     * Find by reset token
     */
    public function findByResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Clear reset token
     */
    public function clearResetToken($id) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search users
     */
    public function search($query, $role = null) {
        $sql = "SELECT id, full_name, email, phone, role, district, status FROM users WHERE (full_name LIKE ? OR email LIKE ? OR nic LIKE ?)";
        $params = ["%$query%", "%$query%", "%$query%"];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY full_name LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
