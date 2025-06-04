<?php
	include 'connect.php';

	class Produto{

		public $nome;
		public $preco;

		//INSERE PRODUTO
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO produtos(nome,preco) VALUES ('$this->nome','$this->preco')");

			return true;
		}

		//ATUALIZA PRODUTO
		public function update($connect,$id){

			$sql = mysqli_query($connect, "UPDATE produtos SET nome = '$this->nome', preco = '$this->preco' WHERE id = '$id'");

			return true;
		}

		// EXCLUI PRODUTO, VARIAÇÃO E ESTOQUE
		public function delete($connect, $produto_id) {
			$produto_id = intval($produto_id);


			// Primeiro exclui o estoque relacionado
			$sql = mysqli_query($connect, "DELETE e FROM estoque e
                    INNER JOIN variacoes v ON e.variacao_id = v.id
                    WHERE v.produto_id = $produto_id");

			// Segundo exclui as variações relacionadas
			$sqlVariacoes = mysqli_query($connect, "DELETE FROM variacoes WHERE produto_id = '$produto_id'");

	        // Depois exclui o produto
	        $sqlProduto = mysqli_query($connect, "DELETE FROM produtos WHERE id = '$produto_id'");

	        return true;
		}

		// EXCLUI APENAS PRODUTO
		public function deleteProduto($connect, $produto_id) {
			$produto_id = intval($produto_id);

	        $sqlProduto = mysqli_query($connect, "DELETE FROM produtos WHERE id = '$produto_id'");

	        return true;
		}
	}
?>