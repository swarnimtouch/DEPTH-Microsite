<?php

namespace Tests\Unit;

use App\Models\Module;
use PHPUnit\Framework\TestCase;

class ModuleYoutubeEmbedUrlTest extends TestCase
{
    public function test_it_converts_a_youtube_live_url_to_an_embed_url(): void
    {
        $url = 'https://www.youtube.com/live/y7VNN_AA7NE?si=HjfWTlPD0gBxYWWi';

        $this->assertSame(
            'https://www.youtube.com/embed/y7VNN_AA7NE',
            Module::youtubeEmbedUrl($url),
        );
    }
}
