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
        $this->info("Done.");
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
        $ssh_username = env('SSH_USERNAME');
        $dumpDir = "packages/db-copy/dump.sql";
        $options = "-u {$username} -p{$password} {$database}";
        if($ssh_username)
        {
            $this->info("Creating SQL dump from {$ssh_username}@{$host} over SSH...");
            exec("ssh {$ssh_username}@{$host} 'mysqldump {$options}' > {$dumpDir} 2> /dev/null");
        }
        else
        {
            $this->info("Creating SQL dump from {$host}...");
            exec("mysqldump -h {$host} {$options} > {$dumpDir} 2> /dev/null");
        }
        $this->info("SQL dump created.");
        return $dumpDir;
    }

    public function wipeDevelopmentDB()
    {
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');
        $host = env('DB_HOST');

        exec("mysql -u {$username} -p{$password} -h {$host} -e'show tables;' {$database} 2> /dev/null", $tables);
        array_shift($tables);

        $this->info("Wiping development database...");

        foreach($tables as $table)
        {
            exec("mysql -u {$username} -p{$password} -h {$host} -e'SET FOREIGN_KEY_CHECKS = 0;DROP TABLE IF EXISTS {$table};SET FOREIGN_KEY_CHECKS = 1;' {$database} 2> /dev/null");
        }
    }

    public function importDumpToDevelopmentDB($dump)
    {
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');
        $host = env('DB_HOST');
        $this->info("Importing dump into development database...");
        exec("mysql -u {$username} -p{$password} {$database} < {$dump} 2> /dev/null");
        exec("rm " . $dump);
    }
}
