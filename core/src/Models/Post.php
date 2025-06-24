<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

use MXRVX\Telegram\Bot\Models\User;
use MXRVX\Telegram\Bot\Tools\Caster;

/**
 * @psalm-type MetaData = array{
 * id: int,
 * title: string,
 * is_active: bool,
 * is_send: bool,
 * created_at: int,
 * updated_at: int,
 * sended_at: int,
 * content: array
 * }
 */
class Post extends ModelWithId
{
    public const FIELD_ID = 'id';
    public const FIELD_TITLE = 'title';
    public const FIELD_IS_ACTIVE = 'is_active';
    public const FIELD_IS_SEND = 'is_send';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_SENDED_AT = 'sended_at';
    public const FIELD_CONTENT = 'content';
    public const FIELDS_FOR_QUERY = [
        self::FIELD_ID,
    ];

    /**
     * @var array<string, callable>
     */
    protected static array $fieldMappers = [
        self::FIELD_ID => [self::class, 'getMetaDataId'],
        self::FIELD_TITLE => [self::class, 'getMetaDataTitle'],
        self::FIELD_IS_ACTIVE => [self::class, 'getMetaDataIsActive'],
        self::FIELD_IS_SEND => [self::class, 'getMetaDataIsSend'],
        self::FIELD_CREATED_AT => [self::class, 'getMetaDataCreatedAt'],
        self::FIELD_UPDATED_AT => [self::class, 'getMetaDataUpdatedAt'],
        self::FIELD_SENDED_AT => [self::class, 'getMetaDataSendedAt'],
        self::FIELD_CONTENT => [self::class, 'getMetaDataContent'],
    ];

    /**
     * @param array<string, mixed> $data
     */
    public static function getMetaDataQueryValues(array $data, array $fields = []): ?array
    {
        /** @psalm-var MetaData|null $metadata */
        if (!$metadata = parent::getMetaDataQueryValues($data, $fields)) {
            return null;
        }

        $metadata = \array_filter($metadata, static function ($value) {
            return !empty($value);
        });

        return $metadata ?? null;
    }

    public static function getMetaDataId(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataTitle(mixed $value): string
    {
        return Caster::string($value);
    }

    public static function getMetaDataIsActive(mixed $value): bool
    {
        return Caster::bool($value);
    }

    public static function getMetaDataIsSend(mixed $value): bool
    {
        return Caster::bool($value);
    }

    public static function getMetaDataCreatedAt(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataUpdatedAt(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataSendedAt(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataContent(mixed $value): array
    {
        return Caster::array($value);
    }

    public function getId(): int
    {
        return $this->getFieldValue(self::FIELD_ID);
    }

    public function getTitle(): string
    {
        return $this->getFieldValue(self::FIELD_TITLE);
    }

    public function getIsActive(): bool
    {
        return $this->getFieldValue(self::FIELD_IS_ACTIVE);
    }

    public function getIsSend(): bool
    {
        return $this->getFieldValue(self::FIELD_IS_SEND);
    }

    public function getCreatedAt(): int
    {
        return $this->getFieldValue(self::FIELD_CREATED_AT);
    }

    public function getUpdatedAt(): int
    {
        return $this->getFieldValue(self::FIELD_UPDATED_AT);
    }

    public function getSendedAt(): int
    {
        return $this->getFieldValue(self::FIELD_SENDED_AT);
    }

    public function getContent(): array
    {
        return $this->getFieldValue(self::FIELD_CONTENT);
    }

    public function setId(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_ID, $value);
    }

    public function setTitle(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_TITLE, $value);
    }

    public function setIsActive(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_IS_ACTIVE, $value);
    }

    public function setIsSend(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_IS_SEND, $value);
    }

    public function setCreatedAt(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_CREATED_AT, $value);
    }

    public function setUpdatedAt(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_UPDATED_AT, $value);
    }

    public function setSendedAt(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_SENDED_AT, $value);
    }

    public function setContent(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_CONTENT, $value);
    }

    public function save($cacheFlag = null)
    {
        if ($this->isNew()) {
            $this->setCreatedAt(\time());
            $this->setIsActive(true);
            $this->setIsSend(false);
        } else {
            $this->setUpdatedAt(\time());
        }

        if ($this->hasChangedMetaData(self::FIELD_IS_SEND) && $this->getIsSend()) {
            if ($this->makeSend()) {
                $this->setSendedAt(\time());
            } else {
                $this->setIsSend(false);
            }
        }

        $result = parent::save($cacheFlag);

        return $result;
    }

    public function makeSend(): bool
    {
        $postId = (int) parent::get('id');
        $tablePostUser = $this->xpdo->getTableName(PostUser::class);
        $tableUser = $this->xpdo->getTableName(User::class);
        $columns = $this->xpdo->getSelectColumns(PostUser::class);
        $statuses = "'" . \implode("','", [User::STATUS_UNKNOWN, User::STATUS_KICKED, User::STATUS_LEFT]) . "'";

        $sql = \sprintf(
            <<<SQL
                INSERT INTO %s (%s)
                SELECT %d, id, 0, 0, UNIX_TIMESTAMP(), 0
                FROM %s u
                WHERE u.status NOT IN (%s) AND
                NOT EXISTS (
                    SELECT 1 FROM %s pu WHERE pu.post_id = %d AND pu.user_id = u.id
                )
                SQL,
            $tablePostUser,
            $columns,
            $postId,
            $tableUser,
            $statuses,
            $tablePostUser,
            $postId,
        );

        $stmt = $this->xpdo->prepare($sql);
        if ($stmt instanceof \PDOStatement) {
            if ($stmt->execute()) {
                return true;
            }
            $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', \var_export($stmt->errorInfo(), true), $sql));
        } else {
            $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, \sprintf('Error: %s SQL: %s', $this->xpdo->errorInfo(), $sql));
        }

        return false;
    }
}
