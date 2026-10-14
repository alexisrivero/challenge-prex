<?php

declare(strict_types=1);

namespace Prex\Audit\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string $http_method
 * @property string $path
 * @property string|null $service_name
 * @property array<array-key, mixed> $request_body
 * @property int $response_status
 * @property array<array-key, mixed> $response_body
 * @property string $ip_address
 * @property Carbon $occurred_at
 */
final class ServiceInteractionModel extends Model
{
    protected $table = 'service_interactions';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id',
        'user_id',
        'http_method',
        'path',
        'service_name',
        'request_body',
        'response_status',
        'response_body',
        'ip_address',
        'occurred_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'request_body' => 'array',
            'response_body' => 'array',
            'response_status' => 'integer',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
