<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Promo Popup
 * Description:       Used to display a promo popup on the front end of the site.
 * Version:           1.0.4
 * Author:            Matt Steiner
 * Text Domain:       aurora-promo-popup
 */

// If this file is called directly, abort.
if (!defined('WPINC')) die;

function aurora_promo_popup_admin_enqueue_scripts($hook) {
    if ('toplevel_page_aurora-promo-popup' !== $hook) {
        return;
    }

    // Enqueue media library scripts and styles
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'aurora_promo_popup_admin_enqueue_scripts');


// Add admin menu and page
function aurora_promo_popup_admin_menu() {
    add_menu_page(
        'Promo Popup Settings',
        'Promo Popup',
        'manage_options',
        'aurora-promo-popup',
        'aurora_promo_popup_admin_page',
        'dashicons-format-image',
        100
    );
}
add_action('admin_menu', 'aurora_promo_popup_admin_menu');

// Create the admin page content
function aurora_promo_popup_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
?>

    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('aurora_promo_popup_options');
            do_settings_sections('aurora_promo_popup_options');
            submit_button('Save Changes');
            ?>
        </form>
    </div>

<?php
}

// Register settings, sections, and fields
function aurora_promo_popup_settings_init() {
    register_setting('aurora_promo_popup_options', 'aurora_promo_popup_options');

    add_settings_section(
        'aurora_promo_popup_general_section',
        'General Settings',
        null,
        'aurora_promo_popup_options'
    );

    add_settings_field(
        'image_id',
        'Promo Image',
        'aurora_promo_popup_image_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );

    // always show popup field
    add_settings_field(
        'always_show',
        'Always Show Popup',
        'aurora_promo_popup_always_show_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );

    // Add the cookie time field
    add_settings_field(
        'cookie_time',
        'Cookie Expiration Time (Days)',
        'aurora_promo_popup_cookie_time_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );

    // Add the promo url field
    add_settings_field(
        'promo_url',
        'Promo URL',
        'aurora_promo_popup_url_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );

    // end date field
    add_settings_field(
        'end_date',
        'Promo Popup End Date',
        'aurora_promo_popup_end_date_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );

    // Add the enabled/disabled toggle field
    add_settings_field(
        'enabled',
        'Enable Promo Popup',
        'aurora_promo_popup_enabled_field',
        'aurora_promo_popup_options',
        'aurora_promo_popup_general_section'
    );
}
add_action('admin_init', 'aurora_promo_popup_settings_init');

// Promo Image field
function aurora_promo_popup_image_field() {
    $options = get_option('aurora_promo_popup_options');
    $image_id = isset($options['image_id']) ? $options['image_id'] : '';
    $image_src = wp_get_attachment_image_src($image_id, 'thumbnail');
    $image_url = $image_src ? $image_src[0] : '';
?>

    <input type="hidden" name="aurora_promo_popup_options[image_id]" id="promo_image_id" value="<?php echo esc_attr($image_id); ?>">
    <img id="promo_image_preview" src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; max-height: 150px; display: <?php echo $image_url ? 'block' : 'none'; ?>">
    <button type="button" class="button" id="promo_image_button"><?php echo $image_url ? 'Change Image' : 'Select Image'; ?></button>
    <button type="button" class="button" id="promo_image_remove" style="display: <?php echo $image_url ? 'inline' : 'none'; ?>">Remove Image</button>

    <script>
        jQuery(document).ready(function($) {
            var frame;

            $('#promo_image_button').on('click', function(event) {
                event.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Select or Upload Promo Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#promo_image_id').val(attachment.id);
                    $('#promo_image_preview').attr('src', attachment.url).css('display', 'block');
                    $('#promo_image_button').text('Change Image');
                    $('#promo_image_remove').css('display', 'inline');
                });

                frame.open();
            });

            $('#promo_image_remove').on('click', function(event) {
                event.preventDefault();
                $('#promo_image_id').val('');
                $('#promo_image_preview').attr('src', '').css('display', 'none');
                $('#promo_image_button').text('Select Image');
                $(this).css('display', 'none');
            });
        });
    </script>

<?php
}

// Always show popup field callback
function aurora_promo_popup_always_show_field() {
    $options = get_option('aurora_promo_popup_options');
    $always_show = isset($options['always_show']) ? $options['always_show'] : 0;
?>

    <input type="checkbox" name="aurora_promo_popup_options[always_show]" value="1" <?php checked($always_show, 1); ?>>

<?php
}

// Cookie Expiration Time field
function aurora_promo_popup_cookie_time_field() {
    $options = get_option('aurora_promo_popup_options');
    $cookie_time = isset($options['cookie_time']) ? $options['cookie_time'] : 7;
?>

    <input type="number" name="aurora_promo_popup_options[cookie_time]" value="<?php echo esc_attr($cookie_time); ?>" min="1" step="1">

<?php

}

// Promo URL field
function aurora_promo_popup_url_field() {
    $options = get_option('aurora_promo_popup_options');
    $promo_url = isset($options['promo_url']) ? $options['promo_url'] : '';
?>
    <input type="url" name="aurora_promo_popup_options[promo_url]" value="<?php echo esc_url($promo_url); ?>">
<?php
}

function aurora_promo_popup_end_date_field() {
    $options = get_option('aurora_promo_popup_options');
    $end_date = isset($options['end_date']) ? $options['end_date'] : '';
?>

    <input type="date" name="aurora_promo_popup_options[end_date]" value="<?php echo esc_attr($end_date); ?>">

<?php
}

// Enabled field callback
function aurora_promo_popup_enabled_field() {
    $options = get_option('aurora_promo_popup_options');
    $enabled = isset($options['enabled']) ? $options['enabled'] : 0;
?>

    <input type="checkbox" name="aurora_promo_popup_options[enabled]" value="1" <?php checked($enabled, 1); ?>>

<?php
}

// show html for promo popup
function aurora_promo_popup() {

    $options = get_option('aurora_promo_popup_options');

    // check if end date is in the past
    $end_date = isset($options['end_date']) ? $options['end_date'] : '';
    $current_date = date("Y-m-d");

    // if end date is in the past, don't show popup
    if ($end_date < $current_date) {
        return;
    }

    $image_id = isset($options['image_id']) ? $options['image_id'] : '';
    $image_src = wp_get_attachment_image_src($image_id, 'full');
    $image_url = $image_src ? $image_src[0] : '';
    $cookie_time = isset($options['cookie_time']) ? $options['cookie_time'] : 7;
    $options = get_option('aurora_promo_popup_options');
    $enabled = isset($options['enabled']) ? $options['enabled'] : 0;

    if (!$enabled) {
        return;
    }

?>

    <!-- CSS -->
    <style>
        /* Background */
        #promo-popup-notification-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: none;
        }

        #promo-popup-notification-bg.show {
            display: block;
            animation: notification-fadein 0.3s;
        }

        /* Notification */
        #promo-popup-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            height: 584px;
            background-color: #fff;
            z-index: 10000;
            display: none;
            text-align: center;
        }

        #promo-popup-notification.show {
            display: flex;
            flex-direction: column;
            animation: notification-fadein 0.3s;
        }

        #promo-popup-notification__link {
            height: calc(100% - 50px);
            background-color: #100a0c;
        }

        #promo-popup-notification__link img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background-color: #100a0c;
        }

        #promo-popup-notification__close {
            display: inline-block;
            text-align: center;
            height: 50px;
            font-weight: 600;
            text-transform: uppercase;
            color: #333;
            line-height: 50px;
        }

        /* Animations */
        @keyframes notification-fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes notification-background-fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>

    <!-- HTML -->
    <div id="promo-popup-notification">
        <a href="<?php echo esc_url($options['promo_url']); ?>" id="promo-popup-notification__link">
            <img src="<?php echo esc_url($image_url); ?>" alt="">
        </a>
        <a href="#" id="promo-popup-notification__close">close &times;</a>
    </div>
    <div id="promo-popup-notification-bg"></div>

    <!-- JS -->
    <script>
        // show notification
        function showNotification() {
            document.getElementById('promo-popup-notification').classList.add('show');
            document.getElementById('promo-popup-notification-bg').classList.add('show');
        }

        // hide notification
        function hideNotification() {
            document.getElementById('promo-popup-notification').classList.remove('show');
            document.getElementById('promo-popup-notification-bg').classList.remove('show');

            // set cookie to expire
            var d = new Date();
            d.setTime(d.getTime() + (<?php echo esc_js($cookie_time); ?> * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toUTCString();
            document.cookie = "promo-popup-notification-2=hide; " + expires + "; path=/";
        }

        // check if cookie exists
        function checkCookie() {
            var cookie = document.cookie.split(';').filter((item) => item.trim().startsWith('promo-popup-notification-2='));
            if (cookie.length) {
                return true;
            } else {
                return false;
            }
        }

        // get always show value
        var alwaysShow = <?php echo $options['always_show'] ? 'true' : 'false'; ?>;

        // show notification if cookie doesn't exist or always show is enabled
        if (!checkCookie() || alwaysShow) {
            setTimeout(showNotification, 3000);
        }

        // hide notification when background is clicked
        document.getElementById('promo-popup-notification-bg').addEventListener('click', hideNotification);


        // hide notification when close button is clicked
        document.getElementById('promo-popup-notification__close').addEventListener('click', hideNotification);
    </script>

<?php }
add_action('wp_footer', 'aurora_promo_popup');
