$(function(){

  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

	$("#wizard").steps({
        headerTag: "h2",
        bodyTag: "section",
        transitionEffect: "fade",
        enableAllSteps: true,
        transitionEffectSpeed: 500,
        labels: {
            finish: "Finalizar",
            next: "Próximo",
            previous: "Anterior"
        },
        onStepChanging: function (event, currentIndex, newIndex) {
            if (currentIndex == 0) {
                if ($("#name").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o seu nome",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#email").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o seu e-mail",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#cpf").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o seu CPF",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#password").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha a sua senha",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#password_confirmation").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Confirme a sua senha",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#password_confirmation").val() != $("#password").val()) {
                    swal({
                      title: "Desculpe",
                      text: "As senhas precisam ser idênticas",
                      icon: "error",
                    });

                    return false;
                }
            }

            if (currentIndex == 1) {
                if ($("#rua").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o nome da Rúa ou Avenida",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#numero").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o número da Rúa ou Avenida",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#bairro").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o nome do bairro",
                      icon: "error",
                    });

                    return false;
                }

                if ($("#cep").val() == '') {
                    swal({
                      title: "Desculpe",
                      text: "Preencha o CEP",
                      icon: "error",
                    });

                    return false;
                }
            }

            return true;
        },
        onFinishing: function (event, currentIndex) {
            if (!$("#termos").prop('checked')) {
                swal({
                  title: "Desculpe",
                  text: "Aceite os termos e condições",
                  icon: "error",
                });
                return false;
            }

            var $spinner = $("#spinner");
            $spinner.show();

            $("a[href='#finish']").prop("disabled", true);

            $("#botao-pagamento").hide();

            var dados = $("#wizard").serialize();

            $.ajax({
                url: '/assinar-plano',
                method: 'POST',
                data: dados,
                success: function (response) {
                    $spinner.hide();
                    $("a[href='#finish']").prop("disabled", false);

                    if (response.success == 'true') {
                        swal({
                          title: "Parabéns",
                          text: "Seus dados foram registrados, agora efetue o pagamento",
                          icon: "success",
                        });

                        $("#botao-pagamento").attr('href', response.url);
                        $("botao-pagamento").attr('target', '_blank');
                        $("#botao-pagamento").show();
                    } else {
                        swal({
                          title: "Desculpe",
                          text: "Houve erro ao registrar seus dados, tente novamente mais tarde",
                          icon: "error",
                        });
                    }
                }
            })

            return true;
        }
    });

    $('.wizard > .steps li a').click(function(){
    	$(this).parent().addClass('checked');
		$(this).parent().prevAll().addClass('checked');
		$(this).parent().nextAll().removeClass('checked');
    });
    // Custome Jquery Step Button
    $('.forward').click(function() {
        var wizard = $("#wizard");

        console.log(wizard.getState());
    	$("#wizard").steps('next');
    })
    $('.backward').click(function(){
        $("#wizard").steps('previous');
    })
    // Select Dropdown
    $('html').click(function() {
        $('.select .dropdown').hide(); 
    });
    $('.select').click(function(event){
        event.stopPropagation();
    });
    $('.select .select-control').click(function(){
        $(this).parent().next().toggle();
    })    
    $('.select .dropdown li').click(function(){
        $(this).parent().toggle();
        var text = $(this).attr('rel');
        $(this).parent().prev().find('div').text(text);
    })
})
