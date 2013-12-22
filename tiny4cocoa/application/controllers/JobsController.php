<?php
class JobsController extends baseController
{

  public function ad728Action() {
    
    //728*90
    $this->_layout = "empty";
    $jobsModel = new JobsModel();
    $city = LocationModel::city(ToolModel::getRealIpAddr());
    if($city){
      
      if(mb_substr($city,-1)=="市")
        $city = mb_substr($city,0,-1);
      $jobs = $jobsModel->newJobsFromCity($city);
      if(!$jobs)
        $jobs = $jobsModel->newJobs();
      else if(count($jobs)==1){
        
        $addJobs = $jobsModel->newJobs();
        shuffle($addJobs);
        $jobs = array_merge($jobs,$addJobs);
        $jobs = array_slice($jobs,0,2);
      }
    }
    else
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


