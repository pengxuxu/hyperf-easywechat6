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

use Hyperf\Utils\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as PsrResponseInterface;

class Helper
{
    public static function Response(ResponseInterface $response)
    {
        $psrResponse = ApplicationContext::getContainer()->get(PsrResponseInterface::class);

        $psrResponse = $psrResponse->withBody(new SwooleStream((string)$response->getBody()))
            ->withStatus($response->getStatusCode());

        foreach ($response->getHeaders() as $key => $item) {
            $psrResponse = $psrResponse->withHeader($key, $item);
        }

        return $psrResponse;
    }
}