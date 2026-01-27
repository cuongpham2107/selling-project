# Hướng dẫn sử dụng Cron API

API này được thiết kế để thay thế cho việc thiết lập Cron job truyền thống trên máy chủ. Bạn có thể sử dụng các dịch vụ bên ngoài (như Dify Cloud, Cron-Job.org, hoặc GitHub Actions) để gọi API này định kỳ.

## 1. Thông tin chung

- **Endpoint**: `http://125.212.133.148:8009/api/cron/run`
- **Phương thức**: `GET` hoặc `POST`
- **Tần suất khuyến nghị**: 1 lần mỗi ngày (vào lúc 00:00) hoặc tùy theo nhu cầu.

## 2. Xác thực (Security)

API được bảo vệ bằng một mã Token bí mật. Bạn có thể gửi mã này theo 1 trong 2 cách sau:

### Cách 1: Gửi qua Header (Khuyến nghị)
Sử dụng Header `X-Cron-Token`.
- **Key**: `X-Cron-Token`
- **Value**: `selling_cron_secret_2026`

### Cách 2: Gửi qua URL (Query Parameter)
Thêm `?token=...` vào cuối đường dẫn.
- **URL**: `http://125.212.133.148:8009/api/cron/run?token=selling_cron_secret_2026`

## 3. Các tác vụ thực hiện

Khi API này được gọi thành công, nó sẽ thực thi các lệnh sau:

1.  **Hoàn tất đơn hàng Shop**: Tự động chuyển trạng thái các đơn hàng đã hết thời gian khiếu nại (3 ngày) sang "Hoàn tất" và cộng tiền cho người bán.
2.  **Hủy giao dịch trung gian**: Tự động hủy các giao dịch trung gian đã quá hạn thanh toán.

## 4. Ví dụ lệnh gọi (cURL)

```bash
curl -X GET "http://125.212.133.148:8009/api/cron/run" \
     -H "X-Cron-Token: selling_cron_secret_2026"
```

## 5. Kết quả trả về (JSON)

Nếu thành công:
```json
{
    "status": "success",
    "timestamp": "2026-01-27 16:19:06",
    "results": {
        "complete_shop_transactions": "...",
        "cancel_overdue_transactions": "..."
    }
}
```

Nếu sai Token hoặc không có Token:
```json
{
    "status": "error",
    "message": "Unauthorized."
}
```
