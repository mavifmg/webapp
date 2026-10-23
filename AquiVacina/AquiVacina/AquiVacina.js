 function clickMenu() {
            if (Itens.style.display == 'block') {
                Itens.style.display = 'none'
            }
            else {
                Itens.style.display = 'block'
            }
        }

let modoEditar = false;

const botaoEditar = document.getElementById("BEst3");

if (botaoEditar) {

botaoEditar.addEventListener("click", function() {

    modoEditar = true;

    alert("Clique na vacina que deseja editar para continuar!");

});

}

const linhas = document.querySelectorAll(".linhaEstoque");

linhas.forEach(function(linha) {

    linha.addEventListener("click", function() {
        
    if (modoEditar === false) {
        return;
    }

    const vacina = linha.dataset.vacina;
    const quantidade = linha.dataset.quantidade;
    const ubs = linha.dataset.ubs;

    document.getElementById("inputnome").value = vacina;
    document.getElementById("inputestoque").value = quantidade;
    document.getElementById("inputubs").value = ubs;

    document.getElementById("nomeantigo").value = vacina;

    modoEditar = false;

    });

});

