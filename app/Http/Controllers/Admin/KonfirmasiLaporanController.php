<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateKonfirmasiLaporanRequest;
use App\Models\Laporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KonfirmasiLaporanController extends Controller
{
    public function index(Request $request): View
    {
        $items = Laporan::filterKonfirmasi($request->only(['search', 'status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Laporan::count(),
            'pending' => Laporan::where('status', 'pending')->count(),
            'dikonfirmasi' => Laporan::where('status', 'dikonfirmasi')->count(),
            'ditolak' => Laporan::where('status', 'ditolak')->count(),
            'selesai' => Laporan::where('status', 'selesai')->count(),
        ];

        return view("admin.layanan.konfirmasi.index", compact("items", "stats"));
    }

    public function update(UpdateKonfirmasiLaporanRequest $request, Laporan $laporan): RedirectResponse
    {
        $data = $request->validated();
        $oldStatus = $laporan->status;
        $newStatus = $data["status"];

        $laporan->update(["status" => $newStatus]);

        $laporan->konfirmasi()->updateOrCreate(
            ["laporan_id" => $laporan->id],
            [
                "petugas_id" => Auth::id(),
                "status" => $newStatus === "ditolak" ? "ditolak" : "valid",
                "catatan" => $data["catatan"] ?? null,
                "dikonfirmasi_pada" => now(),
            ],
        );

        if ($oldStatus !== $newStatus) {
            $user = $laporan->user;
            if ($user) {
                $statusText = match ($newStatus) {
                    'dikonfirmasi' => 'dikonfirmasi',
                    'ditolak' => 'ditolak',
                    'selesai' => 'dinyatakan selesai',
                    default => $newStatus
                };

                $title = "Status Laporan Diperbarui";
                $message = "Laporan Anda dengan judul \"" . $laporan->judul_laporan . "\" telah " . $statusText . " oleh petugas.";
                
                if ($newStatus === 'ditolak' && !empty($data['catatan'])) {
                    $message .= " Catatan: " . $data['catatan'];
                }

                \App\Models\Notification::create([
                    "user_id" => $user->id,
                    "title" => $title,
                    "message" => $message,
                    "type" => "report_status",
                ]);

                if ($user->fcm_token) {
                    try {
                        app(\App\Services\FirebaseService::class)->sendNotification(
                            $user->fcm_token,
                            $title,
                            $message,
                            [
                                "type" => "report_status",
                                "laporan_id" => (string)$laporan->id,
                                "status" => $newStatus
                            ]
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send FCM: " . $e->getMessage());
                    }
                }
            }
        }

        return redirect()
            ->route("admin.konfirmasi-laporan.index")
            ->with("success", "Status laporan berhasil diperbarui.");
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = "konfirmasi-laporan-" . now()->format("Ymd-His") . ".csv";

        $query = Laporan::filterKonfirmasi($request->only(['search', 'status']))->latest();

        return response()->streamDownload(
            function () use ($query) {
                $handle = fopen("php://output", "w");
                fputcsv($handle, [
                    "Pengirim",
                    "Email",
                    "No HP",
                    "Kategori",
                    "Kecamatan",
                    "Polsek",
                    "Judul",
                    "Status",
                    "Petugas",
                    "Tanggal",
                ]);

                $query->chunk(100, function ($laporans) use ($handle) {
                    foreach ($laporans as $laporan) {
                        fputcsv($handle, [
                            $laporan->user?->name,
                            $laporan->user?->email,
                            $laporan->user?->telepon,
                            $laporan->kategori?->nama_kategori,
                            $laporan->lokasi?->nama_lokasi,
                            $laporan->polsek?->nama,
                            $laporan->judul_laporan,
                            $laporan->status,
                            $laporan->konfirmasi?->petugas?->name,
                            Carbon::parse($laporan->created_at)->format("d/m/Y H:i"),
                        ]);
                    }
                });

                fclose($handle);
            },
            $fileName,
            ["Content-Type" => "text/csv"],
        );
    }
}
