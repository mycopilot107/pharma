<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\ReminderService;
use Illuminate\Console\Command;

class SyncRemindersCommand extends Command
{
    protected $signature = 'reminders:sync {--company= : Company ID to sync}';

    protected $description = 'Sync follow-ups, meetings, targets and doctor revisit reminders';

    public function handle(ReminderService $reminders): int
    {
        $companyId = $this->option('company');

        if ($companyId) {
            $reminders->syncForCompany((int) $companyId);
            $this->info("Reminders synced for company {$companyId}.");

            return self::SUCCESS;
        }

        Company::where('status', 'active')->each(function (Company $company) use ($reminders) {
            $reminders->syncForCompany($company->id);
        });

        $this->info('Reminders synced for all active companies.');

        return self::SUCCESS;
    }
}
