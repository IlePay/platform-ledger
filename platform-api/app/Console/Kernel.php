protected function schedule(Schedule $schedule): void
{
    // Traiter les paiements récurrents chaque jour à 8h
    $schedule->command('recurring:process')->dailyAt('08:00');
}
