<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì hệ thống - Laravel CMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .maintenance-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .maintenance-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2d3748;
            font-weight: 700;
        }

        .message {
            font-size: 1.2rem;
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .details {
            background: #f7fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: left;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #2d3748;
        }

        .detail-value {
            color: #4a5568;
        }

        .progress-container {
            margin: 2rem 0;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
            width: {{ $progress ?? 0 }}%;
        }

        .contact-info {
            background: #edf2f7;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .refresh-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: transform 0.2s ease;
        }

        .refresh-button:hover {
            transform: translateY(-2px);
        }

        .countdown {
            font-size: 1.1rem;
            font-weight: 600;
            color: #667eea;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .maintenance-container {
                padding: 2rem;
                margin: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="icon">
            🔧
        </div>
        
        <h1>Bảo trì hệ thống</h1>
        
        <p class="message">
            {{ $message ?? 'Hệ thống đang được bảo trì. Vui lòng thử lại sau.' }}
        </p>

        <div class="details">
            <div class="detail-item">
                <span class="detail-label">Lý do:</span>
                <span class="detail-value">{{ $reason ?? 'Bảo trì định kỳ' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Thời gian dự kiến:</span>
                <span class="detail-value">{{ $estimated_duration ?? '2 giờ' }}</span>
            </div>
            @if(isset($start_time))
            <div class="detail-item">
                <span class="detail-label">Bắt đầu:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($start_time)->format('d/m/Y H:i') }}</span>
            </div>
            @endif
            @if(isset($end_time))
            <div class="detail-item">
                <span class="detail-label">Dự kiến hoàn thành:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($end_time)->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>

        @if(isset($progress) && $progress > 0)
        <div class="progress-container">
            <div class="progress-label">
                <span>Tiến độ</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
        @endif

        <button class="refresh-button" onclick="window.location.reload()">
            Tải lại trang
        </button>

        <div class="countdown" id="countdown"></div>

        <div class="contact-info">
            <strong>Cần hỗ trợ?</strong><br>
            Liên hệ: <a href="mailto:{{ $contact_email ?? 'admin@laravel-cms.com' }}">{{ $contact_email ?? 'admin@laravel-cms.com' }}</a>
        </div>
    </div>

    <script>
        // Auto refresh countdown
        let retryAfter = {{ $retry_after ?? 3600 }};
        let countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            if (retryAfter <= 0) {
                window.location.reload();
                return;
            }
            
            let hours = Math.floor(retryAfter / 3600);
            let minutes = Math.floor((retryAfter % 3600) / 60);
            let seconds = retryAfter % 60;
            
            let timeString = '';
            if (hours > 0) timeString += hours + ' giờ ';
            if (minutes > 0) timeString += minutes + ' phút ';
            timeString += seconds + ' giây';
            
            countdownElement.textContent = `Tự động tải lại sau: ${timeString}`;
            retryAfter--;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
