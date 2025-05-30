<?php

	require_once ("email.php");
	require_once ("../model/connect.php");
	require_once ("../model/Produto.php");
	require_once ("../model/Estoque.php");
	require_once ("../model/Cupom.php");
	require_once ("../model/Pedido.php");
	
	session_start();

	$produto = new Produto();
	$estoque = new Estoque();
	$cupom = new Cupom();
	$pedido = new Pedido();

	$ctrl = $_GET['ctrl'];

	//CADASTRA O PRODUTO
	if ($ctrl == 'cadastroProduto') {
		$produto->nome = $_POST['nome'];
		$produto->preco = $_POST['preco'];
		$produto->variacoes = $_POST['variacoes'];

	    if ($produto->create($connect)) {

	        // Pega o último id inserido
	        $produto_id = mysqli_insert_id($connect);

	        // Atribui o id do produto ao estoque e a quantidade
	        $estoque->produto_id = $produto_id;
	        $estoque->quantidade = $_POST['estoque'];

	        if ($estoque->create($connect)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Produto e estoque cadastrados com sucesso!']);
	        } else {
	            // Erro ao inserir estoque
	            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar estoque.']);
	        }
	    } else {
	        // Erro ao inserir produto
	        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar produto.']);
	    }
		
	}

	//LISTA OS PRODUTOS
	if ($ctrl == 'listarProdutos') {
		
		$sql = mysqli_query($connect,"
			SELECT p.*, e.quantidade AS estoque
			FROM produtos p 
			LEFT JOIN estoque e ON p.id = e.produto_id");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			$cont = 1;
			echo "<table class='table table-bordered table-hover'>
					<thead class='table-light'>
		                <tr>
		                  <th>Nº</th>
		                  <th>Nome</th>
		                  <th>Preço</th>
		                  <th>Variações</th>
		                  <th>Estoque</th>
		                  <th>Ações</th>
		                </tr>
		              </thead>";

		            while ($produto = mysqli_fetch_object($sql)) {
		              echo "<tbody>
			                <tr>
			                  <td>".$cont."</td>
			                  <td>".$produto->nome."</td>
			                  <td>R$ ". number_format($produto->preco, 2, ',', '.') ."</td>
			                  <td>".$produto->variacoes."</td>
			                  <td>".$produto->estoque."</td>
			                  <td>
			                    <button class='btn btn-sm btn-warning me-1' onclick='editarProduto(".$produto->id.")'>Editar</button>
			                    <button class='btn btn-sm btn-danger me-1' onclick='alertExclusao(".$produto->id.")'>Excluir</button>
			                    <button class='btn btn-sm btn-success' onclick='adicionarProdutoCarrinho(".$produto->id.")'>Comprar</button>
			                  </td>
			                </tr>
			              </tbody>";
			          $cont++;
	          		}
	        echo "</table>";
		}else{
			echo "Nenhum produto cadastrado!";
		}
	}

	//DADOS DO PRODUTO PARA EDIÇÃO
	if ($ctrl == "dadosProduto") {
		$id = intval($_POST['id']);

		$sql = mysqli_query($connect,"
			SELECT p.*, e.quantidade AS estoque
			FROM produtos p 
			LEFT JOIN estoque e ON p.id = e.produto_id WHERE p.id = '$id'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			$produto = mysqli_fetch_assoc($sql);
			echo json_encode($produto);
		}
	}

	//ATUALIZA PRODUTO
	if ($ctrl == "atualizaProduto") {
		
		$idProduto = intval($_POST['id']);
		$produto->nome = $_POST['nome'];
		$produto->preco = $_POST['preco'];
		$produto->variacoes = $_POST['variacoes'];

	    if ($produto->update($connect,$idProduto)) {

	        $estoque->quantidade = $_POST['estoque'];

	        if ($estoque->update($connect,$idProduto)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Produto e estoque atualizado com sucesso!']);
	        } else {
	            // Erro ao inserir estoque
	            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar estoque.']);
	        }
	    } else {
	        // Erro ao inserir produto
	        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar produto.']);
	    }
	}

	//EXCLUI O PRODUTO
	if ($ctrl == "excluirProduto") {

	    $id = intval($_POST['id']); 

	    if ($produto->delete($connect, $id)) {
	        echo json_encode(['success' => true, 'message' => 'Produto excluído com sucesso!']);
	        unset($_SESSION['carrinho']);
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o produto.']);
	    }
	}

	//VERIFICA SE EXISTE ALGUM PRODUTO NO CARRINHO
	if ($ctrl == "carrinho") {
		if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
		    
		    echo "<table class='table table-bordered mb-3' id='carrinhoTable'>
			        <thead class='table-light'>
			          <tr>
			            <th>Produto</th>
			            <th>Variação</th>
			            <th>Qtd</th>
			            <th>Subtotal</th>
			            <th>Ações</th>
			          </tr>
			        </thead>
			        <tbody>";

			//CRIA A SESSAO DE SUB TOTAL DOS VALORES
			if (!isset($_SESSION['subTotal'])) {
	            $_SESSION['subTotal'] = [];
	        }


	        $total = 0;

	        foreach ($_SESSION['carrinho'] as $item) {
	           
	            echo "<tr>
			            <td>" . $item['nome'] . "</td>
			            <td>".$item['variacoes']."</td>
			            <td>
			              <div class='d-flex align-items-center'>
			                <button class='btn btn-sm btn-secondary me-1' onclick='btnRemove(".$item['id'].")'>–</button>
			                <input type='number' min='1' max='". $item['estoque']."' value='".$item['quantidade']."' style='width: 50px; text-align: center; margin-left:1px;' disabled id='qtd".$item['id']."'>
			                <button class='btn btn-sm btn-secondary ms-1' onclick='btnAdd(".$item['id'].")'>+</button>
			              </div>
			            </td>
			            <td>R$ " . number_format($item['preco'] * $item['quantidade'], 2, ',', '.') . "</td>
			            <td><button class='btn btn-sm btn-danger' onclick='removerProdutoCarrinho(".$item['id'].")'><i class='bi bi-trash'></i></button></td>
			          </tr>";

			    $subtotal = $item['preco'] * $item['quantidade'];
			    //Adiciona valores a sessao
				$_SESSION['subTotal'][$item['id']] = ['sub_total' => $subtotal];

	            $total+=$subtotal;
			                  
	        }

	        $desconto = 0;
			$subtotal = $total; // salva o valor original dos produtos (sem desconto)

			// Aplica desconto, se houver cupom
			if (isset($_SESSION['cupom'])) {
			    $cupom = $_SESSION['cupom'];
			    $hoje = date('Y-m-d');

			    if ($hoje <= $cupom['validade']) {
			        $desconto = $total * ($cupom['desconto_percentual'] / 100);

			        // Garante que o desconto não ultrapasse o valor mínimo definido
			        if ($desconto > $cupom['valor_minimo']) {
			            $desconto = $cupom['valor_minimo'];
			        }

			    } else {
			        unset($_SESSION['cupom']); // Remove cupom vencido
			    }
			}

			// Calcular frete com base no valor original dos produtos (sem desconto)
			$frete = 0;
			if ($subtotal >= 52 && $subtotal <= 166.59) {
			    $frete = 15.00;
			} elseif ($subtotal > 200) {
			    $frete = 0.00;
			} else {
			    $frete = 20.00;
			}

			// Aplica desconto no total final
			$totalComFrete = ($total - $desconto) + $frete;

			// Formata valores se desejar exibir
			$descontoFormatado = number_format($desconto, 2, ',', '.');
			$totalFormatado = number_format($total, 2, ',', '.');
			$freteFormatado = number_format($frete, 2, ',', '.');
			$totalComFreteFormatado = number_format($totalComFrete, 2, ',', '.');

	        
	        echo "</tbody>
			        <tfoot>
			          <tr>
			            <th colspan='3' class='text-end'>Total:</th>
			            <th>R$ ".$totalFormatado."</th>
			            <th></th>
			          </tr>
			          <tr>
			            <th colspan='3' class='text-end'>Frete:</th>
			            <th>R$ ".$freteFormatado."</th>
			            <th></th>
			          </tr>";
	          	if ($desconto > 0) {
				    echo "<tr>
				            <th colspan='3' class='text-end text-success'>Desconto (".$cupom['codigo']."):</th>
				            <th class='text-success'>- R$ ".$descontoFormatado."</th>
				            <th><button class='btn btn-sm btn-danger' onclick='removerCupom()'><i class='bi bi-trash'></i></button></th>
				          </tr>";
				}
				echo"
			          <tr>
			            <th colspan='3' class='text-end'>Valor Final:</th>
			            <th>R$ ".$totalComFreteFormatado."</th>
			            <th></th>
			          </tr>
			        </tfoot>
			      </table>
			      	<div class='d-flex justify-content-between align-items-center'>
					  <div class='d-flex me-2'>
					  	<form class='d-flex' onsubmit='aplicarCupom(event)'>
					    <input type='text' class='form-control me-2' name='cupom' id='cupom' placeholder='Cupom' required>
					    <button type='submit' class='btn btn-primary'>Aplicar</button>
					    </form>
					  </div>
					  <button class='btn btn-success' id='btnFinalizar' onclick='finalizarPedido(".$totalComFreteFormatado.")'>
						  <span class='spinner-border spinner-border-sm d-none' role='status' aria-hidden='true' id='spinnerFinalizar'></span>
						  Finalizar Pedido
					  </button>
					</div>";
		} else {
		   
		    echo 0;
		}
	}

	//ADICIONA PRODUTO AO CARRINHO
	if ($ctrl == "adicionarProdutoCarrinho") {

	    $idProduto = intval($_POST['id']); 

	    $sql = mysqli_query($connect,"
			SELECT p.*, e.quantidade AS estoque
			FROM produtos p 
			LEFT JOIN estoque e ON p.id = e.produto_id WHERE p.id = '$idProduto'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {		

	        $produto = mysqli_fetch_object($sql);

	        if (!isset($_SESSION['carrinho'])) {
	            $_SESSION['carrinho'] = [];
	        }

	        if($_SESSION['carrinho'][$idProduto]){

	        	//CLICANDO COMPRAR NOVAMENTE NO MESMO PRODUTO, ELE ADICIONA MAIS 1 QUANTIDADE
	        	if($_SESSION['carrinho'][$idProduto]['quantidade'] < $_SESSION['carrinho'][$idProduto]['estoque']
	        		|| $_SESSION['carrinho'][$idProduto]['quantidade'] == 1){
	        		$_SESSION['carrinho'][$idProduto]['quantidade'] += 1;
	        	}
	        }else{
	            $_SESSION['carrinho'][$idProduto] = [
	                'id' => $produto->id,
	                'nome' => $produto->nome,
	                'preco' => $produto->preco,
	                'variacoes' => $produto->variacoes,
	                'estoque' => $produto->estoque,
	                'quantidade' => 1
	            ];
        	}
	    }
	}

	//REMOVE PRODUTO DO CARRINHO
	if ($ctrl == "exclueProdutoCarrinho") {

	    $idProduto = intval($_POST['id']); 

	    if (isset($_SESSION['carrinho'][$idProduto])) {
       		unset($_SESSION['carrinho'][$idProduto]);

	        echo json_encode(['success' => true]);
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Erro ao remover o produto.']);
	    }

	}

	//ATUALIZA QUANTIDADE DO PRODUTO NA SESSION
	if ($ctrl == "atualizaQuantidadeProduto") {
		
		$idProduto = intval($_POST['id']);
		$function = $_POST['function']; 

	    if (isset($_SESSION['carrinho'][$idProduto])) {

	    	if ($function === "add") {
	       		$_SESSION['carrinho'][$idProduto]['quantidade'] = $_SESSION['carrinho'][$idProduto]['quantidade']+1;

		        echo json_encode(['success' => true]);
	    	}else{
	    		$_SESSION['carrinho'][$idProduto]['quantidade'] = $_SESSION['carrinho'][$idProduto]['quantidade']-1;
		        
		        echo json_encode(['success' => true]);
	    	}
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Erro ao escolher quantidade.']);
	    }
	}

	//CADASTRO DO CUPOM
	if ($ctrl == 'cadastroCupom') {
		$cupom->codigo = $_POST['codigo'];
		$cupom->desconto_percentual = $_POST['descontoPercentual'];
		$cupom->validade = $_POST['validadeCupom'];
		$cupom->valor_minimo = $_POST['valorMinimo'];

		$sql = mysqli_query($connect," SELECT *	FROM cupons WHERE codigo = '$cupom->codigo'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			//Ja existe um cupom com esse codigo
	        echo json_encode(['success' => false, 'message' => 'Cupom com o código ja existente.']);
		}else{
			if ($cupom->create($connect)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Cupom cadastrado com sucesso!']);
	        } else {
	            // Erro ao inserir cupom
	            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cupom.']);
	        }
		}
	}

	//LISTA OS CUPONS
	if ($ctrl == 'listarCupons') {
		
		$sql = mysqli_query($connect," SELECT *	FROM cupons");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			$cont = 1;
			echo "<table class='table table-sm table-hover align-middle mb-0'>
		          <thead>
		            <tr>
		              <th>Nº</th>
		              <th>Código</th>
		              <th>Desconto (%)</th>
		              <th>Validade</th>
		              <th>Valor Mínimo (R$)</th>
		              <th>Status</th>
		              <th>Ações</th>
		            </tr>
		          </thead>
		          <tbody>";

		            while ($cupom = mysqli_fetch_object($sql)) {

		            	if ($cupom->validade < date('Y-m-d')) {
		            		echo "<tr>
						              <td>".$cont."</td>
						              <td>".$cupom->codigo."</td>
						              <td>".$cupom->desconto_percentual."%</td>
						              <td>".$cupom->validade."</td>
						              <td>R$ ".$cupom->valor_minimo."</td>
						              <td class='text-danger'>Expirado</td>
						              <td>
						                <button class='btn btn-sm btn-primary me-1' title='Editar' onclick='editarCupom(".$cupom->id.")'>
						                  <i class='bi bi-pencil'></i>
						                </button>
						                <button class='btn btn-sm btn-danger' title='Excluir' onclick='alertExclusaoCupom(".$cupom->id.")'>
						                  <i class='bi bi-trash'></i>
						                </button>
						              </td>
						            </tr>";
		            	}else{
		            		echo "<tr>
						              <td>".$cont."</td>
						              <td>".$cupom->codigo."</td>
						              <td>".$cupom->desconto_percentual."%</td>
						              <td>".$cupom->validade."</td>
						              <td>R$ ".$cupom->valor_minimo."</td>
						              <td class='text-success'>Válido</td>
						              <td>
						                <button class='btn btn-sm btn-primary me-1' title='Editar' onclick='editarCupom(".$cupom->id.")'>
						                  <i class='bi bi-pencil'></i>
						                </button>
						                <button class='btn btn-sm btn-danger' title='Excluir' onclick='alertExclusaoCupom(".$cupom->id.")'>
						                  <i class='bi bi-trash'></i>
						                </button>
						              </td>
						            </tr>";
		            	}
		              
			          $cont++;
	          		}
	        echo "</tbody>
	        	  </table>";
		}else{
			echo "Nenhum cupom cadastrado!";
		}
	}

	//DADOS DO CUPOM PARA EDIÇÃO
	if ($ctrl == "dadosCupom") {
		$id = intval($_POST['id']);

		$sql = mysqli_query($connect," SELECT *	FROM cupons WHERE id = '$id'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			$cupom = mysqli_fetch_assoc($sql);
			echo json_encode($cupom);
		}
	}

	//EXCLUI O CUPOM
	if ($ctrl == "excluirCupom") {

	    $id = intval($_POST['id']); 

	    if ($cupom->delete($connect, $id)) {
	        echo json_encode(['success' => true, 'message' => 'Cupom excluído com sucesso!']);
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o cupom.']);
	    }
	}

	//ATUALIZA CUPOM
	if ($ctrl == "atualizaCupom") {
		
		$idCupom = intval($_POST['id']);
		$cupom->codigo = $_POST['codigo'];
		$cupom->desconto_percentual = $_POST['desconto_percentual'];
		$cupom->validade = $_POST['validade'];
		$cupom->valor_minimo = $_POST['valor_minimo'];

		$sql = mysqli_query($connect, "SELECT * FROM cupons WHERE codigo = '$cupom->codigo' AND id != '$idCupom'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			//Ja existe um cupom com esse codigo
	        echo json_encode(['success' => false, 'message' => 'Cupom com o código ja existente.']);
		}else{

		    if ($cupom->update($connect,$idCupom)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Cupom atualizado com sucesso!']);
	        } else {
	            // Erro ao atualizar cupom
	            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cupom.']);
	        }
	    }
	}

	//APLICA CUPOM
	if ($ctrl == 'aplicarCupom') {
		
		$codigoCupom = $_POST['cupom'];

		$sql = mysqli_query($connect, "SELECT * FROM cupons WHERE codigo = '$codigoCupom'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {

			$cupom = mysqli_fetch_object($sql);

			if ($cupom->validade <  date('Y-m-d')) {
				echo json_encode(['success' => false, 'message' => 'Cupom expirado.']);
			}else{

				if (!isset($_SESSION['cupom'])) {
		            $_SESSION['cupom'] = [];
		        }

		        if(isset($_SESSION['cupom']['codigo']) && $_SESSION['cupom']['codigo'] === $cupom->codigo){

		        	$_SESSION['cupom'] = [
		                'id' => $cupom->id,
		                'codigo' => $cupom->codigo,
		                'validade' => $cupom->validade,
		                'desconto_percentual' => $cupom->desconto_percentual,
		                'valor_minimo' => $cupom->valor_minimo,
		            ];
		        }else{
		            $_SESSION['cupom'] = [
		                'id' => $cupom->id,
		                'codigo' => $cupom->codigo,
		                'validade' => $cupom->validade,
		                'desconto_percentual' => $cupom->desconto_percentual,
		                'valor_minimo' => $cupom->valor_minimo,
		            ];
		    	}

		    	echo json_encode(['success' => true, 'message' => 'Cupom aplicado.']);
			}

    	}else{
    		echo json_encode(['success' => false, 'message' => 'Cupom inválido.']);
    		unset($_SESSION['cupom']);
    	}
	}

	//REMOVE CUPOM APLICADO
	if ($ctrl == 'removerCupom') {

		if(isset($_SESSION['cupom'])) {
        	unset($_SESSION['cupom']);
			echo json_encode(['success' => true, 'message' => 'Cupom removido.']);
		}else{
			echo json_encode(['success' => false, 'message' => 'Nenhum cupom para remover.']);
		}
	}

	//SALVA O PEDIDO
	if ($ctrl == 'salvaPedido') {

		$pedido->cep = $_POST['cep'];
		$rua = $_POST['rua'];
		$bairro = $_POST['bairro'];
		$cidade = $_POST['cidade'];
		$estado = $_POST['estado'];
		$numero = $_POST['numero'];
		$pedido->endereco = $rua . ', ' . $bairro . ' - ' . $cidade . ' - ' . $estado . ' - Nº ' . $numero;
		$pedido->data_pedido = date('Y-m-d');
		$pedido->valor_total = $_POST['valor_total'];

		$sql = mysqli_query($connect," SELECT *	FROM pedidos WHERE cep = '$pedido->cep' AND status = 'PENDENTE'");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			//Ja existe um cupom com esse codigo
	        echo json_encode(['success' => false, 'message' => 'Existe um pedido com esse (cep) pendente.']);
		}else{

			$produtosParaSalvar = [];

			foreach ($_SESSION['carrinho'] as $idProduto => $produtoS) {
			    $produtosParaSalvar[$idProduto] = [
			        'nome' => $produtoS['nome'],
			        'variacoes' => $produtoS['variacoes'],
			        'quantidade' => (int) $produtoS['quantidade'],
			    ];
			}

			$produtosJson = json_encode($produtosParaSalvar, JSON_UNESCAPED_UNICODE);
			$pedido->produtos = $produtosJson;

			$pedido->status = 'PENDENTE';
			if ($pedido->create($connect)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Pedido finalizado!']);
	            unset($_SESSION['carrinho']);

	            envioEmail(
	            	$pedido->cep,
	            	$pedido->endereco,
	            	$pedido->data_pedido,
	            	$pedido->valor_total,
	            	$_POST['email']
	            );

				if (isset($_SESSION['cupom']['id'])) {
				    $cupom->delete($connect, $_SESSION['cupom']['id']);
				}

				unset($_SESSION['cupom']);


				//ESSA PARTE MUDA A QUANTIDADE NO ESTOQUE DO PRODUTO NO BANCO
				$produtos = json_decode($produtosJson, true);

				foreach ($produtos as $idProduto => $quantidadeComprada) {

				    $sql = mysqli_query($connect, "SELECT id,quantidade FROM estoque WHERE produto_id = $idProduto");
				    $estoqueQuantidade = mysqli_fetch_object($sql);

				    $novoEstoque = $estoqueQuantidade->quantidade - $quantidadeComprada['quantidade'];

				    if ($novoEstoque <= 0) {
				    	//APAGA PRODUTO SE CASO O ESTOQUE FOR MENOR QUE 0
				    	$id = intval($idProduto);
				        $produto->delete($connect, $id);
				    }else{
				    	//ATUALIZA ESTOQUE
				    	$id = intval($idProduto);
				    	$estoque->quantidade = $novoEstoque;
				    	$estoque->update($connect,$id);

				    }
				}

	        } else {
	            // Erro ao inserir cupom
	            echo json_encode(['success' => false, 'message' => 'Erro ao finalizar pedido.']);
	        }
		}
	}

	//LISTA OS PEDIDOS
	if ($ctrl == 'listarPedidos') {
		
		$sql = mysqli_query($connect,"SELECT * FROM pedidos");
		$num = mysqli_num_rows($sql);

		if ($num > 0) {
			$cont = 1;
			echo "<table class='table table-bordered table-hover'>
					<thead class='table-light'>
		                <tr>
		                  <th>Nº</th>
		                  <th>ID do Produto</th>
		                  <th>Status</th>
		                  <th>Cep</th>
		                  <th style='width:20%;'>Endereço</th>
		                  <th>Data Pedido</th>
		                  <th>Valor Total</th>
		                  <th>Ações</th>
		                </tr>
		              </thead>";

		            while ($pedido = mysqli_fetch_object($sql)) {
		              echo "<tbody>
			                <tr>
			                  <td>".$cont."</td>
			                  <td>".$pedido->id."</td>";
			                  if ($pedido->status === 'PENDENTE') {
			                  	echo "<td class='text-warning bg'>".$pedido->status."</td>";
			                  }else{
			                  	echo "<td class='text-success'>".$pedido->status."</td>";
			                  }
			            echo "<td>".$pedido->cep."</td>
			                  <td>".$pedido->endereco."</td>
			                  <td>".date('Y-m-d', strtotime($pedido->data_pedido))."</td>
			                  <td>R$ ". number_format($pedido->valor_total, 2, ',', '.') ."</td>
			                  <td class='d-flex justify-content-center'>
				                <button class='btn btn-sm btn-danger' title='Excluir' onclick='excluirPedido(".$pedido->id.")'>
				                  <i class='bi bi-trash'></i>
				                </button>
			                  </td>
			                </tr>
			              </tbody>";
			          $cont++;
	          		}
	        echo "</table>";
		}else{
			echo "Nenhum pedido ainda!";
		}
	}

	//EXCLUI O PEDIDO
	if ($ctrl == "excluirPedido") {

	    $id = intval($_POST['id']); 

	    if ($pedido->delete($connect, $id)) {
	        echo json_encode(['success' => true, 'message' => 'Pedido excluído com sucesso!']);
	    } else {
	        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o pedido.']);
	    }
	}

	//WEBHOOK
	if ($ctrl == 'webhook') {

	    $json = $_POST['jsonWebhook'];
	    $data = json_decode($json, true);

	    if (!$data || !isset($data['id']) || !isset($data['status'])) {
	        echo json_encode(['success' => false, 'message' => 'JSON inválido ou incompleto.']);
	        exit;
	    }

	    $id = intval($data['id']);

	    $sql = mysqli_query($connect,"SELECT * FROM pedidos WHERE id = '$id'");
		$num = mysqli_num_rows($sql);

	    if ($num > 0) {
	    	if ($data['status'] === 'CANCELADO') {

		        $pedido->delete($connect,$data['id']);
		        echo json_encode(['success' => true, 'message' => 'Pedido removido.']);

		    } elseif($data['status'] === 'PAGO') {

		    	$pedido->status = $data['status'];
		        $pedido->update($connect, $data['id'], $data['status']);
		        echo json_encode(['success' => true, 'message' => 'Status atualizado para (pago).']);

		    }

		    exit;
	    }else{
	    	echo json_encode(['success' => false, 'message' => 'Pedido com ID nao existe.']);
	    }
	}

?>