<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

// 1. Kiểm tra quyền admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/login.php');
    exit;
}

// 2. Xử lý xóa thí sinh (nếu cần)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Khi xóa thí sinh, hồ sơ liên quan sẽ tự động xóa nhờ ON DELETE CASCADE trong schema
    mysqli_query($conn, "DELETE FROM thisinh WHERE id = $id");
    header('Location: ../../views/admin/thisinh.php?msg=deleted');
}

// 3. Truy vấn danh sách thí sinh kèm tên đăng nhập từ bảng users
$sql = "SELECT ts.*, u.username 
        FROM thisinh ts 
        LEFT JOIN users u ON ts.user_id = u.id 
        ORDER BY ts.id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thí sinh | Hệ thống Tuyển sinh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .main-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #f1f5f9;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark"><i class="fas fa-users-cog me-2"></i>Quản lý Thí sinh</h2>
                <p class="text-muted small mb-0">Danh sách các thí sinh đã đăng ký tài khoản trên hệ thống</p>
            </div>
            <a href="../../views/admin/dashboard.php" class="btn btn-outline-primary shadow-sm"><i
                    class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Đã xóa thông tin thí sinh thành công!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card main-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">STT</th>
                                <th>Họ và Tên</th>
                                <th>Tài khoản</th>
                                <th>Liên hệ (SĐT/Email)</th>
                                <th>Địa chỉ</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted">
                                            <?php echo $stt++; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-2">
                                                    <?php echo mb_substr($row['hoten'], 0, 1); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">
                                                        <?php echo $row['hoten']; ?>
                                                    </div>
                                                    <small class="text-muted">NS:
                                                        <?php echo date('d/m/Y', strtotime($row['ngaysinh'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-primary border">
                                                <?php echo $row['username'] ?? 'N/A'; ?>
                                            </span></td>
                                        <td>
                                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo $row['sdt']; ?>
                                            </div>
                                            <div class="small"><i class="fas fa-envelope me-1 text-muted"></i>
                                                <?php echo $row['email']; ?>
                                            </div>
                                        </td>
                                        <td class="small text-truncate" style="max-width: 200px;">
                                            <?php echo $row['diachi']; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="view_thisinh.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light"
                                                title="Xem chi tiết">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            <a href="ql_thisinh.php?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-light"
                                                onclick="return confirm('Cảnh báo: Xóa thí sinh sẽ xóa toàn bộ hồ sơ liên quan. Tiếp tục?')"
                                                title="Xóa">
                                                <i class="fas fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Chưa có thí sinh nào đăng ký.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>