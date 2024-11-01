<?php
/**
 * The WordPress Plugin Swift custom post navigator
 *
 *
 * @package   Swift_Custom_Post_Navigator
 * @author    augustinfotech <http://www.augustinfotech.com/>
 * @license   GPL-2.0+
 * @link      http://www.augustinfotech.com
 * @copyright 2014 August Infotech
 *
 * @wordpress-plugin
 * Plugin Name:       Swift Custom Post Navigator
 * Description:       Swift Custom Post Navigator is a WordPress plugin which creates widget & displays post navigation using some CSS transforms and transitions. 
 * Version:           1.1
 * Author:            August Infotech
 * Author URI:        http://www.augustinfotech.com/
 * Text Domain:       aipostnav
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('MT_PDIR_PATH',plugin_dir_path(__FILE__ ));
$_SESSION['ulId'] = 1;
/* ================================================ Create Widget - START ============================================================= */
/**
* Registered the widget Custom Post Selection using widgets_init hook
*
* Function Name: ai_post_settings
*
* @access public
* @param 
*
* @created by E048 and 06/11/2014
**/

add_action( 'plugins_loaded','ai_post_settings' );
add_action('init','ai_image_init');

function ai_image_init()
{
	add_image_size("swift-custom-post-image",200,125,true);
}

function ai_post_settings(){
	
	add_action( 'widgets_init', 'ai_load_custompost_widget' );
}

function ai_load_custompost_widget() {
	register_widget( 'ai_custompost_widget' );
}

class ai_custompost_widget extends WP_Widget
{
	
	function __construct()
	{
		parent::__construct('ai_custompost_widget', __('Custom Post Selection', 'aipostnav'), array( 'description' => __( 'Selection of the custom post type', 'aipostnav' ), ));
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance )
	{  
		$assign_ulId = $_SESSION['ulId'];
		
		wp_enqueue_style('ai_postwidget_swift_custom_post_css',  plugins_url('include/css/swift_custom_post.css', __FILE__ ) );
		wp_enqueue_style('ai_postwidget_swift_custom_css',  plugins_url('include/css/swift_custom.css', __FILE__ ) );
		
		wp_enqueue_script('ai_postwidget_modernizr_script', plugins_url( 'include/js/modernizr.custom.79639.js' , __FILE__ ) );
		wp_enqueue_script('ai_postwidget_swift_custom_post_script', plugins_url( 'include/js/jquery.swift.custom.post.js' , __FILE__ ),array('jquery'),'',false );
		$ai_post_type = $instance['ai_post_type'];
		$ai_title = $instance['ai_title'];
		$ai_no_post = $instance['ai_no_post'];
		$ai_post_content_len = $instance['ai_post_content_len'];
		$ai_post_title_display = $instance['ai_post_title_display'] ? true : false;
		
		if(empty($ai_no_post))
			$ai_no_post = -1;
		
		$args = array(
					'posts_per_page'   => $ai_no_post,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_type'        => $ai_post_type,
					'post_status'      => 'publish',
					'suppress_filters' => true );
	
		$posts_array = get_posts( $args );
		echo "<h2 style='text-align: center;'>".$ai_title."</h2>";
		if(count($posts_array) >= 1)	
		{
			echo "<div class='windy-demo windy-demo-2'>";
			echo "<ul id='wi-el".$assign_ulId."' class='wi-container'>";
				foreach($posts_array as $value)
				{
					echo "<li>";
					
					if(empty($ai_post_content_len))
						$ai_post_content_len = 10;
					elseif(($ai_post_content_len >= 20) && (has_post_thumbnail( $value->ID ) == true))
						$ai_post_content_len = 10;
					elseif(($ai_post_content_len >= 20) && (has_post_thumbnail( $value->ID ) == false))
						$ai_post_content_len = 20;
						
					echo "<a href=".get_the_permalink($value->ID).">".get_the_post_thumbnail( $value->ID,'swift-custom-post-image')."</a>";
					
					if($ai_post_title_display)
						echo "<a href=".get_the_permalink($value->ID)."><h4>".$value->post_title."</h4></a>";
					
					if($value->post_content)
					{
						echo "<p>".wp_trim_words($value->post_content,$ai_post_content_len)."</p>";
					}
					echo "</li>";
				}
			echo "</ul>";	
			if($ai_no_post > 1 || $ai_no_post == -1)
			{
				echo "<nav>";
					echo "<span id='nav-prev".$assign_ulId."'>prev</span>";
					echo "<span id='nav-next".$assign_ulId."'>next</span>";
				echo "</nav>";
			}
			
			echo "</div>";	
			echo "<div class='clear'></div>";
			
			?>
				<script>
					jQuery(function($) {
						assign_ulId = '<?php echo $assign_ulId ;?>';
						var $el = $( '#wi-el'+assign_ulId ),
							windy = $el.windy( {
								// rotation and translation boundaries for the items transitions
								boundaries : {
									rotateX : { min : 40 , max : 90 },
									rotateY : { min : -15 , max : 45 },
									rotateZ : { min : -10 , max : 10 },
									translateX : { min : -400 , max : 400 },
									translateY : { min : -400 , max : 400 },
									translateZ : { min : 350 , max : 550 }
								}
							} ),
							
							allownavnext = false,
							allownavprev = false;

						$( '#nav-prev'+assign_ulId ).on( 'mousedown', function( event ) {

							allownavprev = true;
							navprev();
						
						} ).on( 'mouseup mouseleave', function( event ) {

							allownavprev = false;
						
						} );

						$( '#nav-next'+assign_ulId ).on( 'mousedown', function( event ) {

							allownavnext = true;
							navnext();
						
						} ).on( 'mouseup mouseleave', function( event ) {

							allownavnext = false;
						
						} );

						function navnext() {
							if( allownavnext ) {
								windy.next();
								setTimeout( function() {	
									navnext();
								}, 150 );
							}
						}

						function navprev() {
							if( allownavprev ) {
								windy.prev();
								setTimeout( function() {	
									navprev();
								}, 150 );
							}
						}

					});
				</script>
			<?php
				$_SESSION['ulId'] = $_SESSION['ulId']+1;
		}
		else
		{
			echo "<div class='no-post'><p>No Post Found.</p></div>";
		}	
	}
		
	// Widget Backend 
	public function form( $instance )
	{
		$ai_title = '';
		$ai_post_type = '';
		$ai_no_post = '';
		$ai_post_title_display = '';
		$ai_post_content_len = '';
		if ( isset( $instance[ 'ai_post_type' ] ) )
			$ai_post_type = $instance[ 'ai_post_type' ];
		
		
		if ( isset( $instance[ 'ai_title' ] ) )
			$ai_title = $instance[ 'ai_title' ];
			
		if ( isset( $instance[ 'ai_no_post' ] ) )
			$ai_no_post = $instance[ 'ai_no_post' ];
		
		if ( isset( $instance[ 'ai_post_content_len' ] ) )
			$ai_post_content_len = $instance[ 'ai_post_content_len' ];
			
		if ( isset( $instance[ 'ai_post_title_display' ] ) )
			$ai_post_title_display = $instance[ 'ai_post_title_display' ];
		
		// Widget admin form
		$getposttype_args = array();
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'ai_title' ); ?>"><?php _e( 'Title','aipostnav' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'ai_title' ); ?>" name="<?php echo $this->get_field_name( 'ai_title' ); ?>" type="text" value="<?php echo esc_attr( $ai_title ); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'ai_post_type' ); ?>"><?php _e( 'Post Type:','aipostnav' ); ?></label> 
		<select id="<?php echo $this->get_field_id('ai_post_type'); ?>" name="<?php echo $this->get_field_name('ai_post_type'); ?>" class="widefat" style="width:100%;">
		    <?php foreach(get_post_types($getposttype_args,'names') as $post_type) {
				 $exclude = array( 'page', 'revision','nav_menu_item','attachment' );
  				 if( TRUE === in_array( $post_type, $exclude ) )
               		 continue;
				 ?>
		        <option <?php selected( $ai_post_type, $post_type ); ?> value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
		    <?php } ?>      
		</select>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'ai_no_post' ); ?>"><?php _e( 'No. Of Post:','aipostnav' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'ai_no_post' ); ?>" name="<?php echo $this->get_field_name( 'ai_no_post' ); ?>" type="text" value="<?php echo esc_attr( $ai_no_post ); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'ai_post_content_len' ); ?>"><?php _e( 'Post Content Length:','aipostnav' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'ai_post_content_len' ); ?>" name="<?php echo $this->get_field_name( 'ai_post_content_len' ); ?>" type="text" value="<?php echo esc_attr( $ai_post_content_len ); ?>" />
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'con_text2' ); ?>"><?php _e( 'Display Post Title?' ,'aipostnav'); ?></label> 
		<input class="checkbox" type="checkbox" <?php checked($ai_post_title_display, 'on'); ?> id="<?php echo $this->get_field_id('ai_post_title_display'); ?>" name="<?php echo $this->get_field_name('ai_post_title_display'); ?>" /> 
		</p>
		
		<?php 
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['ai_post_type'] = ( ! empty( $new_instance['ai_post_type'] ) ) ? strip_tags( $new_instance['ai_post_type'] ) : '';
		$instance['ai_title'] = ( ! empty( $new_instance['ai_title'] ) ) ? strip_tags( $new_instance['ai_title'] ) : '';
		$instance['ai_no_post'] = ( ! empty( $new_instance['ai_no_post'] ) ) ? strip_tags( $new_instance['ai_no_post'] ) : '';
		$instance['ai_post_content_len'] = ( ! empty( $new_instance['ai_post_content_len'] ) ) ? $new_instance['ai_post_content_len'] : '';
		$instance['ai_post_title_display'] = $new_instance['ai_post_title_display'];
		return $instance;
	}
	
}
/* ================================================ Create Widget - END ============================================================= */

/*====================================== Plugin deactivate - Start ================================================ */
/**
*  Deactivate Plugin : When deactivate plugin then meta value deleted in database.
*
* Function Name: postnav_deactivate
*
* @created by E048 and 06/11/2014
*
**/
register_deactivation_hook( __FILE__,'postnav_deactivate' );
function postnav_deactivate()
{
	add_action( 'widgets_init', 'ai_remove_custompost_widget' );
}
function ai_remove_custompost_widget()
{
	unregister_widget('ai_custompost_widget');
}
/*====================================== Plugin deactivate - End ================================================ */

/*====================================== Plugin uninstall - Start ================================================ */
/**
*  Uninstall Plugin : When uninstall plugin then tabel droped in databse.
*
* Function Name: postnav_uninstall
*
* @created by E048 and 06/11/2014
*
**/
register_uninstall_hook( __FILE__, 'postnav_uninstall'  );
function postnav_uninstall()
{
	add_action( 'widgets_init', 'ai_remove_custompost_widget' );
}

/*====================================== Plugin uninstall - End ================================================ */

?>