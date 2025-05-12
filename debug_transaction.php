<?php
// Mulai sesi
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Sertakan koneksi database
require_once 'koneksi.php';

// Display connection and transaction debugging info
echo "<h2>Debug Informasi Transaksi</h2>";

// Fungsi untuk menampilkan data dalam format yang mudah dibaca
function debugData($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

// Get transaction ID from URL
$idtransaksi = isset($_GET['idtransaksi']) ? $_GET['idtransaksi'] : '';
echo "<p><strong>ID Transaksi yang dicari:</strong> " . htmlspecialchars($idtransaksi) . "</p>";

try {
    // Buat koneksi database
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p><strong>Status koneksi database:</strong> Berhasil terhubung</p>";
    
    // 1. Periksa data di tabel hkeranjang
    echo "<h3>Data di Tabel hkeranjang:</h3>";
    $sql1 = "SELECT * FROM hkeranjang";
    $result1 = $conn->query($sql1);
    
    if ($result1->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>idtransaksi</th>
                    <th>id</th>
                    <th>tgltransaksi</th>
                    <th>nama</th>
                    <th>alamat</th>
                    <th>kodepos</th>
                    <th>nohp</th>
                </tr>";
        
        while($row = $result1->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['idtransaksi']) . "</td>
                    <td>" . htmlspecialchars($row['id']) . "</td>
                    <td>" . htmlspecialchars($row['tgltransaksi']) . "</td>
                    <td>" . htmlspecialchars($row['nama']) . "</td>
                    <td>" . htmlspecialchars($row['alamat']) . "</td>
                    <td>" . htmlspecialchars($row['kodepos']) . "</td>
                    <td>" . htmlspecialchars($row['nohp']) . "</td>
                </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada data di tabel hkeranjang</p>";
    }
    
    // 2. Periksa data di tabel dkeranjang
    echo "<h3>Data di Tabel dkeranjang:</h3>";
    $sql2 = "SELECT * FROM dkeranjang";
    $result2 = $conn->query($sql2);
    
    if ($result2->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>idkeranjang</th>
                    <th>idproduk</th>
                    <th>id</th>
                    <th>idtransaksi</th>
                    <th>harga</th>
                    <th>quantity</th>
                    <th>keterangan</th>
                </tr>";
        
        while($row = $result2->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['idkeranjang']) . "</td>
                    <td>" . htmlspecialchars($row['idproduk']) . "</td>
                    <td>" . htmlspecialchars($row['id']) . "</td>
                    <td>" . htmlspecialchars($row['idtransaksi']) . "</td>
                    <td>" . htmlspecialchars($row['harga']) . "</td>
                    <td>" . htmlspecialchars($row['quantity']) . "</td>
                    <td>" . htmlspecialchars($row['keterangan']) . "</td>
                </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada data di tabel dkeranjang</p>";
    }
    
    // 3. Periksa apakah transaksi dengan ID yang dicari ada
    if (!empty($idtransaksi)) {
        echo "<h3>Mencari Transaksi dengan ID: " . htmlspecialchars($idtransaksi) . "</h3>";
        
        $sql3 = "SELECT h.idtransaksi, h.tgltransaksi, h.nama, h.alamat, h.kodepos, h.nohp, 
                 d.idproduk, d.harga, d.quantity, p.nama_produk 
                 FROM hkeranjang h 
                 JOIN dkeranjang d ON h.idtransaksi = d.idtransaksi 
                 JOIN produk p ON d.idproduk = p.idproduk 
                 WHERE h.idtransaksi = ?";
        
        $stmt = $conn->prepare($sql3);
        $stmt->bind_param("s", $idtransaksi);
        $stmt->execute();
        $result3 = $stmt->get_result();
        
        if ($result3->num_rows > 0) {
            echo "<p>Transaksi ditemukan! Detail:</p>";
            echo "<table border='1' cellpadding='5'>
                    <tr>
                        <th>idtransaksi</th>
                        <th>tgltransaksi</th>
                        <th>nama</th>
                        <th>idproduk</th>
                        <th>nama_produk</th>
                        <th>harga</th>
                        <th>quantity</th>
                    </tr>";
            
            while($row = $result3->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['idtransaksi']) . "</td>
                        <td>" . htmlspecialchars($row['tgltransaksi']) . "</td>
                        <td>" . htmlspecialchars($row['nama']) . "</td>
                        <td>" . htmlspecialchars($row['idproduk']) . "</td>
                        <td>" . htmlspecialchars($row['nama_produk']) . "</td>
                        <td>" . htmlspecialchars($row['harga']) . "</td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red;'>Transaksi dengan ID $idtransaksi tidak ditemukan.</p>";
            
            // Cek apakah ada di hkeranjang saja
            $sql4 = "SELECT * FROM hkeranjang WHERE idtransaksi = ?";
            $stmt = $conn->prepare($sql4);
            $stmt->bind_param("s", $idtransaksi);
            $stmt->execute();
            $result4 = $stmt->get_result();
            
            if ($result4->num_rows > 0) {
                echo "<p>Ada di tabel hkeranjang tapi tidak terhubung dengan dkeranjang.</p>";
            } else {
                echo "<p>Tidak ada di tabel hkeranjang.</p>";
            }
            
            // Cek apakah ada di dkeranjang saja
            $sql5 = "SELECT * FROM dkeranjang WHERE idtransaksi = ?";
            $stmt = $conn->prepare($sql5);
            $stmt->bind_param("s", $idtransaksi);
            $stmt->execute();
            $result5 = $stmt->get_result();
            
            if ($result5->num_rows > 0) {
                echo "<p>Ada di tabel dkeranjang tapi tidak terhubung dengan hkeranjang.</p>";
            } else {
                echo "<p>Tidak ada di tabel dkeranjang.</p>";
            }
        }
    }
    
    // Tutup koneksi
    $db->closeConnection();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='javascript:history.back()'>Kembali</a></p>";
?>