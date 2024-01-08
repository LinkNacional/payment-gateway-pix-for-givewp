(function ($) {
  'use strict'

  // Pix content
  let lknPixType
  let lknPixKey
  let lknPixName
  let lknPixCity
  let lknPix
  let lknPixHTMLKey
  let lknPixId

  // Frame info
  let lknPixFormType
  let lknPixIframe

  let lknObserver

  function lknCrcChecksum (string) {
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

  function lknPixBuilder (amount = '') {
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
    qr += '26' + (22 + lknPixKey.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })
    qr += '0014BR.GOV.BCB.PIX'
    qr += '01' + lknPixKey.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixKey
    qr += '52040000'
    qr += '5303986' + ((amount.length === 0) ? '' : ('54' + amount.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + amount))
    qr += '5802BR'
    qr += '59' + lknPixName.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixName
    qr += '60' + lknPixCity.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixCity
    qr += '62' + (4 + lknPixId.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + '05' + lknPixId.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixId
    qr += '6304'
    qr += lknCrcChecksum(qr)

    return qr
  }

  let lknChangeDebouncer
  function lknChangeForm () {
    try {
      if (lknPixIframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }

      lknPixType = lknPixIframe.contents().find('input[id="pix_type"]').val()
      lknPixHTMLKey = lknPixIframe.contents().find('input[id="pix_key"]').val()
      lknPixName = lknPixIframe.contents().find('input[id="pix_name"]').val()
      lknPixCity = lknPixIframe.contents().find('input[id="pix_city"]').val()
      lknPixId = lknPixIframe.contents().find('input[id="pix_id"]').val()

      switch (lknPixType) {
        case 'tel':
          lknPixKey = (lknPixHTMLKey.substr(0, 3) === '+55') ? lknPixHTMLKey : '+55' + lknPixHTMLKey
          break
        case 'cpf':
          lknPixKey = lknPixHTMLKey.replace(/[\u0300-\u036f]/g, '')
          break
        case 'cnpj':
          lknPixKey = lknPixHTMLKey.replace(/[\u0300-\u036f]/g, '')
          break
        default:
          lknPixKey = lknPixHTMLKey
          break
      }
      lknPixName = (lknPixName.length > 25) ? lknPixName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : lknPixName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      lknPixCity = (lknPixCity.length > 15) ? lknPixCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : lknPixCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      lknPixId = (lknPixId === '') ? '***' : lknPixId

      const btn = lknPixIframe.contents().find('p[id="copy-pix"]')[0]
      if (btn === undefined || lknPixType === undefined || lknPixHTMLKey === undefined || lknPixName === undefined || lknPixCity === undefined) {
        throw Error(['Pix form not loaded'])
      }
      btn.style.display = 'block'

      let strAux
      switch (lknPixFormType) {
        case 'legacy':
          strAux = document.querySelector('.give-final-total-amount').textContent.split(',')
          break
        case 'classic':
          strAux = lknPixIframe.contents().find('[data-tag="total"]').text().split(',')
          break
        default:
          break
      }

      const amount = parseFloat(strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]).toFixed(2)

      lknPix = lknPixBuilder(amount)
      lknPixIframe.contents().find('p[id="qr"]').html("<img id='qr-img' src='https://quickchart.io/qr?text=" + encodeURIComponent(lknPix) + "&size=150' alt='QR Code for payment via Pix'/>")
      lknPixIframe.contents().find('p[id="pix"]').html(lknPix)
      lknPixIframe.contents().find('button[id="toggle-viewing"]').off('click')
      lknPixIframe.contents().find('button[id="copy-button"]').off('click')
      lknPixIframe.contents().find('button[id="toggle-viewing"]').on('click', () => {
        const pixElement = lknPixIframe.contents().find('p[id="pix"]')[0]
        const hideElement = lknPixIframe.contents().find('span[id="hide"]')[0]
        const showElement = lknPixIframe.contents().find('span[id="show"]')[0]

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
      lknPixIframe.contents().find('button[id="copy-button"]').on('click', () => {
        navigator.clipboard.writeText(lknPix)
      })
    } catch (e) {
      lknObserver = undefined
      lknObserve()

      clearTimeout(lknChangeDebouncer)
      lknChangeDebouncer = setTimeout(
        function () {
          lknChangeForm()
        }, 2000
      )
    }
  }

  let lknObserveDeboncer
  function lknObserve () {
    try {
      if (lknPixIframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }
      if (lknObserver === undefined || lknObserver === null) {
        throw Error('observer not defined')
      }

      let lknObserved
      switch (lknPixFormType) {
        case 'legacy':
          lknObserved = [document.querySelector('.give-final-total-amount')]
          lknObserved.push(document.querySelector('#give_purchase_form_wrap'))
          lknObserved.push(document.querySelector('.give-form-type-multi'))
          break
        case 'classic':
          lknObserved = [lknPixIframe.contents().find('[data-tag="total"]')[0]]
          lknObserved.push(lknPixIframe.contents().find('fieldset[id="give-payment-mode-select"]')[0])
          lknObserved.push(lknPixIframe.contents().find('body[class="give-form-templates"]')[0] ?? lknPixIframe.contents().find('body[class="give-form-templates give-container-boxed"]')[0])

          lknPixIframe.contents().find('input[id="give-amount"]').on('change', function () {
            setTimeout(
              function () {
                lknChangeForm()
              }, 5000)
          })
          break
        default:
          lknObserved = [null]
          break
      }

      lknObserved.forEach((item) => {
        if (lknObserved === undefined || lknObserved === null ||
          item === undefined || item === null) {
          throw Error(['Observed is not set', item, lknObserved, ['Form is of type', lknPixFormType], lknPixIframe])
        }

        lknObserver.lknObserve(item, {
          attributes: true,
          childList: true,
          characterData: true
        })
      })
    } catch (e) {
      if (e.message === 'observer not defined') {
        lknObserver = new MutationObserver((target) => {
          lknChangeForm()
        })
      }

      clearTimeout(lknObserveDeboncer)
      lknObserveDeboncer = setTimeout(
        function () {
          lknObserve()
        }, 5000
      )
    }
  }

  $(window).on('load', function () {
    lknPixIframe = $('iframe').length ? $('iframe') : $('body')
    if (!lknPixIframe.length || lknPixIframe.contents().find('div[id="lkn-react-pix-form"]').length) {
      return
    }

    lknPixFormType = document.querySelector('.give-final-total-amount') ? 'legacy' : 'classic'

    lknObserve()

    lknChangeForm()
  })
})(jQuery)
