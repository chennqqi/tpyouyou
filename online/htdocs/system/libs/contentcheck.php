<?php

define('FANWE_WORD_SUCCEED', 0);    //成功
define('FANWE_WORD_BANNED', 1);     //禁用
define('FANWE_WORD_REPLACED', 2);   //替换
class Contentcheck{
    
    /**
     * 检查敏感词 禁用和替换
     * @param type $content
     * @return string 1为禁用  ，2 为替换 0为没找到
     */
    public static function checkword(&$content){
        $words = load_dynamic_cache("word_cache");
        if($words===false)
        {
           $words = load_auto_cache("word_cache");
           set_dynamic_cache("word_cache", $words);
        }

        $limit_num = 1000; //每次取1000个数组
        $words_found = array();
        $result;
        
        //判断是否有设置，禁用词语
        if(is_array($words['banned']) && !empty($words['banned']))
        {        
                //遍历禁用词语
                foreach($words['banned'] as $banned_words)
                {
                        //正则匹配
                        if(preg_match_all($banned_words,$content, $matches))
                        {
                            //如果存在记入并返回
                                $words_found = $matches[0];
                                $result = FANWE_WORD_BANNED;
                                $words_found = array_unique($words_found);
                                return FANWE_WORD_BANNED;
                        }
                }
        }
        //判断是否有替换词语
        if(!empty($words['filter']))
        {
                $i = 0;
                //必循存在替换词语否则退出循环
                while($find_words = array_slice($words['filter']['find'], $i, $limit_num))
                {
                        if(empty($find_words))
                                break;
                        //取出替换结果数组
                        $replace_words = array_slice($words['filter']['replace'],$i,$limit_num); 
                        $i += $limit_num;
                        //进行替换
                        $content = preg_replace($find_words,$replace_words,$content);
                }
                //赋值返回结果为替换
                $result = FANWE_WORD_REPLACED;
                return FANWE_WORD_REPLACED;
        }
        //都不存在的情况下，返回0
        $result = FANWE_WORD_SUCCEED;
        return FANWE_WORD_SUCCEED;
    }
}
