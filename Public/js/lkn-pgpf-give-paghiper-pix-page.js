(function ($) {
  'use strict'

  const lkn_pgpf_paghiper_REDIRECT = window.pixPageGlobals.redirect
  const lkn_pgpf_paghiper_REDIRECT_URL = window.pixPageGlobals.redirect_url
  const lkn_pgpf_paghiper_DON_VALUE = window.pixPageGlobals.don_value

  $(window).on('load', function () {
    let firstRequest = true
    let time = 30
    let attempt = 5
    let activeButton = true

    if (lkn_pgpf_paghiper_REDIRECT === '1') {
      parent.window.location.replace(lkn_pgpf_paghiper_REDIRECT_URL)
    }

    const formatter = new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    })

    const value = formatter.format(lkn_pgpf_paghiper_DON_VALUE)

    const apiUrl = pixPageGlobals.page_url + '/wp-json/pgpfpaghiper/v1/status'

    $(document).ready(function ($) {
      const transactionId = $('#transactionId').val()
      const donationId = $('#donationId').val()

      function checkPaymentStatus() {
        if (attempt !== 0) {
          attempt -= 1
        }
        $('.schedule_text').text('Proxima verificação em (N. tentativas ' + attempt + '):')
        $.ajax({
          url: apiUrl,
          type: 'POST',
          headers: {
            Accept: 'application/json'
          },
          data: {
            transaction_id: transactionId,
            donationId: pixPageGlobals.donationId
          },
          success: function (response) {
            if (response.status === 'success' || response.status === 'completed' || response.status === 'paid') {
              clearInterval(paymentTimer)
              // Cria a nova div estilizada
              let html = '<div id="pix_success_container" style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:auto;height:auto;">';
              html += '<div class="pix-success-message" style="text-align:center;font-size:1.2em;color:#28a428;padding:20px 0;">' + (response.status === 'success' ? response.message : 'Pagamento Realizado com sucesso!') + '</div>';
              if (response.redirect_url) {
                html += '<button id="pix-receipt-btn" style="margin-top:20px;padding:10px 20px;font-size:16px;background-color:rgb(58,58,58);color:#fff;border:none;border-radius:5px;cursor:pointer;">Ver Recibo do Pagamento</button>';
              }
              html += '</div>';
              // Insere a nova div abaixo das divs existentes
              var $copyContainer = $('#copy_container');
              var $qrCodeContainer = $('#pix_page_qr_code');
              if ($qrCodeContainer.length) {
                $qrCodeContainer.after(html);
              } else if ($copyContainer.length) {
                $copyContainer.after(html);
              } else {
                // Se não encontrar, adiciona ao body
                $('body').append(html);
              }
              // Remove as divs antigas
              $copyContainer.remove();
              $qrCodeContainer.remove();
              // Ajusta o botão de verificação, contador e texto
              var $checkPaymentBtn = $('.payment_check_button');
              $checkPaymentBtn.prop('disabled', true).removeAttr('style');
              $('#timer').text('0s');
              $('.schedule_text').text('Proxima verificação em (N. tentativas 0):');
              // Adiciona evento ao botão
              $('#pix-receipt-btn').on('click', function () {
                window.location.href = response.redirect_url;
              });
            }
          },
          error: function (xhr, status, error) {
            console.error('Erro ao verificar o status do pagamento:', error)
          }
        })
      }

      const paymentTimer = setInterval(function () {
        if (firstRequest) {
          firstRequest = false
          time = 60
          activeButton = true
        }

        time -= 1

        const schedule = $('#timer')
        schedule.text(time + 's')

        if (time === 0) {
          if (activeButton) {
            const checkPayment = $('.payment_check_button')
            checkPayment.prop('disabled', false).css({ 'background-color': '#3A3A3A', cursor: 'pointer' }).addClass('back_hover_button')
            activeButton = false

            checkPayment.on('click', function () {
              const now = new Date()

              const formattedDate = now.getFullYear() + '/' +
                String(now.getMonth() + 1).padStart(2, '0') + '/' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0')

              checkPayment.text(formattedDate)
              checkPaymentStatus()
              if (attempt !== 0) {
                time = 30
              } else {
                time = 0
                clearInterval(paymentTimer)
                schedule.text('')
              }
              checkPayment.prop('disabled', true).css({ 'background-color': '#D9D9D9', cursor: 'not-allowed' }).removeClass('back_hover_button')

              setTimeout(function () {
                checkPayment.prop('disabled', false)
                  .css({
                    'background-color': '#3A3A3A',
                    cursor: 'pointer'
                  }).addClass('back_hover_button')

                checkPayment.text('Já paguei o PIX')
              }, 7000)
            })
          }
          checkPaymentStatus()
          if (attempt !== 0) {
            time = 30
          } else {
            time = 0
            clearInterval(paymentTimer)
            schedule.text('')
          }
        }
      }, 1000)
    })

    // Set the value on page:
    $('#pix_page_currency_text').html(value)

    const shareButton = $('.share_button')

    shareButton.on('click', function () {
      const pixLink2 = $('.input_copy_code')
      if (navigator.share) {
        navigator.share({
          title: 'Pagamento via PIX',
          text: pixLink2.val()
        })
      } else {
        alert('Compartilhamento não é suportado neste navegador.')
      }
    })

    const copyLink = $('.button_copy_code')

    copyLink.on('click', function () {
      const pixLink = $('.input_copy_code')

      navigator.clipboard.writeText(pixLink.val())
      copyLink.text('COPIADO')
      copyLink.prop('disabled', true).css({ 'background-color': '#28a428', cursor: 'not-allowed' })

      setTimeout(function () {
        copyLink.prop('disabled', false)
          .css({
            'background-color': '#3A3A3A',
            cursor: 'pointer'
          })

        copyLink.text('COPIAR')
      }, 3000)
    })
  })

  // eslint-disable-next-line no-undef
})(jQuery)
