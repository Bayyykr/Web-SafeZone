<?php

namespace Database\Seeders;

use App\Models\Polsek;
use Illuminate\Database\Seeder;

class PolsekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polseks = [
            [
                "nama" => "Polsek Lumajang Kota",
                "alamat" => "Jl. Alun-Alun Barat, Lumajang",
                "telepon" => "0334-881001",
            ],
            [
                "nama" => "Polsek Sukodono",
                "alamat" => "Jl. Raya Sukodono, Lumajang",
                "telepon" => "0334-881002",
            ],
            [
                "nama" => "Polsek Sumbersuko",
                "alamat" => "Jl. Raya Sumbersuko, Lumajang",
                "telepon" => "0334-881003",
            ],
            [
                "nama" => "Polsek Tekung",
                "alamat" => "Jl. Raya Tekung, Lumajang",
                "telepon" => "0334-881004",
            ],
            [
                "nama" => "Polsek Senduro",
                "alamat" => "Jl. Raya Senduro, Lumajang",
                "telepon" => "0334-881005",
            ],
            [
                "nama" => "Polsek Pasirian",
                "alamat" => "Jl. Raya Pasirian, Lumajang",
                "telepon" => "0334-881006",
            ],
            [
                "nama" => "Polsek Candipuro",
                "alamat" => "Jl. Raya Candipuro, Lumajang",
                "telepon" => "0334-881007",
            ],
            [
                "nama" => "Polsek Klakah",
                "alamat" => "Jl. Raya Klakah, Lumajang",
                "telepon" => "0334-881008",
            ],
            [
                "nama" => "Polsek Ranuyoso",
                "alamat" => "Jl. Raya Ranuyoso, Lumajang",
                "telepon" => "0334-881009",
            ],
            [
                "nama" => "Polsek Jatiroto",
                "alamat" => "Jl. Raya Jatiroto, Lumajang",
                "telepon" => "0334-881010",
            ],
            [
                "nama" => "Polsek Randuagung",
                "alamat" => "Jl. Raya Randuagung, Lumajang",
                "telepon" => "0334-881011",
            ],
            [
                "nama" => "Polsek Yosowilangun",
                "alamat" => "Jl. Raya Yosowilangun, Lumajang",
                "telepon" => "0334-881012",
            ],
            [
                "nama" => "Polsek Rowokangkung",
                "alamat" => "Jl. Raya Rowokangkung, Lumajang",
                "telepon" => "0334-881013",
            ],
            [
                "nama" => "Polsek Tempursari",
                "alamat" => "Jl. Raya Tempursari, Lumajang",
                "telepon" => "0334-881014",
            ],
            [
                "nama" => "Polsek Pronojiwo",
                "alamat" => "Jl. Raya Pronojiwo, Lumajang",
                "telepon" => "0334-881015",
            ],
        ];

        foreach ($polseks as $polsek) {
            Polsek::updateOrCreate(["nama" => $polsek["nama"]], $polsek);
        }
    }
}
