function crcChecksum(string) {
  let crc = 0xFFFF
  const strlen = string.length
  for (let c = 0; c < strlen; c++) {
    crc ^= string.charCodeAt(c) << 8
    for (let i = 0; i < 8; i++) {
      if (crc & 0x8000) {
        crc = crc << 1 ^ 0x1021
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
function pixBuilder(keyType, key, keyName, keyCity, amount = '', keyId = '***') {
  // TODO: Estudar necessidade de modificação de chaves cpf, cnpj ou email e implementar se necessário
  const pixKey = keyType !== 'tel' || key.substr(0, 3) === '+55' ? key : '+55' + key
  const pixName = keyName.length > 25 ? keyName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  const pixCity = keyCity.length > 15 ? keyCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  const pixAmount = amount

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
  qr += '26' + (22 + pixKey.length).toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  })
  qr += '0014BR.GOV.BCB.PIX'
  qr += '01' + pixKey.length.toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  }) + pixKey
  qr += '52040000'
  qr += '5303986' + (pixAmount.length === 0
    ? ''
    : '54' + pixAmount.length.toLocaleString('en-US', {
      minimumIntegerDigits: 2,
      useGrouping: false
    }) + pixAmount)
  qr += '5802BR'
  qr += '59' + pixName.length.toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  }) + pixName
  qr += '60' + pixCity.length.toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  }) + pixCity
  qr += '62' + (4 + keyId.length).toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  }) + '05' + keyId.length.toLocaleString('en-US', {
    minimumIntegerDigits: 2,
    useGrouping: false
  }) + keyId
  qr += '6304'
  qr += crcChecksum(qr)
  return qr
}
function writeToClipboard() {
  navigator.clipboard.writeText(pixBuilder(lknAttr.pixType, lknAttr.pixKey, lknAttr.pixName, lknAttr.pixCity, amount))
}
const amount = ''
const gateway = {
  id: 'pix-payment-gateway',
  async initialize() {
    // Aqui vai todas as funções necessárias ao carregar a página de pagamento
  },
  async beforeCreatePayment(values) {
    // Aqui vai tudo que precisa rodar depois de submeter o formulário e antes do pagamento ser completado
    // Ponha validações e adicione atributos que você vai precisar no back-end aqui

    // Caso detecte algum erro de validação você pode adicionar uma exceção
    // A mensagem de erro aparecerá para o cliente já formatada
    if (values.firstname === 'error') {
      throw new Error('Gateway failed')
    }

    // Retorna os atributos usados pelo back-end
    // Atributos do objeto value já são passados por padrão
    return {
      pluginIntent: 'lkn-plugin-intent',
      custom: 'anything'
    }
  },
  async afterCreatePayment(response) {
    // Aqui roda tudo que você precisa após o formulário ser submetido
    // Antes de ir para a tela do comprovante de pagamento
  },
  // Função onde os campos HTML são criados
  Fields() {
    return /* #__PURE__ */React.createElement('div', {
      style: {
        textAlign: 'center',
        maxWidth: '400px'
      }
    }, /* #__PURE__ */React.createElement('h3', null, 'Chave Pix:'), /* #__PURE__ */React.createElement('p', {
      id: 'qr'
    }, /* #__PURE__ */React.createElement('img', {
      src: 'https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=' + encodeURIComponent(pixBuilder(lknAttr.pixType, lknAttr.pixKey, lknAttr.pixName, lknAttr.pixCity, amount)),
      alt: 'QR Code for ' + pixBuilder(lknAttr.pixType, lknAttr.pixKey, lknAttr.pixName, lknAttr.pixCity, amount)
    })), /* #__PURE__ */React.createElement('p', {
      id: 'pix'
    }, pixBuilder(lknAttr.pixType, lknAttr.pixKey, lknAttr.pixName, lknAttr.pixCity, amount)), /* #__PURE__ */React.createElement('p', {
      id: 'copy-pix'
    }, /* #__PURE__ */React.createElement('button', {
      type: 'button',
      class: 'copy-button',
      onClick: writeToClipboard
    }, 'Copiar a Chave')))
  }
}
window.givewp.gateways.register(gateway)
