const gateway = {
  id: 'pix-payment-gateway',
  async initialize() {
    // Aqui vai todas as funções necessárias ao carregar a página de pagamento
  },
  async beforeCreatePayment(values) {
    // Aqui vai tudo que precisa rodar depois de submeter o formulário e antes do pagamento ser completado
    // Ponha validações e adicione atributos que você vai precisar no back-end aqui

    // Caso detecte algum erro de validação você pode adicionar uma exceção
    // A mensagem de erro aparecerá para o cliente já formatada
    if (values.firstname === 'error') {
      throw new Error('Gateway failed')
    }

    // Retorna os atributos usados pelo back-end
    // Atributos do objeto value já são passados por padrão
    return {
      pluginIntent: 'lkn-plugin-intent',
      custom: 'anything'
    }
  },
  async afterCreatePayment(response) {
    // Aqui roda tudo que você precisa após o formulário ser submetido
    // Antes de ir para a tela do comprovante de pagamento
  },
  // Função onde os campos HTML são criados
  Fields() {
    return /* #__PURE__ */React.createElement('div', {
      style: {
        textAlign: 'center',
        maxWidth: '648px'
      }
    }, /* #__PURE__ */React.createElement('input', {
      type: 'hidden',
      id: 'pix_type',
      value: lknAttr.pixType
    }), /* #__PURE__ */React.createElement('input', {
      type: 'hidden',
      id: 'pix_key',
      value: lknAttr.pixKey
    }), /* #__PURE__ */React.createElement('input', {
      type: 'hidden',
      id: 'pix_name',
      value: lknAttr.pixName
    }), /* #__PURE__ */React.createElement('input', {
      type: 'hidden',
      id: 'pix_city',
      value: lknAttr.pixCity
    }), /* #__PURE__ */React.createElement('h3', null, 'Chave Pix:'), /* #__PURE__ */React.createElement('p', {
      id: 'qr'
    }), /* #__PURE__ */React.createElement('p', {
      id: 'pix'
    }), /* #__PURE__ */React.createElement('p', {
      id: 'copy-pix'
    }))
  }
}
window.givewp.gateways.register(gateway)
