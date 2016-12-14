<?php
/*
Plugin Name: SW Mailchimp User Sync
Description: Mailchimp User Sync
Version: 0..1
Plugin URI: https://simplyweb.gr/
Author: Giorgos Tsarmpopoulos
Author URI: https://simplyweb.gr/
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//include( plugin_dir_path( __FILE__ ) . 'sw-functions.php');

require_once (plugin_dir_path( __FILE__ ) . 'MCAPI.class.php');

global $apikey;
global $api;

//$apikey='fb9e5f1134166a77ba7a6d6a997161bb-us13'; // Enter your MailChimp API key here
$apikey = esc_attr( get_option('api_key'));
$api = new MCAPI($apikey);



add_action( 'admin_menu', 'sw_mchimp_sync_menu' );

/** Step 1. */
function sw_mchimp_sync_menu() {
	add_options_page( 'SW Mailchimp Sync Settings', 'SW Mailchimp Sync', 'manage_options', 'sw_mchimp_sync', 'sw_mchimp_sync_options' );
	add_action( 'admin_init', 'register_sw_mchimp_sync_settings' );
}

/** Step 3. */
function sw_mchimp_sync_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	include( plugin_dir_path( __FILE__ ) . 'options-view.php');
	

}

function register_sw_mchimp_sync_settings() {
	//register our settings
	register_setting( 'sw-mchimp-sync-settings-group', 'api_key' );
	register_setting( 'sw-mchimp-sync-settings-group', 'list_connections' );
}


function my_enqueue($hook) {
/*
    if ( 'options-general.php' != $hook ) {
        return;
    }
*/

    wp_enqueue_script( 'select2js', plugin_dir_url( __FILE__ ) . 'assets/js/select2.min.js' );
    wp_enqueue_style( 'select2css', plugin_dir_url( __FILE__ ) . 'assets/css/select2.min.css' );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );

function save_api_key() {
	
	$api_key = $_POST['api-key'];
	
	$test_api = new MCAPI($api_key);
	if ($test_api->lists()) {
		if(update_option('api_key', $api_key)) {
			exit('updated');
		}
		else {
			exit('not_updated');
		}
	}
	else {
		exit('wrong_key');
	}
}

add_action( 'wp_ajax_saveApiKey', 'save_api_key' );

function save_list_conn() {
	
	if (isset($_POST['lists'])) {
		$lists = $_POST['lists'];
		
		
		if(update_option('list_connections', $lists)) {
			exit('updated');
		}
		else {
			exit('not_updated');
		}
	}
	
}

add_action( 'wp_ajax_saveListConn', 'save_list_conn' );

/*
function sync_single_user($user, $listid) {
	error_log(print_r($user));
	error_log(print_r($listid));
	
	$apikey = esc_attr( get_option('api_key'));
	$api = new MCAPI($apikey);
	$retval = $api->lists();
	if ($user && $listid) {
		
		$email = $user['email'];
		$fname = $user['firstname'];
		$lname = $user['lastname'];
		$merge_vars = array('FNAME' => $fname, 'LNAME' => $lname);
		//if($api->listSubscribe($listid, $email,$merge_vars, 'html', false, true, false, false) === true) {
		if($api->listSubscribe($listid, $email,$merge_vars) === true) {
			return 'User ' . $email . ' added';
		}
		else {
			return 'User ' . $email . ' failed';;
		}
		
	}
	else {
		return 'data_missing';
	}
	
}
*/


/*
function sync_single_user($user, $listid) {
	
	$apikey = esc_attr( get_option('api_key'));
	$memberId = md5(strtolower($user['email']));
	
	$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
	
	$dataCenter = explode('-', $apikey);
	$dc = $dataCenter[1];
	$url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $listid . '/members/' . $memberId;
	
	error_log($dc);
	

	$json = json_encode([
        'email_address' => $user['email'],
        'status'        => 'subscribed', // "subscribed","unsubscribed","cleaned","pending"
        'merge_fields'  => [
            'FNAME'     => $user['firstname'],
            'LNAME'     => $user['lastname']
        ]
    ]);
    
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                                                                 

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode;
	
	
	
}
*/
function sync_single_user($user, $listid) {
	$apikey = esc_attr( get_option('api_key'));
	//fb9e5f1134166a77ba7a6d6a997161bb-us13
	
	$dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
	
	$dataCenter = explode('-', $apikey);
	$dc = $dataCenter[1];
	$url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $listid . '/members/';
	
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "{\n\t\"email_address\": \"" . $user['email'] . "\",\n\t\"unique_email_id\": \"" . $user['email'] . "\",\n\t\"email_type\": \"html\",\n\t\"status\": \"subscribed\",\n\t\"merge_fields\": {\n\t\t\"FNAME\": \"" . $user['firstname'] . "\",\n\t\t\"LNAME\": \"" . $user['lastname'] . "\"\n\t},\n\t\"list_id\": \"" . $listid . "\"\n}",
	  CURLOPT_HTTPHEADER => array(
	    "authorization: apikey " . $apikey,
	    "cache-control: no-cache",
	    "postman-token: 66cd45bb-34a2-fccb-2fb4-fce40f170b75"
	  ),
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if ($err) {
	  return "cURL Error #:" . $err;
	} else {
	  $result = json_decode($response);
	  return $result->email_address;
	}
	
	
	
	
	
	
	
	
	
	

	
	
	
}






function get_user_details_with_role($role) {
	if ($role) {
		$args = array(
			'role'	=> $role,
			'fields'	=>	'all_with_meta'
		);
		$users = get_users($args);
		$user_with_role = array();
		foreach ($users as $user) {
			$user_with_role[] = array(
				'firstname'	=> $user->first_name,
				'lastname'	=>	$user->last_name,
				'email'		=>	$user->user_email
			);
		}
		
		return $user_with_role;
	}
	else {
		return;
	}
	
}

function sync_users() {
	//error_log('in sync');
	$lists2sync = get_option('list_connections');
	//exit ( json_encode($lists2sync) );
	//error_log($lists2sync);
	foreach ($lists2sync as $listid=>$roles) {
		//error_log('in first foreach');
		
		$list_users = array();
		foreach ($roles as $role) {
			if (is_array(get_user_details_with_role($role))) {
				foreach (get_user_details_with_role($role) as $user) {
					$list_users[] = $user;
				}
				//array_merge($list_users, get_user_details_with_role($role));
			}
			
		}
		//exit(json_encode($list_users));
		foreach ($list_users as $user) {
			if (isset($user['email'], $user['firstname'], $user['lastname'] )) {
				$result = sync_single_user($user, $listid);
				echo $result;
			}
		}
	}
	exit($result);
}
add_action( 'wp_ajax_syncUsers', 'sync_users' );


//Setup Cron Job for mailchimp sync

if ( ! wp_next_scheduled( 'users_sync_cron' ) ) {
  wp_schedule_event( time(), 'daily', 'users_sync_cron' );
}


add_action( 'users_sync_cron', 'sync_users' );