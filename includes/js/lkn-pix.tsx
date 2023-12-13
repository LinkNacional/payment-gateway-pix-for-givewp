import(lknAttr.pluginUrl + "public/css/payment-gateway-pix-for-givewp-public.css")
import "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"

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
            <div>
                <div id="lkn-pix-form-donation" >
                    <legend>Chave Pix:</legend>
                    <div class='pix-container'>
                        <p id='qr'>Carregando...</p>
                        <p id='pix' style={{ display: 'none' }} ></p>
                        <p id='copy-pix' >
                            <button id="toggle-viewing" type="button" title="Mostrar Pix">
                                <span id="show" class="material-symbols-outlined" style={{ display: "none" }}>visibility_off</span>
                                < span id="hide" class="material-symbols-outlined" > visibility</span>
                            </button>
                            <button id="copy-button" type="button" title="Copiar Pix">
                                <span class="material-symbols-outlined">content_copy</span>
                            </button>
                        </p>
                    </div>
                </div>
            </div >
        )
    }
};

window.givewp.gateways.register(gateway);
