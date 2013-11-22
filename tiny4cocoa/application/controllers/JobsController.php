<?php
class JobsController extends baseController
{

  public function ad728Action() {
    
    //728*90
    $this->_layout = "empty";
    $jobsModel = new JobsModel();
    $jobs = $jobsModel->newJobs();
    shuffle($jobs);
    $this->_mainContent->assign("job1",$jobs[0]);
    $this->_mainContent->assign("job2",$jobs[1]);
    $this->display();
  }
  
  public function showAction() {
    
    $id = $this->intVal(3);
    $jobsModel = new JobsModel();
    $job = $jobsModel->jobById($id);
    $this->_mainContent->assign("job",$job);
    $this->display();
  }
  
  public function jsAction() {
    
    $ad = $this->strVal(3);
    $this->_mainContent->assign("ad",$ad);
    $this->_layout = "empty";
    $this->display();
  }
}


