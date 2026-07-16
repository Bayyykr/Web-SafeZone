<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BeritaResource;
use App\Models\Berita;
use Illuminate\Http\JsonResponse;

class NewsController extends Controller
{
    public function index(): JsonResponse
    {
        $beritas = BeritaResource::collection(
            Berita::query()
                ->where("status", "published")
                ->latest("published_at")
                ->latest()
                ->get()
        );

        return response()->json($beritas);
    }

    public function show(Berita $berita): JsonResponse
    {
        abort_unless($berita->status === "published", 404);
        return response()->json(new BeritaResource($berita));
    }
}
