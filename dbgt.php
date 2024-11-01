<?php
/**
 * @Smart Gallery DBGT
 * @version 1.1.8
 */
 
/*
Plugin Name: Smart Gallery DBGT
Description: 🇬🇧 Ultra light plugin to display a random image gallery based on the Flickr / Pixabay library by simply entering the keyword of your choice + the number of images to display.  🇫🇷 Plugin ultra léger pour afficher une gallerie d'images au hasard basée sur la librairie Flickr / Pixabay en saisissant simplement le mot clé de votre choix + le nombre d'images à afficher.
Version: 1.1.8
Author: Kapsule Network
Author URI: https://www.kapsulecorp.com/
License: GPLv2

*/

if ( ! defined( 'ABSPATH' ) )
	exit;

define('ASGDBGT_ROOTPATH', plugin_dir_path( __FILE__ )); // Chemin Serveur

$smartgallery_dbgt_library = get_option('puipui_dbgt_form_option_library');
if (empty($smartgallery_dbgt_library)) {$smartgallery_dbgt_library = "flickr";} // Default

$smartgallery_dbgt_apikey = get_option('puipui_dbgt_form_option_apikey'); // API Pixabay

$puipui_dbgt_form_option_safesearch = get_option('puipui_dbgt_form_option_safesearch'); 

if ((empty($puipui_dbgt_form_option_safesearch)) OR ($puipui_dbgt_form_option_safesearch == "yes")) {
		
	$safe_search_parameter = "&safesearch=true";
				
} else {
				
	$safe_search_parameter = "&safesearch=false";
				
}
			
require_once(ASGDBGT_ROOTPATH.'/widget.php');

// PREMIUM VERSION //

// Test de la Validité de la licence //
require_once(ASGDBGT_ROOTPATH.'/inc/valid_api.php');

$dbgt_doubleface = false;
$dbgt_doubleface = check_kapsuleapi_dbgt_licence();

if ($dbgt_doubleface == "cUrl") {
	
	$msgerreur = "Votre serveur n'arrive pas à se connecter à notre API - cURL error 28: Operation timed out - L'erreur semble venir de votre solution d'hébergement car tout fonctionne du côté des serveurs de DBGT Gallery";$dbgt_doubleface = false;
	
} elseif ($dbgt_doubleface == "Erreur") {
	
	$msgerreur = "Votre serveur n'arrive pas à se connecter à notre API"; 
	$dbgt_doubleface=false;
	
 } else {
	 
	 $msgerreur = "";
	 
}

define('BABYVEGETA', $dbgt_doubleface); // API ACTIVE ou NON

////////////////////////



//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
////// ZONE ADMIN
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////

if (is_admin()){
	
	
	if ((BABYVEGETA != "Erreur") AND (BABYVEGETA != false)) { 
	
		// add tab to media section
		function dbgt_gallery_media_tabs_handler($tabs) { 
			$tabs['pixabaytab'] = __('⚡ Pixabay', 'dbgt_pxbay_images');
			return $tabs;
		}
		add_filter('media_upload_tabs', 'dbgt_gallery_media_tabs_handler');

		// add PREMIUM button
		function dbgt_gallery_button_premium(){
			echo '<a href="'.add_query_arg('tab', 'pixabaytab', esc_url(get_upload_iframe_src())).'" id="add_media" class="thickbox button">⚡ Flash Pics</a>';
		}
		add_action( 'media_buttons', 'dbgt_gallery_button_premium' );

		// Dynamic Image Picker
		// Forks of Pixabay Images 3.4 // Author: Simon Steinberger

		function media_dbgt_pxbay_images_tab() {
			
			global $smartgallery_dbgt_apikey;
			global $safe_search_parameter;
			
			media_upload_header();
			if ( 'fr_' === substr( get_user_locale(), 0, 3 ) ) {$langage = "&lang=fr-fr";} else {$langage = "&lang=en-us";} 
			?>
				<div style="padding:10px 15px 25px">
					<form id="dbgt_pxbay_images_form" style="margin:0">
						<div style="line-height:1.5;margin:1em 0;max-width:500px;position:relative">
							<input id="q" type="text" value="" style="width:100%;padding:7px 32px 7px 9px" autofocus placeholder="<?= htmlspecialchars(__('Your Search : Rose, Taxi, Ski ...', 'dbgt_pxbay_images')); ?>">
							<button type="submit" style="background:#fff;border:0;cursor:pointer;position:absolute;right:5px;top:10px;outline:0" title="<?= _e('Search', 'dbgt_pxbay_images'); ?>">
								<img src="<?= plugin_dir_url(__FILE__).'img/search.png' ?>">
							</button>
						</div>
						<div style="margin:1em 0;padding-left:2px;line-height:2">
							<label style="margin-right:15px;white-space:nowrap"><input type="checkbox" id="filter_photos"><?= _e('Photos', 'dbgt_pxbay_images'); ?></label>
							<label style="margin-right:20px;white-space:nowrap"><input type="checkbox" id="filter_cliparts"><?= _e('Cliparts', 'dbgt_pxbay_images'); ?></label>
							<label style="margin-right:15px;white-space:nowrap"><input type="checkbox" id="filter_horizontal"><?= _e('Horizontal', 'dbgt_pxbay_images'); ?></label>
							<label style="margin-right:25px;white-space:nowrap"><input type="checkbox" id="filter_vertical"><?= _e('Vertical', 'dbgt_pxbay_images'); ?></label>
							<a target="_blank" href="admin.php?page=dbgt-smart-gallery">⚙️</a>
						</div>
					</form>
					<div id="pixabay_results" class="dbgt-bustaflex-pix" style="margin-top:20px;padding-top:25px;border-top:1px solid #ddd"></div>
				</div>
				<script>
					// flexImages
					!function(t){function e(t,a,r,n){function o(t){r.maxRows&&d>r.maxRows||r.truncate&&t&&d>1?w[g][0].style.display="none":(w[g][4]&&(w[g][3].attr("src",w[g][4]),w[g][4]=""),w[g][0].style.width=l+"px",w[g][0].style.height=u+"px",w[g][0].style.display="block")}var g,l,s=1,d=1,f=t.width()-2,w=[],c=0,u=r.rowHeight;for(f||(f=t.width()-2),i=0;i<a.length;i++)if(w.push(a[i]),c+=a[i][2]+r.margin,c>=f){var m=w.length*r.margin;for(s=(f-m)/(c-m),u=Math.ceil(r.rowHeight*s),exact_w=0,l,g=0;g<w.length;g++)l=Math.ceil(w[g][2]*s),exact_w+=l+r.margin,exact_w>f&&(l-=exact_w-f),o();w=[],c=0,d++}for(g=0;g<w.length;g++)l=Math.floor(w[g][2]*s),h=Math.floor(r.rowHeight*s),o(!0);n||f==t.width()||e(t,a,r,!0)}t.fn.flexImages=function(a){var i=t.extend({container:".item",object:"img",rowHeight:180,maxRows:0,truncate:0},a);return this.each(function(){var a=t(this),r=t(a).find(i.container),n=[],o=(new Date).getTime(),h=window.getComputedStyle?getComputedStyle(r[0],null):r[0].currentStyle;for(i.margin=(parseInt(h.marginLeft)||0)+(parseInt(h.marginRight)||0)+(Math.round(parseFloat(h.borderLeftWidth))||0)+(Math.round(parseFloat(h.borderRightWidth))||0),j=0;j<r.length;j++){var g=r[j],l=parseInt(g.getAttribute("data-w")),s=l*(i.rowHeight/parseInt(g.getAttribute("data-h"))),d=t(g).find(i.object);n.push([g,l,s,d,d.data("src")])}e(a,n,i),t(window).off("resize.flexImages"+a.data("flex-t")),t(window).on("resize.flexImages"+o,function(){e(a,n,i)}),a.data("flex-t",o)})}}(jQuery);
					function getCookie(k){return(document.cookie.match('(^|; )'+k+'=([^;]*)')||0)[2]}
					function setCookie(n,v,d,s){var o=new Date;o.setTime(o.getTime()+864e5*d+1000*(s||0)),document.cookie=n+"="+v+";path=/;expires="+o.toGMTString()}
					function escapejs(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,"\\'");}
					// set checkbox filters
					jQuery("input[id^='filter_']").each(function(){
						if (getCookie('px_'+this.id) != '0') this.checked = true;
						jQuery(this).change(function(){ setCookie('px_'+this.id, this.checked ? 1 : 0, 365); });
					});
					var post_id = <?php echo absint($_REQUEST['post_id']); ?>,
						lang = '<?php echo $langage; ?>',
						per_page = 30, form = jQuery('#dbgt_pxbay_images_form'), hits, q, image_type, orientation;
					form.submit(function(e){
						e.preventDefault();
						q = jQuery('#q', form).val();
						if (jQuery('#filter_photos', form).is(':checked') && !jQuery('#filter_cliparts', form).is(':checked')) image_type = 'photo';
						else if (!jQuery('#filter_photos', form).is(':checked') && jQuery('#filter_cliparts', form).is(':checked')) image_type = 'clipart';
						else image_type = 'all';
						if (jQuery('#filter_horizontal', form).is(':checked') && !jQuery('#filter_vertical', form).is(':checked')) orientation = 'horizontal';
						else if (!jQuery('#filter_horizontal', form).is(':checked') && jQuery('#filter_vertical', form).is(':checked')) orientation = 'vertical';
						else orientation = 'all';
						jQuery('#pixabay_results').html('');
						call_api(q, 1);
					});
					function call_api(q, p){
						var xhr = new XMLHttpRequest();
						xhr.open('GET', 'https://pixabay.com/api/?key=<?php echo $smartgallery_dbgt_apikey; echo $safe_search_parameter; ?>'+lang+'&image_type='+image_type+'&orientation='+orientation+'&per_page='+per_page+'&page='+p+'&search_term='+encodeURIComponent(q));
						xhr.onreadystatechange = function(){
							if (this.status == 200 && this.readyState == 4) {
								var data = JSON.parse(this.responseText);
								if (!(data.totalHits > 0)) {
									jQuery('#pixabay_results').html('<div style="color:#bbb;font-size:24px;text-align:center;margin:40px 0">—— <?php echo "No Results"; ?> ——</div>');
									return false;
								}
								render_px_results(q, p, data);
							}
						};
						xhr.send();
						return false;
					}
					function render_px_results(q, p, data){
						hits = data['hits']; // store for upload click
						pages = Math.ceil(data.totalHits/per_page);
						var s = '';
						jQuery.each(data.hits, function(k, v) {
							s += '<div class="item upload" data-url="'+v.largeImageURL+'" data-user="'+v.user+'" data-w="'+v.webformatWidth+'" data-h="'+v.webformatHeight+'"><img src="'+v.previewURL.replace('_150', v.previewURL.indexOf('cdn.') > -1 ? '__340' : '_340')+'"><div class="download"><button class="catchpx" onclick="dbgt_gallery_premium_statham('+v.id+')"><img src="<?= plugin_dir_url(__FILE__).'img/download.svg' ?>"></button><div class="dbgtinfo">'+(v.webformatWidth*2)+'×'+(v.webformatHeight*2)+'<br><a href="https://pixabay.com/users/'+v.user+'/" target="_blank">'+v.user+'</a> @ <a href="'+v.pageURL+'" target="_blank">Pixabay</a></div></div></div>';
						});
						jQuery('#pixabay_results').html(jQuery('#pixabay_results').html()+s);
						jQuery('#load_animation').remove();
						if (p < pages) {
							jQuery('#pixabay_results').after('<div id="load_animation" style="clear:both;padding:15px 0 0;text-align:center"><img style="width:60px" src="<?= plugin_dir_url(__FILE__).'img/loading.svg' ?>"></div>');
							jQuery(window).scroll(function() {
							   if(jQuery(window).scrollTop() + jQuery(window).height() > jQuery(document).height() - 400) {
								   jQuery(window).off('scroll');
								   call_api(q, p+1);
							   }
							});
						}
						jQuery('.dbgt-bustaflex-pix').flexImages({rowHeight: 260});
					}
					function dbgt_gallery_premium_statham(id) {
						jQuery(this).addClass('uploading').find('.download img').replaceWith('<img src="<?= plugin_dir_url(__FILE__).'img/loading.svg' ?>" style="height:80px !important">');
						var html = '[dbgtpremium id='+ id +' alt="" url=""]';
						var win = window.dialogArguments || opener || parent || top;
						win.send_to_editor(html);
						return false;
					}
				</script>
			<?php
		}
		
		// Launch IFRAME
		function media_upload_pixabaytab_handler() { 
			wp_iframe('media_dbgt_pxbay_images_tab'); 
		}
		add_action('media_upload_pixabaytab', 'media_upload_pixabaytab_handler');
		

	} // Fin des Fonctions PREMIUM
	

	// Set DATA
	add_action( 'admin_init', 'puipui_dbgt_form_setupdata' );
		function puipui_dbgt_form_setupdata() {
			
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_apijeton', 	'sanitize_text_field' );
	
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_powered', 	'sanitize_text_field' 	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_library' , 	'sanitize_text_field' 	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_apikey', 		'sanitize_text_field'	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_imagesize' , 	'sanitize_text_field'	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_safesearch', 	'sanitize_text_field' 	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_legal', 		'sanitize_text_field' 	);
			register_setting( 'puipui_dbgt_form-settings-group', 'puipui_dbgt_form_option_cachingtime', 'sanitize_text_field' 	);
		}	
		
	// Creation du Menu
	add_action('admin_menu','puipui_dbgt_form_setupmenu');
	function puipui_dbgt_form_setupmenu(){
		  add_menu_page('Configuration de DBGT Smart Gallery', 'Smart Gallery', 'administrator', 'dbgt-smart-gallery', 'puipui_dbgt_form_init_cave', '   data:image/svg+xml;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAxgAAAMYBsHSbxQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAPXSURBVEiJtVRtSN1lFP+d5/m/eNe6Lh1XU5cupgtyti2npVCrtl6WFUFfGpFFtijGgiAGo7agqG+N9sGiuUKM3r40GI0gGApbs0gaMazlZs6rklPvvf97r9f7f3me0wfLl67JVdgPni+Hc36/83sO5xAz43rC+G8g3NhaHEjzITBKwBQFqSj55nCmr2yc+YheqQAtdFDY1FbkkvyTIzdrWJagZAKYTtvkezaIFKQxAWAEWl+BVkPMGAYhaggRFYEbTf7YObWsA0/jVb2pWvpPtoYXZfkeKJmQlHJKKZUopWSinpx4luJTPqUSQmXSBUqYMtT0ogfDHAcwTIHXPnP+xBeLBNg0tnF51Q05Pk0LXBwBF0cWRgv+eQAYlJkGJRMWpZwN5MQ3yJ/Pbreb99XPCYTrX1qPkKhh0wJNp/L94vnm7BC4tAIorQAA6PUlIetk116yGtvqRIE8DjdoWDHrQtiWCmp3iGDnHgIJiKEBmN90pg0K2++re6p3BM/eBUixeoFrKWkdPjXDA1UhVVMLSiVAQo4ZlHbvC1rqiMOh/IgCDRhLNLLRhnrkdkv89CtUTS0o6QBaDxlgFvl0LnoHUXjifDaIT0uvZYtwn7tb5iQZQpJSAABKxgIoNZjfnwQaa4+e8V7bs7vg+KFXzPDpfi0uji1bQvGYB+hoXgI0GofOeEbLvQ2o3VSJbZs3srg4unxNKgEwRXNOxVLgymLIsnXewaOfiFvLS4xzF3431P5nlhfIpGwQLeFAaYjeQZgf9ih59vLsUAEk336soKfSll2Tw/7Me08ILlm87OLKBMSZS+4seRpQWrIQix3QX0mY737r0tQ01NYy0/io25Vf95H/zuMWR26E29YsXWDRcMmZgfHpOU9+/5vJRWssXgdQMgEA7BbaI3MOZM8fsF/+THFRSLqHd9nBU1uE+8YDNiTDOvClT2OJHKfGyQtsPd8Z0OVx8g7uJNVcRQBAKQcspcOnj7kGAJjt3YHoHyP/6a1SNd4yT2Ib8PY3WWbXL7514KvAf/NRQ9dVQPRdhdne7SLjiWDvHaa6c/Y8iP5rs3WpBEByFPj3mk447B663+DI2txpCYLfut00vrukzLdOBcRMYEbwYI0V7K4mmLnrIGKTGloNzAn4LzSYS5IvQPDwZhnsqoYYdaDLC5feZgAUm4QYGvCh9QfzDvKFIaArb1o2hWITk8x8JNvb0Q0AApYRJye7Ip3/JR9Lphncle3taJ/rCeB28+Pe1/VtERNC0KrJY5msuBo3AXVsUZyZsaZpXxNr3QLCqu81E0Zs4HPnh45YjsD1xN/ffaijNlWRNQAAAABJRU5ErkJggg==' );
	}
	
	// Chargement JS CSS
	function puipui_dbgt_form_monjsdansladmin() {
	echo "<style>
		.gotham_puipui_dbgt_wrap #logo_admin {text-align:center;background:black;color:#ac5b5b;padding:80px;border-radius:8px;}
		.gotham_puipui_dbgt_wrap{margin: 10px 20px 0 2px;}
		.gotham_puipui_dbgt_form{float: left;width: 79%;}
		.gotham_puipui_dbgt_credit{float: left;width:17%;background:#fff;box-shadow: 0 0 0 1px rgba(0,0,0,0.05);padding:1%;margin-left:1%;}
		.gotham_puipui_dbgt_wrap #batbaseadmin tr td.libelle{font-weight:bold;width:470px;}
		.gotham_puipui_dbgt_wrap #batbaseadmin input, #batbaseadmin select, #batbaseadmin textarea {width:280px;float:left;}
		.gotham_puipui_dbgt_wrap .explain {background:white;box-shadow: 0 0 0 1px rgba(0,0,0,0.05);}
		.gotham_puipui_dbgt_wrap .explain p{padding: 10px;background: white;color: black;}
		.gotham_puipui_dbgt_wrap .explain ul{padding: 0 10px;list-style: square inside;}
		.gotham_puipui_dbgt_wrap .explain li{padding:0;}
		.gotham_puipui_dbgt_wrap .explain h3 {padding:6px 10px;border-bottom:1px solid #eee;}
		.gotham_puipui_dbgt_wrap .explain p.shortcode {text-align: center;background: #181818;color: #56ff56;font-style: italic;font-size: 20px;}
		.dbgt-bustaflex-pix { overflow: hidden; }
        .dbgt-bustaflex-pix .item { float: left; margin: 4px; background: #f3f3f3; box-sizing: content-box; overflow: hidden; position: relative; }
        .dbgt-bustaflex-pix .item > img { display: block; width: auto; height: 100%; }
        .dbgt-bustaflex-pix .download {opacity:0;transition:opacity0.3s;position:absolute;top:0;right:0;bottom:0;left:0;cursor:pointer;background:rgba(0,0,0,.65);color:#eee;text-align:center;font-size:14px;line-height:1.5;}
        .dbgt-bustaflex-pix .item:hover .download, .dbgt-bustaflex-pix .item.uploading .download { opacity: 1; }
        .dbgt-bustaflex-pix .download img { position: absolute; top: 0; left: 0; right: 0; bottom: 0; margin: auto; height: 32px; opacity: .7; }
        .dbgt-bustaflex-pix .download a { color: #eee; }
		.dbgt-bustaflex-pix .download button {width: 100%;height: 145px;position: absolute;left: 0;right: 0;cursor: pointer;background: black;}
		.dbgt-bustaflex-pix .download button img{width:120px;height:120px;}
		.dbgt-bustaflex-pix .download .dbgtinfo { position: absolute; left: 0; right: 0; bottom: 15px; padding: 0 5px; }
	</style>
	<script>
    window.onload = function () {
    document.getElementById('puipui_dbgt_form_option_library').addEventListener('change', function () {
        if (this.value == 'pixabay') {
            document.getElementById('sizouu1').style.display = 'table-row';
			document.getElementById('sizouu2').style.display = 'table-row';
			document.getElementById('sizouu3').style.display = 'table-row';
			document.getElementById('sizouu4').style.display = 'table-row';
			document.getElementById('sizouu5').style.display = 'table-row';
        } else {
            document.getElementById('sizouu1').style.display = 'none';
			document.getElementById('sizouu2').style.display = 'none';
			document.getElementById('sizouu3').style.display = 'none';
			document.getElementById('sizouu4').style.display = 'none';
			document.getElementById('sizouu5').style.display = 'none';
        }
    }, false)
	};
	</script>
	";
	}
	add_action('admin_enqueue_scripts', 'puipui_dbgt_form_monjsdansladmin');
	
	
	// Ajout du  Bouton de ShortCode dans Tiny MCE
	function puipui_dbgt_tinymce_button() {
		if ( ! current_user_can('edit_posts') 
		  && ! current_user_can('edit_pages') ) {
			return false;
		}
		if ( get_user_option('rich_editing') == 'true') {
			add_filter( 'mce_external_plugins', 'puipui_dbgt_script_tiny' );
			add_filter( 'mce_buttons', 'puipui_dbgt_register_button' );
		}
	}
	function puipui_dbgt_register_button( $buttons ) {
		array_push( $buttons, '|', 'puipui_dbgt_bouton' );
		return $buttons;
	}
	add_action( 'admin_init', 'puipui_dbgt_tinymce_button' );
	function puipui_dbgt_script_tiny( $plugin_array ) {
		global $smartgallery_dbgt_library; 
		if ($smartgallery_dbgt_library == "pixabay") {
			$plugin_array['ekoflickrdbgt'] = plugins_url( '/tinymscript.js', __FILE__ );
		} else {
			$plugin_array['ekoflickrdbgt'] = plugins_url( '/tinymscript-4flick.js', __FILE__ );
		}
		return $plugin_array;
	}
	// ! Fin Ajout du  Bouton de ShortCode dans Tiny MCE
	
	
	// Init Page Paramètres
	function puipui_dbgt_form_init_cave(){
	if ( 'fr_' === substr( get_user_locale(), 0, 3 ) ) {
		$txt_adwin_welcome = "Paramétrage du Formulaire de Contact";
		$txt_adwin_yes = "Oui";
		$txt_adwin_no = "Non";
		$txt_shortcodeainserer = "➡️ 2. Pour afficher une gallerie d'image, insérez tout simplement le shortcode suivant à l'endroit où vous le désirez : <strong>[smartgallery_dbgt keyword='' number='']</strong>.<br /> Saisissez dans le paramètre %<strong>keyword</strong>% la thématique des images que vous souhaitez afficher et dans le paramètre %<strong>number</strong>% le nombre d'images désiré.";
		$txt_shortcodeainserer2 = "<p>➡️ 1. Choisissez la librairie que vous souhaitez utiliser :</p><ul><li><span style='color:green;'>Flickr ne demande aucune clé d'API dans le cadre d'un usage personnel (<a href='https://www.flickr.com/help/terms/api' target='_blank' rel='nofollow noopener noreferrer'>CGU</a>)</span>, <span style='color:red;'>mais les images sont petites, hotlinkées et ne sont pas formidables</span>.</li><li><span style='color:red;'>Pixabay demande une clé API (<strong>100% gratuite</strong>) à <a href='https://pixabay.com/api/docs/' target='_blank' rel='nofollow noopener noreferrer'>récupérer ici</a> ainsi qu'un lien retour en bas de la gallerie.</span><br /><span style='color:green;'>Les images sont superbes, vous pourrez choisir leurs dimensions, tapez votre recherche en français et obtenir des résultats très pertinents dans 99.9% des cas.<br /><strong>+++ Les images sont mises en cache sur votre serveur !</strong></span></li></ul><p><strong>Faites un essai avec les 2 bibliothèques et choisissez la plus adaptée à votre projet.</strong> => <strong><u>Keyword en français / anglais.</u></strong></p>";
		$txt_adwin_helpkapsule = "Je soutiens l'éditeur de ce plugin car je suis quelqu'un de bien :";
		$txt_adwin_helpkapsule_p = "👍 En sélectionnant OUI, un lien hypertexte discret vers notre site web sera inséré en bas de page.<br />💡 Vous pouvez bien sûr également faire un lien vers notre site https://tonsite.fr/ depuis votre page partenaire par exemple.";
		$txt_adwin_helpkapsule_label = "J'accepte le deal";
		$txt_adwin_blokright_title = "Besoin d’aide ?";
		$txt_adwin_blokright_corpus_1 = "Si vous rencontrez un problème avec cette extension, vous trouverez probablement des réponses dans ces deux pages :";
		$txt_adwin_blokright_corpus_2 = "Documentation";
		$txt_adwin_blokright_corpus_3 = "Forum de Support";
		$txt_adwin_blokright_aime = "Vous aimez cette extension ?";
		$txt_adwin_blokright_vote = "Notez nous 5/5";
		$txt_adwin_blokright_sur = "sur";
		$txt_adwin_blokright_week = "semaine";
		$txt_adwin_blokright_month = "mois";
		$txt_adwin_blokright_library = "Librairie";
		$txt_adwin_blokright_avec = "Avec";
		$txt_adwin_blokright_sans = "Sans";
		$txt_adwin_blokright_imagesizefault = "Taille des images par défaut";
		$txt_adwin_blokright_choose_flickr = "Flickr (Usage non commercial UNIQUEMENT)";
		$txt_adwin_blokright_choose_pixabay = "Pixabay (Clé API nécessaire MAIS 100% gratuite)";
		$txt_adwin_blokright_getyourkey = "Demandez votre clé 100% gratuite";
		$txt_adwin_blokright_legal = "Afficher par défaut les mentions légales de la banque d'image";
		$txt_adwin_blokright_cachingtime = "Durée de mise en cache";
	}
	
	///ANGLAIS
	else {
		$txt_adwin_welcome = "Configuration of the Contact Form";
		$txt_adwin_yes = "Yes";
		$txt_adwin_no = "No";
		$txt_shortcodeainserer = "➡️ To display an smart image gallery, simply insert the following shortcode where you want it: <strong> [smartgallery_dbgt] </strong>.<br />⚠️ Enter in the <strong>%keyword%</strong> parameter the theme of the images do you want to display and in the <strong>%number%</strong> parameter the desired number of images.";
		$txt_shortcodeainserer2 = "<p>➡️ 1. Choose the library you want to use :</p><ul><li><span style='color:green;'>Flickr does not ask for any API key for non commercial use (<a href='https://www.flickr.com/help/terms/api' target='_blank' rel='nofollow noopener noreferrer'>CGU</a>)</span>, <span style='color:red;'>but the images are small, not self-hosted (hotlinking) and not awesome</span>.</li><li><span style='color:red;'>Pixabay requests an API key (<strong>100% FREE</strong>) at <a href='https://pixabay.com/api/docs/' target='_blank' rel='nofollow noopener noreferrer'>get here</a> as well as a backlink at the bottom from the gallery.</span><br /><span style='color:green;'>Images are superb, you can choose their dimensions and obtain very relevant results in 99.9% of cases.<br /><strong>+++ Pics are self-hosted !!!</strong> </span></li></ul><p><strong>Try out the 2 libraries and choose the best one for your project.</strong></p>";
		$txt_adwin_helpkapsule = "I support the editor of this plugin for my karma :";
		$txt_adwin_helpkapsule_p = "👍 By selecting YES, a discreet hypertext link to our website will be inserted at the bottom of your page. <br /> 💡 If you prefer, you can also make a backlink to our site https://tonsite.fr/ from your partner page for example.";
		$txt_adwin_helpkapsule_label = "I accept the deal";
		$txt_adwin_blokright_title = "Need help ?";
		$txt_adwin_blokright_corpus_1 = "If you have a problem with this plugin, you will probably find answers on these two pages:";
		$txt_adwin_blokright_corpus_2 = "Documentation";
		$txt_adwin_blokright_corpus_3 = "Support (Board)";
		$txt_adwin_blokright_aime = "Do you like this extension ?";
		$txt_adwin_blokright_vote = "Rate us 5/5";
		$txt_adwin_blokright_sur = "on";
		$txt_adwin_blokright_week = "week";
		$txt_adwin_blokright_month = "month";
		$txt_adwin_blokright_library = "Library";
		$txt_adwin_blokright_avec = "With";
		$txt_adwin_blokright_sans = "With no";
		$txt_adwin_blokright_imagesizefault = "Images Size (Default)";
		$txt_adwin_blokright_choose_flickr = "Flickr (Non Commercial Usage ONLY)";
		$txt_adwin_blokright_choose_pixabay = "Pixabay (API Key is needed BUT it's 100% Free)";
		$txt_adwin_blokright_getyourkey = "Get your key ~ 100% Free";
		$txt_adwin_blokright_legal = "Display by default the legal notices of the image bank";
		$txt_adwin_blokright_cachingtime = "Caching time";
	}
	?>
	
<div class="gotham_puipui_dbgt_wrap">
 <h1 id="logo_admin">✨ SMART GALLERY DBGT ✨</h1>
  <div class="gotham_puipui_dbgt_form">
  <form method="post" action="options.php">
  <?php settings_fields( 'puipui_dbgt_form-settings-group' ); ?>
  <?php do_settings_sections('puipui_dbgt_form-settings-group'); ?>
	<table id="batbaseadmin">
	
		<tr class="explain">
			<td colspan="2">
				<h3>⚙️ Settings</h3>
				<?php echo $txt_shortcodeainserer2; ?>
			</td>
		</tr>
			
		<tr class="explain">
			<td colspan="2">
			<h3>👀 Shortcode 👀</h3>
			<p><?php echo $txt_shortcodeainserer; ?></p>
			<p class="shortcode">[smartgallery_dbgt keyword='<span style='color:white;'>% YOUR QUERY %</span>' number='<span style='color:white;'>% ITEMS NUMBER %</span>']</p>
			</td>
		</tr>
			
		<tr style="height:40px;">
			<td colspan="2"></td>
		</tr>
		  
		<tr>
			<td class="libelle">
				<label for="puipui_dbgt_form_option_library">1. 🤳 <?php echo $txt_adwin_blokright_library; ?></label>
			</td>
			<td>
				<?php $puipui_dbgt_form_option_library = get_option('puipui_dbgt_form_option_library'); ?>
				<select id="puipui_dbgt_form_option_library" name="puipui_dbgt_form_option_library" value="<?php echo get_option('puipui_dbgt_form_option_library'); ?>">
					<option value="flickr" <?php selected( $puipui_dbgt_form_option_library, 'flickr' ); ?>><?php echo $txt_adwin_blokright_choose_flickr; ?></option>
					<option value="pixabay" <?php selected( $puipui_dbgt_form_option_library, 'pixabay' ); ?>><?php echo $txt_adwin_blokright_choose_pixabay; ?></option>
				</select>
			</td>
		</tr>
		  
		<tr <?php if (($puipui_dbgt_form_option_library == "flickr") OR ($puipui_dbgt_form_option_library == "")) {?>style="display:none"<?php } ?> id="sizouu1">
			<td class="libelle">
				<label for="puipui_dbgt_form_option_apikey">2. 🔑 API Key ( <a href="https://pixabay.com/api/docs/"><?php echo $txt_adwin_blokright_getyourkey; ?></a> )</label>
			</td>
			<td>
				<input type="text" id="puipui_dbgt_form_option_apikey" name="puipui_dbgt_form_option_apikey" value="<?php echo get_option('puipui_dbgt_form_option_apikey'); ?>" />
			</td>
		</tr>
		  
		<?php $puipui_dbgt_form_option_imagesize = get_option('puipui_dbgt_form_option_imagesize'); ?>
		<tr <?php if (($puipui_dbgt_form_option_library == "flickr") OR ($puipui_dbgt_form_option_library == "")) {?>style="display:none"<?php } ?> id="sizouu2">
			<td class="libelle">
				<label for="puipui_dbgt_form_option_imagesize">3. 👨‍💻 <?php echo $txt_adwin_blokright_imagesizefault; ?></label>
			</td>
			<td>
				<select id="puipui_dbgt_form_option_imagesize" name="puipui_dbgt_form_option_imagesize" value="<?php echo $puipui_dbgt_form_option_imagesize; ?>">
					<option value="webformatURL" <?php selected( $puipui_dbgt_form_option_imagesize, 'webformatURL' ); ?>>Medium (640*)</option>
					<option value="largeImageURL" <?php selected( $puipui_dbgt_form_option_imagesize, 'largeImageURL' ); ?>>Large (1280*)</option>
					<option value="previewURL" <?php selected( $puipui_dbgt_form_option_imagesize, 'previewURL' ); ?>>Thumb (150*)</option>	
				</select>
			</td>
		</tr>
		
		<?php $puipui_dbgt_form_option_safesearch = get_option('puipui_dbgt_form_option_safesearch'); ?>
		<tr <?php if (($puipui_dbgt_form_option_library == "flickr") OR ($puipui_dbgt_form_option_library == "")) {?>style="display:none"<?php } ?> id="sizouu3">
			<td class="libelle">
				<label for="puipui_dbgt_form_option_safesearch">4. 🔞 Safe Search</label>
			</td>
			<td>
				<select id="puipui_dbgt_form_option_safesearch" name="puipui_dbgt_form_option_safesearch" value="<?php echo $puipui_dbgt_form_option_safesearch; ?>">
					<option value="yes" <?php selected( $puipui_dbgt_form_option_safesearch, 'yes' ); ?>>✅ <?php echo $txt_adwin_yes; ?></option>
					<option value="no" <?php selected( $puipui_dbgt_form_option_safesearch, 'no' ); ?>> ❌ <?php echo $txt_adwin_no; ?></option>
				</select>
			</td>
		</tr>
		
		  
		<tr <?php if (($puipui_dbgt_form_option_library == "flickr") OR ($puipui_dbgt_form_option_library == "")) {?>style="display:none"<?php } ?> id="sizouu4">
			<td class="libelle">
				<label for="puipui_dbgt_form_option_cachingtime">⌛ <?php echo $txt_adwin_blokright_cachingtime; ?></label>
			</td>
			<td>
				<?php $puipui_dbgt_form_option_cachingtime = get_option('puipui_dbgt_form_option_cachingtime'); ?>
				<select id="puipui_dbgt_form_option_cachingtime" name="puipui_dbgt_form_option_cachingtime" value="<?php echo get_option('puipui_dbgt_form_option_cachingtime'); ?>">
					<option value="86400" <?php selected( $puipui_dbgt_form_option_cachingtime, '86400' ); ?>>24H</option>
					<option value="604800" <?php selected( $puipui_dbgt_form_option_cachingtime, '604800' ); ?>>1 <?php echo $txt_adwin_blokright_week; ?></option>
					<option value="2592000" <?php selected( $puipui_dbgt_form_option_cachingtime, '2592000' ); ?>>1 <?php echo $txt_adwin_blokright_month; ?></option>
					<option value="lifetime" <?php selected( $puipui_dbgt_form_option_cachingtime, 'lifetime' ); ?>>No Limit</option>
				</select>
			</td>
		</tr>
		
		<tr>
			<td class="libelle">
				<label for="puipui_dbgt_form_option_legal">⚖️ <?php echo $txt_adwin_blokright_legal; ?></label>
			</td>
			<td>
				<?php $puipui_dbgt_form_option_legal = get_option('puipui_dbgt_form_option_legal'); ?>
				<select id="puipui_dbgt_form_option_legal" name="puipui_dbgt_form_option_legal" value="<?php echo get_option('puipui_dbgt_form_option_legal'); ?>">
					<option value="yes" <?php selected( $puipui_dbgt_form_option_legal, 'yes' ); ?>>✅ <?php echo $txt_adwin_yes; ?> :: <?php echo $txt_adwin_blokright_avec; ?> Backlink</option>
					<option value="yesbis" <?php selected( $puipui_dbgt_form_option_legal, 'yesbis' ); ?>>✔️ <?php echo $txt_adwin_yes; ?> :: <?php echo $txt_adwin_blokright_sans; ?> Backlink</option>
					<option value="no" <?php selected( $puipui_dbgt_form_option_legal, 'no' ); ?>> ❌ <?php echo $txt_adwin_no; ?></option>
				</select>
			</td>
		</tr>
		  
		<tr <?php if (($puipui_dbgt_form_option_library == "flickr") OR ($puipui_dbgt_form_option_library == "")) {?>style="display:none"<?php } ?> id="sizouu5">
			  <td class="libelle">
				<label for="puipui_dbgt_form_option_apijeton">
					⭐ 
					PREMIUM 
						<?php
						
						$check_if_exist_licence_key_dbgt = get_option('puipui_dbgt_form_option_apijeton'); 
						if ($check_if_exist_licence_key_dbgt == NULL) {
							
							$licence_key_exist_dbgt = "non";
							
						} else {
							
							$licence_key_exist_dbgt = "oui";
							
						}

						if ($licence_key_exist_dbgt == "oui") { // Si le champ API Key est rempli
						
							if (BABYVEGETA != false) { // Si la connexion se fait
									
								echo "<span style='color:green;'>OK</span> ("; echo BABYVEGETA.")";
										
							} else { // Sinon, si pas de connexion
									
								echo "<br /><span style='color:red;'>⚠️ Clé Invalide</span>";
										
							} 
									
						} else {
							
							echo "<br /><span style='color:red;'>⚠️ Clé Inconnue</span>";
						}
						?>
					
				</label>
			</td>
			<td>
				<input type="text" id="puipui_dbgt_form_option_apijeton" name="puipui_dbgt_form_option_apijeton" value="<?php echo get_option('puipui_dbgt_form_option_apijeton'); ?>" />
			</td>
		</tr>
		  
		<tr style="height:40px;">
			<td colspan="2"></td>
		</tr>
		  
		<tr class="explain">
			<td colspan="2">
				<h3>🚀 <?php echo $txt_adwin_helpkapsule; ?></h3>
				<p><?php echo $txt_adwin_helpkapsule_p; ?></p>
			</td>
		</tr>
		
		<tr style="height:50px;">
			<td class="libelle">
				<label for="puipui_dbgt_form_option_powered"><?php echo $txt_adwin_helpkapsule_label; ?> :</label>
			</td>
			<td>
				<?php $puipui_dbgt_form_option_powered = get_option('puipui_dbgt_form_option_powered'); ?>
				<select id="puipui_dbgt_form_option_powered" name="puipui_dbgt_form_option_powered" value="<?php echo get_option('puipui_dbgt_form_option_powered'); ?>">
					<option value="non" <?php selected( $puipui_dbgt_form_option_powered, 'non' ); ?>><?php echo $txt_adwin_no; ?></option>
					<option value="oui" <?php selected( $puipui_dbgt_form_option_powered, 'oui' ); ?>><?php echo $txt_adwin_yes; ?></option>
				</select>
			</td>
		</tr>
	</table>
	
  <?php submit_button(); ?>
  
  </form>
  </div>
  
   <div class="gotham_puipui_dbgt_credit">
					<h3>👻 SMART GALLERY DBGT</h3>
					<div class="inside">
						<h4 class="inner"><?php echo $txt_adwin_blokright_title; ?></h4>
						<p class="inner"><?php echo $txt_adwin_blokright_corpus_1; ?></p>
						<ul>
							<li>- <a href="https://wordpress.org/plugins/smart-gallery-dbgt/"><?php echo $txt_adwin_blokright_corpus_2; ?></a></li>
							<li>- <a href="https://wordpress.org/support/plugin/smart-gallery-dbgt/"><?php echo $txt_adwin_blokright_corpus_3; ?></a></li>
						</ul>
						<hr>
						<h4 class="inner">🏆 <?php echo $txt_adwin_blokright_aime; ?></h4>
						<p class="inner">⭐ <a href="https://wordpress.org/support/plugin/smart-gallery-dbgt/reviews/?filter=5#new-post" target="_blank"><?php echo $txt_adwin_blokright_vote; ?></a> <?php echo $txt_adwin_blokright_sur; ?> WordPress.org</p>
						<hr>
						<p class="inner">© Copyright <a href="https://www.kapsulecorp.com/">Kapsule Corp</a></p>
					</div>
	</div>
	
</div>

<?php } 

}

//////////////////////////////////
//////////////////////////////////
//////////////////////////////////
////// ON ZE FRONT 
//////////////////////////////////
//////////////////////////////////
//////////////////////////////////

// Function POWERED
	$puipui_dbgt_form_option_powered_check = get_option('puipui_dbgt_form_option_powered');
	if ($puipui_dbgt_form_option_powered_check == "oui") {
			function puipui_dbgt_form_powa() {
				echo "<p style='text-align:center;'>Plugin by <a href='https://tonsite.fr/' target='_blank' rel='noopener'>Ton Site.FR</a></p>";
				}
				add_action( 'wp_footer', 'puipui_dbgt_form_powa' );
	}
	
	
// Function to get extension	
	function puipui_dbgt_get_extension($file) {
		$tmp = explode(".", $file);
		$extension = end($tmp);
		return $extension ? $extension : false;
	}
	
	
// Chargement des bibliothèques JS / CSS selon l'API
function smartgallery_dbgt_load_script_css_front() {
	
	global $smartgallery_dbgt_library;
	wp_register_style( 'smartgallery-dbgt-css', false ); // On enregistre le style
	
if ($smartgallery_dbgt_library == "flickr") { // On check si librairie Flickr

		// Function pour vérifier que JQuery est bien chargé 
				if ( ! wp_script_is( 'jquery', 'enqueued' )) {
					//Enqueue
					wp_enqueue_script( 'jquery' );
				}	
		// ! Fin de la function pour vérifier que JQuery est bien chargé 
		
		wp_add_inline_style( 'smartgallery-dbgt-css', '.smartgallery-dbgt img {height:100px;float:left;}.smartgallery-dbgt.smartgallery-dbgt.dbgt-sidebar img{max-width:100%;height:auto;max-height:200px;text-align:center;}.dbgtgallerycopy{font-size:10px;font-style:italic;opacity:0.8;margin-top:15px;display:block;clear:both;}' );
		
	} else {
		
		wp_add_inline_style( 'smartgallery-dbgt-css', '.dbgtgallerycopy{font-size:10px;font-style:italic;text-align:right;opacity:0.8;margin-top:0;display:block;clear:both;}img.dbgtpremiumpics{width:100%;height:auto;}' );
		
	}

}
add_action( 'wp_enqueue_scripts', 'smartgallery_dbgt_load_script_css_front' );


// Function pour afficher la gallerie
function puipui_dbgt_gallery($atts, $content = null, $tag = '') {
	
	// Global Data
	global $smartgallery_dbgt_library;
	global $safe_search_parameter;
	
	$smartgallery_dbgt_apikey = get_option('puipui_dbgt_form_option_apikey');
	if (empty($smartgallery_dbgt_apikey)) {$smartgallery_dbgt_library = "flickr";}
	
	$smartgallery_dbgt_imagesize = get_option('puipui_dbgt_form_option_imagesize');
	if (empty($smartgallery_dbgt_imagesize)) {$smartgallery_dbgt_imagesize = "previewURL";}
	
	$smartgallery_dbgt_legal = get_option('puipui_dbgt_form_option_legal');
	if (empty($smartgallery_dbgt_legal)) {$smartgallery_dbgt_legal = "yesbis";}
	
	// Widget Data
	$a= shortcode_atts( array(
		'keyword' => 'taxi', // Keyword Query
		'number' => '3', // Nombre d'image
		'sidebar' => '', // Sidebar ou Main Content
		'legal' => $smartgallery_dbgt_legal, // Mentions Légales,
		'imagesize' => $smartgallery_dbgt_imagesize, // Taille des images
	), $atts );
	
	// Query
	$gallery_keyword = esc_attr( $a['keyword'] );
	if (empty($gallery_keyword)) { $gallery_keyword = "taxi";}
	$search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
	$replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
	$gallery_keyword = str_replace($search, $replace, $gallery_keyword);
	
	// Item Number
	$item_number = esc_attr( $a['number'] );
	if (empty($item_number)) {
		$item_number_api = 3;
	} else {
		$item_number_api = $item_number;
	}

	// Sidebar or Content
	$sidebar_or_not = esc_attr( $a['sidebar'] );
	if (empty($sidebar_or_not)) {
		$sidebar_or_not = "dbgt-primary";
		$feed_all = false;
	} else {
		$sidebar_or_not = "dbgt-sidebar";
		$feed_all = true;
	}
	
	// Legal
	$widget_dbgt_legal = esc_attr( $a['legal'] ); // si le shortcode le veut, il peut écraser l'affichage ou non du texte légal
	if (empty($widget_dbgt_legal)) 					{$finalylegal = $smartgallery_dbgt_legal;} 
	elseif ($widget_dbgt_legal == "default")		{$finalylegal = $smartgallery_dbgt_legal;} 
	else 											{$finalylegal = $widget_dbgt_legal;}
	
	// Image Size
	$widget_dbgt_imagesize = esc_attr( $a['imagesize'] ); // si le shortcode le veut, il peut écraser la taille d'image par défaut
	if ($widget_dbgt_imagesize == "large") 			{$final_dbgt_imagesize = "largeImageURL";} 
	elseif ($widget_dbgt_imagesize == "medium")	 	{$final_dbgt_imagesize = "webformatURL";} 
	elseif ($widget_dbgt_imagesize == "thumb") 		{$final_dbgt_imagesize = "previewURL";} 
	elseif ($widget_dbgt_imagesize == "default") 	{$final_dbgt_imagesize = $smartgallery_dbgt_imagesize;} 
	else											{$final_dbgt_imagesize = "webformatURL";}

	// Instantiation de la variable retour
	$id_dbgt = "-id".rand();
	$resultatdescourses = "";
	
	// On charge le CSS	
	wp_enqueue_style( 'smartgallery-dbgt-css' );
	
	//////////////////////////////////////////
	///////////// VERSION FLICKRRRRRRRRRRR
	//////////////////////////////////////////
	
	if ($smartgallery_dbgt_library == "flickr") {

		$item_number_api = $item_number_api-1; // Hack pour bien limiter le nombre d'image sur le script Flickr
		$resultatdescourses .= "<div id='smartgallery-dbgt-images$id_dbgt' class='smartgallery-dbgt $sidebar_or_not'></div>";
		
		if ( 'fr_' === substr( get_user_locale(), 0, 3 ) ) {$langage = "&lang=fr-fr";} else {$langage = "&lang=en-us";} 
		
		$sanitize_my_query = urlencode($gallery_keyword);
		
		$resultatdescourses .= '<script>(function() { 
		var flickerAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";
		  jQuery.getJSON( flickerAPI, {
			tags: "'.$sanitize_my_query.'; ?>",
			tagmode: "all",
			format: "json",
			lang: "'.$langage.'",
		  })
			.done(function( data ) {
				jQuery.each( data.items, function( i, item ) {
				jQuery( "<img>" ).attr( "src", item.media.m ).appendTo( "#smartgallery-dbgt-images'.$id_dbgt.'" );
				if ( i === '.$item_number_api.' ) {
				  return false;
				}
			  });
			});
		})();
		</script>';
		
		if ($finalylegal != "no") {
			
			$resultatdescourses .= '<span class="dbgtgallerycopy">';
			
			if ($finalylegal == "yes") {
				
				$resultatdescourses .= '<a href="https://www.flickr.com/services/api/" rel="nofollow nooperner" target="_blank">';
			}
			
			$resultatdescourses .= '« &copy; Ce produit utilise l’APIFlickr mais n’est ni approuvé ni certifié par SmugMug, Inc. »';
			
				
			if ($finalylegal == "yes") {
				
				$resultatdescourses .= '</a>';
			}
			
			$resultatdescourses .= '</span>';
				
		}
		
		
	} else { 
	
		/////////////////////////////
		///////////// VERSION Pixabay
		/////////////////////////////
		
		if ( is_multisite() ) {
			
				$multisite = true; 
				$iddusite = get_current_blog_id();
				$store_multi_id = "$iddusite-";
				
		} else {
			
				$multisite = false; 
				$iddusite = false;
				$store_multi_id = "";
		}
		
		// Durée du Cache
		$cachingtime = get_option('dbgt_puipui_form_option_cachingtime'); 
		
		// Création du Chemin Cache
		$domainname = $_SERVER['HTTP_HOST'];
		$id_page_appelante = get_the_ID();
		if ($feed_all == true) {$id_page_appelante = "sidebar";} // Si c'est en sidebar on créé une variable sidebar pour ne pas stocker sur chaque page
		$upload_dir_url = wp_get_upload_dir();
		$kapsule_dirstokage_p = $upload_dir_url["basedir"];
		$kapsule_dirstokage_pics = $kapsule_dirstokage_p . '/dbgtgallery_pics';
		$kapsule_dirstokage_picsfeed = $kapsule_dirstokage_p . '/dbgtgallery_feed/';
		$ioyasin = $gallery_keyword; 
		$ioyasin = filter_var($ioyasin, FILTER_SANITIZE_URL);
		$ioyasin = preg_replace('/[^A-Za-z0-9]/','', $ioyasin);
		$ioyasin = str_replace(' ', '_', $ioyasin);
		$ioyasin = str_replace(';', '', $ioyasin);
		$ioyasin = strtolower($ioyasin);
		$dynamixcache = "$kapsule_dirstokage_picsfeed$domainname-$id_page_appelante-$ioyasin-$final_dbgt_imagesize.json"; // On créé le chemin du fichier de cache
		
		// Création du Chemin HTTP Upload Images
		$upload_dir_url_chemin_http = $upload_dir_url["baseurl"];
		$upload_dir_url_chemin_http_full = $upload_dir_url_chemin_http. '/dbgtgallery_pics/';
		
		///////////////////////////
		
		if (file_exists($dynamixcache) AND ( 10 > filesize($dynamixcache) )) {  // Si le fichier existe mais qu'il est vide. 
		
			unlink ($dynamixcache); // On l'efface
			
		}
		
		if ((!empty($cachingtime)) AND ($cachingtime != "lifetime")) {
			
			if ( time() - $cachingtime > filemtime($dynamixcache))  {
				unlink ($dynamixcache); // On l'efface
			}
			
		}
		
		if (file_exists($dynamixcache)) { // Si le fichier de cache existe deja
		
			$response = @file_get_contents($dynamixcache); // On charge le fichier de cache depuis mon serveur
			$data = json_decode($response,true);
			$items = $data;
			$compteur = 0;
			foreach ($items as $item){
				$image_url = $item["image_url"];
				$image_alt = stripslashes($item["tags"]);
				$image_id = $item["idp"];
				$fullimageurl = "$upload_dir_url_chemin_http_full$image_url";
				@$getsize = getimagesize($fullimageurl); // On choppe les dimensions
				$getsize = isset($getsize[3]) ? $getsize[3] : NULL; // On choppe les dimensions
				$resultatdescourses .= '<img id="dbgtid-'.$image_id.'" src="'.$fullimageurl.'" alt="'.$image_alt.'" '.$getsize.'>';
				$compteur++;
				if ($compteur == $item_number_api) {
					break;
				}
			}
			
		} else { // Sinon on appelle l'API et on créé le cache	
		
			if ( 'fr_' === substr( get_user_locale(), 0, 3 ) ) {$langage = "&lang=fr";} else {$langage = "&lang=en";} // Set Langage Query
			
			////////////////////////////////////////////
			/////// 3rd party vendor libraries ////////			
			
			$sanitize_my_query = urlencode($gallery_keyword);
			$build_my_query = "https://pixabay.com/api/?key=$smartgallery_dbgt_apikey&q=$sanitize_my_query$langage&orientation=horizontal$safe_search_parameter";
			$response = wp_remote_get($build_my_query);
			$body     = wp_remote_retrieve_body( $response );
			
			///////////////////////////////////////////
			///////////////////////////////////////////

			$data = json_decode($body,true);
			$items = $data['hits'];
			
			// Construction des variables du cache
			if (! is_dir($kapsule_dirstokage_pics)) {
				mkdir( $kapsule_dirstokage_pics, 0755 );
			}
			
			if (! is_dir($kapsule_dirstokage_picsfeed)) {
				mkdir( $kapsule_dirstokage_picsfeed, 0755 );
			}
			
			$compteur = 0;
			$dbgt_json_creator = array();
			foreach ($items as $item){
				$imagee = $item["$final_dbgt_imagesize"];
				$tags = $item['tags'];
				$id_pix_pixa = $item['id'];
				$extension = puipui_dbgt_get_extension($imagee);
				$resultatdescourses .= '<img id="dbgtid-'.$id_pix_pixa.'" src="'.$imagee.'" alt="'.$tags.'">';
				$sanitizimage_name = preg_replace('/[^a-zA-Z0-9\-\._]/','-', strtolower($sanitize_my_query));
				copy($imagee, "$kapsule_dirstokage_pics/$store_multi_id$id_page_appelante-$sanitizimage_name-$id_pix_pixa-$final_dbgt_imagesize.$extension");
				$minitab = array(
					"item" => $compteur,
					"idp" => $id_pix_pixa,
					"tags" => addslashes($tags),
					"image_url" => "$store_multi_id$id_page_appelante-$sanitizimage_name-$id_pix_pixa-$final_dbgt_imagesize.$extension"
				);
				$dbgt_json_creator[] = $minitab;
				$compteur++;
				if ($compteur == $item_number_api) {
					break;
				}
			}
			
			$dbgt_json_creator = json_encode($dbgt_json_creator); // on encode en JSON le tableau
			file_put_contents($dynamixcache , $dbgt_json_creator); // On crée le cache
		}
		
		if ($finalylegal != "no") {
			
			$resultatdescourses .= '<span class="dbgtgallerycopy">';
			
			if ($finalylegal == "yes") {
				
				$resultatdescourses .= '<a href="https://pixabay.com/" rel="nofollow nooperner" target="_blank">';
			}
			
			$resultatdescourses .= '&copy; Crédit Photo : https://pixabay.com/';
			
				
			if ($finalylegal == "yes") {
				
				$resultatdescourses .= '</a>';
			}
			
			$resultatdescourses .= '</span>';
				
		}
		
	}
	
	return $resultatdescourses;
}

add_shortcode( 'smartgallery_dbgt', 'puipui_dbgt_gallery' );



// Function pour afficher la gallerie PREMIUM
function majin_dbgt_gallery_premium($atts, $content = null, $tag = '') {

	global $smartgallery_dbgt_apikey;
	
	$smartgallery_dbgt_imagesize = get_option('puipui_dbgt_form_option_imagesize');
	if (empty($smartgallery_dbgt_imagesize)) {$smartgallery_dbgt_imagesize = "webformatURL";}
	
	$smartgallery_dbgt_legal = get_option('puipui_dbgt_form_option_legal');
	if (empty($smartgallery_dbgt_legal)) {$smartgallery_dbgt_legal = "yesbis";}
	
	$a= shortcode_atts( array(
		'id' => 'id',
		'alt' => 'alt',
		'url' => 'url'
	), $atts );
	
	$id_du_media_pixabay = esc_attr( $a['id'] );
	if (empty($id_du_media_pixabay)) { $id_du_media_pixabay = 3429797;}
	
	$alt = esc_attr( $a['alt'] );
	if (empty($alt)) { $alt = NULL;}
	
	$href = esc_attr( $a['url'] );
	if (empty($href)) { $href = NULL;}
	
	$resultatdescourses = "";
	
	// On charge le CSS	
	wp_enqueue_style( 'smartgallery-dbgt-css' );

	if ( is_multisite() ) {
		
			$multisite = true; 
			$iddusite = get_current_blog_id();
			$store_multi_id = "$iddusite-";
			
	} else {
		
			$multisite = false; 
			$iddusite = false;
			$store_multi_id = "";
	}
	
	// Création du Chemin Cache
	$domainname = $_SERVER['HTTP_HOST'];
	$id_page_appelante = get_the_ID();
	
	$upload_dir_url = wp_get_upload_dir();
	$kapsule_dirstokage_p = $upload_dir_url["basedir"];
	$kapsule_dirstokage_pics = $kapsule_dirstokage_p . '/dbgtgallery_pics';
	$kapsule_dirstokage_picsfeed = $kapsule_dirstokage_p . '/dbgtgallery_feed/';
	$ioyasin = $id_du_media_pixabay; 
	
	$dynamixcache = "$kapsule_dirstokage_picsfeed$domainname-$id_page_appelante-$ioyasin-$smartgallery_dbgt_imagesize.json"; // On créé le chemin du fichier de cache
	
	// Création du Chemin HTTP Upload Images
	$upload_dir_url_chemin_http = $upload_dir_url["baseurl"];
	$upload_dir_url_chemin_http_full = $upload_dir_url_chemin_http. '/dbgtgallery_pics/';
	
	///////////////////////////
	if (file_exists($dynamixcache) AND ( 10 > filesize($dynamixcache) )) {  // Si le fichier existe mais qu'il est vide. 
		unlink ($dynamixcache); // On l'efface
	}
	
	if (file_exists($dynamixcache)) { // Si le fichier de cache existe deja
		$response = @file_get_contents($dynamixcache); // On charge le fichier de cache depuis mon serveur
		$data = json_decode($response,true);
		$items = $data;
		foreach ($items as $item){
			$image_url = $item["image_url"];
			if (is_null($alt)) {
				$image_alt = stripslashes($item["tags"]);
			} else {
				$image_alt = $alt;
			}
			$image_id = $item["idp"];
			$author = $item["author"];
			$fullimageurl = "$upload_dir_url_chemin_http_full$image_url";
			@$getsize = getimagesize($fullimageurl); // On choppe les dimensions
			$getsize = isset($getsize[3]) ? $getsize[3] : NULL;
			
			// Lien éventuel
			if (! is_null($href)) {
				$resultatdescourses .= "<a href='$href' target='_blank' rel='noopener'>";
			}
			
			$resultatdescourses .= '<img class="dbgtpremiumpics" id="dbgtid-'.$image_id.'" src="'.$fullimageurl.'" alt="'.$image_alt.'" '.$getsize.'>';
			
			// Lien éventuel
			if (! is_null($href)) {
				$resultatdescourses .= "</a>";
			}
			
		}
		
	} else { // Sinon on appelle l'API et on créé le cache	
	
		////////////////////////////////////////////
		/////// 3rd party vendor libraries ////////
		$response = wp_remote_get("https://pixabay.com/api/?key=$smartgallery_dbgt_apikey&id=$id_du_media_pixabay");
		$body     = wp_remote_retrieve_body( $response );
		///////////////////////////////////////////
		///////////////////////////////////////////
		$data = json_decode($body,true);
		$items = $data['hits'];
		
		// Construction des variables du cache
		if (! is_dir($kapsule_dirstokage_pics)) {
			mkdir( $kapsule_dirstokage_pics, 0755 );
		}
		
		if (! is_dir($kapsule_dirstokage_picsfeed)) {
			mkdir( $kapsule_dirstokage_picsfeed, 0755 );
		}

		$dbgt_json_creator = array();
		
		foreach ($items as $item){
			
			// Récupération des données
			$imagee = $item["$smartgallery_dbgt_imagesize"];
			$tags = $item['tags'];
			$id_pix_pixa = $item['id'];
			$author = $item['user'];
			if (is_null($alt)) {
				$image_alt = stripslashes($item["tags"]);
			} else {
				$image_alt = $alt;
			}
			$extension = puipui_dbgt_get_extension($imagee);
			
			// Lien éventuel
			if (! is_null($href)) {
				$resultatdescourses .= "<a href='$href' target='_blank' rel='noopener'>";
			}
			
			// Image
			$resultatdescourses .= '<img class="dbgtpremiumpics" id="dbgtid-'.$id_pix_pixa.'" src="'.$imagee.'" alt="'.$image_alt.'">';
			
			// Lien éventuel
			if (! is_null($href)) {
				$resultatdescourses .= "</a>";
			}
			
			// Copie de l'image
			copy($imagee, "$kapsule_dirstokage_pics/$store_multi_id$id_page_appelante-$id_pix_pixa-$smartgallery_dbgt_imagesize.$extension");
			
			// Création du JSON
			$minitab = array(
				"idp" => $id_pix_pixa,
				"tags" => addslashes($tags),
				"author" => $author,
				"image_url" => "$store_multi_id$id_page_appelante-$id_pix_pixa-$smartgallery_dbgt_imagesize.$extension"
			);
			$dbgt_json_creator[] = $minitab;
			
		}
		
		$dbgt_json_creator = json_encode($dbgt_json_creator); // on encode en JSON le tableau
		file_put_contents($dynamixcache , $dbgt_json_creator); // On crée le cache
	}
	
	if ($smartgallery_dbgt_legal != "no") {
			
			$resultatdescourses .= '<span class="dbgtgallerycopy">';
			
			if ($smartgallery_dbgt_legal == "yes") {
				
				$resultatdescourses .= '<a href="https://pixabay.com/" rel="nofollow nooperner" target="_blank">';
			}
			
			$resultatdescourses .= '&copy; Crédit Photo : https://pixabay.com/';
			
				
			if ($smartgallery_dbgt_legal == "yes") {
				
				$resultatdescourses .= '</a>';
			}
			
			$resultatdescourses .= '</span>';
				
	}
	
	return $resultatdescourses;
}

add_shortcode( 'dbgtpremium', 'majin_dbgt_gallery_premium' );