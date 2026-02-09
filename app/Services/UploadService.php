<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

class UploadService
{
    private array $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];
    private array $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/png',
        'image/jpeg',
    ];
    private int $maxBytes = 10485760; // 10MB

    public function handle(?UploadedFile $file, string $destDir): array
    {
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return ['name' => null, 'error' => null];
        }

        $size = $file->getSize();
        if ($size > $this->maxBytes) {
            return ['name' => null, 'error' => 'Arquivo muito grande (máx 10MB).'];
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $this->allowedExt, true)) {
            return ['name' => null, 'error' => 'Formato de arquivo não permitido.'];
        }

        $mime = $file->getClientMimeType();
        if ($mime && !in_array($mime, $this->allowedMimes, true)) {
            return ['name' => null, 'error' => 'Tipo MIME não permitido.'];
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $name = $this->sanitizeFilename($file->getClientName());
        $file->move($destDir, $name);

        return ['name' => $name, 'error' => null];
    }

    public function sanitizeDir(string $dir): string
    {
        return preg_replace('/[^0-9\\-]/', '', $dir);
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/\\s+/', '_', $name);
        $name = preg_replace('/[^\\w\\-\\.]/', '', $name);
        return $name;
    }
}
