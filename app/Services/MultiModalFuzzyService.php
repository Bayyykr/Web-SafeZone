<?php

namespace App\Services;

class MultiModalFuzzyService
{
    /**
     * Basis Aturan Lengkap Logika Fuzzy (Full 36 Rules) - BegalVision
     * Berdasarkan dokumen spesifikasi full_fuzzy_rules_begalvision.md
     */
    protected static array $rules = [
        // 1. Cluster Visual BAHAYA (Terdeteksi begal / senjata) - Rules 01 s/d 12
        [
            'code' => 'R01',
            'visual' => 'Bahaya',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 100,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Serangan Begal Aktif Malam: Pelaku bawa sajam, korban jerit histeris di jalan gelap.',
        ],
        [
            'code' => 'R02',
            'visual' => 'Bahaya',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 90,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Penodongan Sudut Sempit: Korban terpojok tidak bisa lari dan menjerit minta tolong.',
        ],
        [
            'code' => 'R03',
            'visual' => 'Bahaya',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 95,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Begal Siang Bolong: Pelaku sajam serang korban di siang hari dan korban berteriak.',
        ],
        [
            'code' => 'R04',
            'visual' => 'Bahaya',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 85,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Penodongan Siang Hari: Korban diamankan pelaku di siang hari dan menjerit.',
        ],
        [
            'code' => 'R05',
            'visual' => 'Bahaya',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 85,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Begal Senyap Malam: Korban diancam diam (tidak teriak), visual sajam terdeteksi.',
        ],
        [
            'code' => 'R06',
            'visual' => 'Bahaya',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 80,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Todong Sajam Diam: Korban pasrah diancam sajam di tempat gelap.',
        ],
        [
            'code' => 'R07',
            'visual' => 'Bahaya',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 80,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Begal Senyap Siang: Bawa sajam merebut motor tanpa ada suara teriakan.',
        ],
        [
            'code' => 'R08',
            'visual' => 'Bahaya',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 75,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Penodongan Siang Senyap: Korban ketakutan tidak bersuara, sajam terlihat jelas.',
        ],
        [
            'code' => 'R09',
            'visual' => 'Bahaya',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 85,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Serbuan Kilat Malam: Aksi begal cepat dan hening, sajam valid terdeteksi.',
        ],
        [
            'code' => 'R10',
            'visual' => 'Bahaya',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 75,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Pengintaian Berbahaya: Pelaku memegang sajam sembunyi di area gelap malam hari.',
        ],
        [
            'code' => 'R11',
            'visual' => 'Bahaya',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 75,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Perampasan Cepat Siang: Korban syok tidak bersuara, sajam teridentifikasi kamera.',
        ],
        [
            'code' => 'R12',
            'visual' => 'Bahaya',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 70,
            'category' => 'BAHAYA',
            'alarm' => true,
            'scenario' => 'Senjata Siang Hari: Kamera mendeteksi sajam di area publik siang hari.',
        ],

        // 2. Cluster Visual MENCURIGAKAN (Indikasi memepet / orang mendekat / Begal atau Sajam Saja) - Rules 13 s/d 24
        [
            'code' => 'R13',
            'visual' => 'Mencurigakan',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 60,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Orang Mencurigakan Malam: Terdeteksi siluet/ancaman parsial dan suara di malam hari. Status waspada tanpa sirine buzzer.',
        ],
        [
            'code' => 'R14',
            'visual' => 'Mencurigakan',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 55,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Aktivitas Curiga Dekat Suara: Suara di malam hari di dekat orang mencurigakan. Status waspada tanpa sirine buzzer.',
        ],
        [
            'code' => 'R15',
            'visual' => 'Mencurigakan',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 65,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Keributan / Jambret Siang: Ada teriakan dan gerak cepat siang hari. Operator cek.',
        ],
        [
            'code' => 'R16',
            'visual' => 'Mencurigakan',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 55,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Cekcok Warga Siang Hari: Orang ribut di pinggir jalan siang hari (bukan begal).',
        ],
        [
            'code' => 'R17',
            'visual' => 'Mencurigakan',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 60,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Mondar-mandir Malam Hari: Orang gerak-gerik aneh dekat motor malam hari.',
        ],
        [
            'code' => 'R18',
            'visual' => 'Mencurigakan',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 50,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Mengintai Malam: Orang berdiri mencurigakan di area gelap tanpa bicara.',
        ],
        [
            'code' => 'R19',
            'visual' => 'Mencurigakan',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 40,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Aktivitas Ragu Siang: Orang mendekati motor di siang hari.',
        ],
        [
            'code' => 'R20',
            'visual' => 'Mencurigakan',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 35,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Orang Menunggu Siang: Warga berdiri santai di tepi jalan siang hari.',
        ],
        [
            'code' => 'R21',
            'visual' => 'Mencurigakan',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 55,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Pergerakan Senyap Malam: Gerak mencurigakan tanpa suara di malam hari.',
        ],
        [
            'code' => 'R22',
            'visual' => 'Mencurigakan',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 45,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Malam Sunyi Curiga: Siluet orang diam di pojok gelap malam hari.',
        ],
        [
            'code' => 'R23',
            'visual' => 'Mencurigakan',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 35,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Pejalan Kaki Siang: Orang berjalan biasa di siang hari.',
        ],
        [
            'code' => 'R24',
            'visual' => 'Mencurigakan',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 25,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Orang Istirahat Siang: Orang duduk diam di trotoar siang hari.',
        ],

        // 3. Cluster Visual AMAN (Kondisi visual normal / pejalan kaki biasa) - Rules 25 s/d 36
        [
            'code' => 'R25',
            'visual' => 'Aman',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 60,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Suara Jeritan Malam (Visual Aman): Terdeteksi suara keras di area gelap namun visual bersih. Status waspada tanpa sirine buzzer.',
        ],
        [
            'code' => 'R26',
            'visual' => 'Aman',
            'sound' => 'Teriakan',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 50,
            'category' => 'WASPADA',
            'alarm' => false,
            'scenario' => 'Suara Keras Kejauhan: Suara bising malam hari tanpa visual bahaya dan tanpa pergerakan aktif.',
        ],
        [
            'code' => 'R27',
            'visual' => 'Aman',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 30,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Knalpot Brong Siang Hari: Suara bising knalpot/klakson di jalan ramai siang hari.',
        ],
        [
            'code' => 'R28',
            'visual' => 'Aman',
            'sound' => 'Teriakan',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 20,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Petir / Klakson Truk: Suara dentuman keras siang hari saat visual aman.',
        ],
        [
            'code' => 'R29',
            'visual' => 'Aman',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 25,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Warga Melintas Malam: Pejalan kaki atau motor warga lewat dengan wajar.',
        ],
        [
            'code' => 'R30',
            'visual' => 'Aman',
            'sound' => 'Normal',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 20,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Malam Normal: Suasana jalan sepi dan kondusif di malam hari.',
        ],
        [
            'code' => 'R31',
            'visual' => 'Aman',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 15,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Lalu Lintas Normal Siang: Lalu lalang kendaraan dan pejalan kaki siang hari.',
        ],
        [
            'code' => 'R32',
            'visual' => 'Aman',
            'sound' => 'Normal',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 10,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Siang Tenang: Kondisi jalanan aman terkendali.',
        ],
        [
            'code' => 'R33',
            'visual' => 'Aman',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Gerak',
            'score' => 20,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Pejalan Kaki Senyap: Warga jalan santai malam hari tanpa suara.',
        ],
        [
            'code' => 'R34',
            'visual' => 'Aman',
            'sound' => 'Hening',
            'light' => 'Gelap',
            'motion' => 'Diam',
            'score' => 10,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Malam Hening Sempurna: Tidak ada aktivitas apa pun di malam hari.',
        ],
        [
            'code' => 'R35',
            'visual' => 'Aman',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Gerak',
            'score' => 10,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Aktivitas Normal: Pejalan kaki melintas di siang hari yang hening.',
        ],
        [
            'code' => 'R36',
            'visual' => 'Aman',
            'sound' => 'Hening',
            'light' => 'Terang',
            'motion' => 'Diam',
            'score' => 5,
            'category' => 'AMAN',
            'alarm' => false,
            'scenario' => 'Idle Kondisi Ideal: Tidak ada pergerakan, tidak ada suara, visual bersih.',
        ],
    ];

    /**
     * Menghitung nilai keanggotaan Visual AI (YOLO).
     *
     * @param string $label
     * @param float $confidence (0.0 - 1.0)
     * @return array [Bahaya => float, Mencurigakan => float, Aman => float]
     */
    public function getVisualMemberships(string $label, float $confidence): array
    {
        $normalizedLabel = strtolower(trim($label));
        $conf = max(0.0, min(1.0, $confidence));

        // OPSI B (Proteksi Ketat):
        // Visual BAHAYA (Buzzer ON) HANYA jika terdeteksi KEDUANYA (Begal DAN Senjata sekaligus)!
        $isCriticalClass = in_array($normalizedLabel, ['begal_bersenjata', 'begal_senjata', 'begal+senjata']) ||
            (str_contains($normalizedLabel, 'begal') && (str_contains($normalizedLabel, 'senjata') || str_contains($normalizedLabel, 'bersenjata')));

        // Jika hanya salah satu (hanya begal saja, atau hanya senjata sajam saja) -> MENCURIGAKAN (WASPADA, Buzzer OFF)
        $isSuspiciousClass = in_array($normalizedLabel, ['begal', 'senjata', 'weapon', 'knife', 'korban', 'mencurigakan', 'suspicious', 'orang', 'person']);

        if ($isCriticalClass) {
            // Label bahaya begal/senjata
            if ($conf >= 0.65) {
                return ['Bahaya' => 1.0, 'Mencurigakan' => 0.0, 'Aman' => 0.0];
            } elseif ($conf >= 0.30) {
                $bahaya = ($conf - 0.30) / (0.65 - 0.30);
                return [
                    'Bahaya' => round($bahaya, 4),
                    'Mencurigakan' => round(1.0 - $bahaya, 4),
                    'Aman' => 0.0,
                ];
            } else {
                $mencurigakan = $conf / 0.30;
                return [
                    'Bahaya' => 0.0,
                    'Mencurigakan' => round($mencurigakan, 4),
                    'Aman' => round(1.0 - $mencurigakan, 4),
                ];
            }
        } elseif ($isSuspiciousClass) {
            // Label orang mendekat / korban
            if ($conf >= 0.50) {
                return ['Bahaya' => 0.0, 'Mencurigakan' => 1.0, 'Aman' => 0.0];
            } else {
                $mencurigakan = $conf / 0.50;
                return [
                    'Bahaya' => 0.0,
                    'Mencurigakan' => round($mencurigakan, 4),
                    'Aman' => round(1.0 - $mencurigakan, 4),
                ];
            }
        }

        // Default: Kondisi visual aman (motor, sepeda, normal, tidak ada deteksi)
        return ['Bahaya' => 0.0, 'Mencurigakan' => 0.0, 'Aman' => 1.0];
    }

    /**
     * Menghitung nilai keanggotaan Suara INMP441 (dB).
     *
     * @param float $db (30 - 110 dB)
     * @return array [Hening => float, Normal => float, Teriakan => float]
     */
    public function getSoundMemberships(float $db): array
    {
        $db = max(30.0, min(120.0, $db));

        // 1. Hening (30 - 55 dB)
        $hening = 0.0;
        if ($db <= 40.0) {
            $hening = 1.0;
        } elseif ($db < 55.0) {
            $hening = (55.0 - $db) / (55.0 - 40.0);
        }

        // 2. Normal (40 - 75 dB, puncak 55 - 65 dB)
        $normal = 0.0;
        if ($db > 40.0 && $db <= 55.0) {
            $normal = ($db - 40.0) / (55.0 - 40.0);
        } elseif ($db > 55.0 && $db <= 65.0) {
            $normal = 1.0;
        } elseif ($db > 65.0 && $db < 75.0) {
            $normal = (75.0 - $db) / (75.0 - 65.0);
        }

        // 3. Teriakan (65 - 110 dB, tegas jika >= 75 dB)
        $teriakan = 0.0;
        if ($db >= 75.0) {
            $teriakan = 1.0;
        } elseif ($db > 65.0) {
            $teriakan = ($db - 65.0) / (75.0 - 65.0);
        }

        return [
            'Hening' => round($hening, 4),
            'Normal' => round($normal, 4),
            'Teriakan' => round($teriakan, 4),
        ];
    }

    /**
     * Menghitung nilai keanggotaan Cahaya BH1750 (Lux).
     *
     * @param float $lux
     * @return array [Gelap => float, Terang => float]
     */
    public function getLightMemberships(float $lux): array
    {
        $lux = max(0.0, $lux);

        // Gelap: < 20 Lux. Transisi: 20 - 30 Lux. Terang: > 30 Lux.
        if ($lux <= 20.0) {
            return ['Gelap' => 1.0, 'Terang' => 0.0];
        } elseif ($lux < 30.0) {
            $gelap = (30.0 - $lux) / (30.0 - 20.0);
            return [
                'Gelap' => round($gelap, 4),
                'Terang' => round(1.0 - $gelap, 4),
            ];
        } else {
            return ['Gelap' => 0.0, 'Terang' => 1.0];
        }
    }

    /**
     * Menghitung nilai keanggotaan Gerakan PIR HC-SR501.
     *
     * @param bool|int $pir
     * @return array [Gerak => float, Diam => float]
     */
    public function getMotionMemberships($pir): array
    {
        $active = (bool) $pir;
        return [
            'Gerak' => $active ? 1.0 : 0.0,
            'Diam' => $active ? 0.0 : 1.0,
        ];
    }

    /**
     * Konversi nilai audio level mentah ESP-32 (jika berupa PCM 16-bit) ke desibel (dB).
     */
    public function convertAudioLevelToDb($rawAudioLevel): float
    {
        if ($rawAudioLevel === null) {
            return 42.0;
        }

        $val = (float) $rawAudioLevel;

        // Jika nilainya > 200, berarti merupakan raw amplitude I2S (0 - 32767)
        if ($val > 200.0) {
            $clamped = max(0.0, min(32767.0, $val));
            // Pemetaan rentang 30 dB s/d 110 dB
            $db = 30.0 + (($clamped / 32767.0) * 80.0);
            return round($db, 1);
        }

        // Jika nilainya <= 200, diasumsikan sudah berupa skala desibel (misal 30 s/d 110 dB)
        return round(max(30.0, min(120.0, $val)), 1);
    }

    /**
     * Menjalankan inferensi Mamdani/Sugeno Singleton untuk seluruh 36 aturan.
     *
     * @param array $inputs [
     *     'visual_label' => string,
     *     'visual_confidence' => float,
     *     'noise_db' => float (opsional jika audio_level ada),
     *     'audio_level' => int|float (opsional),
     *     'lux' => float,
     *     'pir' => bool|int
     * ]
     * @return array
     */
    public function evaluate(array $inputs): array
    {
        $visualLabel = $inputs['visual_label'] ?? 'normal';
        $visualConf = (float) ($inputs['visual_confidence'] ?? 0.0);

        if (isset($inputs['noise_db'])) {
            $noiseDb = (float) $inputs['noise_db'];
        } else {
            $noiseDb = $this->convertAudioLevelToDb($inputs['audio_level'] ?? null);
        }

        $lux = (float) ($inputs['lux'] ?? 25.0);
        $pir = !empty($inputs['pir']);

        // 1. Fuzzifikasi
        $muVisual = $this->getVisualMemberships($visualLabel, $visualConf);
        $muSound = $this->getSoundMemberships($noiseDb);
        $muLight = $this->getLightMemberships($lux);
        $muMotion = $this->getMotionMemberships($pir);

        // 2. Evaluasi 36 Aturan (T-norm MIN)
        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $dominantRule = null;
        $maxAlpha = -1.0;
        $evaluatedRules = [];

        foreach (self::$rules as $rule) {
            $v = $muVisual[$rule['visual']] ?? 0.0;
            $s = $muSound[$rule['sound']] ?? 0.0;
            $l = $muLight[$rule['light']] ?? 0.0;
            $m = $muMotion[$rule['motion']] ?? 0.0;

            $alpha = min($v, $s, $l, $m);

            if ($alpha > 0.0001) {
                $weightedSum += $alpha * $rule['score'];
                $totalWeight += $alpha;

                $evaluatedRules[] = [
                    'code' => $rule['code'],
                    'alpha' => round($alpha, 4),
                    'score' => $rule['score'],
                ];
            }

            // Identifikasi Aturan Dominan
            if ($alpha > $maxAlpha) {
                $maxAlpha = $alpha;
                $dominantRule = array_merge($rule, ['firing_strength' => round($alpha, 4)]);
            } elseif ($alpha == $maxAlpha && $dominantRule && $rule['score'] > $dominantRule['score']) {
                // Jika firing strength sama, prioritaskan aturan dengan dampak bahaya lebih tinggi
                $dominantRule = array_merge($rule, ['firing_strength' => round($alpha, 4)]);
            }
        }

        // 3. Defuzzifikasi (Centroid / Weighted Average Sugeno Singleton)
        if ($totalWeight > 0.0) {
            $score = round($weightedSum / $totalWeight, 1);
        } else {
            // Default jika tidak ada derajat keanggotaan
            $score = ($dominantRule ? (float) $dominantRule['score'] : 10.0);
        }

        // Bound 0 - 100%
        $score = max(0.0, min(100.0, $score));

        // 4. Penentuan Kategori & Pemicu Alarm
        // Ambang batas dokumen: >= 70% BAHAYA (ALARM ON), 36% - 69% WASPADA, <= 35% AMAN
        if ($score >= 70.0) {
            $category = 'BAHAYA';
            $isAlarmTriggered = true;
        } elseif ($score >= 36.0) {
            $category = 'WASPADA';
            $isAlarmTriggered = false;
        } else {
            $category = 'AMAN';
            $isAlarmTriggered = false;
        }

        // HARD FAIL-SAFE GUARD (OPSI B):
        // Sirine Buzzer HANYA boleh aktif jika terdeteksi KEDUANYA: Begal DAN Senjata secara simultan!
        // Jika hanya terdeteksi 1 begal saja, atau hanya 1 sajam saja, alarm DILARANG AKTIF dan skor dibatasi maksimal 60% (Waspada).
        $isBothBegalSenjata = in_array(strtolower($visualLabel), ['begal_bersenjata', 'begal_senjata', 'begal+senjata']) ||
            (str_contains(strtolower($visualLabel), 'begal') && (str_contains(strtolower($visualLabel), 'senjata') || str_contains(strtolower($visualLabel), 'bersenjata')));

        if (! $isBothBegalSenjata) {
            $isAlarmTriggered = false;
            if ($score >= 70.0) {
                $score = 60.0;
            }
            if ($category === 'BAHAYA') {
                $category = 'WASPADA';
            }
        }

        return [
            'score' => $score,
            'category' => $category,
            'is_alarm_triggered' => $isAlarmTriggered,
            'dominant_rule' => $dominantRule ?? self::$rules[35],
            'inputs' => [
                'visual' => [
                    'label' => $visualLabel,
                    'confidence' => $visualConf,
                    'memberships' => $muVisual,
                ],
                'sound' => [
                    'db' => $noiseDb,
                    'memberships' => $muSound,
                ],
                'light' => [
                    'lux' => $lux,
                    'memberships' => $muLight,
                ],
                'motion' => [
                    'active' => $pir,
                    'memberships' => $muMotion,
                ],
            ],
            'active_rules_count' => count($evaluatedRules),
            'evaluated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Mengembalikan seluruh basis aturan fuzzy untuk referensi dan pengujian.
     */
    public static function getAllRules(): array
    {
        return self::$rules;
    }
}
