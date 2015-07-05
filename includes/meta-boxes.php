<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Set license fields
 *
 * @since       1.0.0
 * @param       int $post_id The ID of this download
 * @return      void
 */
function edd_slm_metabox_licensing( $post_id = 0 ) {

    $edd_slm_licensing_enabled = get_post_meta( $post_id, '_edd_slm_licensing_enabled', true ) ? true : false;
    $edd_slm_sites_allowed       = esc_attr( get_post_meta( $post_id, '_edd_slm_sites_allowed', true ) );
    $edd_slm_display   	    = $edd_slm_licensing_enabled ? '' : ' style="display:none;"';

    ?>

    <script type="text/javascript">jQuery( document ).ready( function($) {
            $( "#_edd_slm_licensing_enabled" ).on( "click",function() {
                // TODO: Improve toggle handling and prevent double display
                $( ".edd-slm-variable-toggled-hide" ).toggle();
                $( ".edd-slm-toggled-hide" ).toggle();
            })
        });</script>

    <p>
        <input type="checkbox" name="_edd_slm_licensing_enabled" id="_edd_slm_licensing_enabled" value="1" <?php echo checked( true, $edd_slm_licensing_enabled, false ); ?> />
        <label for="_edd_slm_licensing_enabled"><?php _e( 'Enable licensing for this download.', 'edd-slm' ); ?></label>
    </p>

    <div <?php echo $edd_slm_display; ?> class="edd-slm-toggled-hide">
        <p>
            <label for="_edd_slm_sites_allowed"><?php _e( 'How many sites can be activated trough a single license key?', 'edd-slm' ); ?></label>
            <input type="number" name="_edd_slm_sites_allowed" class="small-text" value="<?php echo $edd_slm_sites_allowed; ?>" />
        </p>
    </div>

<?php
}
add_action( 'edd_meta_box_fields', 'edd_slm_metabox_licensing' );

/**
 * Price rows header for licensing
 *
 * @since       1.0.0
 * @return      void
 */

function edd_slm_prices_header( $download_id ) {

    if( 'bundle' == edd_get_download_type( $download_id ) ) {
        return;
    }

    // Get membership length enabled for deciding when to show membership length
    $edd_slm_licensing_enabled = get_post_meta( $download_id, '_edd_slm_licensing_enabled', true ) ? true : false;
    $edd_slm_display = $edd_slm_licensing_enabled ? '' : ' style="display:none;"';

    ?>
    <th <?php echo $edd_slm_display; ?> class="edd-slm-variable-toggled-hide"><?php _e( 'Sites allowed', 'edd-slm' ); ?></th>
<?php
}

add_action( 'edd_download_price_table_head', 'edd_slm_prices_header', 800 );

/**
 * Membership length for variable price options
 *
 * @since       1.0.0
 * @return      void
 */
function edd_slm_price_option_sites_allowed( $download_id, $price_id, $args ) {

    if( 'bundle' == edd_get_download_type( $download_id ) ) {
        return;
    }

    // Get membership length and unit for variable prices
    $edd_slm_sites_allowed   = edd_slm_get_variable_price_sites_allowed( $download_id, $price_id );

    // Get membership length enabled for deciding when to show membership length option
    $edd_slm_licensing_enabled = get_post_meta( $download_id, '_edd_slm_licensing_enabled', true ) ? true : false;
    $edd_slm_display   	    = $edd_slm_licensing_enabled ? '' : ' style="display:none;"';

    ?>
    <td <?php echo $edd_slm_display; ?> class="edd-slm-sites-allowed edd-slm-variable-toggled-hide">
        <input type="number" min="0" step="1" name="edd_variable_prices[<?php echo $price_id; ?>][edd_slm_sites_allowed]" id="edd_variable_prices[<?php echo $price_id; ?>][edd_slm_sites_allowed]" size="4" style="width: 70px" value="<?php echo absint( $edd_slm_sites_allowed ); ?>" />
    </td>
<?php
}
add_action( 'edd_download_price_table_row', 'edd_slm_price_option_sites_allowed', 800, 3 );

/**
 * Save the plugin fields when EDD saves other fields.
 *
 * @param  array $fields Existing fields to save
 * @since  1.0.0
 * @return array $fields Modified fields
 */
function edd_slm_save_metabox_fields( $fields ) {

    $fields[] = '_edd_slm_licensing_enabled';
    $fields[] = '_edd_slm_sites_allowed';

    return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_slm_save_metabox_fields' );

/**
 * Sanitize the checkbox value.
 *
 * @since  1.0.0
 * @param  string $input checkbox.
 * @return string (1 or null).
 */
function edd_slm_sanitize_checkbox( $input ) {
    if ( 1 == $input ) {
        return 1;
    } else {
        return '';
    }
}