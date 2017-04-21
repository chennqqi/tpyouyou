<?php
// +----------------------------------------------------------------------
// | Fanwe 乐程旅游b2b
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.lechengly.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 同创网络(778251855@qq.com)
// +----------------------------------------------------------------------

class CacheMemcachedService extends CacheService
{

	private $mem;
    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct()
    {

    	if(!class_exists("Memcache"))
    	return false;
		$this->mem = new Memcache;
		$memcache_config = app_conf("MEMCACHE_HOST");
		$memcache_config = explode(":",$memcache_config);
		$host = $memcache_config[0];
		$port = $memcache_config[1]?$memcache_config[1]:'11211'; //默认端口为11211
		$this->mem->connect($host, $port);   //此处为memcache的连接主机与端口 
    }

    /**
     +----------------------------------------------------------
     * 读取缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name)
    {
    	if(!$this->mem)return false;
    	if(app_conf("CACHE_ON")==0||IS_DEBUG)return false;
    	$var_name = md5(APP_NAME."_".$name);    	
    	global $$var_name;
    	if($$var_name)
    	{
    		return $$var_name;
    	}    	
    	$data = $this->mem->get(APP_NAME."_".$name);
    	$$var_name = $data;
        return $data;
    }


    /**
     +----------------------------------------------------------
     * 写入缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
	public function set($name, $value,$expire ="-1")
    {
    	if(app_conf("CACHE_ON")==0||IS_DEBUG)return false;
    	if(!$this->mem)return false;
    	if($expire=='-1') $expire = 365*3600*24;

		$this->log_names(APP_NAME."_".$name);
		return $this->mem->set(APP_NAME."_".$name,$value,0,$expire);

    }

    /**
     +----------------------------------------------------------
     * 删除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name 缓存变量名
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function rm($name)
    {
    	if(!$this->mem)return false;
		return $this->mem->delete(APP_NAME."_".$name);
    }
    
    
    public function clear()
    {
		$names = $this->get_names();
		foreach($names as $name)
		{
			$this->rm($name);
		}
		$this->del_name_logs();
    }

}//类定义结束

?>