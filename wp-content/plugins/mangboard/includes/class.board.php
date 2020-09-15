<?php
class MangBoard
{
	public function __construct($db=NULL,$mstore=NULL){
	}
	
	public function get_board_panel($args=NULL){
		mbw_add_trace("board->get_board_panel");
		global $mdb,$mstore;
		global $mb_admin_tables,$mb_board_table_name,$mb_comment_table_name;
		global $mb_fields,$mb_api_urls;
		global $mb_vars,$mb_board_name,$mb_words,$mb_languages;
		global $list_model,$view_model,$write_model;
		
		$mb_board_name	= mbw_get_board_name();
		if(empty($mb_board_name) && !empty($args['name'])) $mb_board_name		= $args['name'];
		if(empty($mb_board_table_name) && !empty($mb_board_name)) $mb_board_table_name		= mbw_get_board_table_name($mb_board_name);

		$mb_user_level		= mbw_get_user("fn_user_level");

		if(mbw_get_param("mode")==""){
			if(!empty($args['mode'])){
				mbw_set_param("mode", $args['mode']);
				if($args['mode']=="write") mbw_load_editor_plugin();
			}else mbw_set_param("mode", "list");
			if(!empty($args['board_action'])) mbw_set_param("board_action", $args['board_action']);
			else if(!empty($args['action'])) mbw_set_param("board_action", $args['action']);
			if(!empty($args['board_pid'])) mbw_set_param("board_pid", $args['board_pid']);			
		}
		if(!isset($_GET["category1"]) && mbw_get_param("category1")==""){
			if(!empty($args['category1'])) mbw_set_param("category1", $args['category1']);
			if(!empty($args['category2'])) mbw_set_param("category2", $args['category2']);
			if(!empty($args['category3'])) mbw_set_param("category3", $args['category3']);
			mbw_set_category_params();
		}
		if(!empty($args['search_field'])){
			if(function_exists('mbw_set_search_field')) mbw_set_search_field($args);
		}
		if(!empty($args['write_next_url'])){
			mbw_set_option("write_next_page","url");
			mbw_set_option("write_next_url",mbw_validate_redirect(trim($args['write_next_url'])));
		}
		if(mbw_get_param("mode")=="view" && mbw_get_param("board_pid")==""){
			$where_query		= '';
			$where_data		= array();		
			$board_field		= $mb_fields["select_board"];
			$category1			= mbw_get_param("category1");
			$category2			= mbw_get_param("category2");
			$category3			= mbw_get_param("category3");
			if(!empty($category1)){
				if(strpos($category1, ',') !== false){
					$category1_array		= explode(',',$category1);
					$filter_array1			= array();
					foreach($category1_array as $item){
						$filter_array1[]		= $mdb->prepare($board_field["fn_category1"]."=%s", $item );
					}
					$where_data[]		= " (".implode( ' OR ', $filter_array1).")";
				}else{
					$where_data[]		= $mdb->prepare($board_field["fn_category1"]."=%s",$category1);
				}
			}
			if(!empty($category2)){
				if(strpos($category2, ',') !== false){
					$category2_array		= explode(',',$category2);
					$filter_array2			= array();
					foreach($category2_array as $item){
						$filter_array2[]		= $mdb->prepare($board_field["fn_category2"]."=%s", $item);
					}
					$where_data[]		= " (".implode( ' OR ', $filter_array2).")";
				}else{
					$where_data[]		= $mdb->prepare($board_field["fn_category2"]."=%s",$category2);
				}
			}
			if(!empty($category3)){
				if(strpos($category3, ',') !== false){
					$category3_array		= explode(',',$category3);
					$filter_array3			= array();
					foreach($category3_array as $item){
						$filter_array3[]		= $mdb->prepare($board_field["fn_category3"]."=%s", $item);
					}
					$where_data[]		= " (".implode( ' OR ', $filter_array3).")";
				}else{
					$where_data[]		= $mdb->prepare($board_field["fn_category3"]."=%s",$category3);
				}
			}

			if(!empty($board_field[mbw_get_param("search_field")])){
				$search_field		= $board_field[mbw_get_param("search_field")];
				$where_data[]		= $mdb->prepare($search_field." like %s",'%'.mbw_get_param("search_text").'%');
			}
			if(!empty($where_data)) $where_query				= " WHERE ".implode(" and ",$where_data);
			$board_pid			= intval($mdb->get_var("select pid from ".$mb_board_table_name.$where_query." ORDER BY pid DESC limit 1"));
			if(!empty($board_pid)) mbw_set_param("board_pid",$board_pid);
		}

		$board_mode			= mbw_get_param("mode");
		if(mbw_get_param($board_mode."_type")=="" && !empty($args[$board_mode.'_type'])) mbw_set_param($board_mode."_type", $args[$board_mode.'_type']);

		if($board_mode=="logout"){
			echo mbw_get_move_script("logout");
		}else{
			mbw_init_board_panel();
			if(empty($args['style'])) $board_style			= "";
			else $board_style			= ' style="'.str_replace('"',"'",$args['style']).'"';

			$file_path					= array();
			$file_path["base"]			= MBW_SKIN_PATH;
			$file_path["prefix"]		= "";
			$file_path["mode"]		= $board_mode;

			if(has_filter("mf_board_skin_path")) 
				$file_path		= apply_filters("mf_board_skin_path",$file_path);		
			
			$board_class	= '';
			if(function_exists('mbw_get_board_class')) $board_class	= mbw_get_board_class();
			
			echo '<div class="mb-'.mbw_get_vars("device_type").'">';
			echo '<div id="'.$mb_board_name.'_board" class="mb-board"'.$board_style.'>';
				echo '<div class="'.$board_class.'">';

					if(mbw_get_param("list_type")=="") mbw_set_param("list_type", $mstore->get_list_type());
					require($file_path["base"]."_header.php");
					do_action('mbw_board_header');
					$board_header		= mbw_get_board_option("fn_board_header");
					if(!empty($board_header)) echo do_shortcode($board_header);

					if(mbw_get_param($board_mode."_type")!="" && is_file($file_path["base"].$file_path["prefix"].mbw_get_param($board_mode."_type").".php")){
						require($file_path["base"].$file_path["prefix"].mbw_get_param($board_mode."_type").".php");
					}else if(is_file($file_path["base"].$file_path["prefix"].$file_path["mode"].".php"))
						require($file_path["base"].$file_path["prefix"].$file_path["mode"].".php");
									
					require($file_path["base"]."_footer.php");
					do_action('mbw_board_footer');
					$board_footer		= mbw_get_board_option("fn_board_footer");
					if(!empty($board_footer)) echo do_shortcode($board_footer);

				echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<div style="display:none !important;" class="mb-poweredby"><!-- 워드프레스 쇼핑몰 - 망보드 --><a href="https://www.mangboard.com" target="_blank" style="font-size:13px;" title="Powered by MangBoard">Powered by MangBoard</a></div>';
		}
	}
}
?>