# 50 CÂU HỎI BẢO VỆ ĐỒ ÁN
## Đề tài: Hệ thống tuyển sinh trực tuyến

---

## PHẦN 1 – TỔNG QUAN ĐỀ TÀI

**1. Em hãy giới thiệu ngắn gọn về đề tài của mình?**

Đề tài của em là **Hệ thống tuyển sinh trực tuyến**, được xây dựng bằng PHP theo mô hình MVC, sử dụng MySQL để lưu trữ dữ liệu và Bootstrap cho giao diện. Hệ thống gồm 2 phân hệ: dành cho **thí sinh** (đăng ký, nộp hồ sơ, xem kết quả) và dành cho **quản trị viên** (quản lý ngành học, đợt tuyển sinh, duyệt hồ sơ, xét tuyển). Website đã được triển khai lên hosting tại địa chỉ `vannhat996.gt.tc`.

---

**2. Mục tiêu chính của hệ thống là gì?**

Mục tiêu chính là **số hóa quy trình tuyển sinh**, thay thế việc nộp hồ sơ giấy truyền thống bằng hình thức trực tuyến. Cụ thể:
- Thí sinh có thể đăng ký, nộp hồ sơ và theo dõi kết quả mọi lúc mọi nơi
- Admin quản lý tập trung toàn bộ hồ sơ, ngành học, đợt tuyển sinh
- Tự động tính tổng điểm và hỗ trợ xét tuyển theo tổ hợp môn

---

**3. Hệ thống giải quyết vấn đề gì trong thực tế?**

Trong thực tế, tuyển sinh truyền thống gặp nhiều bất cập: thí sinh phải đến trực tiếp nộp hồ sơ, dễ thất lạc giấy tờ, khó theo dõi tiến trình xét duyệt, nhà trường tốn nhiều nhân lực xử lý hồ sơ thủ công. Hệ thống giải quyết những vấn đề đó bằng cách:
- Cho phép nộp hồ sơ và upload học bạ trực tuyến 24/7
- Admin duyệt hồ sơ, cập nhật trạng thái tức thì
- Thí sinh xem kết quả ngay trên hệ thống, không cần chờ thông báo

---

**4. Đối tượng sử dụng của hệ thống là ai?**

Có 2 đối tượng:
- **Thí sinh**: học sinh THPT muốn đăng ký xét tuyển đại học — thao tác đăng ký tài khoản, nộp hồ sơ, xem kết quả
- **Quản trị viên (Admin)**: cán bộ tuyển sinh của trường — quản lý ngành học, tổ hợp môn, đợt tuyển sinh, duyệt/từ chối hồ sơ, xem thống kê

---

## PHẦN 2 – PHP CƠ BẢN

**5. PHP chạy phía Client hay Server?**

PHP chạy phía **Server (máy chủ)**. Khi trình duyệt gửi yêu cầu, web server (Apache) thực thi file PHP, tạo ra HTML rồi trả về cho trình duyệt. Trình duyệt chỉ nhận HTML thuần, không thấy code PHP.

---

**6. Quy trình xử lý một trang PHP diễn ra như thế nào?**

1. Trình duyệt gửi request đến server (ví dụ: `vannhat996.gt.tc/views/user/profile.php`)
2. Apache nhận request, chuyển file `.php` cho PHP Engine xử lý
3. PHP thực thi code: kết nối DB, truy vấn dữ liệu, xử lý logic
4. PHP tạo ra HTML hoàn chỉnh
5. Apache trả HTML về trình duyệt
6. Trình duyệt render giao diện cho người dùng

---

**7. include và require khác nhau như thế nào?**

| | `include` | `require` |
|---|---|---|
| Khi file không tồn tại | Cảnh báo (Warning), script **tiếp tục chạy** | Lỗi nghiêm trọng (Fatal Error), script **dừng lại** |
| Dùng khi | File không bắt buộc | File bắt buộc phải có |

Trong dự án, em dùng `include '../../config/database.php'` vì đây là file kết nối DB — thực ra nên dùng `require` để dừng hẳn nếu không kết nối được.

---

**8. include_once và require_once dùng để làm gì?**

Giống `include`/`require` nhưng **chỉ nạp file một lần duy nhất**. Nếu file đó đã được nạp trước đó trong cùng script thì bỏ qua, tránh lỗi khai báo lại hàm/class. Ví dụ: `require_once 'config/database.php'` đảm bảo file kết nối DB chỉ chạy 1 lần dù được gọi nhiều chỗ.

---

**9. Biến Session là gì?**

Session là cơ chế lưu trữ thông tin người dùng **trên server** trong suốt phiên làm việc. Mỗi người dùng được cấp một **Session ID** duy nhất (lưu trong cookie trình duyệt), server dùng ID đó để nhận diện và lấy đúng dữ liệu. Trong dự án, em dùng `$_SESSION['user']` để lưu thông tin thí sinh sau khi đăng nhập, và `$_SESSION['admin']` cho quản trị viên.

---

**10. Cookie là gì?**

Cookie là dữ liệu nhỏ được lưu **trực tiếp trên trình duyệt** của người dùng. Server gửi cookie xuống, trình duyệt lưu lại và gửi kèm theo mỗi request tiếp theo. Cookie có thể tồn tại lâu dài (theo thời gian expiry được đặt), ngay cả khi đóng trình duyệt.

---

**11. Phân biệt Session và Cookie?**

| Tiêu chí | Session | Cookie |
|---|---|---|
| Lưu ở đâu | **Server** | **Trình duyệt (Client)** |
| Bảo mật | Cao hơn | Thấp hơn (người dùng có thể xem/sửa) |
| Dung lượng | Lớn hơn | Giới hạn ~4KB |
| Thời gian tồn tại | Hết khi đóng trình duyệt hoặc logout | Có thể kéo dài theo cài đặt |
| Dùng cho | Đăng nhập, giỏ hàng | Ghi nhớ tùy chọn, "nhớ mật khẩu" |

---

**12. Khi nào nên dùng Session thay vì Cookie?**

Dùng Session khi dữ liệu **nhạy cảm hoặc quan trọng** như: thông tin đăng nhập, quyền truy cập (admin/user), giỏ hàng. Dùng Cookie khi dữ liệu không nhạy cảm và cần lưu lâu dài như: ghi nhớ ngôn ngữ, theme giao diện, "nhớ tài khoản". Trong dự án em dùng Session cho toàn bộ xác thực vì bảo mật hơn.

---

**13. Hàm isset() dùng để làm gì?**

`isset()` kiểm tra xem **biến có tồn tại và không phải NULL** không, trả về `true` hoặc `false`. Trong dự án dùng rất nhiều:
```php
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Chưa đăng nhập → chuyển về login
    exit();
}
```
Cũng dùng để kiểm tra form submit: `if (isset($_POST['submit']))`.

---

**14. GET và POST khác nhau như thế nào?**

| Tiêu chí | GET | POST |
|---|---|---|
| Dữ liệu | Trên URL (`?id=1&name=abc`) | Trong body request, không hiện trên URL |
| Bảo mật | Thấp (ai cũng thấy) | Cao hơn |
| Giới hạn | ~2000 ký tự | Không giới hạn thực tế |
| Dùng cho | Tìm kiếm, lọc, lấy dữ liệu | Đăng nhập, đăng ký, nộp form, upload |
| Bookmark được | Có | Không |

---

**15. Khi nào sử dụng POST thay vì GET?**

Dùng POST khi:
- Gửi thông tin nhạy cảm (mật khẩu, CCCD)
- Thay đổi dữ liệu trên server (INSERT, UPDATE, DELETE)
- Upload file
- Dữ liệu lớn

Trong dự án, toàn bộ form đăng ký, đăng nhập, nộp hồ sơ đều dùng POST.

---

**16. Cách upload file trong PHP?**

Trong dự án, em upload học bạ như sau:
```php
// Form HTML cần có enctype="multipart/form-data"
// PHP xử lý:
$target_dir = __DIR__ . "/../../uploads/hocba/";
if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
$file_name = "hocba_" . $user['id'] . "_" . time() . "." . $file_ext;
$target_file = $target_dir . $file_name;
if (move_uploaded_file($_FILES['hocba']['tmp_name'], $target_file)) {
    // Lưu tên file vào DB
}
```
`$_FILES` chứa thông tin file upload, `move_uploaded_file()` di chuyển file từ thư mục tạm sang thư mục đích.

---

**17. Làm thế nào để kiểm tra dữ liệu đầu vào hợp lệ?**

Trong dự án em kiểm tra:
- **Phía Client (JavaScript/HTML5)**: `required`, `type="email"`, `min/max` trên input
- **Phía Server (PHP)**: kiểm tra `empty()`, định dạng email bằng `filter_var($email, FILTER_VALIDATE_EMAIL)`, kiểm tra định dạng CCCD, giới hạn loại file upload (`accept=".pdf,.jpg,.jpeg,.png"`), kiểm tra kích thước file

Nguyên tắc: **luôn validate ở server**, validate client chỉ để cải thiện UX.

---

**18. Nếu dữ liệu lên đến hàng triệu bản ghi thì xử lý thế nào?**

Với hệ thống hiện tại (free hosting), chưa tối ưu cho quy mô lớn. Nếu phát triển thực tế, em sẽ:
- Thêm **INDEX** vào các cột thường dùng WHERE/JOIN (thisinh_id, nganh_id, trangthai)
- Dùng **phân trang (LIMIT/OFFSET)** thay vì lấy toàn bộ dữ liệu
- Dùng **caching** (Redis/Memcached) cho các truy vấn thống kê
- Tối ưu câu query, tránh SELECT *
- Có thể nâng lên server riêng với cấu hình MySQL tốt hơn

---

## PHẦN 3 – MÔ HÌNH MVC

**19. MVC là gì?**

MVC (Model – View – Controller) là một **mô hình kiến trúc phần mềm** giúp tách biệt ứng dụng thành 3 thành phần độc lập, mỗi thành phần đảm nhiệm một vai trò riêng biệt, giúp code dễ quản lý, bảo trì và mở rộng.

---

**20. MVC gồm những thành phần nào?**

- **Model**: Xử lý dữ liệu, giao tiếp với database
- **View**: Hiển thị giao diện cho người dùng (HTML)
- **Controller**: Nhận request, điều phối giữa Model và View, xử lý logic nghiệp vụ

---

**21. Vai trò của Model?**

Model chịu trách nhiệm **toàn bộ liên quan đến dữ liệu**: kết nối CSDL, thực hiện các câu lệnh SELECT/INSERT/UPDATE/DELETE, trả kết quả cho Controller. Trong dự án, phần Model bao gồm `config/database.php` (kết nối DB) và `models/tuyensinh.sql` (cấu trúc CSDL).

---

**22. Vai trò của View?**

View chỉ có **một nhiệm vụ duy nhất: hiển thị dữ liệu**. View nhận dữ liệu từ Controller và render ra HTML. View không chứa logic xử lý hay truy vấn DB. Trong dự án, thư mục `views/admin/` và `views/user/` chứa các file giao diện như `dashboard.php`, `hoso.php`, `profile.php`.

---

**23. Vai trò của Controller?**

Controller là **trung tâm điều phối**: nhận request từ người dùng, gọi Model lấy dữ liệu, xử lý logic nghiệp vụ, rồi chuyển kết quả sang View hiển thị hoặc redirect. Trong dự án, `controllers/admin/ql_hoso.php` xử lý duyệt/từ chối hồ sơ; `controllers/user/dangky_xettuyen.php` xử lý đăng ký xét tuyển.

---

**24. Luồng xử lý trong MVC diễn ra như thế nào?**

```
Người dùng → gửi Request
    → Controller nhận request
    → Controller gọi Model (truy vấn DB)
    → Model trả dữ liệu về Controller
    → Controller truyền dữ liệu sang View
    → View render HTML trả về trình duyệt
    → Người dùng thấy kết quả
```

---

**25. Tại sao phải sử dụng MVC?**

Vì khi không dùng MVC, một file PHP chứa lẫn lộn HTML, logic xử lý và truy vấn DB — rất khó đọc, khó sửa, dễ lỗi. MVC tách biệt rõ ràng 3 tầng, giúp: nhiều người làm việc song song, dễ tìm lỗi, dễ thêm tính năng mới mà không ảnh hưởng phần khác.

---

**26. MVC có ưu điểm gì so với lập trình PHP thuần?**

| PHP thuần | MVC |
|---|---|
| Logic, HTML, DB lẫn lộn trong 1 file | Tách biệt rõ 3 tầng |
| Khó bảo trì khi dự án lớn | Dễ mở rộng, bảo trì |
| Khó làm việc nhóm | Phân công rõ ràng (ai làm View, ai làm Controller) |
| Khó tái sử dụng code | Tái sử dụng dễ dàng |
| Khó test | Có thể test từng thành phần độc lập |

---

**27. Trong dự án của em Controller nào quan trọng nhất?**

**`controllers/user/dangky_xettuyen.php`** là quan trọng nhất vì đây là nghiệp vụ cốt lõi của hệ thống. Controller này xử lý: xác thực thí sinh đã đăng nhập, kiểm tra điều kiện đăng ký (đủ điểm, đúng tổ hợp), tính tổng điểm xét tuyển, upload file học bạ, lưu hồ sơ vào DB với trạng thái "Chờ duyệt".

Phía admin, **`controllers/admin/ql_hoso.php`** cũng rất quan trọng vì xử lý việc duyệt/từ chối hồ sơ.

---

**28. Em đã xây dựng Router như thế nào?**

Dự án của em **chưa xây dựng Router độc lập**. Việc điều hướng được thực hiện trực tiếp qua:
- `header("Location: ...")` trong PHP để redirect sau khi xử lý
- `href="..."` trong HTML để điều hướng giữa các trang

Đây là cách đơn giản phù hợp với quy mô đồ án sinh viên. Nếu phát triển lên, em sẽ xây dựng một file `index.php` làm Front Controller, nhận toàn bộ request và phân phối đến đúng Controller dựa trên URL (ví dụ: `/admin/hoso` → `AdminHosoController`).

---

## PHẦN 4 – BẢO MẬT

**29. Tại sao phải mã hóa mật khẩu? Em sử dụng thuật toán nào?**

Nếu lưu mật khẩu dạng plaintext, khi DB bị tấn công, toàn bộ mật khẩu người dùng bị lộ. Em dùng **`password_hash()` với thuật toán `bcrypt` (PASSWORD_DEFAULT)** — đây là chuẩn bảo mật hiện đại. Mật khẩu được hash thành chuỗi không thể đảo ngược, khi kiểm tra dùng `password_verify()`.

---

**30. password_hash() và md5() khác nhau như thế nào?**

| | `md5()` | `password_hash()` |
|---|---|---|
| Thuật toán | MD5 (lỗi thời) | bcrypt (hiện đại) |
| Salt tự động | Không | **Có** (random mỗi lần) |
| Tốc độ | Rất nhanh (bất lợi bảo mật) | Chậm có chủ ý (chống brute-force) |
| An toàn | Không — đã bị crack | **Có** |
| Cùng input → cùng output | **Có** (nguy hiểm) | Không (khác nhau mỗi lần) |

---

**31. Vì sao không nên dùng MD5 để lưu mật khẩu?**

3 lý do chính:
1. **Rainbow table**: Tin tặc có sẵn bảng tra cứu MD5 của hàng tỷ mật khẩu phổ biến
2. **Không có salt**: Cùng mật khẩu → cùng hash → dễ so sánh và crack
3. **Quá nhanh**: GPU hiện đại có thể thử hàng tỷ MD5/giây → brute-force dễ dàng

bcrypt được thiết kế chậm có chủ ý và có salt ngẫu nhiên, khắc phục cả 3 vấn đề trên.

---

**32. Hệ thống dùng phương pháp bảo mật nào?**

- **Xác thực Session**: kiểm tra `$_SESSION['user']`/`$_SESSION['admin']` trước mọi trang cần đăng nhập
- **Phân quyền**: Admin và User có phiên riêng biệt, trang admin yêu cầu role admin
- **Mã hóa mật khẩu**: bcrypt qua `password_hash()`
- **Giới hạn loại file upload**: chỉ cho phép jpg, jpeg, png, pdf
- **HTTPS**: hosting InfinityFree hỗ trợ SSL

---

**33. Hệ thống của em chống SQL Injection không và bằng cách nào?**

Hiện tại hệ thống dùng **`mysqli_query()` trực tiếp**, chưa áp dụng Prepared Statements đầy đủ — đây là điểm cần cải thiện. Để an toàn hơn, em sẽ dùng:
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```
Prepared Statements tách biệt hoàn toàn dữ liệu và câu lệnh SQL, ngăn chặn SQL Injection triệt để.

---

## PHẦN 5 – GIAO DIỆN & FRONTEND

**34. Responsive Web Design là gì?**

Responsive Web Design là kỹ thuật thiết kế web **tự động điều chỉnh giao diện** phù hợp với mọi kích thước màn hình (máy tính, tablet, điện thoại) mà không cần tạo nhiều phiên bản website riêng. Sử dụng CSS media queries và lưới linh hoạt.

---

**35. Bootstrap có lợi ích gì?**

Bootstrap là framework CSS/JS giúp:
- **Responsive sẵn** với hệ thống grid 12 cột
- **Component UI có sẵn**: button, card, table, modal, navbar — tiết kiệm thời gian thiết kế
- **Tương thích trình duyệt**: đã được test trên nhiều browser
- Trong dự án, em dùng Bootstrap 5.3 cho toàn bộ giao diện admin và user

---

**36. Tại sao giao diện phải Responsive?**

Hiện nay hơn 60% người dùng truy cập web từ điện thoại. Nếu giao diện không responsive, thí sinh dùng điện thoại sẽ không sử dụng được, ảnh hưởng trực tiếp đến hiệu quả của hệ thống tuyển sinh.

---

**37. Em xử lý kiểm tra dữ liệu phía Client bằng gì?**

Em dùng **HTML5 validation** tích hợp sẵn:
- Thuộc tính `required` cho trường bắt buộc
- `type="email"` tự động kiểm tra định dạng email
- `accept=".pdf,.jpg,.jpeg,.png"` giới hạn loại file
- `min="0" max="10"` cho điểm số

Đây là lớp kiểm tra đầu tiên (UX), luôn kết hợp với validate phía server để đảm bảo an toàn.

---

**38. Apache có vai trò gì?**

Apache là **Web Server** — phần mềm nhận HTTP request từ trình duyệt, chuyển cho PHP Engine xử lý (với file .php), hoặc trả thẳng file tĩnh (html, css, js, ảnh). Apache đọc file `.htaccess` để cấu hình: phân quyền thư mục, redirect URL, charset mặc định. Trong dự án, hosting InfinityFree chạy Apache.

---

## PHẦN 6 – TRIỂN KHAI

**39. Quy trình triển khai website lên Hosting?**

Quy trình em đã thực hiện:
1. Đăng ký tài khoản hosting InfinityFree, tạo tên miền `vannhat996.gt.tc`
2. Vào MySQL Databases → tạo database `if0_41979554_db_tuyensinh`
3. Vào phpMyAdmin → Import file `tuyensinh.sql` để tạo cấu trúc bảng và dữ liệu mẫu
4. Cập nhật `config/database.php` với thông tin DB của hosting (host, user, pass, dbname)
5. Nén toàn bộ source code thành `.zip`
6. Vào File Manager → Upload & Unzip vào thư mục `htdocs/`
7. Truy cập domain kiểm tra hoạt động

---

**40. Domain là gì?**

Domain (tên miền) là **địa chỉ dễ nhớ** của website trên Internet, thay thế cho địa chỉ IP dạng số. Ví dụ: `vannhat996.gt.tc` thay vì `198.46.x.x`. Domain được quản lý bởi hệ thống DNS, khi người dùng nhập domain vào trình duyệt, DNS phân giải ra địa chỉ IP của server.

---

**41. Hosting là gì?**

Hosting là **dịch vụ cho thuê không gian lưu trữ** trên server để đặt file website, database và chạy ứng dụng web. Dự án dùng **InfinityFree** — hosting miễn phí với PHP, MySQL, Apache. Hosting cao cấp hơn sẽ có nhiều tài nguyên (RAM, CPU, băng thông) và ít giới hạn hơn.

---

**42. SSL là gì?**

SSL (Secure Sockets Layer) là **giao thức mã hóa** dữ liệu truyền giữa trình duyệt và server. Khi có SSL, địa chỉ web hiển thị `https://` và có biểu tượng ổ khóa. Dữ liệu nhạy cảm như mật khẩu, thông tin cá nhân được mã hóa, không bị nghe lén trên đường truyền.

---

**43. HTTPS khác HTTP như thế nào?**

| HTTP | HTTPS |
|---|---|
| Dữ liệu truyền dạng plaintext | Dữ liệu được **mã hóa** bằng SSL/TLS |
| Dễ bị nghe lén (Man-in-the-middle) | An toàn |
| Không có chứng chỉ | Có **SSL Certificate** xác thực |
| Trình duyệt cảnh báo "Not Secure" | Biểu tượng ổ khóa xanh |

Hosting InfinityFree hỗ trợ HTTPS miễn phí qua Let's Encrypt.

---

## PHẦN 7 – PHÁT TRIỂN & MỞ RỘNG

**44. Nếu 1000 người truy cập cùng lúc thì hệ thống có vấn đề gì?**

Với hosting miễn phí InfinityFree, hệ thống **sẽ gặp vấn đề nghiêm trọng**: giới hạn kết nối DB đồng thời, giới hạn CPU/RAM, có thể bị tạm khóa tài khoản. Để xử lý lưu lượng lớn, cần:
- Nâng lên **VPS hoặc Dedicated Server**
- Thêm **connection pooling** cho MySQL
- Dùng **Load Balancer** chia tải nhiều server
- Áp dụng **caching** giảm truy vấn DB
- Tối ưu query, thêm INDEX

---

**45. Nếu database bị mất dữ liệu thì xử lý ra sao?**

Hiện tại hệ thống chưa có cơ chế backup tự động — đây là điểm yếu cần cải thiện. Giải pháp:
- **Backup định kỳ**: Export SQL hàng ngày/tuần, lưu ở nhiều nơi khác nhau
- **Dùng hosting có tính năng auto-backup** (thường là hosting trả phí)
- Nếu mất dữ liệu: restore từ bản backup gần nhất qua phpMyAdmin Import

---

**46. Nếu muốn phát triển thành ứng dụng di động thì làm thế nào?**

Xây dựng thêm **REST API** cho backend PHP:
- Tạo các endpoint như `/api/login`, `/api/dangky`, `/api/ketqua` trả về JSON
- Ứng dụng mobile (React Native hoặc Flutter) gọi API để lấy và gửi dữ liệu
- Phần backend database và logic xử lý giữ nguyên, chỉ thêm tầng API

---

**47. Nếu được làm lại dự án em sẽ cải tiến những gì?**

1. **Bảo mật**: Áp dụng Prepared Statements toàn bộ để chống SQL Injection
2. **Router**: Xây dựng Front Controller thay vì điều hướng thủ công
3. **Model tách biệt**: Tạo class Model riêng (UserModel, HosoModel) thay vì query thẳng trong Controller
4. **Thông báo email**: Gửi email tự động khi hồ sơ được duyệt/từ chối
5. **Xét tuyển tự động**: Tự động so sánh điểm và chỉ tiêu để ra kết quả
6. **Backup DB**: Thêm chức năng export/import dữ liệu cho admin

---

**48. Em học được những gì từ dự án này?**

- Hiểu sâu về **mô hình MVC** và lý do tại sao cần tách biệt các thành phần
- Kinh nghiệm thực tế **triển khai web lên hosting**: cấu hình DB, xử lý charset, đường dẫn file
- Hiểu tầm quan trọng của **bảo mật**: mã hóa mật khẩu, xác thực phân quyền
- Kỹ năng **debug và xử lý lỗi** thực tế (charset tiếng Việt, đường dẫn tương đối/tuyệt đối)
- Quy trình làm việc với **Git, hosting, phpMyAdmin**

---

**49. Giải thích hoạt động của MVC trong dự án của em.**

Khi thí sinh nộp hồ sơ xét tuyển:
1. **Thí sinh** điền form tại `views/user/dangky_xettuyen.php` (View) → nhấn Nộp hồ sơ
2. Form POST đến **`controllers/user/dangky_xettuyen.php`** (Controller)
3. Controller kiểm tra session, validate dữ liệu, tính tổng điểm, gọi `mysqli_query()` INSERT vào bảng `hosoxettuyen` (Model/DB)
4. Controller redirect về trang profile
5. `views/user/profile.php` (View) query DB lấy danh sách hồ sơ và hiển thị kết quả

Admin duyệt hồ sơ:
1. Admin xem danh sách tại `views/admin/hoso.php` (View) → nhấn Duyệt
2. POST đến `controllers/admin/ql_hoso.php` (Controller)
3. Controller UPDATE trạng thái trong DB (Model)
4. Redirect lại View hiển thị danh sách đã cập nhật

---

**50. PDO (PHP Data Objects) là gì và còn cách nào khác?**

PDO là **lớp trừu tượng hóa kết nối database** trong PHP, hỗ trợ nhiều hệ quản trị DB (MySQL, PostgreSQL, SQLite...) với cùng một cú pháp. PDO hỗ trợ Prepared Statements tốt hơn, bảo mật hơn.

**3 cách kết nối DB trong PHP:**

| Cách | Đặc điểm |
|---|---|
| `mysqli_*` (procedural) | Dự án em đang dùng, chỉ MySQL, đơn giản |
| `mysqli` (OOP) | Hướng đối tượng, chỉ MySQL |
| **PDO** | Hỗ trợ nhiều DB, Prepared Statements tốt nhất, **khuyến nghị** |

Ví dụ PDO:
```php
$pdo = new PDO("mysql:host=localhost;dbname=tuyensinh;charset=utf8mb4", $user, $pass);
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

---
*Tài liệu ôn tập bảo vệ đồ án – Hệ thống tuyển sinh trực tuyến*
