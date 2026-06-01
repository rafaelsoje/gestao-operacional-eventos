<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSubscriptionCharge extends Model
{
    protected $table = 'system_subscription_charges';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $protectFields = true;
    protected $allowedFields = [
        'month_ref',
        'amount',
        'due_date',
        'status',
        'asaas_payment_id',
        'asaas_customer_id',
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code',
        'pix_payload',
        'paid_at',
        'last_webhook_event',
        'last_sync_at',
        'raw_payload',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
