<?= $this->extend('main')?>;

<?php $this->section('content');?>
<?php // Edição de usuário existente com controle de nome e nível de acesso. ?>

<div class="page-shell mt-3">
    <div class="page-hero">
        <div>
            <h2>Editar usuário</h2>
            <p>Atualize nome e nível de acesso do usuário selecionado.</p>
        </div>
        <a href="<?= base_url('/usuarios');?>" type="button" class="btn btn-outline-success">Lista de usuários</a>
    </div>

    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?php echo esc((string)(session()->get('error') ?? '')); ?></div>
    <?php endif; ?>
    <?php if (session()->has('cuccess')) : ?>
        <div class="flash text-center alert alert-success text-success"><?php echo esc((string)(session()->get('success') ?? '')); ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="content-card h-100">
                <div class="card-body text-center">
                    <img src="<?= url_to('avatar.show', $user->avatar ?? 'avatar.svg');?>" class="rounded-circle mb-3" style="width: 140px; height: 140px; object-fit: cover;" alt="Foto do usuário">
                    <div class="fw-semibold"><?= esc($user->name ?? '') ?></div>
                    <div class="text-muted"><?= esc($user->email ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="content-card">
                <div class="card-body">
                    <div class="content-card-header">
                        <div>
                            <h5>Permissões</h5>
                            <small>Controle administrativo do usuário</small>
                        </div>
                    </div>
                    <form class="row g-3" method="post" action="<?= url_to('update.usuario.store');?>" enctype="multipart/form-data">
                        <?= csrf_field(); ?> 
                        <input type="hidden" class="form-control" name="id" value="<?= $user->id;?>">

                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="name" value="<?= esc((string)$user->name); ?>" required>
                        </div>        
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= esc((string)$user->email); ?>" disabled>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Nível de acesso</label>
                            <?php $currentRole = $user->role ?? (($user->admin ?? 0) == 1 ? 'admin' : 'user'); ?>
                            <select class="form-select" name="role" required>
                                <option value="user" <?= $currentRole === 'user' ? 'selected' : ''; ?>>Usuário comum</option>
                                <option value="admin" <?= $currentRole === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div> 

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="<?= base_url('/usuarios');?>" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>  
<?php $this->endSection('content');?>
