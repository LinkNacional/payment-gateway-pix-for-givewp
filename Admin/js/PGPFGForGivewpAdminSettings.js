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
    // Cria o elemento span para o texto da dica de ferramenta
    var tooltipText = document.createElement('span');
    tooltipText.className = 'tooltiptext';

    // Obtém o ID do campo para buscar a nova_desc
    var fieldId = null;
    var inputField = th.nextElementSibling.querySelector('input, select, textarea, fieldset');
    if (inputField) {
        // Verifica se é um fieldset
        if (inputField.tagName.toLowerCase() === 'fieldset') {
            // Se for fieldset, busca o primeiro input dentro dele
            var innerInput = inputField.querySelector('input');
            if (innerInput) {
                // Para campos radio, geralmente usamos o 'name' como ID
                fieldId = innerInput.getAttribute('name') || innerInput.getAttribute('id');
            }
        } else {
            // Se não for fieldset, pega o ID normalmente
            fieldId = inputField.getAttribute('id');
        }
    }
    // Obtém o texto de descrição do campo correspondente
    var descriptionField = th.nextElementSibling.querySelector('.give-field-description');

    let subtitle = pgpfgTranslations.subtitle[fieldId] ?? null;
    // Verifica se o campo de descrição contém um link
    if (descriptionField) {
        var linkElement = descriptionField.querySelector('a');

        let p = document.createElement('p');
        p.innerHTML = descriptionField.innerHTML.replace(/\s+<a/g, '<a');;
        th.appendChild(p)
        //p.innerHTML = p.innerHTML + novaDesc;
        // Remove o campo de descrição
        descriptionField.parentNode.removeChild(descriptionField);

    }
    if (subtitle) {
        let p = document.createElement('p');
        p.innerHTML = subtitle;
        p.classList.add('lkn-pix-subtitle');
        let td = th.parentElement.querySelector('td');
        td.appendChild(p);
        console.log(subtitle)
    }
});

//Configurações iniciais do menu
const lkn_PGPFG_menu = document.querySelectorAll('.lkn-pix-menu li')
const lkn_PGPFG_settings = document.querySelectorAll('.PGPFGForGivewpAdminSettingsDiv')

let antigo;
let atual = 0;

for (var i = 0; i < lkn_PGPFG_menu.length; i++) {
    lkn_PGPFG_menu[i].addEventListener("click", function (event) {
        navegarParaAba(event.target.id); // Passa o ID do elemento clicado
    });
}

//Desabilita os divs responsaveis pelas settings, menos a primeira.
for (var i = 1; i < lkn_PGPFG_settings.length; i++) {
    lkn_PGPFG_settings[i].classList.add('lkn-pix-configuracao-disable');
}

function navegarParaAba(idAba) {
    antigo = atual;
    lkn_PGPFG_menu[antigo].classList.remove('lkn-pix-menu-ativo');
    lkn_PGPFG_settings[antigo].classList.add('lkn-pix-configuracao-disable');

    atual = parseInt(idAba);
    lkn_PGPFG_menu[atual].classList.add('lkn-pix-menu-ativo');
    lkn_PGPFG_settings[atual].classList.remove('lkn-pix-configuracao-disable');
}

const trs = document.querySelectorAll('.PGPFGForGivewpAdminSettingsTr');
trs.forEach(function (tr) {
    let label = tr.querySelector('label');
    let textoComplementar = tr.querySelector('a');
    if (textoComplementar) {
        let elLabel = document.createElement('label');
        let p = document.createElement('p');
        elLabel.appendChild(p);
        p.appendChild(textoComplementar);
        tr.querySelector('td').appendChild(elLabel)
    }
    let labelText = label.innerHTML;

    let novaLabel = document.createElement('label')
    novaLabel.innerHTML = labelText;
    let hr = document.createElement('div');
    hr.classList.add('title-hr');
    let td = tr.querySelector('td');
    td.insertBefore(hr, td.firstChild);
    td.insertBefore(novaLabel, td.firstChild);

    let subtitle = td.querySelector('.lkn-pix-subtitle');
    if (subtitle) {
        td.insertBefore(subtitle, hr);
    }
})

if (!document.getElementById('lkn-payment-pix-license-setting')) {
    for (let i = 1; i < lkn_PGPFG_settings.length; i++) {
        let configs = lkn_PGPFG_settings[i].querySelectorAll('td');
        configs.forEach(function (config) {
            Array.from(config.children).forEach(function (child) {
                if (child.nodeName === 'LABEL' || child.nodeName === 'HR') {
                    return;
                }
                child.classList.add('lkn-disabled-settings');
            });
            let p = document.createElement('p');
            p.innerHTML = 'Disponivel apenas com a versão Pro'
            p.classList.add('lkn-label-pro')
            config.appendChild(p)
        })
    }

    document.querySelector('.give-submit-wrap input').addEventListener('click', () => {
        event.preventDefault();
        //limpa Campos Pro button-primary give-save-button
        for (let i = 1; i < lkn_PGPFG_settings.length; i++) {
            let configs = lkn_PGPFG_settings[i].querySelectorAll('td');
            configs.forEach(function (config) {
                config.querySelectorAll("input[type='text'], input[type='password']").forEach(function (input) {
                    input.value = '';
                })
            })
        }
        const form = document.querySelector('form');
        if (form) {
            form.submit();
        }
    })
}