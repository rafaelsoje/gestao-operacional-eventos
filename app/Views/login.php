<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">      
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="#">
    <meta name="author" content="Rafael Soje - rafaelsoje@gmail.com">
    <meta name="keywords" content="#">
    <!-- <meta name="robots" content="index, follow"> -->
    <meta property="og:title" content="Audiovisual">
    <meta property="og:description" content="#">
    <meta property="og:image" content="<?= base_url('/assets/images/logo.png');?>">
    <meta property="og:url" content="<?= base_url();?>">    

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?= base_url('/assets/images/logo.png') ?>" sizes="180x180">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>" sizes="16x16" type="image/png">
    <link rel="icon" href="<?= base_url('/assets/images/logo.png'); ?>">

  <!-- Bootstrap CSS -->
  <link href="<?=base_url('assets/vendor/bootstrap/css/bootstrap.min.css')?>" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= base_url('assets/vendor/font-awesome/css/all.min.css')?>">

  <title>AudioVisual - Login</title>

  <!-- Custom CSS -->
  <style>
    body {
      background-color: #343A40;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 100%;
    }

    .login-container h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 24px;
      color: #333;
    }

    .login-container .logo {
      display: block;
      margin: 0 auto 1.5rem;
      width: 100px; /* Ajuste o tamanho conforme necessário */
    }

    .login-container .form-control {
      border-radius: 30px;
      padding: 1rem 1.5rem;
    }

    .login-container .btn {
      border-radius: 30px;
      padding: 0.8rem 1.5rem;
      width: 100%;
    }

    .login-container .social-login {
      text-align: center;
      margin-top: 1rem;
    }

    .login-container .social-login i {
      margin: 0 10px;
      font-size: 24px;
      cursor: pointer;
      color: #495057;
    }

    .login-container .social-login i:hover {
      color: #007bff;
    }

    .login-container .forgot-password {
      text-align: center;
      margin-top: 1rem;
      font-size: 14px;
    }
  </style>

  <!-- Validação com JavaScript -->
  <script>
    function validateForm() {
      let email = document.getElementById('email').value;
      let password = document.getElementById('password').value;
      let errorMsg = '';

      // Validação do e-mail
      const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      if (!emailPattern.test(email)) {
        errorMsg += 'Por favor, insira um e-mail válido.\n';
      }

      // Validação da senha (mínimo de 6 caracteres)
      if (password.length < 6) {
        errorMsg += 'A senha deve ter pelo menos 6 caracteres.\n';
      }

      if (errorMsg) {
        alert(errorMsg);
        return false; // Bloqueia o envio do formulário
      }

      return true; // Permite o envio do formulário se passar nas validações
    }
  </script>
</head>
<body>

  <div class="login-container">
    <!-- Logo -->
    <!-- <img class="w-50" src="<?= base_url('./media/logo-black.png')?>" alt="Logo" class="logo"> Substitua 'logo.png' pelo caminho da sua logo -->

    <h1>Login</h1>

    <!-- Formulário com validação -->
    <form onsubmit="return validateForm()" method="post" action="<?= url_to('login.store');?>">
      <div class="mb-3">
        <?= csrf_field(); ?> 
          
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>

        <label for="email" class="form-label">E-mail</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-envelope"></i></span>
          <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <!-- <div class="social-login">
      <p>Ou entre com</p>
      <i class="fab fa-facebook"></i>
      <i class="fab fa-google"></i>
      <i class="fab fa-twitter"></i>
    </div> -->

    <div class="forgot-password">
      <a href="<?= base_url('recovery')?>">Esqueci minha senha?</a>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="<?= base_url('./assets/vendor/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
</body>
</html>
