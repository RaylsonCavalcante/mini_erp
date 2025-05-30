<?php
	include 'connect.php';

	class Cupom{

		public $codigo;
		public $desconto_percentual;
		public $validade;
		public $valor_minimo;

		//INSERE CUMPOM
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO cupons(codigo,desconto_percentual,validade,valor_minimo) VALUES ('$this->codigo','$this->desconto_percentual','$this->validade','$this->valor_minimo')");

			return true;
		}

		//ATUALIZA CUPOM
		public function update($connect,$id){

			$idCupom = intval($id);

			$sql = mysqli_query($connect, "UPDATE cupons SET codigo = '$this->codigo', desconto_percentual = '$this->desconto_percentual', validade = '$this->validade', valor_minimo = '$this->valor_minimo' WHERE id = '$idCupom'");

			return true;
		}

		// EXCLUI CUPOM
		public function delete($connect, $id) {
			$id = intval($id);

	        $sql = mysqli_query($connect, "DELETE FROM cupons WHERE id = '$id'");

	        return true;
		}
	}
?>