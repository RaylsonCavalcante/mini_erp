<?php
	include 'connect.php';

	class Estoque{

		public $produto_id;
		public $quantidade;

		//INSERE ESTOQUE
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO estoque(produto_id,quantidade) VALUES ('$this->produto_id','$this->quantidade')");

			return true;
		}

		//ATUALIZA ESTOQUE
		public function update($connect,$id){
			$id = intval($id);
			$sql = mysqli_query($connect, "UPDATE estoque SET quantidade = '$this->quantidade' WHERE produto_id = '$id'");

			return true;
		}
	}
?>