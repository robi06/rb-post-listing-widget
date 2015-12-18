<?php
/*
Plugin Name: RB Post Listing Widget
Plugin URI: https://github.com/robi06
Description: Displays a list of posts based on filter type
Author: Md Robiul Islam
Version: 0.1
Author URI: https://github.com/robi06
*/

class Rb_Post_Listing_Widget extends WP_Widget {

   /** constructor */
    function __construct() {
      define( 'RI_POST_LISTING_WIDGET_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
      parent::__construct(

          // base ID of the widget
          'rb_post_listing_widget',
          // name of the widget
          __('Post Listing', 'rb-post-listing' ),

          // widget options
          array (
              'description' => __( 'displays a list of posts based on filter type in the same section of the site.', 'rb-post-listing' )
          )

      );

    }

    function uc_load_scripts(){

      wp_register_script( 'slimscrollHandle', RI_POST_LISTING_WIDGET_URL. '/assets/js/jquery.slimscroll.min.js', array(), false, true );
      wp_enqueue_script( 'slimscrollHandle' );
      wp_register_script( 'scrollHandle', RI_POST_LISTING_WIDGET_URL. '/assets/js/scroll.js', array(), false, true );
      wp_enqueue_script( 'scrollHandle' );
    }

    function form($instance) {
      if( isset( $instance[ 'pformat' ]) && $instance[ 'pformat' ] != "marquee" )
        add_action( 'wp_footer', array( $this ,'uc_load_scripts' ) );

      if ( isset( $instance[ 'title' ] ) ) {
        $title = $instance[ 'title' ];
      }else {
        $title = __( 'New title', 'rb-post-listing' );
      }

      $post_type = isset( $instance[ 'post_type' ])?esc_attr($instance['post_type']):'post';
      $post_types = get_post_types('','names');
      $invalid_post_types = array('nav_menu_item','revision','attachment'); // others????

      $post_number = isset( $instance[ 'number' ])?esc_attr($instance['number']):5;
  		$post_timeline = isset( $instance[ 'post_timeline' ])?esc_attr($instance['post_timeline']):'most-recent';
  		$pformat = isset( $instance[ 'pformat' ])?esc_attr($instance['pformat']):'ul';
		?>
      <p>
        <label>Title:</label>
      	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
      </p>
      <p>
        <label>Post Type:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('filter_type'); ?>" name="<?php echo $this->get_field_name('filter_type'); ?>">
        	<?php
          foreach ($post_types as $type ) {
            if (!in_array($ptype,$invalid_post_types)) {
              echo "<option value=\"$type\" ";
              if ($post_type==$type) echo 'selected="true"';
              echo ">$type</option>";
            }
          }
        ?>
        </select>
      </p>
      <p>
        <label>Number of posts to show:</label>
		    <input name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $post_number;?>" size="3">
      </p>
      <p>
        <label>Post Timeline:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('post_timeline'); ?>" name="<?php echo $this->get_field_name('post_timeline'); ?>">
        	<option value="most-recent"<?php if ($post_timeline=="most-recent") echo ' selected="true"';  ?>>Most Recent</option>
          <option value="most-old"<?php if ($post_timeline=="most-old") echo ' selected="true"';  ?>>Most Old</option>
          <option value="month0"<?php if ($post_timeline=="month0") echo ' selected="true"';  ?>>This Month's Posts</option>
          <option value="month1"<?php if ($post_timeline=="month1") echo ' selected="true"';  ?>>Next Month's Post</option>
          <option value="month-1"<?php if ($post_timeline=="month-1") echo ' selected="true"';  ?>>Last Month's Post</option>
        </select>
      </p>

      <p>
        <label>Format:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('pformat'); ?>" name="<?php echo $this->get_field_name('pformat'); ?>">
        	<option value="marquee"<?php if ($pformat=="marquee") echo ' selected="true"';  ?>>List With Marquee</option>
          <option value="ul"<?php if ($pformat=="ul") echo ' selected="true"';  ?>>Unordered List</option>
        	<option value="br"<?php if ($pformat=="br") echo ' selected="true"';  ?>>BR line Breaks</option>
        </select>
      </p>

  <?PHP
  		// end of the functional section
  	}

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
  		// processes widget options to be saved
  		// have to update the new instance
  		return $new_instance;
  	}

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
      // kick things off

      $title = isset( $instance[ 'orderby' ])?esc_attr($instance['title']):'';
      extract( $args );
      echo $before_widget;
      echo $before_title . $title . $after_title;

      $post_type = isset( $instance[ 'post_type' ])?esc_attr($instance['post_type']):'post';
      $post_timeline = isset( $instance[ 'post_timeline' ])?esc_attr($instance['post_timeline']):'most-recent';
  		$pformat = isset( $instance[ 'pformat' ])?esc_attr($instance['pformat']):'ul';
      $sort= 'date';
      $order = 'DESC';
      $datelimit='';
  		$key = 'post_date';
      $args = array(
          'posts_per_page' => -1,
          'post_type' => $post_type,
          'orderby'   => $sort,
          'order'     => $order,
          'post_status' => 'publish'
      );

      if (substr($post_timeline,5)=='0') {
        $start_time = date('m/d/Y', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $end_time = date('m/d/Y', mktime(0, 0, 0, date('m')+1, 1, date('Y')));
        $args['date_query'] = array(
          array(
            'column'  => $key,
            'after'   => $start_time
          ),
          array(
            'column'  => $key,
            'before'   => $end_time
          )
        );
      } elseif(substr($post_timeline,5)=='-1'){
        $start_time = date('m/d/Y', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
        $end_time = date('m/d/Y', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $args['date_query'] = array(
          array(
            'column'  => $key,
            'after'   => $start_time
          ),
          array(
            'column'  => $key,
            'before'   => $end_time
          )
        );

      }elseif(substr($post_timeline,5)=='1'){
        $start_time = date('m/d/Y', mktime(0, 0, 0, date('m')+1, 1, date('Y')));
        $end_time = date('m/d/Y', mktime(0, 0, 0, date('m')+2, 1, date('Y')));
        $args['post_status'] = 'future';
        $args['date_query'] = array(
          array(
            'column'  => $key,
            'after'   => $start_time
          ),
          array(
            'column'  => $key,
            'before'   => $end_time
          )
        );

    }elseif($post_timeline =='most-old'){
      $args['orderby'] = 'date';
      $args['order'] = 'ASC';
    }


  		$items = get_posts( $args );
      //echo "<pre>";print_r($items);
  		if (empty($items)) {echo "\r\n\r\n Sorry, There is no post available \r\n\r\n"; return;}

  		// now go through the list
  		$max=$post_timeline;
  		if ($post_timeline=='most-old' || $post_timeline=='most-recent'|| substr($post_timeline,0,5)=='month') {
  			$max=count($items);
  		}
  		$out='';
      global $wp_query;
  		$thePostID = $wp_query->post->ID;
  		foreach ($items as $post) {
  			$max--;
  			if ($max<0) break;
  			// post needs title, permalink and parent (if we are doing hierarchical)
  			$post_title=$post->post_title;
  			$ID=$post->ID;
  			$cpi='';
  			if ($thePostID==$ID) {
  			   $cpi=' current_page_item';
  			}
  			$post_link= home_url( '/cases/' .$post->post_name);
        switch($pformat) {
  				case 'ul';
  					$out.= "\r\n<li class=\"page_item page-item-$ID $cpi\"><a href=\"$post_link\" title=\"$post_title\" class=\"custom_post_item$cpi\" >".$post_title."</a></li>";
  					break;
  				case 'br';
  					$out.= "\r\n<a href=\"$post_link\" title=\"$post_title\" class=\"custom_post_item$cpi\" >".$post_title."</a><br/>";
  					break;
  				default;
  					$out.= "\r\n<a href=\"$post_link\" title=\"$post_title\" class=\"custom_post_item$cpi\" >".$post_title."</a><br/>";
  			}
    }
		if ( !empty( $out ) ) {
        if($pformat == "ul")
          echo '<div class="inner-content-div">';
        else
          echo '<marquee height="150" align="center" behavior="scroll" direction="up" scrollamount="1" scrolldelay="30"  truespeed  onmouseover="this.stop()" onmouseout="this.start()">';
  			switch($pformat) {
  				case 'ul';
  					echo "<ul>";
  					break;
  				case 'br';
  					echo "<br/>";
  					break;
  				default;
  					echo "<br/>";
  			}
			// out goes out here;
			echo $out;
			// close the ul or select or add a blank line
			switch($pformat) {
				case 'ul';
					echo "</ul>";
					break;
				case 'br';
					echo "<br/>";
					break;
				default;
					echo "<br/>";
			}
      if($pformat == "ul")
        echo "</div>";
      else
        echo "</marquee>";

			echo $after_widget;
		}
		echo "\r\n<!-- end of Custom Post Type List Widget -->\r\n";

	}

}

function register_post_listing_widget() {
    register_widget( 'rb_post_listing_widget' );
}
add_action( 'widgets_init', 'register_post_listing_widget' );
