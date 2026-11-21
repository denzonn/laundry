@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))
@section('page_title', 'Dashboard')
@section('page_description', 'Ringkasan laundry hari ini')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold text-slate-800">Halo, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-sm text-slate-500">Statistik dashboard menyusul di Fase 5.</p>
    </div>
@endsection
