<?php
 
if(isset($_GET['type'])){
    if($_GET['type']=="cron"){
        $output = shell_exec('php artisan Unverified:daily');
      
        // Display the list of all file
        // and directory
        echo "<pre>$output</pre>";
        echo "Cron executed!";
    }elseif($_GET['type']=="deploy"){
        $output = shell_exec('git pull origin main');
      
        // Display the list of all file
        // and directory
        echo "<pre>$output</pre>";
        echo "Git Deploy executed!";
    }
} 

