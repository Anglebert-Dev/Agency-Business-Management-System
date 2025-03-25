<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kibasumba Business App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary-color: #0d6efd;
            --sidebar-width: 250px;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 3.5rem;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 3.5rem;
            left: -250px;
            padding-top: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            z-index: 1050;
            transition: left 0.3s ease-in-out;
        }

        .sidebar.show {
            left: 0;
        }

        .nav-link {
            color: #495057;
            padding: 0.8rem 1rem;
            border-radius: 0.25rem;
            margin: 0.2rem 0.5rem;
        }

        .nav-link:hover {
            background-color: #e9ecef;
            color: var(--primary-color);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: #fff;
        }

        .main-content {
            transition: margin-left 0.3s ease-in-out;
        }
        @media (min-width: 768px) {
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            padding-top: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            z-index: 1000;
            transition: left 0.3s ease-in-out;
        }

        .main-content {
            margin-left: var(--sidebar-width);
        }
    }

        @media (max-width: 767px) {
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: var(--navbar-height);
            left: -250px; 
            padding-top: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            z-index: 1050;
            transition: left 0.3s ease-in-out;
        }

        .sidebar.show {
            left: 0; 
        }

        .main-content {
            margin-left: 0;
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .mobile-menu-overlay.show {
            display: block;
            opacity: 1;
        }
    }

        .dropdown-menu {
            position: absolute !important;
            z-index: 1031;
        }
   
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            @auth
                <button class="navbar-toggler border-0 me-2" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            @endauth
            <a class="navbar-brand" href="/">
                <i class="fas fa-building me-2"></i>
                Kibasumba Business App
            </a>
            
            @guest
                <div class="d-none d-lg-flex navbar-nav ms-auto">
                    <a href="{{ route('login') }}" class="nav-link text-white">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="nav-link text-white">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </div>
                <div class="d-lg-none nav-item dropdown">
                    <a class="nav-link text-white px-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <div class="navbar-nav ms-auto d-flex flex-row">
                    <li class="nav-item dropdown">
                        <a class="nav-link text-white px-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </div>
            @endguest
        </div>
    </nav>

    <div class="mobile-menu-overlay"></div>

    <div class="container-fluid">
        <div class="row">
            @auth
                <div class="col-md-3 col-lg-2 sidebar">
                    <div class="position-sticky">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transactions.index') || (request()->routeIs('transactions.edit') && !request()->routeIs('transactions.transfer')) ? 'active' : '' }}"
                                    href="{{ route('transactions.index') }}">
                                    <i class="fas fa-money-bill-wave me-2"></i> Transactions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transactions.reports') ? 'active' : '' }}"
                                    href="{{ route('transactions.reports') }}">
                                    <i class="fas fa-chart-bar me-2"></i> Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                                    href="{{ route('customers.index') }}">
                                    <i class="fas fa-users me-2"></i> Customers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('banks.*') ? 'active' : '' }}"
                                    href="{{ route('banks.index') }}">
                                    <i class="fas fa-university me-2"></i> Banks
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transactions.transfer') ? 'active' : '' }}"
                                    href="{{ route('transactions.transfer') }}">
                                    <i class="fas fa-exchange-alt me-2"></i> Transfer
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('float_accounts.*') ? 'active' : '' }}"
                                    href="{{ route('float_accounts.index') }}">
                                    <i class="fas fa-wallet me-2"></i> Float Accounts
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endauth
            
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content @guest w-100 @endguest">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-menu-overlay');

            if (!sidebarToggle || !sidebar || !overlay) {
                console.error('One or more required elements are missing');
                return;
            }

            function toggleSidebar() {
                if (window.innerWidth <= 767) {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                }
            }

            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 767) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>