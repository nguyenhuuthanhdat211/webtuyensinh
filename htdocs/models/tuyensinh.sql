-- ===================================================
-- HỆ THỐNG TUYỂN SINH - Database Schema (Updated)
-- ===================================================

CREATE DATABASE IF NOT EXISTS tuyensinh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tuyensinh;

-- 1. Bảng tài khoản (Mở rộng trạng thái)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(255),
    email VARCHAR(100),
    role ENUM('admin','user') DEFAULT 'user',
    status ENUM('Active', 'Locked') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Bảng đợt tuyển sinh
CREATE TABLE IF NOT EXISTS dot_tuyensinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_dot VARCHAR(255) NOT NULL,
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    trang_thai ENUM('Đang mở', 'Đã đóng') DEFAULT 'Đang mở',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Bảng tổ hợp xét tuyển (A00, A01, D01, ...)
CREATE TABLE IF NOT EXISTS tohop_xettuyen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_tohop VARCHAR(10) NOT NULL UNIQUE,
    ten_tohop VARCHAR(255) NOT NULL,
    mo_ta VARCHAR(255)
);

-- 4. Bảng ngành học (Mở rộng chỉ tiêu)
CREATE TABLE IF NOT EXISTS nganhhoc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tennganh VARCHAR(255) NOT NULL,
    ma_nganh VARCHAR(20) UNIQUE,
    chitieu INT DEFAULT 0,
    mo_ta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Bảng liên kết Ngành - Tổ hợp
CREATE TABLE IF NOT EXISTS nganh_tohop (
    nganh_id INT,
    tohop_id INT,
    PRIMARY KEY (nganh_id, tohop_id),
    FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
    FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE CASCADE
);

-- 6. Bảng thí sinh (Chi tiết thông tin)
CREATE TABLE IF NOT EXISTS thisinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    hoten VARCHAR(255) NOT NULL,
    ngaysinh DATE,
    gioitinh VARCHAR(10),
    diachi TEXT,
    sdt VARCHAR(20),
    email VARCHAR(100),
    cccd VARCHAR(20) UNIQUE,
    que_quan VARCHAR(255),
    truong_thpt VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 7. Bảng hồ sơ xét tuyển (Mở rộng đợt và điểm)
CREATE TABLE IF NOT EXISTS hosoxettuyen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thisinh_id INT NOT NULL,
    nganh_id INT NOT NULL,
    dot_id INT,
    tohop_id INT,
    diem_mon1 FLOAT DEFAULT 0,
    diem_mon2 FLOAT DEFAULT 0,
    diem_mon3 FLOAT DEFAULT 0,
    diem_tong FLOAT DEFAULT 0,
    trangthai ENUM('Chờ duyệt','Đã duyệt','Từ chối','Trúng tuyển','Không trúng tuyển') DEFAULT 'Chờ duyệt',
    file_hocba VARCHAR(255),
    ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thisinh_id) REFERENCES thisinh(id) ON DELETE CASCADE,
    FOREIGN KEY (nganh_id) REFERENCES nganhhoc(id) ON DELETE CASCADE,
    FOREIGN KEY (dot_id) REFERENCES dot_tuyensinh(id) ON DELETE SET NULL,
    FOREIGN KEY (tohop_id) REFERENCES tohop_xettuyen(id) ON DELETE SET NULL
);

-- ===================================================
-- DỮ LIỆU MẪU
-- ===================================================

-- Tài khoản admin (password: admin123)
-- Lưu ý: Sử dụng mật khẩu đã hash bằng password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, ho_ten, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin@tuyensinh.edu.vn', 'admin')
ON DUPLICATE KEY UPDATE role='admin';

-- Đợt tuyển sinh
INSERT INTO dot_tuyensinh (ten_dot, ngay_bat_dau, ngay_ket_thuc, trang_thai) VALUES
('Tuyển sinh Đại học Đợt 1 - 2024', '2024-05-01', '2024-08-30', 'Đang mở');

-- Tổ hợp xét tuyển
INSERT INTO tohop_xettuyen (ma_tohop, ten_tohop, mo_ta) VALUES
('A00', 'Toán, Lý, Hóa', 'Khối tự nhiên truyền thống'),
('A01', 'Toán, Lý, Anh', 'Khối tự nhiên có tiếng Anh'),
('D01', 'Toán, Văn, Anh', 'Khối xã hội có tiếng Anh'),
('B00', 'Toán, Hóa, Sinh', 'Khối ngành y dược');

-- Ngành học mẫu
INSERT INTO nganhhoc (tennganh, ma_nganh, chitieu, mo_ta) VALUES
('Công nghệ thông tin', 'CNTT', 200, 'Đào tạo kỹ sư CNTT, lập trình, phân tích hệ thống'),
('Quản trị kinh doanh', 'QTKD', 150, 'Đào tạo cử nhân kinh tế, quản lý doanh nghiệp'),
('Kế toán', 'KT', 120, 'Đào tạo kế toán, kiểm toán, tài chính doanh nghiệp'),
('Điện - Điện tử', 'DDT', 100, 'Đào tạo kỹ sư điện, điện tử viễn thông'),
('Kiến trúc', 'KT2', 80, 'Đào tạo kiến trúc sư thiết kế công trình');

-- Liên kết Ngành - Tổ hợp (Ví dụ CNTT xét A00, A01, D01)
INSERT INTO nganh_tohop (nganh_id, tohop_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 3), (2, 4),
(3, 3),
(4, 1), (4, 2);