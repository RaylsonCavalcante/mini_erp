<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	require '../vendor/autoload.php';

	//ENVIA EMAIL
	function envioEmail($cep,$endereco,$dataPedido,$variacoes,$valorTotal,$email){

		$mail = new PHPMailer(true);

		try {
		    $mail->CharSet = 'UTF-8';
		    $mail->isSMTP();
		    $mail->Host       = 'smtp.gmail.com'; 
		    $mail->SMTPAuth   = true;
		    $mail->Username   = 'example@gmail.com'; //ADICIONA SEU EMAIL REAL
		    $mail->Password   = 'xxxx xxxx xxxx xxxx'; //ADICIONE A SENHA DE APP
		    $mail->SMTPSecure = 'tls';
		    $mail->Port       = 587;

		    
		    $mail->setFrom('minierp@gmail.com', 'Mini ERP');
		    $mail->addAddress($email, 'Cliente');

		    
		    $mail->isHTML(true);
		    $mail->Subject = 'Confirmação de Pedido';
		    $mail->Body    = '
		        <h1>Pedido Recebido</h1>
		        <p>Obrigado por comprar conosco!</p>
		        <h3>Detalhes do Pedido</h3>
			    <ul>
			        <li><strong>Data do Pedido:</strong> ' . $dataPedido . '</li>
			        <li><strong>Endereço:</strong> ' . $endereco . '</li>
			        <li><strong>CEP:</strong> ' . $cep . '</li>
			        <li><strong>Produtos:</strong> ' . $variacoes . '</li>
			        <li><strong>Valor Total:</strong> R$ ' . number_format($valorTotal, 2, ',', '.') . '</li>
			    </ul>
			    <p>Em breve seu pedido será enviado para o endereço informado.</p>
		    ';
		    $mail->AltBody = 'Pedido Recebido. Obrigado por comprar conosco!';

		    
		    $mail->send();

		} catch (Exception $e) {

		    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
		}
	}

?>