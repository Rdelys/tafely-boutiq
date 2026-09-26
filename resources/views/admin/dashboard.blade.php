@extends('layouts.admin')

@section('title', 'Dashboard — Admin Tafely')

@section('page-content')
    <div class="mb-8">
        <h1 class="font-display text-2xl md:text-3xl font-bold text-primary-900">Dashboard</h1>
        <p class="font-body text-gray-500 mt-1">Connecté en tant que {{ auth('admin')->user()->email }}.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <span class="material-symbols-outlined text-primary-700 text-4xl">dashboard</span>
        <p class="font-body text-sm text-gray-500 mt-3">Les KPIs globaux (boutiques, MRR, churn, revenus) arrivent à la prochaine étape.</p>
    </div>
@endsection