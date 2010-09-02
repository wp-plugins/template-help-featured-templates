<?php
/*
Plugin Name: Template_Help Featured Templates
Description: Displays Featured Templates from TemplateHelp.com collection via ajax
Author: TemplateHelp.com
Version: 2.1
Author URI: http://www.mytemplatestorage.com
*/
add_action('wp_ajax_get_url', 'get_url');
add_action('wp_ajax_nopriv_get_url', 'get_url');
define('DEFAULT_AFF', 'wpincome');
define('DEFAULT_PASS', 'd98c52ec04d5ce98f6f000a6d2b65160');

function get_categories_list() {
	$cats = array();
	$file = @fopen("http://api.templatemonster.com/wpinc/categories.txt", "r");
	if ($file) {
		while ($fr = fgets($file, 1024)) {
			$fr = explode("\t", trim($fr));
			$cats[$fr[0]] = $fr[1];
		}
	}
	return $cats;
}
function get_types_list() {
	$types = array();
	$file = @fopen("http://api.templatemonster.com/wpinc/types.txt", "r");
	if ($file) {
		while ($fr = fgets($file, 1024)) {
			$fr = explode("\t", trim($fr));
			$types[$fr[0]] = $fr[1];
		}
	}
	return $types;
}
// This gets called at the plugins_loaded action
function widget_template_help_init() {

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_template_help_control() {
		$options = $newoptions = get_option('widget_template_help');
		if ( $_POST['template_help-submit'] ) {
			/*title*/
			$newoptions['title'] = strip_tags(stripslashes($_POST['template_help-title']));
			/*aff*/
			$newoptions['aff'] = strip_tags(stripslashes($_POST['template_help-aff']));
			/*wap*/
			$newoptions['wap'] = strip_tags(stripslashes($_POST['template_help-wap']));
			/*position*/
			$newoptions['position'] = intval($_POST['template_help-position']);
			/*count*/
			$newoptions['count'] = (int) $_POST['template_help-count'];
			if(($newoptions['count']<1)||($newoptions['count']>10))
				$newoptions['count']=3;
			/*fullview*/
			$newoptions['fullview'] = intval($_POST['template_help-fullview']);
			/*cat*/
			$newoptions['cat'] = strip_tags(stripslashes($_POST['template_help-cat']));
			/*type*/
			$newoptions['type'] = strip_tags(stripslashes($_POST['template_help-type']));
		}
		if ($options['aff'] == '') {
			$newoptions['aff'] = DEFAULT_AFF;
			$newoptions['wap'] = DEFAULT_PASS;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_template_help', $options);
		}
		echo '		<div style="text-align:right">
				<label for="template_help-title" style="line-height:35px;display:block;">';
				_e('Widget title:', 'widgets');
				echo '<input type="text" id="template_help-title" name="template_help-title" value="'.wp_specialchars($options['title'], true).'" />
				</label>

				<label for="template_help-aff" style="line-height:35px;display:block;">';
				_e('Affiliate:', 'widgets');
				echo '<input type="text" id="template_help-aff" name="template_help-aff" value="'.wp_specialchars($options['aff'], true).'" />
				</label>

				<label for="template_help-wap" style="line-height:35px;display:block;">';
				_e('WebAPI Password:', 'widgets');
				echo '<input type="text" id="template_help-wap" name="template_help-wap" value="'.wp_specialchars($options['wap'], true).'" />
				</label>

				<label for="template_help-position" style="line-height:35px;display:block;">';
				_e('Templates position :', 'widgets');
				$position = wp_specialchars($options['position'], true);
				echo '<input type="radio"	name="template_help-position" value="0"'.($position == 0 ? " checked" : "").'/> Ver
				<input type="radio"	name="template_help-position" value="1"'.($position == 1 ? " checked" : "").'/> Hor
				</label>

				<label for="template_help-count" style="line-height:35px;display:block;">';
				_e('Number of templates to display: (1-10)', 'widgets');
				echo '<input type="text" id="template_help-count" name="template_help-count" value="'.$options['count'].'" style="width:18px" />
				</label>

				<label for="template_help-fullview" style="line-height:35px;display:block;">';
				_e('Display template\'s information :', 'widgets');
				$fullview = wp_specialchars($options['fullview'], true);
				echo '<br/>
				<input type="radio"	name="template_help-fullview" value="1"'.($fullview == 1 ? " checked" : "").'/> Full Details
				<input type="radio"	name="template_help-fullview" value="0"'.($fullview == 0 ? " checked" : "").'/> Shorten Preview
				</label>

				<label for="template_help-cats" style="line-height:35px;display:block;">';
				_e('Categories:', 'widgets');
				echo '</label>
				<select style="width:170px;font-size:11px;" id="template_help-cats" name="template_help-cat">
						<option value="All" '.("All" == $options['cat'] ? "selected=true" : "" ).'>Show all</option>';
          $cats = get_categories_list();
					foreach ($cats as $id => $name) {
						echo '<option value="'.$id.'" '.($id == $options['cat'] ? "selected=true" : "" ).'>'.$name.'</option>';
					}
       	echo '</select>

				<label for="template_help-types" style="line-height:35px;display:block;">';
				_e('Types:', 'widgets');
				echo '</label>
				<select style="width:170px;font-size:11px;" id="template_help-types" name="template_help-type">
					<option value="All" '.("All" == $options['type'] ? "selected=true" : "" ).'>Show all</option>';
          $types = get_types_list();
					foreach ($types as $id => $name) {
						echo '<option value="'.$id.'" '.($id == $options['type'] ? "selected=true" : "" ).'>'.$name.'</option>';
					}
				echo '
       	</select>

				<input type="hidden" name="template_help-submit" id="template_help-submit" value="1" />
				</div>';
	}

	// This prints the widget
	function widget_template_help($args) {
		extract($args);
		$options = (array) get_option('widget_template_help');
		echo $before_widget;
		echo '<center>'.$before_title . $options['title'] . $after_title.'</center>
		<table align="center" id="templates"><tr>';
		for ($i=1; $i<=$options['count']; $i++) {
    	echo '
    	<td align="center" style="padding:3px">
    	<div class="ft_image">
				<a href="#" target="_blank">
					<div style="border: 1px solid #b9babc;width:143px;height:154px;background:#fff;">
						<img src="/wp-content/plugins/template-help_wordpress/ajax-loader.gif" style="border:0px;padding:62px 57px;"/>
					</div>
				</a>
				<div class="bottext">
					<a target="_blank" class="view" href="#">View Template</a>
				</div>
			</div>
    	</td>';
    	if($options['position']==0) {
    	echo '</tr><tr>';
    	}
		}
		echo '
		</tr>
		</table>';
		echo $after_widget;
		echo '
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script>
			$(function(){
				$.getJSON("/wp-admin/admin-ajax.php",
				{action:"get_url"},
				function(data){
					if (typeof(data.error) != "undefined" && !data.error) {
						imgs = new Array();
						$.each($("#templates .ft_image"), function(i, item) {
							if (data.templates[i]) {
								var $obj = $(this);
								imgs[i] = new Image();
		  					jQuery(imgs[i]).load(function(){
		  						$obj.find("a div img").fadeOut();
		  						$obj.find("a div").animate({width:imgs[i].width, height:imgs[i].height}, 400, function(){
		  							$(this).find("img").css("padding", "0px").attr("src", data.templates[i].src).fadeIn();
		  						});
		  					}).attr("src",data.templates[i].src);
								$(this).find("a").attr("href", data.templates[i].href);
								if ('.$options['fullview'].') {
									$(this).find(".bottext").prepend("<a href=\'"+data.templates[i].cart+"\' target=\'_blank\'>Price : \$"+data.templates[i].price+"</a> | <a href=\'"+data.templates[i].href+"\' target=\'_blank\'>Details</a><br/>Downloads : "+data.templates[i].downloads);
								}
							} else {
								$(this).remove();
							}
						});
						if ('.$options['fullview'].') {
							$("#templates .bottext .view").remove();
						}
					} else {
						$.each($("#templates .ft_image"), function(i, item) {
							$(this).find("a div").css({width:"145px", height:"156px", background:"url(/wp-content/plugins/template-help_wordpress/preload-template.jpg)", border: "0px"}).html("");
							$(this).find("a").attr("href", "http://www.templatemonster.com/?aff='.trim($options['aff']).'");
						});
					}
				});
			});
		</script>';
	}

	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Theme Widget from Template-Help', 'widgets'), 'widget_template_help');
	register_widget_control(array('Theme Widget from Template-Help', 'widgets'), 'widget_template_help_control');

}

if (!function_exists('curl_get_file_contents')) {
	function curl_get_file_contents($URL) {
	  $c = curl_init();
	  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($c, CURLOPT_TIMEOUT, 60);//in seconds
	  curl_setopt($c, CURLOPT_AUTOREFERER, TRUE);
	  curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
	  curl_setopt($c, CURLOPT_MAXREDIRS, 8);
	  curl_setopt($c, CURLOPT_FRESH_CONNECT, TRUE);

	  curl_setopt($c, CURLOPT_URL, $URL);
	  $contents = trim(curl_exec($c));
	  if(curl_errno($c)) {
	  	$result = curl_errno($c) ? false : $contents;
	  }
	  curl_close($c);
	  return $contents;
	}
}

function get_url() {
	header('Cache-control: no-cache');
	$options = (array) get_option('widget_template_help');
	$type = intval($options['type']);
	$cat = intval($options['cat']);
	$count = intval($options['count']);
	if (!$count)
		$count=3;
	$aff = trim($options['aff']);
	$wap = trim($options['wap']);
	if ($aff=='') {
		$aff = DEFAULT_AFF;
		$wap = DEFAULT_PASS;
	}
	$file = curl_get_file_contents('http://api.templatemonster.com/wpinc.php?login='.$aff.'&webapipassword='.$wap.'&type='.$type.'&cat='.$cat.'&count='.$count);
	$items = (strpos($file, 'Unauthorized usage')!==false) ? array() : explode("\n", $file);
	$templates = array();
	if (!empty($items)) {
		foreach ($items as $i=>$item) {
			if (!empty($item)) {
				$template=explode("|", $item);
				$templates[$i]['src']=$template[0];
				$templates[$i]['href']=$template[1]."?aff=$aff";
				$templates[$i]['price']=$template[2];
				$templates[$i]['downloads']=$template[3];
				$templates[$i]['cart']=$template[4]."&aff=".$aff;
			}
		}
	}
	echo json_encode(array('templates'=>$templates, 'error'=>empty($templates)));
	exit;
}

function th_admin_warnings() {
	$options = (array) get_option('widget_template_help');
	if ($options['aff'] == '' || $options['aff'] == DEFAULT_AFF) {
		function th_warning() {
			echo '
			<div id="th-warning" class="updated fade"><p><strong>Template-Help Widget is almost ready.</strong> You must <a href="/wp-admin/widgets.php">configure Affiliate and WebAPI Password</a> for it to work.</p></div>
			';
		}
		add_action('admin_notices', 'th_warning');
	}
}


// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('widgets_init', 'widget_template_help_init');
th_admin_warnings();

?>
