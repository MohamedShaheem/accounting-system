<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Jewel Plaza Acc</title>
  <link rel="shortcut icon" href="{{asset('media/logo.png')}}" type="image/x-icon">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  
  <!-- Toastr -->
  <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
  
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
  
  <!-- AdminLTE Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>

  .sync-indicator {
      position: relative;
  }

  .sync-indicator::after {
      content: '';
      position: absolute;
      top: 8px;
      right: 8px;
      width: 10px;
      height: 10px;
      background: #ff4444;
      border-radius: 50%;
      border: 2px solid white;
      animation: syncDotPulse 1.5s ease-in-out infinite;
      z-index: 10;
  }

  @keyframes syncDotPulse {
      0%, 100% {
          transform: scale(1);
          opacity: 1;
          box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7);
      }
      50% {
          transform: scale(1.2);
          opacity: 0.8;
          box-shadow: 0 0 0 6px rgba(255, 68, 68, 0);
      }
  }

  .btn{
    border-radius: 7px; 
  }

  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">

<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
          <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-divider"></div>
          {{-- @if (Auth::user()->role == 'superadmin')
          <a href="{{route('profile.edit')}}" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> Profile
          </a>
          @endif --}}
          <div class="dropdown-divider"></div>
          <form method="POST" action="{{ route('logout') }}" class="dropdown-item">
            @csrf
            <a href="{{ route('logout') }}" onclick="event.preventDefault();this.closest('form').submit();">
              <i class="fas fa-sign-out-alt mr-2"></i>Sign out
            </a>
          </form>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" >
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="{{asset('media/logo.png')}}" alt="Jewllery Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Jewel Plaza Account</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          <!-- Sync -->
          <li class="nav-item">
              <a href="{{ route('sync.index') }}"
                class="nav-link {{ request()->routeIs('sync.index') ? 'active' : '' }}
                {{ !$hasSyncedToday ? 'sync-indicator' : '' }}">
                  <i class="nav-icon fas fa-sync-alt"></i>
                  <p>Sync</p>
              </a>
          </li>

          <!-- Dashboard -->
          <li class="nav-item">
            <a href="{{ route('superadmin.index') }}" class="nav-link {{ request()->routeIs('superadmin.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          {{-- DayBook --}}
          <li class="nav-item {{ request()->routeIs('daybook.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('daybook.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Day Book
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">

              <li class="nav-item">
                <a href="{{ route('daybook.create') }}"
                  class="nav-link {{ request()->routeIs('daybook.create') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Transaction</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="{{ route('daybook.index') }}"
                  class="nav-link {{ request()->routeIs('daybook.index') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Day Book List</p>
                </a>
              </li>
              
            </ul>
          </li>

          {{-- Sales Book --}}
        <li class="nav-item {{ request()->routeIs('salesbook.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('salesbook.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-shopping-cart"></i>
            <p>
              Sales Book
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">

            {{-- <li class="nav-item">
              <a href="{{ route('salesbook.create') }}"
                class="nav-link {{ request()->routeIs('salesbook.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Add Transaction</p>
              </a>
            </li> --}}

            <li class="nav-item">
              <a href="{{ route('salesbook.index') }}"
                class="nav-link {{ request()->routeIs('salesbook.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Sales List</p>
              </a>
            </li>
          </ul>
        </li>

        {{-- Bank Transactions --}}
        <li class="nav-item {{ request()->routeIs('bank-transaction.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('bank-transaction.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-university"></i>
            <p>
              Bank Transactions
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">

            <li class="nav-item">
              <a href="{{ route('bank-transaction.create') }}"
                class="nav-link {{ request()->routeIs('bank-transaction.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Add Transaction</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="{{ route('bank-transaction.index') }}"
                class="nav-link {{ request()->routeIs('bank-transaction.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Transaction List</p>
              </a>
            </li>
            
          </ul>
        </li>

        {{-- Daily Expenses --}}
        <li class="nav-item {{ request()->routeIs('daily-expense.*') || request()->routeIs('daily-expense-code.*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->routeIs('daily-expense.*') || request()->routeIs('daily-expense-code.*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-receipt"></i>
            <p>
              Daily Expenses
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('daily-expense.create') }}"
                class="nav-link {{ request()->routeIs('daily-expense.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Add Daily Expense</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('daily-expense.index') }}"
                class="nav-link {{ request()->routeIs('daily-expense.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Daily Expenses List</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('daily-expense-code.index') }}"
                class="nav-link {{ request()->routeIs('daily-expense-code.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Expense Code</p>
              </a>
            </li>
          </ul>
        </li>

        {{-- Individual Accounts --}}
          <li class="nav-item {{ request()->routeIs('individual-account.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('individual-account.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-friends"></i>
              <p>
                Individual Accounts
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('individual-account.create') }}"
                  class="nav-link {{ request()->routeIs('individual-account.create') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add Transaction</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('individual-account.index') }}"
                class="nav-link {{ request()->routeIs('individual-account.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Transaction List</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('individual-account.overview') }}"
                class="nav-link {{ request()->routeIs('individual-account.overview') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Accounts Overview</p>
              </a>
            </li>
            </ul>
          </li>

          {{-- Cash Borrow & Lend --}}
          <li class="nav-item {{ request()->routeIs('cash-borrow-lend.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ request()->routeIs('cash-borrow-lend.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-hand-holding-usd"></i>
                  <p>
                      Cash Borrow & Lend
                      <i class="fas fa-angle-left right"></i>
                  </p>
              </a>

              <ul class="nav nav-treeview">
                <li class="nav-item">
                      <a href="{{ route('cash-borrow-lend.create') }}"
                        class="nav-link {{ request()->routeIs('cash-borrow-lend.create') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Add Transaction</p>
                      </a>
                </li>

                <li class="nav-item">
                      <a href="{{ route('cash-borrow-lend.index') }}"
                        class="nav-link {{ request()->routeIs('cash-borrow-lend.index') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Transaction List</p>
                      </a>
                  </li>
                  
                  <li class="nav-item">
                      <a href="{{ route('cash-borrow-lend.overview') }}"
                        class="nav-link {{ request()->routeIs('cash-borrow-lend.overview') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Accounts Overview</p>
                      </a>
                  </li>             
              </ul>
          </li>

          {{-- User Management --}}
          <li class="nav-item {{ request()->routeIs('users.*', 'login-history.*') ? 'menu-open' : '' }}">
              <a href="#" class="nav-link {{ request()->routeIs('users.*', 'login-history.*') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-users"></i>
                  <p>
                      User Management
                      <i class="fas fa-angle-left right"></i>
                  </p>
              </a>
              <ul class="nav nav-treeview">
                  {{-- User List --}}
                  <li class="nav-item">
                      <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.create') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>User List</p>
                      </a>
                  </li>

                  {{-- Login History --}}
                  <li class="nav-item">
                      <a href="{{ route('login-history.index') }}"
                        class="nav-link {{ request()->routeIs('login-history.*') ? 'active' : '' }}">
                          <i class="far fa-circle nav-icon"></i>
                          <p>Login History</p>
                      </a>
                  </li>
              </ul>
          </li>


        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    @yield('content')
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0
    </div>
    <strong>
    Copyright &copy; {{ date('Y') }}
    <a href="#">Jewel Plaza</a>.
    </strong> 
    All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- ============================================ -->
<!-- SCRIPTS - CORRECT ORDER IS CRITICAL! -->
<!-- ============================================ -->
<!-- 1. jQuery FIRST -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

<!-- 2. Bootstrap 4 Bundle SECOND -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- 3. AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<!-- 4. DataTables -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<!-- 5. Toastr -->
<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

<!-- 6. Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- 7. SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- 8. Toastr Configuration -->
<script>
// Configure toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

@if (session('success'))
    toastr.success("{{ session('success') }}");
@endif

@if (session('info'))
    toastr.info("{{ session('info') }}");
@endif

@if ($errors->any())
    toastr.error("Please fix the errors and try again.");
@endif
</script>

</body>
</html>