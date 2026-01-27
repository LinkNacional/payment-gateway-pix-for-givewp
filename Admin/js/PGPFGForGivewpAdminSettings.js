// Selecione todos os elementos com a classe desejada

var elements = document.querySelectorAll('.give-setting-tab-header.give-setting-tab-header-gateways');
const pgpfgSettingsContainer = document.createElement('div');
pgpfgSettingsContainer.className = 'pgpfg-settings-container';
let firstElementParent = null;
let firstElementNextSibling = null;

// Para cada elemento selecionado
elements.forEach(function (element, index) {
    // Armazenar referência do primeiro elemento para inserir o container no local correto
    if (index === 0) {
        firstElementParent = element.parentNode;
        firstElementNextSibling = element.nextSibling;
    }

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

// Após criar todos os PGPFGForGivewpAdminSettingsDiv, movê-los para dentro do container
const allSettingsDivs = document.querySelectorAll('.PGPFGForGivewpAdminSettingsDiv');
allSettingsDivs.forEach(function (settingsDiv) {
    pgpfgSettingsContainer.appendChild(settingsDiv);
});

// Inserir o container no local onde estava o primeiro elemento
if (firstElementParent && allSettingsDivs.length > 0) {
    if (firstElementNextSibling) {
        firstElementParent.insertBefore(pgpfgSettingsContainer, firstElementNextSibling);
    } else {
        firstElementParent.appendChild(pgpfgSettingsContainer);
    }
}

// Aguardar um momento e adicionar o card lateral
if (pgpfgSettingsContainer && !document.querySelector('#pgpfgSettingsFlexContainer')) {
    // Criar div para card lateral
    const sideCardDiv = document.createElement('div');
    sideCardDiv.id = 'pgpfgSideCard';
    // Aplicar imagens de fundo dinamicamente
    sideCardDiv.style.backgroundImage = `url("${window.wpApiSettings ? wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/backgroundCardRight.svg') : '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/backgroundCardRight.svg'}"), url("${window.wpApiSettings ? wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/backgroundCardLeft.svg') : '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/backgroundCardLeft.svg'}")`;;

    // Criar conteúdo do card lateral margin-bottom: 18px;
    const cardHTML = `
            <div id="pgpfgDivLogo" style="display: flex; flex-direction: column; gap: 2px; font-family: Inter; font-weight: 500; ">
                <div>
                    <img src="${window.wpApiSettings ? wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/linkNacionalLogo.webp') : '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/linkNacionalLogo.webp'}" alt="Plugin Logo" style="height: 25px;">
                </div>
                <p style="font-size: 10px; margin: 0;">${window.pgpfgTranslations ? pgpfgTranslations.versions : 'v1.0'}</p>
            </div>
            <div id="pgpfgDivContent" style="display: flex; flex-direction: column; gap: 20px;">
                <div id="pgpfgDivLinks" style="display: flex; justify-content: space-between; padding-right: 30px; gap: 6%;">
                    <div style="display: flex; flex-direction: column; gap: 5px; font-family: Inter;">
                        <a target="_blank" href="https://www.linknacional.com.br/wordpress/givewp/pix/?utm=plugin" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small; white-space: nowrap;">
                            <b style="color: #D1D838; font-size: small;">•</b> Documentação
                        </a>
                        <a target="_blank" href="https://www.linknacional.com.br/wordpress/planos/?utm=plugin" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small; white-space: nowrap;">
                            <b style="color: #D1D838; font-size: small;">•</b> WordPress VIP
                        </a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px; font-family: Inter;">
                        <a target="_blank" href="https://www.linknacional.com.br/wordpress/suporte/" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small; white-space: nowrap;">
                            <b style="color: #D1D838; font-size: small;">•</b> Suporte WordPress
                        </a>
                        <a target="_blank" href="https://cliente.linknacional.com.br/solicitar/wordpress-woo-gratis/?utm=plugin" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small; white-space: nowrap;">
                            <b style="color: #D1D838; font-size: small;">•</b> Hospedagem WP
                        </a>
                    </div>
                </div>
                <div class="pgpfgSupportLinks" style="display: flex; flex-wrap: wrap; width: 100%; justify-content: space-between; align-items: center;">
                    <div id="pgpfgStarsDiv" style="width: fit-content;">
                        <a target="_blank" href="https://wordpress.org/plugins/payment-gateway-pix-for-givewp/#reviews" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; flex-direction: column; display: flex; align-items: center; gap: 2px; font-size: small;">
                            <p style="margin: 0; font-size: x-small; font-family: Inter; width: 100%; text-align: center;">Avaliar o plugin</p>
                            <div class="PGPFGForGivewpStarRating"">
                                <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                                <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                                <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                                <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                                <span class="dashicons dashicons-star-filled lkn-stars-pix"></span>
                            </div>
                        </a>
                    </div>
                    <div class="pgpfgContactLinks" style="display: flex; gap: 4px;">
                        <a href="https://chat.whatsapp.com/IjzHhDXwmzGLDnBfOibJKO" target="_blank" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small;">
                            <img src="${window.wpApiSettings ? wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/whatsapp-icon.svg') : '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/whatsapp-icon.svg'}" alt="WhatsApp Icon" style="width: 24px; height: 24px; filter: brightness(0) invert(1);">
                        </a>
                        <a href="https://t.me/wpprobr" target="_blank" style="text-decoration: none; color: inherit; background: none; border: none; padding: 0; margin: 0; font: inherit; cursor: pointer; display: flex; align-items: center; gap: 2px; font-size: small;">
                            <img src="${window.wpApiSettings ? wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/telegram-icon.svg') : '/wp-content/plugins/payment-gateway-pix-for-givewp/Admin/images/telegram-icon.svg'}" alt="Telegram Icon" style="width: 24px; height: 24px; filter: brightness(0) invert(1);">
                        </a>
                    </div>
                </div>
            </div>
        `;
    sideCardDiv.innerHTML = cardHTML;



    // Criar wrapper flex e reorganizar elementos
    const flexWrapper = document.createElement('div');
    flexWrapper.id = 'pgpfgSettingsFlexContainer';

    const parentElement = pgpfgSettingsContainer.parentElement;
    parentElement.insertBefore(flexWrapper, pgpfgSettingsContainer);

    flexWrapper.appendChild(pgpfgSettingsContainer);
    flexWrapper.appendChild(sideCardDiv);
}

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
    let description = pgpfgTranslations.description[fieldId] ?? null;
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
        let div = document.createElement('label');
        p.innerHTML = subtitle;
        div.classList.add('lkn-pix-subtitle');
        div.append(p);
        let td = th.parentElement.querySelector('td');
        td.appendChild(div);
    }
    if (description) {
        let p = document.createElement('p');
        let div = document.createElement('label');
        p.classList.add('lkn-pix-description');
        p.innerHTML = description;
        div.append(p);
        let td = th.parentElement.querySelector('td');
        td.appendChild(div);
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

let previous;
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

    // Obtém o ID do campo para buscar a nova_desc
    var fieldId = null;
    var th = tr.querySelector('th');
    var inputField = tr.querySelector('td input, td select, td textarea, td fieldset');
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
    let join = pgpfgTranslations.join[fieldId] ?? null;
    if (join == 'with-next') {
        let td = th.parentElement.querySelector('td');
        td.classList.add('join-next')
        previous = th;
    }
    if (join == 'with-previous') {
        let td = th.parentElement.querySelector('td');
        td.classList.add('join-previous');
        previous.querySelector('label').innerHTML = td.querySelector('label').innerHTML;
        previous.querySelector('p').innerHTML = td.querySelector('p').innerHTML;
        th.querySelector('label').style.display = 'none';
        th.querySelector('p').style.display = 'none';
    }
})

if (!document.getElementById('lkn-payment-pix-license-setting')) {
    for (let i = 2; i < lkn_PGPFG_settings.length; i++) {
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
        for (let i = 2; i < lkn_PGPFG_settings.length; i++) {
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