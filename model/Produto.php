<?php
	include 'connect.php';

	class Produto{

		public $nome;
		public $preco;
		public $variacoes;

		//INSERE PRODUTO
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO produtos(nome,preco,variacoes) VALUES ('$this->nome','$this->preco','$this->variacoes')");

			return true;
		}

		//ATUALIZA PRODUTO
		public function update($connect,$id){

			$sql = mysqli_query($connect, "UPDATE produtos SET nome = '$this->nome', preco = '$this->preco', variacoes = '$this->variacoes' WHERE id = '$id'");

			return true;
		}

		// EXCLUI PRODUTO
		public function delete($connect, $produto_id) {
			$produto_id = intval($produto_id);

			// Primeiro exclui o estoque relacionado
			$sqlEstoque = mysqli_query($connect, "DELETE FROM estoque WHERE produto_id = '$produto_id'");

			if (!$sqlEstoque) {
	            return false;
	        }

	        // Depois exclui o produto
	        $sqlProduto = mysqli_query($connect, "DELETE FROM produtos WHERE id = '$produto_id'");

	        return true;
		}
	}
?>