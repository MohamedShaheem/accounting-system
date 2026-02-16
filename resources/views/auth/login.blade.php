<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jewel Plaza Account</title>
  <link rel="shortcut icon" href="{{asset('media/logo.png')}}" type="image/x-icon">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Crimson+Text:wght@400;600&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
   
  <style>
    body {
      background: linear-gradient(135deg, #001f3f 0%, #0059b3 50%, #003366 100%);
      background-attachment: fixed;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        radial-gradient(circle at 20% 20%, rgba(173, 216, 230, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(173, 216, 230, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 60%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
      animation: shimmer 8s ease-in-out infinite alternate;
    }
    
    @keyframes shimmer {
      0% { opacity: 0.8; }
      100% { opacity: 1; }
    }
    
    .login-box {
      width: 420px;
      margin: 5% auto;
      position: relative;
      z-index: 1;
    }
    
    .login-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .login-logo p {
      color: #ffffff;
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem;
      font-weight: 700;
      text-decoration: none;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
      letter-spacing: 2px;
      position: relative;
    }
    
    @keyframes sparkle {
      0%, 100% { transform: scale(1) rotate(0deg); opacity: 1; }
      50% { transform: scale(1.2) rotate(45deg); opacity: 0.8; }
    }
    
    .card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: none;
      border-radius: 20px;
      box-shadow: 
        0 20px 40px rgba(0, 0, 0, 0.3),
        0 0 0 1px rgba(0, 123, 255, 0.2);
      overflow: hidden;
      position: relative;
    }
    
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, #007bff, #00bfff, #007bff);
    }
    
    .login-card-body {
      padding: 40px;
      position: relative;
    }
    
    .login-box-msg {
      font-family: 'Crimson Text', serif;
      font-size: 2rem;
      color: #0059b3;
      text-align: center;
      margin-bottom: 30px;
      font-weight: 600;
      position: relative;
    }
    
    .form-control {
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      padding: 12px 15px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
    }
    
    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
      background: #ffffff;
      transform: translateY(-1px);
    }
    
    .input-group {
      margin-bottom: 25px;
    }
    
    .input-group-text {
      background: linear-gradient(135deg, #007bff, #0059b3);
      border: none;
      color: white;
      border-radius: 0 12px 12px 0;
      padding: 12px 15px;
      transition: all 0.3s ease;
    }
    
    .input-group:hover .input-group-text {
      background: linear-gradient(135deg, #0059b3, #003366);
      transform: scale(1.05);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #007bff, #0059b3);
      border: none;
      border-radius: 12px;
      padding: 12px 20px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #0059b3, #003366);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
    }
    
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-primary:hover::before {
      left: 100%;
    }
    
    .icheck-primary > input:checked ~ label::before {
      background-color: #007bff;
      border-color: #007bff;
    }
    
    .icheck-primary > input:checked ~ label::after {
      color: #fff;
    }
    
    .checkbox-label {
      font-family: 'Crimson Text', serif;
      color: #666;
      font-weight: 500;
    }
    
    .brand-logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      display: block;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
      transition: transform 0.3s ease;
    }
    
    .brand-logo:hover {
      transform: scale(1.05) rotate(5deg);
    }
    
    .jewel-decoration {
      position: absolute;
      color: rgba(0, 123, 255, 0.1);
      font-size: 2rem;
      animation: float 3s ease-in-out infinite;
    }
    
    .jewel-decoration:nth-child(1) {
      top: 10%;
      right: 10%;
      animation-delay: 0s;
    }
    
    .jewel-decoration:nth-child(2) {
      bottom: 15%;
      left: 15%;
      animation-delay: 1s;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-10px) rotate(10deg); }
    }
    
    @media (max-width: 576px) {
      .login-box {
        width: 90%;
        margin: 10% auto;
      }
      
      .login-card-body {
        padding: 30px 20px;
      }
      
      .login-logo a {
        font-size: 2rem;
      }
    }

    .error-message {
      background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.05));
      border: 2px solid rgba(0, 123, 255, 0.3);
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      animation: slideIn 0.3s ease-out;
      backdrop-filter: blur(5px);
    }
    
    .success-message {
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.1), rgba(0, 200, 83, 0.05));
      border: 2px solid rgba(0, 200, 83, 0.3);
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      animation: slideIn 0.3s ease-out;
      backdrop-filter: blur(5px);
    }
    
    .error-icon, .success-icon {
      margin-right: 12px;
      margin-top: 2px;
      font-size: 1.1rem;
    }
    
    .error-icon { color: #dc3545; }
    .success-icon { color: #28a745; }
    
    .error-content, .success-content { flex: 1; }
    .error-content p { margin: 0; color: #721c24; font-weight: 500; font-size: 0.95rem; line-height: 1.4; }
    .success-content p { color: #155724; }
  </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <img src="{{asset('media/logo.png')}}" alt="Jewel Plaza Logo" class="brand-logo img-circle elevation-3">
    <p>Jewel Plaza Accounts</p>
  </div>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Welcome</p>
      @if ($errors->any())
        <div class="alert alert-danger error-message">
          <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="error-content">
            @foreach ($errors->all() as $error)
              <p class="mb-1">{{ $error }}</p>
            @endforeach
          </div>
        </div>
      @endif
      
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email Address" id="email" name="email" :value="old('email')" required autofocus autocomplete="username">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="password" name="password" required autocomplete="current-password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row mt-4" style="display:flex; justify-content: center;">
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">
              <span>Sign In</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>
</html>
