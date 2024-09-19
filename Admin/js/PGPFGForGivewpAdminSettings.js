// Selecione todos os elementos com a classe desejada
var elements = document.querySelectorAll('.give-setting-tab-header.give-setting-tab-header-gateways');

// Para cada elemento selecionado
elements.forEach(function(element, index) {
    // Crie uma nova div e defina seu ID
    var divElement = document.createElement('div');
    divElement.id = 'PGPFGForGivewpAdminSettingsDiv';

    // Mova o conteúdo do elemento para a nova div
    divElement.innerHTML = element.innerHTML;

    // Substitua o elemento pelo novo div
    element.parentNode.replaceChild(divElement, element);

    // Selecione o elemento h2 dentro do novo div
    var h2Element = divElement.querySelector('h2');

    // Crie um novo elemento p e defina seu ID
    var pElement = document.createElement('p');
    pElement.id = 'PGPFGForGivewpAdminSettingsP';

    // Mova o conteúdo do h2 para o novo p
    pElement.innerHTML = h2Element.innerHTML;

    // Substitua o h2 pelo novo p
    h2Element.parentNode.replaceChild(pElement, h2Element);

    // Pegue a tabela que é o próximo elemento
    var tableElement = divElement.nextElementSibling;

    // Mova a tabela para dentro da nova div
    divElement.appendChild(tableElement);
});