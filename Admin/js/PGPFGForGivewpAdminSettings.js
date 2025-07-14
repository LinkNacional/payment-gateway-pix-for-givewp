// Selecione todos os elementos com a classe desejada
var elements = document.querySelectorAll('.give-setting-tab-header.give-setting-tab-header-gateways');

// Para cada elemento selecionado
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

        // Adiciona o tooltip ao TD novo, e o TD ao elemento th

        var tooltipCell = document.createElement('td');
        tooltipCell.appendChild(tooltip);

        //Classe de estilo
        tooltipCell.classList.add('tdTooltipCell')

        // Adiciona a nova célula ao tr pai
        th.parentElement.appendChild(tooltipCell);

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

const lkn_PGPFG_menu = document.querySelectorAll('.lkn-pix-menu li')
const lkn_PGPFG_settings = document.querySelectorAll('.PGPFGForGivewpAdminSettingsDiv')

let antigo;
let atual = 1;
for (var i = 0; i < lkn_PGPFG_menu.length; i++) {
    lkn_PGPFG_menu[i].addEventListener("click", function (event) {
        navegarParaAba(event.target.id); // Passa o ID do elemento clicado
    });
}
for (var i = 0; i < lkn_PGPFG_settings.length; i++) {
    if (i == 1) {
        i++
    }
    lkn_PGPFG_settings[i].classList.add('lkn-pix-configuracao-disable');
}
function navegarParaAba(idAba) {
    antigo = atual;
    lkn_PGPFG_menu[antigo].classList.remove('lkn-pix-menu-ativo');
    lkn_PGPFG_settings[antigo].classList.add('lkn-pix-configuracao-disable');

    atual = parseInt(idAba);
    lkn_PGPFG_menu[atual].classList.add('lkn-pix-menu-ativo');
    lkn_PGPFG_settings[atual].classList.remove('lkn-pix-configuracao-disable');

    if (!document.getElementById('lkn-payment-pix-license-setting')) {
        if (atual == 0) {
            document.querySelector('.lkn-label-pro').classList.add('lkn-pix-configuracao-disable')
        } else {
            document.querySelector('.lkn-label-pro').classList.remove('lkn-pix-configuracao-disable')
        }
    }
}

if (!document.getElementById('lkn-payment-pix-license-setting')) {
    for (let i = 1; lkn_PGPFG_settings.length; i++) {
        lkn_PGPFG_settings[i].classList.add('config-disable');
    }
}