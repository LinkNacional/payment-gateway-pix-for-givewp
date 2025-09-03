(() => {
  const LKN_PIX_TITLE = pixGlobals.title
  const LKN_PIX_INFO = pixGlobals.info
  const LKN_PIX_CPF_CNPJ_LABEL = pixGlobals.cpf_cnpj_label
  const LKN_PIX_CPF_CNPJ_TOOLTIP = pixGlobals.cpf_cnpj_tooltip
  const LKN_PIX_ASTR_SYMB = pixGlobals.astr_symbol
  const LKN_PIX_FEE = pixGlobals.pix_fee
  function lknPixMaskCPFCNPJ(inputHTML) {
    let cpfCnpjInput = inputHTML.target.value.replace(/\D/gmi, '')
    const cpfCnpjArr = cpfCnpjInput.split('')
    const resultArr = []
    const typeInput = cpfCnpjInput.length > 11 ? 'CNPJ' : 'CPF'
    for (let c = 0; c < cpfCnpjInput.length; c++) {
      resultArr.push(cpfCnpjArr[c])
      if (typeInput === 'CPF') {
        if (c % 9 === 8) {
          resultArr.push('-')
        } else if (c % 3 === 2) {
          resultArr.push('.')
        }
      } else {
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
    }
    cpfCnpjInput = resultArr.join('')
    inputHTML.target.value = cpfCnpjInput
  }
  ;
  function lknPixFormatNumbers(inputHTML) {
    inputHTML.target.value = inputHTML.target.value.replace(/\D/gmi, '')
  }
  ;
  const lknPixElementWithTooltip = props => {
    const [visible, setVisible] = React.useState(false)
    const tooltipText = props.title
    let backgColor
    let textColor
    let verticalIndex
    const mouseEnter = () => {
      setVisible(true)
    }
    const mouseLeave = () => {
      setVisible(false)
    }
    if (visible === true) {
      backgColor = 'rgb(76 76 76)'
      textColor = '#ffffff'
      verticalIndex = 99999
    } else {
      backgColor = 'transparent'
      textColor = 'transparent'
      verticalIndex = 0
    }
    return /* #__PURE__ */React.createElement('p', {
      className: 'lkn_cpf_cnpj_input_container',
      style: {
        textAlign: 'center'
      }
    }, /* #__PURE__ */React.createElement('div', {
      className: props.tooltipClass,
      style: {
        display: 'inline-block',
        paddingBottom: '20px',
        width: '200px',
        height: 'auto',
        minHeight: '35px',
        backgroundColor: backgColor,
        color: textColor,
        textAlign: 'center',
        fontSize: '12px',
        padding: '4px',
        whiteSpace: 'break-spaces',
        marginTop: props.margin,
        zIndex: verticalIndex
      }
    }, tooltipText), /* #__PURE__ */React.createElement('div', {
      onMouseEnter: mouseEnter,
      onMouseLeave: mouseLeave,
      style: {
        display: 'flex',
        justifyContent: 'space-evenly',
        marginTop: '5px',
        padding: '2px',
        zIndex: 1
      }
    }, /* #__PURE__ */React.createElement('input', {
      type: 'tel',
      autocomplete: 'off',
      id: 'lkn_pgpf_give_paghiper_primary_document',
      placeholder: LKN_PIX_CPF_CNPJ_LABEL,
      required: 'true',
      'aria-required': 'true',
      maxlength: '20',
      style: {
        fontSize: '16px'
      },
      onInput: e => lknPixFormatNumbers(e),
      onBlur: e => lknPixMaskCPFCNPJ(e)
    })))
  }
  const lknPixFeeInfoElement = () => {
    let info
    if (LKN_PIX_FEE === '1') {
      info = LKN_PIX_INFO
    } else {
      info = ''
    }
    return /* #__PURE__ */React.createElement('h2', {
      id: 'lkn_pgpf_give_paghiper_info',
      style: {
        fontSize: '18px'
      }
    }, info)
  }
  const LknPixGateway = {
    id: 'lkn-pgpf-give-paghiper-pix',
    // The function that runs when form is opened.
    async initialize() {
      // Function to remove event bubling on value buttons.
      document.addEventListener('DOMContentLoaded', () => {
        const inputCustomAmount = document.querySelectorAll('.givewp-fields-amount__level')
        const emptyEvent = event => { }
        for (let i = 0; i < inputCustomAmount.length; i++) {
          inputCustomAmount[i].removeEventListener('click', emptyEvent, false)
          inputCustomAmount[i].addEventListener('click', emptyEvent, false)
        }
      })
    },
    // The function that runs when form is submited but not completed.
    async beforeCreatePayment(values) {
      const cpfCnpjField = document.querySelectorAll('#lkn_pgpf_give_paghiper_primary_document')[0]
      const cpfCnpj = cpfCnpjField.value
      values.cpfCnpj = cpfCnpj

      // Example of validation.
      if (values.firstName === 'error') {
        throw new Error('Gateway failed')
      }
      return {
        lkn_give_primary_document: cpfCnpj
      }
    },
    // The function that runs when form is submited, completed, but before show the payment proof.
    async afterCreatePayment(response) { },
    // Function that handle the HTML form elements.
    Fields() {
      const PixFeeInfo = React.createElement(lknPixFeeInfoElement)
      const PrimaryDocumentInput = React.createElement(lknPixElementWithTooltip, {
        title: LKN_PIX_CPF_CNPJ_TOOLTIP,
        tooltipClass: 'lkn_cpf_cnpj_tooltip',
        margin: '-45px'
      })
      return /* #__PURE__ */React.createElement('fieldset', {
        className: 'no-fields'
      }, /* #__PURE__ */React.createElement('div', {
        style: {
          display: 'flex',
          justifyContent: 'center',
          flexDirection: 'column',
          alignItems: 'center'
        }
      }, /* #__PURE__ */React.createElement('legend', {
        style: {
          marginBottom: '30px'
        }
      }, /* #__PURE__ */React.createElement('strong', {
        style: {
          fontSize: '22px'
        }
      }, LKN_PIX_TITLE)), /* #__PURE__ */React.createElement('div', {
        id: 'lkn_pgpf_paghiper_wrapper',
        style: {
          width: '100%'
        }
      }, PixFeeInfo, /* #__PURE__ */React.createElement('div', {
        id: 'lkn_pgpf_give_paghiper_cpf_cnpj',
        style: {
          fontSize: '14px',
          marginTop: '30px'
        }
      }, ' ' + LKN_PIX_CPF_CNPJ_LABEL + ' ' + LKN_PIX_ASTR_SYMB, PrimaryDocumentInput))))
    }
  }
  window.givewp.gateways.register(LknPixGateway)
})()
