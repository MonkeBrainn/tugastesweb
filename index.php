<?php
// Mulai sesi
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Sertakan koneksi dan kelas
require_once 'koneksi.php';

// Kelas Product untuk mengelola produk dan keranjang
class Product {
    // Properti privat untuk instance Database
    private $db;

    // Konstruktor untuk inisialisasi koneksi
    public function __construct() {
        try {
            $this->db = new Database();
        } catch (Exception $e) {
            throw new Exception('Gagal inisialisasi database: ' . $e->getMessage());
        }
    }

    // Metode untuk mengambil semua produk
    public function getAllProducts() {
        try {
            $conn = $this->db->getConnection();
            $sql = "SELECT idproduk, nama_produk, harga, gambar FROM produk";
            $result = $conn->query($sql);
            $products = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            }
            return $products;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil produk: ' . $e->getMessage());
        }
    }

    // Metode untuk menambahkan produk ke keranjang
    public function addToCart($id_produk, $user_id) {
        try {
            $conn = $this->db->getConnection();
 
			// Ambil harga produk
			$query_harga = $conn->prepare("SELECT idproduk, harga FROM produk WHERE idproduk = ?");
			$query_harga->bind_param("s", $id_produk);
			$query_harga->execute();
			$query_harga->bind_result($idProduk, $harga);
			$query_harga->fetch();
			$query_harga->close();

			if (!$harga) {
				throw new Exception("Produk tidak ditemukan.");
			}

			// cek apakah produk sudah ada di keranjang dengan status "Pending"
			$cek = $conn->prepare("SELECT idkeranjang, quantity FROM dkeranjang WHERE idproduk = ? AND harga = ? AND id = ? AND keterangan = 'Pending'");
			$cek->bind_param("sis", $id_produk, $harga, $user_id);
			$cek->execute();
				$cek->store_result();

			if ($cek->num_rows > 0) {
				// update quantity jika produk sudah ada
				$cek->bind_result($idkeranjang, $quantity);
				$cek->fetch();
				$new_quantity = $quantity + 1;
				$update = $conn->prepare("UPDATE dkeranjang SET quantity = ? WHERE idkeranjang = ?");
				$update->bind_param("ii", $new_quantity, $idkeranjang);
				$update->execute();
				$update->close();
			} else {
				// insert produk baru ke keranjang
				$insert = $conn->prepare("INSERT INTO dkeranjang (idproduk, harga, quantity, keterangan, id) VALUES (?, ?, 1, 'Pending', ?)");
				$insert->bind_param("sis", $id_produk, $harga, $user_id);
				$insert->execute();
				$insert->close();
			}
			$cek->close();
			return true;
		} catch (Exception $e) {
			throw new Exception("Gagal menambahkan ke keranjang: " . $e->getMessage());
		}
	}

	// Metode untuk menutup koneksi
	public function closeConnection() {
		$this->db->closeConnection();
	}
}

// Inisialisasi variabel
$error = '';
$show_modal = false;

// Proses penambahan ke keranjang
if (isset($_GET['beli'])) {
    $id_produk = $_GET['beli'];
    $user_id = $_SESSION['user_id'];

    try {
        $product = new Product();
        if ($product->addToCart($id_produk, $user_id)) {
            $show_modal = true;
        }
        $product->closeConnection();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Ambil data produk
$products = [];
try {
    $product = new Product();
    $products = $product->getAllProducts();
    $product->closeConnection();
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
    <title>Produk - Toko Online</title>
    <!-- CSS eksternal -->
    <link rel="stylesheet" href="styles.css">
    <!-- Font awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Sertakan header -->
    <?php include 'header.php'; ?>

	<!-- Sertakan navbar -->
	<?php include 'navbar.php'; ?>

    <!-- Modal using proper structure from CSS -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <p>Produk berhasil dimasukkan ke keranjang!</p>
            <button class="btn" onclick="closeModal()">OK</button>
        </div>
    </div>

	<!-- Kontainer produk -->
	<div class="product-container">
		<?php if ($error): ?>
			<div class="message error"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>

		<?php if (empty($products)): ?>
			<p class="no-products">Tidak ada produk tersedia.</p>
		<?php else: ?>
			<?php 
            $delay = 0;
            foreach ($products as $row): 
                $delay++;
            ?>
				<div class="card" style="--delay: <?php echo $delay; ?>">
                    <div class="img-container">
					    <img src="images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                    </div>
                    <div class="content">
					    <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
					    <p class="price">$ <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <p class="desc">Produk berkualitas tinggi dan original</p>
					    <a href="index.php?beli=<?php echo htmlspecialchars($row['idproduk']); ?>">
                            <button class="btn">Beli Sekarang</button>
                        </a>
                    </div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<script>
		// Fungsi untuk menampilkan modal
		function showModal() {
			document.getElementById('myModal').style.display = 'block';
		}

		// Fungsi untuk menyembunyikan modal
		function closeModal() {
			document.getElementById('myModal').style.display = 'none';
		}

		// Tampilkan modal jika data berhasil disimpan
		window.onload = function(){
			<?php if ($show_modal): ?>
				showModal();
			<?php endif; ?>
		};
	</script>
</body>
</html>