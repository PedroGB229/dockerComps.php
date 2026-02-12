import { Validate } from "./Validate.js";
import { Requests } from "./Requests.js";

const InsertButton = document.getElementById('insert');
const Action = document.getElementById('acao');
const Form = document.getElementById('form');

// Verifica se Action existe
if (!Action) console.error('Elemento "acao" não encontrado no formulário');

// Máscaras
$('#codigo_barra').inputmask({
    mask: "9999999999999",
    placeholder: ""
});

$('#preco_custo, #preco_venda').inputmask('decimal', {
    radixPoint: ",",
    groupSeparator: ".",
    digits: 2,
    autoGroup: true,
    rightAlign: false,
    removeMaskOnSubmit: false
});

// Converte valores monetários para ponto antes do envio
function formatDecimal(value) {
    if (!value) return 0;
    return value.toString().replace(/\./g, '').replace(',', '.');
}

// Prepara os dados do form
function getFormData() {
    // Corrige os campos decimais
    document.getElementById('preco_custo').value = formatDecimal(document.getElementById('preco_custo').value);
    document.getElementById('preco_venda').value = formatDecimal(document.getElementById('preco_venda').value);

    const formData = new FormData(Form);

    // Corrige os booleanos para true/false
    formData.set('ativo', document.getElementById('ativo').checked); 
    formData.set('excluido', document.getElementById('excluido').checked); 

    return formData;
}

async function insert() {
    if (!Validate.SetForm('form').Validate()) {
        return Swal.fire({
            icon: "error",
            title: "Por favor preencha corretamente os campos!",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => Swal.showLoading()
        });
    }

    try {
        const response = await Requests.SetForm('form').Post('/produto/insert');

        if (!response.status) {
            return Swal.fire({
                icon: "error",
                title: response.msg,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });
        }

        Action.value = 'e';
        document.getElementById('id').value = response.id;

        Swal.fire({
            icon: "success",
            title: response.msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => Swal.showLoading(),
            willClose: () => window.location.href = '/produto/lista'
        });
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: "error",
            title: "Erro de conexão, tente novamente!",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}

async function update() {
    //Valida todos os campos do formulário
    const IsValid = Validate
        .SetForm('form')//Inform o ID do form
        .Validate();//Aplica a validação no campos 
    if (!IsValid) {
        Swal.fire({
            icon: "error",
            title: "Por favor preencha corretamente os campos!",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        //Em caso de erro encerramos o processo.
        return;
    }
    const response = await Requests.SetForm('form').Post('/produto/update');
    if (!response.status) {
        Swal.fire({
            icon: "error",
            title: response.msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        return;
    }
    Swal.fire({
        icon: "success",
        title: response.msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
        },
        willClose: () => {
            //Redireciona automaticamente para a lista de fornecedores
            window.location.href = '/produto/lista';
        }
    });
}

// Botão salvar
InsertButton.addEventListener('click', async () => {
    if (!Action) return console.error('Campo "acao" não definido');
    Action.value === 'c' ? await insert() : await update();
});

// Depuração
document.addEventListener('DOMContentLoaded', () => {
    if (!Action) console.warn('Campo "acao" não encontrado');
});
