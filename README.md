# Notice
easywechat6使symfony/http-client相关组件替换了之前4、5版本的GuzzleHttp\Client请求组件，但是symfony/http-client没有协和支持

# hyperf-wechat

微信 SDK for Hyperf， 基于 overtrue/wechat

## 安装
~~~shell script
composer require hyper-easywechat6 
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

> 注意：一定是 `Router::addRoute`, 因为微信服务端认证的时候是 `GET`, 接收用户消息时是 `POST` ！
然后创建控制器 `WeChatController`：

```php
<?php
declare(strict_types=1);
namespace App\Controller;
use EasyWeChat\Kernel\Exceptions\BadRequestException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use Glue\EasyWeChat\EasyWechat;
use Glue\EasyWeChat\Helper;
use ReflectionException;
class WeChatController extends AbstractController
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
        // 一定要用Helper::Response去转换
        return Helper::Response($server->serve());
    }
}
```



##### 使用外观

```php
  use Glue\EasyWeChat\EasyWechat;
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

