/* eslint-disable space-before-function-paren */
/* eslint-disable no-undef */
(function ($) {
  'use strict'

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

  function pixBuilder(keyType, key, keyName, keyCity, amount = '', keyId = '***') {
    // TODO: Estudar necessidade de modificação de chaves cpf, cnpj ou email e implementar se necessário
    const pixKey = ((keyType !== 'tel') || (key.substr(0, 3) === '+55')) ? key : '+55' + key
    const pixName = (keyName.length > 25) ? keyName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    const pixCity = (keyCity.length > 15) ? keyCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
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
    qr += '26' + (22 + pixKey.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })
    qr += '0014BR.GOV.BCB.PIX'
    qr += '01' + pixKey.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixKey
    qr += '52040000'
    qr += '5303986' + ((pixAmount.length === 0) ? '' : ('54' + pixAmount.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixAmount))
    qr += '5802BR'
    qr += '59' + pixName.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixName
    qr += '60' + pixCity.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixCity
    qr += '62' + (4 + keyId.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + '05' + keyId.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + keyId
    qr += '6304'
    qr += crcChecksum(qr)

    return qr
  }

  function changeForm(iframe) {
    const pixType = iframe.contents().find('input[id="pix_type"]').val()
    const pixKey = iframe.contents().find('input[id="pix_key"]').val()
    const pixName = iframe.contents().find('input[id="pix_name"]').val()
    const pixCity = iframe.contents().find('input[id="pix_city"]').val()
    const isLegacy = !!iframe.contents().find('span[class="give-final-total-amount"]')
    const aux = isLegacy ? iframe.contents().find('span[class="give-final-total-amount"]').text().replace(',', '.').substr(6) : iframe.contents().find('th[data-tag="total"]').text().substr(2).replace(/[\D]+/g, '')
    const amount = parseFloat(aux).toFixed(2)
    console.log(amount)

    const pix = pixBuilder(pixType, pixKey, pixName, pixCity, amount)
    iframe.contents().find('p[id="qr"]').html("<img src='https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" + encodeURIComponent(pix) + "' alt='QR Code for " + pix + "'/>")
    iframe.contents().find('p[id="pix"]').html(pix)
    iframe.contents().find('p[id="copy-pix"]').html('<button type="button" ' + ($('iframe').length ? 'class="copy-button" ' : '') + 'onclick="navigator.clipboard.writeText(\'' + pix + '\')">Copiar a Chave</button>')
  }

  // TODO: test amount update on legacy
  $(window).on('load', function () {
    const iframe = $('iframe').length ? $('iframe') : $('div[id="content"]')
    if (!iframe.length) {
      console.log('Thats no good!')
      return
    }

    changeForm(iframe)

    iframe.contents().find('button[class="give-btn advance-btn"]').on('click', function () {
      changeForm(iframe)
    })

    iframe.contents().find('button[class="give-btn give-btn-modal"]').on('click', function () {
      console.log('changed')
      changeForm(iframe)
    })

    iframe.contents().find('input[value="pix-payment-gateway"]').on('change', function () {
      // TODO: change delay into waiting for component to load (which component?) (maybe keep trying until loads?)
      setTimeout(
        function () {
          changeForm(iframe)
        }, 5000)
    })
  })
})(jQuery)
