<?php
/**
 * 载入视图的缩略写法
 */
function lv($view, $vars = array(), $return = FALSE)
{
	$CI =& get_instance();
	
	if($return)
	{
		return $CI->load->view($view, $vars, true);
	}
	else
	{
		$CI->load->view($view, $vars, false);
	}
}

function g($key,$clean = false)
{
	$CI =& get_instance();
	return $CI->input->get($key,$clean);
}

function p($key,$clean = false)
{
	$CI =& get_instance();
	return $CI->input->post($key,$clean);
}


/**
 * 载入模型文件的缩略写法
 */
function lm($model, $name = '', $db_conn = FALSE)
{
	$CI =& get_instance();
	
	$CI->load->model($model, $name, $db_conn);
}

/**
 * 载入类库文件的缩略写法。
 */
function ll($library = '', $params = NULL, $object_name = NULL)
{
	$CI =& get_instance();
	
	$CI->load->library($library, $params, $object_name);
}

?>