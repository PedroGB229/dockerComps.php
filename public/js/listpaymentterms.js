import { DataTables } from "./DataTables.js";

const tabela = new $("#tabela").DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    stateSave: true,
    select: true,
    processing: true,
    serverSide: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json',
        searchPlaceholder: 'Digite sua pesquisa...'
    },
    ajax: {
        url: '/pagamento/listaPaymentTerms',
        type: 'POST'
    },
    layout: {
        topStart: 'search',
        topEnd: 'pageLength',
        bottomStart: 'info',
        bottomEnd: 'paging'
    },
    // ✅ Aqui aplicamos a estilização após a tabela estar pronta
    initComplete: function () {
        setTimeout(() => {
            // Remove o label "Pesquisar"
            const label = document.querySelector('.dt-search label');
            if (label) {
                label.remove(); // Remove completamente do DOM
            }
            // Seleciona div que contém o campo de pesquisa
            const searchDiv = document.querySelector('.row > div.dt-layout-start');
            if (searchDiv) {
                searchDiv.classList.remove('col-md-auto');
                searchDiv.classList.add('col-lg-6', 'col-md-6', 'col-sm-12');
            }
            const divSearch = document.querySelector('.dt-search');
            if (divSearch) {
                divSearch.classList.add('w-100'); // ou w-100, w-75 etc.
            }

            const input = document.querySelector('#dt-search-0');
            if (input) {
                input.classList.remove('form-control-sm'); // ou w-100, w-75 etc.
                input.classList.add('form-control-md', 'w-100'); // ou w-100, w-75 etc.
                // Remove margem e padding da esquerda
                input.style.marginLeft = '0';
                input.focus();
            }
            const pageLength = document.querySelector('#dt-length-0');
            if (pageLength) {
                pageLength.classList.add('form-select-md'); // ou form-select-sm, dependendo do tamanho desejado
            }
        }, 100);
    }
});

async function Delete(id) {
    const formData = new FormData();
    formData.append('id', id);

    const response = await fetch('/paymentterms/delete', {
        method: 'POST',
        body: formData
    });

    const data = await response.json();

    if (!data.status) {
        Swal.fire({
            title: "Erro ao remover!",
            icon: "error",
            html: data.msg,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        return;
    }
    Swal.fire({
        title: "Removido com sucesso!",
        icon: "success",
        html: data.msg,
        timer: 3000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    tabela.ajax.reload();
}
window.Delete = Delete;