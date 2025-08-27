<?php

if($_SESSION["profile"] == "Seller"){

  echo '<script>

    window.location = "index.php?route=home";

  </script>';

  return;

}

?>

<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Database Backup Management

    </h1>

    <ol class="breadcrumb">

      <li><a href="index.php?route=home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Backup</li>

    </ol>

  </section>

  <section class="content">

    <!-- Backup Actions Box -->
    <div class="box box-primary">
      
      <div class="box-header with-border">
        
        <h3 class="box-title">Backup Actions</h3>
        
      </div>
      
      <div class="box-body">
        
        <div class="row">
          
          <div class="col-md-6">
            
            <button class="btn btn-success btn-lg btn-block" onclick="createBackup()" id="createBackupBtn">
              
              <i class="fa fa-database"></i> Create Backup Now
              
            </button>
            
          </div>
          
          <div class="col-md-6">
            
            <button class="btn btn-info btn-lg btn-block" onclick="refreshBackupList()">
              
              <i class="fa fa-refresh"></i> Refresh List
              
            </button>
            
          </div>
          
        </div>
        
      </div>
      
    </div>

    <!-- Backup Statistics Box -->
    <div class="box box-info">
      
      <div class="box-header with-border">
        
        <h3 class="box-title">Backup Statistics</h3>
        
      </div>
      
      <div class="box-body" id="backupStats">
        
        <div class="row">
          
          <div class="col-sm-3 col-xs-6">
            
            <div class="description-block border-right">
              
              <span class="description-percentage text-blue"><i class="fa fa-database"></i></span>
              
              <h5 class="description-header" id="totalBackups">-</h5>
              
              <span class="description-text">TOTAL BACKUPS</span>
              
            </div>
            
          </div>
          
          <div class="col-sm-3 col-xs-6">
            
            <div class="description-block border-right">
              
              <span class="description-percentage text-green"><i class="fa fa-archive"></i></span>
              
              <h5 class="description-header" id="totalSize">-</h5>
              
              <span class="description-text">TOTAL SIZE</span>
              
            </div>
            
          </div>
          
          <div class="col-sm-3 col-xs-6">
            
            <div class="description-block border-right">
              
              <span class="description-percentage text-yellow"><i class="fa fa-clock-o"></i></span>
              
              <h5 class="description-header" id="lastBackup">-</h5>
              
              <span class="description-text">LAST BACKUP</span>
              
            </div>
            
          </div>
          
          <div class="col-sm-3 col-xs-6">
            
            <div class="description-block">
              
              <span class="description-percentage text-red"><i class="fa fa-calendar"></i></span>
              
              <h5 class="description-header" id="oldestBackup">-</h5>
              
              <span class="description-text">OLDEST BACKUP</span>
              
            </div>
            
          </div>
          
        </div>
        
      </div>
      
    </div>

    <!-- Backup Files List -->
    <div class="box box-default">
      
      <div class="box-header with-border">
        
        <h3 class="box-title">Available Backups</h3>
        
      </div>
      
      <div class="box-body">
        
        <div class="table-responsive">
          
          <table class="table table-bordered table-hover table-striped" id="backupTable">
            
            <thead>
              
              <tr>
                
                <th style="width:10px">#</th>
                <th>Filename</th>
                <th>Date Created</th>
                <th>File Size</th>
                <th>Actions</th>
                
              </tr>
              
            </thead>
            
            <tbody id="backupTableBody">
              
              <tr>
                
                <td colspan="5" class="text-center">Loading backup files...</td>
                
              </tr>
              
            </tbody>
            
          </table>
          
        </div>
        
      </div>
      
    </div>

    <!-- Backup Log -->
    <div class="box box-warning">
      
      <div class="box-header with-border">
        
        <h3 class="box-title">Recent Backup Log</h3>
        
      </div>
      
      <div class="box-body">
        
        <div style="height: 300px; overflow-y: auto; background: #f4f4f4; padding: 10px; border-radius: 3px;">
          
          <pre id="backupLog">Loading log...</pre>
          
        </div>
        
      </div>
      
    </div>

    <!-- Auto Backup Configuration -->
    <div class="box box-success">
      
      <div class="box-header with-border">
        
        <h3 class="box-title">Automatic Backup Setup</h3>
        
      </div>
      
      <div class="box-body">
        
        <div class="alert alert-info">
          
          <h4><i class="icon fa fa-info"></i> Setup Instructions</h4>
          
          To enable automatic daily backups, you need to schedule the backup script using Windows Task Scheduler:
          
          <ol style="margin-top: 10px;">
            <li>Open <strong>Task Scheduler</strong> from Windows Start menu</li>
            <li>Click <strong>"Create Basic Task"</strong></li>
            <li>Name: <code>POSYS Daily Backup</code></li>
            <li>Trigger: <strong>Daily</strong> at your preferred time (e.g., 2:00 AM)</li>
            <li>Action: <strong>Start a program</strong></li>
            <li>Program: <code><?php echo realpath(dirname(__FILE__) . '/../../backup/run_backup.bat'); ?></code></li>
            <li>Start in: <code><?php echo realpath(dirname(__FILE__) . '/../../backup'); ?></code></li>
          </ol>
          
        </div>
        
      </div>
      
    </div>

  </section>

</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <i class="fa fa-spinner fa-spin fa-3x"></i>
        <h4>Creating Backup...</h4>
        <p>Please wait while we create your database backup.</p>
      </div>
    </div>
  </div>
</div>

<script src="views/js/backup.js"></script>
