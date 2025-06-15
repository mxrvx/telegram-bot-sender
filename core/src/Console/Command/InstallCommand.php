<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MXRVX\Telegram\Bot\Sender\App;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';
    protected static $defaultDescription = 'Install "' . App::NAMESPACE . '" extra for MODX';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = $this->app;
        $modx = $this->app->modx;

        $srcPath = MODX_CORE_PATH . 'vendor/' . (string) \preg_replace('/-/', '/', App::NAMESPACE, 1);
        $corePath = MODX_CORE_PATH . 'components/' . App::NAMESPACE;
        if (!\is_dir($corePath)) {
            \symlink($srcPath . '/core', $corePath);
            $output->writeln('<info>Created symlink for `core`</info>');
        }

        $assetsPath = MODX_ASSETS_PATH . 'components/' . App::NAMESPACE;
        if (!\is_dir($assetsPath)) {
            \symlink($srcPath . '/assets', $assetsPath);
            $output->writeln('<info>Created symlink for `assets`</info>');
        }

        if (!$modx->getObject(\modNamespace::class, ['name' => App::NAMESPACE])) {
            /** @var \modNamespace $namespace */
            $namespace = $modx->newObject(\modNamespace::class);
            $namespace->fromArray(
                [
                    'name' => App::NAMESPACE,
                    'path' => '{core_path}components/' . App::NAMESPACE . '/',
                    'assets_path' => '',
                ],
                '',
                true,
            );
            $namespace->save();
            $output->writeln(\sprintf('<info>Created namespace `%s`</info>', App::NAMESPACE));
        }

        if (!$modx->getObject(\modMenu::class, ['namespace' => App::NAMESPACE])) {
            /** @var \modMenu $menu */
            $menu = $modx->newObject(\modMenu::class);
            $menu->fromArray(
                [
                    'action' => 'index',
                    'namespace' => App::NAMESPACE,
                    'text' => App::NAMESPACE . '.menu.index.text',
                    'description' => App::NAMESPACE . '.menu.index.description',
                    'icon' => '',
                    'parent' => 'components',
                    'params' => '',
                    'handler' => '',
                    'menuindex' => $modx->getCount(\modMenu::class, ['parent' => 'components']),
                ],
                '',
                true,
            );
            $menu->save();
            $output->writeln(\sprintf('<info>Created menu `%s`</info>', App::NAMESPACE));
        }

        /** @var array{key: string, value: mixed} $row */
        foreach ($app->config->getSettingsArray() as $row) {
            if (!$modx->getObject(\modSystemSetting::class, $row['key'])) {
                /** @var \modSystemSetting $setting */
                $setting = $modx->newObject(\modSystemSetting::class);
                $setting->fromArray($row, '', true);
                $setting->save();
                $output->writeln(\sprintf('<info>Created system setting `%s`</info>', $row['key']));
            }
        }

        $schemaFile = $corePath . '/schema/' . App::NAMESPACE . '.mysql.schema.xml';
        if (\file_exists($schemaFile)) {
            $modx->addPackage(
                App::NAMESPACE,
                MODX_CORE_PATH . 'components/' . App::NAMESPACE . '/src/Models/' . App::NAMESPACE . '/',
            );

            /** @var \xPDOCacheManager $cache */
            if ($cache = $modx->getCacheManager()) {
                $cache->deleteTree(
                    $corePath . '/src/Models/' . App::NAMESPACE . '/' . App::NAMESPACE . '/mysql',
                    ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []],
                );
            }

            $generator = null;
            $manager = $modx->getManager();
            if ($manager instanceof \xPDOManager) {
                $generator = $manager->getGenerator();
            }

            if ($generator instanceof \xPDOGenerator) {
                if (!$generator->parseSchema($schemaFile, $corePath . '/src/Models/' . App::NAMESPACE . '/')) {
                    $output->writeln(
                        \sprintf('<error>Model regeneration failed! Error parsing schema `%s`</error>', $schemaFile),
                    );
                } else {
                    $output->writeln(
                        \sprintf('<info>Regeneration of model files completed successfully `%s`</info>', $schemaFile),
                    );
                    $this->updateTables($schemaFile, $output);
                }
            }

        }

        $modx->getCacheManager()->refresh();

        $output->writeln('<info>Cleared MODX cache</info>');

        return Command::SUCCESS;
    }

    public function updateTables(string $schemaFile, OutputInterface $output): void
    {
        $modx = $this->app->modx;
        $manager = $modx->getManager();
        if (!($manager instanceof \xPDOManager)) {
            return;
        }
        $schema = new \SimpleXMLElement($schemaFile, 0, true);
        $objects = [];

        if (isset($schema->object)) {
            foreach ($schema->object as $obj) {
                if ($class = (string) ($obj['class'] ?? '')) {
                    $objects[] = $class;
                }
            }
        }

        foreach ($objects as $class) {
            $table = $modx->getTableName($class);
            if (empty($table)) {
                $output->writeln(\sprintf('<error>I can\'t get a table for the class `%s`</error>', $class));

                continue;
            }

            $sql = "SHOW TABLES LIKE '" . \trim($table, '`') . "'";

            $newTable = true;
            /** @var \PDOStatement|bool $stmt */
            $stmt = $modx->prepare($sql);

            if ($stmt instanceof \PDOStatement && $stmt->execute() && $stmt->fetchAll()) {
                $newTable = false;
            }

            // If the table is just created
            if ($newTable) {
                $manager->createObjectContainer($class);
                $output->writeln(\sprintf('<info>Create table `%s`</info>', $class));
            } else {
                // If the table exists
                // 1. Operate with tables
                $tableFields = [];

                /** @var \PDOStatement|bool $stmt */
                $stmt = $modx->prepare(\sprintf('SHOW COLUMNS IN %s', $modx->getTableName($class)));
                if ($stmt instanceof \PDOStatement) {
                    $stmt->execute();
                } else {
                    continue;
                }


                while ($cl = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $field = (string) ($cl['Field'] ?? '');
                    if (!empty($field)) {
                        $tableFields[$field] = $field;
                    }
                }

                /** @var array<string> $fields */
                $fields = \array_keys($modx->getFields($class));
                foreach ($fields as $field) {
                    if (\in_array($field, $tableFields, true)) {
                        unset($tableFields[$field]);
                        $manager->alterField($class, $field);
                    } else {
                        $manager->addField($class, $field);
                    }
                }

                foreach ($tableFields as $field) {
                    $manager->removeField($class, $field);
                }

                // 2. Operate with indexes

                $indexes = [];

                /** @var \PDOStatement|bool $stmt */
                $stmt = $modx->prepare(\sprintf('SHOW INDEX FROM %s', $modx->getTableName($class)));
                if ($stmt instanceof \PDOStatement) {
                    $stmt->execute();
                } else {
                    continue;
                }


                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $name = (string) ($row['Key_name'] ?? '');

                    if (!isset($indexes[$name])) {
                        $indexes[$name] = [(string) $row['Column_name']];
                    } else {
                        $indexes[$name][] = (string) $row['Column_name'];
                    }
                }

                //var_export($indexes);die;

                foreach ($indexes as $name => $values) {
                    \sort($values);
                    $indexes[$name] = \implode(':', $values);
                }


                /**
                 * @var array<string, array{
                 *     alias: string,
                 *     primary: bool,
                 *     unique: bool,
                 *     type: string,
                 *     columns: array<string, array{}>
                 * }> $map
                 */
                $map = $modx->getIndexMeta($class);

                // Remove old indexes
                foreach ($indexes as $key => $index) {
                    if (!isset($map[$key])) {
                        if ($manager->removeIndex($class, $key)) {
                            $output->writeln(
                                \sprintf('<info>Removed index `%s` of the table `%s`</info>', $key, $class),
                            );
                        }
                    }
                }

                // Add or alter existing
                foreach ($map as $key => $index) {
                    \ksort($index['columns']);
                    $index = \implode(':', \array_keys($index['columns']));

                    if (!isset($indexes[$key])) {
                        if ($manager->addIndex($class, $key)) {
                            $output->writeln(\sprintf('<info>Added index `%s` in the table `%s`</info>', $key, $class));
                        }
                    } else {
                        if ($index !== $indexes[$key]) {
                            if ($manager->removeIndex($class, $key) && $manager->addIndex($class, $key)) {
                                $output->writeln(
                                    \sprintf('<info>Updated index `%s` of the table `%s`</info>', $key, $class),
                                );
                            }
                        }
                    }
                }
            }
            // END FOREACH
        }
        // END FUNC
    }
}
