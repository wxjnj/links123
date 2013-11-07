<?php
/**
 +------------------------------------------------------------------------------
 * 参数过滤类，用于用递归或非递归的方式实现防XSS攻击和/或防SQL注入处理
 +------------------------------------------------------------------------------
 * @category   ORG
 * @package  ORG
 * @author    Rachel <wangruiqicn@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class StringSanitization {

	public  function mysqlHtmlEscapeRecursively($arr) {

		foreach ( $arr as $k => $v ) {

			if (is_string ( $v )) {

				$arr [$k] = $this->mysql_entities_fix_string ( $v );

			} else if (is_array ( $v )) {

				$arr [$k] = $this->mysqlHtmlEscapeRecursively ( $v );

			}

		}



		return $arr;



	}


	public function mysqlEscapeRecursively($data) {

		if (is_string ( $data )) {

				return $this->mysql_fix_string ( $data);

			} else {
		return $this->_mysqlEscapeRecursively($data);
	}

	}

	private function _mysqlEscapeRecursively($arr) {

		foreach ( $arr as $k => $v ) {

			if (is_string ( $v )) {

				$arr [$k] = $this->mysql_fix_string ( $v );

			} else if (is_array ( $v )) {

				$arr [$k] = $this->mysqlEscapeRecursively ( $v );

			}

		}



		return $arr;

	}



	public function htmlEscapeRecursively($arr) {

		foreach ( $arr as $k => $v ) {

			if (is_string ( $v )) {

				$arr [$k] = htmlentities ( $v, ENT_QUOTES, "UTF-8" );

			} else if (is_array ( $v )) {

				$arr [$k] = $this->htmlEscapeRecursively ( $v );

			}

		}



		return $arr;

	}



	public function mysql_fix_string($string) {

		if (get_magic_quotes_gpc ())

			$string = stripslashes ( $string );

		return addslashes( $string );

	}



	public function mysql_entities_fix_string($string) {

		return htmlentities ( $this->mysql_fix_string ( $string ) );

	}

	public function sanitize_html($string){

		return htmlentities ( $string, ENT_QUOTES, "UTF-8" );

	}
}



?>