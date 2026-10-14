<?php

declare(strict_types=1);

namespace Prex\Gifs\Infrastructure\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Prex\Gifs\Application\SearchGifs\SearchGifsQuery;
use Prex\Gifs\Domain\Pagination;
use Prex\Gifs\Domain\SearchTerm;
use Prex\Shared\Infrastructure\Http\Request\NormalizesInputKeys;

final class SearchGifsRequest extends FormRequest
{
    use NormalizesInputKeys;

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:'.SearchTerm::MAX_LENGTH],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.Pagination::MAX_LIMIT],
            'offset' => ['nullable', 'integer', 'min:0', 'max:'.Pagination::MAX_OFFSET],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.required' => 'The query parameter is required (the phrase or term to search for).',
        ];
    }

    public function toQuery(): SearchGifsQuery
    {
        return new SearchGifsQuery(
            term: (string) $this->input('query'),
            limit: $this->integerOrNull('limit'),
            offset: $this->integerOrNull('offset'),
        );
    }

    private function integerOrNull(string $key): ?int
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (int) $value;
    }
}
