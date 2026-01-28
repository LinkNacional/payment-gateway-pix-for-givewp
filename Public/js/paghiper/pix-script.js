(() => {
  const LKN_PIX_TITLE = pixGlobals.title
  const LKN_PIX_INFO = pixGlobals.info
  const LKN_PIX_CPF_CNPJ_LABEL = pixGlobals.cpf_cnpj_label
  const LKN_PIX_CPF_CNPJ_TOOLTIP = pixGlobals.cpf_cnpj_tooltip
  const LKN_PIX_ASTR_SYMB = pixGlobals.astr_symbol
  const LKN_PIX_FEE = pixGlobals.pix_fee
  function lknPixEraseFormat(inputText) {
    inputText.value = inputText.value.replace(/(\.|\/|\-)/g, "");
  }

  function lknPixFormatInput(textInput) {
    textInput.value = textInput.value.replace(/\D/g, '');
    if (textInput.value.length <= 11) {
      textInput.value = lknPixMaskCpf(textInput.value);
    } else {
      textInput.value = lknPixMaskCnpj(textInput.value);
    }
  }

  function lknPixMaskCpf(cpfText) {
    return cpfText.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, "$1.$2.$3-$4");
  }

  function lknPixMaskCnpj(cnpjText) {
    return cnpjText.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, "$1.$2.$3/$4-$5");
  }

  function lknPixValidateCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

    let sum = 0;
    for (let i = 0; i < 9; i++) {
      sum += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.charAt(9))) return false;

    sum = 0;
    for (let i = 0; i < 10; i++) {
      sum += parseInt(cpf.charAt(i)) * (11 - i);
    }
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    return remainder === parseInt(cpf.charAt(10));
  }

  function lknPixValidateCNPJ(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;

    let sum = 0;
    let weight = 2;
    for (let i = 11; i >= 0; i--) {
      sum += parseInt(cnpj.charAt(i)) * weight;
      weight = weight === 9 ? 2 : weight + 1;
    }
    let remainder = sum % 11;
    let digit1 = remainder < 2 ? 0 : 11 - remainder;
    if (parseInt(cnpj.charAt(12)) !== digit1) return false;

    sum = 0;
    weight = 2;
    for (let i = 12; i >= 0; i--) {
      sum += parseInt(cnpj.charAt(i)) * weight;
      weight = weight === 9 ? 2 : weight + 1;
    }
    remainder = sum % 11;
    let digit2 = remainder < 2 ? 0 : 11 - remainder;
    return parseInt(cnpj.charAt(13)) === digit2;
  }

  function lknPixIsValidIdenty(identy) {
    const cleanIdenty = identy.replace(/\D/g, '');
    if (cleanIdenty.length === 11) {
      return lknPixValidateCPF(cleanIdenty);
    } else if (cleanIdenty.length === 14) {
      return lknPixValidateCNPJ(cleanIdenty);
    }
    return false;
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
      maxlength: '18',
      style: {
        fontSize: '16px'
      },
      onFocus: e => {
        lknPixEraseFormat(e.target);
      },
      onChange: e => {
        lknPixFormatInput(e.target);
        if (props.onInputChange) {
          props.onInputChange(e.target.value);
        }
      },
      onInput: e => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (props.onInputChange) {
          props.onInputChange(e.target.value);
        }
      }
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

      // Validar CPF/CNPJ antes de enviar
      if (!lknPixIsValidIdenty(cpfCnpj)) {
        // Definir erro na UI ao invés de lançar exceção
        if (window.setPixError) {
          window.setPixError('CPF/CNPJ Inválido! Tente novamente.');
        }
        throw new Error('CPF/CNPJ inválido!')
      }

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
      const [showMessage, setShowMessage] = React.useState(false)
      const [hasError, setHasError] = React.useState(false)
      const [errorMessage, setErrorMessage] = React.useState('')

      // Expor função para definir erro externamente
      React.useEffect(() => {
        window.setPixError = (message) => {
          setHasError(true);
          setErrorMessage(message);
          setShowMessage(false);
        };
        return () => {
          delete window.setPixError;
        };
      }, []);

      const PixFeeInfo = React.createElement(lknPixFeeInfoElement)
      const PrimaryDocumentInput = React.createElement(lknPixElementWithTooltip, {
        title: LKN_PIX_CPF_CNPJ_TOOLTIP,
        tooltipClass: 'lkn_cpf_cnpj_tooltip',
        margin: '-45px',
        onInputChange: (value) => {
          const cleanValue = value.replace(/\D/g, '');

          // Limpar erro quando usuário alterar o input
          if (hasError) {
            setHasError(false);
            setErrorMessage('');
          }

          // Mostrar mensagem apenas se tiver tamanho correto (não validar aqui)
          setShowMessage(cleanValue.length === 11 || cleanValue.length === 14);
        }
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
      }, ' ' + LKN_PIX_CPF_CNPJ_LABEL + ' ' + LKN_PIX_ASTR_SYMB, PrimaryDocumentInput), showMessage && /* #__PURE__ */React.createElement('p', {
        style: {
          textAlign: 'center',
          marginTop: '15px',
          color: '#415462',
          fontSize: '14px',
          fontWeight: 'bold'
        }
      }, 'Clique em "Doar Agora" para gerar o QrCode'), hasError && /* #__PURE__ */React.createElement('p', {
        style: {
          textAlign: 'center',
          marginTop: '15px',
          color: '#d63384',
          fontSize: '14px',
          fontWeight: 'bold'
        }
      }, errorMessage))))
    }
  }
  window.givewp.gateways.register(LknPixGateway)
})()
