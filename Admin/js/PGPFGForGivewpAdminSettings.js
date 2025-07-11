// Selecione todos os elementos com a classe desejada
var elements = document.querySelectorAll('.give-setting-tab-header.give-setting-tab-header-gateways');

// Para cada elemento selecionado
console.log(elements);
elements.forEach(function (element, index) {
    // Crie uma nova div e defina sua classe
    var divElement = document.createElement('div');
    divElement.className = 'PGPFGForGivewpAdminSettingsDiv';

    // Mova o conteúdo do elemento para a nova div
    divElement.innerHTML = element.innerHTML;

    // Substitua o elemento pelo novo div
    element.parentNode.replaceChild(divElement, element);

    // Selecione o elemento h2 dentro do novo div
    var h2Element = divElement.querySelector('h2');

    // Crie um novo elemento p e defina sua classe
    var pElement = document.createElement('p');
    pElement.className = 'PGPFGForGivewpAdminSettingsP';

    // Mova o conteúdo do h2 para o novo p
    pElement.innerHTML = h2Element.innerHTML;

    // Substitua o h2 pelo novo p
    h2Element.parentNode.replaceChild(pElement, h2Element);

    // Pegue a tabela que é o próximo elemento
    var tableElement = divElement.nextElementSibling;

    // Mova a tabela para dentro da nova div
    divElement.appendChild(tableElement);
});


const lknPaymentPixLogSettingLabel = document.querySelector('label[for="lkn-payment-pix-log-setting"]');
if (lknPaymentPixLogSettingLabel) {
    const link = document.createElement('a');
    link.setAttribute('target', '_blank');
    link.setAttribute('href', wpApiSettings.root.replace('/wp-json/', '/wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=logs'));
    link.innerHTML = pgpfgTranslations.seeLogs;
    lknPaymentPixLogSettingLabel.innerHTML += '<br>';
    lknPaymentPixLogSettingLabel.appendChild(link);
}



var thElements = document.querySelectorAll('.form-table.give-setting-tab-body.give-setting-tab-body-gateways th');
// Para cada elemento th
thElements.forEach(function (th) {
    th.parentElement.className = 'PGPFGForGivewpAdminSettingsTr';
    // Cria um novo elemento div com a classe tooltip
    var tooltip = document.createElement('div');
    tooltip.className = 'tooltip';

    // Cria o elemento span para o ícone de interrogação
    var questionIcon = document.createElement('span');
    questionIcon.textContent = '?';

    // Cria o elemento span para o texto da dica de ferramenta
    var tooltipText = document.createElement('span');
    tooltipText.className = 'tooltiptext';

    // Obtém o texto de descrição do campo correspondente
    var descriptionField = th.nextElementSibling.querySelector('.give-field-description');

    // Verifica se o campo de descrição contém um link
    if (descriptionField) {
        var linkElement = descriptionField.querySelector('a');

        // Se o campo de descrição contém um link, adiciona apenas o texto que não é parte do link ao texto da dica de ferramenta
        if (linkElement) {
            var linkText = linkElement.textContent;
            var descriptionText = descriptionField.textContent;
            var nonLinkText = descriptionText.replace(linkText, '').trim();
            tooltipText.textContent = nonLinkText;

            // Move o link para o elemento th
            var divElement = document.createElement('div');
            divElement.style.display = 'flex';
            divElement.style.flexDirection = 'column';
            divElement.appendChild(th.querySelector('label'));
            divElement.appendChild(linkElement);
            th.appendChild(divElement);
        } else {
            // Se o campo de descrição não contém um link, adiciona todo o texto de descrição ao texto da dica de ferramenta
            tooltipText.textContent = descriptionField.textContent;
        }

        // Remove o campo de descrição
        descriptionField.parentNode.removeChild(descriptionField);

        // Adiciona o ícone de interrogação e o texto da dica de ferramenta ao tooltip
        tooltip.appendChild(questionIcon);
        tooltip.appendChild(tooltipText);

        // Adiciona o tooltip ao elemento th
        th.appendChild(tooltip);

        // Define os estilos para o elemento th
        th.style.display = 'flex';
        th.style.justifyContent = 'space-between';

        // Verifica se o elemento tr contém um input do tipo radio
        var hasRadioInput = th.parentNode.querySelector('input[type="radio"]') !== null;
        // Se o elemento tr contém um input do tipo radio
        if (hasRadioInput) {
            // Adiciona o padding
            th.style.paddingTop = '32px';
        }
    }
});

if (!document.getElementById('lkn-payment-pix-license-setting')) {
    var divElement = document.querySelectorAll('.PGPFGForGivewpAdminSettingsDiv');

    /*if (divElement) {
        // Cria um novo elemento de imagem
        var img = document.createElement('img');
        img.src = window.location.origin + '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/ProSettings.svg';
        img.style.width = '100%';

        // Adiciona a imagem após o divElement
        divElement.parentNode.insertBefore(img, divElement.nextSibling);
    }*/
}
const lkn_menu = document.querySelectorAll('.lkn-pix-menu li')
const campos = document.querySelectorAll('.PGPFGForGivewpAdminSettingsDiv')

let antigo;
let atual = 0;
for (var i = 0; i < lkn_menu.length; i++) {
    lkn_menu[i].addEventListener("click", function (event) {
        clicou(event.target.id); // Passa o ID do elemento clicado
    });
}
for (var i = 1; i < campos.length; i++) {
    campos[i].classList.add('lkn-pix-configuracao-disable');
}

function clicou(i) {
    antigo = atual;
    lkn_menu[antigo].classList.remove('lkn-pix-menu-ativo');
    campos[antigo].classList.add('lkn-pix-configuracao-disable');

    atual = parseInt(i);
    lkn_menu[atual].classList.add('lkn-pix-menu-ativo');
    campos[atual].classList.remove('lkn-pix-configuracao-disable');
}

