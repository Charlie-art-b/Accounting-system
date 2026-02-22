<?php

namespace App\Services;

class PdfFallbackService
{
    public function download(
        string $view,
        array $data,
        string $baseFileName,
        string $paper = 'a4',
        string $orientation = 'portrait',
        array $options = []
    ) {
        $pdfFacade = '\Barryvdh\DomPDF\Facade\Pdf';

        if (class_exists($pdfFacade)) {
            $pdf = $pdfFacade::loadView($view, $data)->setPaper($paper, $orientation);
            if (! empty($options)) {
                $pdf->setOptions($options);
            }
            return $pdf->download($baseFileName . '.pdf');
        }

        $pdfBytes = app(SimplePdfService::class)->fromText(
            view($view, $data)->render(),
            $baseFileName
        );

        return response()->streamDownload(function () use ($pdfBytes) {
            echo $pdfBytes;
        }, $baseFileName . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function stream(
        string $view,
        array $data,
        string $baseFileName,
        string $paper = 'a4',
        string $orientation = 'portrait',
        array $options = []
    ) {
        $pdfFacade = '\Barryvdh\DomPDF\Facade\Pdf';

        if (class_exists($pdfFacade)) {
            $pdf = $pdfFacade::loadView($view, $data)->setPaper($paper, $orientation);
            if (! empty($options)) {
                $pdf->setOptions($options);
            }
            return $pdf->stream($baseFileName . '.pdf');
        }

        $pdfBytes = app(SimplePdfService::class)->fromText(
            view($view, $data)->render(),
            $baseFileName
        );

        return response($pdfBytes, 200, ['Content-Type' => 'application/pdf']);
    }

    public function renderBinary(
        string $view,
        array $data,
        string $paper = 'a4',
        string $orientation = 'portrait',
        array $options = []
    ): array {
        $pdfFacade = '\Barryvdh\DomPDF\Facade\Pdf';

        if (class_exists($pdfFacade)) {
            $pdf = $pdfFacade::loadView($view, $data)->setPaper($paper, $orientation);
            if (! empty($options)) {
                $pdf->setOptions($options);
            }
            return ['ext' => 'pdf', 'content' => $pdf->output()];
        }

        return [
            'ext' => 'pdf',
            'content' => app(SimplePdfService::class)->fromText(
                view($view, $data)->render(),
                'Export'
            ),
        ];
    }
}
