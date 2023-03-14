<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:group
                            {action : action check or create}
                            {courseIDNumber}
                            {groupIDNumber}
                            {groupName}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to check or create group';

    public $paramCourseIDNumber;
    public $paramGroupName;
    public $paramGroupIDNumber;
    public $paramAction;
    public $course;

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
        $this->paramCourseIDNumber=$this->argument('courseIDNumber');
        $this->paramGroupName=$this->argument('groupName');
        $this->paramGroupIDNumber=$this->argument('groupIDNumber');
        $this->paramAction=$this->argument('action');

        $this->setCourse();
        if($this->paramAction=='create'){
            // jika course ditemukan
            if($this->course) $this->store();
            else  return Command::FAILURE;

        }else{
            $this->show();
        }
        return Command::SUCCESS;

    }

    public function show(){
        // find course id

    }
    private function setCourse(){
        $this->course=Course::where('idnumber',$this->paramCourseIDNumber)->first();
        if($this->course)
            $this->info('found course : name :'.$this->course->fullname.'shortname:'.$this->course->shortname.''.'idnumber:'.$this->course->idnumber,'v');
        else
            $this->error('course not found : '.$this->paramCourseIDNumber,'v');

    }
    public function store(){
        $group=Group::where('idnumber',$this->paramGroupIDNumber);
        if($group->exists()){
            $group->update([
                'idnumber'=>$this->paramGroupIDNumber,
                'name'=>$this->paramGroupName,
                'courseid'=>$this->course->id,
                'descriptionformat'=>1,
                'timemodified'=>Carbon::now()->timestamp
            ]);
        }else{
            Group::create([
                'idnumber'=>$this->paramGroupIDNumber,
                'name'=>$this->paramGroupName,
                'courseid'=>$this->course->id,
                'descriptionformat'=>1,
                'timecreated'=>Carbon::now()->timestamp
            ]);
        }


        $group=Group::where('idnumber',$this->paramGroupIDNumber)->first();

        if($group->course->idnumber==$this->paramCourseIDNumber){
            $this->info('create group success : groupname :'.$group->name.'idnumber:'.$group->idnumber.''.'on course: '.$group->course->idnumber.' coursename:'.$group->course->fullname.' shortname:'.$group->course->shortname,'v');
            return true;
        }else{
            $this->error('create group failed : groupname :'.$this->paramGroupName.'grupidnumber:'.$this->paramGroupIDNumber.''.'on course: '.$this->course->idnumber.' coursename:'.$this->course->fullname.' shortname:'.$this->course->shortname,'v');
            return false;
        }
    }
}
