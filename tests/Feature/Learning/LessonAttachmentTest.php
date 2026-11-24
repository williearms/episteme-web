<?php

namespace Tests\Feature\Learning;

use App\Support\LessonAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * These use real files on disk, not UploadedFile::fake(): the fake reports its type from
 * the *filename*, which would make every "renamed script" case pass for the wrong reason.
 * A real UploadedFile sniffs the actual bytes, which is what happens in production.
 */
class LessonAttachmentTest extends TestCase
{
    /** @var array<int, string> */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    private function upload(string $clientName, string $bytes): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'att');
        file_put_contents($path, $bytes);
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, $clientName, null, null, true);
    }

    private function image(string $format): string
    {
        $canvas = imagecreatetruecolor(8, 8);
        ob_start();
        match ($format) {
            'png' => imagepng($canvas),
            'jpeg' => imagejpeg($canvas),
            'gif' => imagegif($canvas),
            'webp' => imagewebp($canvas),
        };

        return ob_get_clean();
    }

    /** @return array<int, string> */
    private function stored(): array
    {
        return Storage::disk('public')->allFiles();
    }

    public function test_each_allowed_image_type_is_stored_with_a_generated_name_and_matching_extension(): void
    {
        foreach (['png' => 'png', 'jpeg' => 'jpg', 'gif' => 'gif', 'webp' => 'webp'] as $format => $extension) {
            $path = LessonAttachment::store($this->upload("Foto Saya.{$extension}", $this->image($format)));

            $this->assertStringStartsWith('lesson-attachments/', $path);
            $this->assertStringEndsWith(".{$extension}", $path);
            $this->assertStringNotContainsString('Foto', $path); // never the client's filename
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_the_extension_comes_from_the_real_content_not_the_filename(): void
    {
        // A genuine JPEG the client calls ".png" is stored as ".jpg".
        $path = LessonAttachment::store($this->upload('misnamed.png', $this->image('jpeg')));

        $this->assertStringEndsWith('.jpg', $path);
    }

    public function test_a_php_script_renamed_to_png_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        try {
            LessonAttachment::store($this->upload('innocent.png', '<?php system($_GET["c"]); ?>'));
        } finally {
            $this->assertSame([], $this->stored());
        }
    }

    public function test_html_and_svg_are_rejected_even_with_an_image_name(): void
    {
        $payloads = [
            'page.png' => '<html><body><script>alert(1)</script></body></html>',
            'icon.png' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
            'icon.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
            'shell.php' => '<?php echo 1;',
            'notes.txt' => 'just text',
        ];

        foreach ($payloads as $name => $bytes) {
            try {
                LessonAttachment::store($this->upload($name, $bytes));
                $this->fail("Expected {$name} to be rejected.");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame([], $this->stored());
    }

    public function test_an_image_with_a_script_appended_is_still_only_stored_as_an_image_file(): void
    {
        // A valid PNG header followed by PHP: it sniffs as PNG, so it's accepted — but it is
        // stored under a generated .png name, which the web server never executes as PHP.
        $path = LessonAttachment::store($this->upload('polyglot.php', $this->image('png').'<?php echo 1; ?>'));

        $this->assertStringEndsWith('.png', $path);
        $this->assertStringNotContainsString('php', $path);
    }

    public function test_oversized_images_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $big = $this->image('png').str_repeat('0', (LessonAttachment::MAX_KILOBYTES + 1) * 1024);

        try {
            LessonAttachment::store($this->upload('huge.png', $big));
        } finally {
            $this->assertSame([], $this->stored());
        }
    }
}
