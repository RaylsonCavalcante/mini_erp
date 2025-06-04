<?php
	include 'connect.php';

	class Variacao{

		public $produto_id;
		public $descricao;

		//INSERE VARIAÇÃO
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO variacoes(produto_id,descricao) VALUES ('$this->produto_id','$this->descricao')");

			return true;
		}

		//ATUALIZA VARIAÇÃO
		public function update($connect,$id){
			$id = intval($id);
			$sql = mysqli_query($connect, "UPDATE variacoes SET descricao = '$this->descricao' WHERE id = $id");

			return true;
		}

		// EXCLUI VARIAÇÃO
		public function delete($connect, $variacao_id) {
			$variacao_id = intval($variacao_id);

			$sql = mysqli_query($connect, "DELETE FROM variacoes WHERE id = $variacao_id");

	        return true;
		}
	}
?>