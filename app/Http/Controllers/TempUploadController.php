<?php

namespace App\Http\Controllers;

use App\Support\PublicFileUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TempUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'scope' => ['required', 'string', 'max:80'],
        ]);

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $scope = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $validated['scope']) ?: 'upload';
        $userId = (int) $request->user()->id;
        $directory = "tmp-uploads/{$userId}/{$scope}";

        $stored = PublicFileUpload::store($file, $directory, 255, 'tmp');

        return response()->json([
            'ok' => true,
            'temp' => [
                'name' => $stored['name'],
                'path' => $stored['path'],
                'size' => $stored['size'],
                'mime' => $stored['mime'],
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);

        $validated = $request->validate([
            'path' => ['required', 'string', 'max:255'],
        ]);

        $path = ltrim((string) $validated['path'], '/');
        $userId = (int) $request->user()->id;

        abort_unless(str_starts_with($path, "tmp-uploads/{$userId}/"), 403);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['ok' => true]);
    }
}

