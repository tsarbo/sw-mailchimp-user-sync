<h1>SW Mailchimp User Sync</h1>

<?php
	global $apikey;
	global $api;
	global $wp_roles;
	
/*
	foreach ( $wp_roles->roles as $key=>$value) {
				echo $key . '-' . $value['name'] . '<br>';
			}
*/
/*
	$user = array(
		'email'	=> 'gtsar@icloud.com',
		'firstname'	=>	'Foo',
		'lastname'	=>	'Bar'
	);
	$listid = 'b21874f009';
	
	$result = sync_single_user($user, $listid);
	
	echo '<h1>' . $result . '</h1>';
*/
	
	
?>

<?php settings_fields( 'sw-mchimp-sync-settings-group' ); ?>
<?php do_settings_sections( 'sw-mchimp-sync-settings-group' ); ?>

<div class="api-key-wrap">
	<label for="api-key">Enter your Mailchimp API Key <input name="api-key" id="api-key" type="text" placeholder="Your API key" value="<?php echo esc_attr( get_option('api_key') ); ?>" /> <?php submit_button('Submit', 'primary', 'submit-api', false); ?><span class="message"></span></label>
</div>

<div id="lists-roles-wrap">
	<div id="lists">
		<h3>Lists</h3>
		<?php
			$connections = get_option('list_connections');
			$retval = $api->lists();
			foreach ($retval['data'] as $list){
				
				?>
				<div class="list-item">
					<span class="list-name"><?php echo $list['name']; ?></span>
					<select name="user-roles-<?php echo $list['id'] ?>" class="user-select" multiple="multiple" data-listid="<?php echo $list['id'] ?>">
						<?php
							foreach ( $wp_roles->roles as $key=>$value) {
								if (in_array($key, $connections[$list['id']])) {
									$selected = ' selected="selected"';
								}
								else {
									$selected = '';
								}
								echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
							}
						?>
					</select>
				</div>
				<?php
				//echo $list['name']; 
				//echo "<br />";
			}
			
		?>
		<button class="button button-primary" id="add-conn">Save Connections</button><span class="message lists"></span>
	</div>
<!--
	<div id="roles">
		<h3>User Roles</h3>
		<?php
			$roles = $wp_roles->get_names();
			//print_r($wp_roles);
			
			
			foreach ($roles as $role) {
				echo $role . '<br>';
			}
		?>
	</div>
-->
</div>

<div id="conn-wrap">
	<h3>User Sync Connections</h3>
	<button class="button button-primary" id="manual-sync">Sync users now</button>
	<div id="sync-results"></div>
</div>


<script>
	jQuery(document).ready(function() {
	   	function saveApiKey() {
/*
		   	jQuery('#submit-api').click(function(){
			   	var apiKey = $('#api-key').val();
			   	if (apiKey != '') {
				   	var optdata = {
					   	action: 'saveApiKey',
					   	data: apiKey
				   	}
				   	jQuery.post()
			   	}
		   	});
*/
		   	jQuery('#submit-api').click(function(){
			   	//alert('ok');
			   	var apiKey = jQuery('#api-key').val();	
			   	//alert(apiKey);
			   	var data = {
						'action': 'saveApiKey',
						'api-key': apiKey
					};
			   	
			   	jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function(response) {
						if (response == 'updated') {
							jQuery('.api-key-wrap .message').html('Key Saved');
						}
						else if (response == 'not_updated') {
							jQuery('.api-key-wrap .message').html('Key already exists!');
						}
						else if (response == 'wrong_key') {
							jQuery('.api-key-wrap .message').html('Key is not valid!');
						}
						console.log('response2-' , response);
						
					}
				});	
				
			});
			
			jQuery('#add-conn').click(function(){
			   //	alert('ok');
			   var listsConn = {};	
			   jQuery('.user-select').each(function(){
				   	listsConn[jQuery(this).data('listid')] =  jQuery(this).val();
			   });
			   
			   console.log(listsConn);
			   	
			   	var data = {
						'action': 'saveListConn',
						'lists': listsConn
					};
			   	
			   	jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function(response) {
						if (response == 'updated') {
							jQuery('.lists.message').html('Key Saved');
						}
						else if (response == 'not_updated') {
							jQuery('.lists.message').html('Key already exists!');
						}
						else if (response == 'wrong_key') {
							jQuery('.lists.message').html('Key is not valid!');
						}
						console.log('response2-' , response);
						
					}
				});	
				
			});
			
			jQuery('#manual-sync').click(function(){
			   
			   
			   
			   	
			   	var data = {
						'action': 'syncUsers'
					};
			   	
			   	jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function(response) {
						jQuery('#sync-results').html(response);
						if (response == 'updated') {
							jQuery('#sync-results').html(response);
						}
						else if (response == 'not_updated') {
							jQuery('.lists.message').html('Key already exists!');
						}
						else if (response == 'wrong_key') {
							jQuery('.lists.message').html('Key is not valid!');
						}
						console.log('response2-' , response);
						
					}
				});	
				
			});
			
	   	}
	   	
	   	saveApiKey();
	   	
	   	jQuery('.user-select').each(function(){
		   	jQuery(this).select2();
	   	});
	});
</script>

<style>
#lists-roles-wrap {
	text-align: center;
}
#lists-roles-wrap > div {
	display: inline-block;
	vertical-align: top;
	padding: 20px;
}
</style>