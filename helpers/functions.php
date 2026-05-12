<?php
/**
 * Helper Functions
 * Food Safety & Premises Grading Management System
 */

/**
 * Redirect to a specific URL
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Generate a random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Flash message system
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd M Y h:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check user role
 */
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

/**
 * Check if user has minimum role level
 */
function hasMinimumRole($roleLevel) {
    if (!isLoggedIn()) return false;
    $roles = array_flip(ROLES);
    return ROLES[$_SESSION['user_role']] <= $roleLevel;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Upload file with validation
 */
function uploadFile($file, $directory, $allowedTypes = ALLOWED_IMAGE_TYPES, $maxSize = MAX_FILE_SIZE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds limit (5MB max)'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateRandomString(16) . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $directory . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate PDF using TCPDF
 */
function generatePDF($html, $filename = 'document.pdf') {
    require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(SITE_NAME);
    $pdf->SetTitle($filename);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');

    return $pdf->Output($filename, 'S');
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
}

/**
 * Generate QR Code
 */
function generateQRCode($data, $size = 200) {
    require_once __DIR__ . '/../vendor/phpqrcode/qrlib.php';

    $tempDir = UPLOAD_PATH . 'qrcodes/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $filename = generateRandomString(16) . '.png';
    $filePath = $tempDir . $filename;

    QRcode::png($data, $filePath, QR_ECLEVEL_H, $size / 25);

    return $filename;
}

/**
 * Calculate grade based on inspection scores
 */
function calculateGrade($scores) {
    $weights = GRADE_WEIGHTS;
    $totalScore = 0;
    $totalWeight = 0;

    foreach ($scores as $criteria => $score) {
        if (isset($weights[$criteria])) {
            $totalScore += ($score * $weights[$criteria]);
            $totalWeight += $weights[$criteria];
        }
    }

    if ($totalWeight === 0) return 'D';

    $percentage = ($totalScore / ($totalWeight * 5)) * 100; // Assuming max score per criteria is 5

    $thresholds = GRADE_THRESHOLDS;
    arsort($thresholds);

    foreach ($thresholds as $grade => $minScore) {
        if ($percentage >= $minScore) {
            return $grade;
        }
    }

    return 'D';
}

/**
 * Get grade color class
 */
function getGradeColor($grade) {
    $colors = [
        'A' => 'success',
        'B' => 'info',
        'C' => 'warning',
        'D' => 'danger'
    ];
    return $colors[$grade] ?? 'secondary';
}

/**
 * Get grade badge HTML
 */
function getGradeBadge($grade) {
    $color = getGradeColor($grade);
    return "<span class='badge bg-{$color} fs-6'>Grade {$grade}</span>";
}

/**
 * Pagination helper
 */
function paginate($total, $perPage, $currentPage) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'perPage' => $perPage,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'offset' => $offset
    ];
}

/**
 * Export to Excel
 */
function exportToExcel($data, $headers, $filename = 'export.xlsx') {
    require_once __DIR__ . '/../vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';

    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set headers
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Set data
    $row = 2;
    foreach ($data as $item) {
        $col = 'A';
        foreach ($item as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
