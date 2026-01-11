<?php

declare(strict_types=1);

namespace Ehmetlabs\WebmanTenant;

use Ehmetlabs\WebmanTenant\Model\Tenant;
use Ehmetlabs\WebmanTenant\Model\TenantAdmin;
use support\Context;
use support\Request;

final class TenantResolver
{
    public static function resolve(Request $request, array $config): TenantResolution
    {
        $tokenClaims = self::resolveTokenClaims($request, $config);
        $adminIdentity = self::resolveAdminIdentity($config, $tokenClaims);
        $tenantKeyResult = self::resolveTenantKey($request, $config, $tokenClaims);

        if (null === $tenantKeyResult) {
            if (null !== $adminIdentity['tenantId']) {
                $tenantId = $adminIdentity['tenantId'];

                return new TenantResolution(
                    new TenantInfo($tenantId, (string) $tenantId, 'admin', $adminIdentity['isGlobalAdmin']),
                );
            }

            if (!($config['require_tenant'] ?? true)) {
                return new TenantResolution(null);
            }

            return new TenantResolution(null, '租户未识别', 400);
        }

        [$tenantKey, $source] = $tenantKeyResult;
        $tenantId = Tenant::resolveId($tenantKey);
        if (null === $tenantId) {
            return new TenantResolution(null, '租户不存在或已禁用', 404);
        }

        if (!$adminIdentity['isGlobalAdmin']
            && null !== $adminIdentity['tenantId']
            && $adminIdentity['tenantId'] !== $tenantId
        ) {
            return new TenantResolution(null, '无权访问该租户', 403);
        }

        return new TenantResolution(
            new TenantInfo($tenantId, $tenantKey, $source, $adminIdentity['isGlobalAdmin']),
        );
    }

    private static function resolveTenantKey(Request $request, array $config, ?array $tokenClaims): ?array
    {
        $order = $config['resolve_order'] ?? ['token', 'header', 'path', 'subdomain'];
        foreach ($order as $source) {
            $key = match ($source) {
                'token' => self::resolveFromToken($tokenClaims, $config),
                'header' => self::resolveFromHeader($request, $config),
                'path' => self::resolveFromPath($request, $config),
                'subdomain' => self::resolveFromSubdomain($request, $config),
                default => null,
            };
            $key = self::normalizeKey($key);
            if (null !== $key) {
                return [$key, $source];
            }
        }

        return null;
    }

    private static function resolveFromToken(?array $tokenClaims, array $config): ?string
    {
        if (null === $tokenClaims) {
            return null;
        }
        $claimKey = (string) ($config['token_claim_key'] ?? 'tenant_id');

        return isset($tokenClaims[$claimKey]) ? (string) $tokenClaims[$claimKey] : null;
    }

    private static function resolveFromHeader(Request $request, array $config): ?string
    {
        $headerKey = (string) ($config['header_key'] ?? 'X-Tenant-Id');
        $value = $request->header($headerKey);

        return $value ? (string) $value : null;
    }

    private static function resolveFromPath(Request $request, array $config): ?string
    {
        $prefix = \trim((string) ($config['path_prefix'] ?? ''), '/');
        if ('' === $prefix) {
            return null;
        }
        $path = \trim($request->path(), '/');
        if ('' === $path) {
            return null;
        }
        $segments = \explode('/', $path);
        if (!isset($segments[1]) || $segments[0] !== $prefix) {
            return null;
        }

        return $segments[1];
    }

    private static function resolveFromSubdomain(Request $request, array $config): ?string
    {
        $baseDomain = (string) ($config['base_domain'] ?? '');
        if ('' === $baseDomain) {
            return null;
        }
        $host = $request->host(true);
        if (!$host || !\str_ends_with($host, $baseDomain)) {
            return null;
        }
        if ($host === $baseDomain) {
            return null;
        }
        $subdomain = \rtrim(\substr($host, 0, -\strlen($baseDomain)), '.');
        if ('' === $subdomain) {
            return null;
        }
        $parts = \explode('.', $subdomain);

        return $parts[0] ?? null;
    }

    private static function resolveTokenClaims(Request $request, array $config): ?array
    {
        $contextClaims = Context::get('auth.claims');
        if (\is_array($contextClaims)) {
            return $contextClaims;
        }
        if (!($config['allow_unverified_jwt'] ?? false)) {
            return null;
        }
        $authorization = (string) $request->header('authorization');
        if (!\str_starts_with($authorization, 'Bearer ')) {
            return null;
        }
        $token = \trim(\substr($authorization, 7));
        if ('' === $token) {
            return null;
        }

        return self::decodeJwtPayload($token);
    }

    private static function decodeJwtPayload(string $token): ?array
    {
        $parts = \explode('.', $token);
        if (3 !== \count($parts)) {
            return null;
        }
        $payload = self::base64UrlDecode($parts[1]);
        if (false === $payload) {
            return null;
        }
        $claims = \json_decode($payload, true);

        return \is_array($claims) ? $claims : null;
    }

    private static function base64UrlDecode(string $value): string|false
    {
        $remainder = \strlen($value) % 4;
        if (0 !== $remainder) {
            $value .= \str_repeat('=', 4 - $remainder);
        }

        return \base64_decode(\strtr($value, '-_', '+/'), true);
    }

    private static function resolveAdminIdentity(array $config, ?array $tokenClaims): array
    {
        $adminId = null;
        if (\function_exists('admin_id')) {
            $adminId = admin_id();
        }
        if (null === $adminId && \is_array($tokenClaims)) {
            $adminClaimKey = (string) ($config['token_admin_claim_key'] ?? 'admin_id');
            if (isset($tokenClaims[$adminClaimKey])) {
                $adminId = (int) $tokenClaims[$adminClaimKey];
            }
        }
        $globalAdminId = (int) ($config['global_admin_id'] ?? 0);
        $isGlobalAdmin = null !== $adminId && $globalAdminId > 0 && $adminId === $globalAdminId;

        $tenantId = null;
        if (null !== $adminId) {
            $tenantId = TenantAdmin::resolveTenantIdByAdminId($adminId);
        }
        if (null === $tenantId && \is_array($tokenClaims)) {
            $tenantClaimKey = (string) ($config['token_claim_key'] ?? 'tenant_id');
            if (isset($tokenClaims[$tenantClaimKey])) {
                $claimValue = (string) $tokenClaims[$tenantClaimKey];
                if (\ctype_digit($claimValue)) {
                    $tenantId = (int) $claimValue;
                } else {
                    $resolvedId = Tenant::resolveId($claimValue);
                    if (null !== $resolvedId) {
                        $tenantId = $resolvedId;
                    }
                }
            }
        }
        if ($isGlobalAdmin) {
            $tenantId = (int) ($config['global_tenant_id'] ?? 0);
        }

        return [
            'adminId' => $adminId,
            'tenantId' => $tenantId,
            'isGlobalAdmin' => $isGlobalAdmin,
        ];
    }

    private static function normalizeKey(?string $key): ?string
    {
        if (null === $key) {
            return null;
        }
        $key = \trim($key);

        return '' === $key ? null : $key;
    }
}
