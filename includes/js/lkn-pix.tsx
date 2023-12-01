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
            throw new Error('Gateway failed');
        }

        // Retorna os atributos usados pelo back-end
        // Atributos do objeto value já são passados por padrão
        return {
            pluginIntent: 'lkn-plugin-intent',
            custom: 'anything'
        };
    },
    async afterCreatePayment(response) {
        // Aqui roda tudo que você precisa após o formulário ser submetido
        // Antes de ir para a tela do comprovante de pagamento
    },
    // Função onde os campos HTML são criados
    Fields() {
        return (
            <div style={{ textAlign: 'center' }}>
                <p><img src={'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' + encodeURIComponent(lknAttr.pixKey)} alt={'QR Code for ' + lknAttr.pixKey} /></p>
                <h3>Chave Pix:</h3>
                <p>{lknAttr.pixKey}</p>
                <p>
                    <button onclick={"navigator.clipboard.writeText(" + lknAttr.pixKey + ")"}>Copy Pix Code</button>
                </p>
                <p>Payment Gateway Pix for GiveWP</p>
            </div>
        )
    }
};

window.givewp.gateways.register(gateway);
