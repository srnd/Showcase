<?php

namespace Showcase\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7;
use GuzzleHttp\Handler\CurlHandler;

class Clear
{
    public static function GetBatches()
    {
        return json_decode(self::GetClient()->get('batches')->getBody()->getContents());
    }

    public static function GetEventsForBatch(string $batchId)
    {
        return json_decode(self::GetClient()->get('batch/'.$batchId.'/events')->getBody()->getContents());
    }

    public static function GetRegistrationsForEvent(string $eventId)
    {
        return json_decode(self::GetClient()->get('event/'.$eventId.'/registrations')->getBody()->getContents());
    }

    public static function GetEventsWithAccess(string $username)
    {
        return json_decode(self::GetClient()->get('events/has-access', ['query' => ['username' => $username]])->getBody()->getContents());
    }

    private static $_client = null;
    protected static function GetClient()
    {
        if (!isset(self::$_client)) {
            // Insert API keys. This is ridiculously stupid.
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $stack->push(Middleware::mapRequest(function (Psr7\Request $request) {
                return $request->withUri(
                    Psr7\Uri::withQueryValue(
                        Psr7\Uri::withQueryValue($request->getUri(), 'public', config('clear.public')),
                        'secret', config('clear.secret')
                    ));
            }));

            // Create the Guzzle client.
            self::$_client = new Client([
                'base_uri' => config('clear.endpoint').'/api/',
                'timeout'  => 2.0,
                'handler' => $stack
            ]);
        }

        return self::$_client;
    }
}
