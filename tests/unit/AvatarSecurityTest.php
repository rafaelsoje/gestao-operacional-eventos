<?php

use App\Services\AvatarService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

final class AvatarSecurityTest extends CIUnitTestCase
{
    public function testRejectsScriptDisguisedAsJpeg(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'avatar-test-');
        file_put_contents($path, '<?php echo "invalid";');
        try {
            $this->expectException(InvalidArgumentException::class);
            (new AvatarService())->save(new UploadedFile($path, 'avatar.jpg', 'image/jpeg', filesize($path)));
        } finally {
            unlink($path);
        }
    }

    public function testRejectsImageWithExecutableExtension(): void
    {
        $path = $this->image();
        try {
            $this->expectException(InvalidArgumentException::class);
            (new AvatarService())->save(new UploadedFile($path, 'avatar.php', 'image/png', filesize($path)));
        } finally {
            unlink($path);
        }
    }

    public function testReencodesValidPngOutsidePublicDirectory(): void
    {
        $path = $this->image();
        $output = null;
        try {
            $name = (new AvatarService())->save(new UploadedFile($path, 'avatar.png', 'image/png', filesize($path)));
            $output = WRITEPATH . 'uploads/avatars/' . $name;
            $this->assertStringEndsWith('.webp', $name);
            $this->assertFileExists($output);
            $this->assertSame('image/webp', getimagesize($output)['mime']);
        } finally {
            if (is_file($path)) unlink($path);
            if ($output !== null && is_file($output)) unlink($output);
        }
    }

    private function image(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'avatar-test-');
        $image = imagecreatetruecolor(12, 12);
        imagepng($image, $path);
        return $path;
    }
}
