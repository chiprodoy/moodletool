<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Validation\Rules\Exists;

class GroupMemberCommand extends Command
{
    private $group;
    private $course;
    private $user;

    public $paramUserName;
    public $paramUserIDNumber;
    public $paramCourseIDNumber;
    public $paramGroupName;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:groupmember
                                {action : create to add member, check to read}
                                {groupname}
                                {courseidnumber}
                                {--u=}
                                {--uidn=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to check or create group member';

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
        $this->paramUserIDNumber=$this->option('uidn');
        $this->paramUserName=$this->option('u');
        $this->paramCourseIDNumber=$this->argument('courseidnumber');
        $this->paramGroupName=$this->argument('groupname');

        switch($this->argument('action')){
            case "check" : $this->show();break;
            case "create" : $this->store();break;

        }
        return Command::SUCCESS;
    }

    private function searchUser(){
        if(!empty($this->paramUserName)){
            $this->user=User::where('username',$this->paramUserName)->first();
            if(!$this->user) $this->error('user not found: username '.$this->paramUserName,'v');
        }else{
            $this->user=User::where('idnumber',$this->paramUserIDNumber)->first();
            if(!$this->user) $this->error('user not found: idnumber '.$this->paramUserIDNumber,'v');
        }
        $this->info("============".$this->user,'v');
    }

    private function searchCourse(){
        $this->course=Course::where('idnumber',$this->paramCourseIDNumber)->first();
            if(!$this->course) $this->error('Course not found: '.$this->paramCourseIDNumber,'v');

        $this->info("============".$this->course,'v');

    }

    private function searchGroup(){
        if($this->course){
            $this->group=Group::where('courseid',$this->course->id)
                            ->where('name',$this->paramGroupName)->first();
                if(!$this->group) $this->error('Group not found: '.$this->paramGroupName.", courseid:".$this->course->id,'v');

            $this->info("=============".$this->group,'v');
        }
    }
    private function store(){
        $this->searchUser();
        $this->searchCourse();
        $this->searchGroup();
        if($this->user && $this->group){
            // Detach all grup from the user...
             //$this->user->groups()->detach();
             $preData=$this->user->groups()
             ->where('groupid',$this->group->id);
             $this->info($preData->get());
             if($preData->doesntExist()){
                $this->user->groups()->attach($this->group->id,['timeadded'=>Carbon::now()->timestamp]);
             }

             $data=$this->user->groups()
             ->where('groupid',$this->group->id)
             ->where('name',$this->paramGroupName);

             if($data->exists()){
                $this->info("sukses sinkron");
                $this->info($data->get(),'v');
                return true;
             }
        }
        $this->error('gagal sinkron: ');
        return false;
    }

    private function show(){
        $this->searchUser();
        $this->searchCourse();
        $this->searchGroup();
        $data=$this->user->groups()
                ->where('groupid',$this->group->id)
                ->where('name',$this->paramGroupName);

        $this->info($data->get(),'v');
        if($data->exists()) $this->info('data ditemukan');
        else $this->warn('data tidak ditemukan');
    }


}
