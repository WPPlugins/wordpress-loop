<?php
/**
 * Plugin Name: WordPress Loop Widget
 * Plugin URI: http://ptahdunbar.com/plugins/wordpress-loop/
 * Description: A WordPress widget that gives you unprecendeted control over displaying your content.
 * Version: 0.4
 * Author: Ptah Dunbar
 * Author URI: http://ptahdunbar.com
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 *
 *	Copyright 2010 Ptah Dunbar (http://ptahdunbar.com/contact)
 *
 *	    This program is free software; you can redistribute it and/or modify
 *	    it under the terms of the GNU General Public License, version 2, as 
 *	    published by the Free Software Foundation.
 *
 *	    This program is distributed in the hope that it will be useful,
 *	    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	    GNU General Public License for more details.
 *
 * @package WordPress_Loop
 */

// Load in the loop functions
require( 'functions.php' );

// Translation declaration
load_plugin_textdomain( 'wordpress-loop', false, '/wordpress-loop' );

// Load up the WordPress Loop Widget
add_action( 'widgets_init', 'register_wordpress_loop_widget' );

// Fix some CSS styling issues in the admin
add_action( 'admin_head-widgets.php', 'wl_widgets_css' );

// Post Thumbnails
add_theme_support( 'post-thumbnails' );

// Register the WordPress Loop widget
function register_wordpress_loop_widget() {
	register_widget( 'WordPress_Loop' );
}

/**
 * WordPress_Loop Widget
 *
 * @since 0.1
 */
class WordPress_Loop extends WP_Widget {
	
	function WordPress_Loop() {
		$widget_ops = array( 'classname' => 'wp-loop', 'description' => __( 'Display custom WordPress loops based on the WP_Query class', 'wordpress-loop' ) );
		$control_ops = array( 'width' => 840 /*835 with no borders*/, 'height' => 350, 'id_base' => 'wordpress-loop' );
		$this->WP_Widget( 'wordpress-loop', 'WordPress Loop', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		
		$args = array();
		
		$post_types = get_post_types( array('exclude_from_search' => false) );
		$args['post_type'] = ( 'all' == $instance['post_type'][0] ) ? $post_types : $instance['post_type'];
		
		$post_type_statuses = array( 'draft' => 'Draft', 'pending' => 'Pending', 'future' => 'Future', 'private' => 'Private', 'publish' => 'Publish', 'trash' => 'Trash' );
		$args['post_status'] = ( 'all' == $instance['post_status'][0] ) ? join( ',', array_keys($post_type_statuses) ) : join( ',', $instance['post_status'] );
		
		$args['order'] = $instance['order'];
		$args['orderby'] = $instance['orderby'];
		
		$args['posts_per_page'] = (int) $instance['posts_per_page'];
		$args['offset'] = $instance['offset'];
		
		if ( $instance['pagination'] )
			$args['paged'] = get_query_var('paged') ? get_query_var('paged') : 1;
		
		$args['caller_get_posts'] = $instance['caller_get_posts'] ? true : false; // Enable stick post
		$args['comments_per_page'] = (int) $instance['comments_per_page'];
		
		if ( isset($instance['post_mime_type']) and 'all' != $instance['post_mime_type'] )
			$args['post_mime_type'] = $instance['post_mime_type'];
				
		if ( isset($instance['users']) and 'all' != $instance['users'][0] )
			$args['author'] = join( ',', $instance['users'] );
		
		if ( isset($instance['category']) and 'all' != $instance['category'][0] )
			$args['cat'] = join( ',', $instance['category'] );
			
		if ( isset($instance['post_tag']) and 'all' != $instance['post_tag'][0] )
			$args['tag__in'] = (array) $instance['post_tag'];
			
		// Date arguments
		if ( isset($instance['year']) and 'all' != $instance['year'] )
			$args['year'] = $instance['year'];
		if ( isset($instance['monthnum']) and 'all' != $instance['monthnum'] )
			$args['monthnum'] = $instance['monthnum'];
		if ( isset($instance['w']) and 'all' != $instance['w'] )
			$args['w'] = $instance['w'];
		if ( isset($instance['day']) and 'all' != $instance['day'] )
			$args['day'] = $instance['day'];
		
		// post thumbnails
		if ( $instance['post_thumbnails'] )
			set_post_thumbnail_size( (int) $instance['thumbnail_size_w'], (int) $instance['thumbnail_size_h'], $instance['hard_crop_switch'] );
		
		// Taxonomies
		foreach ( $post_types as $post_type )
			$taxonomies[] = get_object_taxonomies( $post_type );
		$taxonomies = $taxonomies[0];
		
		foreach ( $taxonomies as $tax ) {
			if ( $instance[$tax] and !is_array($instance[$tax]) and 'all' != $instance[$tax] ) {
				$args[$tax] = $instance[$tax];
			}
		}
		
		// Meta arguments
		if ( $instance['meta_key'] )
			$args['meta_key'] = $instance['meta_key'];
		if ( $instance['meta_value'] )
			$args['meta_value'] = $instance['meta_value'];
		if ( 'all' != $instance['meta_compare'] )
			$args['meta_compare'] = $instance['meta_compare'];
		
		// Use original $wp_query
		if ( $instance['wp_query'] ) {
			global $wp_query;
			$args = $wp_query->query_vars;
		}
		
		// add custom query args, overrides anything, even the world!
		if ( $instance['custom_query_args'] ) {
			parse_str( $instance['custom_query_args'], $custom_args );
			$args[] = wp_parse_args( $custom_args, $args );
		}
		
		echo '<div id="widget-wrap-'. $id .'" class="widget-wordpress-loop">' . "\n";
		
		if ( $instance['use_default_styles'] )
			echo $before_widget;
		
		// Title
		if ( $instance['use_default_styles'] and $instance['title'] )
			echo $before_title . $instance['title'] . $after_title;
		elseif ( $instance['h2'] and $instance['title'] )
			echo "<{$instance['headline_tag']} class=\"widget-title\">{$instance['title']}</{$instance['headline_tag']}>";
		
		if ( in_array($instance['post_container'], array( 'ul', 'ol' )) )
			echo "<{$instance['post_container']}>\n";
		
		// Build the query
		$loop = new WP_Query( $args );
		
		// Check to see if any entries exists
		if ( $loop->have_posts() ) {
			do_action( 'before_loop' );
			
			// Set up the meta data and loop through each entry
			while ( $loop->have_posts() ) {
				$loop->the_post(); // Setup the post data
				
				if ( !isset($loop_count) )
					$loop_count = 1;
				?>
				
				<?php $tag = ( in_array($instance['post_container'], array('ol','ul')) ) ? 'li' : 'div'; ?>
				
				<!--BEGIN .hentry-->
				<<?php echo $tag; ?> id="post-<?php the_ID(); ?>" class="<?php echo join( ' ', get_post_class() ); ?>">
					
					<?php echo wl_entry_title( array('tag'=> $instance['entry_tag']) ); ?>
					
					<?php
					if ( $instance['before_content'] )
						echo wl_postmeta($instance['before_content'])
					?>
					
					<!--BEGIN .entry-content-->
					<div class="entry-content">
						<?php
						if ( $instance['post_thumbnails'] and has_post_thumbnail() ) {
							echo '<p><a href="'. get_permalink() .'">';
							the_post_thumbnail();
							echo '</a></p>';
						}
						
						echo wl_the_content( $instance['more_text'], $instance['content_length'] );
						
						if ( $instance['page_links'] )
							wp_link_pages( array( 'before' => '<div class="paged-links"><span>'. __( 'Pages:', 'wordpress-loop' ) .'</span>', 'after' => '</div>', 'next_or_number' => 'number' ) );
						?>
					<!--END .entry-content-->
					</div>
					
					<?php
					if ( $instance['after_content'] )
						echo wl_postmeta($instance['after_content']);
					?>
					
					<?php do_action( 'the_loop' ); ?>
										
				<!--END .hentry-->
				</<?php echo $tag; ?>>
				
				<?php do_action( "in_the_loop_$loop_count" );
				$loop_count++;
			}
			
			if ( $instance['pagination'] )
				wl_pagniation( $instance['next_link'], $instance['prev_link'] );
			
			do_action( 'after_loop' );
			
		} else {
			
			echo wp_filter_post_kses( $instance['loop_error_msg'] );
			
			do_action( 'loop_404' );
		}
		
		if ( in_array($instance['post_container'], array( 'ul', 'ol' )) )
			echo "</{$instance['post_container']}>\n";
		
		if ( $instance['use_default_styles'] )
			echo $after_widget;
		
		echo '</div>';
	}
	
	function update( $new_instance, $old_instance ) {
		// sanitize options
		$instance = $old_instance;
		$instance = $new_instance;
		
		// checkboxes
		$instance['wp_query'] = $new_instance['wp_query'] ? true : false;
		$instance['use_default_styles'] = $new_instance['use_default_styles'] ? true : false;
		$instance['h2'] = $new_instance['h2'] ? true : false;
		$instance['caller_get_posts'] = $new_instance['caller_get_posts'] ? true : false;
		$instance['pagination'] = $new_instance['pagination'] ? true : false;
		$instance['page_links'] = $new_instance['page_links'] ? true : false;
		$instance['post_thumbnails'] = $new_instance['post_thumbnails'] ? true : false;
		$instance['hard_crop_switch'] = $new_instance['hard_crop_switch'] ? true : false;
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		// update $instance with $new_instance;
		return $instance;
	}
	
	function form( $instance ) {
		global $wpdb;
		
		// grab the post_date column and get it ready for dissection!
		$results = $wpdb->get_results( "SELECT post_date FROM $wpdb->posts" );
		$total = count( $results ) - 1;
		$first_post = str_split( $results[0]->post_date, 4 );
		$last_post = str_split( $results[$total]->post_date, 4 );
		$years[$first_post[0]] = $first_post[0];
		for ( $i = $first_post[0]; $i <= $last_post[0]; $i++ )
			$years[$i] = $i;
		// 7 lines just to get the first/last published post year? wow... [sic]
		
		$m = range( 1, 12 );
		for ( $i = 1; $i <= $m[11]; $i++ ) {
			$key = (string) $i;
			$months[$key] = $key;
		}
		
		$w = range( 1, 53 );
		for ( $i = 1; $i <= $w[52]; $i++ ) {
			$key = (string) $i;
			$weeks[$key] = $key;
		}
			
		$d = range( 1, 31 );
		for ( $i = 1; $i <= $d[30]; $i++ ) {
			$key = (string) $i;
			$days[$key] = $key;
		}
		
		$pmt = get_available_post_mime_types();
		foreach ( $pmt as $post_mime_type ) {
			if ( stripos( $post_mime_type, '/' ) )
				$post_mime_types[$post_mime_type] = $post_mime_type;
		}
		
		foreach ( get_post_types( array('exclude_from_search' => false) ) as $post_type ) {
			$taxonomies[] = get_object_taxonomies( $post_type );
			$post_types[$post_type] = ucwords($post_type);
		}
		$taxonomies = $taxonomies[0];
						
		$_users = get_users_of_blog();
		foreach ( $_users as $user )
			$ppl[$user->user_id] = $user->display_name;
		
		$post_type_statuses = array( 'draft' => 'Draft', 'pending' => 'Pending', 'future' => 'Future', 'private' => 'Private', 'publish' => 'Publish', 'trash' => 'Trash' );
		$tags = array( 'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6', 'p' => 'p', 'span' => 'span', 'div' => 'div' );
		
		/***///***/
		
		$defaults = array( 'title' => '', 'post_container' => 'div', 'wp_query' => false, 'use_default_styles' => true, 'page_links' => true, 'h2' => true,
			'post_type' => 'post', 'post_status' => 'publish', 'users' => 'all', 'order' => 'DESC', 'orderby' => 'date', 'posts_per_page' => get_site_option('posts_per_page'), 'comments_per_page' => get_site_option('comments_per_page'),
			'offset' => '0', 'caller_get_posts' => false, 'loop_error_msg' => __( 'Sorry but we couldn\'t find what you were looking for :(.', 'wordpress-loop' ),
			'next_link' => __( '&laquo; Older Entries', 'wordpress-loop' ), 'prev_link' => __( 'Newer Entries &raquo;', 'wordpress-loop' ),
			'headline_tag' => 'h1', 'entry_tag' => 'h2', 'more_text' => 'Read More', 'content_length' => '-1',
			'before_content' => 'Posted by [author] on [date] [comments before="| "] [edit before="| "]', 'after_content' => '[tax]',
			'enable_images' => false, 'thumbnail_size_w' => get_option('thumbnail_size_w'), 'thumbnail_size_h' => get_option('thumbnail_size_h')
		);
		$instance = wp_parse_args( $instance, $defaults );
		?>
		
		<div class="widget-wp-loop" style="margin-left:0px;">
			<?php
			wl_form_text( $this->get_field_id( 'title' ), $this->get_field_name( 'title' ), $instance['title'], '<code>title</code>' );
			wl_form_checkbox( $this->get_field_id( 'wp_query' ), $this->get_field_name( 'wp_query' ), $instance['wp_query'], __( 'Use <code>$wp_query</code>', 'wordpress-loop' ) );
			wl_form_smalltext( $this->get_field_id( 'posts_per_page' ), $this->get_field_name( 'posts_per_page' ), $instance['posts_per_page'], '<code>posts_per_page</code>' );
			wl_form_multi_select( $this->get_field_id( 'post_type' ), $this->get_field_name( 'post_type' ), $post_types, $instance['post_type'], '<code>post_type</code>' );
			wl_form_multi_select( $this->get_field_id( 'post_status' ), $this->get_field_name( 'post_status' ), $post_type_statuses, $instance['post_status'], '<code>post_status</code>' );
			wl_form_multi_select( $this->get_field_id( 'users' ), $this->get_field_name( 'users' ), $ppl, $instance['users'], '<code>users</code>' );
			?>
		</div>
		
		<div class="widget-wp-loop" style="width:165px;">
			<?php
			wl_form_select( $this->get_field_id( 'order' ), $this->get_field_name( 'order' ), array( 'ASC' => 'ASC', 'DESC' => 'DESC' ), $instance['order'], '<code>order</code>' );
			wl_form_select( $this->get_field_id( 'orderby' ), $this->get_field_name( 'orderby' ), array( 'author' => 'Author', 'date' => 'Date', 'title' => 'Title', 'modified' => 'Modified', 'menu_order' => 'Menu Order', 'parent' => 'Parent', 'ID' => 'ID', 'rand' => 'Random', 'none' => 'None', 'comment_count' => 'Comment Count' ), $instance['orderby'], '<code>orderby</code>' );
			wl_form_select( $this->get_field_id( 'post_mime_type' ), $this->get_field_name( 'post_mime_type' ), $post_mime_types, $instance['post_mime_type'], '<code>post_mime_type</code>' );
//			wl_form_smalltext( $this->get_field_id( 'comments_per_page' ), $this->get_field_name( 'comments_per_page' ), $instance['comments_per_page'], '<code>comments_per_page</code>' );
			wl_form_smalltext( $this->get_field_id( 'offset' ), $this->get_field_name( 'offset' ), $instance['offset'], '<code>offset</code>' );
			wl_form_checkbox( $this->get_field_id( 'caller_get_posts' ), $this->get_field_name( 'caller_get_posts' ), $instance['caller_get_posts'], __( 'Enable sticky post', 'wordpress-loop' ) );
			wl_form_checkbox( $this->get_field_id( 'page_links' ), $this->get_field_name( 'page_links' ), $instance['page_links'], __( 'Enable page links', 'wordpress-loop' ) );
			
			wl_form_checkbox( $this->get_field_id( 'post_thumbnails' ), $this->get_field_name( 'post_thumbnails' ), $instance['post_thumbnails'], __( 'Enable post thumbnails', 'wordpress-loop' ) );
			wl_form_smalltext( $this->get_field_id( 'thumbnail_size_w' ), $this->get_field_name( 'thumbnail_size_w' ), $instance['thumbnail_size_w'], '<code>thumbnail_size_w</code>' );
			wl_form_smalltext( $this->get_field_id( 'thumbnail_size_h' ), $this->get_field_name( 'thumbnail_size_h' ), $instance['thumbnail_size_h'], '<code>thumbnail_size_h</code>' );
			wl_form_checkbox( $this->get_field_id( 'hard_crop_switch' ), $this->get_field_name( 'hard_crop_switch' ), $instance['hard_crop_switch'], __( 'Hard crop images', 'wordpress-loop' ) );
			
			wl_form_checkbox( $this->get_field_id( 'pagination' ), $this->get_field_name( 'pagination' ), $instance['pagination'], __( 'Enable pagination', 'wordpress-loop' ) );
			wl_form_smalltext( $this->get_field_id( 'next_link' ), $this->get_field_name( 'next_link' ), $instance['next_link'], '<code>next_link</code>' );
			wl_form_smalltext( $this->get_field_id( 'prev_link' ), $this->get_field_name( 'prev_link' ), $instance['prev_link'], '<code>prev_link</code>' );
			?>
		</div>
		
		<div class="widget-wp-loop">
			<?php
			// Taxonomies
			if ( !empty($taxonomies) ) {
				foreach ( $taxonomies as $tax_name ) {
					$terms = get_terms($tax_name);
					foreach ( $terms as $term ) {						
						$jm[$tax_name][$term->term_id] = $term->name;
					}
					//  WP_Query currently doesn't handle mutiple custom taxonomies, so it'll be limited to cats and tags until a fix is applied
					if ( 'category' == $tax_name OR 'post_tag' == $tax_name ) {
						wl_form_multi_select( $this->get_field_id( $tax_name ), $this->get_field_name( $tax_name ), $jm[$tax_name], $instance[$tax_name], "<code>{$tax_name}</code>" );
					} else {
						wl_form_select( $this->get_field_id( $tax_name ), $this->get_field_name( $tax_name ), $jm[$tax_name], $instance[$tax_name], "<code>{$tax_name}</code>" );
					}
				}
			}
			?>
		</div>
		
		<div class="widget-wp-loop" style="border-right: 2px #e6e6e6 solid;padding-right:7px;">
			<?php
			wl_form_text( $this->get_field_id( 'meta_key' ), $this->get_field_name( 'meta_key' ), $instance['meta_key'], '<code>meta_key</code>' );
			wl_form_text( $this->get_field_id( 'meta_value' ), $this->get_field_name( 'meta_value' ), $instance['meta_value'], '<code>meta_value</code>' );
			wl_form_select( $this->get_field_id( 'meta_compare' ), $this->get_field_name( 'meta_compare' ), array( '>' => '>', '<' => '<', '=' => '=', '!=' => '!=', '>=' => '>=', '<=' => '<=' ), $instance['meta_compare'], '<code>meta_compare</code>' );			
			wl_form_select( $this->get_field_id( 'year' ), $this->get_field_name( 'year' ), $years, $instance['year'], '<code>year</code>' );
			wl_form_select( $this->get_field_id( 'monthnum' ), $this->get_field_name( 'monthnum' ), $months, $instance['monthnum'], '<code>month</code>' );
			wl_form_select( $this->get_field_id( 'w' ), $this->get_field_name( 'w' ), $weeks, $instance['w'], '<code>week</code>' );
			wl_form_select( $this->get_field_id( 'day' ), $this->get_field_name( 'day' ), $days, $instance['day'], '<code>day</code>' );
			wl_form_text( $this->get_field_id( 'custom_query_args' ), $this->get_field_name( 'custom_query_args' ), $instance['custom_query_args'], '<code>custom_query_args</code>' );
			?>
		</div>
		
		<div class="widget-wp-loop" style="width:165px;margin-left:7px;">
			<?php
			wl_form_checkbox( $this->get_field_id( 'h2' ), $this->get_field_name( 'h2' ), $instance['h2'], __( 'Use title as a headline', 'wordpress-loop' ) );
			wl_form_select_n( $this->get_field_id( 'headline_tag' ), $this->get_field_name( 'headline_tag' ), $tags, $instance['headline_tag'], '<code>headline markup</code>' );
			wl_form_select_n( $this->get_field_id( 'post_container' ), $this->get_field_name( 'post_container' ), array( 'div' => 'div', 'ol' => 'ol', 'ul' => 'ul' ), $instance['post_container'], '<code>entry container markup</code>' );
			wl_form_select_n( $this->get_field_id( 'entry_tag' ), $this->get_field_name( 'entry_tag' ), $tags, $instance['entry_tag'], '<code>entry titles markup</code>' );
			
			wl_form_text( $this->get_field_id( 'before_content' ), $this->get_field_name( 'before_content' ), $instance['before_content'], '<code>before_content</code>' );
			wl_form_smalltext( $this->get_field_id( 'content_length' ), $this->get_field_name( 'content_length' ), $instance['content_length'], '<code>content_length</code>' );
			wl_form_text( $this->get_field_id( 'more_text' ), $this->get_field_name( 'more_text' ), $instance['more_text'], '<code>more_text</code>' );
			wl_form_text( $this->get_field_id( 'after_content' ), $this->get_field_name( 'after_content' ), $instance['after_content'], '<code>after_content</code>' );
			wl_form_bigtext( $this->get_field_id( 'loop_error_msg' ), $this->get_field_name( 'loop_error_msg' ), $instance['loop_error_msg'], '<code>loop_error_msg</code>' );
			wl_form_checkbox( $this->get_field_id( 'use_default_styles' ), $this->get_field_name( 'use_default_styles' ), $instance['use_default_styles'], __( 'Use default widget styles', 'wordpress-loop' ) );
			?>
		</div>
		<?php
	}
}
?>