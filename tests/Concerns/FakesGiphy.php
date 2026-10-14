<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

/**
 * Fakes the GIPHY API at the HTTP boundary: the adapter under test is the real
 * one, only the network is simulated.
 */
trait FakesGiphy
{
    /**
     * @param  list<string>  $ids
     */
    protected function fakeGiphySearch(array $ids, ?int $totalCount = null): void
    {
        Http::fake([
            '*/gifs/search*' => Http::response([
                'data' => array_map(fn (string $id): array => $this->giphyGifPayload($id), $ids),
                'pagination' => [
                    'total_count' => $totalCount ?? count($ids),
                    'count' => count($ids),
                    'offset' => 0,
                ],
                'meta' => ['status' => 200, 'msg' => 'OK'],
            ], 200),
        ]);
    }

    protected function fakeGiphyGif(string $id): void
    {
        Http::fake([
            '*/gifs/'.$id.'*' => Http::response([
                'data' => $this->giphyGifPayload($id),
                'meta' => ['status' => 200, 'msg' => 'OK'],
            ], 200),
        ]);
    }

    protected function fakeGiphyNotFound(): void
    {
        Http::fake([
            '*/gifs/*' => Http::response([
                'data' => [],
                'meta' => ['status' => 404, 'msg' => 'Not Found'],
            ], 404),
        ]);
    }

    protected function fakeGiphyOutage(int $status = 500): void
    {
        Http::fake(['*' => Http::response(['meta' => ['status' => $status]], $status)]);
    }

    /**
     * A trimmed down version of a real GIPHY gif object.
     *
     * @return array<string, mixed>
     */
    protected function giphyGifPayload(string $id): array
    {
        return [
            'type' => 'gif',
            'id' => $id,
            'url' => 'https://giphy.com/gifs/'.$id,
            'slug' => 'funny-cat-'.$id,
            'title' => 'Funny cat '.$id,
            'rating' => 'g',
            'username' => 'prex',
            'images' => [
                'original' => [
                    'url' => 'https://media.giphy.com/media/'.$id.'/giphy.gif',
                    'width' => '480',
                    'height' => '270',
                    'size' => '1048576',
                ],
                'fixed_width_downsampled' => [
                    'url' => 'https://media.giphy.com/media/'.$id.'/200w_d.gif',
                    'width' => '200',
                    'height' => '112',
                    'size' => '32768',
                ],
            ],
        ];
    }
}
