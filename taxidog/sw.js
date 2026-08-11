const CACHE_NAME = "taxi-dog-v63"; // Bump a versão sempre que mudar esta lista

// Só assets realmente estáticos aqui. Páginas que exigem login (frontend/home.php,
// frontend/schedule.php, frontend/earnings.php...) NÃO entram nessa lista:
// se o SW tentar pré-cachear e a sessão não estiver ativa, a página
// redireciona (302) pro index.php e o cache.addAll() guarda a resposta
// já redirecionada. Depois, ao navegar de verdade pra essa página, o
// Chrome recusa servir essa resposta cacheada (erro "a redirected
// response was used for a request whose redirect mode is not follow").
const urlsToCache = [
  "manifest.json",
  "app-icon.png",
  "styles/base.css",
  "styles/components.css",
  "js/app.js",
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)),
  );
  self.skipWaiting(); // Ativa a nova versão imediatamente, sem esperar todas as abas fecharem
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((cacheNames) =>
        Promise.all(
          cacheNames
            .filter((name) => name !== CACHE_NAME)
            .map((name) => caches.delete(name)),
        ),
      )
      .then(() => self.clients.claim()), // Assume o controle das abas abertas na hora
  );
});

self.addEventListener("fetch", (event) => {
  // POST/PUT etc nunca ficam no cache — sempre vão direto pra rede (login, agendamentos...)
  if (event.request.method !== "GET") {
    return;
  }

  // Navegações (abrir uma página) sempre vão direto pra rede. Evita
  // qualquer chance de servir HTML velho/quebrado de páginas dinâmicas.
  if (event.request.mode === "navigate") {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    caches.match(event.request).then((response) => response || fetch(event.request)),
  );
});
