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

    .password-hints {
      margin: 0.5rem 0 0;
      padding-left: 1rem;
      font-size: 0.85rem;
    }

    .password-hints li {
      color: #6c757d;
      margin-bottom: 0.2rem;
    }

    .password-hints li.ok {
      color: #198754;
    }

    .strength-wrap {
      margin-top: 0.5rem;
    }
  </style>

  <!-- Validação com JavaScript -->
  <script>
    function passwordRules(password, confirmation) {
      return {
        length: password.length >= 8,
        lower: /[a-z]/.test(password),
        upper: /[A-Z]/.test(password),
        digit: /\d/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password),
        match: confirmation.length > 0 && password === confirmation
      };
    }

    function calcStrength(rules) {
      let score = 0;
      if (rules.length) score++;
      if (rules.lower) score++;
      if (rules.upper) score++;
      if (rules.digit) score++;
      if (rules.symbol) score++;
      return score;
    }

    function updatePasswordUi() {
      const passwordInput = document.getElementById('password');
      const confirmInput = document.getElementById('password_confirmation');
      const submitBtn = document.getElementById('submit-password');
      const feedback = document.getElementById('password-feedback');
      const strengthBar = document.getElementById('password-strength');

      const rules = passwordRules(passwordInput.value, confirmInput.value);
      const score = calcStrength(rules);

      const map = [
        ['rule-length', rules.length],
        ['rule-lower', rules.lower],
        ['rule-upper', rules.upper],
        ['rule-digit', rules.digit],
        ['rule-symbol', rules.symbol],
        ['rule-match', rules.match],
      ];

      map.forEach(function(item) {
        const el = document.getElementById(item[0]);
        if (!el) return;
        el.classList.toggle('ok', item[1]);
      });

      strengthBar.className = 'progress-bar';
      strengthBar.style.width = (score * 20) + '%';
      strengthBar.setAttribute('aria-valuenow', String(score * 20));
      if (score <= 2) {
        strengthBar.classList.add('bg-danger');
      } else if (score <= 4) {
        strengthBar.classList.add('bg-warning');
      } else {
        strengthBar.classList.add('bg-success');
      }

      const isValid = rules.length && rules.lower && rules.upper && rules.digit && rules.symbol && rules.match;
      submitBtn.disabled = !isValid;
      feedback.textContent = isValid
        ? 'Senha válida.'
        : 'A senha precisa atender aos requisitos abaixo.';

      return isValid;
    }

    function validateForm() {
      return updatePasswordUi();
    }

    document.addEventListener('DOMContentLoaded', function () {
      const passwordInput = document.getElementById('password');
      const confirmInput = document.getElementById('password_confirmation');
      if (!passwordInput || !confirmInput) return;

      passwordInput.addEventListener('input', updatePasswordUi);
      confirmInput.addEventListener('input', updatePasswordUi);
      updatePasswordUi();
    });
    
    function togglePassword(id, btn) {
      const input = document.getElementById(id);
      if (!input) return;
      const hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      btn.innerHTML = hidden ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
    }
  </script>
</head>
<body>

  <div class="login-container">
    <!-- Logo -->
    <!-- <img class="w-50" src="<?= base_url('./media/logo-black.png')?>" alt="Logo" class="logo"> Substitua 'logo.png' pelo caminho da sua logo -->
<?php if(isset($recovery) || !empty($forceChange)):?>
    <h1>Cadastro de nova senha</h1>

    <!-- Formulário com validação -->
    <form onsubmit="return validateForm()" method="post" action="<?= !empty($forceChange) ? url_to('password.force.store') : url_to('recovery.store');?>">
      <div class="mb-3">
        <?= csrf_field(); ?> 
          
        <?php if (session()->has('error')) : ?>
          <div class="flash text-center alert alert-danger text-danger" role="alert" aria-live="assertive"><?php echo session()->get('error') ?? ''; ?></div>
        <?php endif; ?>
        <?php if(isset($recovery)):?>
          <input type="hidden" class="form-control" name="recovery" value="<?= $recovery;?>" required>
        <?php endif;?>
        <label for="password" class="form-label">Senha</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" autocomplete="new-password" required>
          <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)" aria-label="Mostrar ou ocultar senha"><i class="fas fa-eye"></i></button>
        </div>
        <div class="strength-wrap">
          <div class="progress" style="height: 6px;">
            <div id="password-strength" class="progress-bar" role="progressbar" style="width:0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
          </div>
        </div>
        <ul class="password-hints">
          <li id="rule-length">Pelo menos 8 caracteres</li>
          <li id="rule-lower">Letra minúscula</li>
          <li id="rule-upper">Letra maiúscula</li>
          <li id="rule-digit">Número</li>
          <li id="rule-symbol">Símbolo (ex.: !@#$)</li>
          <li id="rule-match">Confirmação igual à senha</li>
        </ul>
        <div id="password-feedback" class="small text-muted" aria-live="polite"></div>
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar senha</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-lock"></i></span>
          <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirme sua senha" autocomplete="new-password" required>
          <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)" aria-label="Mostrar ou ocultar confirmação"><i class="fas fa-eye"></i></button>
        </div>
      </div>

     <button id="submit-password" type="submit" class="btn btn-primary">Alterar senha</button>
    </form>

    <!-- <div class="social-login">
      <p>Ou entre com</p>
      <i class="fab fa-facebook"></i>
      <i class="fab fa-google"></i>
      <i class="fab fa-twitter"></i>
    </div> -->

    <!-- <div class="forgot-password">
      <a href="<?= base_url('recovery')?>">Esqueci minha senha?</a>
    </div> -->
  </div>
  <?php else:?>
    <h1>Forbiden</h1>
  <?php endif;?>

  <!-- Bootstrap JS -->
  <script src="<?= base_url('./vendor/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
</body>
</html>
