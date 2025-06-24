<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models\Traits;

use MXRVX\Autoloader\App as Autoloader;
use MXRVX\Telegram\Bot\Tools\Caster;

/**
 * @psalm-type MetaData = array{
 * }
 */
trait ModelTrait
{
    public function __construct(\xPDO &$xpdo)
    {
        parent::__construct($xpdo);
        self::$modx ??= Autoloader::getModxInstance();
    }

    public static function modx(): \modX
    {
        return self::$modx ??= Autoloader::getModxInstance();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function makeFromData(array $data, bool $isNew): static
    {
        $metadata = static::getMetaData($data, $isNew);
        return static::makeFromMetaData($metadata);
    }

    /**
     * @param MetaData $metadata
     */
    public static function makeFromMetaData(array $metadata): static
    {
        $modx = self::modx();
        $instance = new static($modx);
        $instance->fromArray($metadata, '', true);

        $isNew = static::getMetaDataValue(static::FIELD_IS_NEW, $metadata);
        $instance->setNew($isNew);
        $instance->initChangedMetaDataFields($isNew);

        return $instance;
    }

    /**
     * @return string[]
     */
    public static function getMetaDataFields(): array
    {
        return \array_keys(static::$fieldMappers);
    }

    /**
     * @return array<string, callable>
     */
    public static function getMetaDataFieldMappers(): array
    {
        return static::$fieldMappers;
    }

    public static function getMetaDataFieldMapper(string $field): ?callable
    {
        return static::$fieldMappers[$field] ?? null;
    }

    /**
     * @param array<string, mixed> $data
     * @return MetaData
     */
    public static function getMetaData(array $data, bool $isNew = true): array
    {
        /** @psalm-var MetaData $metadata */
        $metadata = [];

        foreach (static::getMetaDataFieldMappers() as $field => $callable) {
            $metadata[$field] = \call_user_func($callable, $data[$field] ?? null);
        }

        $metadata[static::FIELD_IS_NEW] = $isNew;

        return $metadata;
    }

    public static function getMetaDataIsNew(mixed $value): bool
    {
        return Caster::bool($value);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function getMetaDataValue(string $field, array $data): mixed
    {
        $callable = static::getMetaDataFieldMapper($field);
        if (!$callable && $field !== static::FIELD_IS_NEW) {
            return null;
        } elseif (!$callable && $field === static::FIELD_IS_NEW) {
            return \call_user_func([static::class, 'getMetaDataIsNew'], $data[$field] ?? null);
        }

        return \call_user_func($callable, $data[$field] ?? null);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function getMetaDataQueryValues(array $data, array $fields = []): ?array
    {
        /** @psalm-var MetaData $metadata */
        $metadata = [];

        $fields = !empty($fields) ? $fields : static::FIELDS_FOR_QUERY;
        foreach ($fields as $field) {
            if ($callable = static::getMetaDataFieldMapper($field)) {
                $metadata[$field] = \call_user_func($callable, $data[$field] ?? null);
            }
        }

        return $metadata ?? null;
    }

    public static function getInstanceByQueryField(string $field, mixed $value): ?static
    {
        if (!\in_array($field, static::FIELDS_FOR_QUERY, true)) {
            return null;
        }

        return static::getInstance([$field => $value]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createInstance(array $data): ?static
    {
        return static::makeFromData($data, true);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function getInstance(array $data): ?static
    {
        if (!$query = static::getMetaDataQueryValues($data)) {
            return null;
        }

        if (!$row = static::loadInstanceRow($query)) {
            return null;
        }

        return static::makeFromData($row, false);
    }

    public static function getOrCreateInstance(array $data): ?static
    {
        if ($instance = static::getInstance($data)) {
            return $instance;
        }

        return static::createInstance($data);
    }

    public function getChangedMetaDataFields(): array
    {
        $fields = \array_filter($this->changedMetaDataFields, static fn($value) => $value === true);
        return \array_keys($fields);
    }

    public function hasChangedMetaData(?string $field = null): bool
    {
        return $field ? \in_array($field, $this->getChangedMetaDataFields(), true) : !empty($this->changedMetaDataFields);
    }

    public function getFieldValue(string $field): mixed
    {
        if ($mapper = static::getMetaDataFieldMapper($field)) {
            return \call_user_func($mapper, $this->get($field));
        }
        return null;
    }

    public function setFieldValue(string $field, mixed $value): static
    {
        if ($mapper = static::getMetaDataFieldMapper($field)) {
            $oldValue = \call_user_func($mapper, $this->get($field));
            $newValue = \call_user_func($mapper, $value);

            if ($oldValue !== $newValue) {
                $this->setChangedMetaDataField($field);
            }
            $this->set($field, $newValue);
        }
        return $this;
    }

    public function toMetaData(): array
    {
        $data = $this->toArray('', true);

        $metadata = static::getMetaData($data, static::getMetaDataValue(static::FIELD_IS_NEW, $data));

        return $metadata;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fill(array $data): static
    {
        foreach ($data as $field => $value) {
            $this->setFieldValue($field, $value);
        }
        return $this;
    }

    private static function loadInstanceRow(array $query): ?array
    {
        $modx = self::modx();
        $c = $modx->newQuery(static::class);
        $c->where($query);
        $c->select(\implode(',', static::getMetaDataFields()));

        $row = [];
        $stmt = $c->prepare();
        if ($stmt instanceof \PDOStatement) {
            $start = \microtime(true);
            if ($stmt->execute()) {
                $modx->queryTime += \microtime(true) - $start;
                $modx->executedQueries++;
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $c->toSQL()));
            }
        } else {
            $modx->log(\modX::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', (string) $modx->errorInfo(), $c->toSQL()));
        }

        return !empty($row) && \is_array($row) ? $row : null;
    }

    private function setNew(bool $isNew): void
    {
        $this->_new = $isNew;
    }

    private function initChangedMetaDataFields(bool $changed = true): void
    {
        foreach (static::getMetaDataFields() as $field) {
            $this->changedMetaDataFields[$field] = $changed;
        }
    }

    private function setChangedMetaDataField(string $field, bool $changed = true): void
    {
        if (isset($this->changedMetaDataFields[$field])) {
            $this->changedMetaDataFields[$field] = $changed;
        }
    }
}
