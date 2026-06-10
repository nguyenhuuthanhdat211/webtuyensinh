<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/login.php');
    exit;
}

// Lấy danh sách ngành học (Sửa lỗi thiếu cột ma_nganh)
$sql = "SELECT id, ma_nganh, tennganh, chitieu FROM nganhhoc ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Ngành học | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background: white;
        }

        .table thead {
            background-color: #4e73df;
            color: white;
        }

        .badge-chitieu {
            background-color: #e74a3b;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
        }

        .btn-add {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            border: none;
        }

        .action-btns .btn {
            padding: 5px 10px;
            margin: 0 2px;
        }

        .sidebar-brand {
            font-weight: 800;
            color: #4e73df;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="sidebar-brand m-0"><i class="fas fa-university me-2"></i>Hệ thống Tuyển sinh</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mt-2">
                        <li class="breadcrumb-item"><a href="../../views/admin/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Quản lý ngành học</li>
                    </ol>
                </nav>
            </div>
            <a href="add_nganh.php" class="btn btn-success btn-add px-4 py-2 shadow">
                <i class="fas fa-plus-circle me-2"></i>Thêm ngành mới
            </a>
        </div>

        <div class="card main-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary">Danh mục các ngành đào tạo</h5>
                <div class="input-group w-25">
                    <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm ngành...">
                    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">STT</th>
                                <th>Mã Ngành</th>
                                <th>Tên Ngành Học</th>
                                <th class="text-center">Chỉ Tiêu</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            if (mysqli_num_rows($result) > 0):
                                while ($row = mysqli_fetch_assoc($result)):
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $stt++; ?></td>
                                        <td><span
                                                class="badge bg-light text-dark border fw-bold"><?php echo $row['ma_nganh']; ?></span>
                                        </td>
                                        <td class="fw-bold text-dark"><?php echo $row['tennganh']; ?></td>
                                        <td class="text-center">
                                            <span class="badge-chitieu"><?php echo $row['chitieu']; ?></span>
                                        </td>
                                        <td class="text-center action-btns">
                                            <a href="edit_nganh.php?id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="ql_nganh.php?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa ngành này?')" title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Chưa có ngành học nào được tạo.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <small class="text-muted">Hiển thị <?php echo mysqli_num_rows($result); ?> ngành học trên hệ
                    thống.</small>
            </div>
        </div>
    </div>

</body>

</html>