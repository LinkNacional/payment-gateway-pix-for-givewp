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

    const apiUrl = pixPageGlobals.page_url + '/wp-admin/admin-ajax.php'

    $(document).ready(function ($) {
      const transactionId = $('#transactionId').val()
      const donationId = $('#donationId').val()

      // Get nonce from global variables
      const nonce = pixPageGlobals.status_check_nonce || ''

      function checkPaymentStatus() {
        if (attempt !== 0) {
          attempt -= 1
        }
        $('.schedule_text').text('Proxima verificação em (N. tentativas ' + attempt + '):')
        
        // Data is already base64 encoded from PHP, no need to encode again
        
        // Use generic action name for AJAX
        const actionName = 'pgpf_pix_status_check'
        
        // Create FormData
        const formData = new FormData()
        formData.append('action', actionName)
        formData.append('transaction_id', transactionId)
        formData.append('donation_id', donationId)
        formData.append('nonce', nonce)
        
        $.ajax({
          url: apiUrl,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function (response) {
            if (response.success && (response.data.status === 'success' || response.data.status === 'completed' || response.data.status === 'paid')) {
              clearInterval(paymentTimer)
              
              // Cria a nova div de sucesso usando classes CSS
              let html = '<div id="pix_success_container">';
              html += '<div class="pix-success-message">' + (response.data.message || 'Pagamento Realizado com sucesso!') + '</div>';
              if (response.data.redirect_url && response.data.redirect_url.trim() !== '') {
                html += '<button id="pix-receipt-btn">Ver Recibo do Pagamento</button>';
              }
              html += '</div>';
              
              // Encontra o container principal e insere o elemento de sucesso dentro dele
              var $mainContainer = $('.container_pix').last(); // Pega o último container_pix (onde fica o valor da doação)
              var $copyContainer = $('#copy_container');
              var $qrCodeContainer = $('#pix_page_qr_code');
              
              if ($mainContainer.length) {
                // Insere dentro do container principal, após o span de data
                var $dateSpan = $mainContainer.find('.span_date');
                if ($dateSpan.length) {
                  $dateSpan.after(html);
                } else {
                  $mainContainer.append(html);
                }
              } else if ($qrCodeContainer.length) {
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
              
              // Adiciona evento ao botão de recibo se o redirect_url existe
              if (response.data.redirect_url && response.data.redirect_url.trim() !== '') {
                $('#pix-receipt-btn').on('click', function () {
                  window.location.href = response.data.redirect_url;
                });
              }
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
