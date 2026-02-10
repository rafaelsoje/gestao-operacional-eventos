<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <h3 class="mb-3">Meu Perfil</h3>

    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
    <?php endif; ?>
    <?php if (session()->has('success')) : ?>
        <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>" class="rounded-circle mb-3" style="width: 140px; height: 140px; object-fit: cover;" alt="Foto do usuário">
                    <h5 class="mb-1"><?= esc($user->name ?? '') ?></h5>
                    <div class="text-muted"><?= esc($user->email ?? '') ?></div>
                    <div class="mt-2">
                        <?php
                            $nivel = 'Usuário comum';
                            if ($role === 'admin') {
                                $nivel = 'Administrador';
                            } elseif ($role === 'superuser') {
                                $nivel = 'Superusuário';
                            }
                        ?>
                        <span class="badge bg-secondary"><?= $nivel ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= url_to('perfil.update');?>" enctype="multipart/form-data">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Atualizar foto</label>
                            <input type="file" class="form-control" name="avatar">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">Senha atual</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Digite sua senha atual">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nova senha</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Digite a nova senha">
                            <div class="form-text">Mínimo de 8 caracteres.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar nova senha</label>
                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" placeholder="Repita a nova senha">
                            <div id="password_match_feedback" class="form-text"></div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const pwd = document.getElementById('new_password');
        const confirm = document.getElementById('new_password_confirm');
        const feedback = document.getElementById('password_match_feedback');

        function validate() {
            const p = pwd.value.trim();
            const c = confirm.value.trim();

            pwd.classList.remove('is-invalid', 'is-valid');
            confirm.classList.remove('is-invalid', 'is-valid');
            feedback.textContent = '';
            feedback.className = 'form-text';

            if (!p && !c) return;

            if (p.length > 0 && p.length < 8) {
                pwd.classList.add('is-invalid');
                feedback.textContent = 'A senha deve ter pelo menos 8 caracteres.';
                feedback.classList.add('text-danger');
                return;
            }

            if (p && c && p !== c) {
                confirm.classList.add('is-invalid');
                feedback.textContent = 'As senhas não conferem.';
                feedback.classList.add('text-danger');
                return;
            }

            if (p && c && p === c) {
                pwd.classList.add('is-valid');
                confirm.classList.add('is-valid');
                feedback.textContent = 'Senhas conferem.';
                feedback.classList.add('text-success');
            }
        }

        pwd.addEventListener('input', validate);
        confirm.addEventListener('input', validate);
    })();
</script>

<?php $this->endSection('content');?>
