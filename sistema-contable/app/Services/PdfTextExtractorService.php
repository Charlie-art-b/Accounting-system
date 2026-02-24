<?php

namespace App\Services;

class PdfTextExtractorService
{
    public function extractText(string $filePath): string
    {
        $content = @file_get_contents($filePath);

        if (! is_string($content) || $content === '') {
            return '';
        }

        $textChunks = [];

        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = $this->decodeStream($stream);
                $text = $this->extractTextOperators($decoded);

                if ($text !== '') {
                    $textChunks[] = $text;
                }
            }
        }

        if (! empty($textChunks)) {
            return trim(implode("\n", $textChunks));
        }

        return $this->extractTextOperators($content);
    }

    private function decodeStream(string $stream): string
    {
        $candidates = [
            $stream,
            @gzuncompress($stream),
            @gzinflate($stream),
            @gzinflate(substr($stream, 2)),
            @gzdecode($stream),
        ];

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }

            if (preg_match('/\((.*?)\)\s*Tj/s', $candidate) || preg_match('/\[(.*?)\]\s*TJ/s', $candidate)) {
                return $candidate;
            }
        }

        return $stream;
    }

    private function extractTextOperators(string $payload): string
    {
        $lines = [];

        if (preg_match_all('/\((.*?)\)\s*Tj/s', $payload, $singleMatches)) {
            foreach ($singleMatches[1] as $fragment) {
                $clean = $this->decodePdfString($fragment);
                if ($clean !== '') {
                    $lines[] = $clean;
                }
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $payload, $arrayMatches)) {
            foreach ($arrayMatches[1] as $fragmentGroup) {
                if (preg_match_all('/\((.*?)\)/s', $fragmentGroup, $parts)) {
                    $line = '';
                    foreach ($parts[1] as $fragment) {
                        $line .= $this->decodePdfString($fragment);
                    }
                    $line = trim($line);
                    if ($line !== '') {
                        $lines[] = $line;
                    }
                }
            }
        }

        return trim(implode("\n", $lines));
    }

    private function decodePdfString(string $value): string
    {
        $value = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $value);
        $value = preg_replace('/\\\\[0-7]{1,3}/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value ?? '');

        return trim($value ?? '');
    }
}

