<?php

namespace App\Support;

final class PermissionRegistry
{
    public static function all(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'default_user' => true],
            ['key' => 'checklist', 'label' => 'Checklist', 'default_user' => true],
            ['key' => 'submissions', 'label' => 'Submissions', 'default_user' => false],
            ['key' => 'daily-activity', 'label' => 'Daily Activity', 'default_user' => true],
            ['key' => 'asset', 'label' => 'Asset', 'default_user' => false],
            ['key' => 'document-maker', 'label' => 'Document Maker', 'default_user' => false],
            ['key' => 'reports', 'label' => 'Laporan', 'default_user' => false],
            ['key' => 'user-management', 'label' => 'Manajemen User', 'default_user' => false],
            ['key' => 'history', 'label' => 'Riwayat', 'default_user' => true],
        ];
    }

    public static function names(): array
    {
        return collect(self::all())->flatMap(fn (array $module) => self::permissions($module['key']))->all();
    }

    public static function defaultUserNames(): array
    {
        return collect(self::all())->filter(fn (array $module) => $module['default_user'])
            ->flatMap(fn (array $module) => [self::read($module['key'])])->values()->all();
    }

    public static function modules(): array
    {
        return self::all();
    }

    public static function read(string $key): string
    {
        return "module.{$key}.read";
    }

    public static function write(string $key): string
    {
        return "module.{$key}.write";
    }

    public static function legacy(string $key): string
    {
        return "module.{$key}";
    }

    private static function permissions(string $key): array
    {
        return [self::read($key), self::write($key), self::legacy($key)];
    }
}
