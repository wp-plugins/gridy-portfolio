<?php
/*

Plugin Name: Gridy Portfolio
Version: 1.0
Author URI:
Plugin URI: #
Description:  Portfolio grid with google like expandable preview for title, description and project link
Author: Gangesh Matta

*/
define('GPORTFOLIO_VERSION', '0.1');
define('GPORTFOLIO_URL', plugin_dir_url(__FILE__));
define('GPORTFOLIO_DIR', plugin_dir_path(__FILE__));
define('GPORTFOLIO_BASENAME', plugin_basename(__FILE__));

function gportfolio_function()
{
?>
<script>
   jQuery( document ).ready(function() {
       Grid.init();
       console.log( "ready!" );
   });
   
</script>
<?php
}

add_action('wp_footer', 'gportfolio_function', 100);

function theme_setup()
{
	add_editor_style();
	add_image_size('port-thumb', 325, 233, true);
}

add_action('after_setup_theme', 'theme_setup');

function head_code()
{
	$render_here = '<!--[if lt IE 9]>
<script src="' . GPORTFOLIO_DIR . '/js/html5.js" type="text/javascript"></script>
<![endif]-->
<!--[if lte IE 8]>
<style> .collapsible-container { display: none} </style>
<script src="' . GPORTFOLIO_DIR . 'js/html5.js"></script><![endif]-->
';
	echo $render_here;
}

add_action('wp_head', 'head_code', 100);

function gportfolio_style()
{
	
	wp_enqueue_style('gportfolio-stlye', GPORTFOLIO_URL . 'style.css');
	
	/*wp_register_script('jquery', GPORTFOLIO_URL . "/js/jquery.js");
	wp_enqueue_script('jquery');*/
	wp_enqueue_script('modernizr-js', GPORTFOLIO_URL . "/js/modernizr.custom.js", array('jquery') );
	wp_enqueue_script('grid-js', GPORTFOLIO_URL . "/js/grid.js", array(	'jquery') , '', true);
	
	
}

add_action('wp_enqueue_scripts', 'gportfolio_style');
add_action('init', 'register_gportfolio');

function register_gportfolio()
{
	register_post_type('gportfolio', array(
		'label' => 'G Portfolio',
		'description' => '',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'capability_type' => 'post',
		'hierarchical' => true,
		'rewrite' => array(
			'slug' => ''
		) ,
		'query_var' => true,
		'exclude_from_search' => false,
		'supports' => array(
			'title',
			'editor',
			'custom-fields',
			'thumbnail',
		) ,
		'labels' => array(
			'name' => 'gportfolio',
			'singular_name' => 'G Portfolio item',
			'menu_name' => 'G Portfolio',
			'add_new' => 'Add Portfolio item',
			'add_new_item' => 'Add New Portfolio item',
			'edit' => 'Edit',
			'edit_item' => 'Edit Portfolio item',
			'new_item' => 'New Portfolio item',
			'view' => 'View Portfolio item',
			'view_item' => 'View Portfolio item',
			'search_items' => 'Search Portfolio items',
			'not_found' => 'No Portfolio item Found',
			'not_found_in_trash' => 'No Portfolio item Found in Trash',
			'parent' => 'Parent Portfolio item',
		) ,
	));
}

function create_gtaxonomy()
{
	register_taxonomy('gportfolio-category', 'gportfolio', array(
		'hierarchical' => true,
		'label' => 'Category',
		'query_var' => true,
		'rewrite' => true
	));
}

add_action('init', 'create_gtaxonomy', 0);

// Custom Post Type - Portfolio

add_shortcode('gportfolio', 'portfolio_shortcode_query');

function portfolio_shortcode_query($opt, $content)
{
	$atts = array(
		'posts_per_page' => '-1',
		'post_type' => 'gportfolio'
	);
	if (@$opt['col'] == 3 || @$opt['col'] == '')
	{
		$col = "div4";
		$image_size = "port-thumb";
	}

	if (@$opt['col'] == 4)
	{
		$col = "div3";
		$image_size = "four-col";
	}

	if (@$opt['col'] == 2)
	{
		$col = "div6";
		$image_size = "two-col";
	}

	global $post;
	$posts = new WP_Query($atts);
	$out = '<div class="work-outer bg-div outer-div box-container" id="portfolio">
   <div>
      <div class="work-inner">
         <div class="filterable-32527">
            <div class="filter-categories sixteen columns">
               <div class="filter fmenu-wrap">
                  <nav id="porfolio-nav" class="clearfix">
                     <ul id="portfolio-filter" class="work-menus class="nav nav-tabs clearfix">
                        ';
	
  
  $out.= '<li><a href="#" data-filter=".all" class="selected">All</a></li>
                        ';
	$args = array(
		'name' => 'gportfolio'
	);

  
  $taxonomies = get_terms('gportfolio-category');
	
  
  foreach($taxonomies as $taxonomy)
		{
			$out.= '<li><a href="#" class="filter" data-filter=".' . strtolower($taxonomy->name) . '">' . $taxonomy->name . '</a></li>';
		}

	$out.= '</ul></nav></div>
            </div>
            <div class="clear"></div>
            <div class="portfolio_details"></div>
            ';
	$out.= '
            <div class="filterable_portfolio isotope">
               <div class="portfolio-wrapper">
                  <div class="main">
                     <ul class="og-grid effect-moveup" id="og-grid">
                        ';
	
  if ($posts->have_posts())
	
    while ($posts->have_posts()):
		
    $posts->the_post();
		$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID) , 'port-thumb');
		$taxo = get_the_terms($posts->get_queried_object_id() , 'gportfolio-category');
		$taxi_list = '';
	
  if ($taxo)
		{
			foreach($taxo as $taxi)
			{
				$taxi_list.= strtolower($taxi->name) . ' ';
			}
		}
  

		$thumb_id = get_the_post_thumbnail($post->ID, 'port-thumb', array(
			'class' => 'img-portfolio'
		));
	
	  $thumb_big_url = wp_get_attachment_image_src($thumb_id, $image_size, true);
		$thumb_url = wp_get_attachment_image_src($thumb_id);
		$string = strip_tags(get_the_content());
	
  if (strlen($string) > 20)
		{

			// truncate string

			$stringCut = substr($string, 0, 80);

			// make sure it ends in a word so assassinate doesn't become ass...

			$string = substr($stringCut, 0, strrpos($stringCut, ' ')) . '..';
		}
			
  	$g_link = get_post_meta( $post->ID, 'g_link', true ); 
		
  
  	$out.= '
                        <li data-tags="' . $taxi_list . '" class="' . $col . ' column ' . $taxi_list . ' all ">
                           <a class="work-details" href="' .$g_link. '" data-largesrc="' . $image[0] . '" data-title="' . get_the_title() . '" data-description="' . get_the_content() . '">
                              <div class="portf-load work-image-wrap" rel="nofollow">
                                 <div class="work-image-wrap-inner">
                                    ' . $thumb_id . ' 			
                                    <div class="hoverimage">
                                       <div class="overlay-img"></div>
                                    </div>
                                 </div>
                              </div>
                              <div class="work-desc-wrap">
                                 <span class="work-title">	' . get_the_title() . '	</span>
                                 <div class="work-desc">
                                    ' . $string . '
                                 </div>
                              </div>
                           </a>
                        </li>
                        ';

		// add here more...

		/* these arguments will be available from inside $content
		get_permalink()
		get_the_content()
		get_the_category_list(', ')
		get_the_title()
		and custom fields
		get_post_meta($post->ID, 'field_name', true);
		*/
	endwhile;
	else return; // no posts found
	$out.= '
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
';
	wp_reset_query();
	return html_entity_decode($out);
}
