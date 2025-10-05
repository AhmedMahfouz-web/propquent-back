<?php

namespace App\Console\Commands;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : The email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test password reset email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (!$email) {
            $email = $this->ask('Enter email address to send test email to');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address format');
            return 1;
        }

        $this->info('Testing email configuration...');
        $this->info('Sending to: ' . $email);

        try {
            // Create a test user object
            $testUser = new User([
                'full_name' => 'Test User',
                'email' => $email,
            ]);
            $testUser->password_reset_expires_at = now()->addHour();

            $testToken = 'TEST-' . bin2hex(random_bytes(16));
            $resetUrl = config('app.frontend_url', 'https://properquant.net') . '/reset-password/' . $testToken;

            // Send test email
            Mail::to($email)->send(new ResetPasswordMail($testUser, $testToken, $resetUrl));

            $this->info('✓ Email sent successfully!');
            $this->info('Please check the inbox for: ' . $email);
            
            $this->newLine();
            $this->info('Email Configuration:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Mailer', config('mail.default')],
                    ['Host', config('mail.mailers.smtp.host')],
                    ['Port', config('mail.mailers.smtp.port')],
                    ['Encryption', config('mail.mailers.smtp.encryption')],
                    ['Username', config('mail.mailers.smtp.username')],
                    ['From Address', config('mail.from.address')],
                    ['From Name', config('mail.from.name')],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Failed to send email');
            $this->error('Error: ' . $e->getMessage());
            
            $this->newLine();
            $this->warn('Troubleshooting steps:');
            $this->line('1. Check your .env file has correct SMTP credentials');
            $this->line('2. Run: php artisan config:clear');
            $this->line('3. Verify port 465 is not blocked by firewall');
            $this->line('4. Check storage/logs/laravel.log for detailed errors');

            return 1;
        }
    }
}
