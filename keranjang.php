<?php
// Mulai sesi
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Sertakan koneksi
require_once 'koneksi.php';

// Kelas Cart untuk mengelola operasi keranjang
class Cart {
    // Properti privat untuk instance Database
    private $db;

    // Konstruktor untuk inisialisasi koneksi
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi database: " . $e->getMessage());
        }
    }

    // Metode untuk mengambil data keranjang
    public function getCartItems($user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "
                SELECT d.idkeranjang, d.idproduk, d.harga, d.quantity, p.nama_produk, p.gambar
                FROM dkeranjang d
                JOIN produk p ON d.idproduk = p.idproduk
                WHERE d.id = ? AND d.keterangan = 'Pending'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            $grand_total = 0;
            while ($row = $result->fetch_assoc()) {
                $row['total'] = $row['harga'] * $row['quantity'];
                $grand_total += $row['total'];
                $items[] = $row;
            }
            $stmt->close();
            return ['items' => $items, 'grand_total' => $grand_total];
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data keranjang: " . $e->getMessage());
        }
    }

    // Metode untuk menambah quantity
    public function increaseQuantity($idkeranjang, $user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "UPDATE dkeranjang SET quantity = quantity + 1 WHERE idkeranjang = ? AND id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $idkeranjang, $user_id);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $e) {
            throw new Exception("Gagal menambah quantity: " . $e->getMessage());
        }
    }

    // Metode untuk mengurangi quantity
    public function decreaseQuantity($idkeranjang, $user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "UPDATE dkeranjang SET quantity = quantity - 1 WHERE idkeranjang = ? AND id = ? AND quantity > 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $idkeranjang, $user_id);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $e) {
            throw new Exception("Gagal mengurangi quantity: " . $e->getMessage());
        }
    }

    // Metode untuk menghapus item
    public function deleteItem($idkeranjang, $user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "DELETE FROM dkeranjang WHERE idkeranjang = ? AND id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $idkeranjang, $user_id);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $e) {
            throw new Exception("Gagal menghapus item: " . $e->getMessage());
        }
    }

    // Metode untuk proses checkout
    public function checkout($user_id, $nama, $alamat, $kodepos, $nohp) {
        try {
            $conn = $this->db->getConnection();
        
            // Generate id transaksi (format: TYYYYMMNNN)
            $year = date('Y');
            $month = date('m');
            $sql_max = "SELECT MAX(idtransaksi) as max_id FROM hkeranjang WHERE idtransaksi LIKE ?";
            $pattern = "T$year$month%";
            $stmt_max = $conn->prepare($sql_max);
            $stmt_max->bind_param("s", $pattern);
            $stmt_max->execute();
            $result = $stmt_max->get_result();
            $row = $result->fetch_assoc();
            $max_id = $row['max_id'];
            $stmt_max->close();
            
            $next_id = $max_id ? intval(substr($max_id, -3)) + 1 : 1;
            $idtransaksi = 'T' . $year . $month . sprintf('%03d', $next_id);
			$user_id = 'T' . $year . $month . sprintf('%03d', $next_id);
        
            // Simpan ke keranjang
            $tgltransaksi = date('Y-m-d H:i:s');
            $sql_insert = "INSERT INTO hkeranjang (idtransaksi, id, tgltransaksi, nama, alamat, kodepos, nohp) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssssss", $idtransaksi, $user_id, $tgltransaksi, $nama, $alamat, $kodepos, $nohp);
            $stmt_insert->execute();
            $stmt_insert->close();

            // Update dkeranjang
            $sql_update = "UPDATE dkeranjang SET keterangan = 'Lunas', idtransaksi = ? WHERE id = ? AND keterangan = 'Pending'";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $idtransaksi, $user_id);
            $stmt_update->execute();
            $stmt_update->close();

            return true;
        } catch (Exception $e) {
            throw new Exception("Gagal memproses checkout: " . $e->getMessage());
        }
    }

    // Metode untuk menutup koneksi
    public function closeConnection() {
        $this->db->closeConnection();
    }
}

// Inisialisasi variabel
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$cart_items = [];
$grand_total = 0;

// Proses operasi keranjang
try {
    $cart = new Cart();

    // Tambah quantity
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
        $idkeranjang = $_POST['idkeranjang'];
        if ($cart->increaseQuantity($idkeranjang, $user_id)) {
            header("Location: keranjang.php");
            exit();
        }
    }
       
    // Kurangi quantity
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kurang'])) {
        $idkeranjang = $_POST['idkeranjang'];
        if ($cart->decreaseQuantity($idkeranjang, $user_id)) {
            header('Location: keranjang.php');
            exit();
        }
    }

    // Hapus item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
        $idkeranjang = $_POST['idkeranjang'];
        if ($cart->deleteItem($idkeranjang, $user_id)) {
            header('Location: keranjang.php');
            exit();
        }
    }

    // Proses checkout
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
        $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
        $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
        $kodepos = isset($_POST['kodepos']) ? trim($_POST['kodepos']) : '';
        $nohp = isset($_POST['nohp']) ? trim($_POST['nohp']) : '';

        if (empty($nama) || empty($alamat) || empty($kodepos) || empty($nohp)) {
            $error = "Semua field harus diisi!";
        } else {
            if ($cart->checkout($user_id, $nama, $alamat, $kodepos, $nohp)) {
                $success = "Checkout berhasil! Transaksi telah disimpan.";
            }
        }
    }

    // Ambil data keranjang
    $cart_data = $cart->getCartItems($user_id);
    $cart_items = $cart_data['items'];
    $grand_total = $cart_data['grand_total'];

    $cart->closeConnection();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Metadata -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="keranjang.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Sertakan header -->
    <?php include 'header.php'; ?>

    <!-- Kontainer keranjang -->
    <div class="container">
        

        <!-- Pesan kesalahan -->
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Pesan sukses -->
        <?php if ($success): ?>
          <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Wrapper tabel untuk scroll horizontal -->
        <div class="table-wrapper">
            <!-- Tabel Keranjang -->
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($cart_items as $item):
                        $total = $item['harga'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><img src="images/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"></td>
                        <td class="text-left"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                        <td class="text-right">$ <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td class="text-right">$ <?php echo number_format($total, 0, ',', '.'); ?></td>
                        <td class="actions">
                            <form method="POST" action="keranjang.php">
                                <input type="hidden" name="idkeranjang" value="<?php echo htmlspecialchars($item['idkeranjang']); ?>">
                                <button type="submit" name="tambah" class="action-btn" title="Tambah Quantity"><i class="fas fa-plus"></i></button>
                            </form>
                            <form method="POST" action="keranjang.php">
                                <input type="hidden" name="idkeranjang" value="<?php echo htmlspecialchars($item['idkeranjang']); ?>">
                                <button type="submit" name="kurang" class="action-btn" title="Kurangi Quantity"><i class="fas fa-minus"></i></button>
                            </form>
                            <form method="POST" action="keranjang.php" onsubmit="return confirm('Yakin ingin menghapus item ini?');">
                                <input type="hidden" name="idkeranjang" value="<?php echo htmlspecialchars($item['idkeranjang']); ?>">
                                <button type="submit" name="hapus" class="action-btn" title="Hapus Item"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cart_items)): ?>
                    <tr class="no-products">
                        <td colspan="7">Keranjang kosong.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Grand total -->
        <div class="grand-total">
            Grand Total: $ <?php echo number_format($grand_total, 0, ',', '.'); ?>
        </div>

<!-- Tombol Checkout dan Purchase History -->
        <div class="action-buttons" style="display: flex; gap: 10px; margin-top: 20px;">
            <?php if (!empty($cart_items)): ?>
                <button class="btn" onclick="document.getElementById('checkoutModal').style.display='block'">
                    <i class="fas fa-shopping-cart"></i> Checkout
                </button>
            <?php endif; ?>
            <a href="riwayat_transaksi.php" class="btn-history">
                <i class="fas fa-history"></i> Purchase History
            </a>
        </div>

    <style>
        .btn-history {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
        }
        .btn-history:hover {
            background-color: #45a049;
        }
    </style>

    <!-- Modal Checkout -->
    <div id="checkoutModal" class="checkout-modal">
        <div class="checkout-modal-content">
            <span class="close" onclick="document.getElementById('checkoutModal').style.display='none'">x</span>
            <h2>Checkout</h2>
            <form method="POST" action="" class="checkout-form">
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <input type="text" id="alamat" name="alamat" required>
                </div>
                <div class="form-group">
                    <label for="kodepos">Kode Pos:</label>
                    <input type="text" id="kodepos" name="kodepos" required>
                </div>
                <div class="form-group">
                    <label for="nohp">No. HP:</label>
                    <input type="text" id="nohp" name="nohp" required>
                </div>
                <button type="submit" name="checkout" class="btn">Submit</button>
            </form>
        </div>
    </div>

    <script>
        // Script untuk menutup modal saat klik di luar modal
        window.onclick = function(event) {
            var modal = document.getElementById('checkoutModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>