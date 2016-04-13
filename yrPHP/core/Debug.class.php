<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:quinnh@163.com
 */
namespace core;
class Debug
{
    static $includeFile = array();
    static $info = array();
    static $queries = array();
    static $startTime;                //保存脚本开始执行时的时间（以微秒的形式保存）
    static $stopTime;                //保存脚本结束执行时的时间（以微秒的形式保存）


    /**
     * 在脚本开始处调用获取脚本开始时间的微秒值
     */
    static function start()
    {
        self::$startTime = microtime(true);   //将获取的时间赋给成员属性$startTime
    }

    /**
     *在脚本结束处调用获取脚本结束时间的微秒值
     */
    static function stop()
    {
        self::$stopTime = microtime(true);   //将获取的时间赋给成员属性$stopTime
    }

    /**
     * 添加调试消息
     * @param    string $msg 调试消息字符串
     * @param    int $type 消息的类型
     */
    static function addMsg($msg, $type = 0)
    {
        if (defined("DEBUG") && DEBUG == 1) {
            switch ($type) {
                case 0:
                    self::$info[] = $msg;
                    break;
                case 1:
                    self::$includeFile[] = $msg;
                    break;
                case 2:
                    self::$queries[] = $msg;
                    break;
            }
        }
    }

    /**
     * 输出调试消息
     */
    static function message()
    {
        $uri = loadClass('core\Uri');
        $mess = "";
        $mess .= '<div style="clear:both;font-size:12px;width:97%;margin:10px;padding:10px;background:#ddd;border:1px solid #009900;z-index:100">';
        $mess .= '<div style="float:left;width:100%;"><span style="float:left;width:200px;"><b>运行信息</b>( <font color="red">' . self::spent(STARTTIME,microtime(true)) . ' </font>秒):</span><span onclick="this.parentNode.parentNode.style.display=\'none\'" style="cursor:pointer;float:right;width:35px;background:#500;border:1px solid #555;color:white">关闭X</span></div><br>';
        $mess .= '<ul style="margin:0px;padding:0 10px 0 10px;list-style:none">';
        if (count(self::$includeFile) > 0) {
            $mess .= '自动包含 ' . count(self::$includeFile) . ' 个文件';
            foreach (self::$includeFile as $v) {
                $mess .= '<li>['.$v['time'].'秒]&nbsp;&nbsp;&nbsp;&nbsp;' . $v['path'] . '</li>';
            }
        }
        self::$info[] = '内存使用：<strong style="color:red">' . round(memory_get_usage()/1024, 2) . ' KB</strong>';
        self::$info[] = 'URI字符串：' . implode('/', $uri->segment());
        self::$info[] = 'URI路由地址：' . implode('/', $uri->rsegment());
        if (count(self::$info) > 0) {
            $mess .= '<br>［系统信息］';
            foreach (self::$info as $info) {
                $mess .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;' . $info . '</li>';
            }
        }
        // Key words we want bolded
        $highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

        $mess .= '<br>［SQL语句］';
        foreach (self::$queries as $val) {
            $sql = highlightCode($val['sql']);
            foreach ($highlight as $bold) {
                $sql = str_replace($bold, '<strong>' . $bold . '</strong>', $sql);
            }
            $mess .= '<li style="word-wrap:break-word;word-break:break-all;overflow: hidden;">[' . $val['time'] . ' 秒]&nbsp;&nbsp;&nbsp;&nbsp;' . $sql;

            $mess .= empty($val['error']) ? "" : '<strong style="color: red">Error:</strong>&nbsp;&nbsp;' . $val['error'];
            $mess .= '</li>';
        }

        $mess .= '</ul>';
        $mess .= '</div>';

        return $mess;
    }

    /**
     *返回同一脚本中两次获取时间的差值
     */
    static function spent($startTime=null,$stopTime=null)
    {
        $startTime = empty($startTime)?self::$startTime:$startTime;
        $stopTime = empty($stopTime)?self::$stopTime:$stopTime;
       // return round((self::$stopTime - self::$startTime), 4);  //计算后以4舍5入保留4位返回
        return sprintf("%1\$.4f",($stopTime - $startTime));  //计算后保留4位返回
    }
}
