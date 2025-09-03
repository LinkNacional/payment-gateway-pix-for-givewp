(function ($) {
  'use strict'

  class LknPaghiperPayment {
    dom

    constructor(dom) {
      this.dom = dom
    }

    setInfoBanner(pixFee, bolFee) {
      const divInfo = this.dom.getElementById('lkn_give_paghiper_info')
      const divPix = this.dom.getElementById('paghiper-pix-div')
      const divBol = this.dom.getElementById('paghiper-slip-div')

      if (divInfo) {
        if (divPix && pixFee > 0) {
          divInfo.removeAttribute('hidden') // Show
        } else if (divBol && bolFee > 0) {
          divInfo.removeAttribute('hidden') // Show
        } else {
          divInfo.setAttribute('hidden', '') // Hide
        }
      }
    }

    detectRecurrency() {
      const lknDonationRecurring = this.dom.getElementsByName('_give_is_donation_recurring')[0].value

      if (lknDonationRecurring === '0') {
        const doarBtn = this.dom.getElementById('give-purchase-button')
        if (doarBtn) {
          doarBtn.setAttribute('disabled', '')
        }
      } else {
        const doarBtn = this.dom.getElementById('give-purchase-button')
        if (doarBtn) {
          doarBtn.removeAttribute('disabled')
        }
      }
    }

    validateGateway() {
      const gatewaySelected = this.dom.getElementsByClassName('give-gateway-option-selected')[0].childNodes[1].value
      if (gatewaySelected !== 'lkn-give-paghiper-pix' || gatewaySelected !== 'lkn-give-paghiper-slip') {
        const recurrencyBtn = this.dom.getElementsByClassName('give-recurring-donors-choice')[0]

        if (recurrencyBtn) {
          recurrencyBtn.removeEventListener('click', this.detectRecurrency, false)
        }

        const doarBtn = this.dom.getElementById('give-purchase-button')
        if (doarBtn) {
          doarBtn.removeAttribute('disabled')
        }
      }
    }

    // Format the CPF/CNPJ field.
    formatField(htmlField) {
      const fieldValue = htmlField.target.value.replace(/\D/gmi, '')
      if (fieldValue.length <= 11) {
        htmlField.target.value = this.maskCPF(fieldValue)
      } else {
        htmlField.target.value = this.maskCNPJ(fieldValue)
      }
    }

    // Remove formatting on typing in the field.
    removeFormat(htmlField) {
      htmlField.target.value = htmlField.target.value.replace(/\D/g, '')
    }

    // Insert a mask according to characters quantity.
    maskCPF(value) {
      const cpfArr = value.split('')
      const resultArr = []

      for (let c = 0; c < cpfArr.length; c++) {
        resultArr.push(cpfArr[c])

        if (c % 9 === 8) {
          resultArr.push('-')
        } else if (c % 3 === 2) {
          resultArr.push('.')
        }
      }

      const cpfMasked = resultArr.join('')

      return cpfMasked
    }

    maskCNPJ(value) {
      const cpfArr = value.split('')
      const resultArr = []

      for (let c = 0; c < cpfArr.length; c++) {
        resultArr.push(cpfArr[c])

        if (c === 1) {
          resultArr.push('.')
        } else if (c === 4) {
          resultArr.push('.')
        } else if (c === 7) {
          resultArr.push('/')
        } else if (c === 11) {
          resultArr.push('-')
        }
      }

      const cpfMasked = resultArr.join('')

      return cpfMasked
    }

    defineCpfCnpjField(input) {
      input.addEventListener('focus', (event) => {
        this.removeFormat(event)
      })

      input.addEventListener('blur', (event) => {
        this.removeFormat(event)
        this.formatField(event)
      })

      input.addEventListener('input', (event) => {
        this.removeFormat(event)
      })
    }

    checkRecurrency() {
      const recurrencyInput = this.dom.getElementsByName('_give_is_donation_recurring')[0]
      const divPix = this.dom.getElementById('paghiper-pix-div')
      const divBol = this.dom.getElementById('paghiper-slip-div')

      // Verify if is possible make recurrent donations.
      if (recurrencyInput && recurrencyInput.value === '1') {
        const recurrencyBtn = this.dom.getElementsByClassName('give-recurring-donors-choice')[0]

        if (recurrencyBtn) {
          recurrencyBtn.removeEventListener('click', this.detectRecurrency, false)
          recurrencyBtn.addEventListener('click', this.detectRecurrency, false)

          const gatewayList = this.dom.getElementById('give-gateway-radio-list')

          if (gatewayList) {
            recurrencyBtn.removeEventListener('click', this.validateGateway, false)
            recurrencyBtn.addEventListener('click', this.validateGateway, false)
          }
        }

        const doarBtn = this.dom.getElementById('give-purchase-button')
        doarBtn.setAttribute('disabled', '')

        let alertMessage

        if (divPix) {
          alertMessage = 'Attention! It will not be possible to make recurring donations via Pix..'
        }
        if (divBol) {
          alertMessage = 'Attention! It will not be possible to make recurring donations via Boleto.'
        }

        alert(alertMessage)
      }
    }

    appendDonateBtn() {
      const doarBtnWrap = this.dom.getElementsByClassName('give-submit-button-wrap')[0]
      const divPix = this.dom.getElementById('paghiper-pix-div')
      const divBol = this.dom.getElementById('paghiper-slip-div')

      // Appen the donate button in checkout area.
      if (doarBtnWrap) {
        if (divPix) {
          divPix.append(doarBtnWrap)
        }
        if (divBol) {
          divBol.append(doarBtnWrap)
        }
      }
    }
  }

  $(window).on('load', () => {
    const LKN_PIX_FEE = window.lknPaghiperGlobals.pix_fee
    const LKN_BOL_FEE = window.lknPaghiperGlobals.bol_fee

    const lknPaghiperiframeLoader = document.getElementsByClassName('iframe-loader')[0]
    const lknPaghiperform = document.getElementsByClassName('give-form')[0]

    if (lknPaghiperiframeLoader) {
      const lknPaghiperiframe = document.getElementById('iFrameResizer0')
      const dom = lknPaghiperiframe.contentDocument
      const lknPaghiperPaymethod = new LknPaghiperPayment(dom)
      const lknPaghiperCpfCnpjInput = dom.getElementById('lkn_give_paghiper_cpf_cnpj_input')

      lknPaghiperPaymethod.setInfoBanner(LKN_PIX_FEE, LKN_BOL_FEE)

      lknPaghiperPaymethod.appendDonateBtn()

      lknPaghiperPaymethod.checkRecurrency()

      if (lknPaghiperCpfCnpjInput) {
        lknPaghiperPaymethod.defineCpfCnpjField(lknPaghiperCpfCnpjInput)
      }

      dom.addEventListener('give_gateway_loaded', (event) => {
        const selectedGateway = event.detail.selectedGateway
        const lknPaghiperCpfCnpjInput = dom.getElementById('lkn_give_paghiper_cpf_cnpj_input')

        if (selectedGateway === 'lkn-give-paghiper-pix' || selectedGateway === 'lkn-give-paghiper-slip') {
          lknPaghiperPaymethod.setInfoBanner(LKN_PIX_FEE, LKN_BOL_FEE)

          lknPaghiperPaymethod.appendDonateBtn()

          lknPaghiperPaymethod.checkRecurrency()

          if (lknPaghiperCpfCnpjInput) {
            lknPaghiperPaymethod.defineCpfCnpjField(lknPaghiperCpfCnpjInput)
          }
        }
      })
    } else if (lknPaghiperform) {
      const lknPaghiperPaymethod = new LknPaghiperPayment(document)
      const lknPaghiperCpfCnpjInput = document.getElementById('lkn_give_paghiper_cpf_cnpj_input')

      lknPaghiperPaymethod.setInfoBanner(LKN_PIX_FEE, LKN_BOL_FEE)

      lknPaghiperPaymethod.appendDonateBtn()

      lknPaghiperPaymethod.checkRecurrency()

      if (lknPaghiperCpfCnpjInput) {
        lknPaghiperPaymethod.defineCpfCnpjField(lknPaghiperCpfCnpjInput)
      }

      document.addEventListener('give_gateway_loaded', (event) => {
        const selectedGateway = event.detail.selectedGateway
        const lknPaghiperCpfCnpjInput = document.getElementById('lkn_give_paghiper_cpf_cnpj_input')

        if (selectedGateway === 'lkn-give-paghiper-pix' || selectedGateway === 'lkn-give-paghiper-slip') {
          lknPaghiperPaymethod.setInfoBanner(LKN_PIX_FEE, LKN_BOL_FEE)

          lknPaghiperPaymethod.appendDonateBtn()

          lknPaghiperPaymethod.checkRecurrency()

          if (lknPaghiperCpfCnpjInput) {
            lknPaghiperPaymethod.defineCpfCnpjField(lknPaghiperCpfCnpjInput)
          }
        }
      })
    }
  })

  // eslint-disable-next-line no-undef
})(jQuery)
