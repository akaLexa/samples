let mwce = {};

mwce.waitAsync = true;
mwce.debugMode = false;

mwce._isOpenAjax = false;
mwce._openNotify = 0;

mwce.lang = {};
mwce.errors = [];


mwce.lang = {
    'alertTitle' : 'Внимание'
};


mwce.errors[1] = 'Не указан параметр \'address\' в ajax.';
mwce.errors[2] = 'Предыдущее действие еще не завершено. Пожалуйста, попробуйте еще раз позднее';




mwce.ajax = function (params) {

    if(!params["dataType"]){
        params["dataType"] = 'html';
        if(mwce.debugMode)
            console.warn('-> dataType is empty. Default set html');
    }

    if(!params["type"]){
        params["type"] = 'GET';
        if(mwce.debugMode)
            console.warn('-> type is empty. Default set GET');
    }
    else{
        params["type"] = params["type"].toUpperCase();
    }

    if(!params["data"]){
        params["data"] = "";

        if(mwce.debugMode && params["type"] !== 'GET')
            console.warn('-> Maybe send data is empty!');
    }

    if(!params["address"]){
        throw 1;
    }

    if (mwce.waitAsync && mwce._isOpenAjax){
        throw 2;
    }

    $.ajax({
        url: params["address"],
        cache: false,
        type: params["type"],
        data: params["data"],
        dataType: params["dataType"],//xml, json, jsonp, script, html, text
        async: true,
        beforeSend: function(){
            mwce._isOpenAjax = true;

            if(params["loadicon"] && params["element"])
            {
                $('#' + params['element']).empty();
                $('#' + params['element']).append(params['loadicon']);
            }

            if(params["before"]){
                params["before"]();
            }
        },
        success: function(response)
        {
            mwce._isOpenAjax = false;
            if(params["element"])
            {
                $('#' + params["element"]).empty();

                if(params["fade"] !== undefined)
                    $('#' + params["element"]).append(response).fadeIn(params["fade"]);
                else
                    $('#' + params["element"]).append(response);
            }

            if(params["callback"])
            {
                params["callback"](response);
            }

        },
        error:  function(jqXHR, textStatus, errorThrown){

            mwce._isOpenAjax = false;

            if(params["error"])
            {
                params["error"](jqXHR, textStatus, errorThrown);
            }
            else
            {
                if(params["element"]){

                    $("#" + params["element"]).empty();

                    if(textStatus)
                        $("#"+params["element"]).append(textStatus);
                    else
                        $("#"+params["element"]).append("Resource load error. Maybe wrong web address");
                }
                else{

                    if(textStatus)
                        console.error('-> ' + textStatus);
                    else
                        console.error('-> Resource load error. Maybe wrong web address;');
                }
            }
        }
    });
};

mwce.genIn = function (params) {
    if (params["type"]) {
        params["type"] = params["type"].toUpperCase();
    }

    if(params['alertErrors'] === undefined){
        params['alertErrors'] = true;
    }

    try {
        mwce.ajax(params);
    }
    catch (e) {
        if (typeof e == 'number') {
            var msg = mwce.errors[e] ? mwce.errors[e] : 'error ' + e;
            console.warn(' -> ',msg);
            if(params['alertErrors']){
                mwce.alert(msg);
            }
        }
        else {
            console.error(e);
        }
    }
};

mwce.alert = function (msg,title) {

    if(!msg){
        msg = 'em.. message text is empty 0_o';
    }
    if(!title){
        title = mwce.lang['alertTitle'] ? mwce.lang['alertTitle'] : 'Warning!';
    }

    $('<div/>').dialog({
        title: title,
        modal: true,
        buttons: {
            Ok: function() {
                $( this ).dialog( 'close' );
            }
        },
        open:function () {
            this.innerHTML = msg;
        },
        close:function () {
            $( this ).dialog( 'destroy' );
        }
    });
};

mwce.confirm = function (params){
    mwce.confirm.close();

    if(params instanceof Object){

        if(!params['title']){
            params['title'] = mwce.lang['alertTitle'] ? mwce.lang['alertTitle'] : 'Attention!';
        }

        if(!params['text']){
            console.warn('[mwce.confirm]: params[text] is empty!');
            return;
        }

        if(params['buttons'] === undefined || !(params['buttons'] instanceof Object))
        {
            console.warn('[mwce.confirm]: params[buttons] is empty or wrong!');
            return;
        }

        if(!params['width'])
            params['width'] = 400;

        if(!params['height'])
            params['height'] = 'auto';

        $('<div/>').dialog({
            resizable: false,
            height: params['height'],
            width: params['width'],
            title:params['title'],
            modal: true,
            buttons:params['buttons'],
            open:function () {
                mwce.confirm._body = this;
                this.innerHTML = params['text'];
            },
            close:function () {
                mwce.confirm._body = undefined;
                $(this).dialog('destroy');
            }
        });
    }
    else{
        console.warn('[mwce_confirm]: params must be a JSON');
    }
};
/*
mwce.dialog = function (params) {
    let options = {
        content: '',
        title: '',
        height: 50,
        width: 200,
        modal: false,
        hideOnLick:false,
        closeShow:true,
        position: '',
        buttons: {},
        actions: {},
        target:undefined
    };

    for(let id in options){
        if(params[id] !== undefined){
            options[id] = params[id];
        }
    }

    let dialog = document.createElement('dialog');
    dialog.classList.add('mwce_dialog');
    //dialog.open = 'true';

    if (options.title.length > 0){
        let header = document.createElement('div');
        header.classList.add('mwce_dialog_title');
        header.innerHTML = options.title;
        dialog.appendChild(header);
    }

    if(options['closeShow'] === true){
        let closebt = document.createElement('b');
        closebt.classList.add('mwce_close');
        closebt.onclick = function () {
            dialog.close();
        };
        dialog.appendChild(closebt);
    }

    let body = document.createElement('div');
    body.classList.add('mwce_dialog_content');
    body.innerHTML = options['content'];
    dialog.appendChild(body);

    dialog.style.width = options['width'] + 'px';
    dialog.style.height = options['height'] + 'px';
    dialog.tabIndex = 0;

    if(options.modal === true){
        body.classList.add('mwce_dialog_bg');
    }

    for(let name in options['actions']){
        dialog.addEventListener(name,options['actions']['name']);
    }

    if(options['hideOnLick'] === true && options.modal !== true){
        dialog.addEventListener('blur',function(e){
            dialog.close();
        });
    }

    document.body.appendChild(dialog);
    if(options.modal !== true){
        dialog.show();
    }
    else {
        dialog.showModal();
    }
    //console.log(dialog.clientWidth,dialog.clientHeight)
    if(options.target !== undefined && typeof options.target === 'object'){


        $(dialog).offset($(options.target).offset());
    }


};

*/

mwce.notify = function (msg,title,type,callback,delayz) {

    let options = {
        msg: '',
        type: 0,
        delayz: undefined,
        callback: undefined,
        title: undefined,
    };

    if (typeof msg === 'object') {
        options.msg = msg['msg'] !== undefined && msg['msg'] !== null ? msg['msg'] : '';
        options.type = msg['type'] !== undefined && msg['type'] !== null ? parseInt(msg['type']) : 0;
        options.delayz = msg['delayz'] !== undefined && msg['delayz'] !== null ? parseInt(msg['delayz']) : undefined;
        options.callback = msg['callback'] !== undefined && msg['callback'] !== null ? parseInt(msg['delayz']) : undefined;
        options.title = msg['title'] !== undefined && msg['title'] !== null ? msg['title'] : undefined;
    }
    else {

        options.msg = msg !== undefined && msg !== null ? msg  : '';
        options.type = type !== undefined && type !== null ? parseInt(type) : 0;
        options.delayz = delayz !== undefined && delayz !== null ? parseInt(delayz) : 1000;
        options.callback = callback !== undefined && callback !== null ? parseInt(callback) : undefined;
        options.title = title !== undefined && title !== null ? title : undefined;
    }

    switch(options['type']) {
        case 0:
            options['title'] = 'Информация';
            if(options['delayz'] === undefined){
                options['delayz'] = 5000;
            }
            break;
        case 1:
            options['title'] = 'Внимание';
            if(options['delayz'] === undefined){
                options['delayz'] = 5000;
            }
            break;
        case 2:
            options['title'] = 'Ошибка';
            if(options['delayz'] === undefined){
                options['delayz'] = 10000;
            }
            break;
        case 3:
        case 4:
            options['title'] = 'Уведомление';
            if(options['delayz'] === undefined){
                options['delayz'] = 1000;
            }
            break;
    }

    let types = ['info','warning','danger','success'];



    if(!document.querySelector('#_mwce_notifiesDiv')){
        let MainNotice = document.createElement('div');
        MainNotice.style.position = 'fixed';
        MainNotice.style.width = '350px';
        MainNotice.style.height = 'auto';
        MainNotice.style.bottom = '5px';
        MainNotice.style.left = '5px';
        MainNotice.id = '_mwce_notifiesDiv';

        document.body.appendChild(MainNotice);

        console.log('-> [mwce]: append notice div');
    }

    mwce._openNotify ++;

    options['msg'] = '<strong style="margin-right: 15px;">' + options['title'] + '</strong><p>' + options['msg'] + '</p>';

    let notice = document.createElement('div');
    notice.classList.add('alert','alert-'+types[options['type']]);
    notice.style.float = 'left';
    notice.style.zIndex = '9999';
    notice.style.minWidth = '250px';
    notice.innerHTML = "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>" + options['msg'];

    $(notice).on('closed.bs.alert', function () {
        mwce._openNotify--;
    });

    setTimeout(function () {
        $(notice).fadeOut(3000,function () {
            $(notice).alert('close');
            if(options['callback'])
                options['callback']();
        });

    }, options['delayz']);

    document.querySelector('#_mwce_notifiesDiv').append(notice);
    console.warn('[mwce] -> notify message: ' + options['msg']);
};

mwce.confirm.close = function () {
    if(mwce.confirm._body){
        $(mwce.confirm._body).dialog('close');
    }
};

mwce.replacePoint = function (obj) {
    obj.value = obj.value.replace(/\,/, ".");
};

mwce.AddObjNDS = function (objID) {
    let _o = document.querySelector('#' + objID);
    _o.value = (_o.value * 1.18).toFixed(2);
};

mwce.DelObjNDS = function (objID) {
    let _o = document.querySelector('#' + objID);
    _o = (_o.value * 100 / 118).toFixed(2);
};

mwce.ObjNumbersOnly = function (obj) {
    if(obj.value){
        let value = obj.value;
        if(value.length>0)
        {
            let rep = /[,;":'a-zA-Zа-яА-Я\s]/;
            if (rep.test(value)) {
                value = value.replace(rep, '');
                obj.value = value;
            }
        }
    }
};

mwce.implode = function ( glue, pieces) {
    return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
};

mwce.htmlspecialchars_decode = function (string, quoteStyle) {
    /**
     *  eslint-disable-line camelcase
     *  discuss at: http://locutus.io/php/htmlspecialchars_decode/
     *  original by: Mirek Slugen
     *  improved by: Kevin van Zonneveld (http://kvz.io)
     *  bugfixed by: Mateusz "loonquawl" Zalega
     *  bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
     *  bugfixed by: Brett Zamir (http://brett-zamir.me)
     *  input by: ReverseSyntax
     *  input by: Slawomir Kaniecki
     *  input by: Scott Cariss
     *  input by: Francois
     *  input by: Ratheous
     *  input by: Mailfaker (http://www.weedem.fr/)
     *  revised by: Kevin van Zonneveld (http://kvz.io)
     *  reimplemented by: Brett Zamir (http://brett-zamir.me)
     *  example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
     *  returns 1: '<p>this -> &quot;</p>'
     *  example 2: htmlspecialchars_decode("&amp;quot;")
     *  returns 2: '&quot;'
     */

    var optTemp = 0;
    var i = 0;
    var noquotes = false;

    if (typeof quoteStyle === 'undefined') {
        quoteStyle = 2
    }
    string = string.toString()
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quoteStyle === 0) {
        noquotes = true
    }
    if (typeof quoteStyle !== 'number') {
        // Allow for a single string or an array of string flags
        quoteStyle = [].concat(quoteStyle);
        for (i = 0; i < quoteStyle.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quoteStyle[i]] === 0) {
                noquotes = true
            } else if (OPTS[quoteStyle[i]]) {
                optTemp = optTemp | OPTS[quoteStyle[i]]
            }
        }
        quoteStyle = optTemp
    }
    if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
        // PHP doesn't currently escape if more than one 0, but it should:
        string = string.replace(/&#0*39;/g, "'");
        // This would also be useful here, but not a part of PHP:
        // string = string.replace(/&apos;|&#x0*27;/g, "'");
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"')
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string
};

mwce.copy = function (str){
    //thx to https://ru.stackoverflow.com/users/207618/other
    let tmp   = document.createElement('INPUT'),
        focus = document.activeElement;

    tmp.value = str;

    document.body.appendChild(tmp);
    tmp.select();
    try{
        document.execCommand('copy');
        mwce.notify('Скопировано','Сообщение',3,null,200);
    }
    catch (e){
        mwce.notify('Произошла ошибка при копировании',null,2);
        console.log('[mwce] -> ',e.message);
    }
    document.body.removeChild(tmp);
    focus.focus();
};

mwce.overlay = function (targetID, legend) {

    if (document.getElementById('mwce_overlayDiv')) {
        document.getElementById('mwce_overlayDiv').remove();
    }

    if (!legend)
        legend = 'Загружаю...';


    let dv = document.createElement('div');
    dv.id = 'mwce_overlayDiv';
    //dv.style.width = document.getElementById(targetID).style.width;
    dv.style.height = document.getElementById(targetID).style.height;
    dv.style.width = ($(window).width() - 230) + 'px';
    dv.style.position = 'absolute';
    dv.style.top = 0;
    dv.style.left = 0;
    dv.style.backgroundColor = 'rgba(253,244,255,0.5)';
    dv.innerHTML = '<div style="width: 40%;text-align: center;color:green;font-style: italic;margin: 20% auto; z-index: 200;">' + legend + '</div>';

    document.body.appendChild(dv);
    $("#mwce_overlayDiv").offset($("#" + targetID).offset());
};
mwce.overlay.close = function () {
    document.getElementById('mwce_overlayDiv').remove();
};

let _tmplCache_ = {};
templateFunct= function(str, data) {
/// <summary>
/// Client side template parser that uses &lt;#= #&gt; and &lt;# code #&gt; expressions.
/// and # # code blocks for template expansion.
/// NOTE: chokes on single quotes in the document in some situations
///       use &amp;rsquo; for literals in text and avoid any single quote
///       attribute delimiters.
/// </summary>
/// <param name="str" type="string">The text of the template to expand</param>
/// <param name="data" type="var">
/// Any data that is to be merged. Pass an object and
/// that object's properties are visible as variables.
/// </param>
/// <returns type="string" />
    let err = "";
    try {
        let func = _tmplCache_[str];
        if (!func) {
            let strFunc =
                "var p=[],print=function(){p.push.apply(p,arguments);};" +
                "with(obj){p.push('" +
                //                        str
                //                  .replace(/[\r\t\n]/g, " ")
                //                  .split("<#").join("\t")
                //                  .replace(/((^|#>)[^\t]*)'/g, "$1\r")
                //                  .replace(/\t=(.*?)#>/g, "',$1,'")
                //                  .split("\t").join("');")
                //                  .split("#>").join("p.push('")
                //                  .split("\r").join("\\'") + "');}return p.join('');";

                str.replace(/[\r\t\n]/g, " ")
                    .replace(/'(?=[^#]*#>)/g, "\t")
                    .split("'").join("\\'")
                    .split("\t").join("'")
                    .replace(/<#=(.+?)#>/g, "',$1,'")
                    .split("<#").join("');")
                    .split("#>").join("p.push('")
                + "');}return p.join('');";

            //alert(strFunc);
            func = new Function("obj", strFunc);
            _tmplCache_[str] = func;
        }
        return func(data);
    } catch (e) { err = e.message; }
    return "< # ERROR: " + err + " # >";
};

function getOffset(elem) {
    if (elem.getBoundingClientRect) {
        return getOffsetRect(elem);
    } else {
        return getOffsetSum(elem);
    }
}

function getOffsetSum(elem) {
    let top=0, left=0;
    while(elem) {
        top = top + parseInt(elem.offsetTop);
        left = left + parseInt(elem.offsetLeft);
        elem = elem.offsetParent
    }
    return {top: top, left: left}
}

function getOffsetRect(elem) {

    let box = elem.getBoundingClientRect();

    let body = document.body;
    let docElem = document.documentElement;

    let scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
    let scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;

    let clientTop = docElem.clientTop || body.clientTop || 0;
    let clientLeft = docElem.clientLeft || body.clientLeft || 0;

    let top = box.top + scrollTop - clientTop;
    let left = box.left + scrollLeft - clientLeft;

    return { top: Math.round(top), left: Math.round(left) }
}

//todo: в релизе сжать код.