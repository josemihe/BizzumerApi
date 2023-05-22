<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ImageManager;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    use ImageManager;

    public function index(Request $request): JsonResponse
    {
        $fields = [
            'group_id' => 'required|integer',
            'expense_id' => 'required|integer',
        ];
        $validatedData = $request->validate($fields);
        $image = Image::where('group_id', $validatedData['group_id'])
            ->where('expense_id', $validatedData['expense_id'])
            ->first();
        if ($image) {
            $imagePath = $image->path;
            $fileUrl = asset('storage/' . $imagePath);
            return response()->json([
                'message' => $fileUrl
            ], 200);
        } else {
            return response()->json([
                'message' => 'Image not found.'
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if ($file = $request->file('image')) {
            $path = $file->store('images' . '/' . $request->group_id, 'public');
            $relativePath = $path;
            $fileData = $this->uploads($file, $path);
            $user = $request->user;
            $image = Image::create([
                'name' => 'ticket_' . $request->group_id . '_' . $request->user_id . '_' . $request->expense_id,
                'type' => $fileData['fileType'],
                'path' => $relativePath,
                'size' => $fileData['fileSize'],
                'user_id' => $user->id,
                'group_id' => $request->group_id,
                'expense_id' => $request->expense_id
            ]);
            $image->save();
            return response()->json([
                'message' => 'Image successfully uploaded.'
            ], 201);
        }
        return response()->json([
            'message' => 'File upload failed.',
        ], 500);
    }
}
