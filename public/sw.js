/*
 * Service worker da Coleção de Games: faz o site abrir sem internet.
 *
 * - Páginas (HTML): busca no servidor primeiro e guarda uma cópia. Sem
 *   conexão, mostra a cópia guardada (ou offline.html se ela não existir).
 * - CSS, JS, imagens e Bootstrap: usa a cópia guardada e atualiza por trás.
 * - Formulários (POST) não passam por aqui: quem guarda as alterações feitas
 *   offline e envia depois é o public/js/offline.js.
 *
 * Ao mudar este arquivo, suba a VERSAO para os aparelhos pegarem a nova.
 */
const VERSAO = 'v2';
const CACHE_PAGINAS = 'colecao-paginas';           // muda com o usuário, não com a versão
const CACHE_ESTATICOS = 'colecao-estaticos-' + VERSAO;

const BASE = new URL('./', self.location).href;  // ex: http://localhost/laravel/public/
const OFFLINE_URL = BASE + 'offline.html';

const ESTATICOS = [
    OFFLINE_URL,
    BASE + 'css/theme.css',
    BASE + 'js/offline.js',
    BASE + 'manifest.webmanifest',
    BASE + 'icons/icon-192.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
];

// Páginas que nunca são guardadas (dependem de estar online ou de não estar logado)
const NAO_GUARDAR = ['offline/', 'login', 'logout', 'esqueci-senha', 'redefinir-senha'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_ESTATICOS)
            .then((cache) => Promise.all(ESTATICOS.map((url) =>
                cache.add(new Request(url, { mode: url.startsWith(BASE) ? 'same-origin' : 'cors' }))
                    .catch(() => null) // um arquivo faltando não impede a instalação
            )))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((nomes) => Promise.all(nomes
                .filter((n) => n.startsWith('colecao-estaticos-') && n !== CACHE_ESTATICOS)
                .map((n) => caches.delete(n))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    const mesmoSite = url.href.startsWith(BASE);

    if (mesmoSite && caminho(url).startsWith('offline/')) return; // ping/token: sempre na rede

    if (req.mode === 'navigate' && mesmoSite) {
        event.respondWith(pagina(req));
    } else if (mesmoSite || url.hostname === 'cdn.jsdelivr.net') {
        event.respondWith(estatico(req, event));
    }
});

/** Rede primeiro; se falhar, cópia guardada; se não tiver, offline.html */
async function pagina(req) {
    try {
        const resp = await fetch(req);
        if (podeGuardar(req, resp)) {
            const cache = await caches.open(CACHE_PAGINAS);
            await cache.put(req.url, await copiaLimpa(resp.clone()));
        }
        return resp;
    } catch (e) {
        const guardada = await caches.match(req.url, { cacheName: CACHE_PAGINAS });
        return guardada || (await paginaOffline());
    }
}

/** offline.html com <base> para os links funcionarem em qualquer endereço */
async function paginaOffline() {
    const resp = await caches.match(OFFLINE_URL);
    if (!resp) return Response.error();
    const html = (await resp.text()).replace('<!--BASE-->', `<base href="${BASE}">`);
    return new Response(html, { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
}

/** Cópia guardada na hora; atualiza por trás quando há rede */
async function estatico(req, event) {
    const cache = await caches.open(CACHE_ESTATICOS);
    const guardada = await cache.match(req, { ignoreVary: true });
    const daRede = fetch(req).then((resp) => {
        if (resp.ok || resp.type === 'opaque') cache.put(req, resp.clone());
        return resp;
    });

    if (guardada) {
        event.waitUntil(daRede.catch(() => null));
        return guardada;
    }
    return daRede;
}

function caminho(url) {
    return url.href.slice(BASE.length).split(/[?#]/)[0];
}

function podeGuardar(req, resp) {
    if (!resp.ok || !(resp.headers.get('content-type') || '').includes('text/html')) return false;
    const final = new URL(resp.url || req.url);
    // redirecionou para o login (sessão expirou)? então não é a página pedida
    return ![caminho(new URL(req.url)), caminho(final)].some((c) => NAO_GUARDAR.some((n) => c.startsWith(n)));
}

/** Navegador recusa servir uma resposta "redirecionada" do cache; copia sem a marca */
async function copiaLimpa(resp) {
    if (!resp.redirected) return resp;
    return new Response(await resp.blob(), { status: resp.status, statusText: resp.statusText, headers: resp.headers });
}

/*
 * Mensagens da página:
 *   {tipo: 'guardar', urls: [...]}   baixa e guarda essas páginas (dados atuais do banco)
 *   {tipo: 'limpar'}                 apaga as páginas guardadas (troca de usuário / logout)
 */
self.addEventListener('message', (event) => {
    const msg = event.data || {};
    if (msg.tipo === 'guardar' && Array.isArray(msg.urls)) {
        event.waitUntil(guardarPaginas(msg.urls, event.source));
    } else if (msg.tipo === 'limpar') {
        event.waitUntil(caches.delete(CACHE_PAGINAS).then(() => responder(event.source, { tipo: 'limpo' })));
    }
});

async function guardarPaginas(urls, cliente) {
    const cache = await caches.open(CACHE_PAGINAS);
    const fila = urls.filter((u) => typeof u === 'string' && u.startsWith(BASE));
    let feitas = 0, falhas = 0;

    // 4 downloads por vez, para não travar o servidor nem o celular
    const trabalhador = async () => {
        while (fila.length) {
            const url = fila.shift();
            try {
                const req = new Request(url, { credentials: 'same-origin' });
                const resp = await fetch(req);
                if (podeGuardar(req, resp)) {
                    await cache.put(url, await copiaLimpa(resp));
                    feitas++;
                } else {
                    falhas++;
                }
            } catch (e) {
                falhas++;
            }
        }
    };
    await Promise.all([trabalhador(), trabalhador(), trabalhador(), trabalhador()]);

    // páginas que não estão mais na lista (itens excluídos) saem do cache
    const manter = new Set(urls);
    for (const req of await cache.keys()) {
        if (!manter.has(req.url) && !new URL(req.url).search.includes('page=')) {
            const c = caminho(new URL(req.url));
            if (/\/\d+(\/editar|\/edit)?$/.test(c)) await cache.delete(req);
        }
    }

    responder(cliente, { tipo: 'guardado', feitas, falhas });
}

function responder(cliente, msg) {
    if (cliente) cliente.postMessage(msg);
}
