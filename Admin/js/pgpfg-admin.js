/* eslint-disable no-undef */
const { __ } = wp.i18n;

(function ($) {
  'use strict'

  /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

  $(window).load(function () {
    const adminPage = lknFindGetParameter('section')

    if (adminPage && (adminPage === 'lkn-payment-pix')) {
      const giveForm = $('.give-submit-wrap')
      const noticeDiv = document.createElement('div')
      noticeDiv.setAttribute('style', 'padding: 10px; padding-top:15px;padding-bottom:15px;background-color: #fcf9e8;color: #646970;border: solid 1px lightgrey;border-left-color: #dba617;border-left-width: 4px;font-size: 14px;min-width: 625px;margin-top: 10px;min-width:300px;')

      noticeDiv.innerHTML = '<div style="font-size: 21px;">' + __('Enjoying the plugin?', 'payment-gateway-pix-for-givewp') + '</div>'
      noticeDiv.innerHTML += '<p style="font-size: 14px;">' + __('Try out our other GiveWP plugins', 'payment-gateway-pix-for-givewp') + '</p'
      noticeDiv.innerHTML += '<ul style="margin: 10px; list-style: disclosure-closed;">'
      noticeDiv.innerHTML += '<li>' + __('Cielo API Credit Card Payment', 'payment-gateway-pix-for-givewp') + '</li>'
      noticeDiv.innerHTML += '<li>' + __('Google Pay Payment', 'payment-gateway-pix-for-givewp') + '</li>'
      noticeDiv.innerHTML += '<li>' + __('Bitcoin and Ethereum Payment', 'payment-gateway-pix-for-givewp') + '</li>'
      noticeDiv.innerHTML += '<li>' + __('International Donations using foreign currencies such as Euro or Yen', 'payment-gateway-pix-for-givewp') + '</li>'
      noticeDiv.innerHTML += '<li>' + __('And more!', 'payment-gateway-pix-for-givewp') + '</li>'
      noticeDiv.innerHTML += '</ul>'
      noticeDiv.innerHTML += '<a style="margin: 18px; padding-right: 0px; margin-top: 20px;" href="https://www.linknacional.com.br/wordpress/givewp/" target="_blank">' + __('Learn more now', 'payment-gateway-pix-for-givewp') + '</a>'

      const lknCieloNoticeDiv = document.createElement('div')
      lknCieloNoticeDiv.setAttribute('style', 'background-color: #fcf9e8;color: #646970;border: solid 1px #d3d3d3;border-left: 4px #dba617 solid;font-size: 16px;margin-top: 10px;')
      lknCieloNoticeDiv.setAttribute('id', 'lkn-cielo-hosting-notice')

      lknCieloNoticeDiv.innerHTML = '<a  href="https://www.linknacional.com.br/wordpress/" target="_blank" style="text-decoration:none; display: block;padding: 10px;">' + __('WordPress Maintenance and Support!', 'payment-gateway-pix-for-givewp') + '</a>'

      if (giveForm && lknCieloNoticeDiv) {
        const parent = giveForm.closest('div#pgpfgSettingsFlexContainer')
        parent.after(noticeDiv)
        noticeDiv.after(lknCieloNoticeDiv)

        const linkInNoticeDiv = noticeDiv.getElementsByTagName('a')
        const linkLknCieloInNoticeDiv = lknCieloNoticeDiv.getElementsByTagName('a')

        setDarkCss(linkInNoticeDiv, linkLknCieloInNoticeDiv, noticeDiv, lknCieloNoticeDiv)
        if (typeof WPDarkMode !== 'undefined') {
          WPDarkMode.onChange(() => {
            if (WPDarkMode.isActive) {
              setDarkCss(linkInNoticeDiv, linkLknCieloInNoticeDiv, noticeDiv, lknCieloNoticeDiv)
            } else {
              const linkInNoticeDiv = noticeDiv.getElementsByTagName('a')
              const linkLknCieloInNoticeDiv = lknCieloNoticeDiv.getElementsByTagName('a')

              if (linkInNoticeDiv && linkLknCieloInNoticeDiv) {
                for (let i = 0; i < linkInNoticeDiv.length; i++) {
                  linkInNoticeDiv[i].style.color = '#2271b1'
                }
                for (let i = 0; i < linkLknCieloInNoticeDiv.length; i++) {
                  linkLknCieloInNoticeDiv[i].style.color = '#2271b1'
                }
              }

              noticeDiv.style.color = '#646970'
              noticeDiv.style.backgroundColor = '#fcf9e8'
              lknCieloNoticeDiv.style.color = '#646970'
              lknCieloNoticeDiv.style.backgroundColor = '#fcf9e8'
            }
          })
        }
      }
      const checkLogs = $('#check-logs')

      if (checkLogs) {
        checkLogs.on('click', (ev) => {
          const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(lknAttr.logContents)

          checkLogs.attr('href', dataStr)
          checkLogs.attr('download', 'pix_payment.log')
        })
      }
    }

    function setDarkCss(linkInNoticeDiv, linkLknCieloInNoticeDiv, noticeDiv, lknCieloNoticeDiv) {
      if (typeof WPDarkMode !== 'undefined') {
        if (WPDarkMode.isActive) {
          noticeDiv.style.color = 'white'
          noticeDiv.style.backgroundColor = '#292a2a'
          lknCieloNoticeDiv.style.backgroundColor = '#292a2a'
          lknCieloNoticeDiv.style.color = 'white'

          if (linkInNoticeDiv && linkLknCieloInNoticeDiv) {
            for (let i = 0; i < linkInNoticeDiv.length; i++) {
              linkInNoticeDiv[i].style.color = 'lightblue'
            }
            for (let i = 0; i < linkLknCieloInNoticeDiv.length; i++) {
              linkLknCieloInNoticeDiv[i].style.color = 'lightblue'
            }
          }
        }
      }
    }

    function lknFindGetParameter(parameterName) {
      let result = null
      let tmp = []
      location.search
        .substr(1)
        .split('&')
        .forEach(function (item) {
          tmp = item.split('=')
          if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1])
        })
      return result
    }

    function createFeatureMessage(iconText, messageLines) {
      const featureMessage = document.createElement('div');
      featureMessage.className = 'custom-feature-message';

      // Adiciona o ícone de informação
      const infoIcon = document.createElement('span');
      infoIcon.className = 'feature-icon';
      infoIcon.textContent = iconText;

      // Adiciona o texto da mensagem
      const textContainer = document.createElement('div');
      textContainer.className = 'feature-text-container';

      // Adiciona as linhas de texto
      messageLines.forEach(line => {
        const messageLine = document.createElement('span');
        messageLine.className = 'feature-text-line';
        messageLine.innerHTML = line;
        textContainer.appendChild(messageLine);
      });

      // Adiciona o ícone e o texto ao componente de mensagem
      featureMessage.appendChild(infoIcon);
      featureMessage.appendChild(textContainer);

      return featureMessage;
    }
    const featureMessage1 = createFeatureMessage('✔️', [
      '<strong>ATUALIZADO:</strong> Fluxo de Pagamento otimizado. Cada etapa foi refinada para uma experiência mais suave e rápida para seus doadores.'
    ]);

    // Cria o segundo bloco de mensagem
    const featureMessage2 = createFeatureMessage('✔️', [
      '<strong>NOVO:</strong> Opção de gerar QR Code na tela de agradecimento, permitindo que os doadores concluam o pagamento mesmo após sair da página de checkout.'
    ]);

    const featureMessage3 = createFeatureMessage('✔️', [
      '<strong>NOVO:</strong> Webhook para Gateways: Rede, Cielo e Banco do Brasil. Mais segurança e confiabilidade na confirmação dos pagamentos.'
    ]);
    const flexWrapper = document.getElementsByClassName('lkn-card-container')[0];
    flexWrapper.appendChild(featureMessage1);
    flexWrapper.appendChild(featureMessage2);
    flexWrapper.appendChild(featureMessage3);
  })
})(jQuery)