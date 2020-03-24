<?php

namespace IPX\DbCopy\Console;

use Illuminate\Console\Command;

class CopyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:copy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies your production database to your development environment.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!$this->confirmWipe())
        {
            return;
        }

        $dump = $this->dumpProductionDB();
        $this->wipeDevelopmentDB();
        $this->importDumpToDevelopmentDB($dump);
    }   

    public function confirmWipe()
    {
        $this->info(">>>> This command will wipe your development database.\n>>>> Your development data will be deleted.");
        if(!$this->confirm('Do you wish to continue?',true))
        {
            $this->info("Process terminated by user");
            return false;
        }
        return true;
    }

    public function dumpProductionDB()
    {
        $username = env('PRODUCTION_DB_USERNAME');
        $password = env('PRODUCTION_DB_PASSWORD');
        $database = env('PRODUCTION_DB_DATABASE');
        $host = env('PRODUCTION_DB_HOST');
        $options = "--host {$host} -u {$username} -p{$password} {$database}";
        $dumpDir = "packages/db-copy/dump.sql";
        shell_exec("mysqldump {$options} > {$dumpDir}");
        return $dumpDir;
    }

    public function wipeDevelopmentDB()
    {
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');
        $host = env('DB_HOST');

        exec("mysql -u {$username} -p{$password} -h {$host} -e'show tables;' {$database}", $tables);
        exec("mysql -u {$username} -p{$password} -h {$host} -e'SET FOREIGN_KEY_CHECKS = 0;' {$database}");

        array_shift($tables);

        foreach($tables as $table)
        {
            exec("mysql -u {$username} -p{$password} -h {$host} -e'DROP TABLE IF EXISTS {$table};' {$database}");
        }
        exec("mysql -u {$username} -p{$password} -h {$host} -e'SET FOREIGN_KEY_CHECKS = 1;' {$database}");
    }

    public function importDumpToDevelopmentDB($dump)
    {
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');
        $host = env('DB_HOST');
        shell_exec("mysql -u {$username} -p{$password} {$database} < {$dump}");
    }
}
