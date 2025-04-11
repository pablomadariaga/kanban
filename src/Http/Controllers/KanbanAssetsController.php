<?php

namespace TallStackUi\Foundation\Http\Controllers;

use Exception;
use Livewire\Drawer\Utils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class KanbanAssetsController
{
    /**
     * The path to the dist directory.
     */
    protected const DIST_PATH = __DIR__ . '/../../../dist';

    /** @throws Exception */
    public function script(?string $file = null): Response|BinaryFileResponse
    {
        $file = $this->fallback($file);

        return Utils::pretendResponseIsFile(self::DIST_PATH . '/' . $file, 'text/javascript');
    }

    /**
     * Apply assets fallback feature.
     *
     * @throws Exception
     */
    private function fallback(string $file): string
    {
        return $file;
    }
}
