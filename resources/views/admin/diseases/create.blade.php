@extends('admin.layouts.app', ['title' => 'Tambah Penyakit'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Tambah Penyakit</h1>
    </div>

    <form method="POST" action="{{ route('admin.diseases.store') }}" class="max-w-3xl space-y-5 rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
        @include('admin.diseases._form')
    </form>
@endsection
