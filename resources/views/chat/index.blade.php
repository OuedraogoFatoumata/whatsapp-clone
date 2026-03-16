@extends('layouts.app')

@section('content')

<script>
    window.conversationId = {{ $conversationActive ? $conversationActive->id : 'null' }};
    window.utilisateurId  = {{ auth()->id() }};
    window.utilisateurNom = "{{ auth()->user()->name }}";
</script>

<div class="chat-layout">

   
    <div class="sidebar" id="sidebar">

        <div class="sidebar-header">
            <div class="sidebar-header-gauche">
                <div class="avatar-utilisateur">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <span style="color:var(--texte-principal); font-weight:600;">
                    {{ auth()->user()->name }}
                </span>
            </div>

            <div class="sidebar-icones">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-icone" title="Se déconnecter">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="barre-recherche">
            <div class="barre-recherche-inner">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px;height:18px;color:var(--texte-secondaire);flex-shrink:0;">
                    <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
                <input
                    type="text"
                    id="input-recherche"
                    placeholder="Rechercher ou démarrer une discussion"
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="resultats-recherche" id="resultats-recherche"></div>

        <div class="liste-conversations">
            @forelse($conversations as $conv)
                @php
                    $autreUser  = $conv->autreUtilisateur();
                    $dernierMsg = $conv->dernierMessage;
                    $nomAffiche = $autreUser ? $autreUser->name : ($conv->nom ?? 'Groupe');
                    $initiale   = strtoupper(substr($nomAffiche, 0, 1));
                    $estActive  = $conversationActive && $conversationActive->id === $conv->id;
                @endphp

                <a href="{{ route('chat.show', $conv->id) }}"
                   class="conversation-item {{ $estActive ? 'active' : '' }}"
                   onclick="ouvrirChatMobile()">

                    <div class="avatar">
                        @if($autreUser && $autreUser->avatar)
                            <img src="{{ $autreUser->avatar }}" alt="">
                        @else
                            {{ $initiale }}
                        @endif
                        @if($autreUser && $autreUser->is_online)
                            <div class="statut-en-ligne"></div>
                        @endif
                    </div>

                    <div class="conversation-info">
    <div class="conversation-info-haut">
        <span class="conversation-nom">{{ $nomAffiche }}</span>
        @if($dernierMsg)
            <span class="conversation-heure">
                {{ $dernierMsg->created_at->format('H:i') }}
            </span>
        @endif
    </div>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <p class="conversation-apercu" style="margin:0;">
            @if($dernierMsg)
                {{ Str::limit($dernierMsg->body, 40) }}
            @else
                <em>Aucun message</em>
            @endif
        </p>
        @php $nonLus = $conv->messagesNonLus(auth()->id()); @endphp
        @if($nonLus > 0)
            <span style="background:#25d366;color:white;border-radius:50%;min-width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;padding:0 4px;">
                {{ $nonLus }}
            </span>
        @endif
    </div>
</div>
                </a>

            @empty
                <div style="padding:40px 20px; text-align:center; color:var(--texte-secondaire);">
                    <p style="font-size:14px; margin-bottom:8px;">Aucune conversation</p>
                    <p style="font-size:12px;">Recherchez un utilisateur pour commencer</p>
                </div>
            @endforelse
        </div>

    </div>

   
    <div class="chat-principal" id="chat-principal">

        @if($conversationActive)
            @php
                $autreUser  = $conversationActive->autreUtilisateur();
                $nomContact = $autreUser ? $autreUser->name : 'Groupe';
            @endphp

            <div class="chat-header">
               
                <button class="btn-retour-mobile" onclick="retourSidebar()" title="Retour">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                </button>

                <div class="chat-header-gauche">
                    <div class="avatar" style="width:40px;height:40px;font-size:15px;">
                        @if($autreUser && $autreUser->avatar)
                            <img src="{{ $autreUser->avatar }}" alt="">
                        @else
                            {{ strtoupper(substr($nomContact, 0, 1)) }}
                        @endif
                    </div>
                    <div class="chat-header-info">
                        <h3>{{ $nomContact }}</h3>
                        @if($autreUser)
                            @if($autreUser->is_online)
                                <p>En ligne</p>
                            @else
                                <p class="hors-ligne">
                                    Vu à {{ $autreUser->last_seen_at
                                        ? $autreUser->last_seen_at->format('H:i')
                                        : 'inconnu' }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="zone-messages" id="zone-messages">
                @forelse($messages as $message)
                    @php
                        $estMoi = $message->user_id === auth()->id();
                        $estIA  = in_array($message->type, ['ai_recap', 'ai_suggestion']);
                    @endphp

                    <div class="message-wrapper {{ $estMoi ? 'moi' : 'autre' }}"
                         data-id="{{ $message->id }}">
                        <div class="bulle {{ $estMoi ? 'moi' : 'autre' }} {{ $estIA ? 'ia' : '' }}">

                            @if($estIA)
                                <div class="badge-ia">IA</div>
                            @endif

                            @if(!$estMoi)
                                <p style="color:var(--vert-principal);font-size:12px;font-weight:600;margin-bottom:4px;">
                                    {{ $message->utilisateur->name }}
                                </p>
                            @endif

                            <p class="bulle-texte">{{ $message->body }}</p>

                            <div class="bulle-meta">
                                <span class="bulle-heure">
                                    @if($message->created_at->isToday())
                                        Aujourd'hui {{ $message->created_at->format('H:i') }}
                                    @elseif($message->created_at->isYesterday())
                                        Hier {{ $message->created_at->format('H:i') }}
                                    @else
                                        {{ $message->created_at->format('d/m/Y H:i') }}
                                    @endif
                                </span>
                                @if($estMoi)
                                    <span class="coches">✓✓</span>
                                @endif
                            </div>
                        </div>
                    </div>

                @empty
                    <div style="text-align:center;color:var(--texte-secondaire);margin-top:60px;">
                        <p style="font-size:40px;margin-bottom:12px;"></p>
                        <p style="font-size:15px;">Aucun message pour l'instant</p>
                        <p style="font-size:13px;margin-top:6px;">Envoyez le premier message !</p>
                    </div>
                @endforelse
            </div>

            <div class="chat-pied">
                <div class="chat-pied-inner">

                    <div style="position:relative;">
                        <button class="btn-ia" id="btn-ia" title="Assistant IA">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width:22px;height:22px;">
                                <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2M9 11a2 2 0 0 0-2 2 2 2 0 0 0 2 2 2 2 0 0 0 2-2 2 2 0 0 0-2-2m6 0a2 2 0 0 0-2 2 2 2 0 0 0 2 2 2 2 0 0 0 2-2 2 2 0 0 0-2-2m-3 7a1 1 0 0 0-1 1 1 1 0 0 0 1 1 1 1 0 0 0 1-1 1 1 0 0 0-1-1z"/>
                            </svg>
                        </button>

                        <div class="menu-ia" id="menu-ia">
                            <p class="menu-ia-titre">Assistant IA</p>
                            <button class="menu-ia-item" id="ia-recap">
                                <span></span>
                                <span>Résumer la conversation</span>
                            </button>
                            <button class="menu-ia-item" id="ia-suggerer">
                                <span></span>
                                <span>Proposer une réponse</span>
                            </button>
                            <button class="menu-ia-item" id="ia-reformuler" style="display:none;">
                                <span></span>
                                <span>Reformuler mon message</span>
                            </button>
                        </div>
                    </div>

                    <form id="form-message" action="{{ route('messages.store') }}" method="POST" style="flex:1;display:flex;gap:8px;align-items:flex-end;">
                        @csrf
                         <input type="hidden" name="conversation_id" value="{{ $conversationActive->id }}">   
                            
                        <div class="input-message-wrapper">
                            <textarea
                               name="body"
                                id="input-message"
                                class="input-message"
                                placeholder="Tapez un message"
                                rows="1"
                            ></textarea>
                        </div>
                       <button type="button" id="btn-envoyer" class="btn-envoyer" title="Envoyer">
                            <svg viewBox="0 0 24 24" fill="white">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </form>

                </div>
            </div>

        @else
            <div class="chat-vide">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <h2>WhatsApp Clone</h2>
                <p>Sélectionnez une conversation ou recherchez<br>un utilisateur pour commencer</p>
            </div>
        @endif

    </div>
</div>


<script>
    function ouvrirChatMobile() {
        if (window.innerWidth <= 768) {
            document.getElementById('chat-principal').classList.add('actif');
            document.getElementById('sidebar').classList.add('cachee');
        }
    }

    function retourSidebar() {
        document.getElementById('chat-principal').classList.remove('actif');
        document.getElementById('sidebar').classList.remove('cachee');
    }

    
    @if($conversationActive)
        if (window.innerWidth <= 768) {
            ouvrirChatMobile();
        }
    @endif
</script>

@endsection