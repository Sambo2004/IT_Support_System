<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Login') ?> - <?= e(APP_NAME) ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-primary-hover: #1d4ed8;
            --brand-dark: #0f172a;
            --brand-surface: #1e293b;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0b1329 0%, #172554 45%, #0f172a 100%);
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative ambient glow */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, rgba(37, 99, 235, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(14, 165, 233, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .auth-container {
            position: relative;
            z-index: 1;
            max-width: 1020px;
            margin: auto;
        }

        .auth-card-wrap {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .auth-hero-side {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            position: relative;
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-hero-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.2) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-hero-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            color: #cbd5e1;
            font-size: 0.95rem;
        }

        .auth-hero-feature i {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.2);
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .auth-form-side {
            padding: 3rem 3.5rem;
            background: #ffffff;
        }

        @media (max-width: 991.98px) {
            .auth-form-side {
                padding: 2.25rem 2rem;
            }
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.85rem;
            border-radius: 50px;
            background: rgba(37, 99, 235, 0.15);
            color: #93c5fd;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .form-floating-custom .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding-left: 2.75rem;
            height: 52px;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
        }

        .form-floating-custom .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .form-floating-custom .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.15rem;
            z-index: 4;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-floating-custom .form-control:focus ~ .input-icon {
            color: var(--brand-primary);
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 1.15rem;
            z-index: 4;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: #334155;
        }

        .btn-brand-primary {
            background-color: var(--brand-primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            height: 50px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }

        .btn-brand-primary:hover {
            background-color: var(--brand-primary-hover);
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }

        .btn-brand-primary:active {
            transform: translateY(0);
        }

        .demo-pill {
            cursor: pointer;
            border-radius: 8px;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            transition: all 0.15s ease;
        }

        .demo-pill:hover {
            border-color: var(--brand-primary);
            background: #eff6ff;
            color: var(--brand-primary);
            transform: scale(1.03);
        }

        .caps-lock-warning {
            display: none;
            font-size: 0.8rem;
            color: #d97706;
            margin-top: 0.35rem;
        }

        .auth-footer-text {
            color: #94a3b8;
            font-size: 0.82rem;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-4 my-auto auth-container">
        <?= $content ?>
        <div class="auth-footer-text">
            <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &bull; Enterprise Service Desk Portal &bull; v2.0 Production</span>
        </div>
    </div>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
