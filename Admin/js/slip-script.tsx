(() => {
    const LKN_SLIP_TITLE = bolGlobals.title
    const LKN_SLIP_INFO = bolGlobals.info
    const LKN_SLIP_CPF_CNPJ_LABEL = bolGlobals.cpf_cnpj_label
    const LKN_SLIP_CPF_CNPJ_TOOLTIP = bolGlobals.cpf_cnpj_tooltip
    const LKN_SLIP_ASTR_SYMB = bolGlobals.astr_symbol
    const LKN_SLIP_FEE = bolGlobals.bol_fee

    function lknSlipMaskCPFCNPJ(inputHTML) {
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
    };

    function lknSlipFormatNumbers(inputHTML) {
        inputHTML.target.value = inputHTML.target.value.replace(/\D/gmi, '')
    };

    const lknSlipElementWithTooltip = (props) => {
        const [visible, setVisible] = React.useState(false)
        let tooltipText = props.title
        let backgColor: string
        let textColor: string
        let verticalIndex: number

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

        return (
            <p
                className="lkn_cpf_cnpj_input_container"
                style={{ textAlign: 'center' }}
            >
                <div
                    className={props.tooltipClass}
                    style={{
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
                    }}
                >
                    {tooltipText}
                </div>
                <div
                    onMouseEnter={mouseEnter}
                    onMouseLeave={mouseLeave}
                    style={{ display: 'flex', justifyContent: 'space-evenly', marginTop: '5px', padding: '2px', zIndex: 1 }}
                >
                    <input
                        type="tel"
                        autocomplete="off"
                        id="lkn_pgpf_give_paghiper_primary_document"
                        placeholder={LKN_SLIP_CPF_CNPJ_LABEL}
                        required="true"
                        aria-required="true"
                        maxlength="20"
                        style={{ fontSize: '16px' }}
                        onInput={(e) => lknSlipFormatNumbers(e)}
                        onBlur={(e) => { lknSlipMaskCPFCNPJ(e) }}
                    />
                </div >
            </p >
        )
    }

    const FeeInfoElement = () => {
        let info: string

        if (LKN_SLIP_FEE === '1') {
            info = LKN_SLIP_INFO
        } else {
            info = ''
        }

        return (
            <h2 id="lkn_pgpf_give_paghiper_info" style={{ fontSize: '18px' }}>{info}</h2>
        )
    }

    const LknBolGateway = {
        id: 'lkn-pgpf-give-paghiper-slip',

        // The function that runs when form is opened.
        async initialize() {
            // Function to remove event bubling on value buttons.
            document.addEventListener('DOMContentLoaded', () => {
                const inputCustomAmount = document.querySelectorAll('.givewp-fields-amount__level')

                const emptyEvent = (event) => {
                }

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
                'lkn_give_primary_document': cpfCnpj
            }
        },

        // The function that runs when form is submited, completed, but before show the payment proof.
        async afterCreatePayment(response) {
        },

        // Function that handle the HTML form elements.
        Fields() {
            const BolFeeInfo = React.createElement(FeeInfoElement)

            const PrimaryDocumentInput = React.createElement(lknSlipElementWithTooltip, {
                title: LKN_SLIP_CPF_CNPJ_TOOLTIP,
                tooltipClass: 'lkn_cpf_cnpj_tooltip',
                margin: '-45px',
            })

            return (
                <fieldset className="no-fields">
                    <div style={{ display: 'flex', justifyContent: 'center', flexDirection: 'column', alignItems: 'center' }}>
                        <legend style={{ marginBottom: '30px' }}>
                            <strong style={{ fontSize: '22px' }}>
                                {LKN_SLIP_TITLE}
                            </strong>
                        </legend>

                        <div id="lkn_pgpf_paghiper_wrapper" style={{ width: '100%' }}>
                            {BolFeeInfo}

                            <div id="lkn_pgpf_give_paghiper_cpf_cnpj" style={{ fontSize: '14px', marginTop: '30px' }}>
                                {' ' + LKN_SLIP_CPF_CNPJ_LABEL + ' ' + LKN_SLIP_ASTR_SYMB}

                                {PrimaryDocumentInput}
                            </div>
                        </div>
                    </div>
                </fieldset>
            )
        }
    }

    window.givewp.gateways.register(LknBolGateway)
})()