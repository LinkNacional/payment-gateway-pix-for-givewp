/* eslint-disable space-before-function-paren */
/* eslint-disable no-undef */
(function ($) {
  'use strict'

  /*
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

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

  function pixBuilder(keyType, key, keyName, keyCity, keyId = '***', amount = '') {
    const pixKey = ((keyType !== 'Telefone') || (key.substr(0, 3) === '+55')) ? key : '+55' + key
    const pixName = (keyName.length > 25) ? keyName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    const pixCity = (keyCity.length > 15) ? keyCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : keyCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')

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
    qr += '5303986' + ((amount.length === 0) ? '' : ('54' + amount.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + amount))
    qr += '5802BR'
    qr += '59' + pixName.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixName
    qr += '60' + pixCity.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + pixCity
    qr += '62' + (4 + keyId.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + '05' + keyId.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + keyId
    qr += '6304'
    qr += crcChecksum(qr)

    return qr
  }

  function qrCode(pix) {
    return "<img src='https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" + encodeURI(pix) + "' alt='QR Code for " + pix + "'/>"
  }

  $(window).on('load', function () {
    const iframe = $('iframe[name="give-embed-form"]')
    if (!iframe.length) {
      return
    }
    const pix = pixBuilder('Telefone', '19998513603', 'João Bueno', 'São Carlos')
    iframe.contents().find('p[id="qr"]').append(qrCode(pix))
    iframe.contents().find('p[id="pix"]').append(pix)
    iframe.contents().find('p[id="copy-pix"]').append('<button type="button" class="copy-button" onclick="navigator.clipboard.writeText(\'' + pix + '\')">Copiar a Chave</button>')
  })
})(jQuery)
