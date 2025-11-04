<?php

/**
 * Users Seeder
 * Seeds admin and regular users
 */

class UsersSeeder
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function run()
    {
        echo "  Seeding users...\n";

        // Admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password_hash, role) 
            VALUES ('admin', :password_hash, 'admin')
            ON DUPLICATE KEY UPDATE password_hash = :password_hash
        ");
        $stmt->execute([':password_hash' => $adminPassword]);

        // Regular users
        $users = [
            ['username' => 'teacher1', 'password' => 'teacher123', 'role' => 'user'],
            ['username' => 'teacher2', 'password' => 'teacher123', 'role' => 'user'],
        ];

        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password_hash, role) 
            VALUES (:username, :password_hash, :role)
            ON DUPLICATE KEY UPDATE password_hash = :password_hash
        ");

        foreach ($users as $user) {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt->execute([
                ':username' => $user['username'],
                ':password_hash' => $hashedPassword,
                ':role' => $user['role']
            ]);
        }

        echo "    ✓ Seeded " . (count($users) + 1) . " users\n";
    }
}
