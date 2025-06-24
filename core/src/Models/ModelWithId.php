<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

/**
 * @psalm-import-type MetaData from ModelInterface
 */
abstract class ModelWithId extends \xPDOSimpleObject implements ModelInterface
{
    use Traits\ModelTrait;

    public const FIELD_ID = 'id';
    public const FIELD_IS_NEW = 'is_new';

    /** @var string[] */
    public const FIELDS_FOR_QUERY = [
        self::FIELD_ID,
    ];

    /**
     * @var array<string, callable>
     */
    protected static array $fieldMappers = [];

    protected static \modX $modx;
    protected array $changedMetaDataFields = [];
}
