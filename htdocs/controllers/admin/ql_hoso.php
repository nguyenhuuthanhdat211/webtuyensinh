<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
include '../../config/database.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../../views/admin/login.php');
    exit;
}

// Xử lý cập nhật trạng thái hồ sơ (Duyệt/Từ chối)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $status = ($action == 'approve') ? 'Da duyet' : 'Tu choi';

    $update_sql = "UPDATE hosoxettuyen SET trangthai = '$status' WHERE id = $id";
    mysqli_query($conn, $update_sql);
    header('Location: ../../views/admin/hoso.php');
}

// Truy vấn lấy danh sách hồ sơ (Join 3 bảng)
$sql = "SELECT hs.id, ts.hoten, n.tennganh, hs.diem, hs.trangthai, hs.created_at 
        FROM hosoxettuyen hs
        JOIN thisinh ts ON hs.thisinh_id = ts.id
        JOIN nganhhoc n ON hs.nganh_id = n.id
        ORDER BY hs.created_at DESC";
$result = mysqli_query($conn, $sql);
// Truy vấn kết hợp thông tin thí sinh và ngành học
$sql = "SELECT hs.id, ts.hoten, n.tennganh, hs.diem, hs.trangthai 
        FROM hosoxettuyen hs
        INNER JOIN thisinh ts ON hs.thisinh_id = ts.id
        INNER JOIN nganhhoc n ON hs.nganh_id = n.id
        ORDER BY hs.id DESC";
$result = mysqli_query($conn, $sql);

$stt = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $stt++ . "</td>";
        echo "<td>" . $row['hoten'] . "</td>";
        echo "<td>" . $row['tennganh'] . "</td>";
        echo "<td><b class='text-danger'>" . $row['diem'] . "</b></td>";
        echo "<td>" . $row['trangthai'] . "</td>";
        echo "<td><button class='btn btn-sm btn-info'>Duyệt</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>Hiện chưa có hồ sơ nào được gửi.</td></tr>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Hồ sơ Xét tuyển</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .bg-pending {
            background-color: #f6e05e;
            color: #856404;
        }

        .bg-approved {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .bg-rejected {
            background-color: #fed7d7;
            color: #822727;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold"><i class="fas fa-file-invoice me-2"></i>Quản lý Hồ sơ</h2>
            <a href="../../views/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">Quay lại Dashboard</a>
        </div>

        <div class="card main-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">STT</th>
                                <th>Thí sinh</th>
                                <th>Ngành đăng ký</th>
                                <th class="text-center">Điểm</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $status_class = '';
                                if ($row['trangthai'] == 'Cho duyet')
                                    $status_class = 'bg-pending';
                                elseif ($row['trangthai'] == 'Da duyet')
                                    $status_class = 'bg-approved';
                                else
                                    $status_class = 'bg-rejected';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?php echo $stt++; ?></td>
                                    <td><span class="fw-bold"><?php echo $row['hoten']; ?></span></td>
                                    <td><?php echo $row['tennganh']; ?></td>
                                    <td class="text-center fw-bold text-primary">
                                        <?php echo number_format($row['diem'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $row['trangthai']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['trangthai'] == 'Cho duyet'): ?>
                                            <a href="ql_hoso.php?action=approve&id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-success" title="Duyệt hồ sơ">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="ql_hoso.php?action=reject&id=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-danger" title="Từ chối"
                                                onclick="return confirm('Xác nhận từ chối hồ sơ này?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Đã xử lý</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>