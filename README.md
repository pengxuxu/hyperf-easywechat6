# Notice
- easywechat6用symfony/http-client相关组件，替换了之前4，5等版本的Guzzle请求组件，Symfony Http Client在常驻内存的服务中使用时，[HttpClient会因为多个协程共用而报错](https://wiki.swoole.com/#/coroutine/notice?id=%e5%9c%a8%e5%a4%9a%e4%b8%aa%e5%8d%8f%e7%a8%8b%e9%97%b4%e5%85%b1%e7%94%a8%e4%b8%80%e4%b8%aa%e8%bf%9e%e6%8e%a5)。 pengxuxu/hyperf-easywechat6包使用hyperf的ClassMap替换了InteractWithHttpClient中的HttpClient对象实例，使得不同协程为不同的请求实例，同一协程上下文中获取到的为同一请求实例。

```php
<?php
declare(strict_types=1);
namespace App\Controller;

use EasyWeChat\Kernel\HttpClient\RequestUtil;
use Symfony\Component\HttpClient\HttpClient as SymfonyClient;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
        $client = SymfonyClient::create(RequestUtil::formatDefaultOptions([]));
        go(function () use ($client) {
            $client->request('GET', 'http://www.baidu.com/');
        });
        go(function () use ($client) {
            $client->request('GET', 'http://www.baidu.com/');
        });
        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}
在swoole>=4.7.0且开启native curl修改常量SWOOLE_HOOK_ALL，共用一个HttpClient情况下，报错信息：
PHP Fatal error:  Uncaught Swoole\Error: cURL is executing, cannot be operated in /data/project/hyperf-skeleton/vendor/symfony/http-client/Response/CurlResponse.php:366
```
- pengxuxu/hyperf-easywechat6包用hyperf的容器获得Hyperf\HttpServer\Contract\RequestInterface对应的Hyperf\HttpServer\Request，替换了easywechat6中的同样基于PSR-7规范request；获得Psr\SimpleCache\CacheInterface对应的缓存类，替换easywechat6中同样基于PSR-16规范的cache。
  ```php
  $app = new Application($config);

  if (method_exists($app, 'setRequest')) {
    $app->setRequest(ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface));
  }

  if (method_exists($app, 'setCache')) {
    $app->setCache(ApplicationContext::getContainer()->get(\Psr\SimpleCache\CacheInterface::class)
  }
  ```

- 建议使用Swoole 4.7.0 及以上版本，并且开启 native curl 选项。easywechat4，5版本使用的是Guzzle，该组件默认使用Curl，如果开启navie curl，并修改常量 define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL)，就不用替换Guzzle中的handler。easywechat6则使用symfony/http-client包，它会根据当前环境，按CurlHttpClient（Curl，如果安装了curl扩展），AmpHttpClient（Amp HTTP/2 Support）和NativeHttpClient（Stream）的顺序返回http客户端。拿hyperf官方 Hyperf\Guzzle\CoroutineHandler handler举例来说，这个handler里会new一个继承自Swoole\Coroutine\Http\Client 的协程客户端HttpClient，这个就解释了上面提到的HttpClient会因为多个协程共用而报错和easywechat4，5版本如果换guzzle handler后无需再classmap替换。

- 建议安装easywechat6.11.1（>=）以上版本，从该版本开始加入了[主动请求和异步通知的验签](https://easywechat.com/6.x/pay/index.html#%E4%B8%80%E4%BA%9B%E5%8F%AF%E8%83%BD%E4%BC%9A%E7%94%A8%E5%88%B0%E7%9A%84)。
# hyperf-easywechat6

微信 SDK for Hyperf， 基于 w7corp/easywechat

## 安装

~~~shell script
composer require pengxuxu/hyperf-easywechat6 
~~~

## 配置

1. 发布配置文件

~~~shell script
php ./bin/hyperf.php vendor:publish pengxuxu/hyperf-easywechat6
~~~

2. 修改应用根目录下的 `config/autoload/wechat.php` 中对应的参数即可。
3. 每个模块基本都支持多账号，默认为 `default`。

## 使用

接收普通消息例子：

```php
Router::addRoute(['GET', 'POST', 'HEAD'], '/wechat', 'App\Controller\WeChatController@serve');
```

> 注意：一定是 `Router::addRoute`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！ 然后创建控制器 `WeChatController`：

```php
<?php
declare(strict_types=1);
namespace App\Controller;
use EasyWeChat\Kernel\Exceptions\BadRequestException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Pengxuxu\HyperfWechat\EasyWechat;
use Pengxuxu\HyperfWechat\Helper;
use ReflectionException;
class WeChatController
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     * @throws BadRequestException
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws ReflectionException
     */
    public function serve()
    {
        $app = EasyWechat::officialAccount();
        
        $server = $app->getServer();
        
        $server->with(function ($message, \Closure $next) {
            return '谢谢关注！';
            
            // 你的自定义逻辑
            // return $next($message);
        });
        // 一定要用Helper::Response去转换
        return Helper::Response($server->serve());
    }
}
```

##### 使用外观

```php
  use Pengxuxu\HyperfWechat\EasyWechat;
  $officialAccount = EasyWechat::officialAccount(); // 公众号
  $pay = EasyWechat::pay(); // 微信支付
  $miniApp = EasyWechat::miniApp(); // 小程序
  $openPlatform = EasyWechat::openPlatform(); // 开放平台
  $work = EasyWechat::work(); // 企业微信
  $openWork = EasyWechat::openWork(); // 企业微信开放平台  
  // 均支持传入配置账号名称以及配置
  EasyWeChat::officialAccount('foo',[]); // `foo` 为配置文件中的名称，默认为 `default`。`[]` 可覆盖账号配置
  //...
```

更多 SDK 的具体使用请参考：https://easywechat.com

## License

MIT

