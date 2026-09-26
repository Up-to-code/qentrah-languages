<?php
/** Run with wp --user=ADMIN eval-file tests/integration.php. Fixtures are removed. */
$original_languages=ql_languages();$ids=array();$user=0;$admin=get_current_user_id();
function ql_test($condition,$message){if(!$condition)throw new RuntimeException($message);}
try{
 $languages=$original_languages;$languages['fr']=array('name'=>'Français','locale'=>'fr_FR','dir'=>'ltr');update_option('ql_languages',$languages);
 $source=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'QL test source','post_content'=>'<!-- wp:paragraph --><p>Original content</p><!-- /wp:paragraph -->'));$ids[]=$source;update_post_meta($source,'_ql_language','en');
 $target=ql_create_translation($source,'fr');ql_test(!is_wp_error($target),'Create translation');$ids[]=$target;
 ql_test(get_post_status($target)==='draft','Copies must start as drafts');ql_test(get_post($source)->post_content===get_post($target)->post_content,'Core block content preserved');
 ql_test(!isset(ql_translations($source,true)['fr']),'Draft excluded from switcher');
 wp_update_post(array('ID'=>$target,'post_status'=>'publish'));ql_test(ql_translations($source,true)['fr']===$target,'Published translation exposed');
 ql_test(str_contains(ql_switcher($source),'Français'),'Switcher contains third language');
 ql_test(is_wp_error(ql_create_translation($source,'fr')),'Duplicate translation rejected');
 ql_test(is_wp_error(ql_create_translation($source,'zz')),'Unconfigured language rejected');
 wp_update_post(array('ID'=>$source,'post_content'=>'Changed source'));ql_test(ql_editor_state($target)['reviewNeeded'],'Source-change warning');
 $review=new WP_REST_Request('POST','/qentrah-languages/v1/posts/'.$target);$review->set_param('review',true);$reviewed=rest_do_request($review);ql_test($reviewed->get_status()===200&&!ql_editor_state($target)['reviewNeeded'],'Review acknowledgement');
 $other=wp_insert_post(array('post_type'=>'page','post_status'=>'draft','post_title'=>'QL unrelated'));$ids[]=$other;
 ql_test(is_wp_error(ql_link_translation($source,$other,'fr')),'Do not overwrite existing translation');
 $user=wp_create_user('ql-test-'.wp_generate_password(8,false,false),wp_generate_password(30),'ql-test-'.wp_generate_password(8,false,false).'@example.test');ql_test(!is_wp_error($user),'Create subscriber fixture');wp_set_current_user($user);
 ql_test(is_wp_error(ql_create_translation($source,'ar')),'Subscriber cannot create translation');
 $request=new WP_REST_Request('GET','/qentrah-languages/v1/posts/'.$source);$response=rest_do_request($request);ql_test($response->get_status()===403,'REST permission enforcement');
 wp_set_current_user($admin);
 WP_CLI::success('Third language, drafts, groups, content preservation, missing translations, review status and authorization passed.');
}finally{
 wp_set_current_user($admin);foreach($ids as $id)wp_delete_post($id,true);update_option('ql_languages',$original_languages);
 if(is_int($user)&&$user){require_once ABSPATH.'wp-admin/includes/user.php';wp_delete_user($user);}
}
