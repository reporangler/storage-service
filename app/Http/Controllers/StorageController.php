<?php

namespace App\Http\Controllers;

use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class StorageController extends BaseController
{
    private $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    public function upload(Request $request, string $key)
    {
        $user = Auth::guard('token')->user();

        if (!$user || $user->is_public_user) {
            return new JsonResponse(['error' => 'unauthorized'], 401);
        }

        $content = $request->getContent();

        if (empty($content)) {
            return new JsonResponse(['error' => 'empty_body'], 400);
        }

        $this->storage->put($key, $content);

        return new JsonResponse([
            'ok' => true,
            'key' => $key,
            'size' => strlen($content),
        ], 201);
    }

    public function download(string $key)
    {
        $content = $this->storage->get($key);

        if ($content === null) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return new Response($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => strlen($content),
            'Content-Disposition' => 'attachment; filename="' . basename($key) . '"',
        ]);
    }

    public function exists(string $key)
    {
        return new JsonResponse([
            'exists' => $this->storage->exists($key),
            'key' => $key,
        ]);
    }

    public function delete(Request $request, string $key)
    {
        $user = Auth::guard('token')->user();

        if (!$user || $user->is_public_user) {
            return new JsonResponse(['error' => 'unauthorized'], 401);
        }

        $deleted = $this->storage->delete($key);

        return new JsonResponse([
            'deleted' => $deleted,
            'key' => $key,
        ]);
    }

    public function listObjects(Request $request)
    {
        $prefix = $request->query('prefix', '');
        $items = $this->storage->list($prefix);

        return new JsonResponse([
            'count' => count($items),
            'data' => $items,
        ]);
    }
}
