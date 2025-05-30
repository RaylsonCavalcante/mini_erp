<?php
	include 'connect.php';

	class Pedido{

		public $status;
		public $cep;
		public $endereco;
		public $data_pedido;
		public $valor_total;
		public $produtos;

		//INSERE PEDIDO
		public function create($connect){

			$sql = mysqli_query($connect,"INSERT INTO pedidos(status,cep,endereco,data_pedido,valor_total,produtos) VALUES ('$this->status','$this->cep','$this->endereco','$this->data_pedido','$this->valor_total','$this->produtos')");

			return true;
		}

		//ATUALIZA PEDIDO
		public function update($connect,$id,$status){

			$idPedido = intval($id);

			if ($status) {
				
				$sql = mysqli_query($connect, "UPDATE pedidos SET status = '$status' WHERE id = '$idPedido'");

				return true;

			}

		}

		// EXCLUI PEDIDO
		public function delete($connect, $id) {
			$id = intval($id);

	        $sql = mysqli_query($connect, "DELETE FROM pedidos WHERE id = '$id'");

	        return true;
		}
	}
?>