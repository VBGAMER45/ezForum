var request; //Actual Postvar conf = {};var q; // Input TextjQuery.fn.sugerir = function(opciones) {        conf = tg.extend( {      'limite' : 5,      'filtro' : 3,       'ajaxSuggestUrl' : '',      'max': 6,      'min': 2,    }, opciones);      renderizarEtiquetasActuales();        tg('#searcher_tags').live('click', function(e){        tg('.tag_selectable input').focus();    });        this.live('keyup', function(){        q = tg(this);        tagSendRequest(tg(this));    });        this.live('focusin', function(){        tagSendRequest(tg(this));    });    var x = this;    tg(document).live('click', function(e){        if (tg(e.target).closest('.tag_suggest').length) tagSendRequest(x);        else tg('.tag_suggest').empty();    });    };jQuery.fn.etiquetar = function() {	    this.live('click', function(){        procesarSeleccion(tg(this));    });    };jQuery.fn.teclear = function() {	    this.live('keypress', function(e){        procesarTecla(tg(this), e);    });    };jQuery.fn.tagdelete = function() {	    this.live('click', function(e){        e.preventDefault();        tg(this).parent().remove();        q.removeAttr('readonly');    });    return this;};function tagRemove(btn) {    btn.parent().remove();    if (q != null)        q.removeAttr('readonly');    return btn;};function procesarTecla(campo, evento){    var charCode = evento.charCode || evento.keyCode;    if (charCode == 13) //Enter    {        evento.preventDefault();        if (campo.val().length > 0)        {            addTagFromText(campo.val());        }    }     else if (charCode == 8) //backspace    {        procesarBackspace(campo.val());    }        else if (charCode == 9) //Tab    {            evento.preventDefault();        if (campo.val().length > 0)        {            addTagFromText(campo.val());        }            }    else if (charCode == 32) //Space    {        evento.preventDefault();        if (campo.val().length > 0)        {            addTagFromText(campo.val());        }    }    else if (charCode == 44) //comma    {        evento.preventDefault();        if (campo.val().length > 0)        {            addTagFromText(campo.val());        }    }}function procesarBackspace(texto){    if (texto.length == 0)    {        tg(".tag_selection span:last-child").remove();        q.removeAttr('readonly');    }}function tagSendRequest(campo){    var texto = '';    if (campo.val() != '')        texto = prepararTexto(campo.val());        if (texto.length >= conf.filtro)    {        if (request != null) request.abort();                request = tg.ajax({          url: conf.ajaxSuggestUrl,          type: "POST",          cache: false,          data: {consulta: texto}        }).done(function(data) {            tg('.tag_suggest').empty().html(data);            tg('.opcion').etiquetar();        });    }}function procesarSeleccion(opcion){    var texto = prepararTexto(opcion.text());    var existe = tg(".tag_selection").find('input[alt="' + texto + '"]').length;    var etiquetas = tg(".tag_selection input").length;        if (existe == 0)    {        if (texto.length <= conf.max && conf.min <= texto.length)        {            var label = tg('<span>').attr({id: 'opcion' + opcion.attr('id') }).text(texto);            var accion = tg('<a>').text(" ").attr({href: '#'}).click(function(e){e.preventDefault(); tagRemove(tg(this)); });            var check = tg('<input>').attr({name: 'tags[]', value: opcion.attr('id'), type: "hidden", alt: texto});            label.append(check);            label.append(accion);            tg('.tag_selection').append(label);            tg('.tag_suggest').empty();            tg('#consulta').val('');                        if (etiquetas >= conf.limite)             {                q.attr('readonly', 'readonly');            }        }    }}function addTagFromText(texto){    texto = prepararTexto(texto);    var existe = tg(".tag_selection").find('input[alt="' + texto + '"]').length;    var etiquetas = tg(".tag_selection input").length;        if (existe == 0)    {        if (texto.length <= conf.max && conf.min <= texto.length)        {            var label = tg('<span>').attr({name: texto, "class": "tag_new" }).text(texto);            var accion = tg('<a>').text(" ").attr({href: '#'}).click(function(e){e.preventDefault(); tagRemove(tg(this)); });            var check = tg('<input>').attr({name: 'tags_news[]', value: texto, type: "hidden", alt: texto });                        label.append(check);            label.append(accion);            tg('.tag_selection').append(label);            tg('.tag_suggest').empty();            tg('#consulta').val('');                        if (etiquetas >= conf.limite)             {                q.attr('readonly', 'readonly');            }        }    }}function prepararTexto(texto){        texto = texto.toLowerCase(); // to lower case    texto = jQuery.trim(texto); //remove white spaces at beggining     if (texto.match(/[^a-zA-Z0-9\- ]/g))         texto = texto.replace(/[^a-zA-Z0-9\- ]/g, ''); //only letters, numbers and - are allowed            return texto;}function renderizarEtiquetasActuales(){    var datos = tg('.tag_selectable input').val();    var etiquetas = datos.split(" ");    for (var i = 0; i < etiquetas.length; i ++)    {        if (etiquetas[i].length)            addTagFromText(etiquetas[i]);    }}