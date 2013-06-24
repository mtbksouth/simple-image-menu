var current_nav_to_associate = '';
jQuery(document).ready(function() { 
    jQuery('.upload_image_to_associate').click(function() {  
		tb_show('Upload an image', 'media-upload.php?referer=SIM-menu&type=image&TB_iframe=true&post_id=0', false); 
		current_nav_to_associate = jQuery(this).attr('id');
		current_nav_to_associate = current_nav_to_associate.substr(4);
		//alert(current_nav_to_associate);
		return false;  
    });
	//TODO: get the ID, not the URL
	window.send_to_editor = function(html) {
		//alert(html);
		imgurl = jQuery('img',html).attr('src');
		jQuery('#association_'+current_nav_to_associate).val(imgurl);
		jQuery('#status_'+current_nav_to_associate).html('<span style="color:#FF00FF;">Image Ready</span>');
		tb_remove();
	}
});
