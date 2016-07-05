<?php

namespace CleverAge\EAVManager\CacheBundle\Clearer;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Will call a local URL to clean APC cache
 */
class ApcCacheClearer implements CacheClearerInterface
{
    const URL_PARAM = 'clear_cache';

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $cacheClearerUrl;

    /**
     * @param LoggerInterface $logger
     * @param string          $cacheClearerUrl
     */
    public function __construct(LoggerInterface $logger, $cacheClearerUrl = null)
    {
        $this->logger = $logger;
        $this->cacheClearerUrl = $cacheClearerUrl;
    }

    /**
     * Clears any caches necessary.
     *
     * @param string $cacheDir The cache directory
     *
     * @throws \UnexpectedValueException
     */
    public function clear($cacheDir)
    {
        if (!$this->cacheClearerUrl) {
            return;
        }
        $value = file_get_contents($this->cacheClearerUrl.'?'.self::URL_PARAM.'=1');
        $response = json_decode($value, true);
        if (false === $response || empty($response[self::URL_PARAM]) || $response[self::URL_PARAM] !== 'ok') {
            throw new \UnexpectedValueException("Failed to clear web cache: {$value}");
        }
        $caches = implode(', ', array_keys($response['caches']));

        $this->logger->notice("Successfuly cleared Web caches : {$caches}", $response);
    }

    /**
     * @return string
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public static function clearCache()
    {
        $remoteIp = @$_SERVER['REMOTE_ADDR'];
        // If not called by local client, abort
        if (!in_array($remoteIp, ['127.0.0.1', 'fe80::1', '::1', @$_SERVER['SERVER_ADDR']], true)) {
            throw new AccessDeniedHttpException("Access denied for IP {$remoteIp}");
        }

        $functions = ['apc_clear_cache', 'apcu_clear_cache', 'opcache_reset', 'xcache_clear_cache', 'wincache_ucache_clear'];

        $return = [];
        foreach ($functions as $function) {
            if (function_exists($function)) {
                try {
                    $return[$function] = call_user_func($function);
                } catch (\Exception $e) {
                    $return[$function] = 'ERROR: '.$e->getMessage();
                }
            }
        }

        $response = [
            self::URL_PARAM => 'ok',
            'caches' => $return,
        ];

        return json_encode($response);
    }
}
