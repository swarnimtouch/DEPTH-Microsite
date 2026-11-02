<?php

namespace Tests\Feature;

use App\Mail\RegistrationConfirmation;
use App\Models\User;
use App\Support\RegistrationEmailArtwork;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationConfirmationMailTest extends TestCase
{
    public function test_mailer_uses_the_supplied_artwork_with_the_participant_name_and_program_link(): void
    {
        $doctor = new User([
            'name' => 'Dr. Asha Mehta',
            'email' => 'asha@example.test',
            'country' => 'India',
        ]);
        $doctor->setRelation('registeredModule', null);

        config(['mail.default' => 'array']);
        app('mail.manager')->forgetMailers();

        Mail::to($doctor->email)->send(new RegistrationConfirmation($doctor));

        $transport = app('mail.manager')->mailer()->getSymfonyTransport();
        $email = $transport->messages()->last()->getOriginalMessage();
        $html = $email->getHtmlBody();

        $this->assertStringContainsString('Dear Dr. Asha Mehta', $html);
        $this->assertStringNotContainsString('Dear Dr. Dr. Asha Mehta', $html);
        $this->assertSame(7, substr_count($html, 'src="cid:'));
        $this->assertCount(7, $email->getAttachments());
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'href="'.route('home', ['auth' => 'login']).'#register"'));
        foreach ($email->getAttachments() as $attachment) {
            $this->assertSame('inline', $attachment->getDisposition());
            $this->assertSame('image', $attachment->getMediaType());
            $this->assertSame('png', $attachment->getMediaSubtype());
        }
        $this->assertStringNotContainsString('background-image:', $html);
        $this->assertStringContainsString(
            'href="'.route('home', ['auth' => 'login']).'#register"',
            $html,
        );
    }

    public function test_mailer_artwork_has_the_expected_email_canvas_size(): void
    {
        $artworkPath = public_path('web/depth/assets/email');
        $expectedSlices = [
            'thank-you-header.png' => [700, 141],
            'thank-you-salutation.png' => [700, 39],
            'thank-you-content.png' => [700, 433],
            'thank-you-button-left.png' => [156, 42],
            'thank-you-button-right.png' => [156, 42],
            'thank-you-resources.png' => [700, 168],
            'thank-you-footer-bottom.png' => [700, 221],
        ];

        foreach ($expectedSlices as $file => [$width, $height]) {
            $dimensions = getimagesize($artworkPath.'/'.$file);
            $this->assertSame($width, $dimensions[0]);
            $this->assertSame($height, $dimensions[1]);
        }

        $personalizedSalutation = getimagesizefromstring(
            RegistrationEmailArtwork::salutation('Dr. Asha Mehta'),
        );
        $this->assertSame(700, $personalizedSalutation[0]);
        $this->assertSame(39, $personalizedSalutation[1]);
    }
}
