<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEnrollment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:syncenrollment {tahun : tahun akademik ex 20225}';

    public $tahunAkademik;

    public $tahun;

    public $smt;
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
        $this->tahunAkademik=$this->argument('tahun');

        $this->tahun=substr($this->tahunAkademik,0,strlen($this->tahunAkademik)-1);

        $this->smt=substr($this->tahunAkademik,strlen($this->tahunAkademik)-1,1);

        $academicYearID=$this->getTahunAkademik($this->tahun,$this->smt);

        return 0;
    }

    private function getTahunAkademik($tahun,$smt){
        $academicYear=DB::connection('source_sqlsrv')
                        ->table('AcademicYearID')
                        ->where('Tahun',$tahun)
                        ->where('SemesterID',$smt)
                        ->first();

        if(!$academicYear){
            $this->error('Tahun Akademik Tidak ditemukan');
            return Command::FAILURE;
        }

        if(!$academicYear->moodle_sync_active){
            $this->error('Sinkronisasi moodle pada Tahun Akademik Tidak diaktifkan');
            return Command::FAILURE;
        }
        return $academicYear;

    }
}
