var HttpReq = null;
var dest_combo = null;

function ajaxComboBox(url, comboBox){
    dest_combo = comboBox;
    var indice = document.getElementById('local').selectedIndex;
    var id_local = document.getElementById('local').options[indice].getAttribute('value');
    url = url + '?local=' + id_local;

    if (document.getElementById) { //Verifica se o Browser suporta DHTML.
        if (window.XMLHttpRequest) {
            HttpReq = new XMLHttpRequest();
            HttpReq.onreadystatechange = XMLHttpRequestChange;
            HttpReq.open("GET", url, true);
            HttpReq.send(null);
        } else if (window.ActiveXObject) {
            HttpReq = new ActiveXObject("Microsoft.XMLHTTP");
            if (HttpReq) {
                HttpReq.onreadystatechange = XMLHttpRequestChange;
                HttpReq.open("GET", url, true);
                HttpReq.send();
            }
        }
    }
}

function XMLHttpRequestChange() {
    if (HttpReq.readyState == 4 && HttpReq.status == 200){  //Verifica se o arquivo foi carregado com sucesso.
        var result = HttpReq.responseXML;
        var negocio = result.getElementsByTagName("nome");
        document.getElementById(dest_combo).innerHTML = "";
        for (var i = 0; i < negocio.length; i++) {
            new_opcao = create_opcao(negocio[i]);
            document.getElementById(dest_combo).appendChild(new_opcao);
        }
    }
}

function create_opcao(negocios) { //Cria um novo elemento OPTION.
    //return opcao.cloneNode(true);
    var new_opcao = document.createElement("option"); //Cria um OPTION.
    var texto = document.createTextNode(negocios.childNodes[0].data); //Cria um texto.
    new_opcao.setAttribute("value",negocios.getAttribute("id")); //Adiciona o atributo de valor a nova opção.
    new_opcao.appendChild(texto); //Adiciona o texto a OPTION.
    return new_opcao; // Retorna a nova OPTION.
}
