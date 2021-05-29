<?php
/*
 
Plugin Name: Woocommerce Similar Search
 
Plugin URI: 
 
Description: Woocommerce live AJAX product search which includes products of similar naming.
 
Version: 1.0
 
Author: Dylan Elliott
 
*/

?>

<?php



class Woocommerce_Similar_Search{

    protected $siteUrl;

    public function __construct(){
        add_shortcode( 'similar-search', array($this, 'similar_search_shortcode'));
        add_action( 'wp_enqueue_scripts', array($this, 'woocommerce_similar_search_scripts_enqueue'));
        add_action( 'wp_enqueue_scripts', array($this, 'woocommerce_similar_search_styles_enqueue'));
        add_action('wp_ajax_data_fetch' , array($this, 'get_live_similar_search_results'));
        add_action('wp_ajax_nopriv_data_fetch', array($this, 'get_live_similar_search_results'));
        add_action( 'woocommerce_product_query', array($this, 'filter_product_archive_search_results'), 1000 );

        $this->siteUrl = get_site_url();
    }
    


    public function similar_search_shortcode(){
        return "
        <div class='woocomm-live-search-container'>
        <form class='d-flex' action='$this->siteUrl' method='get'>
            <input class='woocommsearch woocomm-simi-search-input' name='s' type='text' autocomplete='off'>
            <input  type='hidden' name='post_type' value='product' />
            <button class='similar-search-button' type='submit'>
              <div id='loading'></div>
              <img class='woocomm-sim-search-magnifier' src='". plugin_dir_url(__FILE__) . '/search.svg' ."' />
            </button>
        </form>
        <div class='found-matches'>
        </div>
    </div>
        ";
    }

    public function woocommerce_similar_search_scripts_enqueue(){
        wp_enqueue_script('woocommerce-similar-search-script', plugin_dir_url(__FILE__) . 'js/woocommerce-similar-search-input.js', array(), false, true);
        wp_localize_script('woocommerce-similar-search-script', 'my_script_vars', array(
            'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }

    public function woocommerce_similar_search_styles_enqueue(){
        wp_enqueue_style('woocommerce-similar-search-style', plugin_dir_url(__FILE__) . 'css/woocommerce-similar-search-style.css');
    }

    public function get_live_similar_search_results(){
        $prodsForCustomSearch = wc_get_products(array());

		$searchTerm = strtolower($_POST['keyword']);
		$possibleMatches = array();

		
	  
		foreach($prodsForCustomSearch as $prod){
		  similar_text($searchTerm, strtolower($prod->get_name()), $percent);
		  if($percent > 40){
			array_push($possibleMatches, array('name' => $prod->get_name(), 'url' => get_permalink($prod->get_id()), "similarity" => $percent));
		  }
		  else if (strpos(strtolower($prod->get_name()), $searchTerm) !== false) {
			array_push($possibleMatches, array('name' => $prod->get_name(), 'url' => get_permalink($prod->get_id())));
		  }
		}
		echo json_encode($possibleMatches);
		die();
    }

    public function filter_product_archive_search_results($q){
        if (is_post_type_archive( 'product' ) && $q->is_main_query() && isset($q->query['s'])) {
			$prodsForCustomSearch = wc_get_products(array());
			$matchingIds = array();

            if($q->query['s'] == ""){
                return;
            }

			foreach($prodsForCustomSearch as $prod){
				similar_text(strtolower($q->query['s']), strtolower($prod->get_name()), $percent);
				if($percent > 40){
				  array_push($matchingIds, $prod->get_id());
				}
				else if (strpos(strtolower($prod->get_name()), strtolower($q->query['s'])) !== false) {
				  array_push($matchingIds, $prod->get_id());
				}
			  }
			$q->set("post__in", $matchingIds);
			unset($q->query['s']);
			unset($q->query_vars['s']);
            unset($q->is_search);
		 }
    }
}

$similarSearchInstance = new Woocommerce_Similar_Search();  


?>