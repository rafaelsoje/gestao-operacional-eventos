<?php
$item = $item ?? null;
$fieldValue = static function (string $field) use ($item): string {
    return esc(old($field, isset($item) ? (string)($item->{$field} ?? '') : ''));
};
$timeValue = static function (string $field) use ($item): string {
    $value = old($field, isset($item) ? substr((string)($item->{$field} ?? ''), 0, 5) : '');
    return esc((string)$value);
};
?>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Data da visita</label>
        <input type="date" class="form-control" name="data_visita" value="<?= $fieldValue('data_visita'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Tipo de visita</label>
        <input type="text" class="form-control" name="tipo_visita" value="<?= $fieldValue('tipo_visita'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Responsável (EXC)</label>
        <input type="text" class="form-control" name="responsavel_exc" value="<?= $fieldValue('responsavel_exc'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Cliente</label>
        <input type="text" class="form-control" name="cliente" value="<?= $fieldValue('cliente'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Evento</label>
        <input type="text" class="form-control" name="evento" value="<?= $fieldValue('evento'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Data do evento</label>
        <input type="date" class="form-control" name="data_evento" value="<?= $fieldValue('data_evento'); ?>" required>
    </div>

    <div class="col-12 col-md-3">
        <label class="form-label">Horário de início</label>
        <input type="time" class="form-control" name="horario_inicio" value="<?= $timeValue('horario_inicio'); ?>" required>
    </div>

    <div class="col-12 col-md-3">
        <label class="form-label">Horário de término</label>
        <input type="time" class="form-control" name="horario_termino" value="<?= $timeValue('horario_termino'); ?>" required>
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Público estimado</label>
        <input type="number" class="form-control" name="publico_estimado" min="0" step="1" value="<?= $fieldValue('publico_estimado'); ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label">Objetivo da visita</label>
        <textarea class="form-control" name="objetivo_visita" rows="4" required><?= $fieldValue('objetivo_visita'); ?></textarea>
    </div>
</div>
