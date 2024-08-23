(function ($) {
  'use strict'

  // Pix content
  let lknPixGiveWPType
  let lknPixGiveWPKey
  let lknPixGiveWPName
  let lknPixGiveWPCity
  let lknPixGiveWPResult
  let lknPixGiveWPHTMLKey
  let lknPixGiveWPId

  // Frame info
  let lknPixGiveWPFormType
  let lknPixGiveWPIframe

  let lknPixGiveWPObserver

  function lknPixGiveWPCrcChecksum(string) {
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

  function lknPixGiveWPPixBuilder(amount = '') {
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
    qr += '26' + (22 + lknPixGiveWPKey.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })
    qr += '0014BR.GOV.BCB.PIX'
    qr += '01' + lknPixGiveWPKey.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixGiveWPKey
    qr += '52040000'
    qr += '5303986' + ((amount.length === 0) ? '' : ('54' + amount.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + amount))
    qr += '5802BR'
    qr += '59' + lknPixGiveWPName.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixGiveWPName
    qr += '60' + lknPixGiveWPCity.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixGiveWPCity
    qr += '62' + (4 + lknPixGiveWPId.length).toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + '05' + lknPixGiveWPId.length.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false }) + lknPixGiveWPId
    qr += '6304'
    qr += lknPixGiveWPCrcChecksum(qr)

    return qr
  }

  let lknPixGiveWPChangeDebouncer
  function lknPixGiveWPChangeForm() {
    try {
      if (lknPixGiveWPIframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }

      lknPixGiveWPType = lknPixGiveWPIframe.contents().find('input[id="pix_type"]').val()
      lknPixGiveWPHTMLKey = lknPixGiveWPIframe.contents().find('input[id="pix_key"]').val()
      lknPixGiveWPName = lknPixGiveWPIframe.contents().find('input[id="pix_name"]').val()
      lknPixGiveWPCity = lknPixGiveWPIframe.contents().find('input[id="pix_city"]').val()
      lknPixGiveWPId = lknPixGiveWPIframe.contents().find('input[id="pix_id"]').val()

      switch (lknPixGiveWPType) {
        case 'tel':
          lknPixGiveWPKey = (lknPixGiveWPHTMLKey.substr(0, 3) === '+55') ? lknPixGiveWPHTMLKey : '+55' + lknPixGiveWPHTMLKey
          break
        case 'cpf':
          lknPixGiveWPKey = lknPixGiveWPHTMLKey.replace(/[\u0300-\u036f]/g, '')
          break
        case 'cnpj':
          lknPixGiveWPKey = lknPixGiveWPHTMLKey.replace(/[\u0300-\u036f]/g, '')
          break
        default:
          lknPixGiveWPKey = lknPixGiveWPHTMLKey
          break
      }
      lknPixGiveWPName = (lknPixGiveWPName.length > 25) ? lknPixGiveWPName.substr(0, 25).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : lknPixGiveWPName.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      lknPixGiveWPCity = (lknPixGiveWPCity.length > 15) ? lknPixGiveWPCity.substr(0, 15).normalize('NFD').replace(/[\u0300-\u036f]/g, '') : lknPixGiveWPCity.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      lknPixGiveWPId = (lknPixGiveWPId === '') ? '***' : lknPixGiveWPId

      const btn = lknPixGiveWPIframe.contents().find('p[id="copy-pix"]')[0]
      if (btn === undefined || lknPixGiveWPType === undefined || lknPixGiveWPHTMLKey === undefined || lknPixGiveWPName === undefined || lknPixGiveWPCity === undefined) {
        throw Error(['Pix form not loaded'])
      }
      btn.style.display = 'block'

      let strAux
      switch (lknPixGiveWPFormType) {
        case 'legacy':
          strAux = document.querySelector('.give-final-total-amount').textContent.split(',')
          break
        case 'classic':
          strAux = lknPixGiveWPIframe.contents().find('[data-tag="total"]').text().split(',')
          break
        default:
          break
      }

      const amount = parseFloat(strAux[0].replace(/[\D]+/g, '') + '.' + strAux[1]).toFixed(2)

      lknPixGiveWPResult = lknPixGiveWPPixBuilder(amount)
      lknPixGiveWPIframe.contents().find('p[id="pix"]').html(lknPixGiveWPResult)
      lknPixGiveWPIframe.contents().find('button[id="toggle-viewing"]').off('click')
      lknPixGiveWPIframe.contents().find('button[id="copy-button"]').off('click')
      lknPixGiveWPIframe.contents().find('button[id="toggle-viewing"]').on('click', () => {
        const pixElement = lknPixGiveWPIframe.contents().find('p[id="pix"]')[0]
        const hideElement = lknPixGiveWPIframe.contents().find('span[id="hide"]')[0]
        const showElement = lknPixGiveWPIframe.contents().find('span[id="show"]')[0]

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
      lknPixGiveWPIframe.contents().find('button[id="copy-button"]').on('click', () => {
        navigator.clipboard.writeText(lknPixGiveWPResult)
      })

      const qrContainer = lknPixGiveWPIframe.contents().find('p[id="qr"]')[0]
      if (qrContainer !== null || qrContainer !== undefined) {
        qrContainer.innerHTML = ''
        const qrCodeObj = new QRCode(qrContainer, {
          text: lknPixGiveWPResult,
          width: 150,
          height: 150
        })
      }
    } catch (e) {
      lknPixGiveWPObserver = undefined
      lknPixGiveWPObserve()

      clearTimeout(lknPixGiveWPChangeDebouncer)
      lknPixGiveWPChangeDebouncer = setTimeout(
        function () {
          lknPixGiveWPChangeForm()
        }, 2000
      )
    }
  }

  let lknPixGiveWPObserveDeboncer
  function lknPixGiveWPObserve() {
    try {
      if (lknPixGiveWPIframe.contents().find('div[id="lkn-react-pix-form"]').length) { return }
      if (lknPixGiveWPObserver === undefined || lknPixGiveWPObserver === null) {
        throw Error('observer not defined')
      }

      let lknObserved
      switch (lknPixGiveWPFormType) {
        case 'legacy':
          lknObserved = [document.querySelector('.give-final-total-amount')]
          lknObserved.push(document.querySelector('#give_purchase_form_wrap'))
          lknObserved.push(document.querySelector('.give-form-type-multi'))
          break
        case 'classic':
          lknObserved = [lknPixGiveWPIframe.contents().find('[data-tag="total"]')[0]]
          lknObserved.push(lknPixGiveWPIframe.contents().find('fieldset[id="give-payment-mode-select"]')[0])
          lknObserved.push(lknPixGiveWPIframe.contents().find('body[class="give-form-templates"]')[0] ?? lknPixGiveWPIframe.contents().find('body[class="give-form-templates give-container-boxed"]')[0])

          lknPixGiveWPIframe.contents().find('input[id="give-amount"]').on('change', function () {
            setTimeout(
              function () {
                lknPixGiveWPChangeForm()
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
          throw Error(['Observed is not set', item, lknObserved, ['Form is of type', lknPixGiveWPFormType], lknPixGiveWPIframe])
        }

        lknPixGiveWPObserver.observe(item, {
          attributes: true,
          childList: true,
          characterData: true
        })
      })
    } catch (e) {
      if (e.message === 'observer not defined') {
        lknPixGiveWPObserver = new MutationObserver((target) => {
          lknPixGiveWPChangeForm()
        })
      }

      clearTimeout(lknPixGiveWPObserveDeboncer)
      lknPixGiveWPObserveDeboncer = setTimeout(
        function () {
          lknPixGiveWPObserve()
        }, 5000
      )
    }
  }

  $(window).on('load', function () {
    lknPixGiveWPIframe = $('iframe').length ? $('iframe') : $('body')
    if (!lknPixGiveWPIframe.length || lknPixGiveWPIframe.contents().find('div[id="lkn-react-pix-form"]').length) {
      return
    }

    lknPixGiveWPFormType = document.querySelector('.give-final-total-amount') ? 'legacy' : 'classic'

    lknPixGiveWPObserve()

    lknPixGiveWPChangeForm()

    //Aplica css dentro do iframe
    var iframe = document.querySelector('iframe[name="give-embed-form"]');
    if(iframe){
      var doc = iframe.contentDocument || iframe.contentWindow.document;      
      var link = doc.createElement('link');

      link.href = lknAttr.pgpfgPublicCssUrl;
      link.rel = 'stylesheet';
      link.type = 'text/css';
      
      doc.head.appendChild(link);
    }
  })
})(jQuery)
