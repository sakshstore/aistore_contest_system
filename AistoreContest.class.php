<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}





class AistoreContest{
    
    
// get contents feecccc
    public function get_contest_fee($amount )
{
return (get_option('contest_create_fee') / 100) * $amount;
  
}




      // create contents System
      
public static function aistore_contest()
{ 
   
 
 global $wpdb;   
      
if ( !is_user_logged_in() ) {
    
   return  do_shortcode( '[woocommerce_my_account]' );
    
    
   
}

  

$wallet = new AistoreWallet();
$user_id=get_current_user_id();


if(isset($_POST['submit']) and $_POST['action']=='contest' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 




$title=sanitize_text_field($_REQUEST['title']);
$amount=intval($_REQUEST['amount']);
$currency=sanitize_text_field($_REQUEST['currency']);
$term_condition=sanitize_text_field(htmlentities($_REQUEST['term_condition']));
 $ends_date=($_REQUEST['ends_date']);
 
$contest_holder_name=sanitize_text_field($_REQUEST['contest_holder_name']);
$comapny_name=sanitize_text_field($_REQUEST['comapny_name']);
$comapny_slogan=sanitize_text_field($_REQUEST['comapny_slogan']);
$industry_type=sanitize_text_field($_REQUEST['industry_type']);
 
  $object=new AistoreContest();

$contest_fee=$object->get_contest_fee($amount);

   
    $new_amount=$amount-$contest_fee ;
      


$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest ( title, amount,user_id,term_condition,contest_fee  ,currency,end_date,contest_holder_name,comapny_name,comapny_slogan,industry_type,created_by) VALUES ( %s, %d, %s ,%s ,%s,%s,%s,%s ,%s,%s,%s,%s)", array( $title, $new_amount, $user_id  ,$term_condition ,$contest_fee,$currency,$ends_date,$contest_holder_name,$comapny_name,$comapny_slogan,$industry_type,$user_id) ) );

$cid = $wpdb->insert_id;
$user_login = get_the_author_meta( 'user_login',$user_id );

	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id') ,
	'eid'=> $cid,
), home_url() ) );
  // test notification 
   
  
  	$n=array();
	$n['message']="Contest Created Successfully";
	$n['user_login']=$user_login;
	$n['type']="success";
	$n['url']=$details_contest_page_id_url;
	
	$n['user_id']=$user_id;
	
	aistore_notification_new($n);
	

	
	// notification test end
	
  $upload_dir = wp_upload_dir();
  

        if ( ! empty( $upload_dir['basedir'] ) ) {
            
            
            $user_dirname = $upload_dir['basedir'].'/documents/'.$cid;
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }
 
            $filename = wp_unique_filename( $user_dirname, $_FILES['file']['name'] );
            move_uploaded_file(sanitize_text_field($_FILES['file']['tmp_name']), $user_dirname .'/'. $filename);
            
            $image= $upload_dir['baseurl'].'/documents/'.$cid.'/'.$filename;
            
            // save into database  $image
            
                     

$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest_documents ( eid, documents,user_id,documents_name) VALUES ( %d,%s,%d,%s)", array( $cid,$image,$user_id,$filename) ) );
        }
        
        else{
            ?>
            <p> <?php  _e( 'Note : We accept only jpg, png, jpeg images', 'aistore' ) ?></p><?php
        
        }
        




$Payment_details = __( 'Payment transaction for the content id', 'aistore' );

 $details=$Payment_details.$cid ; 
 
 
$wallet->debit($user_id,$amount,$currency,$details);
$admin_id=get_option('escrow_user_id');
$wallet->credit($admin_id,$contest_fee ,$currency,$details);
 $wallet->credit($admin_id,$new_amount,$currency,$details);


?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_contest_page_id_url) ; ?>" /> 

<?php

}
else{
?>
    
    <form method="POST" action="" name="contest" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>

                
                 
<label for="title"><?php   _e('Title', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="title" name="title" required><br><br>
  
  <!--///-->
	  <label for="contest_holder_name"><?php   _e( 'Contest Holder Name', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="contest_holder_name" name="contest_holder_name"  required><br><br>
  
  <label for="comapny_name"><?php   _e( 'Company Name', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="comapny_name" name="comapny_name"  required><br><br>
  
    <label for="comapny_slogan"><?php   _e( 'Company Slogan', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="comapny_slogan" name="comapny_slogan"  required><br><br>
  
  <label for="industry_type"><?php   _e( 'Industry Type', 'aistore' ); ?></label><br>
  
  <input class="input" type="text" id="industry_type" name="industry_type"  required><br><br>
  <!--//-->
  

  <label for="amount"><?php   _e( 'Amount', 'aistore' ); ?></label><br>
  
  <input class="input" type="number" id="amount" name="amount" min="1" max="10000" required><br><br>
 
  <label><?php _e( 'Currency:', 'aistore' ) ;?></label>
<br>
 <select name="currency">
	<option value="USD" selected="selected">United States Dollars</option>
	<option value="EUR">Euro</option>
	<option value="GBP">United Kingdom Pounds</option>
	<option value="DZD">Algeria Dinars</option>
	<option value="ARP">Argentina Pesos</option>
	<option value="AUD">Australia Dollars</option>
	<option value="ATS">Austria Schillings</option>
	<option value="BSD">Bahamas Dollars</option>
	<option value="BBD">Barbados Dollars</option>
	<option value="BEF">Belgium Francs</option>
	<option value="BMD">Bermuda Dollars</option>
	<option value="BRR">Brazil Real</option>
	<option value="BGL">Bulgaria Lev</option>
	<option value="CAD">Canada Dollars</option>
	<option value="CLP">Chile Pesos</option>
	<option value="CNY">China Yuan Renmimbi</option>
	<option value="CYP">Cyprus Pounds</option>
	<option value="CSK">Czech Republic Koruna</option>
	<option value="DKK">Denmark Kroner</option>
	<option value="NLG">Dutch Guilders</option>
	<option value="XCD">Eastern Caribbean Dollars</option>
	<option value="EGP">Egypt Pounds</option>
	<option value="FJD">Fiji Dollars</option>
	<option value="FIM">Finland Markka</option>
	<option value="FRF">France Francs</option>
	<option value="DEM">Germany Deutsche Marks</option>
	<option value="XAU">Gold Ounces</option>
	<option value="GRD">Greece Drachmas</option>
	<option value="HKD">Hong Kong Dollars</option>
	<option value="HUF">Hungary Forint</option>
	<option value="ISK">Iceland Krona</option>
	<option value="INR">India Rupees</option>
	<option value="IDR">Indonesia Rupiah</option>
	<option value="IEP">Ireland Punt</option>
	<option value="ILS">Israel New Shekels</option>
	<option value="ITL">Italy Lira</option>
	<option value="JMD">Jamaica Dollars</option>
	<option value="JPY">Japan Yen</option>
	<option value="JOD">Jordan Dinar</option>
	<option value="KRW">Korea (South) Won</option>
	<option value="LBP">Lebanon Pounds</option>
	<option value="LUF">Luxembourg Francs</option>
	<option value="MYR">Malaysia Ringgit</option>
	<option value="MXP">Mexico Pesos</option>
	<option value="NLG">Netherlands Guilders</option>
	<option value="NZD">New Zealand Dollars</option>
	<option value="NOK">Norway Kroner</option>
	<option value="PKR">Pakistan Rupees</option>
	<option value="XPD">Palladium Ounces</option>
	<option value="PHP">Philippines Pesos</option>
	<option value="XPT">Platinum Ounces</option>
	<option value="PLZ">Poland Zloty</option>
	<option value="PTE">Portugal Escudo</option>
	<option value="ROL">Romania Leu</option>
	<option value="RUR">Russia Rubles</option>
	<option value="SAR">Saudi Arabia Riyal</option>
	<option value="XAG">Silver Ounces</option>
	<option value="SGD">Singapore Dollars</option>
	<option value="SKK">Slovakia Koruna</option>
	<option value="ZAR">South Africa Rand</option>
	<option value="KRW">South Korea Won</option>
	<option value="ESP">Spain Pesetas</option>
	<option value="XDR">Special Drawing Right (IMF)</option>
	<option value="SDD">Sudan Dinar</option>
	<option value="SEK">Sweden Krona</option>
	<option value="CHF">Switzerland Francs</option>
	<option value="TWD">Taiwan Dollars</option>
	<option value="THB">Thailand Baht</option>
	<option value="TTD">Trinidad and Tobago Dollars</option>
	<option value="TRL">Turkey Lira</option>
	<option value="VEB">Venezuela Bolivar</option>
	<option value="ZMK">Zambia Kwacha</option>
	<option value="EUR">Euro</option>
	<option value="XCD">Eastern Caribbean Dollars</option>
	<option value="XDR">Special Drawing Right (IMF)</option>
	<option value="XAG">Silver Ounces</option>
	<option value="XAU">Gold Ounces</option>
	<option value="XPD">Palladium Ounces</option>
	<option value="XPT">Platinum Ounces</option>
</select><br><br>


 
   
  

 

	  <label for="ends_date"><?php   _e( 'Ends Date', 'aistore' ); ?></label><br>
  
  <input class="input" type="date" id="ends_date" name="ends_date"  required><br><br>
	

  
  
   <label for="term_condition"> <?php  _e( 'Description', 'aistore' ) ?></label><br>
   
   



  
  <?php
  
$content   = '';
$editor_id = 'term_condition';

 
   $settings = array(
    'tinymce'       => array(
        'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright   ',
        'toolbar2'      => '',
        'toolbar3'      => ''
       
   
      ),   
         'textarea_rows' => 1 ,
    'teeny' => true,
    'quicktags' => false,
     'media_buttons' => false 
);



wp_editor( $content, $editor_id,$settings);
?>
  



<br><br>

	<label for="documents"><?php  _e( 'Documents', 'aistore' ) ?>: </label>
     <input type="file" name="file"  required /><br>
    


	
	<br><br>
<input 
 type="submit" class="btn" name="submit" value="<?php  _e( 'Make Payment', 'aistore' ) ?>"/>
<input type="hidden" name="action" value="contest" />
</form> 
<?php
}
 

}


// public contest list

public static function aistore_contest_list_page(){
     global $wpdb;   
	 
      ob_start();  
      

                 
$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'contest WHERE status="approved" and  end_date >= NOW()');

 if($results==null)
	{
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	else{
	    
	    
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo $details_contest_page_id_url; ?>" class=" ">  
    <br>
    
 <?php echo "# ".$row->id." ".$row->title ; ?> 
    
    
    </a><br><br>
    
    
    
    <p class="card-text">  <?php echo "Amount: ".number_format($row->amount) ." ".  $row->currency; ?><br />  
  <?php echo "Contest Ends In : ".$row->end_date ;?>
  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />
  <?php echo  "Submitted Entries:".$contest_entries->contest_entries ; ?> 
  </p>
  
<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   

}
	

}

//contest list

public static function contest_list(){
     global $wpdb;   
	 
      ob_start();  
      
      	 
$user_id = get_current_user_id();


                 // this query is wrong
                 
$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'contest WHERE status="approved" and  end_date >= NOW()');

 if($results==null)
	{
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	else{
	    
	    
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo $details_contest_page_id_url; ?>" class=" ">  
    <br>
    
 <?php echo "# ".$row->id." ".$row->title ; ?> 
    
    
    </a><br><br>
    
    
    
    <p class="card-text">  <?php echo "Amount: ".number_format($row->amount) ." ".  $row->currency; ?><br />  
  <?php echo "Contest Ends In : ".$row->end_date ;?>
  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />
  <?php echo  "Submitted Entries:".$contest_entries->contest_entries ; ?> 
  </p>
  
<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   

}
	

}



// your contest List

 public static function aistore_contest_list(){
     
     if ( !is_user_logged_in() ) {
    
   return  do_shortcode( '[aistore_contest_list_page]' );
    
    
   
}

	  global $wpdb;   
	 
      ob_start();  

   // incorrect  query
   
   
  
   
   $results1 = $wpdb->get_row(' 
    SELECT count(*) as total_contest FROM '.$wpdb->prefix.'contest WHERE status="approve" and  end_date >= NOW() ' );
    
   echo  "<br />Active Contests:  " .$results1->total_contest;
   

  

	 
$user_id = get_current_user_id();


                 // this query is wrong
                 
// $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'contest where created_by' );

 
   $results = $wpdb->get_results($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}contest WHERE created_by=%d ",$user_id));
   
 
 if($results==null)
	{
	    
	    
	      echo "<div class='no-result'>";
	      
	     _e( 'contest List Not Found', 'aistore' ); 
	  echo "</div>";
	}
	
	
	else{
   

     
  ?>
 
 
 

	 <div class="row " >
    <?php 
    
     
    foreach($results as $row):
  
	 
	 $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id'),
    'eid' => $row->id,
), home_url() ) ); 

 ?>
 
 
       
	 <div class="col-md-4 " >


  <div class="card ">
      
    <a href="<?php echo $details_contest_page_id_url; ?>" class=" ">  
    <br>
    
 <?php echo "# ".$row->id." ".$row->title ; ?> 
    
    
    </a><br><br>
    
    
    
    <p class="card-text">  <?php echo "Amount: ".number_format($row->amount) ." ".  $row->currency; ?><br />  
  <?php echo "Contest Ends In : ".$row->end_date ;?>
  
  <?php 
    
   $contest_entries = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as contest_entries FROM {$wpdb->prefix}contest_documents WHERE id=%s ",$row->id));

 
    
 
  ?><br />
  <?php echo  "Submitted Entries:".$contest_entries->contest_entries ; ?> 
  </p>
    <?php
     

    // need to remove from here
    if(isset($_POST['submit']) and $_POST['action']=='delete_contest' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$wpdb->delete( $wpdb->prefix.'contest', array( 'id' => $document_id, 'created_by'=>$user_id) );
}
else{


 
    if($row->created_by==$user_id){
        ?>
    
     <form method="POST" action="" name="delete_contest" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo $row->id ; ?>">
<input 
 type="submit" class="btn btn-primary btn-sm" name="submit" value="&#128465;"/>	
<input type="hidden" name="action" value="delete_contest" />
</form>

<?php
}
}
?>

<hr />
  </div>
</div>
    
		 
		   

           
    <?php endforeach;


 return ob_get_clean();   


}

}




// contest Details

public static function aistore_contest_detail( ){
         
         
         if ( !is_user_logged_in() ) {
    
  return do_shortcode( '[woocommerce_my_account]' );
}



 global $wpdb;   
 
 
    if(isset($_POST['submit']) and $_POST['action']=='delete_image' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$wpdb->delete( $wpdb->prefix.'contest_documents', array( 'id' => $document_id) );
}

 
   if(isset($_POST['submit']) and $_POST['action']=='winner_contest')
{


    global $wpdb;  
    $user_id= get_current_user_id();
    $entry_id=sanitize_text_field($_REQUEST['eid']);
$contest_id=sanitize_text_field($_REQUEST['cid']);

$contest_entries = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE id=%d", $entry_id ));

$entry_user_id=$contest_entries->user_id;

$contest_escrow = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest WHERE id=%d and created_by=%d", $contest_id,$user_id ));


 $receiver_email = get_the_author_meta( 'user_email', $entry_user_id );
$amount=$contest_escrow->amount;
$title=$contest_escrow->title;
$term_condition=$contest_escrow->term_condition;
 

$escrow=new AistoreEscrowSystem();
$res=$escrow->add_esrow($amount,$user_id,$receiver_email,$title,$term_condition);



$user_login = get_the_author_meta( 'user_login',$user_id );


$user_login_winner = get_the_author_meta( 'user_login',$entry_user_id );


$escrow_id=$res['eid'];


	 $details_escrow_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_escrow_page_id'),
    'eid' => $escrow_id,
), home_url() ) ); 

   	$n=array();
	$n['message']="Escrow Created Successfully4";
	$n['user_login']=$user_login;
	$n['type']="success";
	$n['url']=$details_escrow_page_id_url;
	
	$n['user_id']=$user_id;
	
	$a=array();
	$a['message']="Escrow Created Successfully5";
	$a['user_login']=$user_login_winner;
	$a['type']="success";
	$a['url']=$details_escrow_page_id_url;
	
	$a['user_id']=$entry_user_id;
	
	aistore_notification_new($n);
	aistore_notification_new($a);
	
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}contest
    SET status = '%s'  WHERE id = '%d' ", 
   'closed' , $contest_id) );
	
?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_escrow_page_id_url) ; ?>" /> 

<?php

}




else{


   if(!sanitize_text_field($_REQUEST['eid'])){
    
    	 $add_contest_page_url  =  esc_url( add_query_arg( array(
    'page_id' => get_option('add_contest_page_id') ,
), home_url() ) );

    ?>
    
   
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($add_contest_page_url) ; ?>" /> 
  
 <?php   }
 
 
 
 
 $user_id= get_current_user_id();
	 
$email_id = get_the_author_meta( 'user_email',$user_id );
 

ob_start();

    $eid=sanitize_text_field($_REQUEST['eid']);
    
     echo aistore_echo_notification() ;
    
    $results1 = $wpdb->get_row($wpdb->prepare( "SELECT count(*) as total_entries  FROM {$wpdb->prefix}contest_documents WHERE eid=%s and user_id!=%d  ",$eid, $user_id ));
    
   echo  "<br />Submitted Entries: " .$results1->total_entries;


$contest = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}contest WHERE id=%s ",$eid ));
  
   echo "<br />  Contest Ends In  :  ". $contest->end_date ;
 

 ?><br>
	  <div><br>
	      
	      
	      
	      
	      
	      
	      
	      <div class="alert alert-success" role="alert">
 <strong>Contest Status   <?php echo $contest->status;?></strong>
  </div>
	  
	  
	  
	      <?php
     echo "<strong>#". $contest->id ." ".$contest->title ."</strong><br>";
       printf(__( "<strong>Contest Holder Name</strong> <br> %s", 'aistore' ),html_entity_decode($contest->contest_holder_name)."<br><br>");
       
         printf(__( "<strong>Company Name</strong><br>  %s", 'aistore' ),html_entity_decode($contest->comapny_name)."<br><br>");
         
           printf(__( "<strong>Company Slogan</strong><br>  %s", 'aistore' ),html_entity_decode($contest->comapny_slogan)."<br><br>");
           
             printf(__( "<strong>Industry Type</strong><br>  %s", 'aistore' ),html_entity_decode($contest->industry_type)."<br><br>");
     
  printf(__( "<strong>Description</strong><br>  %s", 'aistore' ),html_entity_decode($contest->term_condition)."<br><br>");


 echo "<hr />";

  $object=new AistoreContest();



 
$object->contest_file_uploads($contest);

    
    ?>
</div>

<?php
     return ob_get_clean();  
}
}



// contest  file uploads

private	function contest_file_uploads($contest){
       global $wpdb;   
$eid=  $contest->id;
$created_by=$contest->created_by;
$user_id=get_current_user_id();
     	
     	
     	// we need two tables for this not all in one 
     	
   $contest_documents_login = $wpdb->get_results( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE user_id=%d and eid=%d", $contest->user_id,$eid));
?>
 <table class="table"><tr>
         <h3><?php   _e( 'Design Styles We Would Like To See:', 'aistore' ); ?></h3>
        <tr>
    <?php
    
    foreach($contest_documents_login as $row):
        ?>
        <td class="box"> 
     <img   src="<?php echo $row->documents; ?>" class="img-fluid"></td>
     	    
     	    <?php
     
    endforeach;
    ?>
    </tr>
    </table>
    
    <?php
    
    
    
 echo "<hr />";


   $contest_documents = $wpdb->get_results( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE eid=%d and user_id!=%d", $eid,$contest->user_id ));
 
  if(count($contest_documents)>1)
  { 
?>  <h3><?php   _e( 'Contest Entries:', 'aistore' ); ?></h3>
  <?php 
  }
 
 
    
    foreach($contest_documents as $row):
        	$user_login = get_the_author_meta( 'user_login', $row->user_id );
     
    ?>

	<div class="document_list">
<div class="box">
  <div >

			<span class="b"><?php   _e( 'Entry ID:', 'aistore' ); ?> #<?php echo  $row->id;?> <br>
			
	<?php
	   if(isset($_POST['submit']) and $_POST['action']=='rating' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' );
   exit;
} 

$document_id=sanitize_text_field($_REQUEST['document_id']);
$document_rate=sanitize_text_field($_REQUEST['rate']);


   
      $q1=$wpdb->prepare("INSERT INTO {$wpdb->prefix}contest_rating ( document_id,rating,  user_id ,cid) VALUES ( %d, %d, %d ,%d) ", array(  $document_id,$document_rate, $user_id,$row->eid));
     $wpdb->query($q1);
     
    $details_contest_page_id_url =  esc_url( add_query_arg( array(
    'page_id' => get_option('details_contest_page_id') ,
	'eid'=> $row->eid,
), home_url() ) );


?>
<meta http-equiv="refresh" content="0; URL=<?php echo esc_html($details_contest_page_id_url) ; ?>" /> 

<?php
}

    
    ?>
<img class="img-fluid " src="<?php echo $row->documents; ?>" ><br>
<!-- RATING - Form -->

    
    <?php
        $this->print_rating($row);
        
    
    $this->rating_form($row);
    
    if($created_by==$user_id){
    $this->delete_contest_document($row);
  $this->choose_him_as_winner_button($contest,$row);
    }
    
echo "<hr/>";
    endforeach;
    
    
    ?>
     
<br>
	   <div>
	         
	   <style>
#hidden {
  display: none;
  height: 100px;
  
}
:checked + #hidden {
  display: block;
}</style>


<?php

  
if($contest->status=='approved'){
    
?>



<label for="my_checkbox"><h3><u>Submit Entry</u></h3></label><br>



<input type="checkbox" id="my_checkbox" style="display:none;">
<div id="hidden">
	<label for="documents"> <?php   _e( 'Documents', 'aistore' ); ?> : </label>
<form  method="post"  action="<?php echo admin_url('admin-ajax.php').'?action=custom_action&eid='.$eid; ?>" class="dropzone" id="dropzone">
    <?php 
wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' );
?>
  <div class="fallback">
    <input id="file" name="file" type="file"  multiple   />
    <input type="hidden" name="action" value="custom_action" type="submit"  />
  </div>

</form></div>


    <?php }
    ?>
     
     </div>
     <br>
     
     <?php 
      
      
}



function rating_form($row)
{global $wpdb;

    
    ?>
    
    <form class="rating-form" action="" method="POST" name="rating">
<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo $row->id; ?>">
<select name="rate" id="rate" class="form-control">
     <option value="0" selected>Select Rating</option>
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
   <option value="5">5</option>
</select>
<input type="submit" class="btn btn-primary" name="submit" value="Submit"/>
<input type="hidden" name="action" value="rating" />
</form>




 <?php 

}
 
 
 function print_rating($row)
 {
     
 global $wpdb;
$rating = $wpdb->get_results( "SELECT avg(rating) as rate FROM {$wpdb->prefix}contest_rating WHERE document_id = '".   $row->id."' order by id desc  limit 1"   );


  foreach($rating as $row1):

         endforeach;
    
 ?>
<?php if(round($row1->rate)==1){
?> <strong style="color:orange; ">*</strong>   
<?php }
 if(round($row1->rate)==2){
?> <strong style="color:orange; ">* *</strong>   
<?php }
 if(round($row1->rate)==3){
?> <strong style="color:orange; ">* * *</strong>   
<?php }

 if(round($row1->rate)==4){
?> <strong style="color:orange; ">* * * *</strong>   
<?php }

 if(round($row1->rate)==5){
?> <strong style="color:orange; ">* * * * *</strong>   
<?php }
?>
Submission #<?php echo  $row->id;?><br>

				</span>
						</div> 
</div>


<?php
 }
 
 
 function delete_contest_document($entry){
      
 
 
?>
    
     <form method="POST" action="" name="delete_image" enctype="multipart/form-data"> 

<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="document_id" value="<?php echo $entry->id ; ?>">
<input 
 type="submit" class="btn btn-primary btn-sm" name="submit" value="Delete"/>	
<input type="hidden" name="action" value="delete_image" />
</form>

<?php

}

 
	
function choose_him_as_winner_button($contest,$entry)
{
     
      
  
 
?>
<br>
  <form class="rating-form" action="" method="POST" name="winner_contest" enctype="multipart/form-data">
<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="eid" value="<?php echo $entry->id; ?>">
<input type="hidden" name="cid" value="<?php echo $contest->id; ?>">
<input type="submit" class="btn" name="submit" value="Choose him as winner"/>
<input type="hidden" name="action" value="winner_contest" />
</form>

<?php 
 
    
}







	
	
	


}



 add_action( 'wp_ajax_custom_action', 'aistore_contest_upload_file' );


function aistore_contest_upload_file() {
    
 global $wpdb;   
  $eid=sanitize_text_field($_REQUEST['eid']);

$contest = $wpdb->get_row($wpdb->prepare( "SELECT count(id) as count FROM {$wpdb->prefix}contest WHERE id=%s ",$eid ));

  $c=(int)$contest->count;
 if($c>0){
     
    
 if ( isset($_POST['aistore_nonce']) ) {
        $upload_dir = wp_upload_dir();
 
        if ( ! empty( $upload_dir['basedir'] ) ) {
            $user_dirname = $upload_dir['basedir'].'/documents/'.$eid;
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }

            $filename = wp_unique_filename( $user_dirname, $_FILES['file']['name'] );
            
            echo "filename".$filename;
            
            
                
            move_uploaded_file(sanitize_text_field($_FILES['file']['tmp_name']), $user_dirname .'/'. $filename);
            
            
            $image=$upload_dir['baseurl'].'/documents/'.$eid.'/'.$filename;
//             // save into database $image;
      

$user_id=get_current_user_id();
$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest_documents ( eid, documents,user_id,documents_name) VALUES ( %d,%s,%d,%s)", array( $eid,$image,$user_id,$filename) ) );

        }
    }

  wp_die();
}
else{
     _e( 'Unauthorized user', 'aistore' ); 
}


}