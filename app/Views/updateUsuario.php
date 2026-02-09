<?= $this->extend('main')?>;

<?php $this->section('content');?>

<div class="container mt-3">
    <div class="d-flex align-items-end justify-content-end mb-3">
        <a href="<?= base_url('/usuarios');?>" type="button" class="btn btn-success">Lista de usuários</a>
    </div>
</div>
<div class="container border pt-2 pb-2">
    <h3 class="text-center mb-3 border p-1">Cadastro de Usuários</h3>
    <form class="row g-3" method="post" action="<?= url_to('update.usuario.store');?>" enctype="multipart/form-data">
        <?= csrf_field(); ?> 
        <?php if (session()->has('error')) : ?>
                <div class="flash text-center alert alert-danger text-danger"><?php echo session()->get('error') ?? ''; ?></div>
            <?php endif; ?>
            <?php if (session()->has('cuccess')) : ?>
                <div class="flash text-center alert alert-success text-success"><?php echo session()->get('success') ?? ''; ?></div>
            <?php endif; ?>

        <div class="col-md-6">
        <input type="hidden" class="form-control" name="id" value="<?= $user->id;?>">
            <label for="inputEmail4" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" value="<?= $user->name;?>" required>
        </div>        
        <div class="col-md-6">
            <label for="inputPassword4" class="form-label">email</label>
            <input type="email" class="form-control" name="email" value="<?= $user->email;?>" disabled>
        </div>
        <div class="col-12 col-md-6">
        <label class="form-label">Nível de acesso</label>
            <select class="form-select" name="admin">                
                <option>Selecione...</option>
                <option <?php if($user->admin == 1){echo "selected";}?> value="1">Administrador</option>
                <option <?php if($user->admin == 0){echo "selected";}?> value="0">Convidado</option>                
            </select>
        </div> 
        <div class="col-12 col-md-6">
        <label class="form-label">Senha</label>
        <input type="password" class="form-control" name="password">
        </div> 
        <div class="col-md-12">
            <label for="inputCity" class="form-label">Foto do usuário</label>
            <input type="file" class="form-control" name="avatar" >
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </div>
    </form>
</div>  
<?php $this->endSection('content');?>