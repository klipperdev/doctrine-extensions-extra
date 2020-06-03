<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\DoctrineExtensionsExtra\Mapping\Driver;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
trait MergeConfigTrait
{
    /**
     * Merge the config of extension.
     *
     * @param string      $extensionType   The name of extension
     * @param null|string $key             The key of the extension (ex. a field name)
     * @param array       $config          The mappings of all configs
     * @param array       $extensionConfig The config of the mapping extension
     */
    protected function mergeExtensionConfig(string $extensionType, ?string $key, array &$config, array $extensionConfig): void
    {
        $extKeys = array_keys($extensionConfig);

        if ($key) {
            $baseExtConfig = $config[$extensionType][$key] ?? [];
        } else {
            $baseExtConfig = $config[$extensionType] ?? [];
        }

        foreach ($extKeys as $extKey) {
            if (!\array_key_exists($extKey, $baseExtConfig) || null !== $extensionConfig[$extKey]) {
                $baseExtConfig[$extKey] = $extensionConfig[$extKey];
            }
        }

        if ($key) {
            $config[$extensionType][$key] = $baseExtConfig;
        } else {
            $config[$extensionType] = $baseExtConfig;
        }
    }
}
