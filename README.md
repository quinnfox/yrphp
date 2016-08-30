[TOC]
#简介
yrPHP运用大量的单例及工厂模式，确保用最少的资源做最多的事，采用了自动加载，基本上无需手动加载类库文件，还集成了缓存技术及页面静态化技术，确保运行速度及响应速度

#目录结构
www  WEB部署目录（或者子目录）
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
>系统会在第一次调用时 自动生成项目目录结构

#应用目录

www  WEB部署目录（或者子目录）
├─index.php       入口文件

├─application     应用目录

│  ├─controls    默认控制器目录

│  ├─models      默认模型目录

│  ├─models      默认视图目录

│  ├─common      自定义公共函数目录

│  ├─config      自定义配置目录

│  ├─core        自定义核心类库目录

│  ├─lang        自定义语言包目录

│  ├─libs        自定义类库目录

│  ├─runtime    缓存目录
.
.
.

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
example.com/file/.../file(n)/class/function/ID

1. 第一段表示调用控制器文件目录(可多级引导/file/..../file2/... 可省略 为控制器根目录 **所有目录名均为小写**)。
2. 第二段表示调用控制器**类**。
3. 第三段表示调用类中的**函数**或方法。
4. 第四及更多的段表示的是传递给控制器的**参数**，如 ID 或其他各种变量。

####获得URL
>getUrl($url,$indexPage);//如果参数为空 则返回现在所在所在的根目录如`http://example.com/index.php/news/index/id`
则返回 `http://example.com/`
否则返回拼接后的URL
`/**`
`* @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'`
`* @param bool|true $indexPage 如果是REWRITE重写模式 可以不必理会 否则默认显示index.php`
`* @return string`
`*/`

##解析URL (\core\Uri类)
**分析`http://example.com/index.php/news/index/id`**


####rsegment($n = null, $no_result = null)
>返回路由替换过后的uri 数组(也就是实际所访问的地址) 分割一个详细的URI分段。n 为你想要得到的段数
1. news
2. index
3. id
>
下标n从1开始 如果为空 则默认返回 $no_result

####rpart($n = null, $no_result = null)
>同rsegment($n = null, $no_result = null)

####segment($n = null, $no_result = null)
>返回没有经过路由替换的uri 数组(也就是现在所访问的地址) 分割一个详细的URI分段。n 为你想要得到的段数
1. news
2. index
3. id
>
下标n从1开始 如果为空 则默认返回 $no_result

####part($n = null, $no_result = null)
>同segment($n = null, $no_result = null)


####getPath()
>返回没有经过路由替换的uri 字符串(也就是现在所访问的地址)
/news/index/id

####getRPath()
>返回经过路由替换过后的uri 字符串(也就是实际所访问的地址)
/news/index/id

##URL模式
这种URL模式就是系统默认的PATHINFO模式，不同的URL模式获取模块和操作的方法不同，yrphp支持的URL模式有三种：普通模式、PATHINFO模式、REWRITE重写模式 可以通过设置 config/config.php 文件，配置$config[‘urlType’] 参数改变URL模式。

|  URL模式 |  urlType设置 |
| ------------ | ------------ |
|0   | 普通模式  |
|1   | PATHINFO模式  |
|2   | REWRITE重写模式 |


1. 普通模式：example.com?c=class&m=function
普通模式通过GET获得测试
```php
$config['ctlTrigger'] = 'c'; //控制器名
$config['actTrigger'] = 'm'; //方法名
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
       'urlType'  => '2', //URL访问模式
       'defaultCtl' => 'Index', // 默认控制器名称
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

##批量设置：

```php
C(array($key=>$value,$key1=>$value1));
```

##加载配置文件
```php
C(APP_PATH . 'config/config_test.php');
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
display($fileName, $tplVars = '', $cacheId = '');

>$fileName 提供模板文件的文件名
 $tpl_var 动态数据
 $cacheId 当为Boolean值true时则做为数据返回，不输出到屏幕，其他情况做为缓存ID,当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
 如果开启缓存 display方法会自动生成缓存文件 但常常我们的display方法会在最后调用 导致我们display之前的逻辑判断及数据读取做无用功 所以我们可以在 方法开头做判断 直接调用`init($cacheId)`  或则在构造函数中调用checkCacheId方法（系统已经自动调用 你无需再次调用 你只要**重写checkCacheId方法**就可），checkCacheId方法如下
 */

```php
    /**
     * 重写这个方法 在构造函数中调用
     * 缓存初始化 判断缓存ID是否合理 避免生成无用静态文件
     */
    private function checkCacheId(){
    if($this->caching){
        $act = C('actName');
        switch ($act){
            case "index":
                $param =$_GET['id'];
              //  if($param=='') error404('参数错误');

                break;
            default:
                $param = '';
                break;
        }
        $this->init($param);//$param 缓存ID
        }
        //同样 你也可以在方法开头做判断 不调用checkCacheId方法
    }
```

```php
$this->display('name');
```
>上面的 <var>name</var> 便是你的视图文件的名字 如 index.html。



### 给视图添加动态数据
```php
$this->assign('name','yrPHP');//赋值单个数据
//等同于
$this->display('name',array('name'=>'yrPHP'));
```

###视图缓存
>以下参数可用于视图缓存,可在控制器中重载

```
protected $caching = true;   //bool 设置缓存是否开启 配置中可设置
protected $cacheLifeTime = 3600;  //定义缓存时间 配置中可设置
protected $cacheDir;      //定义生成的缓存文件路径 配置中可设置
protected $cacheSubDir;   //定义生成的缓存文件的子目录默认为控制器名
protected $cacheFileName; //定义生成的缓存文件名 默认为方法名
private $cacheFile;      //最后形成的缓存完整路径 根据前面参数生成
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
{foreach ($config as $k=>$v)}
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
####在配置文件tabLib.php文件中自定义标签
```
/*
系统将自动添加定界符，其他同正则表达式
如下 在模版中调用方式为 {=dump $a}
*/
return array(
'=dump\s*(.*)\s*' => "<?php var_dump( \\1);?>",

);
```


####使用
```php
<?php
use core\Controller;

class MyController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
{
$data['arr'] = array(1,2,3,4,5,6);
$this->display('index.html',$data);
}

}
```
####在模版中调用
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TEST</title>
</head>
<body>
    {=dump $a}
</body>
</html>
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

## CURL
### Active Record 模式

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
如果验证有一个不通过则不提交或修改数据

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
* equal:值域:string|null 当值与之相等时，通过验证
* notequal:值域:string|null 当值与之不相等时 通过验证
* in:值域:array(1,2,3)|1,2,3 当值存在指定范围时 通过验证
* notin: 值域:array(1,2,3)|1,2,3  当不存在指定范围时 通过验证
* between: 值域:array(1,30)|1,30 当存在指定范围时 通过验证
* notbetween:值域:array(1,30)|1,30 当不存在指定范围时 通过验证
* length:值域:array(10,30)|10,30 当字符长度大于等于10，小于等于30时 通过验证 || array(30)|30 当字符等于30时 通过验证
* unique:值域:string 当该字段在数据库中不存在该值时 通过验证
* email： 值域：string 当值为email格式时 通过验证
* url： 值域：string 当值为url格式时 通过验证
* number: 值域：string 当值为数字格式时 通过验证
* regex:值域:正则表达式 //当符合正则表达式时 通过验证
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

>**select($field ='', $safe = false)
$field string|array 字段
$safe bool FALSE，是否添加限定符：反引号，默认false不添加**

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
//等同于
$this->where(array('id'=>array(array(1,2,3,4,5,6,7,8,9,10),'in')))->get('users');
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
//SELECT  *  FROM  `yrp_users` `GROUP BY `ip`
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

##事务

####要使用事务来运行你的查询, 你可以使用如下方法:
1. startTrans(); 开启事务
2. transComplete(); 自动判断错误 提交或则回滚
3. commit(); 事务提交
4. rollback(); 事务回滚

####属性
**public $transStatus;bool 事务是否发生错误**

```php
$this->startTrans();
$this->query('一条SQL查询...');
$this->query('另一条查询...');
$re = $this->query('还有一条查询...');

//手动定义错误
if($re = false){
$this->transStatus = false;
//当transStatus 为false时事务失败
}
$this->transComplete();
```
**或则**

```php
$this->startTrans();
$this->query('一条SQL查询...');
$this->query('另一条查询...');
$re = $this->query('还有一条查询...');

//手动定义错误
if($re = false){
$this->rollback();事务回滚
}else{
$this->commit();事务提交
}
/*
if($this->transStatus === false)｛
$this->rollback();
｝else{
$this->commit();
}
*/
```

##错误调试
```php
$db = Model();
$error = $db->error();//返回的是一个数组array
var_export($error);
```

##数据缓存
```php
//获得缓存实例 $dbCacheType 缓存驱动，有file memcache、memcached、redis,默认为file
$cache = core\cache::getInstance($dbCacheType = null);

/**
* 设置缓存
* @param string $key 要设置值的key
* @param string $val 要存储的数据
* @param null $timeout 有效期单位秒 0代表永久 默认为配置文件中的cache_lifetime
* @return bool
*/

$cache->set($key, $val, $timeout = null);


/**
* 获取缓存
* @param $key
* @return mixed
*/
$cache->get($key = null);

/**
* 根据key值删除缓存
* @param string $key
*/
$cache->del($key = null);

/**
* 清空所有缓存 慎用
* @return mixed
*/
$cache->clear();

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
 * @param string|array $name 配置变量 支持传入配置文件
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



#系统类库(/yrPHP/libs)
##加密类     Crypt
####配置密钥
>在`APP_PATH`.config/config.php下配置

```PHP
<?PHP
    return
    array('crypt_mode' => 'des3',//现在加密方式只有DES3
          'crypt_key' => '123456789',//密钥
          'crypt_iv' =>  '123456789',//初始向量
    );
```

####加密解密
```PHP
<?PHP
  $crypt = loadClass('\libs\Crypt');
  $crypt->encrypt($str);//加密数据
  $crypt->decrypt($str);//解密数据
```

##文件处理类 File

```php
<?php
/**
* 建立文件
*
* @param  string $aimUrl 文件地址
* @param  boolean $overWrite 该参数控制是否覆盖原文件
* @return  boolean
*/
\libs\File::createFile($aimUrl, $overWrite = false);

/**
 * 递归删除文件夹或文件
 * @param  string $aimDir 文件地址
 * @return  boolean
 */
\libs\File::rm($aimDir);

/**
 * 建立文件夹
 * @param  string $aimUrl 文件地址
 * @param  int    $mode 权限
 * @return  viod
 */
\libs\File::mkDir($aimUrl, $mode = 0777);

/**
 * 移动文件夹或文件
 * @param  string $oldDir 原地址
 * @param  string $aimDir 目标地址
 * @param  boolean $overWrite 该参数控制是否覆盖原文件
 * @return  boolean
 */
\libs\File::mv($oldDir, $aimDir, $overWrite = false)；

/**
 * 复制文件或则文件夹
 * @param  string $oldDir
 * @param  string $aimDir
 * @param  boolean $overWrite 该参数控制是否覆盖原文件
 * @return  boolean
 */
\libs\File::cp($oldDir, $aimDir, $overWrite = false)；

/**
 * 修改文件名
 *$path 需要修改的文件路径
 *$name 修改后的文件路径及文件名
 * @return    boolean
 */
\libs\File::rename($path, $name)；

/**
 * 将字符串写入文件
 * @param  string $filename 文件路径
 * @param  boolean $str 待写入的字符数据
 */
\libs\File::vi($filename, $str);

/**
 * 将整个文件内容读出到一个字符串中
 * @param  string $filename 文件路径
 * @return string
 */
\libs\File::readsFile($filename);

/**
 * 将文件内容读出到一个数组中
 * @param  string $filename 文件名
 * @return array
 */
\libs\File::readFile2array($filename);

/**
 * 根据关键词列出目录下所有文件
 * @param    string $path 路径
 * @param    string $key 关键词
 * @param    array $list 增加的文件列表
 * @return    array    所有满足条件的文件
 * 返回一个索引为结果集列名的数组
 */
\libs\File::dirList($path, $key = '', $list = array())；

/**
 * 根据关键词列出目录下所有文件
 *
 * @param    string $path 路径
 * @param    string $key 关键词
 * @param    array $list 增加的文件列表
 * @return    array    所有满足条件的文件
 * 返回一个索引为结果集列名和以0开始的列号的数组
 */
\libs\File::search($path, $key = '', $list = array())；

/**
 * 获取文件名后缀
 * @param    string $filename 文件路径
 * @return    string
 */
\libs\File::fileExt($filename)；

/**
 * 获得文件相关信息
 * @param $filename 文件路径
 * @return array|bool
 * 将会返回包括以下单元的数组 array ：dirname(文件实在目录)、basename(文件名带后缀)、extension（文件后缀
 * 如果有）、filename(文件名不带后缀)、dev(设备名)、ino(inode 号码)、mode(inode 保护模式)、nlink(被连接数
 * 目)、uid(所有者的用户 id)、gid(所有者的组 id)、rdev(设备类型，如果是 inode 设备的话)、size(文件大小的
 * 字节数)、atime(上次访问时间（Unix 时间戳）)、ctime(上次改变时间（Unix 时间戳）)、blksize(文件系统 IO
 * 的块大小)、blocks(所占据块的数目)。
 */
\libs\File::getFileInfo($filename);

/**
 * 统计目录大小
 * @param    string $dirname 目录
 * @return    string      比特B
 */
\libs\File::getDirSize($dirname)；

/**
 * 将字节转换成Kb或者Mb...
 * @param $size为字节大小
 */
\libs\File::bitSize($size)；

/**
 * 返回当前目录层级下所有文件及目录列表
 * @param    string $dir 路径
 * @return    array    返回目录列表
array (
  1 => 'application',
  2 => 'public',
  3 => 'yrPHP',
)

 */
\libs\File::dirNodeTree($dir);

/**
 * 递归循环目录列表，并返回关系层级
 * @param    string $dir 路径
 * @param    int $parentid 父id
 * @param    array $dirs 传入的目录
 * @return    array    返回目录及子目录列表

 array (
  1 =>
  array (
    'id' => 1,
    'parentid' => 0,
    'name' => 'application',
    'dir' => './application/',
  ),
  2 =>
  array (
    'id' => 2,
    'parentid' => 1,
    'name' => 'common',
    'dir' => './application/common/',
  ),
  ）
 */
\libs\File::dirTree($dir, $parentid = 0, $dirs = array())；

```

##文件上传类 Uoload
>支持多文件上传

####上传配置设置
|  key | 值选项  | 说明  |
| ------------ | ------------ | ------------ |
| maxSize  | int  | 最大的上传文件 KB 默认为0 不限制 　　注意：通常PHP也有这项限制，可以在php.ini文件中指定。通常默认为2MB。|
| savePath  | `/`  | 上传目录 默认`/`根目录 |
|  fileName | None  | 自定义上传文件后的名称，不含文件后缀   |
| allowedTypes  |array()  |  允许上传文件的后缀列表默认空数组为允许所有 |
|  isRandName | BOOL  |  设置是否随机重命名文件， false不随机 默认 true |
|  overwrite | BOOL  | 是否覆盖。true则覆盖，false则重命名 　默认false |

------------


####init($config)参数初始化

####upload($field)文件上传
>@param 表单名称 $field，上传文件的表单名称  如果为空则上传 $_FILES数组中所有文件

####getFileInfo($inputName=null);获得上传文件相关属性
>inputName 表单名 如果为多文件上传 则在表单名后面跟下标
如果inputName==null 则返回一个以表单名为键的多维数组 return array(inputName1=>array(),inputName2=>array(),...)
>
如果inputName表单名不为空 则返回该表单上传的文件信息 如果表单名错误 则 返回false
>
如果上传文件有错误 则return array('errorCode'=>错误代码)
>
否则 return 包括以下单元的数组 array ：fileName(最终文件名包含后缀)、fileType(文件mime类型)、filePath(包含文件名的完整路径)、origName(上传前的文件名)、fileExt(文件后缀)、 fileSize(文件大小KB)、isImage(是否是图片bool)、imgWidth(图片宽度)、imgHeight(图片高度)

####getError($errorCode = null)
>$errorCode 根据错误代码获得上传出错信息

```php
<?php
 $config = $config = array(
 'maxSize'=>100,
 'savePath'=>'/ttt',
 'isRandName'=>false,
 'allowedTypes'=>array('jpg','png')
 );
 //参数配置可以在实例化时就传入
        $up = loadClass('\libs\Upload',$config);
        $re = $up->upload('file123');

 //参数配置也可以在init方法中传入
        $up = loadClass('\libs\Upload');
        $re = $up->init($config)->upload('file123');
```

##图像处理类 Image
>支持连贯操作

####缩略图
```php
/**
缩略图
**/
$img = loadClass('libs\Image','D:/test.jpg');//实例化 并打开test.jpg图片，也可以用open方法打开图片

/**
* 获得图片的基本信息
* @return array(dirname,basename,extension,filename,width,height,type,mime)
*/
var_dump($img->getInfo());

$img=$img->thumb(array('width'=>100,'height'=>100,'pre'=>0.5));//如果设置了$config['per']则按照$config['per']比例缩放 否则按给定宽高

/**
* 直接在浏览器显示图片
* @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
* @return bool
*/
$img->show($type = null);//显示图片

/**
* 保存图像
* @param  string $imgname 图像保存名称
* @param  string $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
* @param  integer $quality 图像质量
* @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
*/
$img->save($imgPath='test1.jpg', $type = null, $quality = 80, $interlace = true);

/**
* 客服端下载
* @param null $downFileName 文件名 默认为原文件名
* @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
*/
$img->down($downFileName = null, $type = null);
```
####水印
```php
$img = loadClass('libs\Image');//实例化
$img->open('D:/test.jpg');//并打开test.jpg图片

/**
* 为图片添加文字水印
* @param    string $water array('str'=>'ok','font'=>'msyh.ttf','color'=>'#ffffff','size'=>20,'angle'=>0,)
* str水印文字为必填 font字体 color默认黑色 size文字大小默认20，angle文字倾斜度默认0 暂只支持GIF,JPG,PNG格式
* @param    int $position 水印位置，有10种状态，0为随机位置；
*                                1为顶端居左，2为顶端居中，3为顶端居右；
*                                4为中部居左，5为中部居中，6为中部居右；
*                                7为底端居左，8为底端居中，9为底端居右；
*                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
* @return    mixed
*/
$img->text($water = array(), $position = 0);
//其他 显示 下载 保存同上
/*************************************************************/

$img = loadClass('libs\Image','D:/test.jpg');//实例化 并打开test.jpg图片


/**
* 添加水印图片
* @param  string $water 水印图片路径
* @param  integer|array $position 水印位置
* @param    int $position 水印位置，有10种状态，0为随机位置；
*                                1为顶端居左，2为顶端居中，3为顶端居右；
*                                4为中部居左，5为中部居中，6为中部居右；
*                                7为底端居左，8为底端居中，9为底端居右；
*                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
* @param  integer $alpha 水印透明度
* @param  integer $waterConf array('width'=>100,'height'=>100) 调整水印大小 默认调用原图
*/
$img->watermark($water, $position = 0, $alpha = 100, $waterConf = array())；
//其他 显示 下载 保存同上
```
####剪辑
```php
$img = loadClass('libs\Image','D:/test.jpg');//实例化 并打开test.jpg图片

/**
* 裁剪图像
* @param  integer $w 裁剪区域宽度
* @param  integer $h 裁剪区域高度
* @param  integer|array $position 裁剪起始位置 有10种状态，0为随机位置；
*                                 1为顶端居左，2为顶端居中，3为顶端居右；
*                                 4为中部居左，5为中部居中，6为中部居右；
*                                 7为底端居左，8为底端居中，9为底端居右；
*                                 指定位置 array(100,100) | array('x'=>100,'y'=>100)
* @param  integer $width 图像保存宽度 默认为裁剪区域宽度
* @param  integer $height 图像保存高度 默认为裁剪区域高度
*/
$img->cut($w, $h, $position = 1, $width = null, $height = null);
//其他 显示 下载 保存同上
```

##CURL类     Curl
>支持连贯操作

```php
//GET请求
$curl = loadClass('libs\Curl');

//设置需要获取的URL地址
$curl = $curl->setUrl($url . 'https://api.weixin.qq.com/sns/oauth2/access_token');

/**
* 启用时会发送一个常规的GET请求
* @param array|string $data array('user'=>'admin','pass'=>'admin') | admin&admin
* @return $this
*/
$curl = $curl->get('appid=' . $AppID . '&secret=' . $AppSecret . '&code=' . $code . '&grant_type=authorization_code')；

/**
* 执行一个cURL会话 返回执行的结果
* @param bool $debug 是否开启调试模式 如果为true将打印调试信息
* @return mixed
*/
 $curl =$curl->exec();
```

```php
//POST请求
$curl = loadClass('libs\Curl');

//设置需要获取的URL地址
$curl = $curl->setUrl($url . 'https://127.0.0.1/test.php');

/**
* 启用时会发送一个常规的POST请求，默认类型为：application/x-www-form-urlencoded，就像表单提交的一样
* @param array|string $data
* @param string $enctype application|multipart  默认为application，文件上传请用multipart
*/
$curl = $curl->post(array('name' => 'test', 'sex'=>1,'birth'=>'20101010'))；

/**
* 执行一个cURL会话 返回执行的结果
* @param bool $debug 是否开启调试模式 如果为true将打印调试信息
* @return mixed
*/
 $curl =$curl->exec();
```

```php
//获取Cookie模拟登陆
$cookie_file = tempnam('./temp','cookie');

$curl = loadClass('libs\Curl');

//设置需要获取的URL地址
$curl = $curl->setUrl($url . 'https://127.0.0.1/login.php');

/**
* 启用时会发送一个常规的POST请求，默认类型为：application/x-www-form-urlencoded，就像表单提交的一样
* @param array|string $data
* @param string $enctype application|multipart  默认为application，文件上传请用multipart
*/
$curl = $curl->post(array('name' => 'admin', 'passwd'=>'123456'))；

/**
* 获得cookies
* @param string $path 定义Cookie存储路径 必须使用绝对路径
*/
$curl = $curl->getCookie($cookie_file);

/**
* 执行一个cURL会话 返回执行的结果
* @param bool $debug 是否开启调试模式 如果为true将打印调试信息
* @return mixed
*/
 $curl =$curl->exec();

$curl = $curl->setUrl($url . 'https://127.0.0.1/getUserInfo.php');

/**
* 取出cookie，一起提交给服务器
* @param string $path 定义Cookie存储路径 必须使用绝对路径
*/
$data = $curl->setCookieFile($cookie_file)->exec();


/**
* 设定HTTP请求中"Cookie: "部分的内容。多个cookie用分号分隔，分号后带一个空格(例如， "fruit=apple; colour=red")。
* @param string|array $cookies 定义Cookie的值
*/
$curl = $curl->setCookie(array('name'=>'admin','passwd'=>'123456'));

$data = $curl->exec();
var_dump($data);

//清理cookie文件
unlink($cookie_file);
```

```php
$headers['Referer'] = 'http://www.baidu.com';
$headers['CLIENT-IP'] = '202.103.229.40';
$headers['X-FORWARDED-FOR'] = '202.103.229.40';

$curl = loadClass('libs\Curl');

//设置需要获取的URL地址
$curl = $curl->setUrl($url . 'https://127.0.0.1/login.php');

/**
* @param bool $verify 是否验证证书 默认false不验证
* @param string $path 验证证书时，证书路径
* @return $this
*/
$curl = $curl->sslVerify(false);

/**
* 传递一个连接中需要的用户名和密码
* @param array|string $userPassword 格式为：array('userName','password') 或则, "username:password"
*/

$curl = $curl->setUserPassword(array('admin','123456'));


//setHeader(array())设置请求头
$curl->setHeader($headers)->get()->exec();
var_dump($data);

```


##验证码类   VerifyCode
```php
//配置，以下均为默认值
$conf= array(
'width' =>100;//图片宽度
'height' =>40;//图片高度
'size' =>21;//字体大小
'font'=>'yrphp/resource/font/1.ttf';//字体
'len' =>4;//随机字符串长度
'type';//默认是大小写数字混合型，1 2 3 分别表示 小写、大写、数字型
'backColor' => '#eeeeee';     //背景色，默认是浅灰色
'pixelNum' => 666; //干扰点个数
'lineNum'=> 10; //干扰线条数
);

/**
 * @param string $code 验证码key,用于session获取，默认verify
 * @param bool $line 是否显示干扰线
 * @param bool $pixel 是否显示干扰点
 */
//参数可以在实例化时传入 也可以调用init方法初始化时调用
loadClass('libs\VerifyCode',$conf)->show($code = 'verify', $line = true, $pixel = true);
```

##分页类

```php
//配置，以下均为默认值
        $config = array(
            'totalRows' => 100,// 总行数
            'listRows' => 12,// 列表每页显示行数 默认12
            'rollPage' => 6,// 分页栏每页显示的页数 默认8
            'p' => 'p',
            'url' => 'http://example.com/test/page/',//跳转链接URL,不配置 默认为当前页
            'urlParam' => array('key' => 'hello'),// 分页跳转时要带的参数

            //添加封装标签
            'fullTagOpen' => '<div>',//整个分页周围围绕一些标签开始标签
            'fullTagClose' => '</div>',//整个分页周围围绕一些标签结束标签

            //自定义“当前页”链接
            'nowPage' => 3,//当前页，默认为'1'第一页
            'nowTagOpen' => '<strong>',//在当前页外围包裹开始标签 默认<strong>
            'nowTagClose' => '</strong>',//在当前页外围包裹结束标签

            //自定义起始链接
            'firstTagOpen' => '',//在首页外围包裹开始标签
            'firstLink' => '首页',//你希望在分页中显示“首页”链接的名字  如果不想显示该标签 则设置为FALSE即可
            'firstTagClose' => '',//在首页外围包裹标签结束标签

            //自定义结束链接
            'lastTagOpen' => '',//在尾页外围包裹开始标签
            'lastLink' => '尾页',//你希望在分页中显示“尾页”链接的名字  如果不想显示该标签 则设置为FALSE即可
            'lastTagClose' => '',//在尾页外围包裹标签结束标签

            //自定义“上一页”链接
            'prevTagOpen' => '',//在上一页外围包裹开始标签
            'prevLink' => '上一页',//上一页显示文字  如果不想显示该标签 则设置为FALSE即可
            'prevTagClose' => '',//在上一页外围包裹标签结束标签

            //自定义“下一页”链接
            'nextTagOpen' => '',//在下一页外围包裹开始标签
            'nextLink' => '下一页',//你希望在分页中显示“下一页”链接的名字 如果不想显示该标签 则设置为FALSE即可
            'nextTagClose' => '',//在下一页外围包裹标签结束标签

            //自定义“数字”链接  如果不想显示该标签 将rollPage设置为0即可
            'otherTagOpen' => '',//在其他“数字”链接外围包裹开始标签
            'otherTagClose' => '',//在其他“数字”链接外围包裹标签结束标签

            //自定义“select下拉跳转”
            'gotoPage' => false,//是否显示select下拉跳转,默认不显示
            'gotoTagOpen' => '',//在select下拉跳转外围包裹标签
            'gotoTagClose' => '',//在select下拉跳转外围包裹标签闭合

        );

        //实例化分页类 参数也可以通过init方法初始化
        $page = loadClass('libs\page', $config);
        //输出分页的html
        echo $page->show();
```
**生成样式**
<div><a href="http://example.com/test/page/?key=hello&amp;p=1">首页</a><a href="http://example.com/test/page/?key=hello&amp;p=4">上一页</a><a href="http://example.com/test/page/?key=hello&amp;p=2">2</a><a href="http://example.com/test/page/?key=hello&amp;p=3">3</a><a href="http://example.com/test/page/?key=hello&amp;p=4">4</a><strong><a href="http://example.com/test/page/?key=hello&amp;p=5">5</a></strong><a href="http://example.com/test/page/?key=hello&amp;p=6">6</a><a href="http://example.com/test/page/?key=hello&amp;p=7">7</a><a href="http://example.com/test/page/?key=hello&amp;p=6">下一页</a><a href="http://example.com/test/page/?key=hello&amp;p=9">尾页</a></div>


##验证类     Validate
```php
<?php
       /**
         * 当两个值相等时 return true
         * @param string $data
         * @param string $val
         * @return bool
         */
        \libs\Validate::equal(20, 10);//false
        \libs\Validate::equal(20, 20);//true
        /**
         * 当两个不值相等时 return true
         * @param string $data
         * @param string $val
         * @return bool
         */

        \libs\Validate::notEqual(20, 10);//true
        \libs\Validate::notEqual(20, 20);//false
        /**
         * 当存在指定范围时return true
         * @param string $data
         * @param array|string $range
         * @return bool
         */
        \libs\Validate::in(2, '2,8');//true
        \libs\Validate::in(10, array(2, 8));//false

        /**
         * 当不存在指定范围时return true
         * @param string $data
         * @param array|string $range
         * @return bool
         */
        \libs\Validate::notIn(2, '2,8');//false
        \libs\Validate::notIn(10, array(2, 8));//true


        /**
         * 当存在指定范围时return true
         * @param null $data
         * @param array|string $range
         * @return bool
         */
        \libs\Validate::between(10, '10,20');//true
        \libs\Validate::between(10, array(20, 15));//false


        /**
         * 当不存在指定范围时return true
         * @param null $data
         * @param array|string $range
         * @return bool
         */
        \libs\Validate::notBetween(10, '10,20');//false
        \libs\Validate::notBetween(10, array(20, 15));//true


        /**
         * 当数据库中值存在时 return false
         * @param $tableName 表名
         * @param $field 字段名
         * @param $val 值
         * @return bool
         */
        \libs\Validate::unique($tableName, $field, $val);

        /**
         * 当字符长度存在指定范围时return true
         * @param null $data 字符串
         * @param array|string $range 范围
         * @return bool
         * length('abc',3); strlen('abc') ==3
         * length('abc',array(5,3))==length('abc',array(3,5)) => strlen('abc') >=3 && strlen('abc') <=5
         */
        \libs\Validate::length($data = '', $range = '');


        /**
         * Email格式验证
         * @param    string $value 需要验证的值
         */
        \libs\Validate::email('5463@qq.com');//true

        /**
         * URL格式验证
         * @param    string $value 需要验证的值
         */
        \libs\Validate::url('https://www.baidu.com');//true

        /**
         * 数字格式验证
         * @param    string $value 需要验证的值
         */
        \libs\Validate::number(100); //true;

        /**
         * 使用自定义的正则表达式进行验证
         * @param    string $value 需要验证的值
         * @param    string $rules 正则表达式
         */
        \libs\Validate::regex($value, $rules);

        /**
         * 判断是否为手机号码
         * @param    string $value 手机号码
         */
        \libs\Validate::phone($value = '');

        /**
         * 判断验证码的确与否
         * @param string $value 值
         * @param string $code session中的key 默认'verify'
         * @return bool
         */
        \libs\Validate::verifyCode($value, $code);


```
##购物车类   Cart
```php
<?php
//配置参数
$conf = array(
'saveMode' = 'session'，//存储方式，有cookie和session，默认session
'mallMode'=>false,//商城模式 true多商家 false单商家,默认false单商家
'key'=>'cartContents',//保存在session或者cookie中的key
);
//实例化购物车类 配置参数也可以通过init方法初始化
$cart = loadClass('\libs\Cart',$conf);

//添加一个产品到购物车
/**
六个保留的索引分别是:
id - 你的商店里的每件商品都必须有一个唯一的标识符(identifier)
qty - 购买的数量(quantity)。
price - 商品的价格(price)。
name - 商品的名称(name)。
options - 标识商品的任何附加属性。必须通过数组来传递。
seller - 卖家标识ID，多商家模式必须设置
id, qty, price 和name是必需的，options是可选的
除以上六个索引外，还有两个保留字：rowId 和 subtotal。它们是购物车类内部使用的，因此，往购物车中插入数据时，请不要使用这些词作为索引。

其他可自行扩展
 */
      $items = array(
               'id'      => 'sku_123ABC',
               'qty'     => 1,
               'price'   => 39.95,
               'name'    => 'T-Shirt',
               'options' => array('Size' => 'L', 'Color' => 'Red')
            );
/**
* 添加单条或多条购物车项目
* @param array $items 添加多个可为二维数组
* @param bool $accumulation 是否累加,默认累计
* @return bool|string
*/
$cart->insert($items);



/**
* 返回一个包含了购物车中所有信息的数组
* @param null $mallMode 商城模式 true多商家(二维数组) false单商家（一维数组）默认为配置中的模式,当为单商家时，不管设置什么都返回一维数组
* @param null $seller 返回指定商家下的所以产品，默认为null，返回所以商家，单商家下无效
* @return array
*/
$cartList = $cart->getContents()；


/**
* 获得一条购物车的项目
* @param null $rowId
* @return bool|array
*/
$rowId = n'b99ccdf16028f015540f341130b6d8ec';
$item = $cart->getItem($rowId);

/**
* 显示购物车中总共的商品数量
* @param null $seller 商家标识符 单商家模式下无效
* @return int
*/
$totalQty = $cart->totalQty();

/**
* 显示购物车中的总计金额  商家标识符 单商家模式下无效
* @return int
*/
$priceTotal = $cart->total();

/**
* 显示购物车中总共的项目数量
* @param null $seller 商家标识符 单商家模式下无效
* @return int
*/
$totalItems =$cart->totalItems();


/**
* 更新购物车中的项目 必须包含 rowId
* @param $item 修改多个可为二维数组
* @return bool
*/

$items = array(
'rowId'=> 'b99ccdf16028f015540f341130b6d8ec',
'qty'=>6,
)
$cart->update($items);

/**
* 删除一条购物车中的项目  必须包含 rowId
* @param null|array $rowId
* @return bool
*/
$rowId = n'b99ccdf16028f015540f341130b6d8ec';
$cart->remove($rowId);

/**
*删除多条用数组
*/
$rowId = array(
'b99ccdf16028f015540f341130b6d8ec',
'qweuyrf16028f985640f341130b6d66c'
);
$cart->remove($rowId);

/**
* 销毁购物车
*/
$cart->destroy()；

/**
* 根据rowId 查找商家
* @param $key
* @return bool|int|string  当为单商家模式时直接返回false,当找不到时也返回false，否则返回商家标识符
*/
$rowId = 'b99ccdf16028f015540f341130b6d8ec';
$seller = $cart->searchSeller($rowId);
?>
```

##Email 类   PHPMailer