<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------


class databaseModule extends AuthModule
{
	public function index()
	{		
		$sql_list = array();
		$db_back_dir = APP_ROOT_PATH."public/db_backup/";
		if($dir = opendir($db_back_dir))
		{
			while(($file   =   readdir($dir)))
			{
				if (($file!=".")&&($file!=".."))
				{
					if(is_dir($db_back_dir.$file))
					{
						$sql_list[$file] = array();
						if($bk_dir = opendir($db_back_dir.$file."/"))
						{
							while($bk_file=readdir($bk_dir))
							{
								if (($bk_file!=".")&&($bk_file!=".."))
									$sql_list[$file][] = $bk_file;
							}
						}
					}
				}
			}
		}
		
		$res = array();
		foreach($sql_list as $k=>$v)
		{
			$res[$k]['vol_count'] = count($v);
			$res[$k]['vol_count_lang'] = lang("TOTAL_COUNT_VOL",count($v));
			$res[$k]['backup_time'] = to_date($k);
		}

		$GLOBALS['tmpl']->assign("sql_list",$res);
		$GLOBALS['tmpl']->assign("dumpurl",admin_url("database#dump",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("restoreurl",admin_url("database#restore",array("ajax"=>1)));
		$GLOBALS['tmpl']->assign("delurl",admin_url("database#delete",array("ajax"=>1)));
		$GLOBALS['tmpl']->display("core/database/index.html");
	}	
	
	//关于SQL的备份
	private $max_size; //分卷的最大文件大小
	public function dump()
	{
		$this->max_size = app_conf("DB_VOL_MAXSIZE"); 
		error_reporting(0);
		set_time_limit(0);
		es_session::close();
		$filebase_name = strim($_REQUEST['filebase_name']);
		if($filebase_name=='')
			$filebase_name = NOW_TIME;
	
		$vol = intval($_REQUEST['vol']);
		$table_key = intval($_REQUEST['table_key']);
		$last_row = intval($_REQUEST['last_row']);
		$this->vol_dump($filebase_name,$vol,$table_key,$last_row);
	}
	
	
	/**
	 *  生成备份文件头部
	 *
	 * @access  public
	 * @param   int     文件卷数
	 *
	 * @return  string  $str    备份文件头部
	 */
	private function make_head($vol)
	{
		/* 系统信息 */
		$sys_info['os']         = PHP_OS;
		$sys_info['web_server'] = $_SERVER["SERVER_SOFTWARE"];
		$sys_info['php_ver']    = php_sapi_name();
		$sys_info['mysql_ver']  = mysql_get_server_info();
		$sys_info['date']       = date('Y-m-d H:i:s');
	
		$head = "-- fanwe SQL Dump Program\r\n".
				"-- " . $sys_info['web_server'] . "\r\n".
				"-- \r\n".
				"-- DATE : ".$sys_info["date"]."\r\n".
				"-- MYSQL SERVER VERSION : ".$sys_info['mysql_ver']."\r\n".
				"-- PHP VERSION : ".$sys_info['php_ver']."\r\n".
				"-- Vol : ".$vol."\r\n\r\n\r\n";
	
		return $head;
	}
	
	//按vol进行循环调用本函数
	private function vol_dump($filebase_name,$vol=1,$table_key=0,$last_row=0,$dumpsql_vol='',$loop_limit=0)
	{
		$loop_limit++;
		$tables_all = $this->getTables();
		$tables = array();
		foreach($tables_all as $table)
		{
			if(preg_match("/".DB_PREFIX."/",$table))
			{
				array_push($tables,$table);
			}
		}
			
		if($loop_limit>50) //主要用于xdebug或其他对递归做上限限制的服务器
		{
			//超出了递归限制
			if($this->write_sql($filebase_name,$vol,$dumpsql_vol))
			{
				$vol++;
				$result['vol'] = $vol;  //下一卷的卷数
				$result['filebase_name'] = $filebase_name;
				$result['table_key'] = $table_key;
				$result['last_row'] = $last_row;
				$result['done'] = 0;    //全部结束
				$result['status'] = 1; //导出成功
				$result['table_total'] = count($tables);
				$result['table_name'] = $tables[$table_key];
				ajax_return($result);
			}
			else
			{
	
				$result['status'] = 0; //导出失败
				$result['table_name'] = $tables[$table_key];
				$result['info'] = "数据库备份失败";
				ajax_return($result);
			}
		}
			
		if($table_key>=count($tables))
		{
			//超出了表的最大限制
			if($this->write_sql($filebase_name,$vol,$dumpsql_vol))
			{
				$result['vol'] = $vol;  //下一卷的卷数
				$result['filebase_name'] = $filebase_name;
				$result['table_key'] = $table_key;
				$result['last_row'] = $last_row;
				$result['done'] = 1;    //全部结束
				$result['status'] = 1; //导出成功
				$result['table_total'] = count($tables);
				$result['table_name'] = $tables[$table_key];
				ajax_return($result);
			}
			else
			{
	
				$result['status'] = 0; //导出失败
				$result['table_name'] = $tables[$table_key];
				$result['info'] = "数据库备份失败";
				ajax_return($result);
			}
		}
			
		if($dumpsql_vol=='') //非递归调用，则卷数增加,创建卷头
			$dumpsql_vol = $this->make_head($vol);  //每一卷的SQL语句
	
		//开始表的循环
		$tbname = $tables[$table_key];
		$modelname=str_replace(DB_PREFIX,'',$tbname);
		$tbname_o = $tbname;
		$tbname = 	str_replace(DB_PREFIX,'%DB_PREFIX%',$tbname);
			
		if($last_row==0)
		{
			//开始创建表的语句
			$dumpsql_vol .= "DROP TABLE IF EXISTS `$tbname`;\r\n";  //用于表结构导出处理的Sql语句
			$tmp_arr = $GLOBALS['db']->getAll("SHOW CREATE TABLE `$tbname_o`");
			$tmp_sql = $tmp_arr[0]['Create Table'].";\r\n";
			$tmp_sql  = str_replace(DB_PREFIX,'%DB_PREFIX%',$tmp_sql);
			$dumpsql_vol .= $tmp_sql;   //表结构语句处理结束
		}
			
	
		if($modelname!='auto_cache')  //不备份自动缓存的数据
		{
			$limit_str = $last_row.",500";
			$rows = $GLOBALS['db']->getAll("select * from ".DB_PREFIX.$modelname." limit ".$limit_str);
		}
		if(!empty($rows)&&count($rows)>0)
		{
			foreach($rows as $row)
			{
				$dumpsql_row = "INSERT INTO `{$tbname}` VALUES (";   //用于每行数据插入的SQL脚本语句
				foreach($row as $col_value)
				{
					$dumpsql_row .="'".mysql_real_escape_string($col_value)."',";
				}
				$dumpsql_row=substr($dumpsql_row,0,-1);  //删除最后一个逗号
				$dumpsql_row .= ");\r\n";
				$dumpsql_vol.= $dumpsql_row;
				$last_row++;
			}
				
			//开始判断分卷长度
			if(strlen($dumpsql_vol)>$this->max_size)
			{
				//开始写入sql脚本
				if($this->write_sql($filebase_name,$vol,$dumpsql_vol))
				{
					$vol++;  //增加卷数
					$result['status'] = 1; //导出一卷成功
					$result['vol'] = $vol;  //下一卷的卷数
					$result['done'] = 0;    //未结束。还需继续导出
					$result['filebase_name'] = $filebase_name;
					$result['table_key'] = $table_key;
					$result['table_total'] = count($tables);
					$result['table_name'] = $tables[$table_key];
					$result['last_row'] = $last_row;
					ajax_return($result);
				}
				else
				{
	
					$result['status'] = 0; //导出失败
					$result['info'] = "数据库备份失败";
					ajax_return($result);
				}
			}
			else
			{
				//未超出分卷长度，递归调用
				$this->vol_dump($filebase_name,$vol,$table_key,$last_row,$dumpsql_vol,$loop_limit);  //进行递归
			}
		}
		else
		{
	
			//进入下一张表的查询
			$last_row = 0;
			$table_key++;
			$this->vol_dump($filebase_name,$vol,$table_key,$last_row,$dumpsql_vol,$loop_limit);  //进行递归
	
		}
	
	}
	
	//获取表
	private function getTables($dbName='') {
		if(!empty($dbName)) {
			$sql    = 'SHOW TABLES FROM '.$dbName;
		}else{
			$sql    = 'SHOW TABLES ';
		}
		$result =   $GLOBALS['db']->getAll($sql);

		$info   =   array();
		foreach ($result as $key => $val) {
			$info[$key] = current($val);
		}
		return $info;
	}
	
	private function write_sql($filebase_name,$vol,$dumpsql_vol)
	{
		//开始写入sql脚本
		$filepath = APP_ROOT_PATH."public/db_backup/".$filebase_name."/";   //导出的目录
		 
		if (!is_dir($filepath)) {
			if (!  mkdir($filepath))
				return false;
			@chmod($filepath, 0777);
		}
		$filename = $filebase_name."_".$vol.".sql";  //导出的sql名
		$rs = @file_put_contents($filepath.$filename,$dumpsql_vol);
		if($rs==0)
		{
			//导出失败
			for($ii=1;$ii<=$vol;$ii++)
			{
			@unlink($filepath.$filebase_name."_".$ii.".sql");
			}
			return false;
		}
		return true;
		}
	//end SQL备份
	
	//恢复备份
	public function restore()
	{
		set_time_limit(0);
		es_session::close();
		$groupname = strim($_REQUEST['file']);
		$vol = intval($_REQUEST['vol']);
		$db_back_dir = APP_ROOT_PATH."public/db_backup/".$groupname."/";
		$sql_list = $this->dirFileInfo($db_back_dir,".sql");
		$sql_list = $sql_list[$groupname];
	
		$fileItem = $sql_list[$vol];
		$sql = file_get_contents($db_back_dir.$fileItem['filename']);
		$sql = $this->remove_comment($sql);
		$sql = trim($sql);
		$sql = str_replace("\r", '', $sql);
		$segmentSql = explode(";\n", $sql);
		foreach($segmentSql as $itemSql)
		{
			if($itemSql!='')
			{
				$itemSql = str_replace("%DB_PREFIX%",DB_PREFIX,$itemSql);
				$GLOBALS['db']->query($itemSql,"SILENT");
			}
		}
	
		if($vol==count($sql_list))
		{
			$result['done'] = 1;
			$result['status'] = 1;
			save_log($groupname.lang('DB_RESTORE_SUCCESS'),1);
		}
		else
		{
			$vol++;
			$result['filename'] = $groupname;
			$result['vol'] = $vol;
			$result['status'] = 1;
		}
		ajax_return($result);
	
	
	}
	
	private function remove_comment($sql)
	{
		/* 删除SQL行注释，行注释不匹配换行符 */
		$sql = preg_replace('/^\s*(?:--|#).*/m', '', $sql);
	
		/* 删除SQL块注释，匹配换行符，且为非贪婪匹配 */
		//$sql = preg_replace('/^\s*\/\*(?:.|\n)*\*\//m', '', $sql);
		$sql = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql);
	
		return $sql;
	}
	//用于获取指定路径下的文件组
	private function dirFileInfo($dir,$type)
	{
		if(!is_dir($dir))
			return   false;
		$dirhandle=opendir($dir);
		$arrayFileName=array();
		while(($file   =   readdir($dirhandle))   !==   false)
		{
			if (($file!=".")&&($file!=".."))
			{
				$typelen=0-strlen($type);
				if	(substr($file,$typelen)==$type)
				{
					$file_only_name = substr($file,0,strlen($file)+$typelen);
					$file_name_arr = explode("_",$file_only_name);
					$file_only_name = $file_name_arr[0];
					$fileIdx = $file_name_arr[1];
					if($fileIdx)
					{
						$arrayFileName[$file_only_name][$fileIdx]=array
						(
								'filename'=>$file,
								'filedate'=>to_date($file_only_name)
						);
					}
					else
					{
						$arrayFileName[$file_only_name][]=array
						(
								'filename'=>$file,
								'filedate'=>to_date($file_only_name)
						);
					}
				}
			}
			 
		}
		//通过ArrayList类对数组排序
		foreach($arrayFileName as $k=>$group)
		{
			ksort($group);
			$arrayFileName[$k] = $group;
		}
	
		return   $arrayFileName;
	}
	//end SQL恢复备份

	
	public function delete()
	{
		$ajax = intval($_REQUEST['ajax']);
		$groupname = strim($_REQUEST['groupname']);
		
		$groupnames = explode(',', $groupname);
	
		foreach($groupnames as $n)
		{
			$db_back_dir = APP_ROOT_PATH."public/db_backup/".$n."/";
			$sql_list = $this->dirFileInfo($db_back_dir,".sql");
			$deleteGroup = $sql_list[$n];
			foreach($deleteGroup as $fileItem)
			{
				@unlink($db_back_dir.$fileItem['filename']);
			}
		
			$dir = opendir( $db_back_dir );
			closedir($dir);
			rmdir($db_back_dir);
		}
		save_log($groupname.lang('DELETE_SUCCESS'),1);
		showSuccess(lang('DELETE_SUCCESS'),$ajax);		
	}
	
	
	//以下为SQL的脚本操作
	public function sql()
	{
			$ajax = intval($_REQUEST['ajax']);
			$tables = $this->getTables();			
			$GLOBALS['tmpl']->assign('tables',$tables);			
			$formaction = admin_url("database#sql",array("ajax"=>1));
			$GLOBALS['tmpl']->assign('formaction',$formaction);
			
			$sql = "";
			if(isset($_REQUEST['sql']))
			{
				$sql = trim($_REQUEST['sql']);
			}
			$GLOBALS['tmpl']->assign("sql",$sql);
			$sqls = array();
			$sqls_t = array();
			if($sql)
			{
				$sqls_t = explode(";", $sql);				
			}
			foreach($sqls_t as $k=>$sql_t_item)
			{
				if(trim($sql_t_item)!="")
					$sqls[] = trim($sql_t_item);
			}
			
			if(count($sqls)>0)
			{
				//开始执行SQL语句
				$sql_success="";
				$sql_error = "";
				$sql_success_count = 0;
				$sql_count = 0;
				$dataresult = array();
				$dataset = array();
				$startTime	=	microtime(TRUE);	
				$queryIps = 'INSERT|UPDATE|DELETE|REPLACE|'
						. 'CREATE|DROP|'
						. 'LOAD DATA|SELECT .* INTO|COPY|'
								. 'ALTER|GRANT|TRUNCATE|REVOKE|'
										. 'LOCK|UNLOCK';
				foreach($sqls as $sql_item)
				{				
					$sql_item = trim($sql_item);
					if($sql_item!="")
					{
						$sql_count++;
						if (preg_match('/^\s*"?(' . $queryIps . ')\s+/i', $sql_item))
						 {
							$GLOBALS['db']->query($sql_item,"SILENT");	
							if($GLOBALS['db']->error()!="")
							{
								$sql_error.=$sql_item." <span style='color:red;'>".$GLOBALS['db']->error()."(".$GLOBALS['db']->errno().")</span><br />"; //获取SQL错误
							}
							else
							{
								$sql_success.=$sql_item.";";
								$sql_success_count++;
							}				
						}
						else
						 {
							$db_res = $GLOBALS['db']->query($sql_item,"SILENT");
							if($GLOBALS['db']->error()!="")
							{
								$sql_error.=$sql_item." <span style='color:red;'>".$GLOBALS['db']->error()."(".$GLOBALS['db']->errno().")</span><br />"; //获取SQL错误
							}
							else
							{
								$sql_success_count++;
							}							
							if ($db_res !== false)
							{
								$dataresult = array();
								while ($row = mysql_fetch_assoc($db_res))
								{
									foreach($row as $rk =>$rv)
									{
										$row[$rk] = addslashes(htmlspecialchars($rv));
									}
									$dataresult[] = $row;
								}
							}
							
							
							
						}
						
					}
				}			
				
				if($sql_success!="")
				save_log(lang("SUCCESS_EXECUTE_SQL")."：".$sql_success, 1);
				
				$runtime	 =	 number_format((microtime(TRUE) - $startTime), 6);							
				$total = count($dataresult);
				
				if($sql_error!="")
				{					
					$sql_error = lang("SQL_ERROR_TIP",$sql_count,$sql_success_count,$runtime)."<br />".$sql_error;
					
					showErr($sql_error,$ajax);
				}
				//组装dataset			
				$dataset['runtime'] = 	lang("SQL_RUNTIME_TIP",$sql_count,$sql_success_count,$total,$runtime);
				if($total>0)
				{
					$dataset['cols'] = array();
					$dataset['rows'] = $dataresult;
					foreach($dataresult[0] as $kk=>$vv)
					{
						$dataset['cols'][] = $kk;
					}
				}

				$GLOBALS['tmpl']->assign("dataset",$dataset);
				//end 执行
			}
			$GLOBALS['tmpl']->display("core/database/sql.html");
	}

}
?>