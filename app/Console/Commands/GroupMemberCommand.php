<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Console\Command;

class GroupMemberCommand extends Command
{
    private $group;
    private $course;
    private $user;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:groupmember
                                {action : create to add member, check to read}
                                {groupname}
                                {courseidnumber}
                                {--u}
                                {username?}
                                {--uid}
                                {useridnumber?}';

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
        switch($this->argument('action')){
            case "check" : $this->show();break;
        }
        return 0;
    }

    private function searchUser(){
        if(!empty($this->argument('username'))){
            $this->user=User::where('username',$this->argument('username'))->first();
            if(!$this->user) $this->error('user not found: username '.$this->argument('username'),'v');
        }else{
            $this->user=User::where('idnumber',$this->argument('useridnumber'))->first();
            if(!$this->user) $this->error('user not found: idnumber '.$this->argument('useridnumber'),'v');
        }
        $this->info($this->user,'v');

    }

    private function searchCourse(){
        $this->course=Course::where('idnumber',$this->argument('courseidnumber'))->first();
            if(!$this->course) $this->error('Course not found: '.$this->argument('courseidnumber'),'v');

        $this->info($this->course,'v');

    }

    private function searchGroup(){
        $this->group=Group::where('courseid',$this->course->id)
                        ->where('name',$this->argument('groupname'))->first();
            if(!$this->group) $this->error('Group not found: '.$this->argument('groupname').", courseid:".$this->course->id,'v');

        $this->info($this->group,'v');

    }
    private function store(){
        $this->searchUser();
        $this->searchCourse();
        $this->searchGroup();
        return;
        if($this->user && $this->group){
             $this->user->groups()->sync($this->group->id);
             $data=$this->user->groups()->get();
             if($data->name==$this->argument('groupname') && $data->courseid==$this->course->id){
                $this->line("sukses sinkron",'v');
                $this->info($data,'v');
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
        $data=$this->user->groups()->get();
        $this->info($data);
    }


}
