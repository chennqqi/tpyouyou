<?php
class Voucher{
	/**
	 * 发放代金券
	 * @param integer $voucher_type_id 代金券类型ID
	 * @param unknown_type $user_data 用户数据，包含id,group_id,level_id
	 * @return string|Ambigous <boolean, string>|boolean
	 */
	public static function gen($voucher_type_id,$user_data)
	{
			$result['status'] = false;
			$voucher_type_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."voucher_type where is_effect = 1 and id = ".$voucher_type_id);
			if(empty($voucher_type_data))
			{
				$result['message'] = "代金券不存在";
				return $result;
			}
			else
			{
				if($voucher_type_data['deliver_rel_id']>0&&$voucher_type_data['deliver_type']==1)
				{
					//按会员等级
					if($user_data['level_id']!=$voucher_type_data['deliver_rel_id'])
					{
						$result['message'] = "未达到发放的等级要求";
						return $result;
					}
				}
				else if($voucher_type_data['deliver_rel_id']>0&&$voucher_type_data['deliver_type']==2)
				{
					if($user_data['group_id']!=$voucher_type_data['deliver_rel_id'])
					{
						$result['message'] = "不属于发放的会员组";
						return $result;
					}
				}
				
				if($voucher_type_data['deliver_end_time']>0&&$voucher_type_data['deliver_end_time']<NOW_TIME)
				{
					$result['message'] = "代金券过期不可领取";
					return $result;
				}
				
				$GLOBALS['db']->query("update ".DB_PREFIX."voucher_type set deliver_count = deliver_count + 1 where id = ".$voucher_type_id." and (deliver_count + 1 <=deliver_limit or deliver_limit = 0)");
				if($GLOBALS['db']->affected_rows()>0)
				{
					 //开始发放代金券
					 $voucher_data['voucher_name'] = $voucher_type_data['voucher_name'];
					 $voucher_data['create_time'] = NOW_TIME;
					 $voucher_data['end_time'] = $voucher_type_data['voucher_end_time'];
					 $voucher_data['user_id'] = $user_data['id'];
					 $voucher_data['money'] = $voucher_type_data['money'];
					 $voucher_data['voucher_type_id'] = $voucher_type_data['id'];
					 $voucher_data['is_effect'] = 1;
					 $GLOBALS['db']->autoExecute(DB_PREFIX."voucher",$voucher_data);
					 $result['status'] = true;
					 $result['data'] = $voucher_data;
					 
					 return $result;
				}
				else
				{
					$result['message'] = "代金券已经全部发放";
					return $result;
				}
			
			}
	}
}
?>