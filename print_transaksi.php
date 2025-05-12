<?php
// Mulai sesi
session_start();


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Sertakan file FPDF dan koneksi
require('fpdf/fpdf.php'); // Sesuaikan path ke file FPDF
require_once 'koneksi.php';


// Kelas TransactionPrinter untuk mengelola pembuatan PDF transaksi
class TransactionPrinter {
    // Properti privat untuk instance Database, FPDF, dan data transaksi
    private $db;
    private $pdf;
    private $transaction;


    // Konstruktor untuk inisialisasi koneksi dan FPDF
    public function __construct() {
        try {
            $this->db = new Database();
            $this->pdf = new FPDF();
        } catch (Exception $e) {
            throw new Exception("Gagal inisialisasi: " . $e->getMessage());
        }
    }


    // Metode untuk mengambil data transaksi berdasarkan idtransaksi
    public function fetchTransaction($idtransaksi) {
        try {
            $conn = $this->db->getConnection();
            $sql = "
                SELECT h.idtransaksi, h.tgltransaksi, h.nama, h.alamat, h.kodepos, h.nohp, 
                       d.idproduk, d.harga, d.quantity, p.nama_produk, p.gambar 
                FROM hkeranjang h 
                JOIN dkeranjang d ON h.idtransaksi = d.idtransaksi 
                JOIN produk p ON d.idproduk = p.idproduk 
                WHERE h.idtransaksi = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $idtransaksi);
            $stmt->execute();
            $result = $stmt->get_result();


            $this->transaction = [
                'items' => [],
                'total' => 0
            ];
            while ($row = $result->fetch_assoc()) {
                $this->transaction['items'][] = $row;
                $this->transaction['total'] += $row['harga'] * $row['quantity'];
            }


            // Periksa apakah transaksi ditemukan
            if (empty($this->transaction['items'])) {
                throw new Exception("Transaksi dengan ID $idtransaksi tidak ditemukan.");
            }


            $stmt->close();
            return $this->transaction;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data transaksi: " . $e->getMessage());
        }
    }


    // Metode untuk menghasilkan dokumen PDF
    public function generatePDF($idtransaksi) {
        try {
            $this->pdf->AddPage();
            $this->pdf->SetFont('Arial', 'B', 16);


            // Judul
            $this->pdf->Cell(0, 10, "Transaksi: $idtransaksi", 0, 1, 'C');
            $this->pdf->Ln(10);


            // Informasi Transaksi
            $this->pdf->SetFont('Arial', '', 12);
            $this->pdf->Cell(0, 10, "Tanggal: " . $this->transaction['items'][0]['tgltransaksi'], 0, 1);
            $this->pdf->Cell(0, 10, "Nama: " . $this->transaction['items'][0]['nama'], 0, 1);
            $this->pdf->Cell(0, 10, "Alamat: " . $this->transaction['items'][0]['alamat'], 0, 1);
            $this->pdf->Cell(0, 10, "Kode Pos: " . $this->transaction['items'][0]['kodepos'], 0, 1);
            $this->pdf->Cell(0, 10, "No. HP: " . $this->transaction['items'][0]['nohp'], 0, 1);
            $this->pdf->Ln(10);


            // Header Tabel
            $this->pdf->SetFont('Arial', 'B', 12);
            $this->pdf->Cell(10, 10, 'No', 1, 0, 'C');
            $this->pdf->Cell(80, 10, 'Nama Produk', 1, 0, 'C');
            $this->pdf->Cell(30, 10, 'Harga', 1, 0, 'C');
            $this->pdf->Cell(20, 10, 'Qty', 1, 0, 'C');
            $this->pdf->Cell(40, 10, 'Total', 1, 1, 'C');


            // Isi Tabel
            $this->pdf->SetFont('Arial', '', 12);
            $no = 1;
            foreach ($this->transaction['items'] as $item) {
                $total = $item['harga'] * $item['quantity'];
                $this->pdf->Cell(10, 10, $no++, 1, 0, 'C');
                $this->pdf->Cell(80, 10, $item['nama_produk'], 1, 0, 'L');
                $this->pdf->Cell(30, 10, '$ ' . number_format($item['harga'], 0, ',', '.'), 1, 0, 'R');
                $this->pdf->Cell(20, 10, $item['quantity'], 1, 0, 'C');
                $this->pdf->Cell(40, 10, '$ ' . number_format($total, 0, ',', '.'), 1, 1, 'R');
            }


            // Grand Total
            $this->pdf->SetFont('Arial', 'B', 12);
            $this->pdf->Cell(140, 10, 'Grand Total', 1, 0, 'R');
            $this->pdf->Cell(40, 10, '$ ' . number_format($this->transaction['total'], 0, ',', '.'), 1, 1, 'R');


            // Output PDF
            $this->pdf->Output("transaksi_$idtransaksi.pdf", 'I');
        } catch (Exception $e) {
            throw new Exception("Gagal menghasilkan PDF: " . $e->getMessage());
        }
    }


    // Metode untuk menutup koneksi
    public function closeConnection() {
        $this->db->closeConnection();
    }
}


// Validasi idtransaksi
if (!isset($_GET['idtransaksi']) || empty($_GET['idtransaksi'])) {
    die("Error: ID transaksi tidak valid.");
}


$idtransaksi = $_GET['idtransaksi'];


try {
    $printer = new TransactionPrinter();
    $printer->fetchTransaction($idtransaksi);
    $printer->generatePDF($idtransaksi);
    $printer->closeConnection();
} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>
