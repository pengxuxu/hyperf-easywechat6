<?php

declare(strict_types=1);


/**
 * This file is part of Hyperf.
 *
 * @link https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact group@hyperf.io
 * @license https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Pengxuxu\HyperfWechat;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Factory.
 * @method \EasyWeChat\OfficialAccount\Application officialAccount(string $name = "default", array $config = [])
 * @method \EasyWeChat\Pay\Application pay(string $name = "default", array $config = [])
 * @method \EasyWeChat\MiniApp\Application miniApp(string $name = "default", array $config = [])
 * @method \EasyWeChat\OpenPlatform\Application openPlatform(string $name = "default", array $config = [])
 * @method \EasyWeChat\Work\Application work(string $name = "default", array $config = [])
 * @method \EasyWeChat\OpenWork\Application openWork(string $name = "default", array $config = [])
 */
class Factory
{
    protected $configMap = [
        'officialAccount' => 'official_account',
        'pay' => 'pay',
        'miniApp' => 'mini_app',
        'openPlatform' => 'open_platform',
        'work' => 'work',
        'openWork' => 'open_work',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var CacheInterface
     */
    protected $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->config = $container->get(ConfigInterface::class);

        $this->cache = $container->get(CacheInterface::class);
    }

    public function __call($functionName, $args)
    {
        $accountName = $args[0] ?? 'default';

        $accountConfig = $args[1] ?? [];

        if (!isset($this->configMap[$functionName])) {
            throw new \Exception('方法不存在!');
        }

        $configName = $this->configMap[$functionName];

        $appName = ucfirst($functionName);

        $config = $this->getConfig(sprintf('wechat.%s.%s', $configName, $accountName), $accountConfig);

        $application = "\\EasyWeChat\\{$appName}\\Application";

        $app = new $application($config);

        if (method_exists($app, 'setCache')) {
            $app->setCache($this->cache);
        }

        if (Context::get(ServerRequestInterface::class) && method_exists($app, 'setRequest')) {
            $app->setRequest($this->container->get(RequestInterface::class));
        }

        return $app;
    }

    /**
     * 获得配置
     */
    private function getConfig(string $name, array $config = []): array
    {
        $defaultConfig = $this->config->get('wechat.defaults', []);

        $moduleConfig = $this->config->get($name, []);

        return array_merge($moduleConfig, $defaultConfig, $config);
    }
}