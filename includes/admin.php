<?php
if (!defined('ABSPATH')) { exit; }
function ql_admin_url($args=array()) { return add_query_arg($args,admin_url('admin.php?page=qentrah-languages')); }
function ql_content_types() { return array_filter(get_post_types(array('public'=>true),'objects'),function($type){return $type->name!=='attachment'&&current_user_can($type->cap->edit_posts);}); }
add_action('admin_menu',function(){
 add_menu_page('Qentrah Languages','Languages','edit_posts','qentrah-languages','ql_dashboard','dashicons-translation',58);
 add_submenu_page('qentrah-languages','Translations','Translations','edit_posts','qentrah-languages','ql_dashboard');
 add_submenu_page('qentrah-languages','Language settings','Settings','manage_options','qentrah-language-settings','ql_settings');
});
add_action('admin_bar_menu',function($bar){
 if(!current_user_can('edit_posts'))return;
 $id=is_admin()?absint($_GET['post']??0):(is_singular()?get_queried_object_id():0);
 $bar->add_node(array('id'=>'qentrah-languages','title'=>'<span class="ab-icon dashicons dashicons-translation" aria-hidden="true"></span><span class="ab-label">'.esc_html__('Languages','qentrah-languages').'</span>','href'=>ql_admin_url($id&&current_user_can('edit_post',$id)?array('source'=>$id):array()),'meta'=>array('title'=>__('Manage page and post translations','qentrah-languages'))));
 if(current_user_can('manage_options'))$bar->add_node(array('parent'=>'qentrah-languages','id'=>'ql-settings','title'=>__('Language settings','qentrah-languages'),'href'=>admin_url('admin.php?page=qentrah-language-settings')));
},80);
add_action('admin_enqueue_scripts',function($hook){if(strpos($hook,'qentrah-language')!==false)wp_enqueue_style('ql-admin',plugins_url('../assets/admin.css',__FILE__),array(),QL_VERSION);});
foreach(array('page_row_actions','post_row_actions') as $hook)add_filter($hook,function($actions,$post){if(current_user_can('edit_post',$post->ID))$actions['ql']= '<a href="'.esc_url(ql_admin_url(array('source'=>$post->ID))).'">'.esc_html__('Translations','qentrah-languages').'</a>';return $actions;},10,2);
function ql_admin_form($source,$action,$language=''){
 echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="ql-action">';wp_nonce_field('ql_manage_'.$source);echo '<input type="hidden" name="action" value="ql_manage"><input type="hidden" name="source" value="'.absint($source).'"><input type="hidden" name="operation" value="'.esc_attr($action).'"><input type="hidden" name="language" value="'.esc_attr($language).'">';
}
function ql_dashboard(){
 if(!current_user_can('edit_posts'))return;
 $source=absint($_GET['source']??0);$types=ql_content_types();
 echo '<div class="wrap ql-app"><header class="ql-header"><div><p class="ql-eyebrow">QENTRAH / WORDPRESS</p><h1>'.esc_html__('Your content. Every language.','qentrah-languages').'</h1><p>'.esc_html__('Choose content, open a translation, or create a draft. Edit with your usual WordPress editor.','qentrah-languages').'</p></div><a class="button" href="https://www.qentrah.com/documentation/qentrah-languages" target="_blank" rel="noopener">'.esc_html__('User manual','qentrah-languages').'</a></header>';
 if(isset($_GET['ql_saved']))echo '<div class="notice notice-success"><p>'.esc_html__('Translation updated.','qentrah-languages').'</p></div>';
 if($source){
  if(!current_user_can('edit_post',$source)||!isset($types[get_post_type($source)])){echo '<p>'.esc_html__('This content is unavailable.','qentrah-languages').'</p></div>';return;}
  $state=ql_editor_state($source);
  echo '<a href="'.esc_url(ql_admin_url()).'">&larr; '.esc_html__('All content','qentrah-languages').'</a><section class="ql-panel"><p class="ql-eyebrow">'.esc_html(get_post_type($source)).'</p><h2>'.esc_html(get_the_title($source)).'</h2><p>'.esc_html__('Each language is a separate page with its own revisions. Missing translations stay hidden from visitors.','qentrah-languages').'</p>';
  if($state['reviewNeeded']){echo '<div class="notice notice-warning inline"><p>'.esc_html__('The source has changed. Review this translation; your content has not been overwritten.','qentrah-languages').'</p>';ql_admin_form($source,'review');submit_button(__('Mark reviewed','qentrah-languages'),'secondary','submit',false);echo '</form></div>';}
  echo '<div class="ql-grid">';
  foreach($state['translations'] as $item){echo '<article class="ql-card"><span class="ql-code">'.esc_html(strtoupper($item['code'])).'</span><h3>'.esc_html($item['name']).'</h3><p>'.esc_html($item['available']?($item['id']===$source?__('Current page','qentrah-languages'):$item['status']):__('Not created','qentrah-languages')).'</p>';
   if($item['url'])echo '<a class="button button-primary" href="'.esc_url($item['url']).'">'.esc_html__('Open editor','qentrah-languages').'</a>';
   elseif(!$item['available']){ql_admin_form($source,'create',$item['code']);submit_button(__('Create draft & edit','qentrah-languages'),'primary','submit',false);echo '</form><details><summary>'.esc_html__('Already translated? Link a page','qentrah-languages').'</summary>';ql_admin_form($source,'link',$item['code']);echo '<label>'.esc_html__('Existing page or post ID','qentrah-languages').'<input type="number" min="1" required name="target"></label>';submit_button(__('Link existing content','qentrah-languages'),'secondary','submit',false);echo '</form></details>';}
   else echo '<p>'.esc_html__('You do not have permission to edit this translation.','qentrah-languages').'</p>';
   echo '</article>';
  }
  echo '</div><details class="ql-advanced"><summary>'.esc_html__('Change this page’s language','qentrah-languages').'</summary>';ql_admin_form($source,'assign');echo '<label>'.esc_html__('Language','qentrah-languages').'<select name="assign_language">';foreach($state['translations'] as $item)if(!$item['available']||$item['id']===$source)echo '<option value="'.esc_attr($item['code']).'" '.selected($item['code'],$state['language'],false).'>'.esc_html($item['name']).'</option>';echo '</select></label>';submit_button(__('Save language','qentrah-languages'),'secondary','submit',false);echo '</form></details></section></div>';return;
 }
 $type=sanitize_key($_GET['type']??'page');if(!isset($types[$type]))$type=array_key_first($types);
 $search=sanitize_text_field(wp_unslash($_GET['s']??''));$paged=max(1,absint($_GET['paged']??1));
 echo '<section class="ql-panel"><div class="ql-toolbar"><h2>'.esc_html__('Pages & posts','qentrah-languages').'</h2>';
 if(current_user_can('manage_options'))echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=qentrah-language-settings')).'">'.esc_html__('Add a language / settings','qentrah-languages').'</a>';
 echo '</div><p>'.esc_html__('All existing content is available here. Content without an assigned language uses the default language. Adding a language never publishes copied content automatically.','qentrah-languages').'</p><form method="get" class="ql-filters"><input type="hidden" name="page" value="qentrah-languages"><label>'.esc_html__('Content type','qentrah-languages').'<select name="type">';foreach($types as $key=>$object)echo '<option value="'.esc_attr($key).'" '.selected($type,$key,false).'>'.esc_html($object->labels->name).'</option>';echo '</select></label><label>'.esc_html__('Search titles and content','qentrah-languages').'<input type="search" name="s" value="'.esc_attr($search).'"></label>';submit_button(__('Search','qentrah-languages'),'secondary','submit',false);echo '</form>';
 $args=array('post_type'=>$type,'post_status'=>array('publish','draft','pending','private','future'),'posts_per_page'=>15,'paged'=>$paged,'s'=>$search,'orderby'=>'modified','perm'=>'readable');if($type&&!current_user_can($types[$type]->cap->edit_others_posts))$args['author']=get_current_user_id();
 $query=new WP_Query($args);echo '<div class="ql-table-scroll"><table class="widefat striped"><thead><tr><th>'.esc_html__('Content','qentrah-languages').'</th><th>'.esc_html__('Language','qentrah-languages').'</th><th>'.esc_html__('Translations','qentrah-languages').'</th><th>'.esc_html__('Action','qentrah-languages').'</th></tr></thead><tbody>';
 foreach($query->posts as $post){if(!current_user_can('edit_post',$post->ID))continue;$state=ql_editor_state($post->ID);echo '<tr><td><strong>'.esc_html($post->post_title?:__('Untitled','qentrah-languages')).'</strong><br><small>'.esc_html($post->post_status).' · #'.absint($post->ID).'</small></td><td>'.esc_html(strtoupper($state['language'])).'</td><td>';foreach($state['translations'] as $item)echo '<span class="ql-pill '.($item['available']?'':'ql-missing').'">'.esc_html(strtoupper($item['code']).' · '.($item['available']?$item['status']:__('Missing','qentrah-languages'))).'</span>';echo '</td><td><a class="button" href="'.esc_url(ql_admin_url(array('source'=>$post->ID))).'">'.esc_html__('Manage translations','qentrah-languages').'</a></td></tr>';}
 if(!$query->posts)echo '<tr><td colspan="4">'.esc_html__('No matching content. Create a page or post in WordPress first.','qentrah-languages').'</td></tr>';
 echo '</tbody></table></div><nav class="ql-pagination" aria-label="'.esc_attr__('Content pages','qentrah-languages').'">';for($i=max(1,$paged-2);$i<=min($query->max_num_pages,$paged+2);$i++)echo '<a class="button" '.($i===$paged?'aria-current="page"':'').' href="'.esc_url(ql_admin_url(array('type'=>$type,'s'=>$search,'paged'=>$i))).'">'.absint($i).'</a>';echo '</nav></section></div>';
}
add_action('admin_post_ql_manage',function(){
 $source=absint($_POST['source']??0);check_admin_referer('ql_manage_'.$source);
 if(!current_user_can('edit_post',$source))wp_die(esc_html__('You cannot edit this content.','qentrah-languages'),'',array('response'=>403));
 $operation=sanitize_key($_POST['operation']??'');if(!in_array($operation,array('create','link','assign','review'),true))wp_die('Invalid action');
 $request=new WP_REST_Request('POST','/qentrah-languages/v1/posts/'.$source);
 $request->set_param('language',sanitize_key(wp_unslash($_POST[$operation==='assign'?'assign_language':'language']??'')));
 if($operation==='link'){$target=absint($_POST['target']??0);if(!$target)wp_die(esc_html__('Choose an existing page ID.','qentrah-languages'));$request->set_param('target',$target);}
 if(in_array($operation,array('assign','review'),true))$request->set_param($operation,true);
 $response=rest_do_request($request);$data=$response->get_data();if($response->is_error())wp_die(esc_html($data['message']??__('Unable to update translation.','qentrah-languages')),'',array('back_link'=>true));
 wp_safe_redirect(isset($data['url'])?$data['url']:ql_admin_url(array('source'=>$source,'ql_saved'=>1)));exit;
});
