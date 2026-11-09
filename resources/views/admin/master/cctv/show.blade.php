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
                <!-- Telemetry Card: ESP-32 Sensor Grid -->
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
                            <span>Maks: 120 dB</span>
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
                        <div class="text-xs font-bold text-gray-800 uppercase tracking-wider text-[10px]">
                            Kendali Aktuator Lapangan (ESP-32)
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

                <!-- GPS Location Map Widget (Leaflet.js) -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="text-xs font-bold text-gray-800">Posisi GPS BegalVision (ESP-32)</h3>
                        </div>
                        <button
                            type="button"
                            id="btn-recenter-map"
                            class="text-[11px] text-blue-600 font-bold hover:underline cursor-pointer">
                            Pusatkan
                        </button>
                    </div>

                    <div id="cctv-gps-map" class="w-full h-44 rounded-xl border border-gray-200 overflow-hidden bg-slate-100" style="min-height: 176px;"></div>

                    <div class="flex items-center justify-between text-[11px] text-gray-600 pt-1">
                        <div>
                            <span>Lat: <strong>{{ number_format($lat, 6) }}</strong></span>
                            <span class="ml-2">Lng: <strong>{{ number_format($lng, 6) }}</strong></span>
                        </div>
                        <a
                            href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}"
                            target="_blank"
                            class="text-blue-600 hover:underline font-medium">
                            Google Maps ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Canvas for Video Snapshot Download -->
    <canvas id="snapshot-canvas" style="display: none;"></canvas>

    <!-- DIRECT INLINE JAVASCRIPT: Ensures 100% execution regardless of Blade stack handling -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cctvId = {{ $item->id }};
            const flaskBase = @json($flaskBase);
            const isMjpeg = @json($isMjpeg);
            const defaultAiStreamUrl = @json($baseStreamUrl);
            const defaultRawStreamUrl = @json($rawStreamUrl);
            const lat = {{ $lat }};
            const lng = {{ $lng }};

            // Elements
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

            // Telemetry Elements
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

            // Initialize State directly from form inputs
            let currentMode = 'ai'; // 'ai' or 'raw'
            let currentModel = modelSelect ? modelSelect.value : @json($activeModel);
            let currentConf = confSlider ? parseFloat(confSlider.value) : 0.25;
            let currentFps = fpsSlider ? parseInt(fpsSlider.value) : 30;
            let currentSource = cameraSourceSelect ? cameraSourceSelect.value : 0;
            let isBuzzerOn = false;
            let isLedOn = false;
            let isSwitchingCamera = false;
            let isSwitchingModel = false;

            // Audio Synthesizer for Siren
            let audioCtx = null;
            let sirenOsc = null;
            let sirenGain = null;

            function playSiren() {
                try {
                    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    if (sirenOsc) return;

                    sirenOsc = audioCtx.createOscillator();
                    sirenGain = audioCtx.createGain();
                    sirenOsc.type = 'sawtooth';
                    sirenOsc.frequency.setValueAtTime(600, audioCtx.currentTime);

                    let up = true;
                    let f = 600;
                    const modInterval = setInterval(() => {
                        if (!sirenOsc) { clearInterval(modInterval); return; }
                        f = up ? f + 45 : f - 45;
                        if (f >= 1150) up = false;
                        if (f <= 550) up = true;
                        sirenOsc.frequency.setValueAtTime(f, audioCtx.currentTime);
                    }, 50);

                    sirenGain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                    sirenOsc.connect(sirenGain);
                    sirenGain.connect(audioCtx.destination);
                    sirenOsc.start();
                } catch(e) {}
            }

            function stopSiren() {
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

                // Sync HUD UI labels immediately
                if (hudModePill) hudModePill.textContent = (currentMode === 'ai') ? 'AI BEGALVISION' : 'WEBCAM RAW';
                if (hudModelName) hudModelName.textContent = (currentMode === 'ai') ? currentModel : 'None (Raw)';
                if (hudSource) hudSource.textContent = currentSource;

                const newUrl = buildStreamUrl();
                console.log("[BegalVision] Reloading stream:", newUrl);

                // Chrome/WebKit requires clearing src first to cancel previous MJPEG HTTP connection
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
                // 1. Try Laravel Backend Proxy
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

                // 2. Direct Flask Server Call (CORS is active)
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
                const oldSource = currentSource;
                currentSource = newSource;
                isSwitchingCamera = true;

                cameraSourceSelect.disabled = true;
                if (cameraSpinner) cameraSpinner.classList.remove('hidden');
                logEvent('INFO', `Mengalihkan input kamera ke Source #${newSource}...`);

                // Update stream URL immediately so Flask starts the request with new source
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

                // Apply to stream immediately via URL parameter
                updateStream();

                try {
                    await sendFlaskCommand(
                        '/models/select',
                        { model: newModel },
                        `{{ route('admin.cctvs.model-select', $item) }}`
                    );
                    logEvent('INFO', `Model ${newModel} aktif di server AI.`);
                } catch (err) {
                    console.warn('[BegalVision] Model endpoint error, model diterapkan via query stream parameter.');
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

            // 6. Explicit Reload Frame Button: Re-reads ALL current input values and forces reload
            btnRefreshStream?.addEventListener('click', () => {
                if (btnRefreshIcon) btnRefreshIcon.classList.add('spinning');

                // Read real current values from DOM controls
                if (cameraSourceSelect) currentSource = cameraSourceSelect.value;
                if (modelSelect) currentModel = modelSelect.value;
                if (confSlider) currentConf = parseFloat(confSlider.value);
                if (fpsSlider) currentFps = parseInt(fpsSlider.value);

                // Notify Flask server
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

                    // Draw watermark
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

            // Actuator: Toggle Buzzer Siren
            btnToggleBuzzer?.addEventListener('click', async () => {
                isBuzzerOn = !isBuzzerOn;
                btnToggleBuzzer.dataset.active = isBuzzerOn ? '1' : '0';

                if (isBuzzerOn) {
                    btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm radar-pulse cursor-pointer whitespace-nowrap';
                    btnToggleBuzzer.textContent = '🛑 MATIKAN SIRINE';
                    playSiren();
                    logEvent('BAHAYA', 'ALARM SIRINE LAPANGAN DIKENDALIKAN ON! Peringatan darurat dikirim.');
                } else {
                    btnToggleBuzzer.className = 'px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer whitespace-nowrap';
                    btnToggleBuzzer.textContent = '🔔 Picu Alarm';
                    stopSiren();
                    logEvent('INFO', 'Sirine alarm dinonaktifkan.');
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

            // Actuator: Toggle LED
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

            // Polling hardware status & telemetry
            async function pollStatus() {
                let online = false;

                try {
                    const res = await fetch(`{{ route('admin.cctvs.ai-status', $item) }}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.online && data.camera) {
                            online = true;
                            applyCameraStatus(data.camera);
                        }
                    }
                } catch(e) {}

                if (!online) {
                    try {
                        const directRes = await fetch(`${flaskBase}/camera/status`);
                        if (directRes.ok) {
                            const directData = await directRes.json();
                            if (directData && directData.success) {
                                online = true;
                                applyCameraStatus(directData);
                            }
                        }
                    } catch(e) {}
                }

                if (online) {
                    setOnlineStatus(true);
                }

                // Poll sensor readings
                try {
                    const telRes = await fetch(`{{ route('admin.cctvs.telemetry', $item) }}`);
                    if (telRes.ok) {
                        const telData = await telRes.json();
                        if (telData.telemetry) updateTelemetryUI(telData.telemetry);
                    }
                } catch(e) {}
            }

            function applyCameraStatus(cam) {
                if (cam.fps && hudFps) hudFps.textContent = Number(cam.fps).toFixed(1);

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

            function updateTelemetryUI(t) {
                if (noiseDbText && t.noise_db !== undefined) {
                    noiseDbText.textContent = t.noise_db + ' dB';
                    const pct = Math.min(100, Math.round((t.noise_db / 120) * 100));
                    noiseMeterBar.style.width = pct + '%';
                    if (t.noise_db > 75) {
                        noiseMeterBar.className = 'gauge-bar-fill bg-red-500 h-full rounded-full';
                        noiseStatusText.textContent = 'TERIAKAN / KERIBUTAN!';
                        noiseStatusText.className = 'text-red-600 font-bold';
                    } else if (t.noise_db > 55) {
                        noiseMeterBar.className = 'gauge-bar-fill bg-amber-500 h-full rounded-full';
                        noiseStatusText.textContent = 'Aktivitas Suara';
                        noiseStatusText.className = 'text-amber-600 font-bold';
                    } else {
                        noiseMeterBar.className = 'gauge-bar-fill bg-indigo-500 h-full rounded-full';
                        noiseStatusText.textContent = 'Normal';
                        noiseStatusText.className = 'text-gray-700';
                    }
                }

                if (luxText && t.lux !== undefined) {
                    luxText.textContent = t.lux + ' Lux';
                    const pct = Math.min(100, Math.round((t.lux / 500) * 100));
                    luxMeterBar.style.width = pct + '%';
                    if (t.lux < 10) {
                        luxStatusText.textContent = 'Gelap Gulita (Rawan)';
                        luxStatusText.className = 'text-red-600 font-bold';
                    } else if (t.lux < 50) {
                        luxStatusText.textContent = 'Redup / Senja';
                        luxStatusText.className = 'text-amber-600 font-bold';
                    } else {
                        luxStatusText.textContent = 'Terang Cukup';
                        luxStatusText.className = 'text-emerald-600 font-bold';
                    }
                }

                if (t.pir_motion) {
                    pirStatusText.textContent = 'GERAKAN MENCURIGAKAN!';
                    pirStatusText.className = 'text-xs font-bold text-red-600 animate-pulse';
                    pirBadge.className = 'px-2 py-1 rounded-md text-[10px] font-extrabold bg-red-100 text-red-700 radar-pulse';
                    pirBadge.textContent = 'WASPADA';
                    pirIconWrapper.className = 'w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center transition radar-pulse';
                } else {
                    pirStatusText.textContent = 'Aman (Tidak Ada Gerakan)';
                    pirStatusText.className = 'text-xs font-bold text-emerald-600';
                    pirBadge.className = 'px-2 py-1 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-700';
                    pirBadge.textContent = 'STANDBY';
                    pirIconWrapper.className = 'w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center transition';
                }
            }

            // Start polling loop
            setTimeout(pollStatus, 600);
            setInterval(pollStatus, 4000);

            // Leaflet Map Initialization
            const mapEl = document.getElementById('cctv-gps-map');
            if (mapEl && typeof L !== 'undefined') {
                const cctvMap = L.map('cctv-gps-map', {
                    zoomControl: true,
                    attributionControl: false
                }).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(cctvMap);

                L.circle([lat, lng], {
                    color: '#ef4444',
                    fillColor: '#ef4444',
                    fillOpacity: 0.15,
                    radius: 80
                }).addTo(cctvMap);

                const radarIcon = L.divIcon({
                    className: '',
                    html: `<span style="display:block;width:18px;height:18px;border-radius:999px;background:#ef4444;border:3px solid #ffffff;box-shadow:0 0 10px rgba(239,68,68,0.8);"></span>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                });

                const marker = L.marker([lat, lng], { icon: radarIcon }).addTo(cctvMap);
                marker.bindPopup(`
                    <div style="font-family:sans-serif;font-size:12px;">
                        <strong>${@json($item->nama)}</strong><br/>
                        <span style="color:#64748b;">Wilayah: ${@json($item->lokasi?->nama_lokasi ?? 'Lumajang')}</span><br/>
                        <span style="color:#10b981;font-weight:bold;">● Status: BegalVision AI</span>
                    </div>
                `);

                setTimeout(() => cctvMap.invalidateSize(), 200);
                setTimeout(() => cctvMap.invalidateSize(), 600);

                window.addEventListener('resize', () => cctvMap.invalidateSize());

                document.getElementById('btn-recenter-map')?.addEventListener('click', () => {
                    cctvMap.setView([lat, lng], 16);
                    marker.openPopup();
                });
            }
        });
    </script>
</x-admin-layout>
