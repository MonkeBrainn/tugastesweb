<?php
// Kelas Database untuk mengelola koneksi database dengan pendekatan OOP
class Database {
    // Properti privat untuk kredensial database
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'toko_online';

    // Properti privat untuk objek koneksi
    private $conn = null;

    // Metode privat untuk membuka koneksi
    private function connect() {
        try {
            // Inisialisasi koneksi mysqli
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            // Periksa koneksi
            if ($this->conn->connect_error) {
                throw new Exception("Koneksi gagal: " . $this->conn->connect_error);
            }
            // Set charset ke utf8mb4
            $this->conn->set_charset('utf8mb4');
        } catch (Exception $e) {
            throw new Exception("Kesalahan koneksi: " . $e->getMessage());
        }
    }

    // Metode publik untuk mendapatkan koneksi
    public function getConnection() {
        try {
            if ($this->conn === null) {
                $this->connect();
            }
            return $this->conn;
        } catch (Exception $e) {
            throw new Exception("Gagal mendapatkan koneksi: " . $e->getMessage());
        }
    }

    // Metode untuk menutup koneksi
    public function closeConnection() {
        if ($this->conn !== null) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}
?>
