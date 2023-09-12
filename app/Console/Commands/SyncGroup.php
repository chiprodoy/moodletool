<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public $paramCourseIDNumber;
    public $paramCourseShortName;
    public $paramAcademicYearID;
    public $paramGroupName;
    public $paramUserIDNumber;
    public $paramVerbose;
    public $paramAction;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:syncgroup
                                {action : action check or create}
                                {academicYearID? : id tahun akademik}
                                {groupName? : nama grup atau kelas optional}
                                {--courseIDNumber= : id number course}
                                {--shortname= : label pada course }
                                {--useridnumber= : userid number }

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

    public function syncGroup($idNumberCourse,$groupIDNumber,$groupName){

        if($this->paramVerbose){
            $groupParam=[
                'action'=>'create',
                'courseIDNumber'=>$idNumberCourse,
                'groupIDNumber'=>$groupIDNumber,
                'groupName'=>$groupName,
                '-v'=>true,
            ];
        }else{
            $groupParam=[
                'action'=>'create',
                'courseIDNumber'=>$idNumberCourse,
                'groupIDNumber'=>$groupIDNumber,
                'groupName'=>$groupName
            ];
        }
       return $this->call('moodle:group',$groupParam);
    }

    public function syncGroupMember($idNumberCourse,$userIDNumber,$groupName){

        if($this->paramVerbose){
            $groupParam=[
                'action'=>'create',
                'groupname'=>$groupName,
                'courseidnumber'=>$idNumberCourse,
                '--uidn'=>$userIDNumber,
                '-v'=>true,
            ];
        }else{
            $groupParam=[
                'action'=>'create',
                'groupname'=>$groupName,
                'courseidnumber'=>$idNumberCourse,
                '--uidn'=>$userIDNumber
            ];
        }
       return $this->call('moodle:groupmember',$groupParam);
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(empty($this->argument('academicYearID'))){
            $this->paramAcademicYearID=config('moodle.tahun_akademik');
        }else{
            $this->paramAcademicYearID=$this->argument('academicYearID');
        }

        $srcData=$this->withProgressBar($this->setSourceData(),function($srcData){
           // if($srcData->idNumberCourse=='20222-12203-31-0'){
                $this->syncGroup($srcData->idNumberCourse,$srcData->idNumberGroup,$srcData->groupName);
                $this->syncGroupMember($srcData->idNumberCourse,$srcData->idNumber,$srcData->groupName);
           // }
        });
        // $this->paramCourseIDNumber=$this->option('courseIDNumber');
        // $this->paramGroupName=$this->argument('groupName');
        // $this->paramCourseShortName=$this->option('shortname');
        // $this->paramUserIDNumber=$this->option('useridnumber');
        // $this->paramVerbose=$this->option('verbose');
        // $this->paramAction=$this->argument('action');
        // //$this->setCourse();

        // $srcData=$this->withProgressBar($this->setSourceData(),function($srcData){
        //     if($this->paramVerbose){
        //         $param=[
        //             'action' => $this->paramAction,
        //             'groupname'=>$srcData->groupName,
        //             'courseidnumber'=>$srcData->idNumberCourse,
        //             '--uidn'=>$srcData->idNumber,
        //             '-v'=>true,
        //         ];
        //     }else{
        //         $param=[
        //             'action' => $this->paramAction,
        //             'groupname'=>$srcData->groupName,
        //             'courseidnumber'=>$srcData->idNumberCourse,
        //             '--uidn'=>$srcData->idNumber,
        //         ];
        //     }

        //     $this->call('moodle:groupmember',$param);
           // $this->courseIDNumber=$srcData->{$this->extternalIDNumberCourseField};
            //$this->setCourse();

           // if($this->syncGroup($srcData)){
            //     $this->syncGroupMember($srcData);
           // }
       // });
       // $this->showMessage();
       // $this->runMoodleCommand($this->clearCacheCommand);
       // $this->info('Command finish');
        Log::info('Sync Group Running');
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
        ->where('academicYearID',$this->paramAcademicYearID);

        /**
         * jika onject course ada atau option --a
         **/
        if(!empty($this->paramCourseIDNumber)){
            $res->where('idNumberCourse',$this->paramCourseIDNumber);
        }

        if(!empty($this->paramGroupName)){
            $res->where('groupName',$this->paramGroupName);
        }

        if(!empty($this->paramUserIDNumber)){
            $res->where('idNumber',$this->paramUserIDNumber);
        }

        $this->info($res->get(),'v');
        return $res->get();
    }
/**
    public function syncGroup($externalEnrollment){
        $this->info('External idNumberCourse:'.$externalEnrollment->idNumberCourse.' user idnumber :'.$externalEnrollment->idNumber.' role: '.$externalEnrollment->role." idNumberGroup: ".$externalEnrollment->idNumberGroup." groupName: ".$externalEnrollment->groupName." tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField});

        if(Group::where('idnumber',$externalEnrollment->{$this->extternalIDNumberGroupField})
        ->where('name',$externalEnrollment->{$this->extternalGroupNameField})
        ->where('courseid',$this->course->id)
        ->exists()){
            $this->info('Group with idnumber: '.$externalEnrollment->{$this->extternalIDNumberGroupField}.' groupname:'.$externalEnrollment->{$this->extternalGroupNameField}.' academicYearID: '.$externalEnrollment->{$this->extternalTahunAkademikField}." Exist");
            return true;

        }elseif(!$this->course){
            $this->msg('Course with idnumber: '.$externalEnrollment->{$this->extternalIDNumberCourseField}."doesn't Exist");
        }
            $group=Group::updateOrCreate(
                ['idnumber'=>$externalEnrollment->{$this->extternalIDNumberGroupField},'name'=>$externalEnrollment->{$this->extternalGroupNameField}],
                ['courseid'=>$this->course->id,'descriptionformat'=>1]
            );

            if($group){

                $this->info('Grup idnumber:'.$group->idnumber.'grupname :'.$group->name.'course: '.$group->courseid."tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField}."sukses disinkron");
                return true;
            }

        return false;

    }
**/
/*     public function syncGroupMember($externalEnrollment){
        $user=User::where('idnumber',$externalEnrollment->{$this->extternalIDNumberUserField})->first();
        $group=Group::where('idnumber',$externalEnrollment->{$this->extternalIDNumberGroupField})->first();
        if(!$user){
            $this->msg('user with idnumber: '.$externalEnrollment->{$this->extternalIDNumberUserField}."doesn't Exist");
        }elseif(!$group){
            $this->msg('Group with idnumber: '.$externalEnrollment->{$this->extternalIDNumberGroupField}.'groupname:'.$externalEnrollment->{$this->extternalGroupNameField}.'academicYearID:'.$externalEnrollment->{$this->extternalTahunAkademikField}." doesn't Exist");
        }else{
            $user->groups()->sync($group->id);
            $userGroup=$user->groups()->where('groupid',$group->id);
            if($userGroup) $this->info('User username:'.$user->username.'useridnumber:'.$user->idnumber.'berhasil ditambahkan ke grup id:'.$group->id.' grupidnumber: '.$group->idnumber.' grupname :'.$group->name.' course: '.$group->courseid." tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField});
            else $this->msg('User username:'.$user->username.'useridnumber:'.$user->idnumber.'gagal ditambahkan ke grup grupidnumber:'.$group->idnumber.'grupname :'.$group->name.'course: '.$group->courseid."tahun: ".$externalEnrollment->{$this->extternalTahunAkademikField});
        }
    }
 */
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
