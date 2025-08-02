# Hotel Room Booking System - Pure PHP

This is a hotel room booking system developed with pure PHP and MySQL.

## Main Features

- User Registration/Login
- Search and Book Rooms
- Booking Management
- Room Management (Admin)
- Quản lý người dùng (Admin)
- Responsive design

## Cấu trúc thư mục

```
Hotel_Room_Booking/
│
├── assets/                 # Tài nguyên tĩnh
│   ├── css/               # File CSS
│   ├── js/                # File JavaScript
│   ├── images/            # Hình ảnh
│
├── config/                # Cấu hình
│   └── database.php       # Kết nối database
│
├── includes/              # File include chung
│   ├── header.php         # Header
│   ├── footer.php         # Footer
│
├── pages/                 # Các trang
│   ├── login.php          # Đăng nhập
│   ├── register.php       # Đăng ký
│   ├── rooms.php          # Danh sách phòng
│   └── logout.php         # Đăng xuất
│
├── admin/                 # Trang quản trị
│   └── (các file admin)
│
└── README.md              # File hướng dẫn
```

## Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Web server (Apache/Nginx)

### Hướng dẫn cài đặt

1. **Clone/Download project**
   ```bash
   git clone [repository-url]
   ```
2. **Tạo database**
   - Tạo database mới trong MySQL

3. **Cấu hình kết nối database**
   - Mở file `config/database.php`
   - Chỉnh sửa thông tin kết nối database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'hotel');
   ```

4. **Cấu hình web server**
   - Đặt project trong thư mục web root
   - Cấu hình virtual host (nếu cần)

## Sử dụng


### Chức năng người dùng
- Đăng ký tài khoản mới
- Đăng nhập/Đăng xuất
- Tìm kiếm phòng theo ngày
- Xem chi tiết phòng
- Đặt phòng
- Quản lý đặt phòng của mình

### Chức năng admin
- Quản lý phòng (thêm/sửa/xóa)
- Quản lý đặt phòng
- Quản lý người dùng
- Xem báo cáo thống kê

## Công nghệ sử dụng

- **Backend**: PHP thuần
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5



## License

MIT License
