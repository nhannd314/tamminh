<?php

namespace App\Support;

/**
 * Đọc JSON còn dang dở (đang được Gemini stream về) thành mảng PHP để hiển thị dần.
 *
 * Chuỗi đang viết dở vẫn được lấy phần đã có; số, true/false/null, khoá object chưa trọn
 * và giá trị null bị bỏ qua. Gặp ký tự không hợp lệ thì dừng và trả phần đọc được.
 */
final class PartialJson
{
    private int $position = 0;

    private readonly int $length;

    private function __construct(private readonly string $json)
    {
        $this->length = strlen($json);
    }

    public static function decode(string $json): mixed
    {
        return (new self($json))->value();
    }

    private function value(): mixed
    {
        $this->skipWhitespace();

        if ($this->isAtEnd()) {
            return null;
        }

        return match ($this->json[$this->position]) {
            '{' => $this->object(),
            '[' => $this->array(),
            '"' => $this->string()[0],
            default => $this->scalar(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function object(): array
    {
        $this->position++;
        $result = [];

        while (! $this->isAtEnd()) {
            $this->skipWhitespace();
            $char = $this->json[$this->position] ?? null;

            if ($char === '}') {
                $this->position++;

                return $result;
            }

            if ($char === ',') {
                $this->position++;

                continue;
            }

            if ($char !== '"') {
                break;
            }

            [$key, $isComplete] = $this->string();
            $this->skipWhitespace();

            if (! $isComplete || ($this->json[$this->position] ?? null) !== ':') {
                break;
            }

            $this->position++;
            $value = $this->value();

            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        $this->position = $this->length;

        return $result;
    }

    /**
     * @return list<mixed>
     */
    private function array(): array
    {
        $this->position++;
        $result = [];

        while (! $this->isAtEnd()) {
            $this->skipWhitespace();
            $char = $this->json[$this->position] ?? null;

            if ($char === ']') {
                $this->position++;

                return $result;
            }

            if ($char === ',') {
                $this->position++;

                continue;
            }

            if ($char === null) {
                break;
            }

            $before = $this->position;
            $value = $this->value();

            if ($value !== null) {
                $result[] = $value;
            }

            if ($this->position === $before) {
                break;
            }
        }

        $this->position = $this->length;

        return $result;
    }

    /**
     * @return array{0: string, 1: bool} Nội dung chuỗi và chuỗi đã đóng ngoặc kép hay chưa.
     */
    private function string(): array
    {
        $start = ++$this->position;

        while ($this->position < $this->length) {
            $char = $this->json[$this->position];

            if ($char === '"') {
                $raw = substr($this->json, $start, $this->position - $start);
                $this->position++;

                return [$this->unescape($raw), true];
            }

            if ($char === '\\') {
                $escapeLength = ($this->json[$this->position + 1] ?? null) === 'u' ? 6 : 2;

                // Chuỗi bị cắt giữa một ký tự thoát thì bỏ ký tự thoát dở đó.
                if ($this->position + $escapeLength > $this->length) {
                    break;
                }

                $this->position += $escapeLength;

                continue;
            }

            $this->position++;
        }

        $raw = substr($this->json, $start, $this->position - $start);
        $this->position = $this->length;

        return [$this->unescape($raw), false];
    }

    /**
     * Số hoặc true/false/null. Đang viết dở ở cuối chuỗi thì chưa chắc đủ chữ số nên bỏ qua.
     */
    private function scalar(): int|float|bool|null
    {
        preg_match('/\G(?:-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null)/', $this->json, $matches, 0, $this->position);

        if ($matches === [] || $this->position + strlen($matches[0]) >= $this->length) {
            $this->position = $this->length;

            return null;
        }

        $this->position += strlen($matches[0]);

        return json_decode($matches[0]);
    }

    private function unescape(string $raw): string
    {
        $decoded = json_decode('"'.$raw.'"');

        return is_string($decoded) ? $decoded : stripslashes($raw);
    }

    private function skipWhitespace(): void
    {
        $this->position += strspn($this->json, " \t\n\r", $this->position);
    }

    private function isAtEnd(): bool
    {
        return $this->position >= $this->length;
    }
}
