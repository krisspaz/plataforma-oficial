<?php

declare(strict_types=1);

namespace App\Domain\Contract\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Domain service for PDF generation.
 */
final class PDFGenerator
{
    private Dompdf $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('dpi', 96);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF from HTML content.
     */
    public function generateFromHtml(string $html, string $filename = 'document.pdf'): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('letter', 'portrait');
        $this->dompdf->render();

        return $this->dompdf->output();
    }

    /**
     * Generate and save PDF to file.
     */
    public function generateAndSave(string $html, string $filepath): void
    {
        $pdf = $this->generateFromHtml($html);
        file_put_contents($filepath, $pdf);
    }

    /**
     * Generate PDF and return as base64.
     */
    public function generateAsBase64(string $html): string
    {
        $pdf = $this->generateFromHtml($html);
        return base64_encode($pdf);
    }

    /**
     * Add watermark to PDF.
     */
    public function addWatermark(string $html, string $watermarkText): string
    {
        $watermarkHtml = sprintf(
            '<div style="position: fixed; top: 50%%; left: 50%%; transform: translate(-50%%, -50%%) rotate(-45deg); 
                        font-size: 120px; color: rgba(200, 200, 200, 0.3); z-index: -1; 
                        font-weight: bold; white-space: nowrap;">%s</div>',
            htmlspecialchars($watermarkText)
        );

        // Insert watermark before closing body tag
        return str_replace('</body>', $watermarkHtml . '</body>', $html);
    }

    /**
     * Add header and footer to HTML.
     */
    public function addHeaderFooter(string $html, ?string $header = null, ?string $footer = null): string
    {
        if ($header) {
            $headerHtml = sprintf(
                '<div style="position: fixed; top: 0; left: 0; right: 0; height: 50px; 
                            background: #f8f9fa; border-bottom: 2px solid #dee2e6; 
                            padding: 10px; text-align: center;">%s</div>
                <div style="height: 70px;"></div>',
                $header
            );
            $html = str_replace('<body>', '<body>' . $headerHtml, $html);
        }

        if ($footer) {
            $footerHtml = sprintf(
                '<div style="height: 50px;"></div>
                <div style="position: fixed; bottom: 0; left: 0; right: 0; height: 40px; 
                            background: #f8f9fa; border-top: 2px solid #dee2e6; 
                            padding: 10px; text-align: center; font-size: 12px;">%s</div>',
                $footer
            );
            $html = str_replace('</body>', $footerHtml . '</body>', $html);
        }

        return $html;
    }

    /**
     * Get PDF metadata.
     */
    public function setMetadata(string $title, string $author, string $subject = ''): void
    {
        $this->dompdf->getCanvas()->get_cpdf()->setTitle($title);
        $this->dompdf->getCanvas()->get_cpdf()->setAuthor($author);

        if ($subject) {
            $this->dompdf->getCanvas()->get_cpdf()->setSubject($subject);
        }
    }
}
