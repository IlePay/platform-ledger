<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Limites
            ['key' => 'limit_basic_daily', 'value' => '50000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite quotidienne BASIC'],
            ['key' => 'limit_basic_monthly', 'value' => '500000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite mensuelle BASIC'],
            ['key' => 'limit_standard_daily', 'value' => '500000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite quotidienne STANDARD'],
            ['key' => 'limit_standard_monthly', 'value' => '5000000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite mensuelle STANDARD'],
            ['key' => 'limit_premium_daily', 'value' => '5000000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite quotidienne PREMIUM'],
            ['key' => 'limit_premium_monthly', 'value' => '50000000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Limite mensuelle PREMIUM'],
            
            // Frais
            ['key' => 'merchant_commission', 'value' => '1.5', 'type' => 'number', 'category' => 'FEES', 'description' => 'Commission marchands (%)'],
            ['key' => 'transfer_fee_min', 'value' => '0', 'type' => 'number', 'category' => 'FEES', 'description' => 'Frais minimum transfert P2P'],
            ['key' => 'withdrawal_fee', 'value' => '0', 'type' => 'number', 'category' => 'FEES', 'description' => 'Frais retrait (%)'],
            
            // Features
            ['key' => 'feature_2fa_enabled', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => '2FA disponible'],
            ['key' => 'feature_qr_payments', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Paiements QR'],
            ['key' => 'feature_money_requests', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Demandes d\'argent'],
            ['key' => 'feature_refunds', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Remboursements'],
            
            // Email
            ['key' => 'email_support', 'value' => 'support@ilepay.com', 'type' => 'string', 'category' => 'EMAIL', 'description' => 'Email support'],
            ['key' => 'email_from_name', 'value' => 'IlePay', 'type' => 'string', 'category' => 'EMAIL', 'description' => 'Nom expéditeur'],

            // Features additionnelles
            ['key' => 'feature_multi_currency', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Support multi-devises'],
            ['key' => 'feature_email_notifications', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Notifications email'],
            ['key' => 'feature_export_transactions', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Export transactions (PDF/CSV)'],
            ['key' => 'feature_merchant_refunds', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Remboursements marchands'],
            ['key' => 'feature_custom_commission', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Commission personnalisée'],
            ['key' => 'feature_kyc_verification', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Vérification KYC'],
            ['key' => 'feature_fraud_detection', 'value' => '1', 'type' => 'boolean', 'category' => 'FEATURES', 'description' => 'Détection fraude'],

            // Limites système
            ['key' => 'max_transaction_amount', 'value' => '10000000', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Montant max transaction (XAF)'],
            ['key' => 'min_transaction_amount', 'value' => '100', 'type' => 'number', 'category' => 'LIMITS', 'description' => 'Montant min transaction (XAF)'],

            // Maintenance
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'category' => 'GENERAL', 'description' => 'Mode maintenance'],
            ['key' => 'maintenance_message', 'value' => 'IlePay est en maintenance. Merci de revenir plus tard.', 'type' => 'string', 'category' => 'GENERAL', 'description' => 'Message maintenance'],
                    
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}