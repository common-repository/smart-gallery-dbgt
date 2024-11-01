<?php 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

$dbgt_doubleface = false;
$dbgt_home_url = get_home_url();
$dbgt_home_url = str_replace("https://", "", $dbgt_home_url);
$dbgt_home_url = str_replace("http://", "", $dbgt_home_url);
$dbgt_home_url = str_replace("www.", "", $dbgt_home_url);

$dbgt_unik = base64_encode($dbgt_home_url);
$dbgt_unik = preg_replace("/[^a-zA-Z0-9]/", "", $dbgt_unik);

if ( is_multisite() ) {
	
	$dbgt_iddusite = get_current_blog_id();
	$dbgt_lkey = "$dbgt_unik-NJD54-$dbgt_iddusite";
	
} else {
	
	$dbgt_lkey = "SJFEDD54-$dbgt_unik";
	
}
$dbgt_cookie_token = ASGDBGT_ROOTPATH . "temp/token-$dbgt_lkey.json";
$dbgt_newjeton = false;

// L'URL est-elle bien déclarée
$dbgt_url = get_site_url(); 

function dbgt_check_site_current_url ($url) {
	$url = str_replace("https://", "", $url);
	$url = str_replace("http://", "", $url);
	$url = str_replace("www.", "", $url);
	
	if (substr($url, -1) == '/') {
		
		$url = substr($url,0,strlen($url)-1);
		
	}
	return $url;
}

$dbgt_choppezla = dbgt_check_site_current_url($dbgt_url);	

// On vire le cache token quand on update la clé API
function check_licence_update_dbgt(){
	
		global $dbgt_cookie_token;
		if (file_exists($dbgt_cookie_token)) { // S'il existe
			unlink ($dbgt_cookie_token);
		}
		check_kapsuleapi_dbgt_licence();
		
}


function dbgt_connect_2_gtz_server ($call) {

	global $dbgt_cookie_token;
	global $dbgt_choppezla;
	
	$urlcheckapi = base64_decode($call);
	$jokerjeton = get_option('puipui_dbgt_form_option_apijeton');
	
	$api_endpoint = "$urlcheckapi$jokerjeton&url=$dbgt_choppezla";
	
	$time_args = array(
		'timeout'     => 2
	); 
	
	$kapsdata = wp_remote_get($api_endpoint,$time_args);
	
	if ( is_array( $kapsdata ) && ! is_wp_error( $kapsdata ) ) { // Empeche une erreur fatale en cas d'impossibilité de récupérer le flux
	
		$kapsdata_array = json_decode($kapsdata["body"], true);
		
		if (is_null($kapsdata_array)) {
			
			$statut = "cUrl"; // Vraiment impossible de se connecter au serveur Gothamazon / Bug CUrl RemoteAPI		
			
		} elseif( is_wp_error( $kapsdata  ) || !isset($kapsdata["body"]) || $kapsdata["response"]["code"] != 200 || $kapsdata_array['token'] != 'KISh8sUJD5848gkfoSKKSuS' || $kapsdata_array['acces'] != 'true') {
			
			$statut="Erreur";
			
		} else {
			
			if ($kapsdata_array['limit'] == 'premium') {
				
				$statut="premium";
				
			} elseif ($kapsdata_array['limit'] == 'godmod') {
				
				$statut="godmod";
				
			} else {
				
				$statut="free";
				
			}
			
			$kakabody = $kapsdata["body"];
			
			if ((!empty($dbgt_cookie_token)) AND (!empty($kakabody))) {
				
				file_put_contents($dbgt_cookie_token , $kakabody); // On crée le cache
				
			}
			
		} 
		
		
	} else {
	
		$statut = "cUrl"; // Vraiment impossible de se connecter au serveur Gothamazon / Bug CUrl RemoteAPI		
				
	}
		
	return $statut;

}

// On check si API valide
function check_kapsuleapi_dbgt_licence() {

	global $dbgt_doubleface;
	global $dbgt_cookie_token;
	global $dbgt_newjeton;

	 // Si le fichier existe et (qu'il a + de 6H OU qu'il est vide) 
	if (file_exists($dbgt_cookie_token) && (( time() - 21600 > filemtime($dbgt_cookie_token)) OR ( 0 == filesize($dbgt_cookie_token) ) OR ($dbgt_newjeton == true))) { 

		unlink ($dbgt_cookie_token); // On l'efface
		
	}

	if (file_exists($dbgt_cookie_token)) { // Si le fichier de cache existe deja

		$kapsdata = @file_get_contents($dbgt_cookie_token); // On charge le fichier de cache
		$kapsdata_array = json_decode($kapsdata, true);
		if ($kapsdata_array['token'] == 'KISh8sUJD5848gkfoSKKSuS' AND $kapsdata_array['acces'] == 'true') {
				if ($kapsdata_array['limit'] == 'premium') {$statut="premium";}
				elseif ($kapsdata_array['limit'] == 'godmod') {$statut="godmod";}
				else {$statut="free";}
			} 
			else {$statut="Erreur";}
		return $statut;
		
	} else {
		
		$urlcheckapi = "aHR0cHM6Ly9nb3RoYW1hem9uLmNvbS9saWNlbmNlLnBocD90b2tlbj0";
		$urlcheckapi_alt = "aHR0cHM6Ly9nb3JpbGxlLm5ldC9ndHpfYXBpMi5waHA/dG9rZW49";
		
		$statut = dbgt_connect_2_gtz_server($urlcheckapi);
		
		if ($statut == "cUrl") {
			
			$try_2 = dbgt_connect_2_gtz_server($urlcheckapi_alt);
			
			if ($try_2 == "cUrl") {

				$statut = "cUrl";
				
			} else {
				
				$statut = $try_2;
				
			}	
			
		} 
		
		return $statut; 
			
	}
}
	