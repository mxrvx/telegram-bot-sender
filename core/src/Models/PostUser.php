<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

use MXRVX\Telegram\Bot\Tools\Caster;

/**
 * @psalm-type MetaData = array{
 * post_id: int,
 * user_id: int,
 * is_send: bool,
 * is_success: bool,
 * created_at: int,
 * sended_at: int
 * }
 */

class PostUser extends Model
{
    public const FIELD_POST_ID = 'post_id';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_IS_SEND = 'is_send';
    public const FIELD_IS_SUCCESS = 'is_success';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_SENDED_AT = 'sended_at';
    public const FIELDS_FOR_QUERY = [
        self::FIELD_POST_ID,
        self::FIELD_USER_ID,
    ];

    /**
     * @var array<string, callable>
     */
    protected static array $fieldMappers = [
        self::FIELD_POST_ID => [self::class, 'getMetaDataPostId'],
        self::FIELD_USER_ID => [self::class, 'getMetaDataUserId'],
        self::FIELD_IS_SEND => [self::class, 'getMetaDataIsSend'],
        self::FIELD_IS_SUCCESS => [self::class, 'getMetaDataIsSuccess'],
        self::FIELD_CREATED_AT => [self::class, 'getMetaDataCreatedAt'],
        self::FIELD_SENDED_AT => [self::class, 'getMetaDataSendedAt'],
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

    public static function getMetaDataPostId(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataUserId(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataIsSend(mixed $value): bool
    {
        return Caster::bool($value);
    }

    public static function getMetaDataIsSuccess(mixed $value): bool
    {
        return Caster::bool($value);
    }

    public static function getMetaDataCreatedAt(mixed $value): int
    {
        return Caster::int($value);
    }

    public static function getMetaDataSendedAt(mixed $value): int
    {
        return Caster::int($value);
    }

    public function getPostId(): int
    {
        return $this->getFieldValue(self::FIELD_POST_ID);
    }

    public function getUserID(): int
    {
        return $this->getFieldValue(self::FIELD_USER_ID);
    }

    public function getIsSend(): bool
    {
        return $this->getFieldValue(self::FIELD_IS_SEND);
    }

    public function getIsSuccess(): bool
    {
        return $this->getFieldValue(self::FIELD_IS_SUCCESS);
    }

    public function getCreatedAt(): int
    {
        return $this->getFieldValue(self::FIELD_CREATED_AT);
    }

    public function getSendedAt(): int
    {
        return $this->getFieldValue(self::FIELD_SENDED_AT);
    }

    public function setPostId(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_POST_ID, $value);
    }

    public function setUserID(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_USER_ID, $value);
    }

    public function setIsSend(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_IS_SEND, $value);
    }

    public function setIsSuccess(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_IS_SUCCESS, $value);
    }

    public function setCreatedAt(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_CREATED_AT, $value);
    }

    public function setSendedAt(mixed $value): self
    {
        return $this->setFieldValue(self::FIELD_SENDED_AT, $value);
    }

    public function save($cacheFlag = null)
    {
        if ($this->isNew()) {
            $this->setCreatedAt(\time());
        }

        if ($this->hasChangedMetaData(self::FIELD_IS_SEND) && $this->getIsSend()) {
            $this->setSendedAt(\time());
        }

        return parent::save($cacheFlag);
    }
}
