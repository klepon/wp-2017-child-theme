<?php
// include parent and child style
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles' );
function child_theme_enqueue_styles() {
    $parent_style = 'twentyseventeen-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'twentyseventeen-child-style', get_stylesheet_directory_uri() . '/style.css' );
}

// post.php add option for full width content / no sidebar
add_action( 'add_meta_boxes', 'child_theme_register_meta_boxes' );
function child_theme_register_meta_boxes() {
  add_meta_box(
    'child-theme-no-sidebar-meta-box',
    __( 'Remove sidebar', 'twenty-seventeen-child' ),
    'child_theme_my_display_callback',
    ['post', 'page'],
    'advanced',
    'default'
  );
}

// add_meta_boxes callback
function child_theme_my_display_callback( $post ) {
  $isChecked = get_post_meta( $post->ID, '_no-sidebar', true);
  $isChecked = $isChecked === 'true' ? 'checked="checked"' : '';
  ?>

  <input type="checkbox" id="child-theme-no-sidebar" data-id="<?php echo $post->ID; ?>" <?php echo $isChecked; ?> />
  <label for="child-theme-no-sidebar"><?php _e( 'Remove sidebar, use full width page', 'twenty-seventeen-child'); ?><em class="save_full_width_progress hidden"><br />saving, wait...</em></label>

  <?php
}

// add_meta_boxes ajax request
add_action('in_admin_footer', 'child_theme_save_full_width');
function child_theme_save_full_width () {
  ?>
  <script type='text/javascript'>
    var $ = jQuery;
    $(function() {
      function child_theme_save_full_width() {
        var el = $(this);
        $('.save_full_width_progress').removeClass('hidden');
        var data = {
          'action': 'child_theme_save_full_width_ajax',
          'pid': el.data('id'),
          'state': el.is(":checked")
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
          $('.save_full_width_progress').addClass('hidden');
          if( response != 1 ) {
            $('.save_full_width_progress').after('<br /><strong>'+ response +'</strong>');
          }
        });
      }

      $('body').off('click', '#child-theme-no-sidebar', child_theme_save_full_width);
      $('body').on('click', '#child-theme-no-sidebar', child_theme_save_full_width);
    });
  </script>
  <?php
}

// add_meta_boxes ajax resonse
add_action( 'wp_ajax_child_theme_save_full_width_ajax', 'child_theme_save_full_width_ajax' );
function child_theme_save_full_width_ajax() {
  if( isset($_POST['state']) && isset($_POST['pid']) && is_numeric($_POST['pid']) ) {
    add_post_meta($post_id, $meta_key, $meta_value, $unique);
    if ( ! add_post_meta( $_POST['pid'], '_no-sidebar', $_POST['state'], true ) ) {
      update_post_meta( $_POST['pid'], '_no-sidebar', $_POST['state'] );
    }
    echo '1';
  } else {
    _e('Data not valid, please try again', 'twenty-seventeen-child');
    // echo '<br />isset($_POST[state]): '. isset($_POST['state']);
    // echo '<br />isset($_POST[pid]): '. isset($_POST['pid']);
    // echo '<br />is_numeric($_POST[pid]): '. is_numeric($_POST['pid']);
  }

	wp_die(); // this is required to terminate immediately and return a proper response
}

// remove sidebar on full width content page
add_filter( 'widget_display_callback', 'child_theme_remove_sidebar', 10, 3 );
function child_theme_remove_sidebar( $instance, $widget, $args ){
  global $post;

  if ( 'true' === get_post_meta( $post->ID, '_no-sidebar', true) ) {
    return false;
  }
  return $instance;
}

// update body class on full width content page
add_filter( 'body_class', 'child_theme_update_body_class', 100);
function child_theme_update_body_class ($classes) {
  global $post;

  $value = 'has-sidebar';

  if ( 'true' === get_post_meta( $post->ID, '_no-sidebar', true) ) {
    if(($key = array_search($value, $classes)) !== false) {
        $classes[$key] = "full-width";
    }
  }

  return $classes;
}




// helper function
if( !function_exists ( 'kp_debug' ) ) {
  function kp_debug($a, $b = "kp debug") {
    echo '<b>'. $b .'</b><br /><pre>';
    print_r($a);
    echo '</pre>';
  }
}

?>
