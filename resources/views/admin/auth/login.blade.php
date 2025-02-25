<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login do painel administrativo">
    <meta name="author" content="Seu Nome">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Login - Web Scraper Admin</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .form-input {
            border-radius: 10rem;
            padding: 1.5rem 1rem;
        }
        .btn-login {
            font-size: 0.9rem;
            letter-spacing: 0.05rem;
            padding: 0.75rem 1rem;
            border-radius: 10rem;
        }
        .bg-login-image {
            background: url('https://source.unsplash.com/K4mSJ7kc0As/600x800');
            background-position: center;
            background-size: cover;
        }
        .text-gray-900 {
            color: #3a3b45 !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <i class="fas fa-spider fa-2x mb-3"></i><br>
                                            Web Scraper Admin
                                        </h1>
                                    </div>
                                    
                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    <form class="user" method="POST" action="{{ route('admin.login.post') }}">
                                        @csrf
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-input"
                                                id="email" aria-describedby="emailHelp"
                                                placeholder="E-mail" value="{{ old('email') }}">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-input"
                                                id="password" placeholder="Senha">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                                <label class="custom-control-label" for="remember">Lembrar-me</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block btn-login">
                                            Entrar
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <small class="text-muted">
                                            Para fins de testes, use:<br>
                                            Email: admin@example.com<br>
                                            Senha: password
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>