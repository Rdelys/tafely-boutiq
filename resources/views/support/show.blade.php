@extends('layouts.dashboard')

@section('title', $ticket->sujet.' — Support Tafely')

@section('page-content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('support.index') }}" class="text-gray-400 hover:text-primary-700 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-display text-xl md:text-2xl font-bold text-primary-900 truncate">{{ $ticket->sujet }}</h1>
        </div>
        <span @class([
            'shrink-0 inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap',
            'bg-green-50 text-green-700' => $ticket->estOuvert(),
            'bg-gray-100 text-gray-500' => ! $ticket->estOuvert(),
        ])>{{ $ticket->estOuvert() ? 'Ouvert' : 'Clôturé' }}</span>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-primary-50 border border-primary-100 text-primary-700 rounded-xl px-4 py-3">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body text-sm font-semibold">{{ session('status') }}</span>
        </div>
    @endif

    <div class="max-w-2xl flex flex-col gap-4 mb-6">
        @foreach ($ticket->messages as $m)
            <div class="flex {{ $m->estDeLAdmin() ? 'justify-start' : 'justify-end' }}">
                <div @class([
                    'max-w-[85%] rounded-2xl px-4 py-3',
                    'bg-primary-50' => $m->estDeLAdmin(),
                    'bg-white border border-gray-100 shadow-sm' => ! $m->estDeLAdmin(),
                ])>
                    <p class="font-body text-xs font-bold {{ $m->estDeLAdmin() ? 'text-primary-700' : 'text-gray-400' }} mb-1">
                        {{ $m->estDeLAdmin() ? 'Équipe Tafely' : 'Vous' }} · {{ $m->created_at->format('d/m/Y à H:i') }}
                    </p>
                    <p class="font-body text-sm text-gray-800 whitespace-pre-line">{{ $m->message }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if ($ticket->estOuvert())
        <form method="POST" action="{{ route('support.repondre', $ticket) }}" class="max-w-2xl bg-white rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
            @csrf
            <textarea name="message" rows="3" required maxlength="4000" placeholder="Votre message..."
                      class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3.5 py-2.5 font-body text-sm focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors resize-none mb-3"></textarea>
            <button type="submit" class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-body font-bold text-sm px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Envoyer
            </button>
        </form>
    @else
        <p class="font-body text-sm text-gray-400">Ce ticket est clôturé. Créez une nouvelle demande si besoin.</p>
    @endif
@endsection