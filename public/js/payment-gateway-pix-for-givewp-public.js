/* eslint-disable space-before-function-paren */
/* eslint-disable no-undef */
(function ($) {
  'use strict'

  let isLegacy
  let pixType
  let pixKey
  let pixName
  let pixCity

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

  function changeForm(iframe) {
    let aux
    if (isLegacy) {
      const strAux = document.querySelector('.give-final-total-amount').textContent.split(',')
      aux = strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]
    } else {
      const strAux = iframe.contents().find('th[data-tag="total"]').text().split(',')
      aux = strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]
    }
    const amount = parseFloat(aux).toFixed(2)

    const pix = pixBuilder(amount)
    iframe.contents().find('p[id="qr"]').html("<img src='https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" + encodeURIComponent(pix) + "' alt='QR Code for " + pix + "'/>")
    iframe.contents().find('p[id="pix"]').html(pix)
    iframe.contents().find('p[id="copy-pix"]').html(
      '<button type="button" class="copy-button" onclick="togglePix()" >' +
      '<span id="show" class="material-symbols-outlined">visibility_off</span>' +
      '<span id="hide" style="display: none;" class="material-symbols-outlined">visibility</span>' +
      '</button>' +
      '<button type="button" class="copy-button" onclick="navigator.clipboard.writeText(\'' + pix + '\')"><span class="material-symbols-outlined">content_copy</span></button>'
    )
  }

  $(window).on('load', function () {
    const iframe = $('iframe').length ? $('iframe') : $('body')
    if (!iframe.length || iframe.contents().find('form[id="give-next-gen"]').length) {
      return
    }

    pixType = iframe.contents().find('input[id="pix_type"]').val()
    pixKey = iframe.contents().find('input[id="pix_key"]').val()
    pixName = iframe.contents().find('input[id="pix_name"]').val()
    pixCity = iframe.contents().find('input[id="pix_city"]').val()

    isLegacy = !!document.querySelector('.give-final-total-amount')

    let total
    let mainDiv
    let extra

    const observer = new MutationObserver((target) => {
      changeForm(iframe)
    })

    if (isLegacy) {
      total = document.querySelector('.give-final-total-amount')
      mainDiv = document.querySelector('#give_purchase_form_wrap')
      extra = document.querySelector('.give-form-type-multi')
    } else {
      total = iframe.contents().find('th[data-tag="total"]')[0]
      mainDiv = iframe.contents().find('div[id="give-payment-mode-wrap"]')[0]
      extra = iframe.contents().find('div[class="give-donation-summary-table-wrapper"]')[0]

      iframe.contents().find('input[value="pix-payment-gateway"]').on('change', function () {
        setTimeout(
          function () {
            changeForm(iframe)
          }, 5000)
      })
    }

    observer.observe(total, {
      attributes: true,
      childList: true,
      characterData: true
    })
    observer.observe(mainDiv, {
      attributes: true,
      childList: true,
      characterData: true
    })
    observer.observe(extra, {
      attributes: true,
      childList: true,
      characterData: true
    })

    changeForm(iframe)
  })
})(jQuery)
