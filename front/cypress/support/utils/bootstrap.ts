export const injectNoAnimations = () => {
  cy.document().then((doc) => {
    const style = doc.createElement('style');
    style.setAttribute('data-cy', 'no-animations');
    style.innerHTML = `
      *, *::before, *::after { transition: none !important; animation: none !important; }
      html:focus-within { scroll-behavior: auto !important; }
    `;
    doc.head.appendChild(style);
  });
};

export const visitAndWait = (path: string) => {
  cy.intercept('GET', '/assets/config/runtime-config.json', { fixture: 'config/runtime-config.json' }).as('runtime');
  cy.visit(path);
  cy.wait('@runtime');
  injectNoAnimations();
};