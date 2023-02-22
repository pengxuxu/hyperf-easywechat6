<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use EasyWeChat\Kernel\Traits\InteractWithHttpClient;
use Pengxuxu\HyperfWechat\HttpClient;

class Http
{
    use InteractWithHttpClient;
}


/**
 * @internal
 * @coversNothing
 */
class InteractWithHttpClientTest extends AbstractTestCase
{
    public function testGetHttpClient()
    {
        $http = new Http();
        $client = $http->getHttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }
}
