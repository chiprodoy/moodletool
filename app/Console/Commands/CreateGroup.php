<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Group;
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
        exit();
        if($this->paramAction=='create'){
            $this->store();
        }else{
            $this->show();
        }
    }

    public function show(){
        // find course id

    }
    private function setCourse(){
        $this->course=Course::where('idnumber',$this->paramCourseIDNumber)->first();
        dd($this->course);
        $this->info('found course : name :'.$this->course->fullname.'shortname:'.$this->course->shortname.''.'idnumber:'.$this->course->idnumber,'v');

    }
    public function store(){
        $group=Group::updateOrCreate(
            [
                'idnumber'=>$this->paramGroupIDNumber,
                'name'=>$this->paramGroupName,
                'courseid'=>$this->course->id
            ],
            ['descriptionformat'=>1]
        );
    }

    private function searchCourse(){
        $this->course=Course::where('idnumber',$this->paramCourseIDNumber)->first();
            if(!$this->course) $this->error('Course not found: '.$this->paramCourseIDNumber,'v');

        $this->info("============".$this->course,'v');

    }
}
