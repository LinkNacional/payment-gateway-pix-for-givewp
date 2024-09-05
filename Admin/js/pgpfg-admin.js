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

    if (adminPage && adminPage === 'lkn-payment-pix') {
      const giveForm = $('.give-submit-wrap');

      if (giveForm.length > 0) {
        // Criar e estilizar o noticeDiv
        const noticeDiv = document.createElement('div');
        noticeDiv.setAttribute('style', 'padding: 10px; padding-top: 15px; padding-bottom: 15px; background-color: #fcf9e8; color: #646970; border: solid 1px lightgrey; border-left-color: #dba617; border-left-width: 4px; font-size: 14px; min-width: 625px; margin-top: 10px;');

        // Criar o conteúdo do noticeDiv
        const title = document.createElement('div');
        title.style.fontSize = '21px';
        title.innerText = __('Enjoying the plugin?', 'payment-gateway-pix-for-givewp');
        noticeDiv.appendChild(title);

        const description = document.createElement('p');
        description.style.fontSize = '14px';
        description.innerText = __('Try out our other GiveWP plugins', 'payment-gateway-pix-for-givewp');
        noticeDiv.appendChild(description);

        // Criar a lista de plugins
        const ul = document.createElement('ul');
        ul.style.margin = '0'; // Remover margem externa da lista
        ul.style.padding = '0'; // Remover preenchimento externo da lista
        ul.style.listStyle = 'none'; // Remover estilo de lista padrão

        const plugins = [
          __('Cielo API Credit Card Payment', 'payment-gateway-pix-for-givewp'),
          __('Google Pay Payment', 'payment-gateway-pix-for-givewp'),
          __('Bitcoin and Ethereum Payment', 'payment-gateway-pix-for-givewp'),
          __('International Donations using foreign currencies such as Euro or Yen', 'payment-gateway-pix-for-givewp'),
          __('And more!', 'payment-gateway-pix-for-givewp')
        ];

        plugins.forEach(plugin => {
          const li = document.createElement('li');
          li.innerText = plugin;
          li.style.marginLeft = '0'; // Remover margem à esquerda para cada item
          ul.appendChild(li);
        });

        noticeDiv.appendChild(ul);

        const learnMoreLink = document.createElement('a');
        learnMoreLink.href = 'https://www.linknacional.com.br/wordpress/givewp/';
        learnMoreLink.target = '_blank';
        learnMoreLink.style.margin = '18px';
        learnMoreLink.style.paddingRight = '0px';
        learnMoreLink.style.marginTop = '20px';
        learnMoreLink.innerText = __('Learn more now', 'payment-gateway-pix-for-givewp');
        noticeDiv.appendChild(learnMoreLink);

        // Criar e estilizar o lknCieloNoticeDiv
        const lknCieloNoticeDiv = document.createElement('div');
        lknCieloNoticeDiv.setAttribute('style', 'background-color: #fcf9e8; color: #646970; border: solid 1px #d3d3d3; border-left: 4px #dba617 solid; font-size: 16px; margin-top: 10px;');
        lknCieloNoticeDiv.setAttribute('id', 'lkn-cielo-hosting-notice');

        const lknCieloLink = document.createElement('a');
        lknCieloLink.href = 'https://www.linknacional.com.br/wordpress/';
        lknCieloLink.target = '_blank';
        lknCieloLink.style.textDecoration = 'none';
        lknCieloLink.style.display = 'block';
        lknCieloLink.style.padding = '10px';
        lknCieloLink.innerText = __('WordPress Maintenance and Support!', 'payment-gateway-pix-for-givewp');
        lknCieloNoticeDiv.appendChild(lknCieloLink);

        // Adicionando os elementos ao DOM
        giveForm.after(noticeDiv);
        noticeDiv.after(lknCieloNoticeDiv);

        const linkInNoticeDiv = noticeDiv.getElementsByTagName('a');
        const linkLknCieloInNoticeDiv = lknCieloNoticeDiv.getElementsByTagName('a');

        function setDarkCss(links1, links2, div1, div2) {
          for (let i = 0; i < links1.length; i++) {
            links1[i].style.color = '#ffffff'; // Exemplo para o modo escuro
          }
          for (let i = 0; i < links2.length; i++) {
            links2[i].style.color = '#ffffff'; // Exemplo para o modo escuro
          }
          div1.style.color = '#ffffff'; // Exemplo para o modo escuro
          div1.style.backgroundColor = '#333333'; // Exemplo para o modo escuro
          div2.style.color = '#ffffff'; // Exemplo para o modo escuro
          div2.style.backgroundColor = '#333333'; // Exemplo para o modo escuro
        }

        if (typeof WPDarkMode !== 'undefined') {
          WPDarkMode.onChange(() => {
            if (WPDarkMode.isActive) {
              setDarkCss(linkInNoticeDiv, linkLknCieloInNoticeDiv, noticeDiv, lknCieloNoticeDiv);
            } else {
              for (let i = 0; i < linkInNoticeDiv.length; i++) {
                linkInNoticeDiv[i].style.color = '#2271b1'; // Cor padrão para o modo claro
              }
              for (let i = 0; i < linkLknCieloInNoticeDiv.length; i++) {
                linkLknCieloInNoticeDiv[i].style.color = '#2271b1'; // Cor padrão para o modo claro
              }
              noticeDiv.style.color = '#646970'; // Cor padrão para o modo claro
              noticeDiv.style.backgroundColor = '#fcf9e8'; // Cor padrão para o modo claro
              lknCieloNoticeDiv.style.color = '#646970'; // Cor padrão para o modo claro
              lknCieloNoticeDiv.style.backgroundColor = '#fcf9e8'; // Cor padrão para o modo claro
            }
          });
        }

        const checkLogs = $('#check-logs');

        if (checkLogs.length > 0) {
          checkLogs.on('click', (ev) => {
            const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(lknAttr.logContents);
            checkLogs.attr('href', dataStr);
            checkLogs.attr('download', 'pix_payment.log');
          });
        }
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
  })
})(jQuery)
