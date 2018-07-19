let ports = [];
let socket;
let messageContainer;
let lastPortNum =-1;
let isNotification;
let hTime =0;
let tH;
let curUser = -1;
let userKey;
let cntNotice = 0;
let offlineSockets = true;
let timeMark = new Date();
let countTabs =0 ;
let curFocus;

console.log('-> init');

if (Notification === undefined) {
    console.error('нет поддержки HTML Notifications, закругляемся.');
    isNotification = 0;
}
else if (Notification.permission !== "granted" && Notification.permission !== "denied") {
    Notification.requestPermission();
    isNotification = 1;
}
else{
    isNotification = 1;
}

listenForMessage = function (event, port) {

    if(event.data !== undefined){

        for( let t in event.data){
            switch (t.trim()){
                case 'user':
                    console.log('-> Set user <' +event.data[t]+'>');

                    if(curUser < 0){
                        curUser = parseInt(event.data[t]);
                        userKey = event.data['key'];
                    }

                    if(event.data['pageProps'] !== undefined){
                        ports[port['curNumber']]['pageName'] = event.data['pageProps'].name;
                        ports[port['curNumber']]['pageProps'] = event.data['pageProps'];
                        if(curFocus === undefined){
                            curFocus = event.data['pageProps'].name;
                        }
                    }

                    port.postMessage({user:curUser,tab:port['curNumber']});

                    if (port['curNumber'] === 0) {
                        webSocketConnect();
                    }
                    else {
                        if (!offlineSockets && curUser > 0) {

                            port.postMessage({WSstate: {state: true}});

                            socket.send(JSON.stringify({
                                command: "openPage",
                                params: ports[port['curNumber']]['pageProps'],
                                uid: curUser,
                                tabNum: lastPortNum
                            }));
                        }
                        else
                            console.info('->socket is offline');
                    }
                    break;

                case 'deleteMsg':
                    socket.send(JSON.stringify({
                        command:'deleteMsg',
                        msg:event.data[t]
                    }));

                    if(messageContainer[event.data[t]] != undefined)
                        delete messageContainer[event.data[t]];

                    spamInTab(messageContainer,'getMessages',port);

                    break;

                case 'deleteAllMsg':
                    socket.send(JSON.stringify({
                        command:'deletemsgall',
                        msg:'true'
                    }));

                    messageContainer = [];
                    spamInTab(messageContainer,'getMessages',port);

                    break;

                case 'closeAll':
                    spamInTab({cmd:true},'closePage',port);
                    break;

                case 'focus':
                    curFocus = event.data[t];
                    console.log(event.data[t]);
                    console.log('-> focused on '+ curFocus);
                    return;
                    break;

                case 'postDialog':

                    if (!offlineSockets
                        && event.data['postDialog']['msg'] !== undefined
                        && event.data['postDialog']['discuss'] !== undefined
                        && event.data['postDialog']['toUsr'] !== undefined
                    ) {
                        socket.send(JSON.stringify({
                            command: "dialog",
                            msg: event.data['postDialog']['msg'],
                            discNum: event.data['postDialog']['discuss'],
                            toU: event.data['postDialog']['toUsr']
                        }));
                    }
                    else{
                        port.postMessage({noticeError: {type: 2,msg:'К сожалению, сервер сообщений не доступен! Сообщение доставлено не было.'}});
                    }
                    break;
            }
        }
    }
    else{
        console.error('->received data is wrong!');
    }
};

//first connect from page
onconnect = function (event) {

    let port = event.ports[0];
    ports.push(port);

    lastPortNum = (ports.length - 1);
    port['curNumber'] = lastPortNum;
    console.log('-> Connect from ' + lastPortNum + ' port');
    port.start();

    if (!offlineSockets) {
        port.postMessage({WSstate: {state: true}});

        if (messageContainer !== undefined) {
            port.postMessage({getMessages: messageContainer});
        }
    }

    port.addEventListener("message",function (event) {
            listenForMessage(event, port);
    });
};

function webSocketConnect() {
    hTime ++;
    cntNotice = 1;

    if(curUser > 0 && userKey !== undefined){

        socket = new WebSocket('wss://test.glaps.local:9000');

        socket.onopen = function () {
            hTime = 0;
            clearInterval(tH);
            offlineSockets = false;
            console.log("-> connect to server...");
            try{
                socket.send(JSON.stringify({
                    command:'hello',
                    uid:curUser,
                    key:userKey
                }));
            }
            catch (e)
            {
                console.error(e.message);
            }
        };

        socket.onerror = function(error) {
            console.log('Произошла ошибка, при работе с веб сокетами.');
        };

        socket.onmessage = function (event) {

            if (event.data !== undefined) {

                try {
                    let receive = JSON.parse(event.data);

                    switch (receive['command']) {
                        case 'notice':
                            if (receive['msg'] == 'welcome') {
                                spamInTab({state: true}, 'WSstate');
                                socket.send(JSON.stringify({
                                    command: "openPage",
                                    params: ports[port['curNumber']]['pageProps'],
                                    uid: curUser,
                                    tabNum: lastPortNum
                                }));
                                console.log('-> connection established');
                                offlineSockets = false;
                                countTabs++;
                            }
                            break;
                        case 'notices':
                            if (receive['data'] != undefined) {
                                let ntd = new Date();

                                if (isNotification > 0 && ntd.getTime() - timeMark.getTime() > 60000 && receive['cnt'] > 0) {
                                    timeMark = ntd;
                                    let notification = new Notification('Сообщения', {
                                        body: 'В журнале событий есть новые события',
                                        icon: 'https://test.glaps.local/theme/photos/95.jpg'
                                    });
                                    notification.onclick = function () {
                                        notification.close();
                                    };
                                }

                                messageContainer = receive['data'];
                                spamInTab(messageContainer);
                            }

                            break;
                        case 'setReadedPage':
                            if (receive['data'] != undefined && receive['data']['tab'] != undefined) {
                                let sendPort;
                                if (ports[receive['data']['tab']] == undefined) {
                                    sendPort = lastPortNum
                                }
                                else
                                    sendPort = receive['data']['tab'];

                                ports[sendPort].postMessage({
                                    showNotice: {
                                        message: 'Ссылка на данную страницу была указана в непрочтенных событиях. События отмечены как прочтенные',
                                        state: 0
                                    }
                                });
                            }
                            break;
                        case 'error':
                            if(receive['data'] !== undefined){
                                if (isNotification > 0) {
                                    let notification = new Notification('Ошибка в работе оповещений', {
                                        body: receive['data'],
                                        icon: 'https://test.glaps.local/theme/photos/95.jpg'
                                    });
                                    notification.onclick = function () {
                                        notification.close();
                                    };
                                }
                            }

                            console.log('-> WebSocket error msg: ' + receive['data']);
                                if (receive['disconnect'] !== undefined) {
                                    hTime = 99;
                                    if (tH !== undefined) {
                                        clearInterval(tH);
                                    }
                                }
                                if (receive['reload'] !== undefined) {
                                    spamInTab({cmd: true}, 'reloadPage')
                                }

                            break;
                        case 'news':
                            if (receive['data'] != undefined) {
                                if (isNotification > 0) {
                                    let notification = new Notification('Новости', {
                                        body: receive['data'],
                                        icon: 'https://test.glaps.local/theme/photos/95.jpg'
                                    });
                                    notification.onclick = function () {
                                        notification.close();
                                    };
                                }
                            }
                            break;
                        case 'receiveDialog':
                            console.log('-> receive dialog');
                            if((curFocus === undefined || curFocus !== 'Messages' ) && isNotification > 0){
                                let notification = new Notification(receive['fromName'], {
                                    body: receive['msg'],
                                    icon: 'https://test.glaps.local/theme/photos/95.jpg'
                                });

                                notification.onclick = function () {
                                    notification.close();
                                };
                            }
                            else{
                                spamInTab({
                                    type: 0,
                                    msg: '<u>'+receive['fromName']+'</u><br/>' + receive['msg'] +'<br> <a href="https://test.glaps.local/page/Messages.html">Перейти в беседы</a>'
                                }, 'noticeError');
                            }

                            break;
                        case 'refreshDiscuss':
                            if(receive['discNum'] !== undefined){
                                spamInTab({
                                    type: 'dialog',
                                    id:receive['discNum']
                                }, 'refreshEvent');
                            }
                            break;
                        case 'ping':
                            socket.send(JSON.stringify({
                                command:'pong',
                                state:true
                            }));
                            break;
                    }
                }
                catch (e) {
                    console.log('error: ',e.message);
                }
            }
        };

        socket.onclose = function(event) {
            if (event.wasClean) {
                console.log('Соединение закрыто');
            }
            else {
                console.log('Соединение оборвано, Код: ' + event.code);
            }

            spamInTab({state:false},'WSstate');
            offlineSockets = true;

            if(hTime > 5){
                clearInterval(tH);
                spamInTab({parameter:true},'disableRing');
                if(isNotification >0 && hTime !== 99){
                    let notification = new Notification('Ошибка связи...', {body:'К сожалению, сервер событий не доступен, в связи с этим, узнать актуальные события можно только из журнала событий.',icon:'https://test.glaps.local/theme/photos/95.jpg'});
                    notification.onclick = function () {
                        notification.close();
                    };
                }
            }
            else{
                tH = setTimeout(webSocketConnect,35000);
            }
        };
    }
}

function spamInTab(someData,commandType,ingone) {

    if(commandType === undefined){
        commandType = 'getMessages';
    }

    let sended;
    eval('sended = {'+commandType+':someData}');

    for (i = 0; i < ports.length; i++) {

        if(ingone == ports[i])
            continue;

        ports[i].postMessage(sended);
        if (ports[i]['pageName'] === 'events') {
            ports[i].postMessage({refreshEvent: 1});
        }
    }
}