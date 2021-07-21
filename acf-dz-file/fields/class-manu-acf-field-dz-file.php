<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('MANU_acf_field_dz_file') ) :


class MANU_acf_field_dz_file extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct( $settings ) {
		
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		
		$this->name = 'dz_file';
		
		
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		
		$this->label = __('Dropzone File Upload', 'irs_frs');
		
		
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		
		$this->category = 'content';
		
		
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		
		$this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'medium',
			'library'		=> 'all',
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> ''
		);
		
		// filters
		add_filter('get_media_item_args', array($this, 'get_media_item_args'));

		//ajax actions
		add_action( 'wp_ajax_handle_to_get_attachment', array($this, 'frs_handle_to_get_attachment') );
		add_action( 'wp_ajax_handle_dropped_media', array($this, 'frs_handle_dropped_media') );
		add_action( 'wp_ajax_handle_to_medialibrary', array($this, 'frs_handle_to_medialibrary' ));
		add_action( 'wp_ajax_handle_save_media', array($this, 'frs_handle_save_media' ));
		add_action( 'wp_ajax_handle_delete_media', array($this, 'frs_handle_delete_media' ));
		

		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('dz_file', 'error');
		*/
		
		$this->l10n = array(
			'error'	=> __('Error! Please enter a proper value', 'irs_frs'),
		);
		
		
		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/
		
		$this->settings = $settings;
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/
		
			// clear numeric settings
			$clear = array(
				'min_width',
				'min_height',
				'min_size',
				'max_width',
				'max_height',
				'max_size'
			);
			
			foreach( $clear as $k ) {
				
				if( empty($field[$k]) ) {
					
					$field[$k] = '';
					
				}
				
			}
			
			
			// return_format
			/* acf_render_field_setting( $field, array(
				'label'			=> __('Return Format','acf'),
				'instructions'	=> '',
				'type'			=> 'radio',
				'name'			=> 'return_format',
				'layout'		=> 'horizontal',
				'choices'		=> array(
					'array'			=> __("Image Array",'acf'),
					'url'			=> __("Image URL",'acf'),
					'id'			=> __("Image ID",'acf')
				)
			)); */
			
			acf_render_field_setting( $field, array(
				'label'			=> __('Upload File Type','acf'),
				'instructions'	=> '',
				'type'			=> 'radio',
				'name'			=> 'upload_type',
				'layout'		=> 'horizontal',
				'choices'		=> array(
					'dzimage'			=> __("Image",'acf'),
					'dzvideo'			=> __("Video",'acf'),
					'dzavatar'			=> __("User Avatar", 'acf')
					
				)
			));
			
			// preview_size
			acf_render_field_setting( $field, array(
				'label'			=> __('Preview Size','acf'),
				'instructions'	=> '',
				'type'			=> 'select',
				'name'			=> 'preview_size',
				'choices'		=> acf_get_image_sizes()
			));
			
			
			// min
			acf_render_field_setting( $field, array(
				'label'			=> __('Minimum','acf'),
				'instructions'	=> __('Restrict which images can be uploaded','acf'),
				'type'			=> 'text',
				'name'			=> 'min_width',
				'prepend'		=> __('Width', 'acf'),
				'append'		=> 'px',
			));
			
			acf_render_field_setting( $field, array(
				'label'			=> '',
				'type'			=> 'text',
				'name'			=> 'min_height',
				'prepend'		=> __('Height', 'acf'),
				'append'		=> 'px',
				'_append' 		=> 'min_width'
			));
			
			acf_render_field_setting( $field, array(
				'label'			=> '',
				'type'			=> 'text',
				'name'			=> 'min_size',
				'prepend'		=> __('File size', 'acf'),
				'append'		=> 'MB',
				'_append' 		=> 'min_width'
			));	
			
			
			// max
			acf_render_field_setting( $field, array(
				'label'			=> __('Maximum','acf'),
				'instructions'	=> __('Restrict which images can be uploaded','acf'),
				'type'			=> 'text',
				'name'			=> 'max_width',
				'prepend'		=> __('Width', 'acf'),
				'append'		=> 'px',
			));
			
			acf_render_field_setting( $field, array(
				'label'			=> '',
				'type'			=> 'text',
				'name'			=> 'max_height',
				'prepend'		=> __('Height', 'acf'),
				'append'		=> 'px',
				'_append' 		=> 'max_width'
			));
			
			acf_render_field_setting( $field, array(
				'label'			=> '',
				'type'			=> 'text',
				'name'			=> 'max_size',
				'prepend'		=> __('File size', 'acf'),
				'append'		=> 'MB',
				'_append' 		=> 'max_width'
			));	
			
			
			// allowed type
			acf_render_field_setting( $field, array(
				'label'			=> __('Allowed file types','acf'),
				'instructions'	=> __('Comma separated list. Leave blank for all types','acf'),
				'type'			=> 'text',
				'name'			=> 'mime_types',
			));

	}
	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	### DropzoneJS
	
	function frs_handle_to_get_attachment($field)	{
		if( isset($_REQUEST['post']) ){
		$postId = absint( $_REQUEST['post'] );
		error_log(print_r($_REQUEST['post'],true));
		
	if ($_REQUEST['type'] == 'image') {
		$imgID = get_post_thumbnail_id($postId);
		error_log(print_r($imgID,true));
		$imgMeta = wp_get_attachment_metadata($imgID);
		$imgPath = get_attached_file( $imgID); 

		if( $imgID ) {
		$imgName = basename(get_the_post_thumbnail_url($postId));
		$imgSrc = get_the_post_thumbnail_url($postId);
		$imgSize = filesize($imgPath);
		$response = json_encode(array('type' => 'image', 'id' => $imgID,'status' => 'ok', 'name' => $imgName, 'src' => $imgSrc, 'size' => $imgSize ));
		} elseif (!$imgID && $avaID[0]) {
			$imgName = basename(wp_get_attachment_url($avaID[0]));
			$imgSrc = wp_get_attachment_url($avaID[0]);
			$imgPath = get_attached_file($avaID[0]);
			$imgSize = filesize($imgSrc);
			$response = json_encode(array('type' => 'image', 'id' => $avaID[0],'status' => 'ok', 'name' => $imgName, 'src' => $imgSrc, 'size' => $imgSize ));	
	} else {
		$response = json_encode(array('status' => 'no image'));	
		}
	} 
	if ($_REQUEST['type'] == 'video') {
		$vidID = get_field('frs_video_upload',$postId);
		if($vidID) {
		$vidName = basename(wp_get_attachment_url($vidID));
		$vidPath = get_attached_file( $vidID); 
		$vidSrc = wp_get_attachment_url($vidID);
		$vidSize = filesize($vidPath);
		$response = json_encode(array('type' => 'video', 'id' => $vidID, 'status' => 'ok', 'name' => $vidName, 'src' => $vidSrc, 'size' => $vidSize ));
		} else {
		$response = json_encode(array('status' => 'no video'));	
		}	

	}

		echo $response;
		 
		error_log(print_r($response, TRUE));
					
		}
		wp_die();
	}

	function frs_handle_dropped_media() {
			  $chunksFolder = ABSPATH . 'wp-content/uploads/dzupl/chunks';
				$partIndex = (int)$_POST['dzchunkindex'];
				$uuid = $_POST['dzuuid'];
				
	
				$targetFolder = $chunksFolder.DIRECTORY_SEPARATOR.$uuid;
	
				if (!file_exists($targetFolder)){
					mkdir($targetFolder, 0755, true);
				}
	
				$target = $targetFolder.'/'.$partIndex;
				
				$success = move_uploaded_file($_FILES['file']['tmp_name'], $target);
				
			echo json_encode(array("success" => true, "uuid" => $uuid, "chunk" => $partIndex));   
			
			wp_die();          
	}	
	
	function frs_handle_to_medialibrary() {
				
				$datedir = date('Y').DIRECTORY_SEPARATOR.date('m');
				$chunksFolder = ABSPATH . 'wp-content/uploads/dzupl/chunks';
				 $uploadDirectory = ABSPATH . 'wp-content/uploads/'.$datedir;
				$totalParts = (int)$_POST['totalchunks'];
				$uuid = $_POST['uuid'];
				$name = $_POST['name'];
				$targetFolder = $chunksFolder.DIRECTORY_SEPARATOR.$uuid;
	
				$targetPath = join(DIRECTORY_SEPARATOR, array($uploadDirectory, $name));
	
			if (file_exists($targetPath)){
				$newname = substr($uuid,0,4).'_'.$name;
				$targetPath = join(DIRECTORY_SEPARATOR, array($uploadDirectory, $newname));
				
			} else {
				mkdir(dirname($uploadDirectory), 0755, true);
			}
			
			$target = fopen($targetPath, 'wb');
	
			for ($i=0; $i<$totalParts; $i++){
				$chunk = fopen($targetFolder.DIRECTORY_SEPARATOR.$i, "rb");
				stream_copy_to_stream($chunk, $target);
				fclose($chunk);
			}
	
			// Success
			fclose($target);
	
			for ($i=0; $i<$totalParts; $i++){
				unlink($targetFolder.DIRECTORY_SEPARATOR.$i);
			}
	
			rmdir($targetFolder);
			
			$filename = $targetPath;
			
			$filetype = wp_check_filetype( basename( $filename ), null );
			// start fix rotation
				try {
	
					$exif = @exif_read_data($filename);
					if($exif['Orientation']){  
					$orientation = $exif['Orientation'];
					
					if (isset($orientation) && $orientation != 1){
						switch ($orientation) {
							case 3:
							$deg = 180;
							break;
							case 6:
							$deg = 270;
							break;
							case 8:
							$deg = 90;
							break;
						}
	
						if ($deg) {
	
							// If png
							if ($filetype == "png") {
								$img_new = imagecreatefrompng($filename);
								$img_new = imagerotate($img_new, $deg, 0);
	
								// Save rotated image
								imagepng($img_new,$filename);
							}else {
								$img_new = imagecreatefromjpeg($filename);
								$img_new = imagerotate($img_new, $deg, 0);
	
								// Save rotated image
								imagejpeg($img_new,$filename,80);
							}
						}
						//error_log('image chaged:'.$filename);
						}
					}
	
				} 
				catch (Exception $e) {
					error_log('error: '.$e);
				}
		 
			// end fix rotation
		$wp_upload_dir = wp_upload_dir();
		$attachment = array(
		'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit'
		);
		$parent_post_id = '';
		$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
	
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		$attach_url = wp_get_attachment_url($attach_id);
		$data = json_encode(array('id' => $attach_id, 'url' => $attach_url));
		echo $data; //send data id to ajax
		error_log(print_r($data, TRUE));
		
		wp_die();
	}
	
	  
	function frs_handle_delete_media(){
			
			if( isset($_REQUEST['media_id']) ):
			$post_id = absint( $_REQUEST['media_id'] );
	
			
			$status = wp_delete_attachment($post_id, true);
		
			if( false === $status ) : echo json_encode(array('status' => 'FAILED'));
			else : echo json_encode(array('status' => 'OK'));
			endif;
				
			else: echo json_encode(array('status' => 'NO ID'));
			endif;
	   
		wp_die();
	}

	function render_field( $field ) {
		
		
		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/
		
		echo '<pre>';
			//print_r( $field );
		echo '</pre>';
  
		// Elements and attributes.
		$field['return_format'] = 'id';
		$value = '';
		$div_attrs = array(
			'class'				=> 'acf-dzfile-uploader dropzone '.$field['upload_type'],
			'data-preview_size'	=> $field['preview_size'],
			'data-mime_types'	=> $field['mime_types'],
			
		);
		
		// Add "preview size" max width and height style.
		// Apply max-width to wrap, and max-height to img for max compatibility with field widths.
		$size = acf_get_image_size( $field['preview_size'] );
		$size_w = $size['width'] ? $size['width'] . 'px' : '100%';
		$size_h = $size['height'] ? $size['height'] . 'px' : '100%';
		$img_attrs['style'] = sprintf( 'max-height: %s;', $size_h );

		// Render HTML.
		?>
	<div <?php echo acf_esc_attrs( $div_attrs ); ?>></div>
	<?php acf_hidden_input(array( 
		'name' => $field['name'],
		'value' => $value
	)); ?>
		<?php
	}
	
		
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	
	
	function input_admin_enqueue_scripts() {
		
		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		// register & include Dropzone.JS via CDN
		wp_enqueue_script ('frs-dropzone-js', 'https://cdn.jsdelivr.net/npm/dropzone@5.9.2/dist/min/dropzone.min.js', '', '',false);
		wp_enqueue_script ('frs-croperjs-js', "https://unpkg.com/cropperjs", '', '',false);
		// register & include JS
		wp_enqueue_script('dzfile-input', "{$url}assets/js/input.js", array('acf-input'), $version,false);
		wp_enqueue_script('frs-app-js', "{$url}assets/js/app.js", array('acf-input'), $version,false);
		$_GET['pid']? $editPost = $_GET['pid'] : '' ;
		wp_localize_script( 'frs-app-js', 'frs_', array (
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'endpoint' => esc_url_raw( rest_url( '/wp/v2/media/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'postid'   => get_the_ID(),
			'editpost' => $editPost,
			'plink' => get_the_permalink(get_the_ID()),
			'upload'=>admin_url( 'admin-ajax.php?action=handle_dropped_media' ),
			'remove'=>admin_url( 'admin-ajax.php?action=handle_delete_media' ),
			'chunks'=>admin_url('admin-ajax.php?action=handle_to_medialibrary'),
			'getfile'=>admin_url('admin-ajax.php?action=handle_to_get_attachment')
		) ); 
		
		
		// register & include CSS
		wp_register_style('frs-dropzone-css', "{$url}assets/css/dropzone.css", array('acf-input'), $version);
		wp_enqueue_style('frs-dropzone-css');
		

		
	}
	
	
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_head() {
	
		
		
	}
	
	*/
	
	
	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/
   	
   	/*
   	
   	function input_form_data( $args ) {
	   	
		
	
   	}
   	
   	*/
	
	
	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_footer() {
	
		
		
	}
	
	*/
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_enqueue_scripts() {
		
	}
	
	*/

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_head() {
	
	}
	
	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	/*
	
	function load_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	*/
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	/*
	
	function update_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	*/
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// bail early if not numeric (error message)
		if( !is_numeric($value) ) return false;
		
		
		// convert to int
		$value = intval($value);
		
		
		// return
		return $value;
		
	}
		
	/*
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// apply setting
		if( $field['font_size'] > 12 ) { 
			
			// format the value
			// $value = 'something';
		
		}
		
		
		// return
		return $value;
	}
	
	*/
	
	
	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/
	

	function validate_value( $valid, $value, $field, $input ){
		
		return acf_get_field_type('file')->validate_value( $valid, $value, $field, $input );
		
	}
	


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/
	
	/*
	
	function delete_value( $post_id, $key ) {
		
		
		
	}
	
	*/
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0	
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function load_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function update_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/
	
	/*
	
	function delete_field( $field ) {
		
		
		
	}	
	
	*/
	/*
	*  get_media_item_args
	*
	*  description
	*
	*  @type	function
	*  @date	27/01/13
	*  @since	3.6.0
	*
	*  @param	$vars (array)
	*  @return	$vars
	*/
	
	function get_media_item_args( $vars ) {
	
	    $vars['send'] = true;
	    return($vars);
	    
	}
	
}


// initialize
new MANU_acf_field_dz_file( $this->settings );
//acf_register_field_type ("dz_file");

// class_exists check
endif;

?>