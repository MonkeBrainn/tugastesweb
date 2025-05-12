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

// Kelas TransactionHistory untuk mengelola riwayat transaksi
class TransactionHistory {
    // Properti private untuk koneksi Database
    private $db;

    // Konstruktor untuk inisialisasi koneksi
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi database: " . $e->getMessage());
        }
    }

    // Metode untuk mengambil riwayat transaksi
    public function getTransactionHistory($user_id) {
        try {
            $conn = $this->db->getConnection();
            $sql = "
                SELECT 	h.idtransaksi, h.tgltransaksi, h.nama, h.alamat, h.kodepos, h.nohp,
						d.idproduk, d.harga, d.quantity, p.nama_produk, p.gambar
                FROM hkeranjang h
                JOIN dkeranjang d ON h.idtransaksi = d.idtransaksi
                JOIN produk p ON d.idproduk = p.idproduk
                WHERE h.id = ?
                ORDER BY h.idtransaksi DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Kelompokkan data transaksi berdasarkan idtransaksi
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $idtransaksi = $row['idtransaksi'];
                if (!isset($transactions[$idtransaksi])) {
                    $transactions[$idtransaksi] = [
                        'tgltransaksi' => $row['tgltransaksi'],
                        'nama' => $row['nama'],
                        'alamat' => $row['alamat'],
                        'kodepos' => $row['kodepos'],
                        'nohp' => $row['nohp'],
                        'items' => [],
                        'total' => 0
                    ];
                }
                $transactions[$idtransaksi]['items'][] = $row;
                $transactions[$idtransaksi]['total'] += $row['harga'] * $row['quantity'];
            }
            $stmt->close();
            return $transactions;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil riwayat transaksi: " . $e->getMessage());
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

try {
    $history = new TransactionHistory();
    $transactions = $history->getTransactionHistory($user_id);
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
    <title>Riwayat Belanja - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
	<!-- Sertakan header -->
	<?php include 'header.php'; ?>

	<!-- Sertakan navbar -->
	<?php include 'navbar.php'; ?>

	<!-- Kontainer riwayat -->
	<div class="history-container">
		<h2>Riwayat Belanja</h2>

    <!-- Pesan kesalahan -->
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Daftar transaksi -->
    <?php if (empty($transactions)): ?>
        <p class="no-transactions">Belum ada riwayat belanja.</p>
    <?php else: ?>
        <?php foreach ($transactions as $idtransaksi => $transaction): ?>
            <div class="transaction-card">
                <div class="transaction-header">
					<div>
						<h3>Transaksi: <?php echo htmlspecialchars($idtransaksi); ?></h3>
						<p>Tanggal: <?php echo htmlspecialchars($transaction['tgltransaksi']); ?></p>
						<p>Nama: <?php echo htmlspecialchars($transaction['nama']); ?></p>
						<p>Alamat: <?php echo htmlspecialchars($transaction['alamat']); ?></p>
						<p>Kode Pos: <?php echo htmlspecialchars($transaction['kodepos']); ?></p>
						<p>No. HP: <?php echo htmlspecialchars($transaction['nohp']); ?></p>
					</div>
                    <button class="btn print-btn" onclick="printTransaction('<?php echo htmlspecialchars($idtransaksi); ?>')">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                <div class="history-table-wrapper">
                    <table class="transaction-table">
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
                            foreach ($transaction['items'] as $item):
                                $total = $item['harga'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><img src="images/<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"></td>
                                    <td class="text-left"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="text-right">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="grand-total">
                    Grand Total: Rp <?php echo number_format($transaction['total'], 0, ',', '.'); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
	</div>

	<script>
		// Fungsi untuk mencetak transaksi
		function printTransaction(idtransaksi) {
			window.location.href = `print_transaksi.php?idtransaksi=${idtransaksi}`;
		}
	</script>
</body>
</html>
