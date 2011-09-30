<?php
/*
	Section: Boxes
	Author: PageLines
	Author URI: http://www.pagelines.com
	Description: Creates boxes and box layouts
	Class Name: PageLinesBoxes
	Workswith: templates, main, header, morefoot
	Cloning: true
	Edition: pro
*/

class PageLinesBoxes extends PageLinesSection {

	var $taxID = 'box-sets';
	var $ptID = 'boxes';

	function section_persistent(){
		
		$this->post_type_setup();
		
		$this->post_meta_setup();
		
	}
	
	function post_type_setup(){
			$args = array(
					'label' 			=> __('Boxes', 'pagelines'),  
					'singular_label' 	=> __('Box', 'pagelines'),
					'description' 		=> 'For creating boxes in box type layouts.',
					'menu_icon'			=> $this->icon
				);
			$taxonomies = array(
				$this->taxID => array(	
						"label" => __('Box Sets', 'pagelines'), 
						"singular_label" => __('Box Set', 'pagelines'), 
					)
			);
			$columns = array(
				"cb"	 		=> "<input type=\"checkbox\" />",
				"title" 		=> "Title",
				"bdescription" 	=> "Text",
				"bmedia" 		=> "Media",
				$this->taxID 	=> "Box Sets"
			);
		
			$this->post_type = new PageLinesPostType( $this->ptID, $args, $taxonomies, $columns, array(&$this, 'column_display'));
		
				/* Set default posts if none are present */
				
				$this->post_type->set_default_posts( 'pagelines_default_boxes', $this);
	}

	function post_meta_setup(){
		
			$type_meta_array = array(
				'the_box_icon' 		=> array(
						'version' 	=> 'pro',
						'type' 		=> 'image_upload',					
						'title' 	=> 'Box Image',
						'shortexp' 	=> 'Upload an image for the box.',
						'exp'		=> 'Depending on your settings this image will be used as an icon, or splash image; so desired size may vary.'
					), 
				'the_box_icon_link'		=> array(
						'version' => 'pro',
						'type' => 'text',					
						'title' => 'Box Link (Optional)',
						'shortexp' => 'Make the box image and title clickable by adding a link here (optional)...'
					)
			);

			$post_types = array($this->id); 
			
			$type_metapanel_settings = array(
					'id' 		=> 'boxes-metapanel',
					'name' 		=> THEMENAME." Box Options",
					'posttype' 	=> $post_types,
				);
			
			global $boxes_meta_panel;
			
			$boxes_meta_panel =  new PageLinesMetaPanel( $type_metapanel_settings );
			
			$type_metatab_settings = array(
				'id' 		=> 'boxes-type-metatab',
				'name' 		=> "Box Setup Options",
				'icon' 		=> $this->icon,
			);

			$boxes_meta_panel->register_tab( $type_metatab_settings, $type_meta_array );
		
	}

	function section_optionator( $settings ){
		
		$settings = wp_parse_args($settings, $this->optionator_default);
		
			$metatab_array = array(
					'box_set' => array(
						'version' 		=> 'pro',
						'default'		=> 'default-boxes',
						'type' 			=> 'select_taxonomy',
						'taxonomy_id'	=> $this->taxID,				
						'title'		 	=> 'Select Box Set To Show',
						'shortexp' 			=> 'If you are using the box section, select the box set you would like to show on this page.'
					), 
					'box_col_number' => array(
						'type' 			=> 'count_select',
						'default'		=> '3',
						'count_number'	=> '5', 
						'count_start'	=> '1',
						'inputlabel' 	=> 'Number of Feature Box Columns',
						'title' 		=> 'Box Columns',
						'inputlabel' 		=> "Select the number of columns to show boxes in.",
						'shortexp' 			=> "The number you select here will be the number of boxes listed in a row on a page.",
						'exp'				=> "Note: This won't work on the blog page (use the global option)."
					), 
					'box_thumb_type' => array(
						'version' 	=> 'pro',
						'type' 		=> 'select',
						'default'	=> 'inline_thumbs',
						'selectvalues'	=> array(
								'inline_thumbs'	=> array("name" => "Image At Left"),
								'top_thumbs'	=> array("name" => "Image On Top"), 
								'only_thumbs'	=> array("name" => "Only The Image, No Text")
							), 
						'title' => 'Box Thumb Style',				
						'shortexp' => 'Choose between thumbs on left and thumbs on top of boxes.',
						
					),
					'box_thumb_size' => array(
						'version'		=> 'pro',
						'default'		=> '64',
						'type' 			=> 'text',
						'size'			=> 'small',
						'title' 		=> 'Box Icon Size (in Pixels)',
						'inputlabel' 		=> "Enter the icon size in pixels",
						'shortexp' 			=> "Select the default icon size in pixels, set the images when creating new boxes.",
					),
					'box_items' => array(
						'version'		=> 'pro',
						'default'		=> '6',
						'type' 			=> 'text',
						'size'			=> 'small',
						'inputlabel' 	=> 'Maximum Boxes To Show On Page',
						'title' 		=> 'Max Number of Boxes',
						'shortexp' 			=> "Select the max number of boxes to show on this page (overrides default).",
					),
				);

			$metatab_settings = array(
					'id' 		=> 'fboxes_meta',
					'name' 		=> "Boxes Section",
					'icon' 		=> $this->icon, 
					'clone_id'	=> $settings['clone_id'], 
					'active'	=> $settings['active']
				);

			register_metatab($metatab_settings, $metatab_array);
	}

   function section_template( $clone_id = null ) {    

		global $post; 
		global $pagelines_ID;
		
		// Option Settings
			$oset = array('post_id' => $pagelines_ID, 'clone_id' => $clone_id);
		
		// Options
			$box_columns = ( ploption( 'box_col_number', $oset) ) ? ploption( 'box_col_number', $oset) : 3; 
			$box_set = ( ploption( 'box_set', $oset ) ) ? ploption( 'box_set', $oset ) : null;
			$box_limit = ploption( 'box_items', $oset );
			$thumb_type = ( ploption( 'box_thumb_type', $oset) ) ? ploption( 'box_thumb_type', $oset) : 'inline_thumbs';	
			$thumb_size = ( ploption('box_thumb_size', $oset) ) ? ploption('box_thumb_size', $oset) : 64;
				
		// Actions	
			$b = $this->load_pagelines_boxes($box_set, $box_limit); 
			$this->draw_boxes($b, $box_columns, $box_set, $thumb_type, $thumb_size);
		
	}

	function draw_boxes($b, $perline = 3, $class = "", $thumb_type = 'inline_thumbs', $thumb_size = ''){ 
		global $post;
		global $pagelines_ID;
	
		if ( post_password_required() )
			return;

		$post_count = count($b);
		$current_box = 1;
		$row_count = $perline;
	
		if(!empty($b)){
?>
			<div class="pprow <?php echo $class;?> fboxes fix">
	<?php 	foreach($b as $bpost):
				setup_postdata($bpost); 
	 			$box_link = get_post_meta($bpost->ID, 'the_box_icon_link', true);
				$box_icon = get_post_meta($bpost->ID, 'the_box_icon', true);
			
				$box_row_start = ( $row_count % $perline == 0 ) ? true : false;
				$box_row_end = ( ( $row_count + 1 ) % $perline == 0 || $current_box == $post_count ) ? true : false;
				$grid_class = ($box_row_end) ? 'pplast pp'.$perline : 'pp'.$perline;
			
	?>
				<section id="<?php echo 'fbox_'.$bpost->ID;?>" class="<?php echo $grid_class;?> fbox">
					<div class="media dcol-pad <?php echo $thumb_type;?>">	
					
						<?php if($box_icon)
								echo self::_get_box_image( $bpost, $box_icon, $box_link, $thumb_size, $thumb_type); 
							
							if($thumb_type != 'only_thumbs'): ?>		
								<div class="fboxinfo fix bd">
									<div class="fboxtitle">
										<h3>
		<?php 							if($box_link) 
											printf('<a href="%s">%s</a>', $box_link, $bpost->post_title );
										else 
											echo do_shortcode($bpost->post_title); ?>
										</h3>
									</div>
									<div class="fboxtext">
										<?php echo blink_edit( $bpost->ID ); ?>
										<?php echo the_content($bpost->post_content); ?>
									</div>
								</div>
								<?php pagelines_register_hook( 'pagelines_box_inside_bottom', $this->id ); // Hook ?>
							<?php endif;?>
					</div>
				</section>
	<?php 
				$row_count++;
				$current_box++; 
			endforeach;	?>
			</div>
<?php 

		} else
			echo setup_section_notify($this, 'Select box set to activate');
	
	}


	function load_pagelines_boxes($set = null, $limit = null){
		$query = array();
		
		$query['post_type'] = 'boxes'; 
		$query['orderby'] 	= 'ID'; 
		
		if(isset($set)) 
			$query[ $this->taxID ] = $set; 
			
		if(isset($limit)) 
			$query['showposts'] = $limit; 

		$q = new WP_Query($query);
		
		if(is_array($q->posts)) 
			return $q->posts;
		else 
			return array();
	
	}
	

	function _get_box_image( $bpost, $box_icon, $box_link = false, $box_thumb_size = 65, $thumb_type){
			global $pagelines_ID;
			
			if($thumb_type == 'inline_thumbs'){
				$image_style = 'width: 100%';
				$wrapper_style = sprintf('width: 22%%; max-width:%dpx', $box_thumb_size);
				$wrapper_class = 'fboxgraphic img';
			} else {
				$image_style = sprintf('width: 100%%; max-width:%dpx', $box_thumb_size);
				$wrapper_style = '';
				$wrapper_class = 'fboxgraphic';
			}
			
			// Make the image's tag with url
			$image_tag = sprintf('<img src="%s" alt="%s" style="%s" />', $box_icon, esc_html($bpost->post_title), $image_style);
			
			// If link for box is set, add it
			if( $box_link ) 
				$image_output = sprintf('<a href="%s" title="%s">%s</a>', $box_link, esc_html($bpost->post_title), $image_tag );
			else 
				$image_output = $image_tag;
			
			$wrapper = sprintf('<div class="%s" style="%s">%s</div>', $wrapper_class, $wrapper_style, $image_output );
			
			// Filter output
			return apply_filters('pl_box_image', $wrapper, $bpost->ID);
	}

	
		function pagelines_default_boxes($post_type){

			$d = array_reverse( $this->get_default_fboxes() );

			foreach($d as $dp){
				// Create post object
				$default_post = array();
				$default_post['post_title'] = $dp['title'];
				$default_post['post_content'] = $dp['text'];
				$default_post['post_type'] = $post_type;
				$default_post['post_status'] = 'publish';
				if ( defined( 'ICL_LANGUAGE_CODE' ) )
					$default_post['icl_post_language'] = ICL_LANGUAGE_CODE;
				$newPostID = wp_insert_post( $default_post );

				if(isset($dp['media']))
					update_post_meta($newPostID, 'the_box_icon', $dp['media']);

				wp_set_object_terms($newPostID, 'default-boxes', $this->taxID );

				// Add other default sets, if applicable.
				if(isset($dp['set']))
					wp_set_object_terms($newPostID, $dp['set'], $this->taxID, true);

			}
		}

		function get_default_fboxes(){
			$default_boxes[] = array(
			        				'title' => 'Drag&amp;Drop Control',
					        		'text' 	=> 'Control the structure of your site using drag and drop functionality. Pro web design has never been easier.',
									'media' => $this->base_url.'/images/fbox3.png'
			    				);

			$default_boxes[] = array(
			        				'title' => 'PageLines Framework',
					        		'text' 	=> 'The world\'s first ever drag-and-drop framework designed for professional websites. Build beautiful sites faster.',
									'media' => $this->base_url.'/images/fbox2.png'
			    				);

			$default_boxes[] = array(
			        				'title'	=> 'Add-On Marketplace',
			        				'text' 	=> 'Load up your own sections, themes and plugins using PageLines\' one of a kind extension marketplace.', 
									'media' => $this->base_url.'/images/fbox1.png'
			    				);

			return apply_filters('pagelines_default_boxes', $default_boxes);
		}
	
	function column_display($column){
		global $post;

		switch ($column){
			case "bdescription":
				the_excerpt();
				break;
			case "bmedia":
				if(get_post_meta($post->ID, 'the_box_icon', true )){

					echo '<img src="'.get_post_meta($post->ID, 'the_box_icon', true ).'" style="max-width: 80px; margin: 10px; border: 1px solid #ccc; padding: 5px; background: #fff" />';	
				}

				break;
			case $this->taxID:
				echo get_the_term_list($post->ID, 'box-sets', '', ', ','');
				break;
		}
	}

}

