<?php
class BackendController extends baseController
{

  public function updatejobsAction() {
    
    $jobsModel = new JobsModel();
    $jobsModel->update();
  }
  

}


