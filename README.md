#目录结构
>www  WEB部署目录（或者子目录）
├─index.php       入口文件
├─README.md       README文件
├─application     应用目录
├─public          资源文件目录
└─yrPHP           框架目录
│  ├─common      核心公共函数目录
│  ├─config      核心配置目录
│  ├─core        核心类库目录
│  ├─lang        核心语言包目录
│  ├─libs        框架类库目录
│  ├─resource    核心资源文件目录

#人口文件
index.php
```php
    <?php
    //是否开启调试模式,默认不开启
    define('DEBUG',true);
    //定义项目目录
    define("APP", 'application');
    //框架入口文件
    include './yrPHP/base.php';
```

> 注意：APP的定义必须是当前目录下的文件名,不需要标明路径

###系统核心常量

|  常量 |  描述 |
| ------------ | ------------ |
|ROOT_PATH   | 项目根路径绝对路径  |
|BASE_PATH   | 框架目录绝对路径  |
|APP_PATH   | 用户项目目录绝对路径 |
|CORE_PATH   | 框架核心类库目录绝对路径 |
|LIBS_PATH   | 框架集成常用类库目录绝对路径 |
|APP_MODE    | 应用模式  |
|DEBUG   |  是否开启调试模式 （默认false）  |

#核心

## URI及路由

##### URI 段

URL支持普通模式和PATHINFO模式，默认采用PATHINFO模式

根据模型-视图-控制器模式，在此 URL 段一般以如下形式表示：
example.com/class/function/ID

1. 第一段表示调用控制器**类**。
2. 第二段表示调用类中的**函数**或方法。
3. 第三及更多的段表示的是传递给控制器的**参数**，如 ID 或其他各种变量。

##URL模式
这种URL模式就是系统默认的PATHINFO模式，不同的URL模式获取模块和操作的方法不同，yrphp支持的URL模式有三种：普通模式、PATHINFO模式、REWRITE重写模式 可以通过设置 config/config.php 文件，配置$config[‘url_model’] 参数改变URL模式。

|  URL模式 |  url_model设置 |
| ------------ | ------------ |
|0   | 普通模式  |
|1   | PATHINFO模式  |
|2   | REWRITE重写模式 |


1. 普通模式：example.com?c=class&m=function
普通模式通过GET获得测试
```php
$config['controller_trigger'] = 'c'; //控制器名
$config['function_trigger'] = 'm'; //方法名
```
2.PATHINFO模式：如上
3.REWRITE重写模式：
默认情况下，index.php 文件将被包含在你的 URL 中：
example.com/index.php/news/article/my_article

你可以很容易的通过 .htaccess 文件来设置一些简单的规则删除它。下面是一个例子，使用“negative”方法将非指定内容进行重定向：

```php
RewriteEngine on
 RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* index.php
```

在上面的例子中，可以实现任何非 index.php、images 和 robots.txt 的 HTTP 请求都被指向 index.php。


## 添加 URL 后缀

通过设置 config/config.php 文件，你可以为 yrphp 生成的 URL 添加一个指定的文件后缀。举例来说，如果 URL 是这样的：

example.com/index.php/products/view/shoes

你可以随意添加一个后缀，例如 .html，使其显示为：

example.com/index.php/products/view/shoes.html

你只需修改config/config.php 文件中
```php
$config['urlSuffix'] = '.html';
```

## 路由定义：

路由规则定义在/config/routes.php 文件中. 在此文件中，你可以看到一个名为 $route的数组，它可以让你定义你自己的路由规则。 定义可以用 正则表达式(Regular Expressions)

####例子

下面是一些简单的例子:
```php
$route['news/(\d*)'] = 'article/news/:1';
```
以上配置 访问 news/1 则实际访问的是article/news/1

> 注意:  如果你使用逆向引用请将双反斜线语法替换为:语法（\\\1 替换为 :1).



#控制器

例子：创造一个控制器
在APP目录下的controls目录下创建一个名为:
Test.class.php的文件

```php
    <?php
    use core\Controller;

    class Test extends Controller
    {
        function __construct()
        {
            parent::__construct();
        }

        function  index()
        {
          echo "Hello World";
        }
```

接着我们用浏览器打开 example.com/index.php/test
就可以看到 Hello World

##命名空间
```php
use core\Controller;
```
表示引入 core\Controller 命名空间便于直接使用。所以，

```php
use core\Controller;
class Test extends Controller
```

等同于使用：
```php
class Test extends \core\Controller
```
##规则
1. 文件名必须是：***类名***.class.php
2. ***类名首字母必须大写***
3. 必须继承Controller类，可以重写Controller类（这在扩展中再说）

#配置

默认的配置文件在BASE_PATH/config/config.php
如需修改相关配置

如果设置了***APP_MODE***
则在APP_PATH/config***_APP_MODE***.php中修改相关配置
否则
在APP_PATH/config_APP.php中修改相关配置

yrPHP框架中所有配置文件的定义格式均采用返回**PHP数组**的方式，格式为：
```php
//项目配置文件
return array(
       'url_model'  => '2', //URL访问模式
       'default_controller' => 'Index', // 默认控制器名称
       //更多配置参数
       //...);
```
##读取配置

无论何种配置文件，定义了配置文件之后，都统一使用系统提供的C方法（可以借助Config单词来帮助记忆）来读取已有的配置。

获取已经设置的参数值：**C('参数名称')**
```php
$charset = C('charset');//获得配置中的编码格式
```

如果`charset`尚未存在设置，则返回NULL。

> 支持设置默认值例如：

```php
C('my_config',null,'default_config');
```

>如果不传参数 则返回所有配置信息

```php
$config = C();//return array;
```

##动态配置

>设置新的值 如果存在则覆盖，否则新建：

```php
C('参数名称','新的参数值');
```

```php
C("openCache",false);//关闭数据库缓存，只在该次请求有效
```

####批量设置：

```php
C(array($key=>$value,$key1=>$value1));
```

#视图

##配置
```php
'modelDir' => APP_PATH . "models/", //设置模型目录位置
/*--------------------以下是模版配置---------------------------------------*/
'setTemplateDir' => APP_PATH . "views/", //设置模板目录位置
'setCompileDir' => APP_PATH . "runtime/compile_tpl/", //设置模板被编译成PHP文件后的文件位置
'auto_literal' => false, //忽略限定符周边的空白
'caching' => 1, //缓存开关 1开启，0为关闭
'setCacheDir' => (APP_PATH . "runtime/cache/"), //设置缓存的目录
'cache_lifetime' => 60 * 60 * 24 * 7, //设置缓存的时间 0表示永久
'left_delimiter' => "{", //模板文件中使用的“左”分隔符号
'right_delimiter' => "}", //模板文件中使用的“右”分隔符号
```

###加载视图

```php
$this->display('name');
```

 上面的 <var>name</var> 便是你的视图文件的名字 如 index.html。

### 给视图添加动态数据
```php
$this->assign('name','yrPHP');//赋值单个数据
//等同于
$this->display('name',array('name'=>'yrPHP'));
```

#模版
##变量输出
在模板中输出变量的方法很简单，例如，在控制器中我们给模板变量赋值：
```php
{=$test}
```
模板编译后的结果就是：
```php
<?php echo $test;?>
```

##输出函数返回值

```php
{=getUrl('public/css/style.css')}
```

>注意模板标签的`{`和`=`之间不能有任何的空格，否则标签无效。

##运算符
```php
{$i++}
{$i--}
{--$i}
{++$i}
```

## 包含文件
```php
{include header.html}

{require footer.html}
```
##赋值
```php
{assign $name='yrPHP'}
{assign $name = 'yrPHP'}
```
>注意模板标签的`assign`和`$`之间必须有空格，否则标签无效。

####将函数赋值
```php
{assign $config = C()}
```

##判断
```php
{assign $i=10}
{if($i>=90)}
优秀
{elseif($i>=80)}
良
{else if( $i >= 60 )}
及格
{else}
不及格
{/if}
```


##循环
####foreach
```php
{assign $config = C()}
{foreach $config as $k=>$v}
<tr>
    {if ($k=='openCache')}
    {break}
    {/if}
    <td>{=$k}</td>
    <td>{=$v}---</td>

</tr>
{/foreach}
```

###for
```php
{for($i=0;$i<10;$i++)}
{if($i==5)}
{continue}
{/if}
{=$i}
<br/>
{/for}
```
###while
```
{assign $i=10}
{while($i)}
{=$i}
</br>
{$i--}
{/while}
```

##使用php代码
```php
<?php echo "Hello World";?>
```

##自定义标签
```php
<?php
use core\Controller;

class MyController extends Controller
{
    function __construct()
    {
        parent::__construct();

        $this->rule =array(
		             ['/'.C('left_delimiter').'=dump\(.*\)'.C('right_delimiter').'/']=> '<?php var_dump(\\1);?>',
					 //更多规则
					 );
    }
	}
```




#模型

##数据库配置
```php
<?php

return array(
    //主服务器
  'masterServer' => array(
  'dsn' => '',
  'dbDriver' => 'pdo', // 数据库类型
  'dbType' => 'mysql', // 数据库类型
  'dbHost' => '', // 服务器地址
  'dbName' => '', // 数据库名
  'dbUser' => '', // 用户名
  'dbPwd' => '', // 密码
  'dbPort' => '3306', // 端口
  'tablePrefix' => '', // 数据库表前缀
  'charset' => 'utf8',
  ),
  //从服务器可以配置多个,也可以不配置，不做读写分离
  'slaveServer' => array(
   array(
  'dsn' => '',
  'dbDriver' => 'pdo', // 数据库类型
  'dbType' => 'mysql', // 数据库类型
  'dbHost' => '', // 服务器地址
  'dbName' => '', // 数据库名
  'dbUser' => '', // 用户名
  'dbPwd' => '', // 密码
  'dbPort' => '3306', // 端口
  'tablePrefix' => '', // 数据库表前缀
  'charset' => 'utf8',
  ),
  array(
  'dsn' => '',
  'dbDriver' => 'pdo', // 数据库类型
  'dbType' => 'mysql', // 数据库类型
  'dbHost' => '', // 服务器地址
  'dbName' => '', // 数据库名
  'dbUser' => '', // 用户名
  'dbPwd' => '', // 密码
  'dbPort' => '3306', // 端口
  'tablePrefix' => '', // 数据库表前缀
  'charset' => 'utf8',
  ),
  ),
);

````

>数据库配置模版文件在BASE_PATH/config/database.php
如需修改相关配置
>
如果设置了***APP_MODE***
则在APP_PATH/database**__APP_MODE**.php中修改相关配置
否则
在APP_PATH/database.php中修改相关配置

##模型定义

> 模型类并非必须定义，只有当存在独立的业务逻辑或者属性的时候才需要定义。
文件名为**模型名.class.php**  UserModel的文件名为**UserModel.class.php**

模型类通常需要继承系统的\core\Model类或其子类，下面是一个Model\UserModel类的定义：



```php
namespace Model;
use core;

class UserModel extends Model
{

    public function __construct()
    {
        parent::__construct();
  }

}
```
##模型实例化
##### Model(['模型名']);
>模型名是为选填 如果为空则实例化父类。

```php
loadClass('Model\UserModel');//实例化UserModel模型
```

>实例化请确保参数确定 区分大小写

#CURL
##Active Record 模式

####添加数据INSERT
> **$this->insert([添加的数据],[表名]，[是否自动添加前缀bool]);**

```php
namespace Model;
use core;
class UserModel extends Model
{
    public function __construct()
    {
        parent::__construct();
  }

    public function userInsert()
    {
      return $this->insert([添加的数据],[表名]，[是否自动添加前缀bool]);
	   //return int 受影响行数
  }
}

```
>添加的数据如果为空,则获取$_POST数据，默认开启验证，如果数据库不存在 则过滤
如果有临时关闭则 $this->setoptions(array('_validate'=>false));
表名如果为空，则调用上次调用的表名($this->tableName)
是否自动添加前缀 默认 true

------------


####删除数据DELETE

> **$this->delete(条件，[表名]，[是否自动添加前缀bool]);**

**在自定义模型在调用**
```php
<?php
namespace Model;
use core;
class UserModel extends Model
{

    public function __construct()
    {
        parent::__construct();
  }

    public function userDelete()
    {
     return $this->delete(条件，[表名]，[是否自动添加前缀bool]);
     //return int 受影响行数
  }
}

```
>条件为array|string 推荐array
表名如果为空，则调用上次调用的表名
是否自动添加前缀 默认 true


------------


***在控制器在调用***
```php
    <?php
    use core\Controller;

    class Users extends Controller
    {
        function __construct()
        {
            parent::__construct();
        }

       //直接调用父类model，进行操作
        function  model()
        {
		 $db = Model();
		 $db->delete(条件，[表名]，[是否自动添加前缀bool]);

        }
       //实例化刚才创建的模型，操作其方法
		function  userModel()
        {
         $db = Model('UserModel');
		 $db->userDelete();
        }
```

####修改数据
```php
$this->update(array 数据，array 条件，[表名]，[是否自动添加前缀bool]);
//return int 受影响行数
```
>条件为array|string 推荐array
表名如果为空，则调用上次调用的表名
是否自动添加前缀 默认 true

####数据验证
>如果 $this->_validate = true 则验证添加或修改的数据

```php
<?php
namespace Model;
use core;
class UserModel extends Model
{

    public function __construct()
    {
        parent::__construct();

        $this->_validate = true;
        $this->validate=array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
/*
*附加规则:
* require:值域:null 当为空时return false
* equal:值域:int 当不等于某值时return false
* notequal:值域:int 当等于某值时return false
* in:值域:array(1,2,3)|1,2,3 当不存在指定范围时return false
* notin: 值域:array(1,2,3)|1,2,3  当存在指定范围时return false
* between: 值域:array(1,30)|1,30 当不存在指定范围时return false
* notbetween:值域:array(1,30)|1,30 当存在指定范围时return false
* length:值域:array(10,30)|10,30 当字符长度小于10，大于30时return false || array(30)|30 当字符不等于30时return false
* unique:值域:string 当该字段在数据库中存在该值域时 return false
* preg:值域:正则表达式 //当不符合正则表达式时 return false
*/
  }

    public function userDelete()
    {
     return $this->insert([添加的数据],[表名]，[是否自动添加前缀bool]);
     //return int 受影响行数
  }
}

```

####查询数据

**GET**
>**get($tableName = "", $auto = true)
string $tableName 表名
$auto 是否自动添加表前缀**

------------
```php
$this->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select * from `tableName`;
```

**SELECT**

>**select($field ='', $safe = true)
$field string|array 字段
$safe bool FALSE，就可以阻止数据被转义**

------------


```php
$this->select('field1,field2,field3')->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select `field1`,`field2`,`field3` from `tableName`;

$this->select(array('field1','field2','field3'))->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select `field1`,`field2`,`field3` from `tableName`;

$this->select(array('field1','field2','field3'),false)->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select field1,field2,field3 from `tableName`;
```

**LIMIT**

>**limit($offset, $length = null)
$offset 起始位置
$length 查询数量**

------------

```php
//查询一条数据
$this->limit(1)->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select * from `tableName` limit 1;
```

**WHERE**
>**where($where = '', $logical = "and")
 @param $logical 与前一个条件的连接符
 @param $where string|array
string "id>'100'"   `->`     where id>'100'**
>**
一维数组 array($field=>$value) `->` where  \`field\` = 'value'
$value is null `->` where  \`field\` is null
$value string ‘not null’   `->` where  \`field\` is not null**


>**二维数组 array('field'=>array($value,$symbol,$logical))
filed 字段
$value 值 string|int|null|‘not null’
$symbol 运算符 =|!=|<>|>|<|like|is|between|not between|in|not in
$logical or|and 与前一个条件的连接符 默认调用`$logical`
**

```php

$this->where("id='100'")->get([表名]，[是否自动添加前缀bool]);
//生成的SQL语句
//select * from `tableName` where （id = '100'）;

$this->->where("id='1659'")->where(array('id'=>array('1113','!='),'name'=>array('%nathan%','like')))->get('users');//前缀在config/database.php 设置 tablePrefix
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` where (id='1659') or ( `id` != '1113'  or  `name` like '%nathan%' )


$this->where("id='1596'")->where(array('id'=>array('1113','!='),'fullname'=>array('%nathan%','like','or'),
'update_time'=>array('10000 and 100000000','between','and')))->get('users');
//前缀在config/database.php 设置 tablePrefix
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` where (id='1596') and ( `id` != '1113'  or  `fullname` like '%nathan%'  and  `update_time` between '10000' and '100000000' )

$this->where(array('id'=>array('1,2,3,4,5,6,7,8,9,10','in')))->get('users');
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` where ( `id` in(1,2,3,4,5,6,7,8,9,10))
```
>where 可以用连贯查询 一组where会用()包含

**ORDER**
```php
$this->order('id desc')->get('users');
//生成的SQL语句
 SELECT  *  FROM  `yrp_users` ORDER BY `id` desc
```

**GROUP**
```php
$this->order('ip')->get('users');
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` `GROUP BY `ip`
```

**HAVING**
>同WHERE

```php
$this->group('id')->having(array('id'=>array('2000','>')))->get('users');
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` GROUP BY `id` having ( `id` > '2000' )
```

**JOIN**
>**join($table, $cond, $type = '', $auto = true)
 @param $table 表名
 @param $cond  连接条件
 @param string $type 连接方式
 @param bool $auto 是否自动添加表前缀**


```php
$this->join('users as b', 'a.id=b.id', 'left')->get('users as a');
//生成的SQL语句
//SELECT  *  FROM  `yrp_users` as `a` LEFT JOIN `yrp_users` as `b` ON `a`.`id`=`b`.`id`
```

##计算

**统计COUNT**
>**count($tableName,$auto = true)
$tableName 表名
$auto 是否自动添加前缀 bool 默认true**

```php
$this->count('users');
//同
$this->select('count(*) as count')->get('users')->row()->count;
//生成的SQL语句
//SELECT COUNT(*) as `count` FROM  `yrp_users`


```

**最大值MAX**
>**max($tableName,$field,$auto = true)
$tableName 表名
$field 字段名 不能为空
$auto 是否自动添加前缀 bool 默认true**

```php
$this->max('users','id');
//同
$this->select('max(id) as max')->get('users')->row()->max;
//生成的SQL语句
//SELECT MAX(id) as `max` FROM  `yrp_users`
```

**最小值MIN**
>**min($tableName,$field,$auto = true)
$tableName 表名
$field 字段名 不能为空
$auto 是否自动添加前缀 bool 默认true**

```php
$this->min('users','id');
//同
$this->select('min(id) as min')->get('users')->row()->min;
//生成的SQL语句
//SELECT MIN(id) as `min` FROM  `yrp_users`
```

**累计值SUM**
>**sum($tableName,$field,$auto = true)
$tableName 表名
$field 字段名 不能为空
$auto 是否自动添加前缀 bool 默认true**

```php
$this->sum('users','id');
//同
$this->select('sum(id) as sum')->get('users');
//生成的SQL语句
//SELECT SUM(id) as `sum` FROM  `yrp_users`
```

**平均值SUM**
>**sum($tableName,$field,$auto = true)
$tableName 表名
$field 字段名 不能为空
$auto 是否自动添加前缀 bool 默认true**

```php
$this->avg('users','id');
//同
$this->select('avg(id) as avg')->get('users');
//生成的SQL语句
//SELECT AVG(id) as `avg` FROM  `yrp_users`
```
##查询结果返回

####row($assoc = false) 查询一条结果
>**@param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object  当查询价格为空时 返回false
**

```php
//查询一条数据 返回对象格式
$this->select('id')->where(array('id'=>1))->get('users')->row();
//返还一条数据 当查询结果为空时 返回false
//stdClass::__set_state(array( 'id' => '231', ))

//查询一条数据 返回数组格式
$this->select('id')->where(array('id'=>1))->get('users')->row(true);
//返还一条数据 当查询结果为空时 返回false
//array(1) { ["id"]=> string(3) "231" }
```

------------


####result($assoc = false) 查询一条结果
>**@param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object  当查询价格为空时 返回一个空的数组array()
**

```php
//查询所有数据 返回对象格式
$this->select('id')->get('users')->result();
//返还一条数据 当查询结果为空时 返回一个空的数组array()
//array ( 0 => stdClass::__set_state(array( 'id' => '1', )), 1 => stdClass::__set_state(array( 'id' => '2', )), 2 => stdClass::__set_state(array( 'id' => '3', )), .....)

//查询所有数据 返回数组格式
$this->select('id')->get('users')->result(true);
//返还所以数据 当查询结果为空时 返回一个空的数组array()
//array ( 0 => array ( 'id' => '1', ), 1 => array ( 'id' => '2', ), 2 => array ( 'id' => '3', ),....)
```

####rowCount() — 返回受上一个 SQL 语句影响的行数

```php
$db = Model();
$re = $db->select('id')->get('users')->result();
echo  $db->rowCount();//输出查询结果总条数
```

##query 操作SQL
```php
$db = Model();
$re = $db->query("select * from yrp_users")->result();
//查询 同 $db->get('yrp_users')

$re = $db->query("update yrp_users name='nathan' where id=500")->rowCount();
//修改 返回受影响的行数
```

##数据库缓存

####在配置文件中配置数据库相关配置

```php
return array(
/*--------------------以下是数据库配置---------------------------------------*/
'openCache' => true, //是否开启缓存
'defaultFilter' => 'htmlspecialchars', // 默认参数过滤方法 用于I函数过滤 多个用|分割stripslashes|htmlspecialchars
'dbCacheTime' => 0, //数据缓存时间0表示永久
'dbCacheType' => 'file', //数据缓存类型 file|memcache|memcached|redis
//单个item大于1M的数据存memcache和读取速度比file
'dbCachePath' => APP_PATH . 'runtime/data/',//数据缓存文件地址(仅对file有效)
'dbCacheExt' => 'php',//生成的缓存文件后缀(仅对file有效)

'memcache' => '127.0.0.1:11211',//string|array多个用数组传递 array('127.0.0.1:11211','127.0.0.1:1121')

'redis' =>'127.0.0.1:6379',//string|array多个用数组传递 array('127.0.0.1:6379','127.0.0.1:6378')
);
```

```php
$this->setCache(false);
//默认配置文件中openCache = true，临时关闭 可以用setCache 仅当前请求有效
```

##lastQuery() 查询上一条SQL语句
```php
$db = Model();
$re = $db->get('users')->result();
echo $db->lastQuery();
//select * from `yrp_users`
```

------------

#系统函数

```php
<?php 

/**
 * 获取和设置配置参数 支持批量定义  具体请看配置章节
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name = null, $value = null, $default = null){}

/**********************************************************/
/**
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param bool|true $indexPage 如果是REWRITE重写模式 可以不必理会 否则默认显示index.php
 * @return string
 */
getUrl($url = '', $indexPage = true){}

/**********************************************************/
/**
 * 获取语言 支持批量定义
 * @param null $key 语言关键词
 * @param null $value 配置值
 * @return array|null
 */
 function getLang($key = null, $value = null){}

/**********************************************************/
/**
 * 以单例模式实例化类
 * loadClass($className [, mixed $parameter [, mixed $... ]])
 * @param $className 需要得到单例对象的类名
 * @param $parameter $args 0个或者更多的参数，做为类实例化的参数。
 * @return  object
 */
 function loadClass(){}

/**********************************************************/
/**
 * 如果存在自定义的模型类，则实例化自定义模型类，如果不存在，则会实例化Model基类,同时对于已实例化过的模型，不会重复去实例化。
 * @param string $modelName 模型类名
 * @return object
 */
 function Model($modelName = ""){}

/**********************************************************/
 /**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param bool|false $default 默认值
 * @param null $filter 参数过滤方法 array|string 默认为系统配置中的defaultFilter
 * @return array
 */
 function I($name = '', $default = null, $filter = null){}

/**********************************************************/
/**
 * 管理session
 * @param string $key
 * @param string $val
 * @return bool
 */
session($key='',$val=''){};

//添加单个session
session('id','15');//$_SESSION['id'] = 15
//批量添加session
session(array('id'=>15,'name'=>'LiLei'));

//获得session
session('id');

//删除
session('id',null);

//清空session
session(null);

/**********************************************************/
/**
 * 管理cookie
 * @param string $key
 * @param string $val
 * @return bool
 */
cookie($key='',$val=''){};

//添加单个session
cookie('id','15');
//批量添加session
cookie(array('id'=>15,'name'=>'LiLei'));

//获得session
cookie('id');

//删除
cookie('id',null);

/**********************************************************/
/**
 * 判断是不是 AJAX 请求
 * 测试请求是否包含HTTP_X_REQUESTED_WITH请求头。
 * @return    bool
 */
function isAjaxRequest(){}

/**********************************************************/
/**
 * 判断是否SSL协议
 * @return boolean
 */
function isHttps(){}

/**********************************************************/
/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function requireCache($filename){}

/**********************************************************/
/**
 *base64编码压缩序列化数据
 * @param $obj
 * @return string
 */
 function mySerialize($obj = ''){}

 /**********************************************************/
/**
 * 反序列化
 * @param $txt
 * @return mixed
 */
function myUnSerialize($txt = ''){}

 /**********************************************************/
/**
 *404跳转
 * @param string $msg 提示字符串
 * @param string $url 跳转URL
 * @param int $time 指定时间跳转
 */
 function error404($msg = '', $url = '', $time = 3){}
```

------------
# 创建核心系统类

## 扩展核心类
要使用你自己的系统类替换默认类只需简单的将你自己的 .php 文件放入`APP_PATH`/core
文件的命名规则为`类名.class.php`,类名不能与系统核心类（`BASE_PATH`/core）下的类重名

####例：
新建一个名为`MyController.class.php`的文件，**注意文件名不能与系统核心类（`BASE_PATH`/core）下的文件重名**

```php
<?php
namespace core;

class MyController extends Controller
{

    public function __construct()
    {
        parent::__construct();

    }
	}
```

>新建一个控制器，继承我们扩展的控制器类

**例子：创造一个控制器**

在`APP_PATH`/controls目录下创建一个名为:
Test.class.php的文件

```php
    <?php
    use core\MyController;

    class Test extends MyController
    {
        function __construct()
        {
            parent::__construct();
        }

        function  index()
        {
          echo "Hello World";
        }
```

#创造自己的类库
将你自己的 .php 文件放入`APP_PATH`/libs
文件的命名规则为`类名.class.php`,类名不能与系统类库（`LIBS_PATH`）下的类重名

####例：

>在`APP_PATH`/libs文件夹中新建一个名问MyPage.class.php的类文件

```php
    <?php
    namespace libs;

    class MyPage
    {
        function __construct()
        {
            parent::__construct();
        }

        function  index()
        {
          echo "Hello World";
        }
```

>在控制器中调用

```php
    <?php
    use core\MyController;

    class Test extends MyController
    {
        function __construct()
        {
            parent::__construct();
        }

        function  index()
        {
         $class = loadClass('libs\MyPage');
		 $class->index();
        }
```

##loadClass($className)以单例模式实例化类
>请确保类名正确 **区分大小写**

