<?php

namespace App\Support;

use Closure;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

/**
 * Gọi Gemini API (streamGenerateContent) và nhận về JSON theo `responseSchema`.
 *
 * @see https://ai.google.dev/api/generate-content
 */
class Gemini
{
    public function model(): string
    {
        return (string) config('services.gemini.model');
    }

    /**
     * Gọi streamGenerateContent (SSE): `$onText` nhận toàn bộ văn bản đã về sau mỗi đợt dữ liệu.
     *
     * @param  array<string, mixed>  $schema  OpenAPI schema của kết quả (responseSchema).
     * @param  list<array{mime_type: string, data: string}>  $images  Ảnh gửi kèm, `data` là nội dung nhị phân.
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     *
     * @throws RuntimeException khi thiếu khoá API, Gemini trả lỗi hoặc kết quả không phải JSON.
     */
    public function json(string $instruction, string $prompt, array $schema, array $images = [], ?Closure $onText = null): array
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new RuntimeException('Chưa cấu hình GEMINI_API_KEY.');
        }

        $parts = [
            ['text' => $prompt],
            ...array_map(fn (array $image): array => ['inline_data' => [
                'mime_type' => $image['mime_type'],
                'data' => base64_encode($image['data']),
            ]], $images),
        ];

        $response = Http::baseUrl(config('services.gemini.base_url'))
            ->withHeaders(['x-goog-api-key' => $key])
            ->withOptions(['stream' => true])
            ->timeout(config('services.gemini.timeout'))
            ->retry([2000, 5000], when: fn (Throwable $exception): bool => $exception instanceof ConnectionException
                || ($exception instanceof RequestException && in_array($exception->response->status(), [429, 500, 503], true)), throw: false)
            ->post("models/{$this->model()}:streamGenerateContent?alt=sse", [
                'system_instruction' => ['parts' => [['text' => $instruction]]],
                'contents' => [['role' => 'user', 'parts' => $parts]],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $schema,
                ],
            ]);

        if ($response->failed()) {
            // Lỗi của streamGenerateContent có thể là object hoặc mảng một phần tử.
            $message = $response->json('error.message') ?? $response->json('0.error.message') ?? Str::limit($response->body(), 500);

            throw new RuntimeException("Gemini lỗi {$response->status()} (model {$this->model()}): {$message}");
        }

        $text = '';
        $reason = null;

        foreach ($this->events($response->toPsrResponse()->getBody()) as $event) {
            // Mô hình có "thinking" có thể trả thêm phần suy luận (thought = true); chỉ lấy phần trả lời.
            $chunk = collect(data_get($event, 'candidates.0.content.parts', []))
                ->reject(fn (array $part): bool => $part['thought'] ?? false)
                ->pluck('text')
                ->implode('');

            $reason = data_get($event, 'candidates.0.finishReason') ?? data_get($event, 'promptFeedback.blockReason') ?? $reason;

            if ($chunk !== '') {
                $text .= $chunk;
                $onText?->__invoke($text);
            }
        }

        $result = json_decode($text, true);

        if (! is_array($result)) {
            throw new RuntimeException('Gemini không trả về JSON hợp lệ (lý do: '.($reason ?? 'không rõ').'): '.Str::limit($text, 300));
        }

        return $result;
    }

    /**
     * Đọc từng sự kiện `data: {...}` của luồng SSE ngay khi nó về.
     *
     * @return Generator<int, array<string, mixed>>
     */
    private function events(StreamInterface $body): Generator
    {
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            while (preg_match('/\r?\n\r?\n/', $buffer, $match, PREG_OFFSET_CAPTURE) === 1) {
                $block = substr($buffer, 0, $match[0][1]);
                $buffer = substr($buffer, $match[0][1] + strlen($match[0][0]));

                yield from $this->parseEvent($block);
            }
        }

        yield from $this->parseEvent($buffer);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseEvent(string $block): array
    {
        $data = collect(preg_split('/\r?\n/', $block))
            ->filter(fn (string $line): bool => str_starts_with($line, 'data:'))
            ->map(fn (string $line): string => ltrim(substr($line, 5)))
            ->implode("\n");

        $event = $data === '' ? null : json_decode($data, true);

        return is_array($event) ? [$event] : [];
    }
}
