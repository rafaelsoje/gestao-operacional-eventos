<?= $this->extend('main')?>;

<?php $this->section('content');?>
<?php helper('format'); ?>

<?php
$enabled = (bool)($enabled ?? false);
$tableReady = (bool)($tableReady ?? false);
$charge = $charge ?? null;
$charges = $charges ?? [];
$state = $state ?? [];
$settings = $settings ?? ['enabled' => false, 'amount' => 65, 'due_day' => 10, 'warning_day' => 5, 'grace_days' => 2];
$asaasConfigured = (bool)($asaasConfigured ?? false);
$canManualRelease = (bool)($canManualRelease ?? false);
$statusBadge = static function (string $status): string {
    return match ($status) {
        'paid' => 'success',
        'overdue' => 'danger',
        'cancelled', 'refunded' => 'secondary',
        default => 'warning',
    };
};
$statusLabel = static function (string $status): string {
    return match ($status) {
        'paid' => 'Pago',
        'overdue' => 'Vencido',
        'cancelled' => 'Cancelado',
        'refunded' => 'Estornado',
        default => 'Pendente',
    };
};
$monthRefLabel = static function (?string $monthRef): string {
    if (!$monthRef || !preg_match('/^\d{4}-\d{2}$/', $monthRef)) {
        return $monthRef ?: '-';
    }

    [$year, $month] = explode('-', $monthRef);
    return $month . '/' . $year;
};
$openCharges = array_values(array_filter($charges, static function ($item): bool {
    return in_array((string)($item->status ?? 'pending'), ['pending', 'overdue'], true);
}));
$paidCharges = array_values(array_filter($charges, static function ($item): bool {
    return (string)($item->status ?? '') === 'paid';
}));
$openAmount = array_reduce($openCharges, static fn (float $total, object $item): float => $total + (float)($item->amount ?? 0), 0.0);
$paidAmount = array_reduce($paidCharges, static fn (float $total, object $item): float => $total + (float)($item->amount ?? 0), 0.0);
$nextBlockDate = !empty($state['block_date']) ? date('d/m/Y', strtotime((string)$state['block_date'])) : '-';
?>

<div class="page-shell mt-3 mb-5 subscription-page">
    <?php if (session()->has('error')) : ?>
        <div class="flash text-center alert alert-danger text-danger"><?= session()->get('error') ?? ''; ?></div>
    <?php endif; ?>
    <?php if (session()->has('success')) : ?>
        <div class="flash text-center alert alert-success text-success"><?= session()->get('success') ?? ''; ?></div>
    <?php endif; ?>

    <style>
        .subscription-page .payment-box {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }
        .subscription-page .pix-code {
            min-height: 110px;
            font-family: monospace;
            font-size: 0.82rem;
        }
        .subscription-page .qr-image {
            max-width: 260px;
            width: 100%;
            height: auto;
        }
        .subscription-page .status-tile {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 8px;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
            height: 100%;
        }
        .subscription-page .status-tile .label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .subscription-page .status-tile .value {
            color: #172033;
            font-size: 1.35rem;
            font-weight: 800;
        }
        .subscription-page .client-help {
            border-left: 4px solid #3197da;
            background: #eef6ff;
            border-radius: 8px;
            color: #0f5f96;
            padding: 0.85rem 1rem;
        }
    </style>

    <div class="page-hero">
        <div>
            <h2>Assinatura do sistema</h2>
            <p>Consulte mensalidades, gere Pix e acompanhe pagamentos reconhecidos.</p>
        </div>
        <?php if ($charge): ?>
            <span class="badge text-bg-<?= $statusBadge((string)$charge->status); ?>">
                <?= esc($statusLabel((string)$charge->status)); ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (!$enabled && $canManualRelease): ?>
        <div class="app-banner app-banner-info mb-3">
            <div><strong>Assinatura desativada</strong><p>O controle de assinatura está desativado neste ambiente.</p></div>
        </div>
    <?php endif; ?>

    <?php if (!$tableReady && $canManualRelease): ?>
        <div class="app-banner app-banner-warning mb-3">
            <div><strong>Tabela não encontrada</strong><p>Execute as migrations para criar a tabela de mensalidades.</p></div>
        </div>
    <?php endif; ?>

    <?php if (!$asaasConfigured && $enabled && $canManualRelease): ?>
        <div class="app-banner app-banner-warning mb-3">
            <div>
                <strong>Pix automático não configurado</strong>
                <p>Configure ASAAS_API_KEY e ASAAS_CUSTOMER_ID no ambiente para gerar Pix automaticamente.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($state['message'])): ?>
        <div class="app-banner <?= !empty($state['blocked']) ? 'app-banner-danger' : 'app-banner-warning'; ?> mb-3">
            <div>
                <strong><?= !empty($state['blocked']) ? 'Acesso bloqueado' : 'Pagamento pendente'; ?></strong>
                <p><?= esc((string)$state['message']); ?></p>
            </div>
            <span class="badge text-bg-light">Prazo: <?= esc($nextBlockDate); ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-tile">
                <div class="label">Situação do sistema</div>
                <div class="value">
                    <?= !empty($state['blocked']) ? 'Bloqueado' : 'Liberado'; ?>
                </div>
                <small class="text-muted"><?= !empty($state['blocked']) ? 'Regularize a mensalidade para liberar.' : 'Sem bloqueio ativo no momento.'; ?></small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="status-tile">
                <div class="label">Débitos em aberto</div>
                <div class="value"><?= br_money($openAmount); ?></div>
                <small class="text-muted"><?= count($openCharges); ?> mensalidade(s) pendente(s) ou vencida(s).</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="status-tile">
                <div class="label">Mensalidades pagas</div>
                <div class="value"><?= br_money($paidAmount); ?></div>
                <small class="text-muted"><?= count($paidCharges); ?> pagamento(s) reconhecido(s).</small>
            </div>
        </div>
    </div>

    <?php if ($openCharges): ?>
        <div class="content-card p-3 mb-3">
            <div class="content-card-header">
                <div>
                    <h5>Débitos pendentes</h5>
                    <small>Mensalidades que ainda precisam de confirmação de pagamento.</small>
                </div>
            </div>
            <div class="row g-3">
                <?php foreach ($openCharges as $openCharge): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="content-card-soft h-100">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <strong><?= esc($monthRefLabel((string)$openCharge->month_ref)); ?></strong>
                                    <div class="text-muted small">Vence em <?= !empty($openCharge->due_date) ? esc(date('d/m/Y', strtotime((string)$openCharge->due_date))) : '-'; ?></div>
                                </div>
                                <span class="badge text-bg-<?= $statusBadge((string)$openCharge->status); ?>"><?= esc($statusLabel((string)$openCharge->status)); ?></span>
                            </div>
                            <div class="fs-4 fw-bold mt-3"><?= br_money($openCharge->amount ?? 0); ?></div>
                            <?php if (!empty($openCharge->invoice_url)): ?>
                                <a href="<?= esc((string)$openCharge->invoice_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success mt-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Pagar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($charge): ?>
        <div class="row g-3">
            <div class="col-12 col-lg-5">
                <div class="payment-box h-100">
                    <div class="content-card-header">
                        <div>
                            <h5>Débito em aberto</h5>
                            <small>Mensalidade pendente para conferência ou pagamento.</small>
                        </div>
                    </div>
                    <div class="client-help mb-3">
                        Gere o Pix ou use o link do Asaas quando disponível. Após o pagamento ser reconhecido, o status desta mensalidade será atualizado.
                    </div>
                    <p class="mb-2"><strong>Referência:</strong> <?= esc($monthRefLabel((string)$charge->month_ref)); ?></p>
                    <p class="mb-2"><strong>Valor:</strong> <?= br_money($charge->amount ?? 0); ?></p>
                    <p class="mb-2"><strong>Vencimento:</strong> <?= !empty($charge->due_date) ? esc(date('d/m/Y', strtotime((string)$charge->due_date))) : '-'; ?></p>
                    <p class="mb-2"><strong>Status:</strong> <?= esc($statusLabel((string)$charge->status)); ?></p>
                    <?php if (!empty($charge->paid_at)): ?>
                        <p class="mb-2"><strong>Pago em:</strong> <?= esc(date('d/m/Y H:i', strtotime((string)$charge->paid_at))); ?></p>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <?php if (!empty($charge->invoice_url)): ?>
                            <a class="btn btn-success" href="<?= esc((string)$charge->invoice_url); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-box-arrow-up-right"></i> Pagar agora
                            </a>
                        <?php endif; ?>
                        <form method="post" action="<?= url_to('subscription.regenerate'); ?>">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-qr-code"></i> Gerar Pix
                            </button>
                        </form>
                        <?php if (!empty($charge->asaas_payment_id)): ?>
                            <form method="post" action="<?= url_to('subscription.sync'); ?>">
                                <?= csrf_field(); ?>
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-repeat"></i> Verificar pagamento
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <?php if ($canManualRelease): ?>
                        <form method="post" action="<?= url_to('subscription.manualPaid'); ?>" class="mt-3">
                            <?= csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-unlock"></i> Liberar manualmente
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <div class="payment-box h-100">
                    <div class="content-card-header">
                        <div>
                            <h5>Pagamento por Pix</h5>
                            <small>Use o QR Code ou copie o código Pix.</small>
                        </div>
                    </div>
                    <?php if (!empty($charge->pix_qr_code)): ?>
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-md-5 text-center">
                                <img class="qr-image" src="data:image/png;base64,<?= esc((string)$charge->pix_qr_code); ?>" alt="QR Code Pix">
                            </div>
                            <div class="col-12 col-md-7">
                                <label class="form-label">Pix copia e cola</label>
                                <textarea class="form-control pix-code" readonly><?= esc((string)($charge->pix_payload ?? '')); ?></textarea>
                                <button type="button" class="btn btn-outline-secondary mt-2" id="copyPixBtn">
                                    <i class="bi bi-clipboard"></i> Copiar Pix
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="d-flex align-items-center gap-3">
                                <span class="empty-state-icon"><i class="bi bi-qr-code"></i></span>
                                <div>
                                    <h5>Pix ainda não gerado</h5>
                                    <p>Use o botão Gerar Pix para solicitar o QR Code desta mensalidade.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canManualRelease): ?>
        <div class="content-card p-3 mt-4">
            <div class="content-card-header">
                <div>
                    <h5>Configurações administrativas</h5>
                    <small>Somente superusuário. Estes valores sobrescrevem o `.env`.</small>
                </div>
            </div>
            <form class="row g-3 align-items-end" method="post" action="<?= url_to('subscription.settings'); ?>">
                <?= csrf_field(); ?>
                <div class="col-12 col-md-2">
                    <label class="form-label d-block">Controle ativo</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="subscriptionEnabled" name="enabled" value="1" <?= !empty($settings['enabled']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="subscriptionEnabled">Ativar cobrança/bloqueio</label>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Valor mensal</label>
                    <input type="text" class="form-control" name="amount" value="<?= esc(number_format((float)($settings['amount'] ?? 60), 2, ',', '.')); ?>" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Alertar a partir do dia</label>
                    <input type="number" class="form-control" name="warning_day" min="1" max="28" value="<?= esc((string)($settings['warning_day'] ?? 5)); ?>" required>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Dia de vencimento</label>
                    <input type="number" class="form-control" name="due_day" min="1" max="28" value="<?= esc((string)($settings['due_day'] ?? 10)); ?>" required>
                    <div class="form-text">Use 1 a 28 para evitar problemas em fevereiro.</div>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label">Tolerância após vencimento</label>
                    <input type="number" class="form-control" name="grace_days" min="0" max="60" value="<?= esc((string)($settings['grace_days'] ?? 2)); ?>" required>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="content-card p-3 mt-4">
        <div class="content-card-header">
            <div>
                <h5>Pagamentos e mensalidades</h5>
                <small>Mensalidades registradas, vencimentos e pagamentos reconhecidos.</small>
            </div>
        </div>
        <?php if ($charges): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle app-table">
                    <thead>
                        <tr>
                            <th>Referência</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($charges as $item): ?>
                            <tr>
                                <td data-label="Referência"><?= esc($monthRefLabel((string)$item->month_ref)); ?></td>
                                <td data-label="Valor" class="money-cell"><?= br_money($item->amount ?? 0); ?></td>
                                <td data-label="Vencimento"><?= !empty($item->due_date) ? esc(date('d/m/Y', strtotime((string)$item->due_date))) : '-'; ?></td>
                                <td data-label="Status"><span class="badge text-bg-<?= $statusBadge((string)$item->status); ?>"><?= esc($statusLabel((string)$item->status)); ?></span></td>
                                <td data-label="Pagamento"><?= !empty($item->paid_at) ? esc(date('d/m/Y H:i', strtotime((string)$item->paid_at))) : '-'; ?></td>
                                <td data-label="Link">
                                    <?php if (!empty($item->invoice_url) && (string)$item->status !== 'paid'): ?>
                                        <a href="<?= esc((string)$item->invoice_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success">
                                            Pagar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="d-flex align-items-center gap-3">
                    <span class="empty-state-icon"><i class="bi bi-receipt"></i></span>
                    <div>
                        <h5>Nenhuma mensalidade encontrada</h5>
                        <p>Quando a primeira cobrança for gerada, ela aparecerá neste histórico.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    (function () {
        const copyBtn = document.getElementById('copyPixBtn');
        if (!copyBtn) {
            return;
        }
        copyBtn.addEventListener('click', async function () {
            const code = document.querySelector('.pix-code');
            if (!code) {
                return;
            }
            try {
                await navigator.clipboard.writeText(code.value);
                copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
            } catch (error) {
                code.select();
                document.execCommand('copy');
                copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
            }
        });
    })();
</script>

<?php $this->endSection('content');?>
