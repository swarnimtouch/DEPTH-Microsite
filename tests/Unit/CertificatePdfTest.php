<?php

namespace Tests\Unit;

use App\Support\CertificatePdf;
use PHPUnit\Framework\TestCase;

class CertificatePdfTest extends TestCase
{
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
