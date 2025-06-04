<?php

	require_once ("email.php");
	require_once ("../model/connect.php");
	require_once ("../model/Produto.php");
	require_once ("../model/Estoque.php");
	require_once ("../model/Variacao.php");
	require_once ("../model/Cupom.php");
	require_once ("../model/Pedido.php");
	
	session_start();

	$produto = new Produto();
	$estoque = new Estoque();
	$variacao = new Variacao();
	$cupom = new Cupom();
	$pedido = new Pedido();

	$ctrl = $_GET['ctrl'];

	//CADASTRA O PRODUTO
	if ($ctrl == 'cadastroProduto') {
		$produto->nome = $_POST['nome'];
		$produto->preco = $_POST['preco'];

	    if ($produto->create($connect)) {
	    	unset($_SESSION['carrinho']);

	    	//Pega o ultimo id inserido do produto
	    	$variacao->produto_id = mysqli_insert_id($connect);
	        $variacoes = $_POST['variacoes'];

	        foreach ($variacoes as $i => $nome_variacao) {
	            // Inserir variação
	            $variacao->descricao = $nome_variacao['descricao'];
	            $variacao->create($connect);

	            // Inserir estoque
		        $estoque->variacao_id = mysqli_insert_id($connect);
		        $estoque->quantidade = $nome_variacao['estoque'];
		        $estoque->create($connect);
	        }

            // Sucesso: Retorna JSON de confirmação
            echo json_encode(['success' => true, 'message' => 'Produto cadastrado com sucesso!']);
	    } else {
	        // Erro ao inserir produto
	        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar produto.']);
	    }
		
	}

	//LISTA OS PRODUTOS
	if ($ctrl == 'listarProdutos') {
		
		$sql = mysqli_query($connect,"
			SELECT 
			    p.*,
			    v.id AS variacao_id,
			    v.descricao AS variacao_descricao,
			    e.quantidade AS estoque
			FROM produtos p
			LEFT JOIN variacoes v ON v.produto_id = p.id
			LEFT JOIN estoque e ON e.variacao_id = v.id
			ORDER BY p.id, v.id;
		");

		$num = mysqli_num_rows($sql);

		if ($num > 0) {
		    $produtos = [];

		    while ($row = mysqli_fetch_assoc($sql)) {
		        $id = $row['id'];

		        if (!isset($produtos[$id])) {
		            $produtos[$id] = [
		                'nome' => $row['nome'],
		                'preco' => $row['preco'],
		                'variacoes' => []
		            ];
		        }

		        if ($row['variacao_id']) {
		            $produtos[$id]['variacoes'][] = [
		                'descricao' => $row['variacao_descricao'],
		                'estoque' => $row['estoque']
		            ];
		        }
		    }

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
		            </thead>
		            <tbody>";

		    $cont = 1;
		    foreach ($produtos as $id => $produto) {
		        // Montar lista das variações e estoques concatenados
		        $variacoesHtml = '';
		        foreach ($produto['variacoes'] as $var) {
		            $variacoesHtml .= htmlspecialchars($var['descricao']) . "<br>";
		        }
		        $estoqueHtml = '';
		        foreach ($produto['variacoes'] as $var) {
		            $estoqueHtml .= intval($var['estoque']) . "<br>";
		        }

		        echo "<tr>
		                <td>$cont</td>
		                <td>".htmlspecialchars($produto['nome'])."</td>
		                <td>R$ ".number_format($produto['preco'], 2, ',', '.')."</td>
		                <td>$variacoesHtml</td>
		                <td>$estoqueHtml</td>
		                <td>
		                    <button class='btn btn-sm btn-warning me-1' onclick='editarProduto($id)'>Editar</button>
		                    <button class='btn btn-sm btn-danger me-1' onclick='alertExclusao($id)'>Excluir</button>
		                    <button class='btn btn-sm btn-success' id='btnComprar".$id."' onclick='adicionarProdutoCarrinho($id)'>Comprar</button>
		                </td>
		              </tr>";

		        $cont++;
		    }

		    echo "</tbody></table>";

		} else {
		    echo "Nenhum produto cadastrado!";
		}

	}

	//DADOS DO PRODUTO PARA EDIÇÃO
	if ($ctrl == "dadosProduto") {
	    $id = intval($_POST['id']);

	    // Busca produto
	    $sqlProduto = mysqli_query($connect, "SELECT id, nome, preco FROM produtos WHERE id = $id LIMIT 1");
	    if (mysqli_num_rows($sqlProduto) > 0) {
	        $produto = mysqli_fetch_assoc($sqlProduto);

	        // Busca variações do produto com seus respectivos estoques
	        $sqlVariacoes = mysqli_query($connect, "
	            SELECT v.id, v.descricao, IFNULL(e.quantidade, 0) AS quantidade
	            FROM variacoes v
	            LEFT JOIN estoque e ON e.variacao_id = v.id
	            WHERE v.produto_id = $id
	        ");

	        $variacoes = [];
	        while ($v = mysqli_fetch_assoc($sqlVariacoes)) {
	            $variacoes[] = $v;
	        }

	        // Monta e envia JSON
	        $response = [
	            'id' => $produto['id'],
	            'nome' => $produto['nome'],
	            'preco' => $produto['preco'],
	            'variacoes' => $variacoes
	        ];

	        echo json_encode($response);
	    }
	}

	//ATUALIZA PRODUTO
	if ($ctrl == "atualizaProduto") {
	    $idProduto = intval($_POST['id']);
	    $produto->nome = $_POST['nome'];
	    $produto->preco = $_POST['preco'];

	    // Atualiza produto
	    if ($produto->update($connect, $idProduto)) {

	    	// 1. Busca todos os IDs de variações do banco para o produto
			$idsBanco = [];
			$variacoesExistentes = mysqli_query($connect, "SELECT id FROM variacoes WHERE produto_id = $idProduto");
			while ($row = mysqli_fetch_assoc($variacoesExistentes)) {
			    $idsBanco[] = $row['id'];
			}

			// 2. Extrair os IDs que vieram do POST
			$idsPost = array_filter(array_map(function ($v) {
			    return intval($v['id']);
			}, $_POST['variacoes']));

			// 3. Identificar quais variações devem ser deletadas
			$idsParaDeletar = array_diff($idsBanco, $idsPost);

			foreach ($idsParaDeletar as $idDel) {
			    // Deleta o estoque vinculado
			    mysqli_query($connect, "DELETE FROM estoque WHERE variacao_id = $idDel");
			    // Deleta a variação
			    mysqli_query($connect, "DELETE FROM variacoes WHERE id = $idDel");
			}
	        
	        // Atualiza variações
	        foreach ($_POST['variacoes'] as $v) {
	            $idVariacao = intval($v['id']);

	            // Atualiza descrição da variação
	            $variacao->descricao = $v['descricao'];
	            $variacao->update($connect,$idVariacao);

	            // Atualiza estoque da variação
	            $sql = mysqli_query($connect, "SELECT id FROM estoque WHERE variacao_id = $idVariacao");
	            $num = mysqli_num_rows($sql);

	            if ($num > 0) {
	            	//ATUALIZA SOMENTE
	            	$estoque->variacao_id = $idVariacao;
	            	$estoque->quantidade = $v['estoque'];
	            	$estoque->update($connect);
	            }else{
	            	//INSERE NOVA VARIAÇÃO E ESTOQUE
	            	$variacoes = $_POST['variacoes'];
	            	$variacao->produto_id = $idProduto;
		            
		            // Inserir variação
		            $variacao->descricao = $v['descricao'];
		            $variacao->create($connect);

		            // Inserir estoque
			        $estoque->variacao_id = mysqli_insert_id($connect);
			        $estoque->quantidade = $v['estoque'];
			        $estoque->create($connect);
	            }
	        }

	        echo json_encode(['success' => true, 'message' => 'Atualização com sucesso!']);
	    } else {
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
			    $idProduto = $item['id'];
			    $variacaoSelecionada = $item['variacao_selecionada'];

			    echo "<tr>
			            <td>{$item['nome']}</td>
			            <td>
			              <select class='form-select form-select-sm' onchange='atualizaVariacao({$idProduto})' id='variacao{$idProduto}'>";
			    
			    foreach ($item['variacoes'] as $v) {
			        $selected = ($v['variacao_id'] == $variacaoSelecionada) ? 'selected' : '';
			        echo "<option value='{$v['variacao_id']}' data-estoque='{$v['estoque']}' $selected>
			                {$v['descricao']} (Estoque: {$v['estoque']})
			              </option>";
			        
			        // Guarda info da variação ativa
			        if ($v['variacao_id'] == $variacaoSelecionada) {
			            $estoque = $v['estoque'];
			            $quantidade = $v['quantidade'];
			        }
			    }

			    echo "</select>
			          </td>
			          <td>
			            <div class='d-flex align-items-center'>
			              <button class='btn btn-sm btn-secondary me-1' onclick='btnRemove({$idProduto})'>–</button>
			              <input type='number' min='1' max='{$estoque}' 
			                     value='{$quantidade}' 
			                     style='width: 50px; text-align: center;' 
			                     disabled 
			                     id='qtd{$idProduto}'>
			              <button class='btn btn-sm btn-secondary ms-1' onclick='btnAdd({$idProduto})'>+</button>
			            </div>
			            <input type='hidden' id='preco{$idProduto}' value='{$item['preco']}'>
			          </td>
			          <td id='subtotal{$idProduto}'>
			            R$ " . number_format($item['preco'] * $quantidade, 2, ',', '.') . "
			          </td>
			          <td>
			            <button class='btn btn-sm btn-danger' onclick='removerProdutoCarrinho({$idProduto})'>
			              <i class='bi bi-trash'></i>
			            </button>
			          </td>
			        </tr>";
			


			    $subtotal = $item['preco'] * $quantidade;
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

	//ATUALIZAR VARIAÇÃO SELECIONADA NA SESSION
	if ($ctrl == "atualizaVariacaoSelecionada") {
	    $idProduto = intval($_POST['id']);
	    $variacaoId = intval($_POST['variacao_id']);

	    if (isset($_SESSION['carrinho'][$idProduto])) {
	        $_SESSION['carrinho'][$idProduto]['variacao_selecionada'] = $variacaoId;
	        echo json_encode(['success' => true]);
	    } 
	}

	// //ADICIONA PRODUTO AO CARRINHO
	if ($ctrl == "adicionarProdutoCarrinho") {

	    $idProduto = intval($_POST['id']);

	    // Busca produto e suas variações com estoque
	    $sql = mysqli_query($connect, "
	        SELECT 
	            p.id AS produto_id,
	            p.nome,
	            p.preco,
	            v.id AS variacao_id,
	            v.descricao AS variacao_descricao,
	            e.quantidade AS estoque
	        FROM produtos p
	        LEFT JOIN variacoes v ON v.produto_id = p.id
	        LEFT JOIN estoque e ON e.variacao_id = v.id
	        WHERE p.id = '$idProduto'
	    ");
	    $num = mysqli_num_rows($sql);

	    if ($num > 0) {
	     
	        if (!isset($_SESSION['carrinho'])) {
			    $_SESSION['carrinho'] = [];
			}

			$variacoes = [];
			$produtoNome = '';
			$produtoPreco = 0;
			$variacaoSelecionada = null;

			while ($row = mysqli_fetch_object($sql)) {
			    $produtoNome = $row->nome;
			    $produtoPreco = $row->preco;

			    $variacoes[] = [
			        'variacao_id' => $row->variacao_id,
			        'descricao' => $row->variacao_descricao,
			        'estoque' => $row->estoque,
			        'quantidade' => 1
			    ];

			    // Define a primeira variação como selecionada por padrão
			    if ($variacaoSelecionada === null) {
			        $variacaoSelecionada = $row->variacao_id;
			    }
			}

			// Adiciona o produto com todas suas variações no carrinho
			$_SESSION['carrinho'][$idProduto] = [
			    'id' => $idProduto,
			    'nome' => $produtoNome,
			    'preco' => $produtoPreco,
			    'variacoes' => $variacoes,
			    'variacao_selecionada' => $variacaoSelecionada // Salva a variação selecionada
			];

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

	    	$produto =& $_SESSION['carrinho'][$idProduto];

	    	$variacaoIdSelecionada = $produto['variacao_selecionada'];

	    	foreach ($produto['variacoes'] as &$variacao) {
		        if ($variacao['variacao_id'] == $variacaoIdSelecionada) {

		            if ($function === "add") {
		                $variacao['quantidade']++;
		            }else{
		                $variacao['quantidade']--;
		            }

		            echo json_encode(['success' => true]);
		        }
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
	        echo json_encode(['success' => false, 'message' => 'Existe um pedido com esse (cep) pendente.']);
		}else{

			$produtosFormatados = [];

			foreach ($_SESSION['carrinho'] as $idProduto => $produtoS) {
			    $nome = $produtoS['nome'];
			    $variacao_selecionada = $produtoS['variacao_selecionada'];

			    foreach($produtoS['variacoes'] as $variacaoS ){
			    	if($variacaoS['variacao_id'] == $variacao_selecionada){
					    $descricao = $variacaoS['descricao'];
					    $quantidade = $variacaoS['quantidade'];
					}
				}

			    $linha = "{$nome} - {$descricao} - ({$quantidade}x)";
			    $produtosFormatados[] = $linha;
			}

			$pedido->produtos = implode("\n", $produtosFormatados); // quebra de linha entre os produtos

			$produtosParaEstoque = [];

			foreach ($_SESSION['carrinho'] as $idProduto => $produtoS) {
			    $variacao_selecionada = $produtoS['variacao_selecionada'];

			    foreach($produtoS['variacoes'] as $variacaoS ){
			    	if($variacaoS['variacao_id'] == $variacao_selecionada){
					    $produtosParaEstoque[] = [
					        'produto_id' => $idProduto,
					        'variacao_id' => $variacao_selecionada,
					        'quantidade' => $variacaoS['quantidade']
					    ];
					}
				}
			}

			$pedido->status = 'PENDENTE';
			if ($pedido->create($connect)) {
	            // Sucesso: Retorna JSON de confirmação
	            echo json_encode(['success' => true, 'message' => 'Pedido finalizado!']);
	            unset($_SESSION['carrinho']);

	            envioEmail(
	            	$pedido->cep,
	            	$pedido->endereco,
	            	$pedido->data_pedido,
	            	implode("\n", $produtosFormatados),
	            	$pedido->valor_total,
	            	$_POST['email']
	            );

				if (isset($_SESSION['cupom']['id'])) {
				    $cupom->delete($connect, $_SESSION['cupom']['id']);
				}

				unset($_SESSION['cupom']);


				//ESSA PARTE MUDA A QUANTIDADE NO ESTOQUE DO PRODUTO NO BANCO
				foreach ($produtosParaEstoque as $produtoS) {
				    $produtoId = (int) $produtoS['produto_id'];
				    $variacaoId = (int) $produtoS['variacao_id'];
				    $quantidadeComprada = (int) $produtoS['quantidade'];

				    // Busca o estoque da variação
				    $sql = mysqli_query($connect, "SELECT id, quantidade FROM estoque WHERE variacao_id = $variacaoId");
				    $estoqueS = mysqli_fetch_object($sql);

				    if ($estoqueS) {
				        $novoEstoque = $estoqueS->quantidade - $quantidadeComprada;

				        if ($novoEstoque <= 0) {
				            // Remove o estoque
				            $estoqueId = (int) $estoqueS->id;
				            $estoque->delete($connect,$estoqueId);

				            // Remove a variação
				            $variacao->delete($connect,$variacaoId);

				            // Verifica se ainda existem outras variações com estoque para esse produto
				            $sql = mysqli_query($connect, "
				                SELECT COUNT(*) as total 
				                FROM estoque 
				                INNER JOIN variacoes ON estoque.variacao_id = variacoes.id 
				                WHERE variacoes.produto_id = $produtoId
				            ");
				            $resultado = mysqli_fetch_object($sql);

				            if ($resultado->total == 0) {
				                // Se não houver nenhuma variação com estoque, deleta o produto
				                $produto->deleteProduto($connect,$produtoId);
				            }

				        } else {
				            // Atualiza a quantidade da variação
				            $estoqueId = (int) $estoqueS->id;
				            $estoque->quantidade = $novoEstoque;
				            $estoque->updateQuantidade($connect,$estoqueId);
				        }
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
