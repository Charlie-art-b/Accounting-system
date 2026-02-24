<?php

namespace App\Services;

class SimplePdfService
{
    public function fromText(string $text, string $title = 'Export'): string
    {
        $clean = preg_replace('/\s+/', ' ', strip_tags($text)) ?? '';
        $words = preg_split('/\s+/', trim($clean)) ?: [];

        $lines = [$title];
        $line = '';
        foreach ($words as $word) {
            $next = trim($line . ' ' . $word);
            if (strlen($next) > 90) {
                if ($line !== '') {
                    $lines[] = $line;
                }
                $line = $word;
            } else {
                $line = $next;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $this->fromLines($lines);
    }

    public function fromLines(array $lines): string
    {
        $lines = array_values(array_filter(array_map(
            fn ($line) => trim((string) $line),
            $lines
        ), fn ($line) => $line !== ''));

        if (empty($lines)) {
            $lines = ['Sin contenido para exportar.'];
        }

        $content = "BT\n/F1 10 Tf\n14 TL\n40 800 Td\n";
        foreach ($lines as $line) {
            $escaped = $this->escapePdfText($line);
            $content .= "({$escaped}) Tj\nT*\n";
        }
        $content .= "ET\n";

        return $this->buildPdfDocument($content);
    }

    private function buildPdfDocument(string $stream): string
    {
        $objects = [];
        $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>";
        $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n{$stream}endstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $obj) {
            $offsets[] = strlen($pdf);
            $objNumber = $index + 1;
            $pdf .= "{$objNumber} 0 obj\n{$obj}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', ' ', ' '],
            $text
        );
    }
}

