BX.ready(()=>{
    let domButtonMore = document.querySelector('#button-more');
    let domTable = document.querySelector('#test-list-container table');

    let nCurrentPageNum = 1;

    let bAjaxRequestAvailability = true;

    function buttonMoreOnClick(){
        if (!bAjaxRequestAvailability)
            return;

        setAjaxRequestAvailability(false);

        sendAjax()
        .then((arLists) => {
            if (arLists.length > 0) {
                arLists.forEach(drawNewList);
                nCurrentPageNum++;
                setAjaxRequestAvailability(true);
            }
            else
                setAjaxRequestAvailability(null);
        })
        .catch(()=>{
            setAjaxRequestAvailability(true);
        })
    }

    function sendAjax(){
        return new Promise((resolve, reject) => {
            BX.ajax.runComponentAction(
                'test.list',
                'getMore', {
                    mode: 'class',
                    data: {
                        nPageNum: nCurrentPageNum,
                        nPageSize: window.testList.ajaxPageSize,
                        nMainPageNum: window.testList.mainPageNum,
                        nMainPageSize: window.testList.mainPageSize,
                        nIblockId: window.testList.iblockId,
                    }
                }
            )
            .then((response) => {
                if (!isValidAjaxResponse(response)) {
                    console.log('Error. Bad AJAX response');
                    return reject();
                }

                if (response.status !== 'success') {
                    console.log('Error. Server return not successful answer');
                    console.log(response);
                    return reject();
                }

                let parsedResponseData;
                try {
                    parsedResponseData = JSON.parse(response.data)
                }
                catch (e) {
                    console.log('Error. Cant\'t parse server answer');
                    console.log(response.data);
                    return reject();
                }

                if (!isValidAjaxResponseData(parsedResponseData)){
                    console.log('Error. Unknown server answer format');
                    console.log(parsedResponseData);
                    return reject();
                }

                return resolve(parsedResponseData);
            })
            .catch(() => {
                if (!isValidAjaxResponse(response))
                    console.log('Error. Can\'t send AJAX request');
                else
                    console.log('Error. Server return not successful answer');

                console.log(response);
                return reject();
            })
        })
    }

    function isValidAjaxResponse(response){
        return ((typeof response === 'object') &&
            ('status' in response) &&
            ('data' in response)
        );
    }

    function isValidAjaxResponseData(responseData){
        if (!Array.isArray(responseData))
            return false;

        responseData.forEach((element) => {
            if (
                !(typeof element === 'object') ||
                !('NAME' in element) ||
                !('TIMESTAMP_X' in element) ||
                !('ELEMENTS' in element) ||
                !('USERS_FIO' in element) ||
                !(Array.isArray(element.ELEMENTS)) ||
                !(Array.isArray(element.USERS_FIO))
            )
            {
                return false;
            }
        });

        return true;
    }

    function drawNewList(objList){
        let domNewTr = document.createElement('tr');
        let domNewTd;
        let domNewUl;
        let domNewLi;

        domNewTd = document.createElement('td');
        domNewTd.textContent = objList.NAME;
        domNewTr.appendChild(domNewTd);

        domNewTd = document.createElement('td');
        domNewTd.textContent = objList.TIMESTAMP_X;
        domNewTr.appendChild(domNewTd);

        domNewTd = document.createElement('td');
        domNewUl = document.createElement('ul');
        objList.USERS_FIO.forEach((fio)=>{
            domNewLi = document.createElement('li');
            domNewLi.textContent = fio;
            domNewUl.appendChild(domNewLi);
        });
        domNewTd.appendChild(domNewUl);
        domNewTr.appendChild(domNewTd);

        domNewTd = document.createElement('td');
        domNewUl = document.createElement('ul');
        objList.ELEMENTS.forEach((element) => {
            domNewLi = document.createElement('li');
            domNewLi.textContent = '('+element.ID+') '+element.NAME;
            domNewUl.appendChild(domNewLi);
        });
        domNewTd.appendChild(domNewUl);
        domNewTr.appendChild(domNewTd);

        domTable.appendChild(domNewTr);
    }

    function setAjaxRequestAvailability(bAvailability){
        bAjaxRequestAvailability = bAvailability;
        setButtonActivity(bAvailability);
    }

    function setButtonActivity(bIsActive){
        if (bIsActive){
            domButtonMore.textContent = 'Еще';
            domButtonMore.disabled = false;
        }
        else if (bIsActive === false){
            domButtonMore.textContent = 'Выполняется запрос...';
            domButtonMore.disabled = true;
        }
        else{
            domButtonMore.textContent = 'Больше нечего показывать';
            domButtonMore.disabled = true;
        }
    }

    domButtonMore.addEventListener('click',buttonMoreOnClick);
});