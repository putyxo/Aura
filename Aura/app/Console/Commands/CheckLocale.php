<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckLocale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:check {--user_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check which locale is being applied (user, session, config)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user_id');

        $userLang = null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            $userLang = $user?->idioma;
        }

        $sessionLang = Session::get('locale');
        $defaultLang = config('app.locale');

        $this->info('🔎 Locale check:');
        $this->line(" - User ID: " . ($userId ?: 'not provided'));
        $this->line(" - User idioma: " . ($userLang ?? 'null'));
        $this->line(" - Session locale: " . ($sessionLang ?? 'null'));
        $this->line(" - Default (config/app.php): " . $defaultLang);
        $this->line(" - Final applied: " . App::getLocale());
    }
}
