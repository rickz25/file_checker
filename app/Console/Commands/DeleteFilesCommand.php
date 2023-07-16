<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use File;
use Excel;
use DB;
use App\Models\Imports;
use DateTime;
use Carbon\Carbon;
use Response;

class DeleteFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete_files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        date_default_timezone_set('Asia/Manila');
        $uri = storage_path('app/PROCESSED');
        $files = File::allFiles($uri);

        if(count($files)>0){
          
            foreach($files as $fi => $file){
                $file_extension = File::extension($file);
                $filename = basename($file);
                
                $time = Storage::lastModified('PROCESSED/'.$filename);
                $now = new DateTime();
                $datenow = $now->format('Y-m-d');
                $last_modified = gmdate("Y-m-d", $time);

                $date1=date_create($datenow);
                $date2=date_create($last_modified);
                $diff=date_diff($date2,$date1);
                $format =  $diff->format("%R%a");
                $date_old = ltrim($format,'+');
                if($date_old >= 7 ){
                    $this->delete_file($filename);
                }
            }
        }
    }

    public function delete_file($filename){

        ### REMOVE FILE IN PROCESSED FOLDER
        $processed = "PROCESSED/".$filename;
        Storage::delete($processed);
        ###
   }
}
