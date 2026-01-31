let contadorParcelas = 0;
let arrayParcelas = [];

$(document).ready(function() {
    const id = $('#id').val();
    
    if (!id) {
        // Página de listagem
        if ($('#tabelaTermosPagamento').length) {
            initDataTable();
        }
    } else {
        // Carrega parcelas existentes se for edição
        carregarParcelasExistentes();
    }

    // Evento ao clicar em adicionar parcela
    $('#btnAdicionarParcela').click(function() {
        adicionarParcela();
    });

    // Evento de submit do formulário
    $('#formPaymentTerms').submit(function(e) {
        e.preventDefault();
        salvarTermoPagamento();
    });

    // Evento de remover parcela
    $(document).on('click', '.btnRemoverParcela', function() {
        $(this).closest('tr').remove();
    });
});

function initDataTable() {
    $('#tabelaTermosPagamento').DataTable({
        language: {
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
            "sInfoFiltered": "(Filtrado de _MAX_ registros totais)",
            "sInfoPostFix": "",
            "sInfoThousands": ",",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sSearch": "Pesquisar:",
            "sZeroRecords": "Nenhum registro encontrado",
            "oPaginate": {
                "sFirst": "Primeiro",
                "sLast": "Último",
                "sNext": "Próximo",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '/pagamento/listTerms',
            type: 'POST'
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3, orderable: false, searchable: false }
        ]
    });
}

function adicionarParcela() {
    const quantidade = $('#quantidade_parcelas').val();
    const intervalo = $('#intervalo_dias').val();
    const alterarVencimento = $('#alterar_vencimento').val();

    if (!quantidade || !intervalo) {
        alert('Preencha quantidade de parcelas e intervalo em dias');
        return;
    }

    contadorParcelas++;

    const linha = `
        <tr class="parcela-row">
            <td>${contadorParcelas}</td>
            <td>${quantidade}</td>
            <td>${intervalo}</td>
            <td>${alterarVencimento || 0}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btnRemoverParcela">Excluir</button>
            </td>
        </tr>
    `;

    $('#tabelaParcelas').append(linha);

    arrayParcelas.push({
        numero_parcelas: quantidade,
        intervalo_dias: intervalo,
        alterar_vencimento_dias: alterarVencimento || 0
    });

    // Limpar campos
    $('#quantidade_parcelas').val('');
    $('#intervalo_dias').val('');
    $('#alterar_vencimento').val('');
}

function carregarParcelasExistentes() {
    const linhas = $('#tabelaParcelas tr');
    if (linhas.length > 0) {
        linhas.each(function() {
            const cells = $(this).find('td');
            if (cells.length > 0) {
                const id = $(this).data('id');
                const numeroParcelas = cells.eq(1).text().trim();
                const intervaloDias = cells.eq(2).text().trim();
                const alterarVencimento = cells.eq(3).text().trim();

                arrayParcelas.push({
                    id: id,
                    numero_parcelas: numeroParcelas,
                    intervalo_dias: intervaloDias,
                    alterar_vencimento_dias: alterarVencimento
                });

                contadorParcelas++;
            }
        });
    }
}

function salvarTermoPagamento() {
    const id = $('#id').val();
    const descricao = $('#descricao').val();
    const ativo = $('#ativo').is(':checked');

    if (!descricao) {
        alert('Preencha a descrição do termo de pagamento');
        return;
    }

    const dados = {
        descricao: descricao,
        ativo: ativo,
        parcelas: arrayParcelas
    };

    if (id) {
        dados.id = id;
        const url = '/pagamento/update';
    } else {
        var url = '/pagamento/insert';
    }

    $.ajax({
        type: 'POST',
        url: url,
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(dados),
        success: function(response) {
            if (response.status) {
                showNotification('Sucesso', response.msg, 'success');
                setTimeout(() => {
                    window.location.href = '/pagamento/lista';
                }, 2000);
            } else {
                showNotification('Erro', response.msg, 'danger');
            }
        },
        error: function(xhr) {
            console.error('Erro:', xhr);
            showNotification('Erro', 'Erro ao salvar termo de pagamento', 'danger');
        }
    });
}

function Delete(id) {
    if (confirm('Tem certeza que deseja deletar este termo de pagamento?')) {
        $.ajax({
            type: 'POST',
            url: '/pagamento/delete',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ id: id }),
            success: function(response) {
                if (response.status) {
                    showNotification('Sucesso', response.msg, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('Erro', response.msg, 'danger');
                }
            },
            error: function(xhr) {
                console.error('Erro:', xhr);
                showNotification('Erro', 'Erro ao deletar', 'danger');
            }
        });
    }
}

function showNotification(title, message, type) {
    // Aqui você pode usar Toastr ou outro notificador
    console.log(`${type}: ${title} - ${message}`);
    alert(`${title}: ${message}`);
}
