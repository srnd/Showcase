<?php

namespace Showcase\Http\Middleware;

use Closure;

class TrustCloudflare
{
    public function handle($request, Closure $next) {
        $proxy_ips = \Cache::remember('cloudFlareProxyIps', 1440, function () {
        $url = 'https://www.cloudflare.com/ips-v4';
        $ips = file_get_contents($url);
            return array_filter(explode("\n", $ips));
        });

        if ( $forwarded_for = $request->headers->get('X_FORWARDED_FOR') ) {
            $forwarded_ips = explode(", ", $forwarded_for);
            foreach ( $forwarded_ips as $forwarded_ip ) {
                if ( \Symfony\Component\HttpFoundation\IpUtils::checkIp($forwarded_ip, $proxy_ips) ) {
                    $proxy_ips[] = $request->server->get('REMOTE_ADDR');
                    break;
                }
            }
        }

        $request->setTrustedProxies($proxy_ips);

        return $next($request);
    }
}
