<?php

namespace Wilbere\BagistoManualPayments\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'manual-payments:install';

    protected $description = 'Instala y publica los archivos del paquete wilbere/bagisto-manual-payments.';

    public function handle(): int
    {
        $this->info('Iniciando instalación de Bagisto Manual Payments...');

        $this->call('vendor:publish', [
            '--tag' => 'manual-payments-config',
            '--force' => true,
        ]);

        $this->info('Ejecutando migraciones de base de datos...');
        $this->call('migrate');

        $this->info('¡Instalación completada con éxito!');

        return self::SUCCESS;
    }
}
