<?php

namespace App\Console\Commands;

use App\Models\InternetPlan;
use App\Models\Voucher;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateVouchers extends Command
{
    protected $signature = 'hotspot:generate-vouchers 
                          {plan_id : The ID of the internet plan}
                          {quantity=10 : Number of vouchers to generate}
                          {--length=8 : Length of voucher code}
                          {--prefix= : Prefix for voucher codes}';

    protected $description = 'Generate vouchers for a specific internet plan';

    public function handle()
    {
        $planId = $this->argument('plan_id');
        $quantity = $this->argument('quantity');
        $length = $this->option('length');
        $prefix = $this->option('prefix');

        // Validate plan
        $plan = InternetPlan::find($planId);
        if (!$plan) {
            $this->error('Internet plan not found.');
            return 1;
        }

        if (!$plan->is_active) {
            $this->error('Internet plan is not active.');
            return 1;
        }

        $this->info("Generating {$quantity} vouchers for plan: {$plan->name}");

        $bar = $this->output->createProgressBar($quantity);
        $vouchers = [];
        $codes = [];

        // Get existing codes
        $existingCodes = Voucher::pluck('code')->toArray();

        for ($i = 0; $i < $quantity; $i++) {
            // Generate unique code
            do {
                $code = $prefix . strtoupper(Str::random($length));
            } while (in_array($code, $codes) || in_array($code, $existingCodes));

            $codes[] = $code;
            $vouchers[] = [
                'code' => $code,
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'validity_days' => $plan->validity_days,
                'is_used' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $bar->advance();
        }

        // Insert vouchers in chunks
        foreach (array_chunk($vouchers, 100) as $chunk) {
            Voucher::insert($chunk);
        }

        $bar->finish();
        $this->newLine();
        $this->info('Vouchers generated successfully.');

        return 0;
    }
}
