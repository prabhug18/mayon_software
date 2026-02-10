<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\APIResponse;

class MediaController extends Controller
{
    use APIResponse;

    // Upload a file and attach to user
    public function upload(Request $request, User $user)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);
        $media = $user->addMedia($request->file('file'))->toMediaCollection('files');
        return $this->success(['media_id' => $media->id, 'url' => $media->getUrl()], 'File uploaded successfully');
    }

    // List user's media files
    public function list(User $user)
    {
        $media = $user->getMedia('files')->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'url' => $item->getUrl(),
            ];
        });
        return $this->success($media);
    }

    // Delete a media file
    public function delete(User $user, $mediaId)
    {
        $media = $user->media()->findOrFail($mediaId);
        $media->delete();
        return $this->success([], 'File deleted successfully');
    }
}
