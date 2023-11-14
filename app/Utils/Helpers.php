<?php

use Illuminate\Http\Request;

function storageUploadFile(string $prefix, string $name, Request $request): ?string
{
    if ($request->hasFile('image_uri')) {
        $path = str()->slug($name) . '.' . $request->image_uri->extension();
        \Storage::disk('bunnycdn')->putFileAs($prefix, $request->image_uri, $path);

        return $path;
    }

    return $request->image_uri;
}
