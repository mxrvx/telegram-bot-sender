<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Models;

/**
 * @psalm-import-type MetaData from ModelInterface
 */
abstract class Model extends \XPDOObject implements ModelInterface
{
    use Traits\ModelTrait;

    public const FIELD_IS_NEW = 'is_new';

    /** @var string[] */
    public const FIELDS_FOR_QUERY = [];

    /**
     * @var array<string, callable>
     */
    protected static array $fieldMappers = [];

    protected static \modX $modx;
    protected array $changedMetaDataFields = [];
}
