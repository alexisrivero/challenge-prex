<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property string $gif_id
 * @property string $alias
 * @property Carbon $saved_at
 */
final class FavoriteGifModel extends Model
{
    protected $table = 'favorite_gifs';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['id', 'user_id', 'gif_id', 'alias', 'saved_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'saved_at' => 'immutable_datetime',
        ];
    }
}
