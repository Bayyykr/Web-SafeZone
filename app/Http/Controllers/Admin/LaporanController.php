<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompleteDaruratRequest;
use App\Http\Requests\Admin\InfografikFilterRequest;
use App\Http\Requests\Admin\StoreDaruratRequest;
use App\Models\Category;
use App\Models\EmergencyReport;
use App\Models\Laporan;
use App\Services\EmergencyReportService;
use App\Services\LaporanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    protected EmergencyReportService $emergencyService;
    protected LaporanService $laporanService;

    public function __construct(EmergencyReportService $emergencyService, LaporanService $laporanService)
    {
        $this->emergencyService = $emergencyService;
        $this->laporanService = $laporanService;
    }

    public function daruratStatus(): JsonResponse
    {
        return response()->json([
            "active" => EmergencyReport::where("status", "aktif")->count(),
        ]);
    }

    public function infografik(InfografikFilterRequest $request): View
    {
        $selectedMonth = $request->input("bulan", now()->format("Y-m"));
        
        $data = $this->laporanService->getInfografikData(
            $selectedMonth,
            $request->input("tanggal_mulai"),
            $request->input("tanggal_selesai")
        );

        return view("admin.laporan.infografik", [
            "selectedMonth" => $data["selectedMonth"],
            "startDate" => $data["startDate"],
            "endDate" => $data["endDate"],
            "chartData" => $data["chartData"],
        ]);
    }

    public function riwayat(Request $request): View
    {
        $items = Laporan::filterHistory($request->all())
            ->where("status", "selesai")
            ->latest()
            ->paginate(5)
            ->withQueryString();

        $emergencyArchives = EmergencyReport::query()
            ->with(["user", "nearestPolsek.lokasi"])
            ->whereIn("status", ["selesai", "arsip"])
            ->latest("waktu_selesai")
            ->limit(10)
            ->get();

        return view("admin.laporan.riwayat", [
            "items" => $items,
            "emergencyArchives" => $emergencyArchives,
            "categories" => Category::orderBy("jenis")->orderBy("nama_kategori")->get(),
            "summary" => $this->laporanService->getSummaryCards(),
        ]);
    }

    public function darurat(Request $request): View
    {
        $items = EmergencyReport::filterEmergency($request->all())
            ->whereIn("status", ["aktif", "dalam_penanganan"])
            ->latest("waktu_sos")
            ->paginate(5)
            ->withQueryString();

        return view("admin.laporan.darurat", [
            "items" => $items,
            "summary" => [
                "active" => EmergencyReport::where("status", "aktif")->count(),
                "handling" => EmergencyReport::where("status", "dalam_penanganan")->count(),
                "today" => EmergencyReport::whereDate("waktu_sos", today())->count(),
                "done" => EmergencyReport::whereIn("status", ["selesai", "arsip"])->count(),
            ],
        ]);
    }

    public function dispatchDarurat(EmergencyReport $emergencyReport): RedirectResponse
    {
        $this->emergencyService->dispatch($emergencyReport);

        return redirect()
            ->route("admin.laporan.darurat")
            ->with("success", "Personel berhasil dikirim ke titik darurat.");
    }

    public function completeDarurat(CompleteDaruratRequest $request, EmergencyReport $emergencyReport): RedirectResponse
    {
        $this->emergencyService->complete($emergencyReport, $request->validated());

        return redirect()
            ->route("admin.laporan.riwayat")
            ->with("success", "Laporan darurat selesai dan masuk ke riwayat laporan.");
    }

    public function storeDarurat(StoreDaruratRequest $request): RedirectResponse
    {
        $this->emergencyService->store($request->validated());

        return redirect()
            ->route("admin.laporan.darurat")
            ->with("success", "Simulasi SOS berhasil dibuat.");
    }
}
