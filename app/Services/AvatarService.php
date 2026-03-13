<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;
use Throwable;

class AvatarService
{
    private string $uploadDir;
    private string $defaultAvatarPath;

    public function __construct()
    {
        $this->uploadDir = WRITEPATH . 'uploads/avatars';
        $this->defaultAvatarPath = FCPATH . 'media/avatar.svg';
    }

    public function save(UploadedFile $file): string
    {
        // Garante diretório local antes de qualquer manipulação de imagem.
        $this->ensureUploadDir();

        $baseName = sha1(uniqid('', true));
        $webpName = $baseName . '.webp';
        $webpPath = $this->uploadDir . DIRECTORY_SEPARATOR . $webpName;

        if (function_exists('imagewebp')) {
            try {
                // Pipeline padrão: converte para WEBP e normaliza dimensões de avatar.
                Services::image('gd')
                    ->withFile($file)
                    ->convert(IMAGETYPE_WEBP)
                    ->resize(200, 237, true, 'auto')
                    ->save($webpPath);

                return $webpName;
            } catch (Throwable $e) {
                log_message('warning', 'Falha ao gerar avatar WEBP, usando fallback: {message}', ['message' => $e->getMessage()]);
            }
        }

        $ext = strtolower((string)$file->getClientExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            $ext = 'jpg';
        }

        $fallbackName = $baseName . '.' . $ext;
        // Fallback para upload original quando não houver suporte/erro no GD->WEBP.
        $file->move($this->uploadDir, $fallbackName);

        return $fallbackName;
    }

    public function resolvePath(string $avatar): string
    {
        // Resolve avatar customizado com fallback para mídia padrão do sistema.
        $safe = basename($avatar);
        if ($safe === '' || $safe !== $avatar || $safe === 'avatar.svg') {
            return $this->defaultAvatarPath;
        }

        $path = $this->uploadDir . DIRECTORY_SEPARATOR . $safe;
        if (is_file($path)) {
            return $path;
        }

        $legacy = FCPATH . 'media/' . $safe;
        if (is_file($legacy)) {
            return $legacy;
        }

        return $this->defaultAvatarPath;
    }

    private function ensureUploadDir(): void
    {
        // Cria pasta de uploads sob demanda para manter deploy idempotente.
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
}
