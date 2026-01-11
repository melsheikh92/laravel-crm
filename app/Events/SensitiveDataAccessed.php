<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensitiveDataAccessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The model being accessed.
     *
     * @var Model
     */
    public $model;

    /**
     * The fields being accessed.
     *
     * @var array
     */
    public $fields;

    /**
     * The access type (view, export, etc).
     *
     * @var string
     */
    public $accessType;

    /**
     * Additional metadata about the access.
     *
     * @var array
     */
    public $metadata;

    /**
     * Additional tags for the audit log.
     *
     * @var array|null
     */
    public $tags;

    /**
     * The user ID performing the access (optional, defaults to authenticated user).
     *
     * @var int|null
     */
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param array $fields
     * @param string $accessType
     * @param array $metadata
     * @param array|null $tags
     * @param int|null $userId
     * @return void
     */
    public function __construct(
        Model $model,
        array $fields = [],
        string $accessType = 'view',
        array $metadata = [],
        ?array $tags = null,
        ?int $userId = null
    ) {
        $this->model = $model;
        $this->fields = $fields;
        $this->accessType = $accessType;
        $this->metadata = $metadata;
        $this->tags = $tags;
        $this->userId = $userId;
    }
}
