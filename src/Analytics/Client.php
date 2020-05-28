<?php

declare(strict_types=1);

namespace NS8\ProtectSDK\Analytics;

use NS8\ProtectSDK\Http\Client as HttpClient;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_string;
use function json_decode;
use function sprintf;
use function sys_get_temp_dir;
use function time;

/**
 * Class for dictating general NS8 Analytics components
 */
class Client extends BaseClient
{
    /**
     * POST route for fetching TrueStats script from NS8
     */
    public const TRUE_STATS_ROUTE = '/init/script';

    /**
     * The temporary file used for caching the TrueStats script.
     */
    protected const TRUE_STATS_CACHE_FILE = 'ns8-truestats.json';

    /**
     * The TrueStats script gets cached for 1 day
     */
    protected const TRUE_STATS_CACHE_TTL = 86400;

    /**
     * HTTP Client used to make API requests
     *
     * @var HttpClient
     */
    protected static $httpClient;

    /**
     * Returns TrueStats URL
     *
     * @return string Path to TrueStats Script endpoint
     */
    public static function getTrueStatsRoute() : string
    {
        return self::TRUE_STATS_ROUTE;
    }

    /**
     * Returns the TrueStats JavaScript block
     *
     * @return string A string containing TrueStats JavaScript to load on front-end pages
     */
    public static function getTrueStatsScript() : string
    {
        $cachedScript = self::getScriptFromCache();

        if (isset($cachedScript)) {
            return $cachedScript;
        }

        $script        = self::getHttpClient()->sendNonObjectRequest(self::getTrueStatsRoute());
        $decodedScript = json_decode($script) ?? '';

        if ($decodedScript !== '') {
            self::saveScriptToCache($decodedScript);
        }

        return $decodedScript;
    }

    /**
     * Returns the HTTP client to be used for making API requests
     *
     * @return HttpClient The client to be used
     */
    public static function getHttpClient() : HttpClient
    {
        self::$httpClient = self::$httpClient ?? new HttpClient();

        return self::$httpClient;
    }

     /**
      * Sets an explicit HTTP client for making API requests
      *
      * @param HttpClient $httpClient The client we are passing in to make requests
      *
      * @return void
      */
    public static function setHttpClient(HttpClient $httpClient) : void
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Gets the full path to the cache file for the TrueStats script (platform independent)
     *
     * @return string The full path
     */
    protected static function getFullPathToScriptCacheFile() : string
    {
        return sprintf('%s/%s', sys_get_temp_dir(), self::TRUE_STATS_CACHE_FILE);
    }

    /**
     * Gets the script from the cache (if available)
     *
     * @return string|null The cached script
     */
    protected static function getScriptFromCache() : ?string
    {
        $file = self::getFullPathToScriptCacheFile();

        if (! file_exists($file) || filemtime($file) < time() - self::TRUE_STATS_CACHE_TTL) {
            return null;
        }

        $script = file_get_contents($file);

        return is_string($script) && $script !== '' ? $script : null;
    }

    /**
     * Saves the script to the cache
     *
     * @param string $script The script
     *
     * @return void
     */
    protected static function saveScriptToCache(string $script) : void
    {
        file_put_contents(self::getFullPathToScriptCacheFile(), $script);
    }
}
