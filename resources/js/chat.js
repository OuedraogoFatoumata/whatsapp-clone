

document.addEventListener('DOMContentLoaded', () => {

   
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

   
    const conversationId = window.conversationId || null;

   
    function scrollerVersBas() {
        if (zoneMessages) {
            zoneMessages.scrollTop = zoneMessages.scrollHeight;
        }
    }

   
    scrollerVersBas();



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

    if (inputMessage) {
        inputMessage.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                formMessage.dispatchEvent(new Event('submit'));
            }
        });


        inputMessage.addEventListener('input', () => {
            inputMessage.style.height = 'auto';
            inputMessage.style.height = Math.min(inputMessage.scrollHeight, 120) + 'px';

            
            if (btnReformuler) {
                btnReformuler.style.display = inputMessage.value.trim() ? 'flex' : 'none';
            }
        });
    }

    
    /**
     * Crée et ajoute une bulle de message dans la zone des messages
     * @param {object} message - Les données du message
     * @param {boolean} estMoi - true si c'est mon message
     */
    function afficherMessage(message, estMoi = false) {
        if (!zoneMessages) return;

        const wrapper = document.createElement('div');
        wrapper.classList.add('message-wrapper', estMoi ? 'moi' : 'autre');
        wrapper.dataset.messageId = message.id;

       
        const estIA = message.type && message.type !== 'text';
        const badgeIA = estIA
            ? `<div class="badge-ia"> IA</div>`
            : '';

       
        const dateMessage = new Date(message.created_at);
const maintenant = new Date();

let dateAffichee;


if (dateMessage.toDateString() === maintenant.toDateString()) {
    dateAffichee = "Aujourd’hui";
} else {
    const hier = new Date();
    hier.setDate(maintenant.getDate() - 1);

    if (dateMessage.toDateString() === hier.toDateString()) {
        dateAffichee = "Hier";
    } else {
        dateAffichee = dateMessage.toLocaleDateString('fr-FR');
    }
}

const heure = dateMessage.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
});

        wrapper.innerHTML = `
            <div class="bulle ${estMoi ? 'moi' : 'autre'} ${estIA ? 'ia' : ''}">
                ${badgeIA}
                <p class="bulle-texte">${echapper(message.body)}</p>
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
            const oscillateur = contexteAudio.createOscillator();
            const gain = contexteAudio.createGain();

            oscillateur.connect(gain);
            gain.connect(contexteAudio.destination);

            oscillateur.frequency.value = 440;
            gain.gain.setValueAtTime(0.1, contexteAudio.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, contexteAudio.currentTime + 0.3);

            oscillateur.start(contexteAudio.currentTime);
            oscillateur.stop(contexteAudio.currentTime + 0.3);
        } catch (e) {
           
        }
    }

   

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
            const reponse = await axios.get('/users/search', {
                params: { q: texte }
            });

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
                    <p style="color: var(--texte-secondaire); font-size: 13px;">
                        Aucun utilisateur trouvé
                    </p>
                </div>
            `;
        } else {
            utilisateurs.forEach(user => {
                const item = document.createElement('div');
                item.classList.add('resultat-item');
                item.innerHTML = `
                    <div class="avatar" style="width:40px;height:40px;font-size:15px;">
                        ${user.avatar
                            ? `<img src="${user.avatar}" alt="${user.name}">`
                            : user.initiales
                        }
                        ${user.is_online ? '<div class="statut-en-ligne"></div>' : ''}
                    </div>
                    <div class="resultat-info">
                        <h4>${echapper(user.name)}</h4>
                        <p>@${echapper(user.username)}</p>
                    </div>
                `;

                item.addEventListener('click', () => {
                    demarrerConversation(user.id);
                });

                resultatsRecherche.appendChild(item);
            });
        }

        resultatsRecherche.classList.add('visible');
    }

    function masquerResultats() {
        if (resultatsRecherche) {
            resultatsRecherche.classList.remove('visible');
        }
    }

   
    async function demarrerConversation(userId) {
        try {
            const reponse = await axios.post('/chat', {
                user_id: userId
            });

            
            window.location.href = `/chat/${reponse.data.conversation_id}`;
        } catch (erreur) {
            console.error('Erreur création conversation:', erreur);
        }
    }

  
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

    /**
     * Appelle le module IA du serveur
     * @param {string} action - 'recap', 'suggest', ou 'reformulate'
     */
    async function appellerIA(action) {
        if (!conversationId) return;

       
        const texteOriginalBtn = btnIA.innerHTML;
        btnIA.innerHTML = '<div class="spinner-ia"></div>';
        btnIA.disabled = true;

        try {
            const donnees = {
                conversation_id: conversationId
            };

           
            if (action === 'reformulate') {
                donnees.draft_text = inputMessage.value.trim();
                if (!donnees.draft_text) {
                    afficherNotification('Écris d\'abord un message à reformuler', 'info');
                    return;
                }
            }

            const reponse = await axios.post(`/ai/${action}`, donnees);
            const resultat = reponse.data.result;

            if (action === 'recap') {
               
                afficherMessage({
                    id: 'ia-' + Date.now(),
                    body: 'Résumé IA :\n\n' + resultat,
                    type: 'ai_recap',
                    created_at: new Date().toISOString(),
                }, false);
                scrollerVersBas();

            } else if (action === 'suggest' || action === 'reformulate') {
                
                inputMessage.value = resultat;
                inputMessage.style.height = 'auto';
                inputMessage.style.height = Math.min(inputMessage.scrollHeight, 120) + 'px';
                inputMessage.focus();

                if (btnReformuler) {
                    btnReformuler.style.display = 'flex';
                }
            }

        } catch (erreur) {
            console.error('Erreur IA:', erreur);
            afficherNotification('Erreur lors de l\'appel IA', 'erreur');
        } finally {
           
            btnIA.innerHTML = texteOriginalBtn;
            btnIA.disabled = false;
        }
    }

    

    /**
     * Affiche une petite notification en bas de l'écran
     * @param {string} texte - Le message à afficher
     * @param {string} type - 'info', 'succes', ou 'erreur'
     */
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
            animation: apparaitre 0.2s ease;
            border: 1px solid var(--bordure);
        `;
        toast.textContent = texte;
        document.body.appendChild(toast);

        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    
    async function mettreAJourStatut() {
        try {
            await axios.post('/users/statut', { is_online: true });
        } catch (e) {
            
        }
    }

   
    if (window.utilisateurId) {
        mettreAJourStatut();
        setInterval(mettreAJourStatut, 30000);
    }

});