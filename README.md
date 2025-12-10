# PHP Cuối Kỳ: Smart Notes

Dự án quản lý ghi chú thông minh, hỗ trợ tạo dự án, thêm ghi chú, quản lý thành viên với quyền hạn khác nhau. Phù hợp cho sinh viên học PHP cơ bản.

## Cài đặt
1. Tạo database: Chạy file `smart_notes.sql` để tạo bảng và dữ liệu mẫu (admin: email admin@gmail.com, pass admin).
2. Cấu hình: Chỉnh `db.php` nếu cần (host, dbname, user, pass).
3. Upload folder: Copy toàn bộ folder vào XAMPP/htdocs hoặc server.
4. Truy cập: Mở browser, đi đến `http://localhost/php-cuoi-ki/index.php`.
5. Đăng ký/Đăng nhập: Sử dụng trang register.php hoặc login với admin.

## Các tính năng chính
- Đăng ký, đăng nhập.
- Tạo dự án, thêm ghi chú với file đính kèm.
- Quản lý thành viên dự án với level quyền (observer, contributor, moderator, owner).
- Admin panel để xóa user/note/project.
- Thông báo, chia sẻ ghi chú.

## Lưu ý
- Folder uploads/ phải có quyền write (chmod 755).
- Không có frontend framework, thuần PHP + CSS đơn giản.