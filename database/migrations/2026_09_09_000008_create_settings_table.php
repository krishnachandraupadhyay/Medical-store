<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, file, json
            $table->string('group')->default('general'); // general, branding, contact, system, security, notification, subscription
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('group');
        });

        // Insert initial baseline system settings
        $defaults = [
            // General
            ['key' => 'app_name', 'value' => 'MEDISTORE', 'type' => 'string', 'group' => 'general', 'label' => 'Application Name', 'description' => 'Global SaaS platform name.'],
            ['key' => 'app_tagline', 'value' => 'Next-Gen Medical Store & Pharmacy SaaS Platform', 'type' => 'string', 'group' => 'general', 'label' => 'Tagline', 'description' => 'Platform branding tagline.'],
            ['key' => 'default_timezone', 'value' => 'Asia/Kolkata', 'type' => 'string', 'group' => 'general', 'label' => 'Default Timezone', 'description' => 'System default timezone.'],
            ['key' => 'default_currency', 'value' => 'INR', 'type' => 'string', 'group' => 'general', 'label' => 'Default Currency', 'description' => 'Primary platform currency.'],
            ['key' => 'currency_symbol', 'value' => '₹', 'type' => 'string', 'group' => 'general', 'label' => 'Currency Symbol', 'description' => 'Symbol used for formatted currency displays.'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general', 'label' => 'Date Format', 'description' => 'Default display format for dates.'],
            ['key' => 'time_format', 'value' => 'h:i A', 'type' => 'string', 'group' => 'general', 'label' => 'Time Format', 'description' => 'Default display format for time.'],

            // Branding
            ['key' => 'app_logo', 'value' => null, 'type' => 'file', 'group' => 'branding', 'label' => 'Application Logo', 'description' => 'Primary header logo.'],
            ['key' => 'app_favicon', 'value' => null, 'type' => 'file', 'group' => 'branding', 'label' => 'Favicon', 'description' => 'Browser tab favicon.'],
            ['key' => 'login_logo', 'value' => null, 'type' => 'file', 'group' => 'branding', 'label' => 'Login Page Logo', 'description' => 'Logo displayed on authentication portal.'],

            // Contact
            ['key' => 'support_email', 'value' => 'support@medistore.io', 'type' => 'string', 'group' => 'contact', 'label' => 'Support Email', 'description' => 'Platform public support contact email.'],
            ['key' => 'support_phone', 'value' => '+91 9876543210', 'type' => 'string', 'group' => 'contact', 'label' => 'Support Phone', 'description' => 'Platform support contact phone number.'],
            ['key' => 'company_name', 'value' => 'Medistore Technologies Pvt Ltd', 'type' => 'string', 'group' => 'contact', 'label' => 'Company Name', 'description' => 'Legal SaaS vendor entity name.'],
            ['key' => 'company_website', 'value' => 'https://medistore.io', 'type' => 'string', 'group' => 'contact', 'label' => 'Website', 'description' => 'Official website URL.'],
            ['key' => 'company_address', 'value' => '1204 Tech Hub, Outer Ring Road, Bangalore, Karnataka, 560103', 'type' => 'string', 'group' => 'contact', 'label' => 'Corporate Address', 'description' => 'Headquarters physical address.'],

            // System
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system', 'label' => 'Maintenance Mode', 'description' => 'Enable maintenance mode for non-super-admin users.'],
            ['key' => 'maintenance_message', 'value' => 'MEDISTORE is undergoing scheduled system upgrades. We will be back online shortly.', 'type' => 'string', 'group' => 'system', 'label' => 'Maintenance Message', 'description' => 'Displayed to users during maintenance.'],
            ['key' => 'enable_store_registration', 'value' => '1', 'type' => 'boolean', 'group' => 'system', 'label' => 'Enable New Store Registration', 'description' => 'Permit new store provisioning.'],
            ['key' => 'enable_store_owner_creation', 'value' => '1', 'type' => 'boolean', 'group' => 'system', 'label' => 'Enable Store Owner Creation', 'description' => 'Permit creating new store owner accounts.'],

            // Security
            ['key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'label' => 'Session Timeout (Minutes)', 'description' => 'Inactivity duration before automatic logout.'],
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'group' => 'security', 'label' => 'Password Minimum Length', 'description' => 'Minimum required character length for passwords.'],
            ['key' => 'require_strong_password', 'value' => '0', 'type' => 'boolean', 'group' => 'security', 'label' => 'Require Strong Password', 'description' => 'Require mix of uppercase, numbers, and symbols.'],
            ['key' => 'login_attempt_protection', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'label' => 'Login Rate Limiting', 'description' => 'Throttle brute-force login attempts.'],
            ['key' => 'enable_audit_logging', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'label' => 'Audit Logging', 'description' => 'Log platform administrative activities.'],

            // Notifications
            ['key' => 'enable_in_app_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'notification', 'label' => 'In-App Notifications', 'description' => 'Enable internal notification center.'],
            ['key' => 'enable_email_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'notification', 'label' => 'Email Notifications', 'description' => 'Enable platform email delivery availability.'],
            ['key' => 'enable_sms_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'notification', 'label' => 'SMS Notifications', 'description' => 'Enable SMS delivery availability.'],
            ['key' => 'enable_whatsapp_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'notification', 'label' => 'WhatsApp Notifications', 'description' => 'Enable WhatsApp delivery availability.'],

            // Subscriptions
            ['key' => 'default_trial_days', 'value' => '14', 'type' => 'integer', 'group' => 'subscription', 'label' => 'Default Trial Days', 'description' => 'Standard free trial period in days.'],
            ['key' => 'allow_trial', 'value' => '1', 'type' => 'boolean', 'group' => 'subscription', 'label' => 'Allow Free Trial', 'description' => 'Allow new tenant stores to start on a free trial.'],
            ['key' => 'allow_new_subscriptions', 'value' => '1', 'type' => 'boolean', 'group' => 'subscription', 'label' => 'Allow New Subscriptions', 'description' => 'Allow assigning new subscriptions.'],
            ['key' => 'subscription_expiry_warning_days', 'value' => '7', 'type' => 'integer', 'group' => 'subscription', 'label' => 'Expiry Warning Threshold (Days)', 'description' => 'Days prior to expiration when alert is flagged.'],
        ];

        $now = now();
        foreach ($defaults as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('settings')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
