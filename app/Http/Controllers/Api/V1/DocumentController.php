<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{User, Document, DocumentCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends BaseController
{
    public function store(Request $request, User $user)
    {
        if (!$request->user()->canManageMemberDetails($user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'is_private' => 'boolean',
            'metadata' => 'nullable|array'
        ]);

        $category = DocumentCategory::findOrFail($validated['category_id']);
        
        if ($category->allowed_file_types) {
            $extension = $request->file('file')->extension();
            if (!in_array($extension, $category->allowed_file_types)) {
                return $this->errorResponse('Invalid file type', 422);
            }
        }

        $path = $request->file('file')->store('documents/' . $user->id, 'public');

        $document = $user->documents()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'is_private' => $validated['is_private'] ?? true,
            'metadata' => $validated['metadata'] ?? null
        ]);

        return $this->successResponse($document, 'Document uploaded successfully');
    }

    public function destroy(Request $request, Document $document)
    {
        if (!$request->user()->canManageMemberDetails($document->user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        Storage::delete($document->file_path);
        $document->delete();

        return $this->successResponse(null, 'Document deleted successfully');
    }

    public function download(Request $request, Document $document)
    {
        if ($document->is_private && !$request->user()->canViewMemberDetails($document->user)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return Storage::download($document->file_path, $document->title);
    }
} 