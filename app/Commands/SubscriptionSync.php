<?php

namespace App\Commands;

use App\Services\SystemSubscriptionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SubscriptionSync extends BaseCommand
{
    protected $group = 'Subscription';
    protected $name = 'subscription:sync';
    protected $description = 'Gera e sincroniza a cobrança mensal da assinatura do sistema.';

    public function run(array $params): void
    {
        $service = new SystemSubscriptionService();

        if (!$service->isEnabled()) {
            CLI::write('Assinatura desativada.', 'yellow');
            return;
        }

        if (!$service->tableReady()) {
            CLI::error('Tabela system_subscription_charges não encontrada. Execute php spark migrate.');
            return;
        }

        $charge = $service->payableCharge(true);
        if (!$charge) {
            CLI::error('Não foi possível localizar ou criar cobrança.');
            return;
        }

        if (!empty($charge->asaas_payment_id)) {
            $service->syncCharge($charge);
        }

        $state = $service->subscriptionState();
        $status = (string)($state['charge']->status ?? $charge->status ?? 'pending');
        $monthRef = (string)($state['charge']->month_ref ?? $charge->month_ref ?? '');

        CLI::write('Assinatura sincronizada: ' . $monthRef . ' / status=' . $status, 'green');
    }
}
