<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Assinatura Revista Digital Shoes</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="colorlib.com">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<base href="/site/">

		<!-- MATERIAL DESIGN ICONIC FONT -->
		<link rel="stylesheet" href="fonts/material-design-iconic-font/css/material-design-iconic-font.css">

		<!-- STYLE CSS -->
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<div class="wrapper">
            <form action="" id="wizard">
        		<!-- SECTION 1 -->
                <h2></h2>
                <section>
                    <div class="inner">
						<div class="image-holder">
							<img src="images/form-wizard-1.jpg" alt="">
						</div>
						<div class="form-content" >
							<div class="form-header">
								<h3>Assinatura Revista Digital Shoes</h3>
							</div>
							<p>Seus dados pessoais</p>
							<div class="form-row">
								<div class="form-holder">
									<input type="text" id="name" name="name" placeholder="Nome" class="form-control">
								</div>
								<div class="form-holder">
									<input type="text" id="email" name="email" placeholder="E-mail" class="form-control">
								</div>
							</div>
							<div class="form-row">
								<div class="form-holder">
									<input type="text" id="cpf" name="cpf" placeholder="CPF" class="form-control cpf">
								</div>
							</div>
							<div class="form-row">
								<div class="form-holder">
									<input type="password" id="password" name="password" placeholder="Senha" class="form-control">
								</div>
								<div class="form-holder">
									<input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirmar Senha" class="form-control">
								</div>
							</div>
						</div>
					</div>
                </section>

				<!-- SECTION 2 -->
                <h2></h2>
                <section>
                    <div class="inner">
						<div class="image-holder">
							<img src="images/form-wizard-2.jpg" alt="">
						</div>
						<div class="form-content">
							<div class="form-header">
								<h3>Assinatura Revista Shoes</h3>
							</div>
							<p>Seu endereço</p>
							<div class="form-row">
								<div class="form-holder">
									<input type="text" id="rua" name="rua" placeholder="Rua, Av" class="form-control">
								</div>
								<div class="form-holder">
									<input type="text" id="numero" name="numero" placeholder="Número" class="form-control">
								</div>
							</div>
							<div class="form-row">
								<div class="form-holder">
									<input type="text" id="bairro" name="bairro" placeholder="Bairro" class="form-control">
								</div>
								<div class="form-holder">
									<input type="text" name="complement" placeholder="Complemento" class="form-control">
								</div>
							</div>
							<div class="form-row">
								<div class="form-holder">
									<input type="text" id="cep" name="cep" placeholder="CEP" class="form-control cep">
								</div>
								<div class="form-holder">
									<input type="text" name="cidade" placeholder="Cidade" class="form-control">
								</div>
								<div class="form-holder">
									<input type="text" name="estado" placeholder="Estado" class="form-control">
								</div>
							</div>
							
						</div>
					</div>
                </section>

                <!-- SECTION 3 -->
                <h2></h2>
                <section>
                    <div class="inner">
						<div class="image-holder">
							<img src="images/form-wizard-3.jpg" alt="">
						</div>
						<div class="form-content">
							<div class="form-header">
								<h3>Assinatura Revista Shoes</h3>
							</div>
							<p>Selecione o plano desejado</p>

							<div class="form-row">
								<div class="form-holder">
									<select name="plano" id="plano" class="form-control">
										<option value="plano1_trimestral">
											Trimestral por R$ 280,00
										</option>
										<option value="plano1_semestral">
											Semestral por R$ 560,00
										</option>
									</select>
								</div>
							</div>

							<a style="display: none;" href="#" role="button" id="botao-pagamento" class="btn btn-primary" target="_blank">Efetuar pagamento</a>

							<div class="checkbox-circle mt-24">
								<label>
									<input type="checkbox" id="termos">  Tudo bem eu aceito os termos e condições!
									<span class="checkmark"></span>
								</label>
							</div>
							<div class="form-row">								
								<div style="width: 100px; margin:10px auto">
									<div id="spinner" style="display: none">
										<img height='100' src='/site/images/spinner.gif'></img>
									</div>		
								</div>
							</div>
						</div>
					</div>
                </section>
            </form>
		</div>
		
		<!-- JQUERY -->
		<script src="js/jquery-3.3.1.min.js"></script>
		<!-- JQUERY STEP -->
		<script src="js/jquery.steps.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>		
		<script src="js/main.js"></script>
		<!-- Template created and distributed by Colorlib -->
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.js"></script>
		<script type="text/javascript">
			$(function () {
				$(".cpf").mask('000.000.000-00');
				$(".cep").mask('00000-000');
			});
		</script>

</body>
</html>
