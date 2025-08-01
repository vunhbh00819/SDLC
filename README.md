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
│   └── fonts/             # Font chữ
│
├── config/                # Cấu hình
│   └── database.php       # Kết nối database
│
├── includes/              # File include chung
│   ├── header.php         # Header
│   ├── footer.php         # Footer
│   └── functions.php      # Các hàm tiện ích
│
├── classes/               # Các class PHP
│   ├── User.php           # Class quản lý người dùng
│   ├── Room.php           # Class quản lý phòng
│   └── Booking.php        # Class quản lý đặt phòng
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
├── uploads/               # Thư mục upload
│   └── rooms/             # Hình ảnh phòng
│
├── index.php              # Trang chủ
├── hotel_booking.sql      # File database
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
   - Import file `hotel_booking.sql` vào database

3. **Cấu hình kết nối database**
   - Mở file `config/database.php`
   - Chỉnh sửa thông tin kết nối database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'hotel_booking');
   ```

4. **Cấu hình web server**
   - Đặt project trong thư mục web root
   - Cấu hình virtual host (nếu cần)

5. **Phân quyền thư mục**
   - Cho phép ghi vào thư mục `uploads/`
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/rooms/
   ```

## Sử dụng

### Tài khoản mặc định
- **Admin**: admin@hotel.com / password: password

### Chức năng người dùng
- Đăng ký tài khoản mới
- Đăng nhập/Đăng xuất
- Tìm kiếm phòng theo ngày và số khách
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
- **Icons**: Font Awesome
- **Architecture**: MVC pattern

## Tính năng bảo mật

- Mã hóa mật khẩu bằng bcrypt
- Validation dữ liệu đầu vào
- Prepared statements chống SQL injection
- CSRF protection
- Session management

## Responsive Design

Website được thiết kế responsive, tương thích với:
- Desktop
- Tablet
- Mobile

## Liên hệ

Nếu có bất kỳ câu hỏi nào, vui lòng liên hệ:
- Email: info@hotel.com
- Phone: 0123456789

## License

MIT License
