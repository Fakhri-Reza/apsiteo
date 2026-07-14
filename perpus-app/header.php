<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Sistem Informasi Perpustakaan' ?></title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Style Layout -->
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }
        #wrapper {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }
        #sidebar-wrapper {
            min-width: 260px;
            max-width: 260px;
            background: #0f172a;
            color: #94a3b8;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1e293b;
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 1.5rem 1.25rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #f8fafc;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        #sidebar-wrapper .list-group {
            flex-grow: 1;
            padding: 1rem 0;
        }
        #sidebar-wrapper .list-group-item {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 0.8rem 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }
        #sidebar-wrapper .list-group-item:hover {
            color: #f8fafc;
            background: #1e293b;
            border-left-color: #4f46e5;
        }
        #sidebar-wrapper .list-group-item.active {
            color: #fff;
            background: #1e293b;
            border-left-color: #4f46e5;
            font-weight: 600;
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        #page-content-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-y: auto;
        }
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
        }
        /* Custom Cards for Dashboard Statistics */
        .stat-card {
            border: none;
            border-radius: 16px;
            background: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 5rem;
            opacity: 0.08;
            transform: rotate(-15deg);
        }
    </style>
</head>
<body>
    <div id="wrapper">
