<?php
/**
 * Plugin Name: Qentrah Languages
 * Plugin URI: https://github.com/Up-to-code/qentrah-languages
 * Description: Local-first multilingual publishing with native editor language navigation, linked translations and accessible language switchers.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Qentrah
 * Author URI: https://www.qentrah.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: qentrah-languages
 */
if (!defined('ABSPATH')) { exit; }
define('QL_VERSION', '0.1.0');
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
add_action('add_meta_boxes',function(){foreach(get_post_types(array('public'=>true)) as $type){if($type!=='attachment')add_meta_box('ql-translations',__('Qentrah Languages','qentrah-languages'),'ql_metabox',$type,'side','default',array('__back_compat_meta_box'=>true));}});
function ql_metabox($post){
    wp_nonce_field('ql_language_'.$post->ID,'ql_language_nonce');echo '<p><label for="ql-language">'.esc_html__('Page language','qentrah-languages').'</label><select id="ql-language" name="ql_language">';
    foreach(ql_languages() as $code=>$language)echo '<option value="'.esc_attr($code).'" '.selected(ql_post_language($post->ID),$code,false).'>'.esc_html($language['name']).'</option>';
    echo '</select></p><p>'.esc_html__('Use the Languages sidebar in the block editor to open or create translations. Save your edits before switching.','qentrah-languages').'</p>';
    foreach(ql_editor_state($post->ID)['translations'] as $item){if($item['url'])echo '<p><a href="'.esc_url($item['url']).'">'.esc_html($item['name']).'</a> · '.esc_html($item['status']).'</p>';}
}
add_action('save_post',function($id){
    if(wp_is_post_revision($id)||wp_is_post_autosave($id)||!isset($_POST['ql_language_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ql_language_nonce'])),'ql_language_'.$id)||!current_user_can('edit_post',$id))return;
    $language=sanitize_key(wp_unslash($_POST['ql_language']??''));if(!isset(ql_languages()[$language]))return;
    $siblings=ql_translations($id);if(isset($siblings[$language])&&(int)$siblings[$language]!==$id)return;
    update_post_meta($id,'_ql_language',$language);
});
add_action('admin_menu',function(){add_options_page('Qentrah Languages','Qentrah Languages','manage_options','qentrah-languages','ql_settings');});
function ql_settings(){
    if(!current_user_can('manage_options'))return;
    if(isset($_POST['ql_add'])){check_admin_referer('ql_settings');$code=sanitize_key(wp_unslash($_POST['code']??''));$name=sanitize_text_field(wp_unslash($_POST['name']??''));$locale=sanitize_text_field(wp_unslash($_POST['locale']??''));$dir=sanitize_key(wp_unslash($_POST['dir']??''))==='rtl'?'rtl':'ltr';
        if(preg_match('/^[a-z]{2,3}(-[a-z0-9]{2,8})*$/',$code)&&preg_match('/^[a-z]{2,3}([_-][A-Za-z0-9]{2,8})*$/',$locale)&&$name){$languages=ql_languages();$languages[$code]=array('name'=>$name,'locale'=>$locale,'dir'=>$dir);update_option('ql_languages',$languages);echo '<div class="notice notice-success"><p>'.esc_html__('Language saved.','qentrah-languages').'</p></div>';}
        else echo '<div class="notice notice-error"><p>'.esc_html__('Enter a valid language code, locale and display name.','qentrah-languages').'</p></div>';
    }
    if(isset($_POST['ql_default'])){check_admin_referer('ql_settings');$code=sanitize_key(wp_unslash($_POST['default_language']??''));if(isset(ql_languages()[$code]))update_option('ql_default_language',$code);}
    echo '<div class="wrap"><h1>Qentrah Languages</h1><p>'.esc_html__('Own your languages. Edit in WordPress. Content stays in your database; no external translation service is used.','qentrah-languages').'</p><table class="widefat"><thead><tr><th>Language</th><th>Code</th><th>Locale</th><th>Direction</th></tr></thead><tbody>';
    foreach(ql_languages() as $code=>$language)echo '<tr><td>'.esc_html($language['name']).'</td><td>'.esc_html($code).'</td><td>'.esc_html($language['locale']).'</td><td>'.esc_html($language['dir']).'</td></tr>';
    echo '</tbody></table><h2>'.esc_html__('Add or update a language','qentrah-languages').'</h2><form method="post">';wp_nonce_field('ql_settings');
    foreach(array('code'=>'Language code (fr, de, ar)','name'=>'Display name','locale'=>'Locale (fr_FR, de_DE, ar)') as $key=>$label)echo '<p><label>'.esc_html($label).' <input required name="'.esc_attr($key).'" type="text"></label></p>';
    echo '<p><label>Direction <select name="dir"><option value="ltr">Left to right</option><option value="rtl">Right to left</option></select></label></p>';submit_button('Save language','primary','ql_add');echo '</form><h2>Default language</h2><form method="post">';wp_nonce_field('ql_settings');echo '<select name="default_language">';foreach(ql_languages() as $code=>$language)echo '<option value="'.esc_attr($code).'" '.selected(ql_default_language(),$code,false).'>'.esc_html($language['name']).'</option>';echo '</select>';submit_button('Save default','secondary','ql_default');echo '</form><h2>Data portability</h2><p>Export language relationships and settings. Use WordPress Tools → Export for the content itself. Posts and translations are retained when the plugin is removed.</p><a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=ql_export'),'ql_export')).'">Export relationships</a></div>';
}
add_action('admin_post_ql_export',function(){if(!current_user_can('manage_options'))wp_die('Forbidden',403);check_admin_referer('ql_export');$posts=get_posts(array('post_type'=>'any','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_ql_group'));$rows=array();foreach($posts as $post)$rows[]=array('id'=>$post->ID,'url'=>get_permalink($post),'language'=>ql_post_language($post->ID),'group'=>get_post_meta($post->ID,'_ql_group',true));nocache_headers();header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename=qentrah-languages.json');echo wp_json_encode(array('version'=>1,'languages'=>ql_languages(),'default'=>ql_default_language(),'relationships'=>$rows),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);exit;});
