// Pix content
let pixType
let pixKey
let pixName
let pixCity
let pix

function crcChecksum(string) {
    let crc = 0xFFFF
    const strlen = string.length

    for (let c = 0; c < strlen; c++) {
        crc ^= string.charCodeAt(c) << 8
        for (let i = 0; i < 8; i++) {
            if (crc & 0x8000) {
                crc = (crc << 1) ^ 0x1021
            } else {
                crc = crc << 1
            }
        }
    }
    let hex = crc & 0xFFFF
    if (hex < 0) {
        hex = 0xFFFFFFFF + hex + 1
    }
    hex = parseInt(hex, 10).toString(16).toUpperCase().padStart(4, '0')

    return hex
}

function pixBuilder(amount = '', keyId = '***') {
    const pixType = lknAttr.pixType
    const pixKey = lknAttr.pixKey
    const pixName = lknAttr.pixName
    const pixCity = lknAttr.pixCity

    // TODO: Estudar necessidade de modificação de chaves cpf, cnpj ou email e implementar se necessário
    const key = ((pixType !== 'tel') || (pixKey.substr(0, 3) === '+55')) ? pixKey : '+55' + pixKey
    const keyName = (pixName.length > 25) ? pixName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : pixName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    const keyCity = (pixCity.length > 15) ? pixCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : pixCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')

    // (00 Payload Format Indicator)
    // (26 Merchant Account Information)
    //   (00 GUI - Default br.gov.bcb.pix)
    //   (01 Chave Pix)
    // (52 Merchant Category Code)
    // (53 Transaction  Currency - BRL 986)
    // (54 Transaction Amount - Optional)
    // (58 Country Code - BR)
    // (59 Merchant Name)
    // (60 Merchant City)
    // (62 Additional Data Field - Default ***)
    // (63 CRC16 Chcksum)
    let qr = '000201'
    qr += '26' + (22 + key.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })
    qr += '0014BR.GOV.BCB.PIX'
    qr += '01' + key.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + key
    qr += '52040000'
    qr += '5303986' + ((amount.length === 0) ? '' : ('54' + amount.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + amount))
    qr += '5802BR'
    qr += '59' + keyName.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + keyName
    qr += '60' + keyCity.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + keyCity
    qr += '62' + (4 + keyId.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + '05' + keyId.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + keyId
    qr += '6304'
    qr += crcChecksum(qr)

    return qr
}

let catchTimer
function changeForm() {
    try {
        let strAux
        const btn = document.getElementById("copy-pix")
        if (btn === undefined || pixType === undefined || pixKey === undefined || pixName === undefined || pixCity === undefined) throw ['Pix form not loaded']

        btn!.style.display = 'flex'
        strAux = document.querySelector('.givewp-elements-donationSummary__list__item__value')?.innerHTML.split(',')

        const amount = parseFloat(strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]).toFixed(2)

        pix = pixBuilder(amount)
        document.getElementById("toggle-viewing")?.addEventListener('onClick', () => {
            togglePix()
        })
        document.getElementById("copy-button")?.addEventListener('onClick', () => {
            navigator.clipboard.writeText(pix)
        })
    } catch (e) {
        console.debug(e)

        observe()

        clearTimeout(catchTimer)
        catchTimer = setTimeout(
            function () {
                changeForm()
            }, 2000
        )
    }
}

function observe() {
    let observeTimer
    try {
        // TODO: change into single const once v3 is implemented
        const total = document.querySelector('.givewp-elements-donationSummary__list__item__value')

        if (total === undefined) {
            throw ['Total not yet set', total]
        }
        console.log(total)

        // TODO: Classic (one screen) stops observing on change, fix this
        const observer = new MutationObserver((target) => {
            console.log(target)
            changeForm()
        })

        observer.observe(total!, {
            attributes: true,
            childList: true,
            characterData: true
        })
    } catch (e) {
        console.debug(e)

        clearTimeout(observeTimer)
        observeTimer = setTimeout(
            function () {
                observe()
            }, 5000
        )
    }
}

function togglePix() {
    const pixElement = document.getElementById("pix")
    const hideElement = document.getElementById("hide")
    const showElement = document.getElementById("show")

    if (pixElement!.style.display === 'none') {
        showElement!.style.display = 'none'
        hideElement!.style.display = 'block'
        pixElement!.style.display = 'block'
    } else {
        showElement!.style.display = 'block'
        hideElement!.style.display = 'none'
        pixElement!.style.display = 'none'
    }
}

const gateway = {
    id: 'pix-payment-gateway',
    async initialize() {
        // Aqui vai todas as funções necessárias ao carregar a página de pagamento
        observe()
    },
    async beforeCreatePayment(values) {
        // Aqui vai tudo que precisa rodar depois de submeter o formulário e antes do pagamento ser completado
        // Ponha validações e adicione atributos que você vai precisar no back-end aqui

        // Caso detecte algum erro de validação você pode adicionar uma exceção
        // A mensagem de erro aparecerá para o cliente já formatada
        if (values.firstname === 'error') {
            throw new Error('Gateway failed');
        }

        // Retorna os atributos usados pelo back-end
        // Atributos do objeto value já são passados por padrão
        return {
            pluginIntent: 'lkn-plugin-intent',
            custom: 'anything'
        };
    },
    async afterCreatePayment(response) {
        // Aqui roda tudo que você precisa após o formulário ser submetido
        // Antes de ir para a tela do comprovante de pagamento
    },
    // Função onde os campos HTML são criados
    Fields() {
        return (
            <div>
                <link rel="stylesheet" href={lknAttr.pluginUrl + "public/css/payment-gateway-pix-for-givewp-public.css"} />
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
                <input type="hidden" id="react-pix-form" />
                <div id="lkn-pix-form-donation" >
                    <legend>Chave Pix:</legend>
                    <div className='pix-container'>
                        <p id='qr'><img id='qr-img' src={'https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl="' + encodeURIComponent(pix) + '"'} alt='QR Code for payment via Pix' /></p>
                        <p id='pix'>{pix}</p>
                        <p id='copy-pix' >
                            <button id="toggle-viewing" type="button" title="Mostrar Pix">
                                <span id="show" className="material-symbols-outlined" style={{ display: "none" }}>visibility_off</span>
                                < span id="hide" className="material-symbols-outlined" > visibility</span>
                            </button>
                            <button id="copy-button" type="button" title="Copiar Pix">
                                <span className="material-symbols-outlined">content_copy</span>
                            </button>
                        </p>
                    </div>
                </div>
            </div >
        )
    }
};

window.givewp.gateways.register(gateway);
