<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Throwable;

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

        $mime = strtolower((string)$file->getMimeType());
        if ($mime === '' || !in_array($mime, $this->allowedMimes, true)) {
            return ['name' => null, 'error' => 'Tipo MIME não permitido.'];
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $name = $this->buildUniqueFilename($destDir, $this->sanitizeFilename($file->getClientName()), $ext);

        try {
            $file->move($destDir, $name);
        } catch (Throwable $e) {
            log_message('warning', 'Falha no upload: {message}', ['message' => $e->getMessage()]);
            return ['name' => null, 'error' => 'Falha ao salvar o arquivo enviado.'];
        }

        return ['name' => $name, 'error' => null];
    }

    public function sanitizeDir(string $dir): string
    {
        return preg_replace('/[^0-9\\-]/', '', $dir);
    }

    private function sanitizeFilename(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/\\s+/', '_', $name);
        $name = preg_replace('/[^\\w\\-\\.]/', '', $name);
        if ($name === '' || $name === '.' || $name === '..') {
            return 'arquivo';
        }
        return $name;
    }

    private function buildUniqueFilename(string $destDir, string $name, string $fallbackExt): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        if ($base === '') {
            $base = 'arquivo';
        }

        $ext = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === '') {
            $ext = $fallbackExt;
        }

        $candidate = $base . '.' . $ext;
        if (!is_file($destDir . DIRECTORY_SEPARATOR . $candidate)) {
            return $candidate;
        }

        return $base . '_' . date('YmdHis') . '_' . substr(sha1(uniqid('', true)), 0, 6) . '.' . $ext;
    }
}
