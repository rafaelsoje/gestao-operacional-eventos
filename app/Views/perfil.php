<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="page-shell mt-3 mb-5 profile-page">
    <style>
        .profile-page .profile-card {
            padding: 1.4rem;
        }
        .profile-page .profile-avatar {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border: 6px solid #eef6ff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        }
        .profile-page .access-card {
            padding: 1.25rem;
        }
        .profile-page .security-section {
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #e5edf6;
        }
    </style>

    <div class="page-hero">
        <div>
            <h2>Meu perfil</h2>
            <p>Atualize sua foto e mantenha sua senha de acesso em dia.</p>
        </div>
    </div>

    <?php if (session()->has('error')) : ?>
        <div class="app-banner app-banner-danger mb-3">
            <div><strong>Não foi possível atualizar</strong><p><?= esc((string)session()->get('error')); ?></p></div>
        </div>
    <?php endif; ?>
    <?php if (session()->has('success')) : ?>
        <div class="app-banner app-banner-info mb-3">
            <div><strong>Perfil atualizado</strong><p><?= esc((string)session()->get('success')); ?></p></div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="content-card profile-card h-100 text-center">
                <img src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>" class="profile-avatar rounded-circle mb-3" alt="Foto do usuário">
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
                <div class="content-card-soft text-start mt-4">
                    <strong>Conta</strong>
                    <div class="text-muted"><?= esc($user->email ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="content-card access-card">
                <div class="content-card-header">
                    <div>
                        <h5>Dados de acesso</h5>
                        <small>Avatar e alteração de senha</small>
                    </div>
                </div>
                <!-- Atualização de avatar e senha do usuário autenticado. -->
                <form method="post" action="<?= url_to('perfil.update');?>" enctype="multipart/form-data">
                    <?= csrf_field(); ?>

                    <div class="content-card-soft mb-3">
                        <label class="form-label fw-semibold">Atualizar foto</label>
                        <input type="file" class="form-control" name="avatar">
                    </div>

                    <div class="security-section">
                        <div class="form-section-title mt-0">Segurança</div>

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
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        // Validação visual de senha no front para reduzir erros de submissão.
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
