<?php
/**
 * Plugin Name: Single Product Page Contact Buttons
 * Description: Responsive Instagram Profile & Send Whatsapp Message buttons for WooCommerce Single Product Page
 * Author: Emre Güler
 * Author URI: https://eguler.net
 * Version: 1.7
 * Text Domain: product-contact-buttons
 *
 * Copyright: (c) 2019 Emre Güler (iletisim@eguler.net)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    Emre Güler
 * @copyright Copyright (c) 2019, Emre Güler
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Direkt erişimi engelle

register_activation_hook( __FILE__, 'egpcb_activate' );
register_deactivation_hook( __FILE__, 'egpcb_deactivate' );

function egpcb_activate(){
    add_option('pb_whatsapp', '');
    add_option('pb_insta', '');
    add_option('pb_wtext', 'Whatsapp Mesaj');
    add_option('pb_wmesaj', '');
    add_option('pb_itext', 'Instagram Takip');
    add_option('pb_whatsappa', '0');
    add_option('pb_instaa', '0');
}

function egpcb_deactivate(){
    delete_option('pb_whatsapp');
    delete_option('pb_insta');
    delete_option('pb_wtext');
    delete_option('pb_wmesaj');
    delete_option('pb_itext');
    delete_option('pb_whatsappa');
    delete_option('pb_instaa');
    if(wp_style_is( 'font-awesome-free' )){
        wp_dequeue_style( 'font-awesome-free' );
    }
    if(wp_style_is( 'egpcb' )){
        wp_dequeue_style( 'egpcb' );
    }
}

add_action('plugins_loaded', 'wan_load_textdomain');
function wan_load_textdomain() {
	load_plugin_textdomain( 'product-contact-buttons', false, dirname( plugin_basename(__FILE__) ) . '/i18n/' );
}

function egpcbCheckTel($text) {
    $text  = preg_replace("/[^0-9]/", "", $text);
    $first = substr("$text",0,1);
    if($first == "0") { $text = substr($text,1); }
    $new_telefon = "+$text";
    return $new_telefon; 
}

add_action( 'wp_enqueue_scripts', 'egpcb_load_scripts' );
function egpcb_load_scripts() {
    if(!wp_style_is( 'font-awesome-free' )){
        wp_enqueue_style( 'font-awesome-free', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );
    }
    if(!wp_style_is( 'egpcb' )){
        wp_enqueue_style( 'egpcb', plugins_url( 'egpcb.css', __FILE__ ) );
    }
}

add_action( 'woocommerce_share', 'add_pcb', 5 ); 
function add_pcb() {
    $wa=egpcbCheckTel(esc_html(get_option("pb_whatsapp")));
    $insta=esc_html(get_option("pb_insta"));
    $wtext=(!empty(get_option("pb_wtext"))?esc_html(get_option("pb_wtext")):"Whatsapp Mesaj");
    $product = wc_get_product( get_the_ID() );  
    $name = $product->get_title();
    $wmesaj=(!empty(get_option("pb_wmesaj"))?rawurlencode(esc_attr(str_replace("[urun-ad]", $name, get_option("pb_wmesaj")))):"Merhaba");
    $itext=(!empty(get_option("pb_itext"))?esc_html(get_option("pb_itext")):"Instagram Takip");
    echo '<div id="pbcontainer" class="pbflexChild pbrowParent">'.
            ((get_option("pb_whatsappa")==1)?'
          <div id="pbrowChild" class="pbflexChild">
                <a class="wa-button" href="//api.whatsapp.com/send?phone='.$wa.'&text='.$wmesaj.'" target="_blank">
                  <span class="wa-button-icon"><i class="fab fa-whatsapp"></i></span>
                  <span class="wa-button-text">'. $wtext .'</span>
                </a>
          </div>':'').((get_option("pb_instaa")==1)?'
          <div id="pbrowChild2" class="pbflexChild">
                <a class="insta-button" href="https://instagram.com/'.$insta.'" target="_blank">
                  <span class="insta-button-icon"><i class="fab fa-instagram"></i></span>
                  <span class="insta-button-text">'. $itext .'</span>
                </a>
          </div>':'')
        .'</div>';
}

add_action('admin_menu', 'egpcb_admin_menu');
function egpcb_admin_menu(){
 add_menu_page('Instagram & Whatsapp Buttons','EG PCB', 'manage_options', 'egpcb-ayarlar', 'egpcb_admin_panel','dashicons-share');
}

function egpcb_admin_panel(){?>
<h1><?php _e( 'Instagram &amp; Whatsapp Buttons', 'product-contact-buttons' )?></h1>
<?php
if(isset($_POST["pb_whatsappa"])){
  // Wp_nonce Kontrol edelim
  if (!isset($_POST['pb_update']) || ! wp_verify_nonce( $_POST['pb_update'], 'pb_update' ) ) {
    print 'Üzgünüz, bu sayfaya erişim yetkiniz yok!';
    exit;
  }else{
    // Güvenliği geçti ise
    $whatsappno = egpcbCheckTel(sanitize_text_field($_POST['pb_whatsapp']));
    if($whatsappno !== "false"){update_option('pb_whatsapp', substr($whatsappno,3));}
    $instaccount = sanitize_key($_POST['pb_insta']);
    update_option('pb_insta', $instaccount);
    $wtext = sanitize_text_field($_POST['pb_wtext']);
    update_option('pb_wtext', $wtext);
    $wmesaj = wp_filter_nohtml_kses($_POST['pb_wmesaj']);
    update_option('pb_wmesaj', $wmesaj);
    $itext = sanitize_text_field($_POST['pb_itext']);
    update_option('pb_itext', $itext);
    $wae = (isset($_POST['pb_whatsappa']))?"1":"0";
    $inse = (isset($_POST['pb_instaa']))?"1":"0";
    update_option('pb_whatsappa', $wae);
    update_option('pb_instaa', $inse);
    echo'<div class="updated"><p><strong>';
    _e( 'Settings saved.', 'product-contact-buttons' );
    echo'</strong></p></div>';
  }
}?>
<p><? _e( 'Product contact buttons settings', 'product-contact-buttons' ) ?></p>
<p class="pcbsmalltext"><? _e( 'You can use [urun-ad] shortcode for adding product name to your message', 'product-contact-buttons' ) ?></p>
<form method="post">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="pb_whatsappa">
                    <? _e( 'Enable Whatsapp Button', 'product-contact-buttons' ) ?></label></th>
            <td><input type="checkbox" id="pb_whatsappa" name="pb_whatsappa" value="1" <?php checked("1"== get_option("pb_whatsappa") ); ?>></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_whatsapp">
                    <? _e( 'Whatsapp Phone Number (International Phone Number)', 'product-contact-buttons' ) ?></label></th>
            <td><input type="number" min="1000000000" placeholder="905551231212" name="pb_whatsapp" value="<?php echo intval(esc_html(get_option("pb_whatsapp")));?>"></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_wtext">
                    <? _e( 'Whatsapp Button Text', 'product-contact-buttons' ) ?></label></th>
            <td><input type="text" placeholder="Whatsapp Message" name="pb_wtext" value="<?php echo esc_html(get_option("pb_wtext"));?>"></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_wmesaj">
                    <? _e( 'Whatsapp Message', 'product-contact-buttons' ) ?></label></th>
            <td><textarea rows="4" cols="50" placeholder="Hi there!" name="pb_wmesaj"><?php echo esc_html(get_option("pb_wmesaj"));?></textarea></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_instaa">
                    <? _e( 'Enable Instagram Button', 'product-contact-buttons' ) ?></label></th>
            <td><input type="checkbox" id="pb_instaa" name="pb_instaa" value="1" <?php checked( "1"==get_option("pb_instaa") ); ?>></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_insta">
                    <? _e( 'Instagram Username', 'product-contact-buttons' ) ?></label></th>
            <td><input type="text" placeholder="emreguler" name="pb_insta" value="<?php echo esc_html(get_option("pb_insta"));?>"></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="pb_itext">
                    <? _e( 'Instagram Button Text', 'product-contact-buttons' ) ?></label></th>
            <td><input type="text" placeholder="Instagram Takip" name="pb_itext" value="<?php echo esc_html(get_option("pb_itext"));?>"></td>
        </tr>
        <?php wp_nonce_field('pb_update','pb_update');//Güvenlik amaçlı gizli form alanları?>
        <input type="hidden" name="action" value="guncelle">
        <tr valign="top">
            <th scope="row"></th>
            <td><input type="submit" value="Güncelle"></td>
        </tr>
    </table>
</form>
<?php }  ?>
