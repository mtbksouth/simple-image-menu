<?php
/*
Plugin Name: Simple Image Menu
Plugin URI: http://mikekelsey.com
Description: Add simple images into your menu
Version: 1.0
Author: Mike Kelsey
Author URI: http://mikekelsey.com
*/

add_action('admin_menu', 'add_SIM_admin_menu');
function add_SIM_admin_menu(){
	add_theme_page( 'Simple Image Menu Images', 'Simple Image Menu Images', 'edit_theme_options', 'SIM-menu', 'create_SIM_admin_menu');
}
function create_sim_admin_menu(){
	if (!current_user_can('edit_theme_options')) {
		wp_die( __('You do not have sufficient permission to access this page.') );
	}
	if(isset($_POST['nav_menu']) && isset($_POST['save_association'])){
		//user has selected images to go with the nav links. Save them.
		$error			= false;
		$associations	= array();
		foreach($_POST as $key=>$val){
			if(substr($key, 0,12) == 'association_'){
				$menu_item_id					= intval(substr($key,12));
				$associations[$menu_item_id]	= $val;
			}
		}
		if(empty($associations)) {
			echo '<br />Error saving: no images to save. Please try again.<br />';
			//var_dump($associations);
			$error = true;
		}
		//var_dump($associations);
		//die();
		$nav_menu_id = intval($_POST['nav_menu']);
		if(!is_nav_menu($nav_menu_id)){
			echo '<br />Error saving, nav menu seems not to exist.<br />';
			$error = true;
		}
		if($error == false){
			//add_option sanitizes, so let's just stick it right in there
			if(update_option('SIM_menu_image_associations_'.$nav_menu_id,$associations)){
				echo '<br /><h2>Saved.</h2><br />';
				unset($_POST['nav_menu']);
				//unset($_POST['nav_menu']);
			} else echo '<br />Error saving.<br />';
		}
	}
	echo '<h2>Simple Image Menu: add images to a menu</h2>
		<form method="post" action="">';
		
	if (isset($_POST['nav_menu'])){
		//user has selected a nav menu to associate images with. Show interface permitting this.
		$nav_menu_id = intval($_POST['nav_menu']);
		if(is_nav_menu($nav_menu_id)){
			echo '<input type="hidden" name="nav_menu" value="'.$nav_menu_id.'" />';
			$associations = get_option('SIM_menu_image_associations_'.$nav_menu_id);
			$items = wp_get_nav_menu_items($nav_menu_id);
			echo '<input type="hidden" name="save_association" value="1" />
				<table>
					<tr>
						<th>Menu Item</th>
						<th>Choose An Image</th>
						<th>Status</th>
					</tr>';
			foreach($items as $item_obj){
				echo '<tr>
						<td>
							'.$item_obj->title.'
							<input type="hidden" name="association_'.$item_obj->ID.'" id="association_'.$item_obj->ID.'" value="'.$associations[$item_obj->ID].'" /> 
						</td>
						<td>
							<input class="upload_image_to_associate" type="button" id="btn_'.$item_obj->ID.'" value="Choose Image" />
						</td>
						<td id="status_'.$item_obj->ID.'">
							'.(isset($associations[$item_obj->ID])?'<img src="'.$associations[$item_obj->ID].'" width="50px" />':'No Image Chosen').'
						</td>
					</tr>';
			}
			echo '</table>';
		} else wp_die ('That is not a menu.');
	} else {
		echo 'Choose a menu: <select name="nav_menu" id="nav_menu">';
		//populate this with all the menus.
		$nav_menus = wp_get_nav_menus();
		foreach ($nav_menus as $menu_obj) {
			echo '<option value="' . $menu_obj->term_id . '">' . $menu_obj->name . '</option>';
		}
		//TODO: add javascript to make changing this submit the form automatically, and clear any image associations indicated
		echo '</select>';
		//var_dump($nav_menus);
	}
		
	echo '<br /><input type="submit" value="Submit" />
		</form>';
}
//this function adds the img uri to the menu object itself so the walker filter function can access it
function SIM_modify_menu_obj($content,$menu,$args){	
	$associations = get_option('SIM_menu_image_associations_'.$menu->term_id);
	foreach($content as $k=>$obj){
		$content[$k]->SIM_image = isset($associations[$content[$k]->ID])?$associations[$content[$k]->ID]:'';
		//$content[$k]->SIM_menu_id	= $menu->term_id;
	}
	return($content);
}
//this function adds the img uri to the HTML, accessing it from to the menu object itself, which we have modified using SIM_modify_menu_obj
function SIM_modify_menu_html($item_output, $item, $depth, $args){
	$item_output = str_replace('!!!SIM_IMG!!!', '<img src="'.$item->SIM_image.'" />', $item_output);
	return $item_output;
}

add_filter('wp_get_nav_menu_items','SIM_modify_menu_obj',NULL,3);
add_filter('walker_nav_menu_start_el','SIM_modify_menu_html',NULL,4);

function enqueue_admin_scripts($hook) {
	if($hook == 'appearance_page_SIM-menu') {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		//TODO: maybe better way of doing this than constant var?
		wp_register_script('SIM-admin', WP_PLUGIN_URL.'/simple-image-menu/admin.js', array('jquery','media-upload','thickbox'));
		wp_enqueue_script('SIM-admin');
	}
}
 
function enqueue_admin_styles() {
	wp_enqueue_style('thickbox');
}
//probably a better way to do this:
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');
add_action('admin_print_styles', 'enqueue_admin_styles');

?>
