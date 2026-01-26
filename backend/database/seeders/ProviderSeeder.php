<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        // idempotent: keyで upsert
        DB::table('providers')->updateOrInsert(
            ['key' => 'stripe'],
            [
                'project_id' => null,
                'display_name' => 'Stripe',
                'provider_type' => 'psp',
                'status' => 'active',
                'website_url' => 'https://stripe.com',
                'support_url' => 'https://support.stripe.com',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}