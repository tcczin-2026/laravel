// Comandos usados nos testes. Carregado antes de cada arquivo em cypress/e2e.

/** Entra no sistema pela tela de login (a sessão é reaproveitada entre os testes) */
Cypress.Commands.add('entrar', () => {
    cy.fixture('filtros').then(({ usuario }) => {
        cy.session(['login', usuario.email], () => {
            cy.visit('/login');
            cy.get('input[name="email"]').type(usuario.email);
            cy.get('input[name="password"]').type(usuario.senha, { log: false });
            cy.get('input[type="submit"]').click();
            cy.location('pathname').should('eq', '/colecao');
        });
    });
});

/** Nomes dos itens que aparecem na tabela da listagem */
Cypress.Commands.add('nomesNaTabela', () => {
    return cy.get('.tabela-lista tbody').then(($corpo) =>
        [...$corpo.find('td.col-nome .fw-semibold')].map((el) => el.innerText.trim())
    );
});
