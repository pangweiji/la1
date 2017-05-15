<?php
/**
 * 公共函数库
 *
 */
if (! function_exists('lang')) {
	/**
	 *	转换语言
	 */
	function lang($text, $parameters = array())
	{
		return trans('system.'.$text, $parameters);
	}
}
