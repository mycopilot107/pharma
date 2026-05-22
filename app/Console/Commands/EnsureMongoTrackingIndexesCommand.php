<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

class EnsureMongoTrackingIndexesCommand extends Command
{
    protected $signature = 'tracking:mongo-indexes';

    protected $description = 'Create MongoDB indexes for GPS tracking collections';

    public function handle(): int
    {
        $connection = config('tracking.mongodb_connection', 'mongodb');

        Schema::connection($connection)->create('location_pings', function (Blueprint $collection) {
            $collection->index(['company_id' => 1, 'user_id' => 1, 'recorded_at' => -1]);
            $collection->index(['user_id' => 1, 'recorded_at' => -1]);
            $collection->index(['recorded_at' => -1]);
        });

        Schema::connection($connection)->create('geofence_events', function (Blueprint $collection) {
            $collection->index(['company_id' => 1, 'user_id' => 1, 'recorded_at' => -1]);
            $collection->index(['customer_id' => 1, 'event_type' => 1]);
        });

        $this->info('MongoDB tracking indexes ensured on connection: '.$connection);

        return self::SUCCESS;
    }
}
