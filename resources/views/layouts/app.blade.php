<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Business App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --primary-color: #0d6efd;
            --sidebar-width: 250px;
        }

        body {
            background-color: #f8f9fa;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 3.5rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            z-index: 1000;
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
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: calc(100vh - 3.5rem);
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: 100vh;
                position: fixed;
                padding-top: 4rem;
                display: none;
                background-color: white;
                z-index: 1001;
                overflow-y: auto;
            }

            .sidebar.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                margin-top: 1rem;
            }

            .mobile-menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                display: none;
            }

            .mobile-menu-overlay.show {
                display: block;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top mb-4">
        <div class="container-fluid">
            @auth
                <button class="navbar-toggler border-0 me-2" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            @endauth
            <a class="navbar-brand" href="/">Agency Business</a>
            <div class="navbar-nav ms-auto d-flex flex-row">
                @auth
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
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link text-white">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link text-white">Register</a>
                    </li>
                @endauth
            </div>
        </div>
    </nav>

    <div class="mobile-menu-overlay"></div>

    <div class="container-fluid">
        <div class="row">
            @auth
                <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                    <div class="position-sticky mt-4">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('transactions.index') || (request()->routeIs('transactions.edit') && !request()->routeIs('transactions.transfer')) ? 'active' : '' }}"
                                    href="{{ route('transactions.index') }}">
                                    <i class="fas fa-exchange-alt me-2"></i> Transactions
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
            
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content mt-4 @guest w-100 @endguest">
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
    @stack('scripts')
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.mobile-menu-overlay');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            // Close sidebar when clicking a link on mobile
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            });
        });
    </script>
    @endpush
</body>
</html>