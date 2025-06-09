<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Sender\Controllers;

abstract class ModelController extends Controller
{
    use Traits\ModelTrait;
    use Traits\ModelOperationTrait;

    /** @var class-string */
    protected string $model;

    protected string $alias;

    /** @var string|array<string> */
    protected string|array $primaryKey = 'id';

    protected string $defaultSortField = 'id';
    protected string $defaultSortDirection = 'asc';
    protected int $maxLimit = 100;

    /** @var array<string> */
    protected array $searchFields = [];
}
