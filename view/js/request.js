//NAV BAR
$(document).ready(function(){
  $('a[data-scroll]').click(function(e){
    e.preventDefault();
    const target = $(this).data('scroll');
    const offset = $(target).offset().top - 60; // 60px para compensar o navbar fixo

    $('html, body').animate({
      scrollTop: offset
    }, 600);
  });
});

//CADASTRO DE PRODUTO
$(document).ready(function(){
	$("#formCadastro").submit(function (event) {
        event.preventDefault();

        // Mostra o spinner
  		$('#spinnerCadastrar').removeClass('d-none');
  		$('#btnCadastrar').prop('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=cadastroProduto",
			async: true,
			dataType: 'json',
			data: {
				nome: $("#nome").val(),
                preco: $("#preco").val(),
                variacoes: $("#variacoes").val(),
                estoque: $("#estoque").val(),
			},
			success: function(data){
				var response = data;

			    if(response.success) {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			        $("input").val('');
			        
			        // Oculta o spinner
      				$('#spinnerCadastrar').addClass('d-none');
      				$('#btnCadastrar').prop('disabled', false);

      				atualizarListaProdutos();
			    } else {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					// Oculta o spinner
      				$('#spinnerCadastrar').addClass('d-none');
      				$('#btnCadastrar').prop('disabled', false);
			    }
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				// Oculta o spinner
      			$('#spinnerCadastrar').addClass('d-none');
      			$('#btnCadastrar').prop('disabled', false);
			}
		});
    });
});


//LISTAR PRODUTOS
$(document).ready(function(){
	$.ajax({
		type: "POST",
		url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarProdutos",
		success: function(data){
			$("#listarProdutos").html(data);
		}
	});
});

//ATUALIZA LISTA DE PRODUTOS
function atualizarListaProdutos(){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarProdutos",
			success: function(data){
				$("#listarProdutos").html(data);
			}
		});
	});
}

//MOSTRA TELA DE EDIÇÃO COM OS DADOS DO PRODUTO
function editarProduto(id){
	$(document).ready(function(){
		$("#divEditar").show();
		$("#divCadastrar").hide();

		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=dadosProduto",
			async: true,
			dataType: 'json',
			data:{
				id: id
			},
			success: function(data){
				$("#idProduto").val(data.id);
				$("#nomeEditar").val(data.nome);
				$("#precoEditar").val(data.preco);
				$("#variacoesEditar").val(data.variacoes);
				$("#estoqueEditar").val(data.estoque);

				$('html, body').animate({
				  scrollTop: $('#divCadastrar').offset().top
				}, 200); // 500 ms = meio segundo
			}
		});
	});
}
//OCULTA TELA DE EDIÇÃO
function voltar(){
	$(document).ready(function(){
		$("#divEditar").hide();
		$("#divCadastrar").show();
	});
}

//EDIÇÃO DE PRODUTO
$(document).ready(function(){
	$("#btnAtualizar").click(function (event) {
        event.preventDefault();

        // Mostra o spinner
  		$('#spinnerEditar').removeClass('d-none');
  		$('#btnAtualizar').prop('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=atualizaProduto",
			async: true,
			dataType: 'json',
			data: {
				id: $("#idProduto").val(),
				nome: $("#nomeEditar").val(),
                preco: $("#precoEditar").val(),
                variacoes: $("#variacoesEditar").val(),
                estoque: $("#estoqueEditar").val(),
			},
			success: function(data){
				var response = data;

			    if(response.success) {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			        
			        // Oculta o spinner
      				$('#spinnerEditar').addClass('d-none');
      				$('#btnAtualizar').prop('disabled', false);

      				atualizarListaProdutos();

      				$("#divEditar").hide();
							$("#divCadastrar").show();
			    } else {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					// Oculta o spinner
      				$('#spinnerEditar').addClass('d-none');
      				$('#btnAtualizar').prop('disabled', false);
			    }
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				// Oculta o spinner
      			$('#spinnerEditar').addClass('d-none');
      			$('#btnAtualizar').prop('disabled', false);
			}
		});
    });
});

function alertExclusao(id) {
	Swal.fire({
	  text: "Excluir Produto?",
	  icon: 'question',
	  cancelButtonText: 'Não',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Sim'
	}).then((result) => {
		if (result.value) {
			excluirProduto(id);
		}
	});
}

//EXCLUIR PRODUTO
function excluirProduto(id){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=excluirProduto",
			async: true,
			dataType: 'json',
			data:{
				id: id
			},
			success: function(data){

				var response = data;

			    if(response.success) {

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					atualizarListaProdutos();
					atualizaCarrinho();
					localStorage.clear();
					$("input").val('');
					$('#dadosEndereco').hide();
					$('#divEmail').hide();
					$("#divEditar").hide();
					$("#divCadastrar").show();

				}else{

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
				}
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
			}
		});
	});
}

//MOSTRA CARRINHO ASSIM QUE ATUALIZA A PAGINA
$(document).ready(function(){
	$.ajax({
	    type: "POST",
	    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=carrinho",
	    async: true,
		dataType: 'text',
	    success: function (data) {
	      	if (data === '0') {
	    		$('#carrinho').html("<p style='font-size:20px;'>Carrinho vazio...</p>");
	    		// $("#divCarrinho").style('display','none');
	    		$("#divCep").hide();
	    	}else{
	      		
	      		$('#carrinho').html(data);
	      		$("#divCep").show();
	    	}
	    }
	});
});

//ATUALIZA CARRINHO
function atualizaCarrinho(){
	$(document).ready(function(){
		$.ajax({
		    type: "POST",
		    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=carrinho",
		    async: true,
			dataType: 'text',
		    success: function (data) {
		    	if (data === '0') {
		    		$('#carrinho').html("<p style='font-size:20px;'>Carrinho vazio...</p>");
			    	$("#divCep").hide();
		    	}else{
		      		
		      		$('#carrinho').html(data);
		      		$("#divCep").show();
		    	}
		    }
		});
	});
}

//ADICIONA PRODUTO AO CARRINHO
function adicionarProdutoCarrinho(id){
	$(document).ready(function(){
		$.ajax({
		    type: "POST",
		    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=adicionarProdutoCarrinho",
		    async: true,
			dataType: 'text',
		    data: {
		      id: id
		    },
		    success: function (data) {
		      	atualizaCarrinho();
		      	$('html, body').animate({
						  scrollTop: $('#divCarrinho').offset().top - 60
						}, 200);
		    },
		    error: function (data) {
		      alert("Erro ao adicionar produto.");
		    }
		});
	});
}

//REMOVE PRODUTO DO CARRINHO
function removerProdutoCarrinho(id){
	$(document).ready(function(){
		$.ajax({
		    type: "POST",
		    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=exclueProdutoCarrinho",
		    async: true,
			dataType: 'json',
		    data: {
		      id: id
		    },
		    success: function (data) {
				var response = data;

				if(response.success) {
					atualizaCarrinho();
					localStorage.clear();
					$("input").val('');
					$('#dadosEndereco').hide();
					$('#divEmail').hide();
				}else{
					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
				}
		    },
		    error: function (data) {
		      	Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
		    }
		});
	});
}

//BTN ADICIONAR QUANTIDADE
function btnAdd(id){
	$(document).ready(function(){
		const input = document.getElementById("qtd"+id);
		let current = parseInt(input.value);
		if (current < parseInt(input.max)) {

			$.ajax({
			    type: "POST",
			    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=atualizaQuantidadeProduto",
			    async: true,
				dataType: 'json',
			    data: {
			      id: id,
			      function : 'add'
			    },
			    success: function (data) {

					var response = data;

					if(response.success) {
						atualizaCarrinho();
					}else{
						Swal.fire({
							toast: true,
						    position: 'top-end',
						    icon: 'error',
						    title: response.message,
						    showConfirmButton: false,
						    timer: 3000,
						    timerProgressBar: true
						});
					}
			    },
			    error: function(data){
			    	Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: 'Erro de conexão!',
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			    }
			});
		}
	});
}

//BTN REMOVER QUANTIDADE
function btnRemove(id){
	$(document).ready(function(){
		const input = document.getElementById("qtd"+id);
		let current = parseInt(input.value);
		if (current > parseInt(input.min)) {

			$.ajax({
			    type: "POST",
			    url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=atualizaQuantidadeProduto",
			    async: true,
				dataType: 'json',
			    data: {
			      id: id,
			      function : 'remove'
			    },
			    success: function (data) {

					var response = data;

					if(response.success) {
						atualizaCarrinho();
					}else{
						Swal.fire({
							toast: true,
						    position: 'top-end',
						    icon: 'error',
						    title: response.message,
						    showConfirmButton: false,
						    timer: 3000,
						    timerProgressBar: true
						});
					}
			    },
			    error: function(data){
			    	Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: 'Erro de conexão!',
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			    }
			});
		}
	});
}

//BUSCA CEP
function buscarCep() {
	$(document).ready(function(){
		// Mostra o spinner
		$('#spinnerCep').removeClass('d-none');
		$('#btnCep').prop('disabled', true);

	    let cep = document.getElementById('cep').value.replace(/\D/g, '');
	    if (cep.length !== 8 || "") {
		    Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'CEP inválido',
				    text: 'Digite um CEP com 8 dígitos.',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});

				// Remove o spinner
				$('#spinnerCep').addClass('d-none');
				$('#btnCep').prop('disabled', false);
	    }else{

		    $.ajax({
			  url: `https://viacep.com.br/ws/${cep}/json/`,
			  method: 'GET',
			  async: true,
			  dataType: 'json',
			  success: function(data) {
			  	console.log(data)
			    if (!data) {
			      Swal.fire({
			        toast: true,
			        position: 'top-end',
			        icon: 'error',
			        title: 'CEP não encontrado',
			        text: 'Verifique o CEP digitado.',
			        showConfirmButton: false,
			        timer: 3000,
			        timerProgressBar: true
			      });
			      // Remove o spinner
						$('#spinnerCep').addClass('d-none');
						$('#btnCep').prop('disabled', false);
			    }else{
				    $('#endereco').text(
				    	 data['logradouro']+', '
				    	+data['bairro']+' - '
				    	+data['localidade']+' - '
				    	+data['uf']
				    );
				    $('#dadosEndereco').show();
				    $('#divEmail').show();

				    // Salvar endereço no localStorage
						localStorage.setItem('rua', data['logradouro']);
						localStorage.setItem('bairro', data['bairro']);
						localStorage.setItem('cidade', data['localidade']);
						localStorage.setItem('estado', data['uf']);

				    // Remove o spinner
						$('#spinnerCep').addClass('d-none');
						$('#btnCep').prop('disabled', false);
				}
			  },
			  error: function(data) {
			    Swal.fire({
			      toast: true,
			      position: 'top-end',
			      icon: 'error',
			      title: 'Não foi possível buscar o CEP',
			      showConfirmButton: false,
			      timer: 3000,
			      timerProgressBar: true
			    });

			    // Remove o spinner
					$('#spinnerCep').addClass('d-none');
					$('#btnCep').prop('disabled', false);
			  }
			});
		}
	});

}

//CADASTRO DE CUPOM
$(document).ready(function(){
	$("#formCupom").submit(function (event) {
        event.preventDefault();

        // Mostra o spinner
  		$('#spinnerCadastrarCupom').removeClass('d-none');
  		$('#btnCadastrarCupom').prop('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=cadastroCupom",
			async: true,
			dataType: 'json',
			data: {
				codigo: $("#codigoCupom").val(),
                descontoPercentual: $("#descontoPercentual").val(),
                validadeCupom: $("#validadeCupom").val(),
                valorMinimo: $("#valorMinimo").val(),
			},
			success: function(data){
				var response = data;

			    if(response.success) {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			        $("input").val('');
			        
			        // Oculta o spinner
      				$('#spinnerCadastrarCupom').addClass('d-none');
      				$('#btnCadastrarCupom').prop('disabled', false);

      				atualizarListaCupons();
			    } else {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					// Oculta o spinner
      				$('#spinnerCadastrarCupom').addClass('d-none');
      				$('#btnCadastrarCupom').prop('disabled', false);
			    }
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				// Oculta o spinner
      			$('#spinnerCadastrarCupom').addClass('d-none');
      			$('#btnCadastrarCupom').prop('disabled', false);
			}
		});
    });
});

//MOSTRA OS CUPONS
$(document).ready(function(){
	$.ajax({
		type: "POST",
		url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarCupons",
		success: function(data){
			$("#listarCupons").html(data);
		}
	});
});

//ATUALIZA LISTA DE CUPONS
function atualizarListaCupons(){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarCupons",
			success: function(data){
				$("#listarCupons").html(data);
			}
		});
	});
}

//MOSTRA TELA DE EDIÇÃO COM OS DADOS DO CUPOM
function editarCupom(id){
	$(document).ready(function(){
		$("#divEditarCupom").show();
		$("#divCadastroCupons").hide();

		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=dadosCupom",
			async: true,
			dataType: 'json',
			data:{
				id: id
			},
			success: function(data){
				$("#idCupom").val(data.id);
				$("#codigoCupomEditar").val(data.codigo);
				$("#descontoPercentualEditar").val(data.desconto_percentual);
				$("#validadeCupomEditar").val(data.validade);
				$("#valorMinimoEditar").val(data.valor_minimo);
			}
		});
	});
}
//OCULTA TELA DE EDIÇÃO CUPOM
function voltarCadastroCupom(){
	$(document).ready(function(){
		$("#divEditarCupom").hide();
		$("#divCadastroCupons").show();
	});
}

//EDIÇÃO DE CUPOM
$(document).ready(function(){
	$("#formEditarCupom").submit(function (event) {
      event.preventDefault();

      // Mostra o spinner
  		$('#spinnerEditarCupom').removeClass('d-none');
  		$('#btnAtualizarCupom').prop('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=atualizaCupom",
			async: true,
			dataType: 'json',
			data: {
				id: $("#idCupom").val(),
				codigo: $("#codigoCupomEditar").val(),
				desconto_percentual: $("#descontoPercentualEditar").val(),
				validade: $("#validadeCupomEditar").val(),
				valor_minimo: $("#valorMinimoEditar").val(),
			},
			success: function(data){
				var response = data;

			    if(response.success) {
			        Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
			        
			        // Oculta o spinner
      				$('#spinnerEditarCupom').addClass('d-none');
      				$('#btnAtualizarCupom').prop('disabled', false);

      				atualizarListaCupons();

      				$("#divEditarCupom").hide();
					$("#divCadastroCupons").show();
			    } else {
			        Swal.fire({
						toast: tsrue,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					// Oculta o spinner
      				$('#spinnerEditarCupom').addClass('d-none');
      				$('#btnAtualizarCupom').prop('disabled', false);
			    }
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				// Oculta o spinner
      			$('#spinnerEditarCupom').addClass('d-none');
      			$('#btnAtualizarCupom').prop('disabled', false);
			}
		});
    });
});

function alertExclusaoCupom(id) {
	Swal.fire({
	  text: "Excluir Cupom?",
	  icon: 'question',
	  cancelButtonText: 'Não',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Sim'
	}).then((result) => {
		if (result.value) {
			excluirCupom(id);
		}
	});
}

//EXCLUIR PRODUTO
function excluirCupom(id){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=excluirCupom",
			async: true,
			dataType: 'json',
			data:{
				id: id
			},
			success: function(data){

				var response = data;

			    if(response.success) {

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					atualizarListaCupons();

				}else{

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
				}
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
			}
		});
	});
}

//APLICAR CUPOM
function aplicarCupom(event){
	event.preventDefault();
	$.ajax({
		type: "POST",
		url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=aplicarCupom",
		async: true,
		dataType: 'json',
		data:{
			cupom: $("#cupom").val(),
		},
		success: function(data){

			var response = data;

	    if(response.success) {

			Swal.fire({
				toast: true,
			    position: 'top-end',
			    icon: 'success',
			    title: response.message,
			    showConfirmButton: false,
			    timer: 3000,
			    timerProgressBar: true
			});

			atualizaCarrinho();

			}else{

				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: response.message,
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				atualizaCarrinho();
			}
		},
		error: function(data){
			Swal.fire({
				toast: true,
			    position: 'top-end',
			    icon: 'error',
			    title: 'Erro de conexão!',
			    showConfirmButton: false,
			    timer: 3000,
			    timerProgressBar: true
			});
		}
	});
}

//REMOVER CUPOM
function removerCupom(){
	$.ajax({
		type: "POST",
		url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=removerCupom",
		async: true,
		dataType: 'json',
		success: function(data){

			var response = data;

	    if(response.success) {

			Swal.fire({
				toast: true,
			    position: 'top-end',
			    icon: 'success',
			    title: response.message,
			    showConfirmButton: false,
			    timer: 3000,
			    timerProgressBar: true
			});

			atualizaCarrinho();

			}else{

				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: response.message,
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
				atualizaCarrinho();
			}
		},
		error: function(data){
			Swal.fire({
				toast: true,
			    position: 'top-end',
			    icon: 'error',
			    title: 'Erro de conexão!',
			    showConfirmButton: false,
			    timer: 3000,
			    timerProgressBar: true
			});
		}
	});
}

//FINALIZAR PEDIDO
function finalizarPedido(total){
	if(localStorage.getItem('rua') && $("#numero").val() != "" && $("#email").val() != "" && $("#cep").val() != ""){
    let cep = document.getElementById('cep').value.replace(/\D/g, '');

    // Mostra o spinner
		$('#spinnerFinalizar').removeClass('d-none');
		$('#btnFinalizar').prop('disabled', true);

		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=salvaPedido",
			async: true,
			dataType: 'json',
			data: {
				cep: cep,
				rua: localStorage.getItem('rua'),
				bairro: localStorage.getItem('bairro'),
				cidade: localStorage.getItem('cidade'),
				estado: localStorage.getItem('estado'),
				numero: $("#numero").val(),
				valor_total: total,
				email: $("#email").val()
			},
			success: function(data){

				var response = data;

		    if(response.success) {

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					atualizaCarrinho();
					atualizarListaProdutos();
					atualizarListaPedidos();
					atualizarListaCupons();
					localStorage.clear();
					$("input").val('');
					$('#dadosEndereco').hide();
					$('#divEmail').hide();

					// Oculta o spinner
					$('#spinnerFinalizar').addClass('d-none');
					$('#btnFinalizar').prop('disabled', false);

					$('html, body').animate({
					  scrollTop: $('#divPedidos').offset().top - 60
					}, 200);

				}else{

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					// Oculta o spinner
					$('#spinnerFinalizar').addClass('d-none');
					$('#btnFinalizar').prop('disabled', false);
				}
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});

				// Oculta o spinner
				$('#spinnerFinalizar').addClass('d-none');
				$('#btnFinalizar').prop('disabled', false);
			}
		});

	}else{
		Swal.fire({
			toast: true,
		    position: 'top-end',
		    icon: 'info',
		    title: 'Preencha Cep,Numero e Email!',
		    showConfirmButton: false,
		    timer: 3000,
		    timerProgressBar: true
		});
	}
}

//LISTAR PEDIDOS
$(document).ready(function(){
	$.ajax({
		type: "POST",
		url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarPedidos",
		success: function(data){
			$("#listarPedidos").html(data);
		}
	});
});

//ATUALIZA LISTA DE PEDIDOS
function atualizarListaPedidos(){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=listarPedidos",
			success: function(data){
				$("#listarPedidos").html(data);
			}
		});
	});
}

//EXCLUIR PEDIDO
function excluirPedido(id){
	$(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=excluirPedido",
			async: true,
			dataType: 'json',
			data:{
				id: id
			},
			success: function(data){

				var response = data;

			    if(response.success) {

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'success',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});

					atualizarListaPedidos();

				}else{

					Swal.fire({
						toast: true,
					    position: 'top-end',
					    icon: 'error',
					    title: response.message,
					    showConfirmButton: false,
					    timer: 3000,
					    timerProgressBar: true
					});
				}
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
			}
		});
	});
}



//ENVIO WEBHOOK
$(document).ready(function(){
	$("#formWebhook").submit(function (event) {
    event.preventDefault();

      // Mostra o spinner
		$('#spinnerEnviar').removeClass('d-none');
		$('#btnEnviar').prop('disabled', true);
		
		$.ajax({
			type: "POST",
			url: "http://localhost/mini-erp/controller/controllerERP.php?ctrl=webhook",
			async: true,
			dataType: 'json',
			data: {
				jsonWebhook: $("#jsonWebhook").val(),
			},
			success: function(data){
				var response = data;

			    if(response.success) {
			        Swal.fire({
								toast: true,
						    position: 'top-end',
						    icon: 'success',
						    title: response.message,
						    showConfirmButton: false,
						    timer: 3000,
						    timerProgressBar: true
							});
			        $("input").val('');
			        atualizarListaPedidos();
			        
			        // Oculta o spinner
      				$('#spinnerEnviar').addClass('d-none');
      				$('#btnEnviar').prop('disabled', false);

			    } else {
			        Swal.fire({
								toast: true,
						    position: 'top-end',
						    icon: 'error',
						    title: response.message,
						    showConfirmButton: false,
						    timer: 3000,
						    timerProgressBar: true
							});

							// Oculta o spinner
      				$('#spinnerEnviar').addClass('d-none');
      				$('#btnEnviar').prop('disabled', false);
			    }
			},
			error: function(data){
				Swal.fire({
					toast: true,
				    position: 'top-end',
				    icon: 'error',
				    title: 'Erro de conexão!',
				    showConfirmButton: false,
				    timer: 3000,
				    timerProgressBar: true
				});
						// Oculta o spinner
      			$('#spinnerEnviar').addClass('d-none');
      			$('#btnEnviar').prop('disabled', false);
			}
		});
    });
});