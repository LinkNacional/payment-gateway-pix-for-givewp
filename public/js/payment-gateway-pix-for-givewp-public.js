/* eslint-disable space-before-function-paren */
/* eslint-disable no-undef */
(function ($) {
  'use strict'

  // Pix content
  let pixType
  let pixKey
  let pixName
  let pixCity
  let pix

  // Frame info
  let formType
  let iframe

  let observer

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
    amount = amount === 'NaN' ? '' : amount

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

  let changeDebouncer
  function changeForm() {
    try {
      if (iframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }

      pixType = iframe.contents().find('input[id="pix_type"]').val()
      pixKey = iframe.contents().find('input[id="pix_key"]').val()
      pixName = iframe.contents().find('input[id="pix_name"]').val()
      pixCity = iframe.contents().find('input[id="pix_city"]').val()

      const btn = iframe.contents().find('p[id="copy-pix"]')[0]
      if (btn === undefined || pixType === undefined || pixKey === undefined || pixName === undefined || pixCity === undefined) {
        throw Error(['Pix form not loaded'])
      }
      btn.style.display = 'block'

      let strAux
      switch (formType) {
        case 'legacy':
          strAux = document.querySelector('.give-final-total-amount').textContent.split(',')
          break
        case 'classic':
          strAux = iframe.contents().find('[data-tag="total"]').text().split(',')
          break
        default:
          break
      }

      const amount = parseFloat(strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]).toFixed(2)

      pix = pixBuilder(amount)
      iframe.contents().find('p[id="qr"]').html("<img id='qr-img' src='https://chart.googleapis.com/chart?cht=qr&chs=150x150&chl=" + encodeURIComponent(pix) + "' alt='QR Code for payment via Pix'/>")
      iframe.contents().find('p[id="pix"]').html(pix)
      iframe.contents().find('button[id="toggle-viewing"]').off('click')
      iframe.contents().find('button[id="copy-button"]').off('click')
      iframe.contents().find('button[id="toggle-viewing"]').on('click', () => {
        const pixElement = iframe.contents().find('p[id="pix"]')[0]
        const hideElement = iframe.contents().find('span[id="hide"]')[0]
        const showElement = iframe.contents().find('span[id="show"]')[0]

        if (pixElement.style.display === 'none') {
          showElement.style.display = 'none'
          hideElement.style.display = 'block'
          pixElement.style.display = 'block'
        } else {
          showElement.style.display = 'block'
          hideElement.style.display = 'none'
          pixElement.style.display = 'none'
        }
      })
      iframe.contents().find('button[id="copy-button"]').on('click', () => {
        navigator.clipboard.writeText(pix)
      })
    } catch (e) {
      console.debug(e)

      observer = undefined
      observe()

      clearTimeout(changeDebouncer)
      changeDebouncer = setTimeout(
        function () {
          changeForm()
        }, 2000
      )
    }
  }

  let observeDeboncer
  function observe() {
    try {
      if (iframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }
      if (observer === undefined || observer === null) {
        throw Error('observer not defined')
      }

      let observed
      switch (formType) {
        case 'legacy':
          observed = [document.querySelector('.give-final-total-amount')]
          observed.push(document.querySelector('#give_purchase_form_wrap'))
          observed.push(document.querySelector('.give-form-type-multi'))
          break
        case 'classic':
          observed = [iframe.contents().find('[data-tag="total"]')[0]]
          observed.push(iframe.contents().find('fieldset[id="give-payment-mode-select"]')[0])
          observed.push(iframe.contents().find('body[class="give-form-templates"]')[0] ?? iframe.contents().find('body[class="give-form-templates give-container-boxed"]')[0])

          iframe.contents().find('input[id="give-amount"]').on('change', function () {
            setTimeout(
              function () {
                changeForm()
              }, 5000)
          })
          break
        default:
          observed = [null]
          break
      }

      observed.forEach((item) => {
        if (observed === undefined || observed === null ||
          item === undefined || item === null) {
          throw Error(['Observed is not set', item, observed, ['Form is of type', formType], iframe])
        }

        observer.observe(item, {
          attributes: true,
          childList: true,
          characterData: true
        })
      })
    } catch (e) {
      if (e.message === 'observer not defined') {
        observer = new MutationObserver((target) => {
          changeForm()
        })
      }

      console.debug(e)
      clearTimeout(observeDeboncer)
      observeDeboncer = setTimeout(
        function () {
          observe()
        }, 5000
      )
    }
  }

  $(window).on('load', function () {
    iframe = $('iframe').length ? $('iframe') : $('body')
    if (!iframe.length || iframe.contents().find('div[id="lkn-react-pix-form"]').length) {
      return
    }

    formType = document.querySelector('.give-final-total-amount') ? 'legacy' : 'classic'

    iframe.contents().find('button[id="toggle-viewing"]').on('click', () => {
      if (iframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }
      togglePix()
    })
    iframe.contents().find('button[id="copy-button"]').on('click', () => {
      if (iframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }
      navigator.clipboard.writeText(pix)
    })

    observe()

    changeForm()
  })
})(jQuery)
