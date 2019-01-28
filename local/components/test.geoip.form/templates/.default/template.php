<?php
CJSCore::Init(array('ajax'));
?>
<div class="geo-ip-layout js-geo-ip-layout">
    <div class="geo-ip-wrapper">
        <div class="geo-ip-title">
            Определить местоположение
        </div>

        <div>
            <input type="text"
                   class="geo-ip-input js-geo-ip-input"
                   placeholder="Введите IP-адрес в формате &#34;255.255.255.255&#34;"
            >
        </div>

        <div class="geo-ip-result-wrapper js-geo-ip-result-wrapper closed">
            <div class="geo-ip-result js-geo-ip-result"></div>
        </div>

        <button type="button"
                class="geo-ip-button js-geo-ip-button"
        >
            Определить
        </button>
    </div>
</div>
