/**
 * Testes dos filtros das listagens (console, controle, jogo e acessório),
 * clicando na tela como um usuário.
 *
 * Os dados vêm de cypress/fixtures/filtros.json — o mesmo arquivo que o
 * CypressSeeder usa pra montar o banco. O resultado esperado de cada
 * filtro é calculado a partir dele, então os testes acompanham os dados.
 *
 * Rodar:  npm run test:e2e        (sem janela)
 *         npm run cypress:abrir   (com a janela do Cypress)
 */

const fixture = require('../fixtures/filtros.json');
const POR_PAGINA = 10;

/** Todos os itens da tela, incluindo os "extras" */
function todosOsItens(tela) {
    const itens = [...tela.itens];
    const extras = tela.extras;
    for (let i = 1; i <= (extras ? extras.quantos : 0); i++) {
        itens.push({ nome: `${extras.prefixo} ${String(i).padStart(2, '0')}`, ...extras.atributos });
    }
    return itens;
}

/** Itens que devem aparecer com esses filtros ({ plataforma: 'xbox', ... }) */
function esperados(tela, filtros, termoNome = '') {
    return todosOsItens(tela)
        .filter((item) => Object.entries(filtros).every(([campo, valor]) => item[campo] === valor))
        .filter((item) => item.nome.toLowerCase().includes(termoNome.toLowerCase()))
        .map((item) => item.nome)
        .sort();
}

function abrirFiltro(tipo) {
    cy.visit(`/${tipo}`);
    cy.contains('a', 'Filtro').click();
    cy.contains('.card', 'Filtrar').should('be.visible');
}

function escolher(tela, filtros) {
    Object.entries(filtros).forEach(([campo, valor]) => {
        cy.get(`select[name="${tela.filtros[campo]}"]`).select(valor);
    });
}

function aplicar() {
    cy.contains('button', 'Aplicar filtros').click();
}

/** Confere a tabela: até 10 nomes na 1ª página e o total no "Mostrando ... de N" */
function conferirResultado(tela, nomes) {
    if (nomes.length === 0) {
        cy.contains(tela.vazio).should('be.visible');
        return;
    }

    cy.nomesNaTabela().then((mostrados) => {
        if (nomes.length <= POR_PAGINA) {
            expect([...mostrados].sort(), 'itens na tabela').to.deep.equal(nomes);
        } else {
            expect(mostrados, 'itens na 1ª página').to.have.length(POR_PAGINA);
            mostrados.forEach((nome) => expect(nomes, 'item mostrado é esperado').to.include(nome));
        }
    });

    if (nomes.length > POR_PAGINA) {
        cy.get('.paginacao').should('contain.text', `de ${nomes.length} resultados`);
    }
}

function badgeDoFiltro() {
    return cy.contains('a', 'Filtro').find('.badge');
}

Object.entries(fixture.telas).forEach(([tipo, tela]) => {
    describe(`Filtros de ${tipo}`, () => {
        beforeEach(() => cy.entrar());

        it('abre e fecha o painel de filtro', () => {
            abrirFiltro(tipo);
            cy.get('.card a.btn-close').click();
            cy.contains('.card', 'Filtrar').should('not.exist');
        });

        // um teste por filtro, passando por todos os valores que existem nos dados
        Object.keys(tela.filtros).forEach((campo) => {
            const valores = [...new Set(todosOsItens(tela).map((item) => item[campo]))];

            it(`filtra por ${campo} (${valores.join(', ')})`, () => {
                valores.forEach((valor) => {
                    abrirFiltro(tipo);
                    escolher(tela, { [campo]: valor });
                    aplicar();

                    conferirResultado(tela, esperados(tela, { [campo]: valor }));

                    // a opção escolhida continua marcada e o contador mostra 1 filtro
                    cy.get(`select[name="${tela.filtros[campo]}"] option:selected`).should(($opcao) => expect($opcao.text().trim()).to.eq(valor));
                    badgeDoFiltro().should('have.text', '1');
                });
            });
        });

        it(`busca por nome sem diferenciar maiúsculas (${tela.buscasNome.join(', ')})`, () => {
            tela.buscasNome.forEach((termo) => {
                abrirFiltro(tipo);
                cy.get('input[name="nome"]').clear().type(termo);
                aplicar();

                conferirResultado(tela, esperados(tela, {}, termo));
                cy.get('input[name="nome"]').should('have.value', termo);
            });
        });

        it(`combina filtros: ${JSON.stringify(tela.combinacao)}`, () => {
            abrirFiltro(tipo);
            escolher(tela, tela.combinacao);
            aplicar();

            conferirResultado(tela, esperados(tela, tela.combinacao));
            badgeDoFiltro().should('have.text', String(Object.keys(tela.combinacao).length));
        });

        it('"Limpar filtros" volta a mostrar tudo', () => {
            abrirFiltro(tipo);
            escolher(tela, tela.combinacao);
            aplicar();

            cy.contains('a', 'Limpar filtros').click();

            Object.values(tela.filtros).forEach((nome) => cy.get(`select[name="${nome}"]`).should('have.value', ''));
            cy.get('input[name="nome"]').should('have.value', '');
            badgeDoFiltro().should('not.exist');
            conferirResultado(tela, esperados(tela, {}));
        });

        it('fechar o painel mantém o filtro aplicado', () => {
            abrirFiltro(tipo);
            escolher(tela, tela.combinacao);
            aplicar();
            cy.get('.card a.btn-close').click();

            cy.contains('.card', 'Filtrar').should('not.exist');
            conferirResultado(tela, esperados(tela, tela.combinacao));
            badgeDoFiltro().should('have.text', String(Object.keys(tela.combinacao).length));
        });
    });
});

describe('Filtro junto com a paginação (jogos)', () => {
    const tela = fixture.telas.jogo;
    const filtro = { plataforma: tela.extras.atributos.plataforma }; // só os "Sonic Extra"
    const nomes = esperados(tela, filtro);

    beforeEach(() => {
        cy.entrar();
        abrirFiltro('jogo');
        escolher(tela, filtro);
        aplicar();
    });

    it('a página 2 continua filtrada', () => {
        cy.get('.pagination').contains('a', '2').click();

        cy.location('search').should('include', 'page=2').and('include', `${tela.filtros.plataforma}=`);
        cy.nomesNaTabela().then((mostrados) => {
            expect(mostrados).to.have.length(nomes.length - POR_PAGINA);
            mostrados.forEach((nome) => expect(nome).to.match(new RegExp(`^${tela.extras.prefixo}`)));
        });
    });

    it('"Itens por página" mantém o filtro', () => {
        cy.get('select[name="por_pagina"]').select('25');

        cy.nomesNaTabela().should('have.length', nomes.length);
        cy.get(`select[name="${tela.filtros.plataforma}"] option:selected`).should(($opcao) => expect($opcao.text().trim()).to.eq(filtro.plataforma));
    });

    it('"Ir para" uma página mantém o filtro', () => {
        cy.get('input[name="page"]').type('2');
        cy.contains('button', 'Ir').click();

        cy.nomesNaTabela().should('have.length', nomes.length - POR_PAGINA);
        cy.location('search').should('include', `${tela.filtros.plataforma}=`);
    });
});
