<x-admin-layout>
    <x-slot name="header">{{ $item->exists ? 'Edit CCTV' : 'Tambah CCTV' }}</x-slot>

    <div class="master-page">
        <form class="form-card space-y-4" method="POST" action="{{ $item->exists ? route('admin.cctvs.update', $item) : route('admin.cctvs.store') }}">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            @include('admin.master.cctv.partials.fields', ['item' => $item])

            <div class="flex gap-2 pt-2">
                <button class="btn-primary" type="submit">Simpan</button>
                <a class="btn-secondary" href="{{ route('admin.cctvs.index') }}">Kembali</a>
            </div>
        </form>
    </div>
</x-admin-layout>
