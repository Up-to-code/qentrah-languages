<?php
/**
 * Plugin Name: Qentrah Languages
 * Plugin URI: https://github.com/Up-to-code/qentrah-languages
 * Description: Local-first multilingual publishing with native editor language navigation, linked translations and accessible language switchers.
 * Version: 0.3.1
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Qentrah
 * Author URI: https://www.qentrah.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qentrah-languages
 */
if (!defined('ABSPATH')) { exit; }
define('QL_VERSION', '0.3.1');
add_action('init',function(){load_plugin_textdomain('qentrah-languages',false,dirname(plugin_basename(__FILE__)).'/languages');});
/** Validate BCP 47 syntax, including private-use and grandfathered tags.
 * This accepts syntax, not a claim that a tag has an IANA registration.
 */
function ql_valid_language_tag($tag) {
    if (!is_string($tag) || strlen($tag) > 255) return false;
    $tag = strtolower($tag);
    $grandfathered = array('en-gb-oed','i-ami','i-bnn','i-default','i-enochian','i-hak','i-klingon','i-lux','i-mingo','i-navajo','i-pwn','i-tao','i-tay','i-tsu','sgn-be-fr','sgn-be-nl','sgn-ch-de','art-lojban','cel-gaulish','no-bok','no-nyn','zh-guoyu','zh-hakka','zh-min','zh-min-nan','zh-xiang');
    if (in_array($tag, $grandfathered, true)) return true;
    if (preg_match('/^x(?:-[a-z0-9]{1,8})+$/D', $tag)) return true;
    if (!preg_match('/^(?:[a-z]{2,3}(?:-[a-z]{3}){0,3}|[a-z]{4}|[a-z]{5,8})(?:-[a-z]{4})?(?:-(?:[a-z]{2}|[0-9]{3}))?(?:-(?:[a-z0-9]{5,8}|[0-9][a-z0-9]{3}))*(?:-[0-9a-wy-z](?:-[a-z0-9]{2,8})+)*(?:-x(?:-[a-z0-9]{1,8})+)?$/D', $tag)) return false;
    $seen = array();
    foreach (explode('-', $tag) as $part) {
        if ($part === 'x') break;
        if (strlen($part) === 1) {
            if (isset($seen[$part])) return false;
            $seen[$part] = true;
        }
    }
    return true;
}
function ql_languages() {
    return get_option('ql_languages', array(
        'en'=>array('name'=>'English','locale'=>'en_US','dir'=>'ltr'),
        'ar'=>array('name'=>'العربية','locale'=>'ar','dir'=>'rtl'),
    ));
}
function ql_default_language() { $languages=ql_languages(); $default=get_option('ql_default_language','en'); return isset($languages[$default])?$default:array_key_first($languages); }
function ql_post_language($id=0) { $language=get_post_meta($id?:get_queried_object_id(),'_ql_language',true); return isset(ql_languages()[$language])?$language:ql_default_language(); }
function ql_current_language() { if(is_singular())return ql_post_language(); $requested=sanitize_key(get_query_var('ql_lang'));return isset(ql_languages()[$requested])?$requested:ql_default_language(); }
function ql_translations($id,$published=false) {
    $group=get_post_meta($id,'_ql_group',true); $out=array();
    if(!$group) { if(get_post($id)&&(!$published||get_post_status($id)==='publish'))$out[ql_post_language($id)]=(int)$id;return $out; }
    $posts=get_posts(array('post_type'=>get_post_type($id),'post_status'=>$published?'publish':array('publish','draft','pending','private','future'),'numberposts'=>count(ql_languages()),'meta_key'=>'_ql_group','meta_value'=>$group,'orderby'=>'ID','order'=>'ASC','suppress_filters'=>false));
    foreach($posts as $post){$language=ql_post_language($post->ID);if(!isset($out[$language]))$out[$language]=$post->ID;}
    return $out;
}
function ql_link_translation($source,$target,$language) {
    if(!isset(ql_languages()[$language])||!get_post($source)||!get_post($target)||get_post_type($source)!==get_post_type($target))return new WP_Error('invalid_translation',__('Invalid translation or language.','qentrah-languages'));
    $translations=ql_translations($source);
    if(isset($translations[$language])&&(int)$translations[$language]!==$target)return new WP_Error('language_exists',__('This language already has a translation.','qentrah-languages'));
    $target_group=get_post_meta($target,'_ql_group',true);$source_group=get_post_meta($source,'_ql_group',true);
    if($target_group&&$target_group!==$source_group&&count(ql_translations($target))>1)return new WP_Error('already_linked',__('The selected page belongs to another translation group.','qentrah-languages'));
    $group=$source_group?:wp_generate_uuid4();
    update_post_meta($source,'_ql_group',$group);update_post_meta($source,'_ql_language',ql_post_language($source));
    update_post_meta($target,'_ql_group',$group);update_post_meta($target,'_ql_language',$language);
    update_post_meta($target,'_ql_source_hash',hash('sha256',get_post($source)->post_content));update_post_meta($target,'_ql_source_id',$source);
    return $target;
}
function ql_create_translation($source,$language) {
    $post=get_post($source);$type=$post?get_post_type_object($post->post_type):null;
    if(!$post||!$type||!current_user_can('edit_post',$source)||!current_user_can($type->cap->create_posts))return new WP_Error('forbidden',__('You cannot create this translation.','qentrah-languages'),array('status'=>403));
    if(!isset(ql_languages()[$language]))return new WP_Error('invalid_language',__('Choose a configured language.','qentrah-languages'));
    $existing=ql_translations($source);if(isset($existing[$language]))return new WP_Error('exists',__('A translation already exists.','qentrah-languages'));
    $parent=$post->post_parent?(ql_translations($post->post_parent)[$language]??0):0;
    $content=apply_filters('ql_translation_content',$post->post_content,$source,$language);
    $id=wp_insert_post(wp_slash(array('post_type'=>$post->post_type,'post_status'=>'draft','post_title'=>$post->post_title.' — '.ql_languages()[$language]['name'],'post_name'=>$post->post_name.'-'.$language,'post_content'=>$content,'post_excerpt'=>$post->post_excerpt,'post_parent'=>$parent)),true);
    if(is_wp_error($id))return $id;
    foreach(array('_thumbnail_id','_wp_page_template') as $key){$value=get_post_meta($source,$key,true);if($value!=='')update_post_meta($id,$key,$value);}
    $linked=ql_link_translation($source,$id,$language);if(is_wp_error($linked)){wp_delete_post($id,true);return $linked;}do_action('ql_translation_created',$id,$source,$language);
    return $id;
}
add_filter('query_vars',function($vars){$vars[]='ql_lang';return $vars;});
add_filter('language_attributes',function($attributes){if(is_admin())return $attributes;$language=ql_current_language();return 'lang="'.esc_attr(str_replace('_','-',ql_languages()[$language]['locale'])).'" dir="'.esc_attr(ql_languages()[$language]['dir']).'"';},30);
add_action('pre_get_posts',function($query){
    if(is_admin()||!$query->is_main_query()||$query->is_singular())return;
    $language=$query->get('ql_lang');if(!isset(ql_languages()[$language]))return;
    $existing=$query->get('meta_query')?:array();
    $clause=array('key'=>'_ql_language','value'=>$language);
    if($language===ql_default_language())$clause=array('relation'=>'OR',$clause,array('key'=>'_ql_language','compare'=>'NOT EXISTS'));
    $query->set('meta_query',array('relation'=>'AND',$existing,$clause));
});
function ql_switcher($id=0) {
    $id=$id?:get_queried_object_id();$translations=ql_translations($id,true);if(count($translations)<2)return '';
    $html='<nav class="ql-language-switcher" aria-label="'.esc_attr__('Languages','qentrah-languages').'"><ul>';
    foreach($translations as $code=>$post_id){$language=ql_languages()[$code];$html.='<li><a href="'.esc_url(get_permalink($post_id)).'" lang="'.esc_attr(str_replace('_','-',$language['locale'])).'" hreflang="'.esc_attr(str_replace('_','-',$language['locale'])).'"'.($post_id===$id?' aria-current="page"':'').'>'.esc_html($language['name']).'</a></li>';}
    return $html.'</ul></nav>';
}
add_shortcode('qentrah_languages',function(){return ql_switcher();});
add_action('wp_head',function(){
    if(!is_singular()||get_post_status()!=='publish')return;
    $translations=ql_translations(get_queried_object_id(),true);
    if(count($translations)<2)return;
    foreach($translations as $code=>$id)echo '<link rel="alternate" hreflang="'.esc_attr(str_replace('_','-',ql_languages()[$code]['locale'])).'" href="'.esc_url(get_permalink($id)).'">'."\n";
    $default=$translations[ql_default_language()]??0;if($default)echo '<link rel="alternate" hreflang="x-default" href="'.esc_url(get_permalink($default)).'">'."\n";
},5);
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('qentrah-languages',plugins_url('assets/frontend.css',__FILE__),array(),QL_VERSION);});
function ql_editor_state($id) {
    $translations=ql_translations($id);$items=array();
    foreach(ql_languages() as $code=>$language){$target=$translations[$code]??0;$allowed=$target&&current_user_can('edit_post',$target);$items[]=array('code'=>$code,'name'=>$language['name'],'id'=>$allowed?$target:0,'url'=>$allowed?get_edit_post_link($target,'raw'):'','status'=>$allowed?get_post_status($target):'missing','available'=>(bool)$target);}
    $source=(int)get_post_meta($id,'_ql_source_id',true);$hash=get_post_meta($id,'_ql_source_hash',true);
    return array('language'=>ql_post_language($id),'direction'=>ql_languages()[ql_post_language($id)]['dir'],'translations'=>$items,'reviewNeeded'=>$source&&get_post($source)&&$hash!==hash('sha256',get_post($source)->post_content));
}
add_action('rest_api_init',function(){
    register_rest_route('qentrah-languages/v1','/posts/(?P<id>\d+)',array(
        array('methods'=>'GET','permission_callback'=>function($r){return current_user_can('edit_post',(int)$r['id']);},'callback'=>function($r){return ql_editor_state((int)$r['id']);}),
        array('methods'=>'POST','permission_callback'=>function($r){return current_user_can('edit_post',(int)$r['id']);},'callback'=>function($r){
            $source=(int)$r['id'];
            if($r->get_param('assign')){$language=sanitize_key($r->get_param('language'));$siblings=ql_translations($source);if(!isset(ql_languages()[$language])||(isset($siblings[$language])&&$siblings[$language]!==$source))return new WP_Error('invalid_language','Language unavailable');update_post_meta($source,'_ql_language',$language);return ql_editor_state($source);}
            if($r->get_param('review')){$original=(int)get_post_meta($source,'_ql_source_id',true);if($original&&get_post($original))update_post_meta($source,'_ql_source_hash',hash('sha256',get_post($original)->post_content));return ql_editor_state($source);}
            $language=sanitize_key($r->get_param('language'));$target=absint($r->get_param('target'));
            if($target){if(!current_user_can('edit_post',$target))return new WP_Error('forbidden','Forbidden',array('status'=>403));$result=ql_link_translation($source,$target,$language);}else{$result=ql_create_translation($source,$language);}
            return is_wp_error($result)?$result:array('id'=>$result,'url'=>get_edit_post_link($result,'raw'));
        })
    ));
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_script('qentrah-languages-editor',plugins_url('assets/editor.js',__FILE__),array('wp-plugins','wp-editor','wp-components','wp-element','wp-data','wp-api-fetch','wp-i18n','wp-blocks','wp-block-editor'),QL_VERSION,true);});
add_action('init',function(){register_block_type('qentrah/language-switcher',array('api_version'=>3,'render_callback'=>function(){return ql_switcher();}));});
require_once __DIR__.'/includes/admin.php';
function ql_settings(){
    if(!current_user_can('manage_options'))return;
    if(isset($_POST['ql_add'])){check_admin_referer('ql_settings');$code=strtolower(trim(wp_unslash($_POST['code']??'')));$name=sanitize_text_field(wp_unslash($_POST['name']??''));$locale=sanitize_text_field(wp_unslash($_POST['locale']??''))?:$code;$dir=sanitize_key(wp_unslash($_POST['dir']??''))==='rtl'?'rtl':'ltr';
        if(ql_valid_language_tag($code)&&ql_valid_language_tag(str_replace('_','-',$locale))&&$name){$languages=ql_languages();$languages[$code]=array('name'=>$name,'locale'=>$locale,'dir'=>$dir);update_option('ql_languages',$languages);echo '<div class="notice notice-success"><p>'.esc_html__('Language saved.','qentrah-languages').'</p></div>';}
        else echo '<div class="notice notice-error"><p>'.esc_html__('Enter a valid language code, locale and display name.','qentrah-languages').'</p></div>';
    }
    if(isset($_POST['ql_default'])){check_admin_referer('ql_settings');$code=sanitize_key(wp_unslash($_POST['default_language']??''));if(isset(ql_languages()[$code]))update_option('ql_default_language',$code);}
    echo '<div class="wrap ql-app"><div class="ql-panel"><h1>Qentrah Languages</h1><p>'.esc_html__('Own your languages. Edit in WordPress. Content stays in your database; no external translation service is used.','qentrah-languages').'</p><table class="widefat"><thead><tr><th>Language</th><th>Code</th><th>Locale</th><th>Direction</th></tr></thead><tbody>';
    foreach(ql_languages() as $code=>$language)echo '<tr><td>'.esc_html($language['name']).'</td><td>'.esc_html($code).'</td><td>'.esc_html($language['locale']).'</td><td>'.esc_html($language['dir']).'</td></tr>';
    echo '</tbody></table><p><a class="button" href="'.esc_url(ql_admin_url()).'">'.esc_html__('Manage translations','qentrah-languages').'</a></p><h2>'.esc_html__('Add or update a language','qentrah-languages').'</h2><p>'.esc_html__('Add any content language using a BCP 47 tag and a name in its own script. No fixed language list or translation service is required. Choose the writing direction explicitly.','qentrah-languages').'</p><form method="post">';wp_nonce_field('ql_settings');
    foreach(array('code'=>'Language tag (en, ar, pt-BR, zh-Hant, x-custom)','name'=>'Display name','locale'=>'Locale (optional: fr_FR, de_DE, ar)') as $key=>$label)echo '<p><label>'.esc_html($label).' <input '.($key==='locale'?'':'required').' name="'.esc_attr($key).'" type="text"></label></p>';
    echo '<p><label>Direction <select name="dir"><option value="ltr">Left to right</option><option value="rtl">Right to left</option></select></label></p>';submit_button('Save language','primary','ql_add');echo '</form><h2>Default language</h2><form method="post">';wp_nonce_field('ql_settings');echo '<select name="default_language">';foreach(ql_languages() as $code=>$language)echo '<option value="'.esc_attr($code).'" '.selected(ql_default_language(),$code,false).'>'.esc_html($language['name']).'</option>';echo '</select>';submit_button('Save default','secondary','ql_default');echo '</form><h2>Data portability</h2><p>Export language relationships and settings. Use WordPress Tools → Export for the content itself. Posts and translations are retained when the plugin is removed.</p><a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=ql_export'),'ql_export')).'">Export relationships</a></div></div>';
}
add_action('admin_post_ql_export',function(){if(!current_user_can('manage_options'))wp_die('Forbidden',403);check_admin_referer('ql_export');$posts=get_posts(array('post_type'=>'any','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_ql_group'));$rows=array();foreach($posts as $post)$rows[]=array('id'=>$post->ID,'url'=>get_permalink($post),'language'=>ql_post_language($post->ID),'group'=>get_post_meta($post->ID,'_ql_group',true));nocache_headers();header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename=qentrah-languages.json');echo wp_json_encode(array('version'=>1,'languages'=>ql_languages(),'default'=>ql_default_language(),'relationships'=>$rows),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);exit;});
