<?= $this->extend('main')?>;

<?php $this->section('content');?>
<?php // Tela de cadastro administrativo de usuários com definição de papel e senha padrão. ?>

<div class="page-shell mt-3">
    <div class="page-hero">
        <div>
            <h2>Cadastro de usuários</h2>
            <p>Crie novos acessos com perfil de usuário ou administrador.</p>
        </div>
        <a href="<?= base_url('/usuarios');?>" type="button" class="btn btn-outline-secondary">Lista de usuários</a>
    </div>
<div class="content-card p-3">
    <div class="content-card-header">
        <div>
            <h5>Novo usuário</h5>
            <small>A senha padrão será aplicada e o usuário trocará no primeiro login.</small>
        </div>
    </div>
    <form class="row g-3" method="post" action="<?= url_to('usuario.store');?>">
        <?= csrf_field(); ?> 
        <?php if (session()->has('error')) : ?>
                <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
            <?php endif; ?>
            <?php if (session()->has('cuccess')) : ?>
                <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
            <?php endif; ?>

        <div class="col-md-6">
            
            
            <label for="inputEmail4" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" required>
        </div>        
        <div class="col-md-6">
            <label for="inputPassword4" class="form-label">email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="col-12 col-md-6">
        <label class="form-label">Nível de acesso</label>
            <select class="form-select" name="role" required>
                <option value="user">Usuário comum</option>
                <option value="admin">Administrador</option>
            </select>
        </div> 
        <div class="col-12 col-md-6">
        <label class="form-label">Senha</label>
        <input type="text" class="form-control" value="Senha padrão será aplicada no cadastro" disabled>
        <div class="form-text">O usuário será obrigado a alterar a senha no primeiro login.</div>
        </div> 
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
        </div>
    </form>
</div>
</div>  
<?php $this->endSection('content');?>
