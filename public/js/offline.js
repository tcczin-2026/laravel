/*
 * Modo offline da Coleção de Games (carregado pelo layouts/app.blade.php).
 *
 *  1. Mostra o aviso "Você está offline" quando não há conexão com o servidor.
 *  2. Formulários enviados sem conexão (cadastrar, editar, excluir, desfazer...)
 *     ficam guardados neste aparelho (IndexedDB) e o site segue funcionando.
 *  3. Quando a conexão volta, as alterações são enviadas ao servidor, na ordem
 *     em que foram feitas, e gravadas no banco (veja RespostaSincronizacao.php).
 *     Depois as páginas guardadas são baixadas de novo, com os dados atuais do
 *     banco — inclusive o que outras pessoas alteraram.
 *
 * No iPhone não existe sincronização em segundo plano: ela acontece quando o
 * site está aberto (ao abrir, ao voltar a conexão e a cada 30 segundos).
 */
(function () {
    'use strict';

    const meta = (nome) => document.querySelector(`meta[name="${nome}"]`)?.content || '';
    const BASE = meta('app-base');                   // ex: http://localhost/laravel/public/
    const USUARIO = Number(meta('usuario-id')) || null;
    const INTERVALO_VERIFICACAO = 30 * 1000;
    const INTERVALO_ATUALIZAR_PAGINAS = 10 * 60 * 1000;

    let online = navigator.onLine;
    let sincronizando = false;
    let ultimoResultado = null;                      // mensagem depois de sincronizar

    // ------------------------------------------------------------ IndexedDB

    const BANCO = 'colecao-offline';
    const FILA = 'fila';

    function abrirBanco() {
        return new Promise((ok, erro) => {
            const req = indexedDB.open(BANCO, 1);
            req.onupgradeneeded = () => req.result.createObjectStore(FILA, { keyPath: 'id' });
            req.onsuccess = () => ok(req.result);
            req.onerror = () => erro(req.error);
        });
    }

    async function operacao(modo, fn) {
        const db = await abrirBanco();
        return new Promise((ok, erro) => {
            const tx = db.transaction(FILA, modo);
            const resultado = fn(tx.objectStore(FILA));
            tx.oncomplete = () => ok(resultado?.result);
            tx.onerror = () => erro(tx.error);
        });
    }

    const filaListar = async () => ((await operacao('readonly', (s) => s.getAll())) || [])
        .sort((a, b) => a.criadoEm - b.criadoEm);
    const filaObter = (id) => operacao('readonly', (s) => s.get(id));
    const filaSalvar = (item) => operacao('readwrite', (s) => s.put(item));
    const filaRemover = (id) => operacao('readwrite', (s) => s.delete(id));
    const filaLimpar = () => operacao('readwrite', (s) => s.clear());

    function novoId() {
        return (crypto.randomUUID ? crypto.randomUUID()
            : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12));
    }

    // ------------------------------------------------------------ conexão

    async function verificarConexao() {
        if (!navigator.onLine) return definirOnline(false);

        const controle = new AbortController();
        const tempo = setTimeout(() => controle.abort(), 5000);
        try {
            const resp = await fetch(BASE + 'offline/ping', { cache: 'no-store', signal: controle.signal });
            definirOnline(resp.ok);
        } catch (e) {
            definirOnline(false);
        } finally {
            clearTimeout(tempo);
        }
    }

    function definirOnline(valor) {
        const voltou = valor && !online;
        online = valor;
        atualizarAviso();
        if (voltou) {
            atualizarTokens();
        }
        if (online) {
            sincronizar();
        }
    }

    // ------------------------------------------------------------ aviso no topo

    async function atualizarAviso() {
        const aviso = document.getElementById('aviso-conexao');
        if (!aviso) return;

        const fila = await filaListar().catch(() => []);
        const pendentes = fila.filter((i) => i.status === 'pendente').length;
        const comErro = fila.filter((i) => i.status === 'erro').length;

        let classe = '', texto = '';
        if (!online) {
            classe = 'offline';
            texto = '<strong>Você está offline.</strong> Pode continuar usando: as alterações ficam salvas neste aparelho e serão enviadas quando a conexão voltar.';
            if (pendentes) texto += ` <span class="aviso-contador">${pendentes} aguardando</span>`;
        } else if (sincronizando) {
            classe = 'sincronizando';
            texto = `Sincronizando ${pendentes} alteraç${pendentes === 1 ? 'ão' : 'ões'} com o servidor...`;
        } else if (comErro) {
            classe = 'erro';
            texto = `<strong>${comErro} alteraç${comErro === 1 ? 'ão não pôde' : 'ões não puderam'} ser enviada${comErro === 1 ? '' : 's'}.</strong>`;
        } else if (pendentes) {
            classe = 'sincronizando';
            texto = `${pendentes} alteraç${pendentes === 1 ? 'ão aguardando' : 'ões aguardando'} envio.`;
        } else if (ultimoResultado) {
            classe = 'ok';
            texto = ultimoResultado + ' <a href="" class="aviso-link">Atualizar página</a>';
        }

        if (fila.length) {
            texto += ' <button type="button" class="aviso-link" data-offline-ver>Ver alterações</button>';
        }

        aviso.className = 'aviso-conexao ' + classe;
        aviso.innerHTML = texto;
        aviso.hidden = !texto;
    }

    // ------------------------------------------------------------ formulários

    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (e.defaultPrevented || !(form instanceof HTMLFormElement)) return;

        const metodo = (form.getAttribute('method') || 'get').toLowerCase();
        if (metodo !== 'post') return; // filtros (GET) seguem normais; sem conexão, abre a cópia guardada

        const acao = form.action;

        if (ehLogout(acao)) {
            e.preventDefault();
            return sair(form);
        }

        // Formulário mostrando uma alteração guardada: substitui a da fila (e envia, se online)
        if (form.dataset.offlineItem) {
            e.preventDefault();
            return guardarFormulario(form, e.submitter);
        }

        if (online && navigator.onLine) return; // com conexão: envio normal

        e.preventDefault();
        await guardarFormulario(form, e.submitter);
    });

    function ehLogout(url) {
        return url.replace(/\/$/, '') === BASE + 'logout';
    }

    async function guardarFormulario(form, botao) {
        const dados = new FormData(form, botao || undefined);
        const campos = [];
        for (const [nome, valor] of dados.entries()) {
            if (valor instanceof File) {
                if (valor.size) campos.push([nome, { arquivo: valor, nome: valor.name, tipo: valor.type }]);
            } else {
                campos.push([nome, valor]);
            }
        }

        const metodo = (dados.get('_method') || 'POST').toUpperCase();
        const fila = await filaListar().catch(() => []);
        const anteriores = alteracoesAnteriores(fila, form, metodo);
        const anterior = anteriores[anteriores.length - 1] || null;

        // Imagem escolhida na versão anterior continua valendo se não escolheu outra
        if (anterior) {
            for (const [nome, valor] of anterior.campos) {
                if (valor && valor.arquivo && !campos.some(([n]) => n === nome)) campos.push([nome, valor]);
            }
        }

        const item = {
            id: novoId(),                                   // id novo: o servidor não confunde com a versão antiga
            substitui: anterior ? anterior.id : null,
            // cadastro editado mantém o lugar na fila; edição vai para o fim (é a mais recente)
            criadoEm: anterior && anterior.metodo === 'POST' ? anterior.criadoEm : Date.now(),
            usuario: USUARIO,
            url: form.action,
            pagina: location.href.split('#')[0],            // onde o formulário está (para editar depois)
            editavel: metodo !== 'DELETE' && !ehHistorico(form.action),
            metodo,
            descricao: descrever(form.action, dados),
            campos,
            status: 'pendente',
            erro: null,
        };

        try {
            await filaSalvar(item);
            for (const velho of anteriores) await filaRemover(velho.id);
        } catch (err) {
            avisar('Não foi possível guardar a alteração neste aparelho: ' + err, 'danger');
            return;
        }

        let msg = anterior
            ? `Alteração guardada atualizada: ${item.descricao}. Será enviada quando a conexão voltar.`
            : `Salvo offline: ${item.descricao}. Será enviado quando a conexão voltar.`;

        if (online && navigator.onLine) {
            await sincronizar();
            const restou = await filaObter(item.id).catch(() => null);
            if (!restou) msg = `Salvo: ${item.descricao}.`;
            else if (restou.status === 'erro') msg = `Não foi possível salvar ${item.descricao}: ${restou.erro}`;
            else msg = `Guardado: ${item.descricao}. Será enviado em instantes.`;
        }

        // Formulário de cadastro/edição: volta para a listagem (como faria online)
        const lista = urlDaListagem(form.action, item.metodo);
        if (lista && lista !== location.href.split(/[?#]/)[0]) {
            guardarMensagem(msg);
            location.href = lista;
            return;
        }

        avisar(msg, 'warning');
        marcarPendentes();
        atualizarAviso();
    }

    const NOMES = {
        console: 'console', controle: 'controle', jogo: 'jogo', acessorio: 'acessório',
        usuario: 'usuário', auxiliar: 'registro', historico: 'histórico',
    };

    /** "Cadastrar console: Mega Drive", "Editar jogo #12", "Excluir acessório #3" */
    function descrever(url, dados) {
        const partes = url.slice(BASE.length).split(/[?#]/)[0].split('/').filter(Boolean);
        const tipo = NOMES[partes[0]] || partes[0] || 'item';
        const id = partes.find((p) => /^\d+$/.test(p));
        const nome = dados.get('nome') || dados.get('name');
        const metodo = (dados.get('_method') || 'POST').toUpperCase();

        if (partes[0] === 'historico') return `${partes[3] === 'restaurar' ? 'Restaurar' : 'Desfazer'} alteração no ${partes[1]}`;
        if (metodo === 'DELETE') return `Excluir ${tipo} #${id}`;
        if (metodo === 'PUT' || metodo === 'PATCH') return `Editar ${tipo} #${id}` + (nome ? ` (${nome})` : '');
        return `Cadastrar ${tipo}` + (nome ? `: ${nome}` : '');
    }

    /** /console/5 -> /console   |   /auxiliar/cor/3 -> /auxiliar/cor   |   exclusão e histórico: fica */
    function urlDaListagem(url, metodo) {
        if (metodo === 'DELETE') return null;
        const partes = url.slice(BASE.length).split(/[?#]/)[0].split('/').filter(Boolean);
        if (!partes.length || partes[0] === 'historico') return null;
        while (partes.length && /^\d+$/.test(partes[partes.length - 1])) partes.pop();
        return BASE + partes.join('/');
    }

    function ehHistorico(url) {
        return url.slice(BASE.length).startsWith('historico/');
    }

    /**
     * Alterações da fila que este envio substitui:
     *  - a que o formulário está mostrando (aberta pelo "Editar" da lista de alterações), ou
     *  - edições ainda não enviadas do mesmo item (editar duas vezes offline = uma alteração só).
     */
    function alteracoesAnteriores(fila, form, metodo) {
        const minhas = fila.filter((i) => i.usuario === USUARIO);
        const aberta = form.dataset.offlineItem && minhas.find((i) => i.id === form.dataset.offlineItem);
        const mesmas = (metodo === 'PUT' || metodo === 'PATCH')
            ? minhas.filter((i) => i.url === form.action && i.metodo === metodo)
            : [];
        const todas = aberta ? [aberta, ...mesmas.filter((i) => i.id !== aberta.id)] : mesmas;
        return todas.sort((a, b) => a.criadoEm - b.criadoEm);
    }

    // ------------------------------------------------------------ editar alteração guardada

    /**
     * Ao abrir um formulário de edição que tem alteração guardada neste aparelho
     * (ou "#offline-editar=<id>" vindo da lista de alterações), o formulário
     * mostra a versão guardada, e não a cópia antiga do servidor.
     */
    async function prepararEdicao() {
        const fila = (await filaListar().catch(() => [])).filter((i) => i.usuario === USUARIO && i.editavel !== false);
        if (!fila.length) return;

        const forms = [...document.querySelectorAll('form[method="post"]')];
        const pedido = location.hash.match(/^#offline-editar=([\w-]+)$/);
        let item = null, form = null;

        if (pedido) {
            item = fila.find((i) => i.id === pedido[1]) || null;
            form = item && forms.find((f) => f.action === item.url && metodoDoForm(f) === item.metodo);
        }
        if (!form) {
            for (const f of forms) {
                const metodo = metodoDoForm(f);
                if (metodo !== 'PUT' && metodo !== 'PATCH') continue;
                const it = fila.filter((i) => i.url === f.action && i.metodo === metodo).pop();
                if (it) { item = it; form = f; break; }
            }
        }
        if (!item || !form || form.dataset.offlineItem === item.id) return;

        preencher(form, item.campos);
        form.dataset.offlineItem = item.id;
        avisoNoFormulario(form, item);
    }

    function metodoDoForm(form) {
        return (form.querySelector('input[name="_method"]')?.value || 'POST').toUpperCase();
    }

    function preencher(form, campos) {
        const valores = new Map();
        for (const [nome, valor] of campos) {
            if (!valores.has(nome)) valores.set(nome, []);
            valores.get(nome).push(valor);
        }

        const usados = new Map();
        for (const el of form.elements) {
            const nome = el.name;
            if (!nome || nome === '_token' || nome === '_method' || el.disabled) continue;
            if (['submit', 'button', 'reset'].includes(el.type)) continue;
            const lista = valores.get(nome) || [];

            if (el.type === 'file') {
                const arq = lista.find((v) => v && v.arquivo);
                if (arq) mostrarArquivoGuardado(el, arq.nome);
            } else if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = lista.includes(el.value);
            } else if (el.tagName === 'SELECT' && el.multiple) {
                for (const op of el.options) op.selected = lista.includes(op.value);
            } else if (valores.has(nome)) {
                const i = usados.get(nome) || 0;            // campos repetidos (nome[]) na ordem
                if (i < lista.length) el.value = lista[i];
                usados.set(nome, i + 1);
            }
        }
    }

    function mostrarArquivoGuardado(input, nomeArquivo) {
        if (input.parentElement.querySelector('[data-offline-arquivo]')) return;
        const nota = document.createElement('div');
        nota.className = 'small text-warning mt-1';
        nota.dataset.offlineArquivo = '';
        nota.textContent = `Imagem guardada neste aparelho: ${nomeArquivo}. Escolha outra só se quiser trocar.`;
        input.after(nota);
    }

    function avisoNoFormulario(form, item) {
        form.parentElement.querySelector('[data-offline-editando]')?.remove();

        const div = document.createElement('div');
        div.className = 'alert alert-warning d-flex flex-wrap align-items-center gap-2';
        div.dataset.offlineEditando = '';

        const texto = document.createElement('span');
        texto.className = 'me-auto';
        texto.textContent = item.status === 'erro'
            ? `Esta alteração guardada não pôde ser enviada (${item.erro}). Corrija e salve de novo.`
            : 'Este formulário mostra a sua alteração guardada neste aparelho, que ainda não foi enviada ao servidor. Ao salvar, ela é substituída.';

        const descartar = botaoAcao('Descartar alteração', 'btn-outline-light', async () => {
            await filaRemover(item.id);
            location.replace(location.href.split('#')[0]);
        });

        div.append(texto, descartar);
        form.before(div);
    }

    window.addEventListener('hashchange', prepararEdicao);

    // ------------------------------------------------------------ sincronização

    async function sincronizar() {
        if (sincronizando || !online) return;

        const pendentes = (await filaListar().catch(() => [])).filter((i) => i.status === 'pendente');
        if (!pendentes.length) return;

        // Vários abas abertas: só uma envia (o servidor também ignora repetidos)
        if (navigator.locks) {
            return navigator.locks.request('colecao-sincronizar', { ifAvailable: true }, (lock) => lock && enviar(pendentes));
        }
        return enviar(pendentes);
    }

    async function enviar(pendentes) {
        sincronizando = true;
        atualizarAviso();

        let enviados = 0;
        try {
            let sessao = await buscarToken();
            if (!sessao) return;

            for (const daLista of pendentes) {
                // pode ter sido editada ou descartada enquanto as anteriores eram enviadas
                const item = await filaObter(daLista.id).catch(() => null);
                if (!item || item.status !== 'pendente') continue;

                if (item.usuario && item.usuario !== sessao.usuario) {
                    await marcarErro(item, 'Feita por outro usuário. Entre com a conta dele para enviar.');
                    continue;
                }

                let resp = await reenviar(item, sessao.token);
                if (resp === null) break;                         // caiu a conexão: tenta depois

                if (resp.status === 419) {                        // token velho: pega outro e tenta de novo
                    sessao = await buscarToken();
                    if (!sessao) break;
                    resp = await reenviar(item, sessao.token);
                    if (resp === null) break;
                }

                const corpo = await resp.json().catch(() => ({}));

                if (resp.ok) {
                    await filaRemover(item.id);
                    enviados++;

                    // Cadastro editado durante o envio: o original já foi gravado, a edição
                    // não pode virar um segundo cadastro.
                    const nova = (await filaListar().catch(() => [])).find((i) => i.substitui === item.id);
                    if (nova && nova.metodo === 'POST') {
                        await marcarErro({ ...nova, editavel: false },
                            'O cadastro foi enviado antes de você terminar de editar. Edite o item pela listagem.');
                    }
                } else if (resp.status === 401) {
                    avisar('Sua sessão expirou. Entre de novo para enviar as alterações guardadas.', 'warning');
                    break;
                } else if (resp.status === 422) {
                    await marcarErro(item, Object.values(corpo.errors || {}).flat().join(' ') || corpo.message);
                } else if (resp.status === 404) {
                    await marcarErro(item, 'O registro não existe mais no servidor.');
                } else if (resp.status === 409 || resp.status === 403) {
                    await marcarErro(item, corpo.mensagem || corpo.message || 'O servidor recusou a alteração.');
                } else {
                    break;                                        // erro no servidor: tenta depois
                }
            }
        } finally {
            sincronizando = false;
            if (enviados) {
                ultimoResultado = `${enviados} alteraç${enviados === 1 ? 'ão sincronizada' : 'ões sincronizadas'} com o servidor.`;
                atualizarPaginasGuardadas(true);
            }
            atualizarAviso();
            marcarPendentes();
        }
    }

    async function buscarToken() {
        try {
            const resp = await fetch(BASE + 'offline/token', {
                headers: { Accept: 'application/json' }, cache: 'no-store', credentials: 'same-origin',
            });
            if (resp.status === 401) {
                avisar('Sua sessão expirou. Entre de novo para enviar as alterações guardadas.', 'warning');
                return null;
            }
            return resp.ok ? await resp.json() : null;
        } catch (e) {
            definirOnline(false);
            return null;
        }
    }

    /** Envia para a mesma rota do formulário. null = sem conexão */
    async function reenviar(item, token) {
        const corpo = new FormData();
        for (const [nome, valor] of item.campos) {
            if (nome === '_token') continue;
            if (valor && valor.arquivo) corpo.append(nome, valor.arquivo, valor.nome);
            else corpo.append(nome, valor);
        }
        corpo.append('_token', token);

        try {
            return await fetch(item.url, {
                method: 'POST',                                   // PUT/DELETE vão no campo _method
                body: corpo,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Offline-Sync': '1',
                    'X-Offline-Id': item.id,
                    'X-CSRF-TOKEN': token,
                },
            });
        } catch (e) {
            definirOnline(false);
            return null;
        }
    }

    function marcarErro(item, mensagem) {
        return filaSalvar({ ...item, status: 'erro', erro: mensagem || 'Erro desconhecido.' });
    }

    /** Depois de ficar offline a página pode ter um token CSRF velho */
    async function atualizarTokens() {
        if (!USUARIO) return;
        const sessao = await buscarToken();
        if (!sessao) return;
        document.querySelectorAll('input[name="_token"]').forEach((i) => { i.value = sessao.token; });
        document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', sessao.token);
    }

    // ------------------------------------------------------------ páginas guardadas

    function lerLocal(chave) {
        try { return localStorage.getItem(chave); } catch (e) { return null; }
    }

    function gravarLocal(chave, valor) {
        try { localStorage.setItem(chave, valor); } catch (e) { /* modo privado */ }
    }

    /** Baixa de novo as páginas com os dados atuais do banco (servidor -> aparelho) */
    async function atualizarPaginasGuardadas(forcar) {
        if (!online || !USUARIO || !navigator.serviceWorker?.controller) return;

        const ultima = Number(lerLocal('offline-paginas-em')) || 0;
        const doUsuario = Number(lerLocal('offline-paginas-usuario')) === USUARIO;
        if (!forcar && doUsuario && Date.now() - ultima < INTERVALO_ATUALIZAR_PAGINAS) return;

        try {
            const resp = await fetch(BASE + 'offline/paginas', { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!resp.ok) return;
            const dados = await resp.json();

            if (!doUsuario) await mensagemSw({ tipo: 'limpar' }, 'limpo'); // páginas de outro usuário

            gravarLocal('offline-paginas-em', String(Date.now()));
            gravarLocal('offline-paginas-usuario', String(dados.usuario));
            navigator.serviceWorker.controller.postMessage({ tipo: 'guardar', urls: dados.urls });
        } catch (e) { /* tenta na próxima */ }
    }

    function mensagemSw(msg, respostaEsperada) {
        return new Promise((ok) => {
            const sw = navigator.serviceWorker?.controller;
            if (!sw) return ok();
            const ouvir = (e) => {
                if (e.data?.tipo === respostaEsperada) {
                    navigator.serviceWorker.removeEventListener('message', ouvir);
                    ok();
                }
            };
            navigator.serviceWorker.addEventListener('message', ouvir);
            sw.postMessage(msg);
            setTimeout(ok, 3000);
        });
    }

    // ------------------------------------------------------------ sair

    async function sair(form) {
        if (!online) {
            avisar('Para sair do sistema é preciso estar com conexão.', 'warning');
            return;
        }

        const fila = await filaListar().catch(() => []);
        if (fila.length) {
            await sincronizar();
            const restantes = await filaListar().catch(() => []);
            if (restantes.length) {
                const confirmou = await confirmar(
                    `Há ${restantes.length} alteraç${restantes.length === 1 ? 'ão' : 'ões'} neste aparelho que não ${restantes.length === 1 ? 'foi enviada' : 'foram enviadas'} ao servidor. Se sair agora, ${restantes.length === 1 ? 'ela será perdida' : 'elas serão perdidas'}.`,
                    'Sair e descartar'
                );
                if (!confirmou) return;
            }
        }

        // Ninguém que use o aparelho depois vê as páginas guardadas deste usuário
        await filaLimpar().catch(() => null);
        await mensagemSw({ tipo: 'limpar' }, 'limpo');
        gravarLocal('offline-paginas-em', '0');
        form.submit();
    }

    // ------------------------------------------------------------ janelas e mensagens

    function avisar(texto, tipo) {
        const area = document.querySelector('.app-main');
        if (!area) return;
        const div = document.createElement('div');
        div.className = `alert alert-${tipo} alert-dismissible`;
        div.textContent = texto;
        const fechar = document.createElement('button');
        fechar.type = 'button';
        fechar.className = 'btn-close';
        fechar.setAttribute('data-bs-dismiss', 'alert');
        fechar.setAttribute('aria-label', 'Fechar');
        div.appendChild(fechar);
        area.prepend(div);
        div.scrollIntoView({ block: 'nearest' });
    }

    function guardarMensagem(texto) {
        try { sessionStorage.setItem('offline-mensagem', texto); } catch (e) { /* ignora */ }
    }

    function mostrarMensagemGuardada() {
        try {
            const texto = sessionStorage.getItem('offline-mensagem');
            if (texto) {
                sessionStorage.removeItem('offline-mensagem');
                avisar(texto, 'warning');
            }
        } catch (e) { /* ignora */ }
    }

    function modal(id) {
        const el = document.getElementById(id);
        return el && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(el) : null;
    }

    function confirmar(texto, rotuloBotao) {
        return new Promise((ok) => {
            const el = document.getElementById('modalOfflineConfirmar');
            const janela = modal('modalOfflineConfirmar');
            if (!janela) return ok(false);

            el.querySelector('[data-texto]').textContent = texto;
            const botao = el.querySelector('[data-confirmar]');
            botao.textContent = rotuloBotao;

            let resposta = false;
            botao.onclick = () => { resposta = true; janela.hide(); };
            el.addEventListener('hidden.bs.modal', () => ok(resposta), { once: true });
            janela.show();
        });
    }

    async function mostrarFila() {
        const el = document.getElementById('modalOfflineFila');
        const janela = modal('modalOfflineFila');
        if (!janela) return;

        const lista = el.querySelector('[data-lista]');
        const fila = await filaListar().catch(() => []);
        lista.innerHTML = '';

        if (!fila.length) {
            lista.innerHTML = '<p class="text-muted mb-0">Nenhuma alteração guardada neste aparelho.</p>';
        }

        for (const item of fila) {
            const linha = document.createElement('div');
            linha.className = 'offline-item ' + item.status;

            const info = document.createElement('div');
            const titulo = document.createElement('strong');
            titulo.textContent = item.descricao;
            const detalhe = document.createElement('small');
            detalhe.textContent = new Date(item.criadoEm).toLocaleString('pt-BR')
                + (item.status === 'erro' ? ' — ' + item.erro : ' — aguardando envio');
            info.append(titulo, detalhe);

            const acoes = document.createElement('div');
            acoes.className = 'acoes';
            if (item.pagina && item.editavel && item.usuario === USUARIO) {
                acoes.appendChild(botaoAcao('Editar', 'btn-info', () => {
                    janela.hide();
                    location.href = item.pagina + '#offline-editar=' + item.id;
                }));
            }
            if (item.status === 'erro') {
                acoes.appendChild(botaoAcao('Tentar de novo', 'btn-warning', async () => {
                    await filaSalvar({ ...item, status: 'pendente', erro: null });
                    await sincronizar();
                    mostrarFila();
                }));
            }
            acoes.appendChild(botaoAcao('Descartar', 'btn-danger', async () => {
                await filaRemover(item.id);
                atualizarAviso();
                marcarPendentes();
                mostrarFila();
            }));

            linha.append(info, acoes);
            lista.appendChild(linha);
        }

        janela.show();
    }

    function botaoAcao(texto, classe, fn) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn btn-sm ' + classe;
        b.textContent = texto;
        b.onclick = fn;
        return b;
    }

    /** Linhas com exclusão guardada aparecem apagadas; com edição guardada, marcadas */
    async function marcarPendentes() {
        const fila = await filaListar().catch(() => []);
        const excluir = new Set(fila.filter((i) => i.metodo === 'DELETE').map((i) => i.url));
        const editar = new Set(fila.filter((i) => i.metodo === 'PUT' || i.metodo === 'PATCH').map((i) => i.url));
        document.querySelectorAll('form[method="post"]').forEach((f) => {
            const linha = f.closest('tr');
            if (!linha) return;
            linha.classList.toggle('offline-exclusao', excluir.has(f.action));
            linha.classList.toggle('offline-edicao', editar.has(f.action) && !excluir.has(f.action));
        });
    }

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-offline-ver]')) mostrarFila();
    });

    // ------------------------------------------------------------ início

    window.addEventListener('online', verificarConexao);
    window.addEventListener('offline', () => definirOnline(false));
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') verificarConexao();
    });
    setInterval(verificarConexao, INTERVALO_VERIFICACAO);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(BASE + 'sw.js', { scope: BASE }).catch(() => null);
        navigator.serviceWorker.ready.then(() => atualizarPaginasGuardadas(false));
        // primeira instalação: o service worker só assume a página um instante depois
        navigator.serviceWorker.addEventListener('controllerchange', () => atualizarPaginasGuardadas(false));
    }

    mostrarMensagemGuardada();
    prepararEdicao();
    marcarPendentes();
    atualizarAviso();
    verificarConexao();
})();
