<?php




class AistoreContestSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'aistore_contest_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'aistore_contest_page_register_setting' ) );
        
    

	
    }

    /**
     * Add options page
     */
public function aistore_contest_add_plugin_page()
{
    // This page will be under "Settings"
    add_options_page('Settings Admin', __( 'My Setting', 'aistore' ), 'administrator', 'my-setting-admin', array(
        $this,
        'aistore_contest_page_setting'
    ));
    
    
    
    
    
    add_menu_page(__( 'Contest System', 'aistore' ),  __('Contest System', 'aistore' ), 'administrator', 'aistore_user_contest_list');
    
    
     
    add_submenu_page('aistore_user_contest_list', __('Contest List', 'aistore' ), __('Contest List', 'aistore' ), 'administrator', 'aistore_user_contest_list', array(
        $this,
        'aistore_user_contest_list'
    ));
    
   
    
       add_submenu_page('aistore_user_contest_list', __(' Contest Details','aistore'), __('','aistore'), 'administrator', 'contest_details', array(
        $this,
        'aistore_contest_details'
    ));
    
    add_submenu_page('aistore_user_contest_list', __('Setting','aistore'), __('Setting','aistore'), 'administrator', 'aistore_contest_page_setting', array(
        $this,
        'aistore_contest_page_setting'
    ));
    
    
}

function aistore_user_contest_list(){
    
	
global  $wpdb;

 $results = $wpdb->get_results( 
   $wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest order by id desc")
                 );
     
  ?>
  <h1> <?php  _e( 'Contest List', 'aistore' ) ?> </h1>
  <table class="widefat fixed striped">
        
     <tr>
         <th>Id</th>
      <th>Title</th>
    
        <th>Amount</th>
         <th>Date</th>
          <th>Status</th>
           
     </tr>
      

    <?php 
    
    if($results==null)
	{
	     _e( "No Contest Found", 'aistore' );

	}
	else{
    foreach($results as $row):
    
 $link= '<a href="/wp-admin/admin.php?page=contest_details&eid='.$row->id.'">'.$row->id.'</a>';
    ?> 
      <tr>

		   <td> 	
		   
		   <?php echo $link ; ?> </td>
		  
		   
		   <td> 		   <?php echo $row->title ; ?> </td>
		  
	
		   
		   <td> 		   <?php echo $row->amount." ".$row->currency ; ?> </td>
		  
		     <td> 		   <?php echo $row->created_at ; ?> </td>
		     	   <td> 		   <?php echo $row->status ; ?> </td>
         
                
            </tr>
    <?php endforeach;
	}
	
	?>



    </table>
	<?php 


}

    //aistore_escrow_details
    
    function aistore_contest_details(){
        
   


   $eid=sanitize_text_field($_REQUEST['eid']);
   
$user_id= get_current_user_id();
	 $email_id = get_the_author_meta( 'user_email',$user_id );

if ( isset($_POST['upload_file']) ) {
        $upload_dir = wp_upload_dir();
 
        if ( ! empty( $upload_dir['basedir'] ) ) {
            $user_dirname = $upload_dir['basedir'].'/documents/'.$eid;
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }
 
            $filename = wp_unique_filename( $user_dirname, $_FILES['file']['name'] );
            
            move_uploaded_file(sanitize_text_field($_FILES['file']['tmp_name']), $user_dirname .'/'. $filename);
            
            $image=$upload_dir['baseurl'].'/documents/'.$eid.'/'.$filename;
            // save into database $image;
      
            global $wpdb;   

$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}contest_documents ( eid, documents,user_id,documents_name) VALUES ( %d,%s,%d,%s)", array( $eid,$image,$user_id,$filename) ) );
        }
    }
ob_start();


if(!isset($eid))
{

	
	 $url  =  "/wp-admin/admin.php?page=aistore_user_contest_list";

?>
	<div><a href="<?php echo $url ; ?>" >
	    <?php   _e( 'Go To Contest List Page', 'aistore' ); ?> 
	     </a></div>
<?php	

return ob_get_clean();  
}



global $wpdb;







if(isset($_POST['submit']) and $_POST['action']=='approved')
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify', 'aistore' ) ;
    
}  

$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}contest
    SET status = '%s'  WHERE id = '%d'", 
   'approved' , $eid) );

}
 
  
  $escrow = $wpdb->get_row( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest WHERE id=%s", $eid) 
                 );
 
 
 
 
 ?>
	  <div>
	        <div class="alert alert-success" role="alert">
 <strong>Escrow Status   <?php echo $escrow->status;?></strong>
  </div>
	  
	      <?php
	      
     echo "<h1>#". $escrow->id ." ".$escrow->title ."</h1><br>";
     
     
  printf(__( "Description : %s", 'aistore' ),html_entity_decode($escrow->term_condition)."<br>");

  printf(__( "Status : %s", 'aistore' ),$escrow->status."<br><br>");
  if($escrow->status=='pending'){
 ?>
  <form method="POST" action="" name="approved" enctype="multipart/form-data"> 
 
<?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
<input type="hidden" name="escrow_id"  id="escrow_id" value="<?php echo $escrow->id; ?>" />
  <input type="submit"  name="submit" value="<?php  _e( 'Approved', 'aistore' ) ?>">
  <input type="hidden" name="action" value="approved" />
</form>


<?php
}
$eid=  $escrow->id;
 
  global $wpdb;
   $contest_documents = $wpdb->get_results( 
$wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_documents WHERE eid=%d", $eid)  );
 
  
?> 
  
    <table class="table">
    <?php
    foreach($contest_documents as $row):
     
    ?> 
	
	<div class="document_list">
   


  <p><a href="<?php echo $row->documents; ?>" target="_blank">
	       <b><?php echo $row->documents_name ; ?></b></a></p>
  <h6 > <?php echo $row->created_at; ?></h6>
</div>

<hr>
    
    <?php endforeach;
    
    
    ?>
    </table>
<br>
	   <div>  
    

   

	<label for="documents"> <?php   _e( 'Documents', 'aistore' ); ?> : </label>
<form  method="post"  action="<?php echo admin_url('admin-ajax.php').'?action=custom_action&eid='.$eid; ?>" class="dropzone" id="dropzone">
    <?php 
wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' );
?>
  <div class="fallback">
    <input id="file" name="file" type="file" multiple  />
    <input type="hidden" name="action" value="custom_action" type="submit"  />
  </div>

</form>
     
     </div>

  


<?php

}

 
	




 


// page Setting

function aistore_contest_page_register_setting() {
	//register our settings

	
	register_setting( 'aistore_contest_page', 'add_contest_page_id' );
	register_setting( 'aistore_contest_page', 'contest_create_fee' );
	 register_setting( 'aistore_contest_page', 'list_contest_page_id' );
	register_setting( 'aistore_contest_page', 'details_contest_page_id' );

     register_setting( 'aistore_contest_page', 'aistore_contest_list_page' );
      register_setting( 'aistore_contest_page', 'contest_list' );
      
	register_setting( 'aistore_contest_page', 'contest_user_id' );
	
	

}

 function aistore_contest_page_setting() {
	 
	  $pages = get_pages(); 
	
	   ?>
	  <div class="wrap">
	  
	  <div class="card">
	  
<h3><?php  _e( 'Contest Setting', 'aistore' ) ?></h3>
 
	                     
<p><?php  _e( 'Step 1', 'aistore' ) ?></p>


<p><?php  _e( 'Install required plugin Escrow System link https://wordpress.org/plugins/saksh-escrow-system  and activate as per their setup process. ', 'aistore' ) ?></p>

<hr />

  
<p><?php  _e( 'Step 2', 'aistore' ) ?></p>

<?php
if(isset($_POST['submit']) and $_POST['action']=='create_all_pages' )
{

if ( ! isset( $_POST['aistore_nonce'] ) 
    || ! wp_verify_nonce( $_POST['aistore_nonce'], 'aistore_nonce_action' ) 
) {
   return  _e( 'Sorry, your nonce did not verify.', 'aistore' );

   exit;
}

$contest_user_id=sanitize_text_field($_REQUEST['contest_user_id']);

 $my_post = array(
   'post_title'     => 'Contest',
    'post_type'     => 'page',
   'post_content'  => '[aistore_contest]',
   'post_status'   => 'publish',
   'post_author'   => 1
 );

 // Insert the post into the database
$add_contest_page_id=wp_insert_post( $my_post );


update_option( 'add_contest_page_id', $add_contest_page_id);




 $my_post = array(
   'post_title'     => 'Contest List',
    'post_type'     => 'page',
   'post_content'  => '[aistore_contest_list] ',
   'post_status'   => 'publish',
   'post_author'   => 1
 );

 // Insert the post into the database
$list_contest_page_id=wp_insert_post( $my_post );


update_option( 'list_contest_page_id', $list_contest_page_id);

 $my_post = array(
   'post_title'     => 'All Contest List ',
    'post_type'     => 'page',
   'post_content'  => '[aistore_contest_list_page] ',
   'post_status'   => 'publish',
   'post_author'   => 1
 );

 // Insert the post into the database
$aistore_contest_list_page=wp_insert_post( $my_post );

//
 $my_post = array(
   'post_title'     => 'Contest List',
    'post_type'     => 'page',
   'post_content'  => '[contest_list] ',
   'post_status'   => 'publish',
   'post_author'   => 1
 );

 // Insert the post into the database
$contest_list=wp_insert_post( $my_post );
update_option( 'contest_list', $contest_list);

//

update_option( 'aistore_contest_list_page', $aistore_contest_list_page);

 $my_post = array(
      'post_title'     => 'Details Contest',
   'post_type'     => 'page',
   'post_title'    => 'Contest Detail',
   'post_content'  => '[aistore_contest_detail]',
   'post_status'   => 'publish',
   'post_author'   => 1
 );

 // Insert the post into the database
$details_escrow_page_id=wp_insert_post( $my_post );

update_option( 'details_contest_page_id', $details_contest_page_id);






 

   $user_id = username_exists( $contest_user_id );
   
if ( ! $user_id ) {
$user_id = wp_insert_user( array(
  'user_login' => $contest_user_id,
  'user_pass' => $contest_user_id,
  'user_email' => $contest_user_id,
  'first_name' => $contest_user_id,
  'last_name' => $contest_user_id,
  'display_name' => $contest_user_id,
  'role' => 'administrator'
));
update_option( 'contest_user_id', $user_id);
update_option( 'contest_user_name', $contest_user_id);
}
 

 $pages = get_pages(); 
}
else{
    
     $pages = get_pages(); 
?>
 <form method="POST" action="" name="create_all_pages" enctype="multipart/form-data"> 
    <?php wp_nonce_field( 'aistore_nonce_action', 'aistore_nonce' ); ?>
    
<p><?php  _e( 'Create all pages with short codes automatically to ', 'aistore' ) ?>
<br><br>
<?php  _e( 'Contest Admin Email ID: ', 'aistore' ) ?>
<input type="email" name="contest_user_id" value="<?php echo esc_attr( get_option('contest_user_name') ); ?>" required />

<input class="input" type="submit" name="submit" value="<?php  _e( 'Click here', 'aistore' ) ?>"/>
<input type="hidden" name="action"  value="create_all_pages"/>
    </form>
    
<?php
}
?>
<p><?php  _e( 'Create 3 pages with short codes and select here  ', 'aistore' ) ?></p>


<form method="post" action="options.php">
    <?php settings_fields( 'aistore_contest_page' ); ?>
    <?php do_settings_sections( 'aistore_contest_page' ); ?>
	
    <table class="form-table">
         <tr valign="top">
        <th scope="row"><?php  _e( 'Create Contest form', 'aistore' ) ?></th>
        <td>
		<select name="add_contest_page_id"  >
		 
		 
     <?php 

                    foreach($pages as $page){ 
					
					if($page->ID==get_option('add_contest_page_id'))
					{
		 echo '	<option selected value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		  } else {
                      
   echo '	<option value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		

		}  
	 } ?> 
	 
	 
</select>


<p>Create a page add this shortcode <strong> [aistore_contest] </strong> and then select that page here. </p>

</td>
        </tr>  
        
        	
	
	
		
		  <tr valign="top">
        <th scope="row"><?php  _e( 'Contest List page', 'aistore' ) ?></th>
        <td>
		<select name="list_contest_page_id">
		  
		   <?php 
                    foreach($pages as $page){ 
					
					if($page->ID==get_option('list_contest_page_id'))
					{
		 echo '	<option selected value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		  } else {
                      
   echo '	<option  value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		

		}  
	 } ?> 

		   
		   
		   
</select>



<p>Create a page add this shortcode <strong> [aistore_contest_list] </strong> and then select that page here. </p>


</td>
        </tr>  
		
		
		 <tr valign="top">
        <th scope="row"><?php  _e( 'Contest Details Page', 'aistore' ) ?></th>
        <td>
		<select name="details_contest_page_id" >
		 
		 
		  <?php 
                    foreach($pages as $page){ 
                        
				

					if($page->ID==get_option('details_contest_page_id'))
					{
		 echo '	<option selected value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		  } else {
                      
   echo '	<option  value="'.$page->ID.'">'.$page->post_title .'</option>';
		 
		

		}  
	 } ?> 
	 
	 
		 
					  
					
 
</select>



<p>Create a page add this shortcode <strong> [aistore_contest_detail] </strong> and then select that page here. </p>




</td>
        </tr>
        
        
        
        
        
        
        
        
	 </table>
        
        	<hr/>
        	
<p><?php  _e( 'Step 3', 'aistore' ) ?></p>


<p><?php  _e( 'Create an admin account and set its ID this will be used to hold payments ', 'aistore' ) ?></p>

        	
        <table class="form-table">
        
        <h3><?php  _e( 'Admin Contest Setting', 'aistore' ) ?></h3>
        
		 <tr valign="top">
        <th scope="row"><?php  _e( 'Contest Admin ID ', 'aistore' ) ?></th>
        <td>
		<select name="contest_user_id" >
		 
		 
		  <?php 
		  
		   
        $blogusers = get_users( [ 'role__in' => [ 'administrator' ] ] );


                    foreach($blogusers as $user){ 
                        
					
					if($user->ID==get_option('contest_user_id'))
					{
		 echo '	<option selected value="'.$user->ID.'">'.$user->display_name .'</option>';
		 
		  } else {
                      
   echo '	<option  value="'.$user->ID.'">'.$user->display_name .'</option>';
		 
		

		}  
	 } ?> 
  </tr>  
</select>

<p><?php  _e( 'Add an user with admin roll and then select its ID here', 'aistore' ) ?></p>
 
              
  
    </table>
    

    	<hr/>
        	 
        	
<p><?php  _e( 'Step 4', 'aistore' ) ?></p>


<p><?php  _e( 'Set fee here for the profits percentage ', 'aistore' ) ?></p>


        <table class="form-table">
        
        <h3><?php  _e( 'Fee Setting', 'aistore' ) ?></h3>
        
	 
        
       	 <tr valign="top">
        <th scope="row"><?php  _e( 'Contest Create Fee', 'aistore' ) ?></th>
        <td><input type="number" name="contest_create_fee" value="<?php echo esc_attr( get_option('contest_create_fee') ); ?>" />%</td>
        </tr>
  
  
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
</div>
 <?php
	 
 }



}


    


if( is_admin() )
    $AistoreSettingsPage = new AistoreContestSettingsPage(); 


