<?php

declare(strict_types=1);

namespace plugin\tenant\app\command;

use support\Db;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('tenant:init', '初始化租户表与管理员映射')]
class TenantInitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $config = config('plugin.ehmetlabs.webman-tenant.tenant', []);
            $globalTenantId = (int) ($config['global_tenant_id'] ?? 1);
            $globalAdminId = (int) ($config['global_admin_id'] ?? 0);

            $this->createTenantsTable();
            $this->createTenantAdminsTable();
            $this->seedGlobalTenant($globalTenantId, $output);
            $this->seedGlobalAdmin($globalAdminId, $globalTenantId, $output);

            $output->writeln('<info>租户初始化完成</info>');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>租户初始化失败: ' . $exception->getMessage() . '</error>');

            return self::FAILURE;
        }
    }

    private function createTenantsTable(): void
    {
        Db::statement(<<<SQL
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(128) NOT NULL COMMENT '租户名称',
  `slug` varchar(64) NOT NULL COMMENT '租户标识',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统租户',
  `created_at` datetime DEFAULT NUll COMMENT '创建时间',
  `updated_at` datetime DEFAULT NUll COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tenants_slug` (`slug`),
  KEY `idx_tenants_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='租户';
SQL);
    }

    private function createTenantAdminsTable(): void
    {
        Db::statement(<<<SQL
CREATE TABLE IF NOT EXISTS `tenant_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `tenant_id` int(10) unsigned NOT NULL COMMENT '租户ID',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_tenant_admins_admin` (`admin_id`),
  KEY `idx_tenant_admins_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员租户映射';
SQL);
    }

    private function seedGlobalTenant(int $globalTenantId, OutputInterface $output): void
    {
        if ($globalTenantId <= 0) {
            return;
        }
        $exists = Db::table('tenants')->where('id', $globalTenantId)->exists();
        if ($exists) {
            Db::table('tenants')->where('id', $globalTenantId)->update(['is_system' => 1]);

            return;
        }
        $slug = 'system';
        $slugExists = Db::table('tenants')
            ->where('slug', $slug)
            ->where('id', '!=', $globalTenantId)
            ->exists();
        if ($slugExists) {
            $slug = 'system-' . $globalTenantId;
        }

        Db::table('tenants')->insert([
            'id' => $globalTenantId,
            'name' => '系统租户',
            'slug' => $slug,
            'status' => 1,
            'is_system' => 1,
        ]);

        $output->writeln('<info>已初始化系统租户</info>');
    }

    private function seedGlobalAdmin(int $globalAdminId, int $globalTenantId, OutputInterface $output): void
    {
        if ($globalAdminId <= 0 || $globalTenantId <= 0) {
            return;
        }
        Db::table('tenant_admins')->updateOrInsert(
            ['admin_id' => $globalAdminId],
            ['tenant_id' => $globalTenantId],
        );

        $output->writeln('<info>已绑定全局管理员租户关系</info>');
    }
}
