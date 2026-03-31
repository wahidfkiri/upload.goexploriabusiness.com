<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CDNController extends Controller
{
    // Upload de fichier
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'path' => 'nullable|string',
            'visibility' => 'nullable|in:public,private'
        ]);

        $file = $request->file('file');
        $path = $request->input('path', '');
        $visibility = $request->input('visibility', 'public');
        
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $fullPath = trim($path . '/' . $filename, '/');
        
        $stored = Storage::disk('cdn')->put($fullPath, file_get_contents($file), $visibility);
        
        if ($stored) {
            return response()->json([
                'success' => true,
                'path' => $fullPath,
                'url' => Storage::disk('cdn')->url($fullPath),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'filename' => $filename
            ], 201);
        }
        
        return response()->json(['error' => 'Upload failed'], 500);
    }
    
    // Upload multiple
    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:102400',
            'path' => 'nullable|string'
        ]);
        
        $uploaded = [];
        $errors = [];
        
        foreach ($request->file('files') as $file) {
            try {
                $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $path = $request->input('path', '');
                $fullPath = trim($path . '/' . $filename, '/');
                
                Storage::disk('cdn')->put($fullPath, file_get_contents($file));
                
                $uploaded[] = [
                    'path' => $fullPath,
                    'url' => Storage::disk('cdn')->url($fullPath),
                    'original_name' => $file->getClientOriginalName()
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => count($uploaded),
            'failed' => count($errors),
            'uploaded' => $uploaded,
            'errors' => $errors
        ]);
    }
    
    // Récupérer un fichier
    public function getFile($path)
    {
        if (!Storage::disk('cdn')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $file = Storage::disk('cdn')->get($path);
        $mime = Storage::disk('cdn')->mimeType($path);
        
        return response($file)->header('Content-Type', $mime);
    }
    
    // Supprimer un fichier
    public function deleteFile(Request $request, $path)
    {
        $path = urldecode($path);
        
        if (!Storage::disk('cdn')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        Storage::disk('cdn')->delete($path);
        
        return response()->json(['success' => true, 'message' => 'File deleted']);
    }
    
    // Lister les fichiers
    public function listFiles(Request $request)
    {
        $directory = $request->input('directory', '');
        $files = Storage::disk('cdn')->files($directory);
        
        $fileDetails = array_map(function($file) {
            return [
                'path' => $file,
                'url' => Storage::disk('cdn')->url($file),
                'size' => Storage::disk('cdn')->size($file),
                'last_modified' => Storage::disk('cdn')->lastModified($file),
                'mime_type' => Storage::disk('cdn')->mimeType($file)
            ];
        }, $files);
        
        return response()->json($fileDetails);
    }
    
    // Générer URL temporaire
    public function temporaryUrl(Request $request, $path)
    {
        $expiration = $request->input('expiration', now()->addMinutes(30));
        
        if (!Storage::disk('cdn')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $url = Storage::disk('cdn')->temporaryUrl($path, $expiration);
        
        return response()->json(['url' => $url, 'expires_at' => $expiration]);
    }
}