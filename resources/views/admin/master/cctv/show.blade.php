<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/cctv.css'])
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            .begal-hud {
                background: linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, transparent 40%, rgba(15, 23, 42, 0.7) 100%);
            }
            .radar-pulse {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
                animation: pulse-ring 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
            }
            .radar-pulse-green {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
                animation: pulse-ring-green 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
            }
            @keyframes pulse-ring {
                0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                70% { box-shadow: 0 0 0 14px rgba(239, 68, 68, 0); }
                100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }
            @keyframes pulse-ring-green {
                0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
                70% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
                100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
            }
            .gauge-bar-fill {
                transition: width 0.35s ease, background-color 0.35s ease;
            }
            @keyframes spin-fast {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spinning {
                animation: spin-fast 0.6s linear infinite;
            }
        </style>
    @endpush

    <x-slot name="header">
        Live Monitoring BegalVision: {{ $item->nama }}
    </x-slot>

    @php
        $isMjpeg = $item->is_mjpeg;
        $isYoutube = $item->is_youtube;
        $lat = $item->lokasi?->latitude ?? -8.133182;
        $lng = $item->lokasi?->longitude ?? 113.224522;
        $baseStreamUrl = $item->ai_stream_url;
        $rawStreamUrl = $item->raw_stream_url;
    @endphp

    <div class="user-page space-y-6">
        <!-- Top Command Bar -->
        <div class="user-hero flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="user-hero-left">
                <!-- Sized icon container to prevent any giant SVG expansion -->
                <div class="user-hero-icon shrink-0 rounded-xl flex items-center justify-center bg-indigo-600 text-white" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;">
                    <svg class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="user-hero-title text-xl font-bold">{{ $item->nama }}</h1>
                        <span id="badge-online" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isAiOnline ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            <span id="badge-dot" class="w-2 h-2 rounded-full {{ $isAiOnline ? 'bg-emerald-500 radar-pulse-green' : 'bg-amber-500' }}"></span>
                            <span id="status-text">{{ $isAiOnline ? 'AI STREAM ONLINE' : 'STREAM STANDBY' }}</span>
                        </span>
                        @if ($item->is_begalvision)
                            <span class="bg-indigo-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded tracking-wider uppercase">
                                BegalVision Edge AI
                            </span>
                        @endif
                    </div>
                    <div class="user-hero-subtitle text-xs text-gray-500 mt-1">
                        Wilayah: <strong>{{ $item->lokasi?->nama_lokasi ?? 'Lumajang' }}</strong> |
                        Koordinat: <span class="font-mono text-xs">{{ $lat }}, {{ $lng }}</span> |
                        Flask Server: <span class="font-mono text-xs text-blue-600 font-semibold" id="flask-host-display">{{ $flaskBase }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    id="btn-take-snapshot"
                    class="btn-secondary flex items-center gap-1.5 text-xs font-bold px-3 py-2 cursor-pointer"
                    title="Ambil foto tangkapan layar saat ini">
                    <svg class="w-4 h-4 text-gray-600 shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Snapshot Bukti</span>
                </button>

                <button
                    type="button"
                    id="btn-toggle-fullscreen"
                    class="btn-secondary flex items-center gap-1.5 text-xs font-bold px-3 py-2 cursor-pointer"
                    title="Tampilan layar penuh">
                    <svg class="w-4 h-4 text-gray-600 shrink-0" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <span>Layar Penuh</span>
                </button>

                <a class="btn-secondary flex items-center gap-1.5 text-xs font-bold px-3 py-2" href="{{ route('admin.cctvs.index') }}">
                    <span>← Kembali</span>
                </a>
            </div>
        </div>

        <!-- Main Video Stream and Telemetry Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left / Center: Video Stream Player (8 Cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div id="video-stream-container" class="relative rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-2xl aspect-video flex items-center justify-center">
                    @if ($isYoutube && $item->embed_url)
                        <iframe
                            id="youtube-player"
                            class="w-full h-full object-cover"
                            src="{{ $item->embed_url }}"
                            title="Live Streaming {{ $item->nama }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    @else
                        <!-- Continuous MJPEG Video Feed Stream -->
                        <img
                            id="live-mjpeg-stream"
                            src="{{ $baseStreamUrl }}?model={{ $activeModel }}&conf=0.25&quality=80&fps=30"
                            alt="Live Stream BegalVision"
                            class="w-full h-full object-contain"
                            crossorigin="anonymous"
                        />

                        <!-- Fallback / Disconnected Overlay -->
                        <div id="stream-offline-overlay" class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6 text-white" style="display: none;">
                            <div class="w-16 h-16 rounded-2xl bg-red-500/20 text-red-400 flex items-center justify-center mb-4 radar-pulse">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-1">Stream Kamera AI Sedang Menghubungkan</h3>
                            <p class="text-xs text-slate-400 max-w-md mb-4">
                                Pastikan server Flask AI aktif pada <code class="text-blue-400 bg-slate-900 px-1.5 py-0.5 rounded">{{ $flaskBase }}</code> dan Webcam USB terpasang.
                            </p>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    id="btn-reconnect-stream"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Coba Sambungkan Ulang</span>
                                </button>
                                <button
                                    type="button"
                                    id="btn-switch-to-raw"
                                    class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-medium transition cursor-pointer">
                                    Coba Raw Stream
                                </button>
                            </div>
                        </div>

                        <!-- Minimal BegalVision HUD (Non-intrusive) -->
                        <div class="begal-hud absolute inset-0 pointer-events-none p-3 flex flex-col justify-between">
                            <!-- HUD Top Right: Minimal Badge -->
                            <div class="flex items-center justify-end">
                                <div class="flex items-center gap-2 bg-slate-950/75 backdrop-blur-md px-2.5 py-1 rounded-lg border border-slate-800 text-[11px] font-mono text-emerald-400 shadow-lg">
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                                    <span class="font-bold text-white">REC</span>
                                    <span class="text-slate-500">|</span>
                                    <span id="hud-clock" class="text-slate-200">{{ now()->format('H:i:s') }}</span>
                                    <span class="text-slate-500">|</span>
                                    <span id="hud-mode-pill" class="text-indigo-400 font-bold">AI BEGALVISION</span>
                                </div>
                            </div>

                            <!-- HUD Center Watermark / Threat Alert Box -->
                            <div id="hud-threat-alert" class="self-center hidden bg-red-600/95 text-white px-6 py-2 rounded-xl text-center shadow-2xl radar-pulse border border-red-400 pointer-events-auto">
                                <div class="text-[10px] font-black tracking-widest uppercase">⚠️ PERINGATAN ANCAMAN AI</div>
                                <div id="hud-threat-text" class="text-sm font-black">TERDETEKSI POTENSI BEGAL</div>
                            </div>

                            <!-- HUD Bottom Row -->
                            <div class="flex items-end justify-between text-[11px] font-mono text-slate-300">
                                <div class="bg-slate-950/75 backdrop-blur-md px-2.5 py-1 rounded border border-slate-800 text-[10px]">
                                    <span class="text-slate-400">CAM:</span> USB #<span id="hud-source">0</span>
                                    <span class="text-slate-400 ml-2">LOC:</span> {{ $item->lokasi?->nama_lokasi ?? 'Lumajang' }}
                                </div>
                                <div class="bg-slate-950/75 backdrop-blur-md px-2.5 py-1 rounded border border-slate-800 text-[10px] text-right">
                                    <span class="text-slate-400">ENGINE:</span> <span id="hud-model-name" class="text-yellow-400 font-bold">{{ $activeModel }}</span>
                                    <span class="text-slate-500 mx-1">|</span>
                                    <span class="text-slate-400">FPS:</span> <span id="hud-fps" class="text-emerald-400 font-bold">30.0</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Live Stream Controls Toolbar -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <!-- Mode Switcher -->
                        <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-lg">
                            <button
                                type="button"
                                id="mode-ai-btn"
                                class="px-3 py-1.5 rounded-md text-xs font-bold transition bg-indigo-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>AI BegalVision</span>
                            </button>
                            <button
                                type="button"
                                id="mode-raw-btn"
                                class="px-3 py-1.5 rounded-md text-xs font-medium text-gray-700 hover:text-black transition flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Webcam Murni (Raw)</span>
                            </button>
                        </div>

                        <!-- Camera Source Selector with Dynamic Feedback -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-700 whitespace-nowrap">Source Kamera:</label>
                            <div class="relative flex items-center">
                                <select id="camera-source-select" class="form-select text-xs py-1.5 px-3 pr-8 rounded-lg border-gray-300 font-medium">
                                    <option value="0">Kamera 0 (Default USB)</option>
                                    <option value="1">Kamera 1 (Webcam USB)</option>
                                    <option value="2">Kamera 2 (Eksternal)</option>
                                    <option value="3">Kamera 3</option>
                                </select>
                                <span id="camera-switching-spinner" class="hidden absolute right-2 text-indigo-600 animate-spin text-xs">⟳</span>
                            </div>
                        </div>

                        <!-- Reload Button -->
                        <button
                            type="button"
                            id="btn-refresh-stream"
                            class="text-xs text-gray-700 hover:text-blue-600 font-bold flex items-center gap-1.5 cursor-pointer px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 shadow-sm transition">
                            <svg id="btn-refresh-icon" class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reload Frame</span>
                        </button>
                    </div>

                    <!-- AI Model & Inference Parameters -->
                    <div class="pt-3 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-gray-700">Model ONNX Aktif:</label>
                                <span id="model-switching-spinner" class="hidden text-indigo-600 text-[10px] animate-pulse">Menyiapkan...</span>
                            </div>
                            <select id="model-select" class="form-select text-xs py-1.5 px-3 w-full rounded-lg border-gray-300 font-medium">
                                @foreach ($models as $mod)
                                    <option value="{{ $mod['name'] }}" @selected($mod['name'] === $activeModel)>
                                        {{ $mod['display_name'] ?? $mod['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">Mengganti engine AI ONNX langsung pada backend Flask.</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <label class="font-bold text-gray-700">Confidence Threshold:</label>
                                <span id="conf-val-label" class="font-mono font-bold text-blue-600">25%</span>
                            </div>
                            <input
                                id="conf-slider"
                                type="range"
                                min="0.10"
                                max="0.95"
                                step="0.05"
                                value="0.25"
                                class="w-full accent-blue-600 cursor-pointer"
                            />
                            <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                                <span>Sensitif (10%)</span>
                                <span>Ketat (95%)</span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <label class="font-bold text-gray-700">Batasan FPS Streaming:</label>
                                <span id="fps-val-label" class="font-mono font-bold text-emerald-600">30 FPS</span>
                            </div>
                            <input
                                id="fps-slider"
                                type="range"
                                min="10"
                                max="60"
                                step="5"
                                value="30"
                                class="w-full accent-emerald-600 cursor-pointer"
                            />
                            <div class="flex justify-between text-[10px] text-gray-400 mt-0.5">
                                <span>Hemat (10 FPS)</span>
                                <span>Fluid (60 FPS)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Threat Detection & Event Log Feed -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                            <h3 class="text-sm font-bold text-gray-900">Log Aktivitas & Deteksi Ancaman BegalVision</h3>
                        </div>
                        <span class="text-xs text-gray-400">Sinkron Real-time</span>
                    </div>

                    <div id="event-log-container" class="space-y-2 max-h-52 overflow-y-auto pr-1 text-xs">
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex items-start justify-between">
                            <div class="flex items-start gap-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">INFO</span>
                                <div>
                                    <p class="font-semibold text-gray-800">Sistem Monitoring Terhubung</p>
                                    <p class="text-gray-500 text-[11px]">Kamera {{ $item->nama }} aktif di stasiun pantau SafeZone Lumajang.</p>
                                </div>
                            </div>
                            <span class="text-gray-400 text-[10px] font-mono">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Mendeteksi ancaman mencurigakan?</span>
                        <a
                            href="{{ route('admin.laporan.darurat') }}"
                            class="btn-primary text-xs font-bold py-1.5 px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Buka Tanggap Darurat Polsek</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: ESP-32 Telemetry & Sensor Panels (4 Cols) -->
            <div class="lg:col-span-4 space-y-4">
                <!-- 1. MULTI-MODAL FUZZY THREAT GAUGE CARD -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold text-xs">
                                AI+
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Skor Ancaman Fuzzy (Multi-Modal)</h3>
                                <p class="text-[10px] text-gray-500">YOLO AI + INMP441 + BH1750 + PIR</p>
                            </div>
                        </div>
                        <span id="rtdb-status-pill" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            RTDB LIVE
                        </span>
                    </div>

                    <!-- Threat Score Big Display & Progressive Meter -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 text-center">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                            Tingkat Ancaman Begal Terhitung
                        </div>
                        <div class="flex items-baseline justify-center gap-1">
                            <span id="fuzzy-score-number" class="text-4xl font-extrabold font-mono text-emerald-600">
                                {{ $fuzzyResult['score'] ?? 10 }}%
                            </span>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-2">
                            <span id="fuzzy-status-pill" class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                🟢 {{ $fuzzyResult['category'] ?? 'AMAN' }}
                            </span>
                            <span id="fuzzy-auto-alarm-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">
                                Auto-Trigger (≥70%)
                            </span>
                        </div>

                        <!-- 3-Zone Progressive Meter Bar -->
                        <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden mt-3 relative">
                            <div id="fuzzy-meter-bar" class="h-full rounded-full transition-all duration-500 bg-emerald-500" style="width: {{ $fuzzyResult['score'] ?? 10 }}%;"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-gray-500 font-mono mt-1 px-0.5">
                            <span>0% Aman</span>
                            <span class="text-amber-600 font-bold">36% Waspada</span>
                            <span class="text-red-600 font-extrabold">🚨 70% ALARM AKTIF</span>
                            <span>100%</span>
                        </div>
                    </div>

                    <!-- Active Fuzzy Rule Box (from full_fuzzy_rules_begalvision.md) -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5 text-left">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-gray-700">Aturan Logika Fuzzy Aktif:</span>
                            <span id="fuzzy-rule-code" class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-slate-200 text-slate-800">
                                {{ $fuzzyResult['dominant_rule']['code'] ?? 'R36' }}
                            </span>
                        </div>
                        <p id="fuzzy-rule-scenario" class="text-xs text-gray-700 font-medium leading-relaxed">
                            {{ $fuzzyResult['dominant_rule']['scenario'] ?? 'Idle Kondisi Ideal: Tidak ada pergerakan, tidak ada suara, visual bersih.' }}
                        </p>
                    </div>

                    <!-- Live Multi-Modal Sensor Fusion Status -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60">
                            <div class="text-[10px] text-gray-500">Visual AI (YOLO)</div>
                            <div id="fused-visual-text" class="font-bold text-gray-800 truncate">
                                @if($initVisualLabel === 'begal_bersenjata')
                                    <span class="text-red-600 animate-pulse">🚨 Bahaya (Begal + Senjata {{ round(($initVisualConf ?? 0.8) * 100) }}%)</span>
                                @elseif($initVisualLabel === 'begal')
                                    <span class="text-amber-600 font-bold">⚠️ Waspada (Begal Tanpa Sajam {{ round(($initVisualConf ?? 0.8) * 100) }}%)</span>
                                @elseif($initVisualLabel === 'senjata')
                                    <span class="text-amber-600 font-bold">⚠️ Waspada (Sajam Saja {{ round(($initVisualConf ?? 0.8) * 100) }}%)</span>
                                @else
                                    <span class="text-emerald-600 font-bold">Aman / Standby</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60">
                            <div class="text-[10px] text-gray-500">Suara (INMP441)</div>
                            <div id="fused-sound-text" class="font-bold text-gray-800 truncate">{{ $telemetry['noise_db'] }} dB ({{ $telemetry['noise_status'] }})</div>
                        </div>
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60">
                            <div class="text-[10px] text-gray-500">Cahaya (BH1750)</div>
                            <div id="fused-lux-text" class="font-bold text-gray-800 truncate">{{ $telemetry['lux'] }} Lux ({{ $telemetry['lux_status'] }})</div>
                        </div>
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/60">
                            <div class="text-[10px] text-gray-500">Gerakan (PIR)</div>
                            <div id="fused-pir-text" class="font-bold text-gray-800 truncate">{{ $telemetry['pir_motion'] ? 'Gerak' : 'Diam' }}</div>
                        </div>
                    </div>

                    <!-- Interactive Scenario Test Toolbar (Untuk Menguji Perubahan Bar Fuzzy) -->
                    <div class="pt-2.5 border-t border-gray-100 space-y-2">
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-bold text-gray-700 uppercase tracking-wider">🧪 Uji Coba Cepat Respon Bar:</span>
                            <span id="test-mode-status" class="font-mono text-emerald-600 font-bold">● Mode Live AI</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button
                                type="button"
                                id="btn-test-begal"
                                class="px-2 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-[10px] font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-sm"
                                title="Uji kondisi Begal Senyap Malam (R05 - Skor 85%)">
                                🚨 Begal (85%)
                            </button>
                            <button
                                type="button"
                                id="btn-test-scream"
                                class="px-2 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-sm"
                                title="Uji kondisi Suara Jeritan Malam (R25 - Skor 60% Waspada, No Buzzer)">
                                📢 Jeritan (60%)
                            </button>
                            <button
                                type="button"
                                id="btn-test-extreme"
                                class="px-2 py-1.5 rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-[10px] font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-sm"
                                title="Uji kondisi Serangan Begal Ekstrim (R01 - Skor 100%)">
                                ⚡ Ekstrim (100%)
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="text-gray-400">Klik tombol di atas untuk simulasi instan</span>
                            <button
                                type="button"
                                id="btn-test-reset"
                                class="text-indigo-600 hover:text-indigo-800 font-bold underline cursor-pointer">
                                🔄 Reset Live
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Telemetry Card: ESP-32 Sensor Grid -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                IoT
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">Telemetri Sensor ESP-32</h3>
                                <p class="text-[10px] text-gray-500">Koneksi MQTT ke Firebase RTDB</p>
                            </div>
                        </div>
                        <span id="telemetry-status-pill" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            TERHUBUNG
                        </span>
                    </div>

                    <!-- Sensor 1: PIR Motion Sensor -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div id="pir-icon-wrapper" class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-gray-800">PIR Gerakan Fisik</div>
                                <div id="pir-status-text" class="text-xs font-bold text-emerald-600">Aman (Tidak Ada Gerakan)</div>
                            </div>
                        </div>
                        <span id="pir-badge" class="px-2 py-1 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-700">
                            STANDBY
                        </span>
                    </div>

                    <!-- Sensor 2: Microphone INMP441 (Audio Noise dB) -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                <span class="text-xs font-bold text-gray-800">Mikrofon INMP441 (Suara)</span>
                            </div>
                            <span id="noise-db-text" class="text-xs font-extrabold font-mono text-indigo-700">42 dB</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                            <div id="noise-meter-bar" class="gauge-bar-fill bg-indigo-500 h-full rounded-full" style="width: 35%;"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500">
                            <span>Status: <strong id="noise-status-text" class="text-gray-700">Normal</strong></span>
                            <span class="text-red-500 font-bold">Ambang Teriakan: 75 dB</span>
                        </div>
                    </div>

                    <!-- Sensor 3: BH1750 Ambient Light (Lux Meter) -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="text-xs font-bold text-gray-800">Intensitas Cahaya (BH1750)</span>
                            </div>
                            <span id="lux-text" class="text-xs font-extrabold font-mono text-amber-700">24 Lux</span>
                        </div>
                        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                            <div id="lux-meter-bar" class="gauge-bar-fill bg-amber-400 h-full rounded-full" style="width: 25%;"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500">
                            <span>Kondisi: <strong id="lux-status-text" class="text-gray-700">Redup (Malam Hari)</strong></span>
                            <span>Maks: 500 Lux</span>
                        </div>
                    </div>

                    <!-- Actuator Remote Controls (ESP-32 Buzzer & Strobe LED) -->
                    <div class="pt-2 border-t border-gray-100 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-bold text-gray-800 uppercase tracking-wider text-[10px]">
                                Kendali Aktuator Lapangan (ESP-32)
                            </div>
                            <label class="flex items-center gap-1.5 cursor-pointer text-[10px] text-gray-600 font-bold">
                                <input type="checkbox" id="chk-auto-alarm" class="rounded text-red-600 focus:ring-red-500 cursor-pointer" checked />
                                <span>Auto-Trigger</span>
                            </label>
                        </div>

                        <!-- Buzzer Siren Button -->
                        <div class="flex items-center justify-between p-3 rounded-xl border border-red-200 bg-red-50/50">
                            <div>
                                <div class="text-xs font-bold text-red-900">Sirine Alarm Lapangan</div>
                                <div class="text-[10px] text-red-700">Bunyikan buzzer darurat di lokasi kamera</div>
                            </div>
                            <button
                                type="button"
                                id="btn-toggle-buzzer"
                                data-active="0"
                                class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap">
                                🔔 Picu Alarm
                            </button>
                        </div>

                        <!-- LED Floodlight Button -->
                        <div class="flex items-center justify-between p-3 rounded-xl border border-yellow-200 bg-yellow-50/50">
                            <div>
                                <div class="text-xs font-bold text-yellow-900">Lampu Sorot LED</div>
                                <div class="text-[10px] text-yellow-700">Nyalakan penerangan titik kamera</div>
                            </div>
                            <button
                                type="button"
                                id="btn-toggle-led"
                                data-active="0"
                                class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap">
                                💡 Lampu ON
                            </button>
                        </div>
                    </div>
                </div>

                <!-- GPS Location & Proximity Matching Widget (Leaflet.js) -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="text-xs font-bold text-gray-800">Posisi GPS & Pencocokan CCTV</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                id="btn-sync-gps"
                                class="text-[11px] text-indigo-600 font-bold hover:underline cursor-pointer"
                                title="Samakan koordinat lokasi CCTV persis dengan posisi GPS alat IoT terkini agar jarak menjadi 0 meter">
                                🎯 Samakan Koordinat
                            </button>
                            <button
                                type="button"
                                id="btn-recenter-map"
                                class="text-[11px] text-blue-600 font-bold hover:underline cursor-pointer">
                                Pusatkan
                            </button>
                        </div>
                    </div>

                    <!-- Proximity Match Status Badge (Max 10 Meter) -->
                    <div id="proximity-match-box" class="p-2.5 rounded-lg border {{ ($proximity['is_matched'] ?? false) ? 'bg-emerald-50/70 border-emerald-200' : 'bg-amber-50/70 border-amber-200' }} flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span id="proximity-match-dot" class="w-2.5 h-2.5 rounded-full {{ ($proximity['is_matched'] ?? false) ? 'bg-emerald-500 radar-pulse-green' : 'bg-amber-500' }}"></span>
                            <div>
                                <div id="proximity-match-title" class="font-bold {{ ($proximity['is_matched'] ?? false) ? 'text-emerald-900' : 'text-amber-900' }}">
                                    {{ ($proximity['is_matched'] ?? false) ? 'Alat IoT Terdeteksi di Lokasi CCTV' : 'Alat IoT di Luar Jangkauan Kamera' }}
                                </div>
                                <div id="proximity-match-desc" class="text-[10px] text-gray-500">
                                    Jarak: <strong id="proximity-distance-val">{{ $proximity['distance_meters'] ?? '—' }}</strong> meter | Batas Maks: <strong>10 meter</strong>
                                </div>
                            </div>
                        </div>
                        <span id="proximity-status-badge" class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ ($proximity['is_matched'] ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ($proximity['is_matched'] ?? false) ? 'MATCHED (≤10m)' : 'OUT OF RANGE' }}
                        </span>
                    </div>

                    <div id="cctv-gps-map" class="w-full h-44 rounded-xl border border-gray-200 overflow-hidden bg-slate-100" style="min-height: 176px;"></div>

                    <div class="flex items-center justify-between text-[11px] text-gray-600 pt-1">
                        <div>
                            <span>CCTV: <strong id="cctv-coord-text">{{ number_format($cctvLat, 6) }}, {{ number_format($cctvLng, 6) }}</strong></span><br>
                            <span>IoT GPS: <strong id="iot-coord-text">{{ $iotLat ? number_format($iotLat, 6) : '—' }}, {{ $iotLng ? number_format($iotLng, 6) : '—' }}</strong></span>
                        </div>
                        <a
                            id="link-google-maps"
                            href="https://www.google.com/maps?q={{ $cctvLat }},{{ $cctvLng }}"
                            target="_blank"
                            class="text-blue-600 hover:underline font-medium self-end">
                            Google Maps ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Audio Unblocker / Emergency Alarm Toast (Muncul jika Autoplay diblokir Browser) -->
    <div id="alarm-sound-unblocker" class="hidden fixed bottom-6 right-6 z-50 bg-red-600 text-white p-4 rounded-2xl shadow-2xl flex items-center gap-4 border-2 border-red-400 radar-pulse cursor-pointer">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl animate-bounce">
            🚨
        </div>
        <div>
            <div class="font-extrabold text-xs uppercase tracking-wider text-red-100">Peringatan BegalVision Aktif!</div>
            <div class="text-sm font-black text-white">Sirine Alarm Sedang Berbunyi</div>
            <div class="text-[11px] text-red-200 mt-0.5">Klik di sini untuk memastikan suara audio keluar melalui speaker</div>
        </div>
        <button type="button" id="btn-unblock-sound" class="px-3 py-2 bg-white text-red-700 font-extrabold text-xs rounded-xl shadow hover:bg-red-50 transition cursor-pointer whitespace-nowrap">
            🔊 Buka Audio
        </button>
    </div>

    <!-- Hidden Canvas for Video Snapshot Download -->
    <canvas id="snapshot-canvas" style="display: none;"></canvas>

    <!-- DIRECT INLINE JAVASCRIPT: Ensures 100% execution regardless of Blade stack handling -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cctvId = {{ $item->id }};
            const flaskBase = @json($flaskBase);
            const isMjpeg = @json($isMjpeg);
            const defaultAiStreamUrl = @json($baseStreamUrl);
            const defaultRawStreamUrl = @json($rawStreamUrl);
            const firebaseDbUrl = @json($firebaseDbUrl);

            let cctvLat = {{ $cctvLat }};
            let cctvLng = {{ $cctvLng }};
            let iotLat = {{ $iotLat ?? 'null' }};
            let iotLng = {{ $iotLng ?? 'null' }};
            let lat = iotLat || cctvLat;
            let lng = iotLng || cctvLng;

            // Elements - Video & Stream Controls
            const liveImg = document.getElementById('live-mjpeg-stream');
            const offlineOverlay = document.getElementById('stream-offline-overlay');
            const badgeOnline = document.getElementById('badge-online');
            const badgeDot = document.getElementById('badge-dot');
            const statusText = document.getElementById('status-text');
            const hudClock = document.getElementById('hud-clock');
            const hudModePill = document.getElementById('hud-mode-pill');
            const hudModelName = document.getElementById('hud-model-name');
            const hudSource = document.getElementById('hud-source');
            const hudFps = document.getElementById('hud-fps');
            const hudThreatAlert = document.getElementById('hud-threat-alert');
            const hudThreatText = document.getElementById('hud-threat-text');
            const eventLogContainer = document.getElementById('event-log-container');

            const modeAiBtn = document.getElementById('mode-ai-btn');
            const modeRawBtn = document.getElementById('mode-raw-btn');
            const modelSelect = document.getElementById('model-select');
            const modelSpinner = document.getElementById('model-switching-spinner');
            const confSlider = document.getElementById('conf-slider');
            const confValLabel = document.getElementById('conf-val-label');
            const fpsSlider = document.getElementById('fps-slider');
            const fpsValLabel = document.getElementById('fps-val-label');
            const cameraSourceSelect = document.getElementById('camera-source-select');
            const cameraSpinner = document.getElementById('camera-switching-spinner');
            const btnRefreshStream = document.getElementById('btn-refresh-stream');
            const btnRefreshIcon = document.getElementById('btn-refresh-icon');
            const btnReconnect = document.getElementById('btn-reconnect-stream');
            const btnSwitchRaw = document.getElementById('btn-switch-to-raw');
            const btnSnapshot = document.getElementById('btn-take-snapshot');
            const btnFullscreen = document.getElementById('btn-toggle-fullscreen');
            const videoContainer = document.getElementById('video-stream-container');

            // Elements - Multi-Modal Fuzzy Threat Gauge
            const rtdbStatusPill = document.getElementById('rtdb-status-pill');
            const fuzzyScoreNumber = document.getElementById('fuzzy-score-number');
            const fuzzyStatusPill = document.getElementById('fuzzy-status-pill');
            const fuzzyAutoAlarmBadge = document.getElementById('fuzzy-auto-alarm-badge');
            const fuzzyMeterBar = document.getElementById('fuzzy-meter-bar');
            const fuzzyRuleCode = document.getElementById('fuzzy-rule-code');
            const fuzzyRuleScenario = document.getElementById('fuzzy-rule-scenario');
            const fusedVisualText = document.getElementById('fused-visual-text');
            const fusedSoundText = document.getElementById('fused-sound-text');
            const fusedLuxText = document.getElementById('fused-lux-text');
            const fusedPirText = document.getElementById('fused-pir-text');

            // Elements - Proximity Matching & Coordinates (Radius max 10 meter)
            const proximityMatchBox = document.getElementById('proximity-match-box');
            const proximityMatchDot = document.getElementById('proximity-match-dot');
            const proximityMatchTitle = document.getElementById('proximity-match-title');
            const proximityDistanceVal = document.getElementById('proximity-distance-val');
            const proximityStatusBadge = document.getElementById('proximity-status-badge');
            const btnSyncGps = document.getElementById('btn-sync-gps');
            const cctvCoordText = document.getElementById('cctv-coord-text');
            const iotCoordText = document.getElementById('iot-coord-text');
            const linkGoogleMaps = document.getElementById('link-google-maps');

            // Elements - IoT Sensors & Actuators
            const pirIconWrapper = document.getElementById('pir-icon-wrapper');
            const pirStatusText = document.getElementById('pir-status-text');
            const pirBadge = document.getElementById('pir-badge');
            const noiseDbText = document.getElementById('noise-db-text');
            const noiseMeterBar = document.getElementById('noise-meter-bar');
            const noiseStatusText = document.getElementById('noise-status-text');
            const luxText = document.getElementById('lux-text');
            const luxMeterBar = document.getElementById('lux-meter-bar');
            const luxStatusText = document.getElementById('lux-status-text');
            const btnToggleBuzzer = document.getElementById('btn-toggle-buzzer');
            const btnToggleLed = document.getElementById('btn-toggle-led');
            const chkAutoAlarm = document.getElementById('chk-auto-alarm');

            // Initial State
            let currentMode = 'ai';
            let currentModel = modelSelect ? modelSelect.value : @json($activeModel);
            let currentConf = confSlider ? parseFloat(confSlider.value) : 0.25;
            let currentFps = fpsSlider ? parseInt(fpsSlider.value) : 30;
            let currentSource = cameraSourceSelect ? cameraSourceSelect.value : 0;
            let isBuzzerOn = false;
            let isLedOn = false;
            let isSwitchingCamera = false;
            let isSwitchingModel = false;
            let autoAlarmTriggered = false;

            // Live Sensor Readings (From Firebase RTDB)
            let liveSensors = {
                noise_db: {{ $telemetry['noise_db'] ?? 42 }},
                lux: {{ $telemetry['lux'] ?? 24 }},
                pir_motion: {{ !empty($telemetry['pir_motion']) ? 'true' : 'false' }},
                visual_label: '{{ $initVisualLabel ?? 'normal' }}',
                visual_conf: {{ $initVisualConf ?? 0.0 }}
            };

            // Hitung jarak Haversine (meter) antara dua titik koordinat GPS
            function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
                if (lat1 === null || lon1 === null || lat2 === null || lon2 === null) return null;
                const R = 6371000; // Radius rata-rata bumi dalam meter
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            // Memperbarui UI status kecocokan jarak IoT ke CCTV (Batas Max: 10 meter)
            function updateProximityUI(distMeters) {
                if (!proximityMatchBox || distMeters === null) return;
                const roundedDist = Math.round(distMeters * 10) / 10;
                if (proximityDistanceVal) proximityDistanceVal.textContent = roundedDist;

                const isMatched = (roundedDist <= 10.0);
                if (isMatched) {
                    proximityMatchBox.className = 'p-2.5 rounded-lg border bg-emerald-50/70 border-emerald-200 flex items-center justify-between text-xs transition';
                    if (proximityMatchDot) proximityMatchDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 radar-pulse-green';
                    if (proximityMatchTitle) {
                        proximityMatchTitle.className = 'font-bold text-emerald-900';
                        proximityMatchTitle.textContent = 'Alat IoT Terdeteksi di Lokasi CCTV';
                    }
                    if (proximityStatusBadge) {
                        proximityStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800';
                        proximityStatusBadge.textContent = 'MATCHED (≤10m)';
                    }
                } else {
                    proximityMatchBox.className = 'p-2.5 rounded-lg border bg-amber-50/70 border-amber-200 flex items-center justify-between text-xs transition';
                    if (proximityMatchDot) proximityMatchDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-500';
                    if (proximityMatchTitle) {
                        proximityMatchTitle.className = 'font-bold text-amber-900';
                        proximityMatchTitle.textContent = 'Alat IoT di Luar Jangkauan Kamera';
                    }
                    if (proximityStatusBadge) {
                        proximityStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800';
                        proximityStatusBadge.textContent = 'OUT OF RANGE (>10m)';
                    }
                }

                if (typeof updateMapPositions === 'function') {
                    updateMapPositions();
                }
            }

            // Tombol Penyelarasan Koordinat CCTV dengan GPS IoT
            btnSyncGps?.addEventListener('click', async () => {
                if (!confirm(`Samakan koordinat lokasi CCTV (${cctvLat.toFixed(5)}, ${cctvLng.toFixed(5)}) persis dengan posisi GPS alat IoT (${iotLat ? iotLat.toFixed(5) : '0'}, ${iotLng ? iotLng.toFixed(5) : '0'})? Jarak akan menjadi 0 meter.`)) {
                    return;
                }

                btnSyncGps.disabled = true;
                btnSyncGps.textContent = 'Menyelaraskan...';

                try {
                    const res = await fetch(`{{ route('admin.cctvs.sync-gps', $item) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        logEvent('INFO', data.message);
                        cctvLat = parseFloat(data.latitude);
                        cctvLng = parseFloat(data.longitude);
                        if (cctvCoordText) cctvCoordText.textContent = `${cctvLat.toFixed(6)}, ${cctvLng.toFixed(6)}`;
                        if (linkGoogleMaps) linkGoogleMaps.href = `https://www.google.com/maps?q=${cctvLat},${cctvLng}`;
                        updateProximityUI(0.0);
                        if (typeof updateMapPositions === 'function') updateMapPositions();
                    } else {
                        alert(data.message || 'Gagal menyamakan koordinat.');
                    }
                } catch(e) {
                    console.error(e);
                    alert('Gagal menghubungi server.');
                } finally {
                    btnSyncGps.disabled = false;
                    btnSyncGps.textContent = '🎯 Samakan Koordinat';
                }
            });

            // Audio Synthesizer for Siren (Web Audio API)
            let audioCtx = null;
            let sirenOsc = null;
            let sirenGain = null;
            let sirenInterval = null;
            const soundUnblocker = document.getElementById('alarm-sound-unblocker');
            const btnUnblockSound = document.getElementById('btn-unblock-sound');

            // Unlock AudioContext pada interaksi user pertama (menghindari blokir autoplay browser)
            const unlockAudio = () => {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx && audioCtx.state === 'suspended') {
                    audioCtx.resume().catch(() => {});
                }
            };
            ['click', 'touchstart', 'keydown'].forEach(evt => {
                document.addEventListener(evt, unlockAudio, { once: true });
            });

            soundUnblocker?.addEventListener('click', () => {
                unlockAudio();
                playSiren();
                if (soundUnblocker) soundUnblocker.classList.add('hidden');
            });

            function playSiren() {
                // HARD FAIL-SAFE GUARD (OPSI B):
                // Sirine Audio Web DILARANG KERAS BERBUNYI jika TIDAK terdeteksi KEDUANYA (Begal + Senjata)!
                const isBothVisual = ['begal_bersenjata', 'begal_senjata', 'begal+senjata'].some(c => (liveSensors.visual_label || '').includes(c));
                if (!isBothVisual && !isSimulationMode) {
                    stopSiren();
                    return;
                }

                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume().then(() => {
                            if (soundUnblocker) soundUnblocker.classList.add('hidden');
                        }).catch(() => {
                            if (soundUnblocker) soundUnblocker.classList.remove('hidden');
                        });
                    }

                    if (sirenOsc) return;

                    sirenOsc = audioCtx.createOscillator();
                    sirenGain = audioCtx.createGain();
                    sirenOsc.type = 'sawtooth';
                    sirenOsc.frequency.setValueAtTime(650, audioCtx.currentTime);

                    let up = true;
                    let f = 650;
                    if (sirenInterval) clearInterval(sirenInterval);
                    sirenInterval = setInterval(() => {
                        if (!sirenOsc) { clearInterval(sirenInterval); return; }
                        f = up ? f + 60 : f - 60;
                        if (f >= 1350) up = false;
                        if (f <= 600) up = true;
                        sirenOsc.frequency.setValueAtTime(f, audioCtx.currentTime);
                    }, 40);

                    sirenGain.gain.setValueAtTime(0.28, audioCtx.currentTime);
                    sirenOsc.connect(sirenGain);
                    sirenGain.connect(audioCtx.destination);
                    sirenOsc.start();
                } catch(e) {
                    console.warn('[BegalVision] Audio siren notice:', e);
                }
            }

            function stopSiren() {
                if (soundUnblocker) soundUnblocker.classList.add('hidden');
                if (sirenInterval) {
                    clearInterval(sirenInterval);
                    sirenInterval = null;
                }
                if (sirenOsc) {
                    try { sirenOsc.stop(); sirenOsc.disconnect(); } catch(e) {}
                    sirenOsc = null;
                }
            }

            // Real-time Clock
            setInterval(() => {
                const now = new Date();
                if (hudClock) hudClock.textContent = now.toTimeString().split(' ')[0];
            }, 1000);

            // ==========================================
            // 36 BASIS ATURAN LENGKAP FUZZY LOGIC BEGALVISION
            // Sesuai full_fuzzy_rules_begalvision.md
            // ==========================================
            const FUZZY_RULES = [
                // Cluster Visual BAHAYA (Rules 01 s/d 12)
                { code: 'R01', visual: 'Bahaya', sound: 'Teriakan', light: 'Gelap', motion: 'Gerak', score: 100, category: 'BAHAYA', alarm: true, scenario: 'Serangan Begal Aktif Malam: Pelaku bawa sajam, korban jerit histeris di jalan gelap.' },
                { code: 'R02', visual: 'Bahaya', sound: 'Teriakan', light: 'Gelap', motion: 'Diam', score: 90, category: 'BAHAYA', alarm: true, scenario: 'Penodongan Sudut Sempit: Korban terpojok tidak bisa lari dan menjerit minta tolong.' },
                { code: 'R03', visual: 'Bahaya', sound: 'Teriakan', light: 'Terang', motion: 'Gerak', score: 95, category: 'BAHAYA', alarm: true, scenario: 'Begal Siang Bolong: Pelaku sajam serang korban di siang hari dan korban berteriak.' },
                { code: 'R04', visual: 'Bahaya', sound: 'Teriakan', light: 'Terang', motion: 'Diam', score: 85, category: 'BAHAYA', alarm: true, scenario: 'Penodongan Siang Hari: Korban diamankan pelaku di siang hari dan menjerit.' },
                { code: 'R05', visual: 'Bahaya', sound: 'Normal', light: 'Gelap', motion: 'Gerak', score: 85, category: 'BAHAYA', alarm: true, scenario: 'Begal Senyap Malam: Korban diancam diam (tidak teriak), visual sajam terdeteksi.' },
                { code: 'R06', visual: 'Bahaya', sound: 'Normal', light: 'Gelap', motion: 'Diam', score: 80, category: 'BAHAYA', alarm: true, scenario: 'Todong Sajam Diam: Korban pasrah diancam sajam di tempat gelap.' },
                { code: 'R07', visual: 'Bahaya', sound: 'Normal', light: 'Terang', motion: 'Gerak', score: 80, category: 'BAHAYA', alarm: true, scenario: 'Begal Senyap Siang: Bawa sajam merebut motor tanpa ada suara teriakan.' },
                { code: 'R08', visual: 'Bahaya', sound: 'Normal', light: 'Terang', motion: 'Diam', score: 75, category: 'BAHAYA', alarm: true, scenario: 'Penodongan Siang Senyap: Korban ketakutan tidak bersuara, sajam terlihat jelas.' },
                { code: 'R09', visual: 'Bahaya', sound: 'Hening', light: 'Gelap', motion: 'Gerak', score: 85, category: 'BAHAYA', alarm: true, scenario: 'Serbuan Kilat Malam: Aksi begal cepat dan hening, sajam valid terdeteksi.' },
                { code: 'R10', visual: 'Bahaya', sound: 'Hening', light: 'Gelap', motion: 'Diam', score: 75, category: 'BAHAYA', alarm: true, scenario: 'Pengintaian Berbahaya: Pelaku memegang sajam sembunyi di area gelap malam hari.' },
                { code: 'R11', visual: 'Bahaya', sound: 'Hening', light: 'Terang', motion: 'Gerak', score: 75, category: 'BAHAYA', alarm: true, scenario: 'Perampasan Cepat Siang: Korban syok tidak bersuara, sajam teridentifikasi kamera.' },
                { code: 'R12', visual: 'Bahaya', sound: 'Hening', light: 'Terang', motion: 'Diam', score: 70, category: 'BAHAYA', alarm: true, scenario: 'Senjata Siang Hari: Kamera mendeteksi sajam di area publik siang hari.' },

                // Cluster Visual MENCURIGAKAN (Rules 13 s/d 24)
                { code: 'R13', visual: 'Mencurigakan', sound: 'Teriakan', light: 'Gelap', motion: 'Gerak', score: 60, category: 'WASPADA', alarm: false, scenario: 'Orang Mencurigakan Malam: Terdeteksi siluet/ancaman parsial dan suara di malam hari. Status waspada tanpa sirine buzzer.' },
                { code: 'R14', visual: 'Mencurigakan', sound: 'Teriakan', light: 'Gelap', motion: 'Diam', score: 55, category: 'WASPADA', alarm: false, scenario: 'Aktivitas Curiga Dekat Suara: Suara di malam hari di dekat orang mencurigakan. Status waspada tanpa sirine buzzer.' },
                { code: 'R15', visual: 'Mencurigakan', sound: 'Teriakan', light: 'Terang', motion: 'Gerak', score: 65, category: 'WASPADA', alarm: false, scenario: 'Keributan / Jambret Siang: Ada teriakan dan gerak cepat siang hari. Operator cek.' },
                { code: 'R16', visual: 'Mencurigakan', sound: 'Teriakan', light: 'Terang', motion: 'Diam', score: 55, category: 'WASPADA', alarm: false, scenario: 'Cekcok Warga Siang Hari: Orang ribut di pinggir jalan siang hari (bukan begal).' },
                { code: 'R17', visual: 'Mencurigakan', sound: 'Normal', light: 'Gelap', motion: 'Gerak', score: 60, category: 'WASPADA', alarm: false, scenario: 'Mondar-mandir Malam Hari: Orang gerak-gerik aneh dekat motor malam hari.' },
                { code: 'R18', visual: 'Mencurigakan', sound: 'Normal', light: 'Gelap', motion: 'Diam', score: 50, category: 'WASPADA', alarm: false, scenario: 'Mengintai Malam: Orang berdiri mencurigakan di area gelap tanpa bicara.' },
                { code: 'R19', visual: 'Mencurigakan', sound: 'Normal', light: 'Terang', motion: 'Gerak', score: 40, category: 'WASPADA', alarm: false, scenario: 'Aktivitas Ragu Siang: Orang mendekati motor di siang hari.' },
                { code: 'R20', visual: 'Mencurigakan', sound: 'Normal', light: 'Terang', motion: 'Diam', score: 35, category: 'AMAN', alarm: false, scenario: 'Orang Menunggu Siang: Warga berdiri santai di tepi jalan siang hari.' },
                { code: 'R21', visual: 'Mencurigakan', sound: 'Hening', light: 'Gelap', motion: 'Gerak', score: 55, category: 'WASPADA', alarm: false, scenario: 'Pergerakan Senyap Malam: Gerak mencurigakan tanpa suara di malam hari.' },
                { code: 'R22', visual: 'Mencurigakan', sound: 'Hening', light: 'Gelap', motion: 'Diam', score: 45, category: 'WASPADA', alarm: false, scenario: 'Malam Sunyi Curiga: Siluet orang diam di pojok gelap malam hari.' },
                { code: 'R23', visual: 'Mencurigakan', sound: 'Hening', light: 'Terang', motion: 'Gerak', score: 35, category: 'AMAN', alarm: false, scenario: 'Pejalan Kaki Siang: Orang berjalan biasa di siang hari.' },
                { code: 'R24', visual: 'Mencurigakan', sound: 'Hening', light: 'Terang', motion: 'Diam', score: 25, category: 'AMAN', alarm: false, scenario: 'Orang Istirahat Siang: Orang duduk diam di trotoar siang hari.' },

                // Cluster Visual AMAN (Rules 25 s/d 36)
                { code: 'R25', visual: 'Aman', sound: 'Teriakan', light: 'Gelap', motion: 'Gerak', score: 60, category: 'WASPADA', alarm: false, scenario: 'Suara Jeritan Malam (Visual Aman): Terdeteksi suara keras di area gelap namun visual bersih. Status waspada tanpa sirine buzzer.' },
                { code: 'R26', visual: 'Aman', sound: 'Teriakan', light: 'Gelap', motion: 'Diam', score: 50, category: 'WASPADA', alarm: false, scenario: 'Suara Keras Kejauhan: Suara bising malam hari tanpa visual bahaya dan tanpa pergerakan aktif.' },
                { code: 'R27', visual: 'Aman', sound: 'Teriakan', light: 'Terang', motion: 'Gerak', score: 30, category: 'AMAN', alarm: false, scenario: 'Knalpot Brong Siang Hari: Suara bising knalpot/klakson di jalan ramai siang hari.' },
                { code: 'R28', visual: 'Aman', sound: 'Teriakan', light: 'Terang', motion: 'Diam', score: 20, category: 'AMAN', alarm: false, scenario: 'Petir / Klakson Truk: Suara dentuman keras siang hari saat visual aman.' },
                { code: 'R29', visual: 'Aman', sound: 'Normal', light: 'Gelap', motion: 'Gerak', score: 25, category: 'AMAN', alarm: false, scenario: 'Warga Melintas Malam: Pejalan kaki atau motor warga lewat dengan wajar.' },
                { code: 'R30', visual: 'Aman', sound: 'Normal', light: 'Gelap', motion: 'Diam', score: 20, category: 'AMAN', alarm: false, scenario: 'Malam Normal: Suasana jalan sepi dan kondusif di malam hari.' },
                { code: 'R31', visual: 'Aman', sound: 'Normal', light: 'Terang', motion: 'Gerak', score: 15, category: 'AMAN', alarm: false, scenario: 'Lalu Lintas Normal Siang: Lalu lalang kendaraan dan pejalan kaki siang hari.' },
                { code: 'R32', visual: 'Aman', sound: 'Normal', light: 'Terang', motion: 'Diam', score: 10, category: 'AMAN', alarm: false, scenario: 'Siang Tenang: Kondisi jalanan aman terkendali.' },
                { code: 'R33', visual: 'Aman', sound: 'Hening', light: 'Gelap', motion: 'Gerak', score: 20, category: 'AMAN', alarm: false, scenario: 'Pejalan Kaki Senyap: Warga jalan santai malam hari tanpa suara.' },
                { code: 'R34', visual: 'Aman', sound: 'Hening', light: 'Gelap', motion: 'Diam', score: 10, category: 'AMAN', alarm: false, scenario: 'Malam Hening Sempurna: Tidak ada aktivitas apa pun di malam hari.' },
                { code: 'R35', visual: 'Aman', sound: 'Hening', light: 'Terang', motion: 'Gerak', score: 10, category: 'AMAN', alarm: false, scenario: 'Aktivitas Normal: Pejalan kaki melintas di siang hari yang hening.' },
                { code: 'R36', visual: 'Aman', sound: 'Hening', light: 'Terang', motion: 'Diam', score: 5, category: 'AMAN', alarm: false, scenario: 'Idle Kondisi Ideal: Tidak ada pergerakan, tidak ada suara, visual bersih.' }
            ];

            // Client-side Multi-Modal Fuzzy Inference Engine
            function evaluateClientFuzzy(visualLabel, visualConf, noiseDb, lux, pir) {
                const label = (visualLabel || 'normal').toLowerCase();
                const conf = Math.max(0.0, Math.min(1.0, visualConf || 0.0));
                const db = Math.max(30.0, Math.min(120.0, noiseDb || 42.0));
                const lx = Math.max(0.0, lux || 24.0);
                const isMotion = !!pir;

                // 1. Visual Membership (OPSI B: Wajib Keduanya Begal + Senjata untuk Bahaya Ekstrim)
                let muVisual = { Bahaya: 0.0, Mencurigakan: 0.0, Aman: 1.0 };
                const isCritical = ['begal_bersenjata', 'begal_senjata', 'begal+senjata'].some(c => label.includes(c)) ||
                    (label.includes('begal') && (label.includes('senjata') || label.includes('bersenjata')));
                const isSuspicious = ['begal', 'senjata', 'weapon', 'knife', 'korban', 'mencurigakan', 'suspicious', 'orang'].some(c => label.includes(c));

                if (isCritical) {
                    if (conf >= 0.65) {
                        muVisual = { Bahaya: 1.0, Mencurigakan: 0.0, Aman: 0.0 };
                    } else if (conf >= 0.30) {
                        const b = (conf - 0.30) / (0.65 - 0.30);
                        muVisual = { Bahaya: b, Mencurigakan: 1.0 - b, Aman: 0.0 };
                    } else {
                        const m = conf / 0.30;
                        muVisual = { Bahaya: 0.0, Mencurigakan: m, Aman: 1.0 - m };
                    }
                } else if (isSuspicious) {
                    if (conf >= 0.50) {
                        muVisual = { Bahaya: 0.0, Mencurigakan: 1.0, Aman: 0.0 };
                    } else {
                        const m = conf / 0.50;
                        muVisual = { Bahaya: 0.0, Mencurigakan: m, Aman: 1.0 - m };
                    }
                }

                // 2. Sound Membership (INMP441 dB)
                let muSound = { Hening: 0.0, Normal: 0.0, Teriakan: 0.0 };
                if (db <= 40.0) muSound.Hening = 1.0;
                else if (db < 55.0) muSound.Hening = (55.0 - db) / (55.0 - 40.0);

                if (db > 40.0 && db <= 55.0) muSound.Normal = (db - 40.0) / (55.0 - 40.0);
                else if (db > 55.0 && db <= 65.0) muSound.Normal = 1.0;
                else if (db > 65.0 && db < 75.0) muSound.Normal = (75.0 - db) / (75.0 - 65.0);

                if (db >= 75.0) muSound.Teriakan = 1.0;
                else if (db > 65.0) muSound.Teriakan = (db - 65.0) / (75.0 - 65.0);

                // 3. Light Membership (BH1750 Lux)
                let muLight = { Gelap: 0.0, Terang: 0.0 };
                if (lx <= 20.0) muLight.Gelap = 1.0;
                else if (lx < 30.0) {
                    const g = (30.0 - lx) / (30.0 - 20.0);
                    muLight.Gelap = g;
                    muLight.Terang = 1.0 - g;
                } else {
                    muLight.Terang = 1.0;
                }

                // 4. Motion Membership (PIR)
                const muMotion = {
                    Gerak: isMotion ? 1.0 : 0.0,
                    Diam: isMotion ? 0.0 : 1.0
                };

                // 5. Evaluate all 36 Rules (Sugeno Singleton)
                let weightedSum = 0.0;
                let totalWeight = 0.0;
                let dominantRule = FUZZY_RULES[35];
                let maxAlpha = -1.0;

                for (let i = 0; i < FUZZY_RULES.length; i++) {
                    const r = FUZZY_RULES[i];
                    const v = muVisual[r.visual] || 0.0;
                    const s = muSound[r.sound] || 0.0;
                    const l = muLight[r.light] || 0.0;
                    const m = muMotion[r.motion] || 0.0;
                    const alpha = Math.min(v, s, l, m);

                    if (alpha > 0.0001) {
                        weightedSum += alpha * r.score;
                        totalWeight += alpha;
                    }

                    if (alpha > maxAlpha) {
                        maxAlpha = alpha;
                        dominantRule = { ...r, firing_strength: alpha };
                    } else if (alpha === maxAlpha && dominantRule && r.score > dominantRule.score) {
                        dominantRule = { ...r, firing_strength: alpha };
                    }
                }

                let score = totalWeight > 0.0 ? Math.round((weightedSum / totalWeight) * 10) / 10 : dominantRule.score;
                score = Math.max(0.0, Math.min(100.0, score));

                let category = 'AMAN';
                let isAlarmTriggered = false;

                if (score >= 70.0) {
                    category = 'BAHAYA';
                    isAlarmTriggered = true;
                } else if (score >= 36.0) {
                    category = 'WASPADA';
                    isAlarmTriggered = false;
                }

                // HARD FAIL-SAFE GUARD (OPSI B):
                // Wajib Keduanya Begal + Senjata untuk Bahaya Ekstrim (Alarm Aktif)
                // Jika hanya terdeteksi salah satu (hanya begal saja atau sajam saja), sirine dilarang berbunyi dan skor dibatasi <= 60%.
                if (!isCritical) {
                    isAlarmTriggered = false;
                    if (score >= 70.0) score = 60.0;
                    if (category === 'BAHAYA') category = 'WASPADA';
                }

                return {
                    score: score,
                    category: category,
                    isAlarmTriggered: isAlarmTriggered,
                    dominantRule: dominantRule,
                    muVisual: muVisual,
                    muSound: muSound,
                    muLight: muLight,
                    muMotion: muMotion
                };
            }

            // Update Threat Gauge UI & Coordinate Auto-Alarm
            function updateFuzzyUI(fuzzy) {
                if (!fuzzyScoreNumber) return;

                const score = fuzzy.score;
                fuzzyScoreNumber.textContent = score + '%';

                if (fuzzyMeterBar) {
                    fuzzyMeterBar.style.width = Math.min(100, Math.max(5, score)) + '%';
                }

                if (fuzzy.category === 'BAHAYA') {
                    fuzzyScoreNumber.className = 'text-4xl font-extrabold font-mono text-red-600 animate-pulse';
                    if (fuzzyMeterBar) fuzzyMeterBar.className = 'h-full rounded-full transition-all duration-500 bg-red-600 radar-pulse';
                    if (fuzzyStatusPill) {
                        fuzzyStatusPill.className = 'px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-red-600 text-white radar-pulse';
                        fuzzyStatusPill.textContent = '🚨 BAHAYA EKSTRIM';
                    }
                    if (fuzzyAutoAlarmBadge) {
                        fuzzyAutoAlarmBadge.className = 'px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-100 text-red-800 animate-pulse';
                        fuzzyAutoAlarmBadge.textContent = 'ALARM ON (≥70%)';
                    }

                    // Auto-Trigger Alarm Handling (Hanya jika KEDUANYA Begal + Senjata terdeteksi!)
                    const isBothVisual = ['begal_bersenjata', 'begal_senjata', 'begal+senjata'].some(c => (liveSensors.visual_label || '').includes(c));
                    if (chkAutoAlarm && chkAutoAlarm.checked && !autoAlarmTriggered && isBothVisual) {
                        autoAlarmTriggered = true;
                        playSiren();
                        videoContainer?.classList.add('ring-4', 'ring-red-600', 'ring-offset-2');
                        logEvent('BAHAYA', `SKOR ANCAMAN FUZZY ${score}% (BAHAYA EKSTRIM)! Parameter begal: true dikirim ke Firebase untuk dibaca IoT.`);

                        // Sync to Hardware ESP-32 via Firebase RTDB (Kirim parameter begal: true)
                        if (fbDb) {
                            fbDb.ref('devices/BEGAL-VISION-01/status').update({
                                begal: true,
                                siren_active: true,
                                is_warning: true,
                                threat_score: score,
                                matched_cctv_id: cctvId
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01').update({
                                begal: true
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                                alarm: true,
                                buzzer: true
                            }).catch(console.warn);
                        }

                        // Update Buzzer Button State
                        if (btnToggleBuzzer) {
                            btnToggleBuzzer.dataset.active = '1';
                            btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm radar-pulse cursor-pointer whitespace-nowrap';
                            btnToggleBuzzer.textContent = '🛑 MATIKAN SIRINE';
                        }
                    } else if (!isBothVisual && autoAlarmTriggered) {
                        autoAlarmTriggered = false;
                        stopSiren();
                        videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                    }
                } else if (fuzzy.category === 'WASPADA') {
                    fuzzyScoreNumber.className = 'text-4xl font-extrabold font-mono text-amber-500';
                    if (fuzzyMeterBar) fuzzyMeterBar.className = 'h-full rounded-full transition-all duration-500 bg-amber-500';
                    if (fuzzyStatusPill) {
                        fuzzyStatusPill.className = 'px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800';
                        fuzzyStatusPill.textContent = '⚠️ WASPADA';
                    }
                    if (fuzzyAutoAlarmBadge) {
                        fuzzyAutoAlarmBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700';
                        fuzzyAutoAlarmBadge.textContent = 'Standby Alarm';
                    }

                    if (autoAlarmTriggered && chkAutoAlarm && chkAutoAlarm.checked) {
                        autoAlarmTriggered = false;
                        stopSiren();
                        videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                        if (fbDb) {
                            fbDb.ref('devices/BEGAL-VISION-01/status').update({
                                begal: false,
                                siren_active: false,
                                is_warning: false
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01').update({
                                begal: false
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                                alarm: false,
                                buzzer: false
                            }).catch(console.warn);
                        }
                    }
                } else {
                    fuzzyScoreNumber.className = 'text-4xl font-extrabold font-mono text-emerald-600';
                    if (fuzzyMeterBar) fuzzyMeterBar.className = 'h-full rounded-full transition-all duration-500 bg-emerald-500';
                    if (fuzzyStatusPill) {
                        fuzzyStatusPill.className = 'px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800';
                        fuzzyStatusPill.textContent = '🟢 AMAN';
                    }
                    if (fuzzyAutoAlarmBadge) {
                        fuzzyAutoAlarmBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700';
                        fuzzyAutoAlarmBadge.textContent = 'Auto-Trigger (≥70%)';
                    }

                    if (autoAlarmTriggered && chkAutoAlarm && chkAutoAlarm.checked) {
                        autoAlarmTriggered = false;
                        stopSiren();
                        videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                        if (fbDb) {
                            fbDb.ref('devices/BEGAL-VISION-01/status').update({
                                begal: false,
                                siren_active: false,
                                is_warning: false
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01').update({
                                begal: false
                            }).catch(console.warn);
                            fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                                alarm: false,
                                buzzer: false
                            }).catch(console.warn);
                        }
                    }
                }

                // Update Rule details
                const domRule = fuzzy.dominantRule || fuzzy.dominant_rule;
                if (domRule) {
                    if (fuzzyRuleCode) {
                        fuzzyRuleCode.textContent = domRule.code + ` (${Math.round((domRule.firing_strength || 1) * 100)}% Match)`;
                        fuzzyRuleCode.className = (fuzzy.category === 'BAHAYA')
                            ? 'px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-red-100 text-red-700'
                            : ((fuzzy.category === 'WASPADA') ? 'px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-amber-100 text-amber-700' : 'px-1.5 py-0.5 rounded font-mono font-bold text-[10px] bg-slate-200 text-slate-800');
                    }
                    if (fuzzyRuleScenario) {
                        fuzzyRuleScenario.textContent = domRule.scenario;
                    }
                }
            }

            // ==========================================
            // FIREBASE REALTIME DATABASE SUBSCRIPTION
            // https://safezone-9088b-default-rtdb.asia-southeast1.firebasedatabase.app/
            // ==========================================
            let fbDb = null;
            function initFirebaseRTDB() {
                try {
                    if (typeof firebase !== 'undefined') {
                        const app = firebase.initializeApp({
                            databaseURL: firebaseDbUrl
                        }, 'begalvision-web-' + Date.now());
                        fbDb = app.database();

                        const deviceRef = fbDb.ref('devices/BEGAL-VISION-01');
                        deviceRef.on('value', (snapshot) => {
                            const val = snapshot.val();
                            if (val) {
                                handleRealtimeFirebaseData(val);
                                if (rtdbStatusPill) {
                                    rtdbStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1';
                                    rtdbStatusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> RTDB LIVE';
                                }
                            }
                        }, (err) => {
                            console.warn('[BegalVision] Firebase RTDB subscription notice:', err);
                            if (rtdbStatusPill) {
                                rtdbStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800';
                                rtdbStatusPill.textContent = 'RTDB POLLING';
                            }
                        });
                    }
                } catch(e) {
                    console.warn('[BegalVision] Firebase init note:', e);
                }
            }

            function handleRealtimeFirebaseData(deviceData) {
                if (isSimulationMode) return;

                const sensors = deviceData.sensors || {};
                const status = deviceData.status || {};
                const gps = deviceData.gps || {};
                const gpsHistory = deviceData.gps_history || {};

                // 1. Ekstrak timestamp data terakhir perangkat IoT untuk cek batas 20 detik
                let lastTimestampMs = null;
                if (deviceData.updated_at) {
                    const rawTs = deviceData.updated_at;
                    if (typeof rawTs === 'number') {
                        lastTimestampMs = (rawTs < 100000000000) ? rawTs * 1000 : rawTs;
                    } else if (typeof rawTs === 'string') {
                        lastTimestampMs = new Date(rawTs).getTime();
                    }
                } else if (sensors.timestamp) {
                    const rawTs = sensors.timestamp;
                    lastTimestampMs = (rawTs < 100000000000) ? rawTs * 1000 : rawTs;
                } else if (gpsHistory && typeof gpsHistory === 'object') {
                    const histKeys = Object.keys(gpsHistory);
                    if (histKeys.length > 0) {
                        const lastKey = histKeys[histKeys.length - 1];
                        if (gpsHistory[lastKey] && gpsHistory[lastKey].timestamp) {
                            const rawTs = gpsHistory[lastKey].timestamp;
                            lastTimestampMs = (rawTs < 100000000000) ? rawTs * 1000 : rawTs;
                        }
                    }
                }

                const nowMs = Date.now();
                const ageSeconds = lastTimestampMs ? Math.max(0, Math.round((nowMs - lastTimestampMs) / 1000)) : 9999;
                const isDeviceStale = (ageSeconds > 20);

                let noiseDb = 40.0;
                let lux = 50.0;
                let pir = false;

                if (isDeviceStale) {
                    // NORMALISASI: Alat mati / data tidak terkirim > 20 detik
                    // Kembalikan semua sensor ke kondisi normal/idle agar alarm tidak bunyi terus-menerus
                    noiseDb = 40.0;
                    lux = 50.0;
                    pir = false;

                    liveSensors.noise_db = 40.0;
                    liveSensors.lux = 50.0;
                    liveSensors.pir_motion = false;

                    if (rtdbStatusPill) {
                        rtdbStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800';
                        rtdbStatusPill.textContent = `OFFLINE (>20s)`;
                    }

                    if (telemetryStatusPill) {
                        telemetryStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700';
                        telemetryStatusPill.textContent = `OFFLINE (${ageSeconds}s)`;
                    }

                    // Matikan sirine dan border darurat jika aktif karena sensor usang
                    const isVisualBegal = liveSensors.visual_label === 'begal_bersenjata';
                    if (!isVisualBegal) {
                        isBuzzerOn = false;
                        if (btnToggleBuzzer) {
                            btnToggleBuzzer.dataset.active = '0';
                            btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                            btnToggleBuzzer.textContent = '🔔 Picu Alarm';
                        }
                        if (autoAlarmTriggered) {
                            autoAlarmTriggered = false;
                            stopSiren();
                            videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                            if (hudThreatAlert) hudThreatAlert.classList.add('hidden');
                        }
                    }

                    // Bersihkan parameter darurat usang di Firebase agar tidak looping
                    if (fbDb && (status.begal || status.siren_active || (sensors.audio_level && sensors.audio_level > 500))) {
                        fbDb.ref('devices/BEGAL-VISION-01/status').update({
                            begal: false,
                            siren_active: false,
                            is_warning: false,
                            threat_score: 10
                        }).catch(console.warn);
                        fbDb.ref('devices/BEGAL-VISION-01').update({
                            begal: false
                        }).catch(console.warn);
                        fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                            alarm: false,
                            buzzer: false
                        }).catch(console.warn);
                    }
                } else {
                    // Data sensor masih segar (<= 20 detik)
                    if (sensors.sound_db !== undefined) {
                        noiseDb = parseFloat(sensors.sound_db);
                    } else if (sensors.audio_level !== undefined) {
                        const raw = parseFloat(sensors.audio_level);
                        if (raw > 200) {
                            noiseDb = Math.round((30.0 + ((Math.min(32767.0, Math.max(0, raw)) / 32767.0) * 80.0)) * 10) / 10;
                        } else {
                            noiseDb = raw;
                        }
                    }

                    lux = (sensors.lux !== undefined) ? parseFloat(sensors.lux) : 24;
                    pir = (sensors.pir == 1 || sensors.pir === true);

                    liveSensors.noise_db = noiseDb;
                    liveSensors.lux = lux;
                    liveSensors.pir_motion = pir;

                    if (rtdbStatusPill) {
                        rtdbStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1';
                        rtdbStatusPill.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> RTDB LIVE';
                    }

                    if (telemetryStatusPill) {
                        telemetryStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800';
                        telemetryStatusPill.textContent = 'TERHUBUNG';
                    }

                    // Sync Actuator Button UI & Web Alarm with Firebase Hardware Status
                    const hasAlarmState = (status.siren_active !== undefined || status.begal !== undefined);
                    if (hasAlarmState) {
                        const isHardwareAlarmActive = !!(status.siren_active || status.begal);
                        isBuzzerOn = isHardwareAlarmActive;
                        if (btnToggleBuzzer) {
                            btnToggleBuzzer.dataset.active = isBuzzerOn ? '1' : '0';
                            if (isBuzzerOn) {
                                btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm radar-pulse cursor-pointer whitespace-nowrap';
                                btnToggleBuzzer.textContent = '🛑 MATIKAN SIRINE';

                                const isBothDetected = ['begal_bersenjata', 'begal_senjata', 'begal+senjata'].some(c => (liveSensors.visual_label || '').includes(c));
                                if (chkAutoAlarm && chkAutoAlarm.checked && !autoAlarmTriggered && isBothDetected) {
                                    autoAlarmTriggered = true;
                                    playSiren();
                                    videoContainer?.classList.add('ring-4', 'ring-red-600', 'ring-offset-2');
                                    if (hudThreatAlert) {
                                        hudThreatAlert.classList.remove('hidden');
                                        hudThreatText.textContent = 'ALARM DARURAT AKTIF (TERHUBUNG KE LAPANGAN)';
                                    }
                                } else if (!isBothDetected) {
                                    // BUKAN KEDUA OBJEK: Matikan sirine dan bersihkan status darurat di Firebase!
                                    isBuzzerOn = false;
                                    if (autoAlarmTriggered) {
                                        autoAlarmTriggered = false;
                                        stopSiren();
                                        videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                                        if (hudThreatAlert) hudThreatAlert.classList.add('hidden');
                                    }
                                    btnToggleBuzzer.dataset.active = '0';
                                    btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                                    btnToggleBuzzer.textContent = '🔔 Picu Alarm';

                                    if (fbDb && chkAutoAlarm && chkAutoAlarm.checked) {
                                        fbDb.ref('devices/BEGAL-VISION-01/status').update({
                                            begal: false,
                                            siren_active: false,
                                            is_warning: false
                                        }).catch(console.warn);
                                        fbDb.ref('devices/BEGAL-VISION-01').update({
                                            begal: false
                                        }).catch(console.warn);
                                        fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                                            alarm: false,
                                            buzzer: false
                                        }).catch(console.warn);
                                    }
                                }
                            } else {
                                btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                                btnToggleBuzzer.textContent = '🔔 Picu Alarm';

                                if (autoAlarmTriggered) {
                                    autoAlarmTriggered = false;
                                    stopSiren();
                                    videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                                    if (hudThreatAlert) hudThreatAlert.classList.add('hidden');
                                }
                            }
                        }
                    }
                }

                // Sync UI widgets
                updateSensorTelemetryUI({
                    noise_db: noiseDb,
                    lux: lux,
                    pir_motion: pir,
                    buzzer_active: !isDeviceStale && !!status.siren_active,
                    led_active: !isDeviceStale && !!status.led_active,
                    is_stale: isDeviceStale,
                    age_seconds: ageSeconds
                });

                // Update fused indicators
                if (fusedSoundText) fusedSoundText.textContent = isDeviceStale ? '40 dB (Hening / Offline)' : `${noiseDb} dB (${(noiseDb >= 75) ? 'Teriakan!' : ((noiseDb >= 55) ? 'Normal' : 'Hening')})`;
                if (fusedLuxText) fusedLuxText.textContent = isDeviceStale ? '50 Lux (Standby)' : `${lux} Lux (${(lux < 20) ? 'Gelap' : 'Terang'})`;
                if (fusedPirText) fusedPirText.textContent = isDeviceStale ? 'Diam (Offline)' : (pir ? 'Gerak (Aktif)' : 'Diam');

                if (status.led_active !== undefined) {
                    isLedOn = !!status.led_active;
                    if (btnToggleLed) {
                        btnToggleLed.dataset.active = isLedOn ? '1' : '0';
                        if (isLedOn) {
                            btnToggleLed.className = 'px-3 py-1.5 bg-yellow-400 text-slate-900 rounded-lg text-xs font-extrabold transition shadow-sm cursor-pointer whitespace-nowrap';
                            btnToggleLed.textContent = '💡 Lampu OFF';
                        } else {
                            btnToggleLed.className = 'px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                            btnToggleLed.textContent = '💡 Lampu ON';
                        }
                    }
                }

                // Update GPS Map Location
                let newLat = null, newLng = null;
                if (gps.valid && gps.latitude && gps.longitude) {
                    newLat = parseFloat(gps.latitude);
                    newLng = parseFloat(gps.longitude);
                } else if (typeof gpsHistory === 'object' && Object.keys(gpsHistory).length > 0) {
                    const keys = Object.keys(gpsHistory);
                    const lastKey = keys[keys.length - 1];
                    const lastHist = gpsHistory[lastKey];
                    if (lastHist && lastHist.latitude && lastHist.longitude) {
                        newLat = parseFloat(lastHist.latitude);
                        newLng = parseFloat(lastHist.longitude);
                    }
                }

                if (newLat && newLng && typeof updateMapPosition === 'function') {
                    updateMapPosition(newLat, newLng);
                }

                // Re-evaluate Multi-Modal Fuzzy Logic instantly
                const fuzzy = evaluateClientFuzzy(
                    liveSensors.visual_label,
                    liveSensors.visual_conf,
                    noiseDb,
                    lux,
                    pir
                );
                updateFuzzyUI(fuzzy);
            }

            initFirebaseRTDB();

            // Construct new stream URL with all active parameters
            function buildStreamUrl() {
                const rawBase = (currentMode === 'ai') ? defaultAiStreamUrl : defaultRawStreamUrl;
                const url = new URL(rawBase, window.location.origin);

                if (currentMode === 'ai') {
                    url.searchParams.set('model', currentModel);
                    url.searchParams.set('conf', currentConf);
                } else {
                    url.searchParams.delete('model');
                    url.searchParams.delete('conf');
                }

                url.searchParams.set('source', currentSource);
                url.searchParams.set('fps', currentFps);
                url.searchParams.set('quality', '80');
                url.searchParams.set('_t', Date.now());
                return url.toString();
            }

            // Update MJPEG video stream with clean disconnect/reconnect
            function updateStream() {
                if (!liveImg || !isMjpeg) return;

                if (hudModePill) hudModePill.textContent = (currentMode === 'ai') ? 'AI BEGALVISION' : 'WEBCAM RAW';
                if (hudModelName) hudModelName.textContent = (currentMode === 'ai') ? currentModel : 'None (Raw)';
                if (hudSource) hudSource.textContent = currentSource;

                const newUrl = buildStreamUrl();
                console.log("[BegalVision] Reloading stream:", newUrl);

                liveImg.src = '';
                setTimeout(() => {
                    liveImg.src = newUrl;
                }, 100);
            }

            // Stream load/error event listeners
            if (liveImg) {
                liveImg.addEventListener('load', () => {
                    if (offlineOverlay) offlineOverlay.style.display = 'none';
                    setOnlineStatus(true);
                });

                liveImg.addEventListener('error', () => {
                    if (!isSwitchingCamera) {
                        if (offlineOverlay) offlineOverlay.style.display = 'flex';
                        setOnlineStatus(false);
                    }
                });
            }

            function setOnlineStatus(online) {
                if (!badgeOnline || !statusText) return;
                if (online) {
                    badgeOnline.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800';
                    if (badgeDot) badgeDot.className = 'w-2 h-2 rounded-full bg-emerald-500 radar-pulse-green';
                    statusText.textContent = (currentMode === 'ai') ? 'AI STREAM ONLINE' : 'RAW STREAM ONLINE';
                } else {
                    badgeOnline.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800';
                    if (badgeDot) badgeDot.className = 'w-2 h-2 rounded-full bg-amber-500';
                    statusText.textContent = 'STREAM TERPUTUS';
                }
            }

            // Dual-mode API Helper (Laravel Proxy + Direct Flask Fallback)
            async function sendFlaskCommand(endpoint, payload, laravelRoute) {
                try {
                    const proxyRes = await fetch(laravelRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    if (proxyRes.ok) {
                        const data = await proxyRes.json();
                        if (data && data.success !== false) return data;
                    }
                } catch(e) {
                    console.warn('[BegalVision] Proxy call failed, trying direct Flask call...', e);
                }

                try {
                    const directUrl = `${flaskBase}${endpoint}`;
                    const directRes = await fetch(directUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    return await directRes.json();
                } catch(directErr) {
                    console.error('[BegalVision] Direct Flask call failed:', directErr);
                    throw directErr;
                }
            }

            // 1. Camera Source Changing
            cameraSourceSelect?.addEventListener('change', async (e) => {
                const newSource = e.target.value;
                currentSource = newSource;
                isSwitchingCamera = true;

                cameraSourceSelect.disabled = true;
                if (cameraSpinner) cameraSpinner.classList.remove('hidden');
                logEvent('INFO', `Mengalihkan input kamera ke Source #${newSource}...`);

                updateStream();

                try {
                    const res = await sendFlaskCommand(
                        '/camera/source',
                        { source: newSource },
                        `{{ route('admin.cctvs.camera-source', $item) }}`
                    );

                    if (res && res.success !== false) {
                        logEvent('INFO', `Kamera berhasil dialihkan ke Source #${newSource}.`);
                    } else {
                        throw new Error(res?.message || res?.error || 'Gagal mengubah source');
                    }
                } catch (err) {
                    logEvent('WASPADA', `Peringatan: Source #${newSource} mungkin tidak terpasang di server.`);
                } finally {
                    cameraSourceSelect.disabled = false;
                    if (cameraSpinner) cameraSpinner.classList.add('hidden');
                    isSwitchingCamera = false;
                }
            });

            // 2. Model Selection Changing
            modelSelect?.addEventListener('change', async (e) => {
                const newModel = e.target.value;
                currentModel = newModel;
                isSwitchingModel = true;

                modelSelect.disabled = true;
                if (modelSpinner) modelSpinner.classList.remove('hidden');
                logEvent('INFO', `Mengganti model YOLO ONNX ke ${newModel}...`);

                updateStream();

                try {
                    await sendFlaskCommand(
                        '/models/select',
                        { model: newModel },
                        `{{ route('admin.cctvs.model-select', $item) }}`
                    );
                    logEvent('INFO', `Model ${newModel} aktif di server AI.`);
                } catch (err) {
                    console.warn('[BegalVision] Model endpoint fallback applied.');
                } finally {
                    modelSelect.disabled = false;
                    if (modelSpinner) modelSpinner.classList.add('hidden');
                    isSwitchingModel = false;
                }
            });

            // 3. Mode AI vs Raw Switching
            modeAiBtn?.addEventListener('click', () => {
                currentMode = 'ai';
                modeAiBtn.className = 'px-3 py-1.5 rounded-md text-xs font-bold transition bg-indigo-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer';
                modeRawBtn.className = 'px-3 py-1.5 rounded-md text-xs font-medium text-gray-700 hover:text-black transition flex items-center gap-1.5 cursor-pointer';
                updateStream();
                logEvent('INFO', 'Beralih ke mode AI BegalVision (Bounding Box aktif).');
            });

            modeRawBtn?.addEventListener('click', () => {
                currentMode = 'raw';
                modeRawBtn.className = 'px-3 py-1.5 rounded-md text-xs font-bold transition bg-slate-800 text-white shadow-sm flex items-center gap-1.5 cursor-pointer';
                modeAiBtn.className = 'px-3 py-1.5 rounded-md text-xs font-medium text-gray-700 hover:text-black transition flex items-center gap-1.5 cursor-pointer';
                updateStream();
                logEvent('INFO', 'Beralih ke mode Webcam Murni (Raw, hemat bandwidth/GPU).');
            });

            btnSwitchRaw?.addEventListener('click', () => {
                modeRawBtn?.click();
            });

            // 4. Confidence Slider
            confSlider?.addEventListener('input', (e) => {
                currentConf = parseFloat(e.target.value);
                if (confValLabel) confValLabel.textContent = Math.round(currentConf * 100) + '%';
            });

            confSlider?.addEventListener('change', () => {
                updateStream();
                logEvent('INFO', `Confidence threshold diperbarui ke ${Math.round(currentConf * 100)}%.`);
            });

            // 5. FPS Slider
            fpsSlider?.addEventListener('input', (e) => {
                currentFps = parseInt(e.target.value);
                if (fpsValLabel) fpsValLabel.textContent = currentFps + ' FPS';
            });

            fpsSlider?.addEventListener('change', () => {
                updateStream();
                logEvent('INFO', `Batasan streaming FPS diatur ke ${currentFps} FPS.`);
            });

            // 6. Explicit Reload Frame Button
            btnRefreshStream?.addEventListener('click', () => {
                if (btnRefreshIcon) btnRefreshIcon.classList.add('spinning');

                if (cameraSourceSelect) currentSource = cameraSourceSelect.value;
                if (modelSelect) currentModel = modelSelect.value;
                if (confSlider) currentConf = parseFloat(confSlider.value);
                if (fpsSlider) currentFps = parseInt(fpsSlider.value);

                sendFlaskCommand('/camera/source', { source: currentSource }, `{{ route('admin.cctvs.camera-source', $item) }}`).catch(console.warn);
                sendFlaskCommand('/models/select', { model: currentModel }, `{{ route('admin.cctvs.model-select', $item) }}`).catch(console.warn);

                updateStream();
                logEvent('INFO', `Stream direfresh: Model=${currentModel}, Cam=#${currentSource}, Conf=${Math.round(currentConf * 100)}%`);

                setTimeout(() => {
                    if (btnRefreshIcon) btnRefreshIcon.classList.remove('spinning');
                }, 600);
            });

            btnReconnect?.addEventListener('click', () => {
                btnRefreshStream?.click();
            });

            // Fullscreen
            btnFullscreen?.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    videoContainer?.requestFullscreen().catch(err => alert(err.message));
                } else {
                    document.exitFullscreen();
                }
            });

            // Snapshot Evidence Capture
            btnSnapshot?.addEventListener('click', () => {
                if (!liveImg || !liveImg.complete) {
                    alert('Gambar stream belum siap untuk diambil.');
                    return;
                }

                try {
                    const canvas = document.getElementById('snapshot-canvas');
                    canvas.width = liveImg.naturalWidth || 640;
                    canvas.height = liveImg.naturalHeight || 480;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(liveImg, 0, 0, canvas.width, canvas.height);

                    ctx.fillStyle = 'rgba(0, 0, 0, 0.65)';
                    ctx.fillRect(10, canvas.height - 35, 420, 25);
                    ctx.font = '12px monospace';
                    ctx.fillStyle = '#10b981';
                    ctx.fillText(`SafeZone BegalVision | ${new Date().toLocaleString()} | ${lat}, ${lng}`, 15, canvas.height - 18);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                    const link = document.createElement('a');
                    link.download = `BegalVision_Evidence_${Date.now()}.jpg`;
                    link.href = dataUrl;
                    link.click();

                    logEvent('INFO', 'Snapshot bukti foto berhasil disimpan ke perangkat.');
                } catch (e) {
                    window.open(liveImg.src, '_blank');
                }
            });

            // Actuator: Toggle Buzzer Siren (Manual Operator Control)
            btnToggleBuzzer?.addEventListener('click', async () => {
                isBuzzerOn = !isBuzzerOn;
                btnToggleBuzzer.dataset.active = isBuzzerOn ? '1' : '0';

                if (isBuzzerOn) {
                    btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm radar-pulse cursor-pointer whitespace-nowrap';
                    btnToggleBuzzer.textContent = '🛑 MATIKAN SIRINE';
                    playSiren();
                    videoContainer?.classList.add('ring-4', 'ring-red-600', 'ring-offset-2');
                    logEvent('BAHAYA', 'ALARM SIRINE LAPANGAN DIKENDALIKAN ON! Peringatan darurat dikirim.');
                } else {
                    btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                    btnToggleBuzzer.textContent = '🔔 Picu Alarm';
                    stopSiren();
                    autoAlarmTriggered = false;
                    videoContainer?.classList.remove('ring-4', 'ring-red-600', 'ring-offset-2');
                    logEvent('INFO', 'Sirine alarm dinonaktifkan oleh operator.');
                }

                // Sync directly to Firebase RTDB (Kirim parameter begal & siren)
                if (fbDb) {
                    fbDb.ref('devices/BEGAL-VISION-01/status').update({
                        begal: isBuzzerOn,
                        siren_active: isBuzzerOn,
                        is_warning: isBuzzerOn,
                        threat_score: isBuzzerOn ? 95 : 10
                    }).catch(console.warn);
                    fbDb.ref('devices/BEGAL-VISION-01').update({
                        begal: isBuzzerOn
                    }).catch(console.warn);
                    fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                        alarm: isBuzzerOn,
                        buzzer: isBuzzerOn
                    }).catch(console.warn);
                }

                try {
                    await fetch(`{{ route('admin.cctvs.actuator', $item) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ device: 'buzzer', state: isBuzzerOn })
                    });
                } catch(err) {
                    console.error(err);
                }
            });

            // Actuator: Toggle LED Floodlight
            btnToggleLed?.addEventListener('click', async () => {
                isLedOn = !isLedOn;
                btnToggleLed.dataset.active = isLedOn ? '1' : '0';

                if (isLedOn) {
                    btnToggleLed.className = 'px-3 py-1.5 bg-yellow-400 text-slate-900 rounded-lg text-xs font-extrabold transition shadow-sm cursor-pointer whitespace-nowrap';
                    btnToggleLed.textContent = '💡 Lampu OFF';
                    logEvent('INFO', 'Lampu sorot lapangan dinyalakan.');
                } else {
                    btnToggleLed.className = 'px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                    btnToggleLed.textContent = '💡 Lampu ON';
                    logEvent('INFO', 'Lampu sorot lapangan dimatikan.');
                }

                // Sync directly to Firebase RTDB
                if (fbDb) {
                    fbDb.ref('devices/BEGAL-VISION-01/status').update({
                        led_active: isLedOn
                    }).catch(console.warn);
                    fbDb.ref('devices/BEGAL-VISION-01/actuators').update({
                        led: isLedOn
                    }).catch(console.warn);
                }

                try {
                    await fetch(`{{ route('admin.cctvs.actuator', $item) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ device: 'led', state: isLedOn })
                    });
                } catch(err) {
                    console.error(err);
                }
            });

            // Log event function
            function logEvent(type, message) {
                if (!eventLogContainer) return;
                const timeStr = new Date().toTimeString().split(' ')[0];
                let badgeClass = 'bg-blue-100 text-blue-700';
                if (type === 'BAHAYA') badgeClass = 'bg-red-100 text-red-700 font-extrabold';
                if (type === 'WASPADA') badgeClass = 'bg-amber-100 text-amber-800 font-bold';

                const item = document.createElement('div');
                item.className = 'p-2.5 rounded-lg bg-slate-50 border border-slate-100 flex items-start justify-between transition animate-fadeIn';
                item.innerHTML = `
                    <div class="flex items-start gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] ${badgeClass}">${type}</span>
                        <div>
                            <p class="font-semibold text-gray-800">${message}</p>
                        </div>
                    </div>
                    <span class="text-gray-400 text-[10px] font-mono">${timeStr}</span>
                `;
                eventLogContainer.prepend(item);

                if (type === 'BAHAYA' && hudThreatAlert) {
                    hudThreatAlert.classList.remove('hidden');
                    hudThreatText.textContent = message;
                    setTimeout(() => hudThreatAlert.classList.add('hidden'), 5000);
                }
            }

            // Interactive Scenario Testing for Demonstration & Testing Bar Response
            let isSimulationMode = false;
            const btnTestBegal = document.getElementById('btn-test-begal');
            const btnTestScream = document.getElementById('btn-test-scream');
            const btnTestExtreme = document.getElementById('btn-test-extreme');
            const btnTestReset = document.getElementById('btn-test-reset');
            const testModeStatus = document.getElementById('test-mode-status');

            function applySimulatedScenario(vLabel, vConf, sDb, sLux, sPir, scenarioName) {
                isSimulationMode = true;
                if (testModeStatus) {
                    testModeStatus.textContent = '● ' + scenarioName;
                    testModeStatus.className = 'font-mono text-red-600 font-extrabold animate-pulse';
                }

                liveSensors.visual_label = vLabel;
                liveSensors.visual_conf = vConf;
                liveSensors.noise_db = sDb;
                liveSensors.lux = sLux;
                liveSensors.pir_motion = sPir;

                if (fusedVisualText) {
                    if (vLabel === 'begal_bersenjata') {
                        fusedVisualText.textContent = `🚨 Bahaya (Begal + Senjata ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-red-600 truncate animate-pulse';
                    } else if (vLabel === 'begal') {
                        fusedVisualText.textContent = `⚠️ Waspada (Begal Tanpa Sajam ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-amber-600 truncate';
                    } else if (vLabel === 'senjata') {
                        fusedVisualText.textContent = `⚠️ Waspada (Sajam Saja ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-amber-600 truncate';
                    } else {
                        fusedVisualText.textContent = 'Aman / Standby';
                        fusedVisualText.className = 'font-bold text-emerald-600 truncate';
                    }
                }

                updateSensorTelemetryUI({
                    noise_db: sDb,
                    lux: sLux,
                    pir_motion: sPir
                });

                if (fusedSoundText) fusedSoundText.textContent = `${sDb} dB (${(sDb >= 75) ? 'Teriakan!' : 'Normal'})`;
                if (fusedLuxText) fusedLuxText.textContent = `${sLux} Lux (${(sLux < 20) ? 'Gelap' : 'Terang'})`;
                if (fusedPirText) fusedPirText.textContent = sPir ? 'Gerak (Aktif)' : 'Diam';

                const fuzzy = evaluateClientFuzzy(vLabel, vConf, sDb, sLux, sPir);
                updateFuzzyUI(fuzzy);

                logEvent('BAHAYA', `[SIMULASI] Menguji skenario: ${scenarioName} -> Rule ${fuzzy.dominantRule?.code} (Skor: ${fuzzy.score}%)`);
            }

            btnTestBegal?.addEventListener('click', () => {
                applySimulatedScenario('begal_bersenjata', 0.85, liveSensors.noise_db || 57.9, liveSensors.lux || 8.33, true, 'Uji R05: Begal Bersenjata Malam');
            });

            btnTestScream?.addEventListener('click', () => {
                applySimulatedScenario('normal', 0.0, 85.0, liveSensors.lux || 8.33, true, 'Uji R25: Suara Jeritan Malam (Waspada, No Buzzer)');
            });

            btnTestExtreme?.addEventListener('click', () => {
                applySimulatedScenario('begal_bersenjata', 0.95, 95.0, 5.0, true, 'Uji R01: Serangan Begal Bersenjata Ekstrim');
            });

            btnTestReset?.addEventListener('click', () => {
                isSimulationMode = false;
                if (testModeStatus) {
                    testModeStatus.textContent = '● Mode Live AI';
                    testModeStatus.className = 'font-mono text-emerald-600 font-bold';
                }
                logEvent('INFO', 'Mode simulasi direset. Kembali membaca input nyata dari webcam & sensor.');
                pollStatus();
            });

            // Polling hardware status & fallback telemetry (Realtime per 1 detik)
            async function pollStatus() {
                if (isSimulationMode) return;
                let online = false;
                let camData = null;

                // 1. Ambil status & deteksi AI BegalVision langsung dari Flask
                try {
                    const ctrl = new AbortController();
                    const timer = setTimeout(() => ctrl.abort(), 1200);
                    const directRes = await fetch(`${flaskBase}/camera/status`, { cache: 'no-store', signal: ctrl.signal });
                    clearTimeout(timer);
                    if (directRes.ok) {
                        const directJson = await directRes.json();
                        if (directJson && directJson.success) {
                            camData = directJson;
                        }
                    }
                } catch(e) {}

                // 2. Jika koneksi direct belum merespons, gunakan proxy Laravel ai-status
                if (!camData) {
                    try {
                        const ctrl = new AbortController();
                        const timer = setTimeout(() => ctrl.abort(), 1800);
                        const res = await fetch(`{{ route('admin.cctvs.ai-status', $item) }}`, { cache: 'no-store', signal: ctrl.signal });
                        clearTimeout(timer);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.online && data.camera) {
                                camData = data.camera;
                            }
                        }
                    } catch(e) {}
                }

                if (camData) {
                    online = true;
                    setOnlineStatus(true);
                    applyCameraStatus(camData);
                }

                // 3. Poll pembacaan sensor IoT & server-evaluated fuzzy logic per 1 detik
                try {
                    const ctrl = new AbortController();
                    const timer = setTimeout(() => ctrl.abort(), 1800);
                    const telRes = await fetch(`{{ route('admin.cctvs.telemetry', $item) }}?visual_label=${encodeURIComponent(liveSensors.visual_label)}&visual_confidence=${liveSensors.visual_conf}`, { cache: 'no-store', signal: ctrl.signal });
                    clearTimeout(timer);
                    if (telRes.ok) {
                        const telData = await telRes.json();
                        if (telData.telemetry) {
                            updateSensorTelemetryUI(telData.telemetry);
                            if (telData.fuzzy) updateFuzzyUI(telData.fuzzy);
                        }
                    }
                } catch(e) {}
            }

            function applyCameraStatus(cam) {
                if (cam.fps && hudFps) hudFps.textContent = Number(cam.fps).toFixed(1);

                // Sinkronisasi Deteksi Visual AI Begal / Senjata ke liveSensors & Fuzzy Engine
                let vLabel = 'normal';
                let vConf = 0.0;

                const lt = cam.latest_threat;
                const detections = cam.latest_detections || (lt && lt.detections) || [];

                // OPSI B (Proteksi Ketat):
                // 1. Ekstrak langsung dari array detections (paling presisi)
                let hasBegal = false;
                let hasSenjata = false;
                let begalConf = 0.0;
                let senjataConf = 0.0;

                if (Array.isArray(detections) && detections.length > 0) {
                    const begal = detections.find(d => {
                        const name = (d.class_name || d.class || '').toLowerCase();
                        return name === 'begal' || name.includes('begal');
                    });
                    const senjata = detections.find(d => {
                        const name = (d.class_name || d.class || '').toLowerCase();
                        return ['senjata', 'clurit', 'pisau', 'weapon', 'knife', 'celurit', 'parang', 'pedang'].some(k => name.includes(k));
                    });

                    if (begal) {
                        hasBegal = true;
                        begalConf = Number(begal.confidence || 0.5);
                    }
                    if (senjata) {
                        hasSenjata = true;
                        senjataConf = Number(senjata.confidence || 0.5);
                    }
                }

                // Cek juga fallback latest_threat dari Flask
                if (lt) {
                    if (lt.has_both || lt.primary_label === 'begal_bersenjata') {
                        hasBegal = true;
                        hasSenjata = true;
                    } else if (lt.primary_label === 'begal' || lt.has_begal) {
                        hasBegal = true;
                        begalConf = Math.max(begalConf, Number(lt.max_conf || 0.5));
                    } else if (lt.primary_label === 'senjata' || lt.has_senjata) {
                        hasSenjata = true;
                        senjataConf = Math.max(senjataConf, Number(lt.max_conf || 0.5));
                    }
                }

                // Evaluasi Opsi B: Wajib Keduanya Begal + Senjata untuk Bahaya Ekstrim
                if (hasBegal && hasSenjata) {
                    vLabel = 'begal_bersenjata';
                    vConf = Math.max(begalConf, senjataConf, 0.7);
                } else if (hasBegal) {
                    vLabel = 'begal';
                    vConf = begalConf || 0.6;
                } else if (hasSenjata) {
                    vLabel = 'senjata';
                    vConf = senjataConf || 0.6;
                } else if (Array.isArray(detections) && detections.length > 0) {
                    const korban = detections.find(d => {
                        const name = (d.class_name || d.class || '').toLowerCase();
                        return ['korban', 'orang', 'person', 'mencurigakan'].some(k => name.includes(k));
                    });
                    if (korban) {
                        vLabel = detections.length > 1 ? 'mencurigakan' : 'normal';
                        vConf = Number(korban.confidence || 0.5);
                    }
                }

                liveSensors.visual_label = vLabel;
                liveSensors.visual_conf = vConf;

                // Update Teks Visual AI pada Kartu Fuzzy
                if (fusedVisualText) {
                    if (vLabel === 'begal_bersenjata') {
                        fusedVisualText.textContent = `🚨 Bahaya (Begal + Senjata ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-red-600 truncate animate-pulse';
                    } else if (vLabel === 'begal') {
                        fusedVisualText.textContent = `⚠️ Waspada (Begal Tanpa Sajam ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-amber-600 truncate';
                    } else if (vLabel === 'senjata') {
                        fusedVisualText.textContent = `⚠️ Waspada (Sajam Saja ${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-amber-600 truncate';
                    } else if (vLabel === 'mencurigakan' || vLabel === 'korban') {
                        fusedVisualText.textContent = `⚠️ Mencurigakan (${Math.round(vConf * 100)}%)`;
                        fusedVisualText.className = 'font-bold text-amber-600 truncate';
                    } else {
                        fusedVisualText.textContent = 'Aman / Standby';
                        fusedVisualText.className = 'font-bold text-emerald-600 truncate';
                    }
                }

                // Tampilkan Watermark Peringatan pada HUD CCTV (HANYA jika Begal + Senjata Keduanya Terdeteksi)
                if (vLabel === 'begal_bersenjata') {
                    if (hudThreatAlert) {
                        hudThreatAlert.classList.remove('hidden');
                        hudThreatText.textContent = `🚨 TERDETEKSI BEGAL BERSENJATA (${Math.round(vConf * 100)}%)`;
                    }
                } else {
                    if (hudThreatAlert) {
                        hudThreatAlert.classList.add('hidden');
                    }
                }

                // Segera evaluasi ulang logika Fuzzy multi-modal di browser
                const clientFuzzy = evaluateClientFuzzy(
                    liveSensors.visual_label,
                    liveSensors.visual_conf,
                    liveSensors.noise_db,
                    liveSensors.lux,
                    liveSensors.pir_motion
                );
                updateFuzzyUI(clientFuzzy);

                if (Array.isArray(cam.available_cameras) && cameraSourceSelect && !isSwitchingCamera) {
                    const avail = cam.available_cameras;
                    Array.from(cameraSourceSelect.options).forEach(opt => {
                        const idx = parseInt(opt.value);
                        if (avail.includes(idx)) {
                            opt.textContent = `Kamera ${idx} (Terhubung ●)`;
                        } else {
                            opt.textContent = `Kamera ${idx} (Tidak terdeteksi)`;
                        }
                    });
                }
            }

            function updateSensorTelemetryUI(t) {
                if (t.is_stale) {
                    if (telemetryStatusPill) {
                        telemetryStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700';
                        telemetryStatusPill.textContent = `OFFLINE (${t.age_seconds || 20}s)`;
                    }
                    if (rtdbStatusPill) {
                        rtdbStatusPill.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800';
                        rtdbStatusPill.textContent = `OFFLINE (>20s)`;
                    }
                }

                if (noiseDbText && t.noise_db !== undefined) {
                    noiseDbText.textContent = t.noise_db + ' dB';
                    const pct = Math.min(100, Math.round((t.noise_db / 120) * 100));
                    noiseMeterBar.style.width = pct + '%';
                    if (t.is_stale) {
                        noiseMeterBar.className = 'gauge-bar-fill bg-gray-400 h-full rounded-full';
                        noiseStatusText.textContent = 'Hening (Alat Mati / >20s)';
                        noiseStatusText.className = 'text-gray-500 font-medium';
                    } else if (t.noise_db >= 75) {
                        noiseMeterBar.className = 'gauge-bar-fill bg-red-500 h-full rounded-full radar-pulse';
                        noiseStatusText.textContent = 'TERIAKAN TERDETEKSI!';
                        noiseStatusText.className = 'text-red-600 font-bold';
                    } else if (t.noise_db >= 55) {
                        noiseMeterBar.className = 'gauge-bar-fill bg-amber-500 h-full rounded-full';
                        noiseStatusText.textContent = 'Aktivitas Normal';
                        noiseStatusText.className = 'text-amber-600 font-bold';
                    } else {
                        noiseMeterBar.className = 'gauge-bar-fill bg-indigo-500 h-full rounded-full';
                        noiseStatusText.textContent = 'Hening';
                        noiseStatusText.className = 'text-gray-700';
                    }
                }

                if (luxText && t.lux !== undefined) {
                    luxText.textContent = t.lux + ' Lux';
                    const pct = Math.min(100, Math.round((t.lux / 500) * 100));
                    luxMeterBar.style.width = pct + '%';
                    if (t.is_stale) {
                        luxStatusText.textContent = 'Standby (Offline)';
                        luxStatusText.className = 'text-gray-500 font-medium';
                    } else if (t.lux < 20) {
                        luxStatusText.textContent = 'Gelap (Malam Hari)';
                        luxStatusText.className = 'text-red-600 font-bold';
                    } else if (t.lux < 50) {
                        luxStatusText.textContent = 'Redup / Senja';
                        luxStatusText.className = 'text-amber-600 font-bold';
                    } else {
                        luxStatusText.textContent = 'Terang Cukup';
                        luxStatusText.className = 'text-emerald-600 font-bold';
                    }
                }

                if (t.is_stale) {
                    pirStatusText.textContent = 'Aman (Alat Offline)';
                    pirStatusText.className = 'text-xs font-medium text-gray-500';
                    pirBadge.className = 'px-2 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600';
                    pirBadge.textContent = 'OFFLINE';
                    pirIconWrapper.className = 'w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center transition';
                } else if (t.pir_motion) {
                    pirStatusText.textContent = 'GERAKAN MENCURIGAKAN!';
                    pirStatusText.className = 'text-xs font-bold text-red-600 animate-pulse';
                    pirBadge.className = 'px-2 py-1 rounded-md text-[10px] font-extrabold bg-red-100 text-red-700 radar-pulse';
                    pirBadge.textContent = 'AKTIF';
                    pirIconWrapper.className = 'w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center transition radar-pulse';
                } else {
                    pirStatusText.textContent = 'Aman (Tidak Ada Gerakan)';
                    pirStatusText.className = 'text-xs font-bold text-emerald-600';
                    pirBadge.className = 'px-2 py-1 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-700';
                    pirBadge.textContent = 'STANDBY';
                    pirIconWrapper.className = 'w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center transition';
                }
            }

            // Leaflet Map Initialization with 10-Meter Proximity Circle & Dual Marker
            let cctvMap = null;
            let cctvMarker = null;
            let iotMarker = null;
            let detectionCircle = null;
            let proximityLine = null;

            const mapEl = document.getElementById('cctv-gps-map');
            if (mapEl && typeof L !== 'undefined') {
                cctvMap = L.map('cctv-gps-map', {
                    zoomControl: true,
                    attributionControl: false
                }).setView([cctvLat, cctvLng], 18);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 20
                }).addTo(cctvMap);

                // Lingkaran Ambang Deteksi 10 Meter sekeliling CCTV
                const initialDist = (iotLat && iotLng) ? calculateHaversineDistance(cctvLat, cctvLng, iotLat, iotLng) : null;
                const initialMatched = (initialDist !== null && initialDist <= 10.0);

                detectionCircle = L.circle([cctvLat, cctvLng], {
                    color: initialMatched ? '#10b981' : '#f59e0b',
                    fillColor: initialMatched ? '#10b981' : '#f59e0b',
                    fillOpacity: 0.22,
                    weight: 2,
                    radius: 10 // Batas Jarak Maksimal 10 Meter
                }).addTo(cctvMap);

                detectionCircle.bindPopup(`<strong>Radius Deteksi Kamera (10 Meter)</strong><br><span style="color:#64748b;">Perangkat IoT harus berada dalam radius ini agar terdeteksi di CCTV.</span>`);

                // CCTV Marker (Ikon Biru Kamera)
                const cctvIcon = L.divIcon({
                    className: '',
                    html: `<span style="display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:999px;background:#2563eb;border:3px solid #ffffff;box-shadow:0 0 10px rgba(37,99,235,0.7);color:#ffffff;font-size:11px;">📹</span>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                cctvMarker = L.marker([cctvLat, cctvLng], { icon: cctvIcon }).addTo(cctvMap);
                cctvMarker.bindPopup(`
                    <div style="font-family:sans-serif;font-size:12px;">
                        <strong style="color:#1e40af;">📹 CCTV: ${@json($item->nama)}</strong><br/>
                        <span style="color:#64748b;">Lokasi: ${@json($item->lokasi?->nama_lokasi ?? 'Lumajang')}</span><br/>
                        <span style="font-size:10px;color:#475569;">${cctvLat.toFixed(6)}, ${cctvLng.toFixed(6)}</span>
                    </div>
                `);

                // IoT Marker (Ikon Ungu Chip ESP-32)
                const effectiveIotLat = iotLat || cctvLat;
                const effectiveIotLng = iotLng || cctvLng;
                const iotIcon = L.divIcon({
                    className: '',
                    html: `<span style="display:flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:6px;background:#8b5cf6;border:2px solid #ffffff;box-shadow:0 0 8px rgba(139,92,246,0.8);color:#ffffff;font-size:10px;">📡</span>`,
                    iconSize: [22, 22],
                    iconAnchor: [11, 11]
                });

                iotMarker = L.marker([effectiveIotLat, effectiveIotLng], { icon: iotIcon }).addTo(cctvMap);
                iotMarker.bindPopup(`
                    <div style="font-family:sans-serif;font-size:12px;">
                        <strong style="color:#7c3aed;">📡 IoT: BEGAL-VISION-01</strong><br/>
                        <span id="popup-iot-match" style="font-weight:bold;color:${initialMatched ? '#10b981' : '#f59e0b'};">
                            ● ${initialMatched ? 'TERHUBUNG (≤10m)' : 'DI LUAR JANGKAUAN (>10m)'}
                        </span><br/>
                        <span style="font-size:10px;color:#475569;">Jarak: ${initialDist !== null ? initialDist.toFixed(1) : '—'} m</span>
                    </div>
                `);

                setTimeout(() => cctvMap.invalidateSize(), 200);
                setTimeout(() => cctvMap.invalidateSize(), 600);
                window.addEventListener('resize', () => cctvMap.invalidateSize());

                document.getElementById('btn-recenter-map')?.addEventListener('click', () => {
                    cctvMap.setView([cctvLat, cctvLng], 18);
                    cctvMarker.openPopup();
                });
            }

            function updateMapPositions() {
                if (!cctvMap) return;
                if (cctvMarker) cctvMarker.setLatLng([cctvLat, cctvLng]);
                if (detectionCircle) detectionCircle.setLatLng([cctvLat, cctvLng]);

                const effIotLat = iotLat || cctvLat;
                const effIotLng = iotLng || cctvLng;
                if (iotMarker) iotMarker.setLatLng([effIotLat, effIotLng]);

                const dist = (iotLat && iotLng) ? calculateHaversineDistance(cctvLat, cctvLng, iotLat, iotLng) : 0;
                const isMatched = (dist <= 10.0);

                if (detectionCircle) {
                    detectionCircle.setStyle({
                        color: isMatched ? '#10b981' : '#f59e0b',
                        fillColor: isMatched ? '#10b981' : '#f59e0b'
                    });
                }
            }

            function updateMapPosition(newLat, newLng) {
                cctvLat = newLat;
                cctvLng = newLng;
                updateMapPositions();
            }

            // Mulai polling loop sensor & AI per 1 detik secara konsisten
            setTimeout(pollStatus, 200);
            setInterval(pollStatus, 1000);
        });
    </script>
</x-admin-layout>
