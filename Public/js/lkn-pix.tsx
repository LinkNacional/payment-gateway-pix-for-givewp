const { __ } = wp.i18n;
var iframe = window.parent.document.querySelector('iframe');
if (iframe) {
  var doc = iframe.contentDocument || iframe.contentWindow.document;
  var link = doc.createElement('link');
  link.href = lknAttr.pgpfgPublicCssUrl;
  link.rel = 'stylesheet';
  link.type = 'text/css';
  doc.head.appendChild(link);
}
function lknPGPFGGiveWPCrcChecksum(string) {
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

function lknPGPFGGiveWPPixBuilder(amount = '') {
    const pixType = lknAttr.pixType
    const pixKey = lknAttr.pixKey
    const pixName = lknAttr.pixName
    const pixCity = lknAttr.pixCity

    let key
    switch (pixType) {
        case 'tel':
            key = (pixKey.substr(0, 3) === '+55') ? pixKey : '+55' + pixKey
            break
        case 'cpf':
            key = pixKey.replace(/[\u0300-\u036f]/g, '')
            break
        case 'cnpj':
            key = pixKey.replace(/[\u0300-\u036f]/g, '')
            break
        default:
            key = pixKey
            break
    }
    const keyName = (pixName.length > 25) ? pixName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : pixName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    const keyCity = (pixCity.length > 15) ? pixCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : pixCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    const keyId = (lknAttr.pixId === '') ? '***' : lknAttr.pixId

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
    qr += lknPGPFGGiveWPCrcChecksum(qr)

    return qr
}

const lknGatewayPix = {
    id: 'pix-payment-gateway',
    async initialize() {
        // Aqui vai todas as funções necessárias ao carregar a página de pagamento
    },
    async beforeCreatePayment(values) {


        // Retorna os atributos usados pelo back-end
        // Atributos do objeto value já são passados por padrão
        return {
            "pix-payment-gateway-id": 'pix',
            pluginIntent: 'lkn-plugin-intent'
        };
    },
    async afterCreatePayment(response) {
        // Aqui roda tudo que você precisa após o formulário ser submetido
        // Antes de ir para a tela do comprovante de pagamento
    },
    // Função onde os campos HTML são criados
    Fields() {
        const { useWatch } = window.givewp.form.hooks
        const { useEffect } = wp.element

        const [pix, setPix] = React.useState(lknPGPFGGiveWPPixBuilder())
        const donationAmount = useWatch({ name: 'amount' })

        useEffect(() => {
            const donationSummary = document.querySelector('.givewp-elements-donationSummary__list__item__value')
            if (donationSummary) {
                const strAux = donationSummary.innerHTML.split(',')
                const amount = parseFloat(strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]).toFixed(2)
                setPix(lknPGPFGGiveWPPixBuilder(amount))
            }

            if (document.getElementById('qr') !== undefined) {
                document.getElementById('qr')!.innerHTML = ''
                document.getElementById('qr')!.style.display = 'flex';
                document.getElementById('qr')!.style.justifyContent = 'center';
                const qrCode = new QRCode(document.getElementById('qr'), {
                    text: pix,
                    width: 150,
                    height: 150,

                })
            }
        })

        return (
            <div id="lkn-react-pix-form" style={{ textAlign: "center" }}>
                <input type="hidden" id="donation-value" value={donationAmount}></input>
                <div id="lkn-pix-form-donation"  >
                    <legend>{__('Pix Key:', 'payment-gateway-pix-for-givewp')}</legend>
                    <div className='pix-container'>
                        <p id='qr' ></p>
                        <br />
                        <p id='pix' name='pix'>{pix}</p>
                        <p id='copy-pix' >
                            <button id="toggle-viewing" type="button" title={__('Show Pix', 'payment-gateway-pix-for-givewp')} onClick={() => {
                                const pixElement = document.getElementById('pix')
                                const hideElement = document.getElementById('hide')
                                const showElement = document.getElementById('show')

                                if (pixElement!.style.display === 'none') {
                                    showElement!.style.display = 'none'
                                    hideElement!.style.display = 'block'
                                    pixElement!.style.display = 'block'
                                } else {
                                    showElement!.style.display = 'block'
                                    hideElement!.style.display = 'none'
                                    pixElement!.style.display = 'none'
                                }
                            }}>
                                <span id="show" className="material-symbols-outlined">visibility_off</span>
                                <span id="hide" className="material-symbols-outlined" style={{ display: 'none' }}>visibility</span>
                            </button>
                            <button id="copy-button" type="button" title={__('Copy Pix', 'payment-gateway-pix-for-givewp')} onClick={() => { navigator.clipboard.writeText(pix) }}>
                                <span className="material-symbols-outlined">content_copy</span>
                            </button>
                        </p>
                    </div>
                </div>
            </div >
        )
    }
};

window.givewp.gateways.register(lknGatewayPix);
