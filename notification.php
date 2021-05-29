<?php 

add_shortcode('aistore_notification', 'aistore_echo_all_notification');


function aistore_echo_all_notification()
{
	 
ob_start();

$user_id=get_current_user_id();

 
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}aistore_notification WHERE user_id='$user_id' order by id desc";
	
	 

  $v1= $wpdb->get_results($sql );
	 
	foreach ($v1 as $row):
            
?> 
  
  <div class="discussionmsg">
   
  <p><a href="<?php echo $row->url; ?>"> <?php echo html_entity_decode($row->message); ?> </a> </p>
  
  
  <h6 > <?php echo $row->created_at; ?></h6>
</div>
 
<hr>
    
    <?php
        endforeach;  
	
 return ob_get_clean();	

}


 function aistore_echo_notification( ){
	
 
    global $wpdb;
	
	
$user_id=get_current_user_id();
	
$notification = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}aistore_notification WHERE    user_id = '".   $user_id."' order by id desc  limit 1"   );
 
	 if(isset($notification->type))
return ' <div><a href="'.$notification->url.'"><div class="alert alert-'.$notification->type .'" role="alert"> '. 
"# ".$notification->id."  ".$notification->message.'</div></a></div>';
else
return "";

 			
}
 
function aistore_notification($notification,$type="success",$user_login="" ){
	
	 if($user_login=="")
	 {
 	
$user_id=get_current_user_id();
 	
 	
	 }
	
   global $wpdb;
 
 
  

   $q1=$wpdb->prepare("INSERT INTO {$wpdb->prefix}aistore_notification (  message,type,  user_id ) VALUES ( %s, %s, %d ) ", array(  $notification,$type, $user_id));
     $wpdb->query($q1);
   
   
}


function aistore_notification_new($n  ){
    
    

	/*
	$n=array();
	$n['message']="test notification msg";
	
	$n['type']="success";
	$n['url']="localhost";
	
	$n['user_login']=$login_email;
	*/
	
	 
	//$n['user_email']=$n['user_login'];
	
   global $wpdb;
 
 
 $q1= ( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}aistore_notification ( user_login, message,type,user_id,url ) VALUES ( %s, %s, %s ,%d ,%s)", array( $n['user_login'], $n['message'], $n['type']  ,$n['user_id'] ,$n['url']) ) );

	
//	echo $q1;
   
   $wpdb->query($q1);
}

function qr_to_log($log)
{
	
 $file =  dirname(__FILE__) . '/qrlog.php';
 
  $_SESSION['qr']=$log;
file_put_contents($file,print_r($log,true).PHP_EOL .PHP_EOL .PHP_EOL .PHP_EOL .PHP_EOL , FILE_APPEND | LOCK_EX);
 
	
	return 2;
}



?>