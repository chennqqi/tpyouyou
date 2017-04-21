<?php
/**
 * 获取商户信息
 */
function get_supplier($id){
	$supplier = $GLOBALS['db']->getRow('SELECT * FROM '.DB_PREFIX.'supplier WHERE id='.intval($id));
	if($supplier)
		unset($supplier['user_pwd']);
	return $supplier;
}
?>