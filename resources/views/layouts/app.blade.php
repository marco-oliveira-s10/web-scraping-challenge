<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Product Catalog') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --text-color: #333;
            --bg-light: #f4f6f7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        .nav-link {
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .product-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            object-fit: cover;
            height: 250px;
            width: 100%;
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .page-link {
            color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .badge {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .product-card img {
                height: 200px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <a class="navbar-brand" href="{{ route('products.index') }}">
                    <i class="fas fa-shopping-basket me-2"></i>Product Catalog
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <!--  -->
                        </li>
                        <li class="nav-item">
                            <!--  -->
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.login') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-shield me-1"></i> Admin Login
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="container py-4">
        @yield('content')
    </main>

    <footer class="bg-white border-top py-4 mt-4">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; {{ date('Y') }} Product Catalog. Built with
                <i class="fas fa-heart text-danger mx-1"></i>
                using Laravel {{ app()->version() }}
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto close alerts
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>

</html>