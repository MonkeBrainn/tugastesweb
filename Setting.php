<?php
// Mulai sesi
session_start();
// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
header("Location: login.php");
exit();
}
// Sertakan koneksi
require_once 'koneksi.php';
// Kelas UserSettings untuk mengelola pengaturan profil pengguna
class UserSettings {
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
// Metode untuk mengambil informasi pengguna
public function getUserInfo($user_id) {
try {
$conn = $this->db->getConnection();
$sql = "SELECT nama_user, email_user, foto_user FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
$row = $result->fetch_assoc();
$stmt->close();
return [
'nama_user' => $row['nama_user'],
'email_user' => $row['email_user'],
'foto_user' => $row['foto_user'] ?: 'Uploads/user.jpg'
];
} else {
$stmt->close();
return [

'nama_user' => 'User',
'email_user' => '',
'foto_user' => 'Uploads/user.jpg'
];
}
} catch (Exception $e) {
throw new Exception("Gagal mengambil data pengguna: " . $e->getMessage());
}
}
// Metode untuk memperbarui profil pengguna
public function updateProfile($user_id, $data, $file) {
try {
$conn = $this->db->getConnection();
$nama_user = htmlspecialchars(trim($data['nama_user']));
$email_user = htmlspecialchars(trim($data['email_user']));
$password_user = isset($data['password_user']) ? trim($data['password_user']) : '';
$foto_user = $file['foto_user'];
// Validasi email unik
$sql_check_email = "SELECT id FROM user WHERE email_user = ? AND id != ?";
$stmt_check_email = $conn->prepare($sql_check_email);
$stmt_check_email->bind_param("ss", $email_user, $user_id);
$stmt_check_email->execute();
$result_check_email = $stmt_check_email->get_result();
if ($result_check_email->num_rows > 0) {
$stmt_check_email->close();
throw new Exception("Email sudah digunakan oleh pengguna lain.");
}
$stmt_check_email->close();
// Update nama dan email
$sql_update = "UPDATE user SET nama_user = ?, email_user = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("sss", $nama_user, $email_user, $user_id);
$stmt_update->execute();
$stmt_update->close();
// Update password jika diisi
if (!empty($password_user)) {
$hashed_password = password_hash($password_user, PASSWORD_DEFAULT);
$sql_update_password = "UPDATE user SET password_user = ? WHERE id = ?";
$stmt_update_password = $conn->prepare($sql_update_password);
$stmt_update_password->bind_param("ss", $hashed_password, $user_id);
$stmt_update_password->execute();
$stmt_update_password->close();
}
// Update foto profil jika diunggah
$new_file_path = null;

if ($foto_user['error'] === UPLOAD_ERR_OK) {
$target_dir = "Uploads/";
$imageFileType = strtolower(pathinfo($foto_user["name"], PATHINFO_EXTENSION));
// Validasi file gambar
$check = getimagesize($foto_user["tmp_name"]);
if ($check === false || !in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
throw new Exception("File yang diunggah bukan gambar atau format tidak didukung.");
}
// Hapus foto lama jika ada
$current_foto = $this->getUserInfo($user_id)['foto_user'];
if ($current_foto && file_exists($current_foto) && $current_foto !== 'Uploads/user.jpg') {
unlink($current_foto);
}
// Pindahkan file baru ke folder uploads
$new_file_name = uniqid() . "." . $imageFileType;
$new_file_path = $target_dir . $new_file_name;
if (!move_uploaded_file($foto_user["tmp_name"], $new_file_path)) {
throw new Exception("Gagal mengunggah foto profil.");
}
// Update path foto di database
$sql_update_foto = "UPDATE user SET foto_user = ? WHERE id = ?";
$stmt_update_foto = $conn->prepare($sql_update_foto);
$stmt_update_foto->bind_param("ss", $new_file_path, $user_id);
$stmt_update_foto->execute();
$stmt_update_foto->close();
}
// Kembalikan data yang diperbarui untuk memperbarui sesi
return [
'success' => true,
'message' => 'Profil berhasil diperbarui.',
'nama_user' => $nama_user,
'email_user' => $email_user,
'new_foto_path' => $new_file_path
];
} catch (Exception $e) {
return [
'success' => false,
'message' => $e->getMessage()
];
}
}
// Metode untuk menutup koneksi
public function closeConnection() {
$this->db->closeConnection();

}
}
// Inisialisasi variabel
$user_id = $_SESSION['user_id'];
$message = '';
$user_info = [
'nama_user' => 'User',
'email_user' => '',
'foto_user' => 'Uploads/user.jpg'
];
try {
$user_settings = new UserSettings();
// Ambil informasi pengguna
$user_info = $user_settings->getUserInfo($user_id);
// Proses pembaruan profil jika ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$result = $user_settings->updateProfile($user_id, $_POST, $_FILES);
$message = $result['message'];
if ($result['success']) {
// Perbarui data sesi
$_SESSION['nama_user'] = $result['nama_user'];
$_SESSION['email_user'] = $result['email_user'];
if (isset($result['new_foto_path']) && $result['new_foto_path']) {
$_SESSION['foto_user'] = $result['new_foto_path'];
$user_info['foto_user'] = $result['new_foto_path'];
}
// Perbarui $user_info untuk form
$user_info['nama_user'] = $result['nama_user'];
$user_info['email_user'] = $result['email_user'];
}
}
$user_settings->closeConnection();
} catch (Exception $e) {
$message = "Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<!-- Metadata -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Profil - Toko Online</title>
<!-- CSS eksternal -->

<link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Sertakan header -->
<?php include 'header.php'; ?>
<!-- Sertakan navbar -->
<?php include 'navbar.php'; ?>
<!-- Kontainer pengaturan -->
<div class="container settings-container">
<h2>Pengaturan Profil</h2>
<!-- Pesan sukses atau kesalahan -->
<?php if ($message): ?>
<div class="message <?= strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
<?= htmlspecialchars($message); ?>
</div>
<?php endif; ?>
<!-- Form pengaturan -->
<form action="setting.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label for="nama_user">Nama:</label>
<input type="text" id="nama_user" name="nama_user" value="<?=
htmlspecialchars($user_info['nama_user']); ?>" required>
</div>
<div class="form-group">
<label for="email_user">Email:</label>
<input type="email" id="email_user" name="email_user" value="<?=
htmlspecialchars($user_info['email_user']); ?>" required>
</div>
<div class="form-group">
<label for="password_user">Password Baru (biarkan kosong jika tidak ingin
mengubah):</label>
<input type="password" id="password_user" name="password_user">
</div>
<div class="form-group">
<label for="foto_user">Foto Profil:</label>
<input type="file" id="foto_user" name="foto_user" accept="image/*">
<?php if ($user_info['foto_user']): ?>
<img src="<?= htmlspecialchars($user_info['foto_user']); ?>" alt="Foto Profil Saat Ini"
class="profile-photo">
<?php endif; ?>
</div>
<button type="submit" class="btn">Simpan Perubahan</button>
</form>
</div>
</body>
</html>