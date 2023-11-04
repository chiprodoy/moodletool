<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveUserDouble extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:removedoubleuser {--username=} {--mhs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $mhsOnly=true;

    public $userName;


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
        $this->userName=$this->option('username');

        $userSisfo=$this->getUserSisfo();
        foreach($userSisfo as $k =>$v){

            $userMoodel=$this->getUserMoodle($v->Userid);
            dd($userMoodel->count());
            foreach($userMoodel as $x => $um){
                if($um->username==$v->UserCode){
                    $this->info();
                }
            }

        }
        return 0;
    }

     /**
     * Execute the console command.
     *
     * @return int
     */
    public function getUserSisfo()
    {
        //cari user mahasiswa di sisfo
        $userMhsSisfo=DB::connection('source_sqlsrv')->table('UserMaster');

        if($this->mhsOnly){
            $userMhsSisfo->where('UserCatogoriesID',1);
        }

        if(!empty($this->userName)){
            $userMhsSisfo->where('UserCode',$this->userName);
        }

        return $userMhsSisfo->get();

    }

     /**
     * Execute the console command.
     *
     * @return int
     */
    public function getUserMoodle($idnumber)
    {
        //cari user mahasiswa di sisfo
        $userMoodle=User::where('idnumber',$idnumber);

        return $userMoodle->get();

    }

}
