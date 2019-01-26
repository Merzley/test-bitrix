//Основной обработчик каждого из подключенных компонентов
function processComponentLogic(domComponentRoot){
    const ENM_PARTIAL_IP_VALIDATION = 1;
    const ENM_FULL_IP_VALIDATION = 2;

    let domInput = domComponentRoot.querySelector('.js-geo-ip-input');
    let domButton = domComponentRoot.querySelector('.js-geo-ip-button');
    let domResultWrapper = domComponentRoot.querySelector('.js-geo-ip-result-wrapper');
    let domResult = domComponentRoot.querySelector('.js-geo-ip-result');

    let bAjaxRequestAvailability = true;

    //Обработчик нажатия кнопки "отправить"
    function buttonOnClick(){
        //Ограничитель, не дающий выполняться двум запросам одновременно
        //Включается перед отправкой запроса, а выключается после получения ответа
        if (!bAjaxRequestAvailability)
            return;

        let inputValue = domInput.value;

        //Проверяем введенный IP на валидность
        if (!isValidIp(inputValue, ENM_FULL_IP_VALIDATION)) {
            setInputValidityMark(false);
            return;
        }

        //Врубаем ограничитель от двух запросов и прячем предыдущий результат
        setAjaxRequestAvailability(false);
        setResultSectionVisibility(false);

        //Отправляем запрос на сервер
        //По окончании запроса выводим результат или ошибку,
        //  выключаем ограничитель и показываем новый результат
        sendAjax(inputValue)
            .then((objDataFromServer) => {
                showDataFromServer(objDataFromServer);
                setResultSectionVisibility(true);
                setAjaxRequestAvailability(true);
            })
            .catch(()=>{
                showErrorMessage();
                setResultSectionVisibility(true);
                setAjaxRequestAvailability(true);
            })
    }

    //Обработчик ввода IP
    function inputOnInput(){
        //Не даем вводить ничего кроме цифр и точек
        let originalValue = domInput.value;
        let resultValue = originalValue.replace(/([^0-9.]+)/gi, "");

        //Возвращаем курсор на место
        if (originalValue !== resultValue) {
            let caretPos = domInput.selectionStart-1;

            domInput.value = resultValue;
            domInput.selectionStart = caretPos;
            domInput.selectionEnd = caretPos;
        }

        //Вырезали лишние символы - запускаем не строгий валидатор
        setInputValidityMark(
            isValidIp(resultValue, ENM_PARTIAL_IP_VALIDATION)
        );
    }

    //Валидатор IP адреса
    //Проверяет в двух режимах:
    //  1. Не строгая валидация для обработки ввода на лету
    //  2. Строгая валидация для проверки перед отправкой на сервер
    function isValidIp(value, enmValidationType)
    {
        let arOctets = value.split('.');

        switch (enmValidationType) {
            case ENM_PARTIAL_IP_VALIDATION:
                if (arOctets.length > 4)
                    return false;
                break;
            case ENM_FULL_IP_VALIDATION:
                if (arOctets.length !== 4)
                    return false;
                break;
            default:
                return false;
        }

        try {
            arOctets.forEach((strOctet) => {
                if ((strOctet === '') && (enmValidationType === ENM_FULL_IP_VALIDATION))
                    throw AbortException;

                if (parseInt(strOctet) > 255)
                    throw AbortException;
            });
        }
        catch (e) {
            return false;
        }

        return true;
    }

    //Обработчики состояний визуальных элементов
    function setInputValidityMark(bIsValid){
        if (bIsValid)
            domInput.classList.remove('invalid');
        else
            domInput.classList.add('invalid');
    }

    function setResultSectionVisibility(bIsVisible){
        if (bIsVisible)
            domResultWrapper.classList.remove('closed');
        else
            domResultWrapper.classList.add('closed');
    }

    function setButtonActivity(bIsActive){
        if (bIsActive){
            domButton.textContent = 'Определить';
            domButton.disabled = false;
        }
        else{
            domButton.textContent = 'Выполняется запрос...';
            domButton.disabled = true;
        }
    }

    //Обработчик состояния ограничителя от двух запросов
    //Здесь же отключается и кнопка "отправить"
    function setAjaxRequestAvailability(bAvailability){
        bAjaxRequestAvailability = bAvailability;
        setButtonActivity(bAvailability);
    }

    //Обработчик AJAX-запроса
    //Возвращает промис с распакованными данными по IP адресу в случае успеха
    //  Либо просто отменяет промис в случае ошибки. Причины ошибок выводятся в консоль
    function sendAjax(strValidIp){
        return new Promise((resolve, reject) => {
            BX.ajax.runComponentAction(
                'test.geoip.ajax',
                'getGeoIp', {
                    mode: 'class',
                    data: {ip: strValidIp},
                }
            )
            .then((response) => {
                //Куча валидаций пришедшего ответа + логи в консоль, если что-то не так
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

                if (parsedResponseData.success !== true) {
                    console.log('Error. Server can\'t receive geo-ip data');
                    console.log(response);
                    return reject();
                }

                //Возвращаем распакованные данные по IP-адресу
                return resolve(parsedResponseData.data);
            })
            .catch((response)=>{
                if (!isValidAjaxResponse(response))
                    console.log('Error. Can\'t send AJAX request');
                else
                    console.log('Error. Server return not successful answer');

                console.log(response);
                return reject();
            });
        });
    }

    //Валидаторы AJAX-ответа
    function isValidAjaxResponse(response){
        return ((typeof response === 'object') &&
                ('status' in response) &&
                ('data' in response)
               );
    }

    function isValidAjaxResponseData(responseData){
        return ((typeof responseData === 'object') &&
                ('success' in responseData) &&
                ('data' in responseData) &&
                ('first' in responseData.data) &&
                ('second' in responseData.data) &&
                ('third' in responseData.data) &&
                ('fourth' in responseData.data)
               );
    }

    //Обработчики показа результатов работы сервиса
    //Заглушки
    function showDataFromServer(objDataFromServer){
        domResult.textContent =
            objDataFromServer.first + ' ' +
            objDataFromServer.second + ' ' +
            objDataFromServer.third + ' ' +
            objDataFromServer.fourth;
    }
    function showErrorMessage(){
        domResult.textContent = 'Не удалось получить данные по данному IP';
    }

    //Вешаем обработчики событий на кнопку и инпут
    domButton.addEventListener('click', buttonOnClick);
    domInput.addEventListener('input', inputOnInput)
}

//Ищем все подключенные на страницу экземпляры компонента и натравливаем на них обработчик
BX.ready(()=>{
    let arComponentRoots = document.querySelectorAll('.js-geo-ip-layout');

    arComponentRoots.forEach((domComponentRoot)=>{
        processComponentLogic(domComponentRoot);
    });
});

