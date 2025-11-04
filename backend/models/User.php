<?php

class User {
    public $id;
    public $username;
    public $password_hash;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct($id = null, $username = null, $password_hash = null, $role = 'user', $created_at = null, $updated_at = null) {
        $this->id = $id;
        $this->username = $username;
        $this->password_hash = $password_hash;
        $this->role = $role;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * Verify a password against the stored hash
     * 
     * @param string $password The plain text password to verify
     * @return bool True if password matches, false otherwise
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Check if user has admin role
     * 
     * @return bool True if user is admin, false otherwise
     */
    public function isAdmin() {
        return $this->role === 'admin';
    }

    /**
     * Convert user object to array (excluding password_hash for security)
     * 
     * @param bool $includePassword Whether to include password_hash in output
     * @return array User data as associative array
     */
    public function toArray($includePassword = false) {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];

        if ($includePassword) {
            $data['password_hash'] = $this->password_hash;
        }

        return $data;
    }
}
