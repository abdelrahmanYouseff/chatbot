(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const botId = urlParams.get('bot_id') || 'default';

    const iframe = document.createElement('iframe');
    iframe.src = `http://127.0.0.1:8000/widget?bot_id=${botId}`;
    iframe.style.position = 'fixed';
    iframe.style.bottom = '20px';
    iframe.style.right = '20px';
    iframe.style.width = '360px';
    iframe.style.height = '500px';
    iframe.style.border = 'none';
    iframe.style.zIndex = '9999';
    iframe.style.borderRadius = '10px';
    iframe.allow = 'clipboard-write';

    document.body.appendChild(iframe);
})();
