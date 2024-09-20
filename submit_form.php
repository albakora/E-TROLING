<?php
// Sertakan file PHPMailer dari folder src
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

const UPLOAD_DIR = 'uploads/';
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function handleFileUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File tidak diunggah atau ada kesalahan saat pengunggahan.');
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_EXTENSIONS)) {
        throw new Exception('Hanya file dengan ekstensi ' . implode(', ', ALLOWED_EXTENSIONS) . ' yang diizinkan.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Ukuran file tidak boleh lebih dari ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
    }

    $dest_path = UPLOAD_DIR . md5(time() . $file['name']) . '.' . $fileExtension;
    if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
        throw new Exception('Ada kesalahan dalam mengupload file.');
    }

    return $dest_path;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
            throw new Exception('Gagal membuat direktori upload.');
        }

        $name = cleanInput($_POST['name'] ?? '');
        $date = cleanInput($_POST['date'] ?? '');
        $rupam = cleanInput($_POST['rupam'] ?? '');
        $shift = cleanInput($_POST['shift'] ?? '');
        $blok = cleanInput($_POST['blok'] ?? '');
        $situasi = cleanInput($_POST['situasi'] ?? '');

        $dest_path = handleFileUpload($_FILES['file']);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lapasmeraukeaksi@gmail.com'; // Ganti dengan alamat email Google Anda
        $mail->Password = 'eubxddtlizlecfzt'; // Ganti dengan Password Aplikasi Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('lapasmeraukeaksi@gmail.com', 'E-TROLING');
        $mail->addAddress('lapasmeraukeaksi@gmail.com');

        $mail->addAttachment($dest_path, basename($dest_path));

        $mail->isHTML(true);
        $mail->Subject = 'Form Pengisian Data Diri Baru';
        $mail->Body = "
        <html>
        <head>
            <title>Data Form</title>
            <style>
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; }
                th { background-color: #007bff; color: white; text-align: left; }
                tr:nth-child(even) { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <p>Data pengisian form baru:</p>
            <table>
                <tr><th>1. Nama Petugas</th><td>$name</td></tr>
                <tr><th>2. Rupam</th><td>$rupam</td></tr>
                <tr><th>3. Waktu</th><td>$date</td></tr>
                <tr><th>4. Shift</th><td>$shift</td></tr>
                <tr><th>5. Blok</th><td>$blok</td></tr>
                <tr><th>6. Situasi</th><td>$situasi</td></tr>
            </table>
        </body>
        </html>";

        $mail->send();
        echo 'DATA TELAH DITERIMA. Selamat Bertugas Waspada Jangan-Jangan !!!';
    } catch (Exception $e) {
        error_log("Error in form submission: " . $e->getMessage());
        echo "PENGIRIMAN GAGAL. Silakan coba lagi nanti atau hubungi administrator.";
    }
}
?>
