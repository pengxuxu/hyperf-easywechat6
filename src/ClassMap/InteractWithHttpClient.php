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
namespace EasyWeChat\Kernel\Traits;

use Glue\EasyWeChat\HttpClient;
use Psr\Log\LoggerAwareInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait InteractWithHttpClient
{
    protected ?HttpClientInterface $httpClient = null;

    public function getHttpClient(): HttpClientInterface
    {
        if (! $this->httpClient) {
            $this->httpClient = $this->createHttpClient();
        }

        return $this->httpClient;
    }

    public function setHttpClient(HttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;

        if ($this instanceof LoggerAwareInterface && $httpClient instanceof LoggerAwareInterface && \property_exists($this, 'logger') && $this->logger) {
            $httpClient->setLogger($this->logger);
        }

        return $this;
    }

    protected function createHttpClient(): HttpClientInterface
    {
        return new HttpClient($this->getHttpClientDefaultOptions());
    }

    /**
     * @return array<string,mixed>
     */
    protected function getHttpClientDefaultOptions(): array
    {
        return [];
    }
}
