<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncGroup extends Command
{
    public $extternalIDNumberGroupField;
    public $extternalIDNumberCourseField;
    public $extternalGroupNameField;
    public $extternalTahunAkademikField;
    public $extternalIDNumberUserField;

    public $enrollmentCommand;
    public $clearCacheCommand;

    public $course;
    public $message=[];

    public $courseIDNumber;
    public $courseShortName;
    public $academicYearID;
    public $groupName;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:group
                                {academicYearID : id tahun akademik}
                                {groupName? : nama grup atau kelas optional}
                                {--courseIDNumber= : id number course}
                                {--shortname= : label pada course }
                                {--a|all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'syncronize moodle group ';
    public function __construct()
    {
        $this->extternalIDNumberGroupField=config('moodle.external_enrollment.id_number_group_field');
        $this->extternalIDNumberCourseField=config('moodle.external_enrollment.id_number_course_field');
        $this->extternalGroupNameField=config('moodle.external_enrollment.group_name_field');
        $this->extternalTahunAkademikField=config('moodle.external_enrollment.tahun_akademik_field');
        $this->extternalIDNumberUserField=config('moodle.external_enrollment.id_number_user_field');
        $this->enrollmentCommand=config('moodle.external_enrollment.executeable_path');
        $this->clearCacheCommand=config('moodle.clear_cache.executeable_path');

        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->academicYearID=$this->argument('academicYearID');
        $this->courseIDNumber=$this->option('courseIDNumber');
        $this->groupName=$this->argument('groupName');
        $this->courseShortName=$this->option('shortname');

        $this->setCourse();
        $srcData=$this->withProgressBar($this->setSourceData(),function($srcData){
            $this->courseIDNumber=$srcData->{$this->extternalIDNumberCourseField};
            $this->setCourse();

            if($this->syncGroup($srcData)){
                 $this->syncGroupMember($srcData);
            }
        });
        $this->showMessage();
        $this->runMoodleCommand($this->clearCacheCommand);
        $this->info('Command finish');

        return Command::SUCCESS;
    }

    public function setCourse(){
        if(!empty($this->courseIDNumber)){
            $this->course=Course::where('idnumber',$this->courseIDNumber)->first();
        }elseif(!empty($this->courseShortName)){
            $this->course=Course::where('shortname',$this->courseShortName)->first();
        }
    }

    public function setSourceData(){
        $res=DB::connection('source_sqlsrv')
        ->table('MoodleEnrollment3')
        ->where('academicYearID',$this->academicYearID)
        ->where('role',5);
        /**
         * jika onject course ada atau option --a
         **/
        if($this->course){
            $this->info('Course id: '.$this->course->id.' idnumber: '.$this->course->idnumber." fullname: ".$this->course->fullname." shortname:".$this->course->shortname,'v');
            $res->where('idNumberCourse',$this->course->idnumber);
        }

        if(!empty($this->groupName)){
            $res->where('groupName',$this->groupName);
        }

        return $res->get();
    }

    public function syncGroup($externalEnrollment){

        if(Group::where('idnumber',$externalEnrollment->{$this->extternalIDNumberGroupField})->exists()){
            $this->info('Group with idnumber: '.$externalEnrollment->{$this->extternalIDNumberGroupField}.' groupname:'.$externalEnrollment->{$this->extternalGroupNameField}.' academicYearID: '.$externalEnrollment->{$this->extternalTahunAkademikField}." Exist");
            return true;

        }elseif(!$this->course){
            $this->msg('Course with idnumber: '.$externalEnrollment->{$this->extternalIDNumberCourseField}."doesn't Exist");
        }else{
            $group=Group::firstOrCreate(
                ['idnumber'=>$externalEnrollment->{$this->extternalIDNumberGroupField},'name'=>$externalEnrollment->{$this->extternalGroupNameField}],
                ['courseid'=>$this->course->id,'descriptionformat'=>1]
            );

            if($group){
                $this->info('Grup idnumber:'.$group->idnumber.'grupname :'.$group->name.'course: '.$group->courseid."tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField}."sukses disinkron");
                return true;
            }
        }
        return false;

    }

    public function syncGroupMember($externalEnrollment){
        $user=User::where('idnumber',$externalEnrollment->{$this->extternalIDNumberUserField})->first();
        $group=Group::where('idnumber',$externalEnrollment->{$this->extternalIDNumberGroupField})->first();
        if(!$user){
            $this->msg('user with idnumber: '.$externalEnrollment->{$this->extternalIDNumberUserField}."doesn't Exist");
        }elseif(!$group){
            $this->msg('Group with idnumber: '.$externalEnrollment->{$this->extternalIDNumberGroupField}.'groupname:'.$externalEnrollment->{$this->extternalGroupNameField}.'academicYearID:'.$externalEnrollment->{$this->extternalTahunAkademikField}." doesn't Exist");
        }else{
            $user->groups()->sync($group->id);
            $userGroup=$user->groups()->where('groupid',$group->id);
            if($userGroup) $this->info('User username:'.$user->username.'useridnumber:'.$user->idnumber.'berhasil ditambahkan ke grup grupidnumber:'.$group->idnumber.'grupname :'.$group->name.'course: '.$group->courseid."tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField});
            else $this->msg('User username:'.$user->username.'useridnumber:'.$user->idnumber.'gagal ditambahkan ke grup grupidnumber:'.$group->idnumber.'grupname :'.$group->name.'course: '.$group->courseid."tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField});
        }
    }

    public function info($string, $verbosity = null)
    {
        parent::info($string,'v');
    }

    private function msg($string)
    {
        array_push($this->message,$string);
    }

    public function showMessage(){

        if(!empty($this->message)) $this->line('Command finish with following error :');
        foreach($this->message as $k => $v){
            $this->error($v);
        }
    }

    private function runMoodleCommand($executablePath){
        if(file_exists($executablePath)){
            exec('php '.$executablePath);
        }
    }

}
