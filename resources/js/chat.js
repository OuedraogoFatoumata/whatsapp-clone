document.addEventListener('DOMContentLoaded', () => {

    // ── ÉLÉMENTS HTML ──────────────────────────────────────
    const inputMessage       = document.getElementById('input-message');
    const formMessage        = document.getElementById('form-message');
    const zoneMessages       = document.getElementById('zone-messages');
    const inputRecherche     = document.getElementById('input-recherche');
    const resultatsRecherche = document.getElementById('resultats-recherche');
    const btnIA              = document.getElementById('btn-ia');
    const menuIA             = document.getElementById('menu-ia');
    const btnRecap           = document.getElementById('ia-recap');
    const btnSuggerer        = document.getElementById('ia-suggerer');
    const btnReformuler      = document.getElementById('ia-reformuler');
    const btnEnvoyer         = document.getElementById('btn-envoyer');
    const labelFichier  = document.getElementById('label-fichier');
     const inputFichier  = document.getElementById('input-fichier');

    const conversationId = window.conversationId || null;

    // ══════════════════════════════════════════════════════
    // NAVIGATION ONGLETS (Discussions / Statuts / Profil)
    // ══════════════════════════════════════════════════════
    window.changerOnglet = function(onglet) {
        document.querySelectorAll('.panel').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.nav-btn, .nav-bas-btn').forEach(b => b.classList.remove('actif'));

        const panel = document.getElementById('panel-' + onglet);
        if (panel) panel.style.display = 'flex';

        const btnPC     = document.getElementById('nav-' + onglet);
        const btnMobile = document.getElementById('mobile-' + onglet);
        if (btnPC)     btnPC.classList.add('actif');
        if (btnMobile) btnMobile.classList.add('actif');
    };

    // ══════════════════════════════════════════════════════
    // RESPONSIVE MOBILE
    // ══════════════════════════════════════════════════════
    window.ouvrirChatMobile = function() {
        if (window.innerWidth <= 768) {
            const chat    = document.getElementById('chat-principal');
            const sidebar = document.getElementById('sidebar');
            if (chat)    chat.classList.add('actif');
            if (sidebar) sidebar.classList.add('cachee');
        }
    };

    window.retourSidebar = function() {
        const chat    = document.getElementById('chat-principal');
        const sidebar = document.getElementById('sidebar');
        if (chat)    chat.classList.remove('actif');
        if (sidebar) sidebar.classList.remove('cachee');
    };

    // Si conversation déjà active au chargement → ouvrir sur mobile
    if (conversationId && window.innerWidth <= 768) {
        window.ouvrirChatMobile();
    }

    // ══════════════════════════════════════════════════════
    // FORMULAIRE STATUT
    // ══════════════════════════════════════════════════════
    window.toggleFormulaireStatut = function() {
        const form = document.getElementById('formulaire-statut');
        if (form) {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    };

    // ══════════════════════════════════════════════════════
    // VIEWER STATUT
    // ══════════════════════════════════════════════════════
    window.voirStatut = function(userId) {
        const viewer = document.getElementById('statut-viewer');
        if (viewer) viewer.style.display = 'flex';
    };

    window.fermerStatut = function() {
        const viewer = document.getElementById('statut-viewer');
        if (viewer) viewer.style.display = 'none';
    };

    // Fermer le viewer statut avec la touche Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') window.fermerStatut();
    });

    // ══════════════════════════════════════════════════════
    // SCROLL VERS LE BAS
    // ══════════════════════════════════════════════════════
    function scrollerVersBas() {
        if (zoneMessages) {
            zoneMessages.scrollTop = zoneMessages.scrollHeight;
        }
    }

    scrollerVersBas();
    // ══════════════════════════════════════════════════════
// UPLOAD FICHIER (photo/vidéo)
// ══════════════════════════════════════════════════════
if (inputFichier) {
    inputFichier.addEventListener('change', async () => {
        const fichier = inputFichier.files[0];
        if (!fichier) return;

        // Aperçu avant envoi
        const lecteur = new FileReader();
        lecteur.onload = (e) => {
            afficherNotification('Envoi en cours...', 'info');
        };
        lecteur.readAsDataURL(fichier);

        try {
            const formData = new FormData();
            formData.append('conversation_id', conversationId);
            formData.append('body', '');
            formData.append('fichier', fichier);

            const reponse = await axios.post('/messages', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            afficherMessage(reponse.data.message, true);
            scrollerVersBas();

        } catch (erreur) {
            console.error('Erreur upload:', erreur);
            afficherNotification('Erreur lors de l\'envoi du fichier', 'erreur');
        }

        // Réinitialiser l'input
        inputFichier.value = '';
    });
}

    // ══════════════════════════════════════════════════════
    // ENVOI DE MESSAGE
    // ══════════════════════════════════════════════════════
    if (formMessage) {
        formMessage.addEventListener('submit', async (e) => {
            e.preventDefault();

            const texte = inputMessage.value.trim();
            if (!texte) return;

            inputMessage.value = '';
            inputMessage.style.height = 'auto';

            try {
                const reponse = await axios.post('/messages', {
                    conversation_id: conversationId,
                    body: texte,
                });

                afficherMessage(reponse.data.message, true);
                scrollerVersBas();

            } catch (erreur) {
                console.error('Erreur envoi message:', erreur);
                afficherNotification('Erreur lors de l\'envoi du message', 'erreur');
            }
        });
    }

    // Bouton envoyer
    if (btnEnvoyer) {
        btnEnvoyer.addEventListener('click', () => {
            if (formMessage) formMessage.dispatchEvent(new Event('submit'));
        });
    }

    // Entrée pour envoyer, Shift+Entrée = nouvelle ligne
    if (inputMessage) {
        inputMessage.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (formMessage) formMessage.dispatchEvent(new Event('submit'));
            }
        });

        // Auto-resize textarea
        inputMessage.addEventListener('input', () => {
            inputMessage.style.height = 'auto';
            inputMessage.style.height = Math.min(inputMessage.scrollHeight, 120) + 'px';

            if (btnReformuler) {
                btnReformuler.style.display = inputMessage.value.trim() ? 'flex' : 'none';
            }
        });
    }

    // ══════════════════════════════════════════════════════
    // AFFICHER UNE BULLE DE MESSAGE
    // ══════════════════════════════════════════════════════
    function afficherMessage(message, estMoi = false) {
        if (!zoneMessages) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('message-wrapper', estMoi ? 'moi' : 'autre');
        wrapper.dataset.messageId = message.id;

        const estIA   = message.type && message.type !== 'text';
        const badgeIA = estIA ? `<div class="badge-ia">IA</div>` : '';

        const dateMessage = new Date(message.created_at);
        const maintenant  = new Date();

        let dateAffichee;
        if (dateMessage.toDateString() === maintenant.toDateString()) {
            dateAffichee = "Aujourd'hui";
        } else {
            const hier = new Date();
            hier.setDate(maintenant.getDate() - 1);
            dateAffichee = dateMessage.toDateString() === hier.toDateString()
                ? "Hier"
                : dateMessage.toLocaleDateString('fr-FR');
        }

        const heure = dateMessage.toLocaleTimeString('fr-FR', {
            hour: '2-digit', minute: '2-digit'
        });

       wrapper.innerHTML = `
    <div class="bulle ${estMoi ? 'moi' : 'autre'} ${estIA ? 'ia' : ''}">
        ${badgeIA}
        ${message.file_path && message.file_type === 'image' ? `
            <img src="${message.file_path}" 
                 style="max-width:100%;border-radius:6px;cursor:pointer;margin-bottom:4px;"
                 onclick="window.open('${message.file_path}', '_blank')">
        ` : ''}
        ${message.file_path && message.file_type === 'video' ? `
            <video controls style="max-width:100%;border-radius:6px;margin-bottom:4px;">
                <source src="${message.file_path}">
            </video>
        ` : ''}
        ${message.body ? `<p class="bulle-texte">${echapper(message.body)}</p>` : ''}
        <div class="bulle-meta">
            <span class="bulle-heure">${heure}</span>
            ${estMoi ? '<span class="coches">✓✓</span>' : ''}
        </div>
    </div>
`;

        zoneMessages.appendChild(wrapper);
    }

    function echapper(texte) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(texte));
        return div.innerHTML;
    }

   
    if (conversationId && window.Echo) {
        window.Echo.private(`conversation.${conversationId}`)
            .listen('MessageSent', (data) => {
                if (data.message.user_id !== window.utilisateurId) {
                    afficherMessage(data.message, false);
                    scrollerVersBas();
                    jouerSonNotification();
                }
            });
    }

    function jouerSonNotification() {
        try {
            const contexteAudio = new AudioContext();
            const oscillateur   = contexteAudio.createOscillator();
            const gain          = contexteAudio.createGain();

            oscillateur.connect(gain);
            gain.connect(contexteAudio.destination);

            oscillateur.frequency.value = 440;
            gain.gain.setValueAtTime(0.1, contexteAudio.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, contexteAudio.currentTime + 0.3);

            oscillateur.start(contexteAudio.currentTime);
            oscillateur.stop(contexteAudio.currentTime + 0.3);
        } catch (e) {}
    }

    // ══════════════════════════════════════════════════════
    // RECHERCHE UTILISATEURS
    // ══════════════════════════════════════════════════════
    let minuterieRecherche = null;

    if (inputRecherche) {
        inputRecherche.addEventListener('input', () => {
            const texte = inputRecherche.value.trim();
            clearTimeout(minuterieRecherche);

            if (texte.length < 2) {
                masquerResultats();
                return;
            }

            minuterieRecherche = setTimeout(async () => {
                await rechercherUtilisateurs(texte);
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.barre-recherche') && !e.target.closest('.resultats-recherche')) {
                masquerResultats();
            }
        });
    }

    async function rechercherUtilisateurs(texte) {
        try {
            const reponse = await axios.get('/users/search', { params: { q: texte } });
            afficherResultats(reponse.data);
        } catch (erreur) {
            console.error('Erreur recherche:', erreur);
        }
    }

    function afficherResultats(utilisateurs) {
        if (!resultatsRecherche) return;

        resultatsRecherche.innerHTML = '';

        if (utilisateurs.length === 0) {
            resultatsRecherche.innerHTML = `
                <div class="resultat-item">
                    <p style="color:var(--texte-secondaire);font-size:13px;">Aucun utilisateur trouvé</p>
                </div>
            `;
        } else {
            utilisateurs.forEach(user => {
                const item = document.createElement('div');
                item.classList.add('resultat-item');
                item.innerHTML = `
                    <div class="avatar" style="width:40px;height:40px;font-size:15px;">
                        ${user.avatar ? `<img src="${user.avatar}" alt="${user.name}">` : user.initiales}
                        ${user.is_online ? '<div class="statut-en-ligne"></div>' : ''}
                    </div>
                    <div class="resultat-info">
                        <h4>${echapper(user.name)}</h4>
                        <p>@${echapper(user.username)}</p>
                    </div>
                `;
                item.addEventListener('click', () => demarrerConversation(user.id));
                resultatsRecherche.appendChild(item);
            });
        }

        resultatsRecherche.classList.add('visible');
    }

    function masquerResultats() {
        if (resultatsRecherche) resultatsRecherche.classList.remove('visible');
    }

    async function demarrerConversation(userId) {
        try {
            const reponse = await axios.post('/chat', { user_id: userId });
            window.ouvrirChatMobile();
            window.location.href = `/chat/${reponse.data.conversation_id}`;
        } catch (erreur) {
            console.error('Erreur création conversation:', erreur);
        }
    }

    // ══════════════════════════════════════════════════════
    // MODULE IA
    // ══════════════════════════════════════════════════════
    if (btnIA && menuIA) {
        btnIA.addEventListener('click', (e) => {
            e.stopPropagation();
            menuIA.classList.toggle('visible');
        });

        document.addEventListener('click', () => {
            menuIA.classList.remove('visible');
        });
    }

    if (btnRecap) {
        btnRecap.addEventListener('click', async () => {
            menuIA.classList.remove('visible');
            await appellerIA('recap');
        });
    }

    if (btnSuggerer) {
        btnSuggerer.addEventListener('click', async () => {
            menuIA.classList.remove('visible');
            await appellerIA('suggest');
        });
    }

    if (btnReformuler) {
        btnReformuler.addEventListener('click', async () => {
            menuIA.classList.remove('visible');
            await appellerIA('reformulate');
        });
    }

    async function appellerIA(action) {
        if (!conversationId) return;

        const texteOriginalBtn = btnIA.innerHTML;
        btnIA.innerHTML = '<div class="spinner-ia"></div>';
        btnIA.disabled = true;

        try {
            const donnees = { conversation_id: conversationId };

            if (action === 'reformulate') {
                donnees.texte = inputMessage.value.trim();
                if (!donnees.texte) {
                    afficherNotification('Écris d\'abord un message à reformuler', 'info');
                    return;
                }
            }

            const reponse  = await axios.post(`/ai/${action}`, donnees);
            const resultat = reponse.data.reponse;

            if (action === 'recap') {
                afficherMessage({
                    id:         'ia-' + Date.now(),
                    body:       'Résumé IA :\n\n' + resultat,
                    type:       'ai_recap',
                    created_at: new Date().toISOString(),
                }, false);
                scrollerVersBas();

            } else if (action === 'suggest' || action === 'reformulate') {
                inputMessage.value = resultat;
                inputMessage.style.height = 'auto';
                inputMessage.style.height = Math.min(inputMessage.scrollHeight, 120) + 'px';
                inputMessage.focus();
                if (btnReformuler) btnReformuler.style.display = 'flex';
            }

        } catch (erreur) {
            console.error('Erreur IA:', erreur);
            afficherNotification('Erreur lors de l\'appel IA', 'erreur');
        } finally {
            btnIA.innerHTML = texteOriginalBtn;
            btnIA.disabled  = false;
        }
    }

    // ══════════════════════════════════════════════════════
    // NOTIFICATIONS TOAST
    // ══════════════════════════════════════════════════════
    function afficherNotification(texte, type = 'info') {
        const couleurs = {
            info:   '#2a3942',
            succes: '#005c4b',
            erreur: '#3d1515',
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: ${couleurs[type] || couleurs.info};
            color: #e9edef;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            z-index: 9999;
            border: 1px solid var(--bordure);
        `;
        toast.textContent = texte;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity    = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ══════════════════════════════════════════════════════
    // STATUT EN LIGNE
    // ══════════════════════════════════════════════════════
    async function mettreAJourStatut() {
        try {
            await axios.post('/users/statut', { is_online: true });
        } catch (e) {}
    }

    if (window.utilisateurId) {
        mettreAJourStatut();
        setInterval(mettreAJourStatut, 30000);
    }

});