<?php

namespace Tests\Unit;

use App\Support\CertificatePdf;
use PHPUnit\Framework\TestCase;

class CertificatePdfTest extends TestCase
{
    public function test_renders_delegate_name_in_bold_helvetica_and_red(): void
    {
        $pdf = "%PDF-1.4\n"
            ."1 0 obj\n<</Type /Catalog /Pages 2 0 R>>\nendobj\n"
            ."2 0 obj\n<</Type /Pages /Kids [3 0 R] /Count 1>>\nendobj\n"
            ."3 0 obj\n<</Type /Page /Parent 2 0 R /MediaBox [0 0 600 400]>>\nendobj\n";

        $templatePath = tempnam(sys_get_temp_dir(), 'certificate-test-');
        file_put_contents($templatePath, $pdf);

        try {
            $certificate = CertificatePdf::delegateCertificate($templatePath, 'Dr. Sample Name');
        } finally {
            unlink($templatePath);
        }

        $this->assertStringContainsString('/BaseFont/Helvetica-Bold', $certificate);
        $this->assertStringContainsString('0.941176 0.12549 0.12549 rg', $certificate);
        $this->assertStringContainsString('(Dr. Sample Name) Tj', $certificate);
    }

    public function test_finds_a_page_after_a_large_image_stream(): void
    {
        $pdf = "%PDF-1.3\n1 0 obj\n<</Length 1600000>>\nstream\n"
            .str_repeat('A', 1600000)
            ."\nendstream\nendobj\n2 0 obj\n<</Type /Page>>\nendobj\n";

        $objects = (new \ReflectionMethod(CertificatePdf::class, 'objects'))->invoke(null, $pdf);
        $pageId = (new \ReflectionMethod(CertificatePdf::class, 'firstPageObjectId'))->invoke(null, $objects);

        $this->assertSame(2, $pageId);
    }

    public function test_finds_a_page_stored_in_a_compressed_object_stream(): void
    {
        $page = '<</Type /Page /MediaBox [0 0 600 400]>>';
        $catalog = '<</Type /Catalog /Pages 2 0 R>>';
        $header = '7 0 8 '.strlen($page).' ';
        $contents = gzcompress($header.$page.$catalog);
        $pdf = "%PDF-1.5\n10 0 obj\n<</Type /ObjStm /N 2 /First ".strlen($header)
            ." /Filter /FlateDecode /Length ".strlen($contents).">>\nstream\n"
            .$contents."\nendstream\nendobj\n";

        $objects = (new \ReflectionMethod(CertificatePdf::class, 'objects'))->invoke(null, $pdf);
        $pageId = (new \ReflectionMethod(CertificatePdf::class, 'firstPageObjectId'))->invoke(null, $objects);

        $this->assertSame(7, $pageId);
        $this->assertSame($catalog, trim($objects[8]));
    }
}
