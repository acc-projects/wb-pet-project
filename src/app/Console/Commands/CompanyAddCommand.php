<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class CompanyAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:add
                            {name : The name of the company}
                            {--email= : Contact email}
                            {--description= : Company description}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new company to the system';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');
        $email = $this->option('email');
        $description = $this->option('description');

        if (Company::where('name', $name)->exists()) {
            $this->error("Company with name '{$name}' already exists!");
            return;
        }

        if (!$email) {
            $email = $this->ask('Please enter contact email (optional)');
        }

        if (!$description) {
            $description = $this->ask('Please enter company description (optional)');
        }

        try {
            $company = Company::create([
                'name' => $name,
                'contact_email' => $email,
                'description' => $description,
            ]);

            $contactEmail = $company->contact_email ?: 'Not set';
            $description = $company->description ?: 'Not set';

            $this->info("✅ Company '{$company->name}' created successfully!");
            $this->line("   ID: {$company->id}");
            $this->line("   Email: {$contactEmail}");
            $this->line("   Description: {$description}");

        } catch (\Exception $e) {
            $this->error("Failed to create company: " . $e->getMessage());
        }
    }
}
