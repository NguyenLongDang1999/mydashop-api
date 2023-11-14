<?php

use Illuminate\Http\Request;

function storageUploadFile(string $prefix, string $slug, Request $request): ?string
{
    if ($request->hasFile('image_uri')) {
        $path = $slug . '.' . $request->image_uri->extension();
        \Storage::disk('bunnycdn')->putFileAs($prefix, $request->image_uri, $path);

        return $path;
    }

    return $request->image_uri;
}
