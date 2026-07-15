<?php

namespace App\Http\Controllers;

use App\Models\ProviderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProviderDocumentController extends Controller
{
    public function show(Request $request, ProviderDocument $document)
    {
        $document->load('providerProfile');

        $this->authorize('view', $document);

        $disk = config('kyc.disk');
        abort_unless(Storage::disk($disk)->exists($document->path), 404);

        return Storage::disk($disk)->response($document->path, $document->original_name);
    }
}