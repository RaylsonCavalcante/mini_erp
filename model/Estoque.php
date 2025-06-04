<?php
	include 'connect.php';

	class Estoque{

		public $variacao_id;
		public $quantidade;

		//INSERE ESTOQUE
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO estoque(variacao_id,quantidade) VALUES ('$this->variacao_id','$this->quantidade')");

			return true;
		}

		//ATUALIZA ESTOQUE
		public function update($connect){
			$sql = mysqli_query($connect, "UPDATE estoque SET quantidade = '$this->quantidade' WHERE variacao_id = '$this->variacao_id'");

			return true;
		}

		//ATUALIZA QUANTIDADE ESTOQUE
		public function updateQuantidade($connect, $estoque_id){
			$estoque_id = intval($estoque_id);

			$sql = mysqli_query($connect, "UPDATE estoque SET quantidade = '$this->quantidade' WHERE id = '$estoque_id'");

			return true;
		}

		// EXCLUI ESTOQUE
		public function delete($connect, $estoque_id) {
			$estoque_id = intval($estoque_id);

			$sql = mysqli_query($connect, "DELETE FROM estoque WHERE id = $estoque_id");

	        return true;
		}
	}
?>