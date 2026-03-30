@extends('layouts.app')

@section('content')

<h2>Créer un statut</h2>

<form method="POST" action="{{ route('status.store') }}" enctype="multipart/form-data">
    @csrf

    <input type="text" name="content" placeholder="Écris ton statut">

    <input type="file" name="media">

    <button type="submit">Publier</button>
</form>

<hr>

<h2>Statuts (24h)</h2>

@foreach($statuses as $status)
    <div style="margin-bottom:20px;">
        <strong>{{ $status->user->name }}</strong><br>

        @if($status->content)
            <p>{{ $status->content }}</p>
        @endif

        @if($status->media)
            <img src="{{ asset('storage/' . $status->media) }}" width="200">
        @endif

        <small>{{ $status->created_at->diffForHumans() }}</small>
    </div>
@endforeach
@endsection