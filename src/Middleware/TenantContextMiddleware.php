<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant\Middleware;

use Ehmetlabs\WebmanTenant\TenantContext;
use Ehmetlabs\WebmanTenant\TenantResolver;
use support\Context;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

class TenantContextMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $config = config('plugin.ehmetlabs.tenant.tenant', []);
        if ($this->isExemptPath($request->path(), $config)) {
            return $handler($request);
        }

        $resolution = TenantResolver::resolve($request, $config);
        if (!$resolution->isResolved()) {
            if (null === $resolution->errorMessage) {
                return $handler($request);
            }

            return $this->errorResponse($request, $resolution->errorMessage, $resolution->errorStatus);
        }

        TenantContext::set($resolution->tenant);
        Context::onDestroy(static fn () => TenantContext::clear());

        return $handler($request);
    }

    private function isExemptPath(string $path, array $config): bool
    {
        $exemptPaths = $config['exempt_paths'] ?? [];
        $path = '/' . \trim($path, '/');
        foreach ($exemptPaths as $exempt) {
            $exempt = '/' . \trim((string) $exempt, '/');
            if ('/' === $exempt) {
                continue;
            }
            if ($path === $exempt || \str_starts_with($path, $exempt . '/')) {
                return true;
            }
        }

        return false;
    }

    private function errorResponse(Request $request, string $message, int $status): Response
    {
        if ($request->expectsJson()) {
            return json([
                'code' => $status,
                'msg' => $message,
                'data' => [],
            ], $status);
        }

        return response($message, $status);
    }
}
