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

// Kelas TransaksiHistory untuk mengelola riwayat transaksi
class TransaksiHistory {
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

    // Metode untuk mengambil data header transaksi
    public function getTransactionHeaders($user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "
                SELECT idtransaksi, tgltransaksi, nama, alamat, kodepos, nohp
                FROM hkeranjang
                WHERE id = ?
                ORDER BY tgltransaksi DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $headers = [];
            while ($row = $result->fetch_assoc()) {
                $headers[] = $row;
            }
            $stmt->close();
            return $headers;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data transaksi: " . $e->getMessage());
        }
    }

    // Metode untuk mengambil detail transaksi
    public function getTransactionDetails($idtransaksi) {
        try {
            $conn = $this->db->getConnection();
            $sql = "
                SELECT d.idkeranjang, d.idproduk, d.harga, d.quantity, p.nama_produk, p.gambar
                FROM dkeranjang d
                JOIN produk p ON d.idproduk = p.idproduk
                WHERE d.idtransaksi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $idtransaksi);
            $stmt->execute();
            $result = $stmt->get_result();
            $details = [];
            $grand_total = 0;
            while ($row = $result->fetch_assoc()) {
                $row['total'] = $row['harga'] * $row['quantity'];
                $grand_total += $row['total'];
                $details[] = $row;
            }
            $stmt->close();
            return ['items' => $details, 'grand_total' => $grand_total];
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil detail transaksi: " . $e->getMessage());
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
$transactions = [];
$selected_transaction = null;
$transaction_details = [];
$grand_total = 0;

// Proses pengambilan data
try {
    $history = new TransaksiHistory();
    
    // Ambil data header transaksi
    $transactions = $history->getTransactionHeaders($user_id);
    
    // Jika ada parameter idtransaksi, ambil detail transaksi
    if (isset($_GET['idtransaksi'])) {
        $idtransaksi = $_GET['idtransaksi'];
        $transaction_data = $history->getTransactionDetails($idtransaksi);
        $transaction_details = $transaction_data['items'];
        $grand_total = $transaction_data['grand_total'];
        
        // Cari data header transaksi terpilih
        foreach ($transactions as $transaction) {
            if ($transaction['idtransaksi'] == $idtransaksi) {
                $selected_transaction = $transaction;
                break;
            }
        }
    }
    
    $history->closeConnection();
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
    <title>Riwayat Transaksi - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="keranjang.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Tambahan CSS untuk halaman riwayat transaksi */
        .history-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .history-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            width: calc(33.33% - 20px);
            min-width: 250px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .history-card:hover {
            transform: translateY(-5px);
        }
        
        .history-card.active {
            border: 2px solid #4CAF50;
        }
        
        .history-card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .history-card p {
            margin: 5px 0;
            color: #666;
        }
        
        .history-details {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        
        .transaction-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .transaction-info-item {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            flex: 1;
            min-width: 200px;
        }
        
        .transaction-info-item h4 {
            margin-top: 0;
            color: #555;
        }
        
        .transaction-info-item p {
            margin: 5px 0;
            color: #777;
        }
        
        .btn-back {
            background-color: #555;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background-color: #333;
        }
        
        .no-transactions {
            text-align: center;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .history-card {
                width: 100%;
            }
        }
    </style>
    <style>
    /* Existing CSS remains... */
    
    /* Add these styles for the print button */
    .print-section {
        text-align: right;
        margin-top: 20px;
    }
    
    .print-btn {
        background-color:rgb(17, 17, 18);
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s;
    }
    
    .print-btn:hover {
        background-color:rgb(0, 0, 0);
    }
</style>

</head>
<body>
    <!-- Sertakan header -->
    <?php include 'header.php'; ?>

    <!-- Kontainer utama(scrap) -->
    <div class="container">

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Tombol kembali ke keranjang -->
        <a href="keranjang.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
        </a>
        
        <?php if (empty($transactions)): ?>
            <div class="no-transactions">
                <i class="fas fa-history fa-3x" style="margin-bottom: 15px;"></i>
                <h2>Belum Ada Transaksi</h2>
                <p>Anda belum memiliki riwayat transaksi.</p>
            </div>
        <?php else: ?>
            <!-- Kartu riwayat transaksi -->
            <div class="history-container">
                <?php foreach ($transactions as $transaction): ?>
                    <a href="?idtransaksi=<?php echo htmlspecialchars($transaction['idtransaksi']); ?>" class="history-card <?php echo (isset($_GET['idtransaksi']) && $_GET['idtransaksi'] == $transaction['idtransaksi']) ? 'active' : ''; ?>">
                        <h3><i class="fas fa-receipt"></i> <?php echo htmlspecialchars($transaction['idtransaksi']); ?></h3>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d M Y H:i', strtotime($transaction['tgltransaksi'])); ?></p>
                        <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($transaction['nama']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($transaction['alamat']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Detail transaksi terpilih -->
            <?php if ($selected_transaction): ?>
                <div class="history-details">
                    <h2>Detail Transaksi: <?php echo htmlspecialchars($selected_transaction['idtransaksi']); ?></h2>
                    
                    <!-- Informasi transaksi -->
                    <div class="transaction-info">
                        <div class="transaction-info-item">
                            <h4>Informasi Pengiriman</h4>
                            <p>Nama: <?php echo htmlspecialchars($selected_transaction['nama']); ?></p>
                            <p>Alamat: <?php echo htmlspecialchars($selected_transaction['alamat']); ?></p>
                            <p>Kode Pos: <?php echo htmlspecialchars($selected_transaction['kodepos']); ?></p>
                            <p>No. HP: <?php echo htmlspecialchars($selected_transaction['nohp']); ?></p>
                        </div>
                        <div class="transaction-info-item">
                            <h4>Informasi Transaksi</h4>
                            <p>ID Transaksi: <?php echo htmlspecialchars($selected_transaction['idtransaksi']); ?></p>
                            <p>Tanggal: <?php echo date('d M Y H:i', strtotime($selected_transaction['tgltransaksi'])); ?></p>
                            <p>Status: <span class="badge success">Lunas</span></p>
                        </div>
                    </div>
                    
                    <!-- Tabel detail produk -->
                    <div class="table-wrapper">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($transaction_details as $item):
                                    $total = $item['harga'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><img src="images/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"></td>
                                    <td class="text-left"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                    <td class="text-right">$ <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="text-right">$ <?php echo number_format($total, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Grand total -->
                    <div class="grand-total">
                        Grand Total: $ <?php echo number_format($grand_total, 0, ',', '.'); ?>
                    </div>
                    <div class="print-section">
                        <a href="print_transaksi.php?idtransaksi=<?php echo htmlspecialchars($selected_transaction['idtransaksi']); ?>" class="btn print-btn" target="_blank">
                            <i class="fas fa-print"></i> Print Transaksi
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>