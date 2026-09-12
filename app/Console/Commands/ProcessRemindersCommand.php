<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class ProcessRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automated business alerts, scheduled reminders, and subscription notices';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $this->info('Starting automated reminders and business alerts processing...');

        $results = $reminderService->processScheduledReminders();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Stores Processed', $results['stores_processed']],
                ['Low Stock Alerts', $results['low_stock']],
                ['Out of Stock Alerts', $results['out_of_stock']],
                ['Expiring Batch Alerts', $results['expiring_batch']],
                ['Expired Batch Alerts', $results['expired_batch']],
                ['Customer Outstanding Notices', $results['customer_outstanding']],
                ['Supplier Outstanding Notices', $results['supplier_outstanding']],
                ['Subscription Alerts', $results['subscription_alerts']],
                ['Scheduled Dispatched', $results['scheduled_dispatched']],
            ]
        );

        $this->info('Reminders and business alerts processed successfully.');

        return Command::SUCCESS;
    }
}
