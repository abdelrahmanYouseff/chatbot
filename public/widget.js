(function () {
      if (window.luxuriaWidgetInitialized) return;
    window.luxuriaWidgetInitialized = true;

    const urlParams = new URLSearchParams(window.location.search);
    const botId = urlParams.get('bot_id') || 'default';

    // لو الأيقونة موجودة بالفعل، لا تعمل أي شيء
    if (document.getElementById('luxuria-chat-icon')) return;

    // إنشاء الأيقونة السوداء مباشرة
    const chatIcon = document.createElement('div');
    chatIcon.id = 'luxuria-chat-icon';
    chatIcon.innerHTML = '💬';
    chatIcon.style.position = 'fixed';
    chatIcon.style.bottom = '20px';
    chatIcon.style.right = '20px';
    chatIcon.style.width = '60px';
    chatIcon.style.height = '60px';
    chatIcon.style.backgroundColor = '#000'; // سوداء
    chatIcon.style.color = 'white';
    chatIcon.style.borderRadius = '50%';
    chatIcon.style.display = 'flex';
    chatIcon.style.justifyContent = 'center';
    chatIcon.style.alignItems = 'center';
    chatIcon.style.cursor = 'pointer';
    chatIcon.style.fontSize = '28px';
    chatIcon.style.zIndex = '9998';
    chatIcon.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';

    // لو الـ iframe موجود بالفعل، لا تعمل أي شيء
    if (document.getElementById('luxuria-chat-iframe')) return;

    // إنشاء الـ iframe
    const iframe = document.createElement('iframe');
    iframe.id = 'luxuria-chat-iframe';
    iframe.src = `http://127.0.0.1:8000/widget?bot_id=${botId}`;
    iframe.style.position = 'fixed';
    iframe.style.bottom = '90px';
    iframe.style.right = '20px';
    iframe.style.width = '360px';
    iframe.style.height = '500px';
    iframe.style.border = 'none';
    iframe.style.zIndex = '9999';
    iframe.style.borderRadius = '10px';
    iframe.style.display = 'none';
    iframe.allow = 'clipboard-write';

    // إظهار / إخفاء الشات عند الضغط
    chatIcon.addEventListener('click', () => {
        iframe.style.display = iframe.style.display === 'none' ? 'block' : 'none';
    });

    document.body.appendChild(chatIcon);
    document.body.appendChild(iframe);
})();
