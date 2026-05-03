// ============================================
// EnsiBeats 2.0 — JavaScript principal
// ============================================

'use strict';

/* ─── Toast ─── */
function toast(msg, type = 'success') {
  let el = document.getElementById('toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'toast';
    el.className = 'toast';
    document.body.appendChild(el);
  }
  el.textContent = msg;
  el.className = `toast ${type}`;
  el.offsetHeight;
  el.classList.add('show');
  clearTimeout(el._t);
  el._t = setTimeout(() => el.classList.remove('show'), 3200);
}

/* ─── Confetti ─── */
function launchConfetti(x, y) {
  const colors = ['#FF2D78','#2D6AFF','#FFD600','#00E5A0','#FF7BAB'];
  for (let i = 0; i < 14; i++) {
    const dot = document.createElement('div');
    dot.className = 'confetti-dot';
    dot.style.cssText = `
      left: ${x + (Math.random()-0.5)*80}px;
      top: ${y}px;
      background: ${colors[Math.floor(Math.random()*colors.length)]};
      width: ${4+Math.random()*8}px;
      height: ${4+Math.random()*8}px;
      animation-duration: ${1.5+Math.random()*2}s;
      animation-delay: ${Math.random()*0.3}s;
    `;
    document.body.appendChild(dot);
    setTimeout(() => dot.remove(), 3500);
  }
}

/* ─── Visualizer ─── */
function initVisualizer() {
  document.querySelectorAll('.visualizer').forEach(viz => {
    viz.innerHTML = '';
    for (let i = 0; i < 20; i++) {
      const bar = document.createElement('div');
      bar.className = 'viz-bar';
      const min = 3 + Math.random() * 6;
      const max = 14 + Math.random() * 26;
      bar.style.setProperty('--min', min + 'px');
      bar.style.setProperty('--max', max + 'px');
      bar.style.setProperty('--dur', (0.4 + Math.random() * 0.7) + 's');
      bar.style.animationDelay = (Math.random() * 0.5) + 's';
      viz.appendChild(bar);
    }
  });
}

/* ─── Floating Notes ─── */
function initFloatingNotes() {
  const wrap = document.querySelector('.floating-notes');
  if (!wrap) return;
  const notes = ['🎵','🎶','🎸','🎹','🎤','🎧','🥁','🎺'];
  for (let i = 0; i < 12; i++) {
    const n = document.createElement('span');
    n.className = 'note';
    n.textContent = notes[Math.floor(Math.random() * notes.length)];
    n.style.cssText = `
      left: ${Math.random()*100}%;
      top: ${Math.random()*100}%;
      --dur: ${4+Math.random()*6}s;
      --delay: ${-Math.random()*6}s;
      font-size: ${1+Math.random()*1.5}rem;
    `;
    wrap.appendChild(n);
  }
}

/* ─── Active Nav ─── */
function setActiveNav() {
  const page = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.nav-links a').forEach(a => {
    a.classList.toggle('active', a.getAttribute('href') === page);
  });
}

/* ─── Auth Modal ─── */
function initAuthModal() {
  const overlay = document.getElementById('auth-modal');
  if (!overlay) return;

  // Open
  document.querySelectorAll('[data-open-auth]').forEach(btn => {
    btn.addEventListener('click', () => {
      const tab = btn.dataset.openAuth || 'login';
      switchTab(tab);
      overlay.classList.add('open');
    });
  });

  // Close
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => overlay.classList.remove('open'));
  });

  // Tabs
  document.querySelectorAll('.modal-tab').forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
  });

  function switchTab(name) {
    document.querySelectorAll('.modal-tab').forEach(t =>
      t.classList.toggle('active', t.dataset.tab === name)
    );
    document.querySelectorAll('.auth-form').forEach(f =>
      f.classList.toggle('active', f.id === `form-${name}`)
    );
  }

  // Register
  const formRegister = document.getElementById('form-register');
  if (formRegister) {
    formRegister.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = formRegister.querySelector('button[type=submit]');
      btn.disabled = true; btn.textContent = '…';
      const fd = new FormData(formRegister);
      fd.append('action', 'register');
      const res  = await fetch('php/api.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.error) {
        toast(data.error, 'error');
        btn.disabled = false; btn.textContent = 'Créer mon compte';
      } else {
        launchConfetti(window.innerWidth/2, window.innerHeight/2);
        toast(`🎉 Bienvenue ${data.pseudo} !`, 'success');
        setTimeout(() => location.reload(), 1200);
      }
    });
  }

  // Login
  const formLogin = document.getElementById('form-login');
  if (formLogin) {
    formLogin.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = formLogin.querySelector('button[type=submit]');
      btn.disabled = true; btn.textContent = '…';
      const fd = new FormData(formLogin);
      fd.append('action', 'login');
      const res  = await fetch('php/api.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.error) {
        toast(data.error, 'error');
        btn.disabled = false; btn.textContent = 'Se connecter';
      } else {
        toast(`🎵 Bon retour ${data.pseudo} !`, 'success');
        setTimeout(() => location.reload(), 1000);
      }
    });
  }
}

/* ─── Logout ─── */
function initLogout() {
  document.querySelectorAll('[data-logout]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const fd = new FormData();
      fd.append('action', 'logout');
      await fetch('php/api.php', { method:'POST', body:fd });
      location.reload();
    });
  });
}

/* ─── Vote ─── */
function initVote() {
  document.querySelectorAll('.btn-vote').forEach(btn => {
    btn.addEventListener('click', async function () {
      const chansonId = this.dataset.id;
      if (!chansonId) return;
      this.disabled = true; this.textContent = '…';
      const fd = new FormData();
      fd.append('action', 'voter');
      fd.append('chanson_id', chansonId);
      const res  = await fetch('php/api.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.error) {
        toast(data.error, 'error');
        this.disabled = false; this.textContent = 'Voter';
      } else {
        launchConfetti(this.getBoundingClientRect().left, this.getBoundingClientRect().top);
        this.textContent = '✓ Voté !';
        this.classList.add('voted');
        toast('🎵 Vote enregistré ! +5 points', 'success');
        // Désactiver tous les boutons vote de la page
        document.querySelectorAll('.btn-vote').forEach(b => {
          b.disabled = true;
          if (!b.classList.contains('voted')) b.textContent = 'Fermé';
        });
      }
    });
  });
}

/* ─── Favoris ─── */
function initFavoris() {
  document.querySelectorAll('.btn-heart').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      e.stopPropagation();
      const chansonId = this.dataset.id;
      const fd = new FormData();
      fd.append('action', 'favori');
      fd.append('chanson_id', chansonId);
      const res  = await fetch('php/api.php', { method:'POST', body:fd });
      const data = await res.json();
      if (data.error) { toast(data.error, 'error'); return; }
      if (data.action === 'added') {
        this.textContent = '❤️';
        this.classList.add('active');
        toast('❤️ Ajouté aux favoris ! +2 points', 'success');
        launchConfetti(this.getBoundingClientRect().left, this.getBoundingClientRect().top);
      } else {
        this.textContent = '🤍';
        this.classList.remove('active');
        toast('💔 Retiré des favoris', 'success');
      }
    });
  });
}

/* ─── Mood Selector ─── */
function initMood() {
  document.querySelectorAll('.mood-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
      const humeur = this.dataset.mood;
      document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const fd = new FormData();
      fd.append('action', 'save_mood');
      fd.append('humeur', humeur);
      const res  = await fetch('php/api.php', { method:'POST', body:fd });
      const data = await res.json();

      if (data.error) { toast(data.error, 'error'); return; }
      toast('Mood enregistré 🎯', 'success');

      // Afficher les suggestions
      const container = document.getElementById('mood-suggestions');
      if (!container || !data.suggestions) return;

      container.innerHTML = '';
      if (data.suggestions.length === 0) {
        container.innerHTML = '<p class="text-muted">Aucune suggestion pour ce mood.</p>';
        return;
      }

      const grid = document.createElement('div');
      grid.className = 'suggest-grid';
      data.suggestions.forEach(s => {
        const card = document.createElement('div');
        card.className = 'suggest-card pop-in';
        card.innerHTML = `
          <div class="emoji">🎵</div>
          <h4>${escHtml(s.titre)}</h4>
          <p>${escHtml(s.artiste)}</p>
          <span class="tag" style="margin-top:6px">${escHtml(s.genre)}</span>
        `;
        grid.appendChild(card);
      });
      container.appendChild(grid);
      launchConfetti(window.innerWidth/2, window.innerHeight/3);
    });
  });
}

/* ─── Commentaires ─── */
function initCommentaires() {
  const form = document.getElementById('form-comment');
  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const input    = form.querySelector('input[name=contenu]');
    const contenu  = input.value.trim();
    const chansonId = form.dataset.chanson;
    if (!contenu) return;

    const fd = new FormData();
    fd.append('action', 'commenter');
    fd.append('chanson_id', chansonId);
    fd.append('contenu', contenu);

    const res  = await fetch('php/api.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.error) { toast(data.error, 'error'); return; }

    // Ajouter le commentaire en haut de la liste
    const list = document.getElementById('comments-list');
    if (list) {
      const item = document.createElement('div');
      item.className = 'comment-item slide-in';
      item.innerHTML = `
        <div class="avatar-sm" style="background:${data.avatar_couleur}">${escHtml(data.pseudo.slice(0,2).toUpperCase())}</div>
        <div>
          <div class="comment-meta"><strong>${escHtml(data.pseudo)}</strong> · À l'instant</div>
          <div class="comment-text">${escHtml(data.contenu)}</div>
        </div>
      `;
      list.prepend(item);
    }

    input.value = '';
    toast('💬 Commentaire ajouté ! +3 points', 'success');
  });
}

/* ─── Recherche live ─── */
function initSearch() {
  const input    = document.getElementById('search-input');
  const results  = document.getElementById('search-results');
  const songList = document.getElementById('song-list');
  if (!input || !results) return;

  let timer;

  input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();

    // Vide → remontre la liste principale
    if (q.length < 2) {
      results.innerHTML = '';
      if (songList) songList.style.display = '';
      return;
    }

    // Cache la liste principale
    if (songList) songList.style.display = 'none';

    timer = setTimeout(async () => {
      try {
        const res  = await fetch('php/api.php?action=recherche&q=' + encodeURIComponent(q));
        const data = await res.json();
        results.innerHTML = '';

        if (!data.length) {
          results.innerHTML = '<div class="empty-state"><p>Aucun résultat pour <strong>' + escHtml(q) + '</strong></p></div>';
          return;
        }

        data.forEach((s, i) => {
          const el = document.createElement('div');
          el.className = 'song-card fade-in';
          el.style.animationDelay = (i * 0.05) + 's';
          el.innerHTML =
            '<div class="song-rank">' + (i+1) + '</div>' +
            '<div class="song-disc">🎵</div>' +
            '<div class="song-info">' +
              '<div class="song-title">' + escHtml(s.titre) + '</div>' +
              '<div class="song-artist">' + escHtml(s.artiste) + '</div>' +
            '</div>' +
            '<div style="display:flex;align-items:center;gap:8px">' +
              '<span class="tag">' + escHtml(s.genre) + '</span>' +
              '<span style="font-size:0.8rem;color:var(--pink);font-weight:700">▲ ' + s.votes_total + '</span>' +
            '</div>';
          results.appendChild(el);
        });
      } catch(e) {
        results.innerHTML = '<div class="empty-state"><p>Erreur de recherche.</p></div>';
      }
    }, 250);
  });

  // Escape pour effacer
  input.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      input.value = '';
      results.innerHTML = '';
      if (songList) songList.style.display = '';
    }
  });
}

/* ─── Filtres ─── */
function initFilters() {
  document.querySelectorAll('.pill[data-filter]').forEach(pill => {
    pill.addEventListener('click', function () {
      document.querySelectorAll('.pill[data-filter]').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      const val = this.dataset.filter;
      const type = this.dataset.filterType || 'genre';

      document.querySelectorAll('.song-card[data-genre], .song-card[data-humeur]').forEach(card => {
        const match = val === 'tous' || card.dataset[type] === val;
        card.style.display = match ? '' : 'none';
      });

      // Renumber
      let rank = 1;
      document.querySelectorAll('.song-card').forEach(card => {
        if (card.style.display !== 'none') {
          const r = card.querySelector('.song-rank');
          if (r) r.textContent = rank++;
        }
      });
    });
  });
}

/* ─── Profil edit ─── */
function initProfileEdit() {
  const form = document.getElementById('form-profil');
  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(form);
    fd.append('action', 'update_profil');
    const res  = await fetch('php/api.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.error) { toast(data.error, 'error'); return; }
    toast('✅ Profil mis à jour !', 'success');
  });
}

/* ─── Util ─── */
function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ─── Init ─── */
document.addEventListener('DOMContentLoaded', () => {
  setActiveNav();
  initVisualizer();
  initFloatingNotes();
  initAuthModal();
  initLogout();
  initVote();
  initFavoris();
  initMood();
  initCommentaires();
  initSearch();
  initFilters();
  initProfileEdit();
});
