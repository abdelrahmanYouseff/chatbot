<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Chat Widget</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Inter', 'Cairo', Arial, sans-serif;
      background: transparent;
    }

    #chat-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1a1a1a 60%, #4e54c8 100%);
      color: white;
      font-size: 28px;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      box-shadow: 0 4px 16px rgba(0,0,0,0.18);
      transition: box-shadow 0.2s, background 0.2s;
    }
    #chat-toggle:hover {
      box-shadow: 0 8px 32px rgba(0,0,0,0.22);
      background: linear-gradient(135deg, #222 60%, #4e54c8 100%);
    }

    #chat-box {
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 370px;
      height: 440px;
      background: #fff;
      border-radius: 18px;
      display: none;
      flex-direction: column;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      z-index: 9998;
      overflow: hidden;
      animation: fadeInUp 0.3s;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .chat-header {
      background: linear-gradient(90deg, #1a1a1a 60%, #4e54c8 100%);
      color: #fff;
      padding: 18px 0 16px 0;
      font-weight: 700;
      text-align: center;
      font-size: 1.15rem;
      letter-spacing: 1px;
      border-top-left-radius: 18px;
      border-top-right-radius: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .chat-header:before {
      content: '\f544';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      margin-right: 8px;
      font-size: 1.2rem;
    }

    .chat-messages {
      flex: 1;
      padding: 20px 18px 10px 18px;
      overflow-y: auto;
      background: #fafbfc;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .message {
      max-width: 80%;
      font-size: 14px;
      line-height: 1.5;
      display: inline-block;
      word-wrap: break-word;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin-bottom: 2px;
      padding: 13px 18px;
      border-radius: 18px 18px 18px 6px;
      transition: background 0.2s;
    }

    .bot-message {
      background: #f5f6fa;
      color: #222;
      border-radius: 18px 18px 18px 6px;
      align-self: flex-start;
    }

    .user-message {
      background: linear-gradient(90deg, #4e54c8 60%, #8f94fb 100%);
      color: #fff;
      border-radius: 18px 18px 6px 18px;
      align-self: flex-end;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .chat-input {
      display: flex;
      align-items: center;
      border-top: 1px solid #eee;
      padding: 14px 16px;
      background: #fafbfc;
      gap: 10px;
    }

    .chat-input input[type="text"] {
      flex: 1;
      border: none;
      border-radius: 24px;
      padding: 12px 18px;
      font-size: 1rem;
      background: #f5f6fa;
      margin-right: 10px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.03);
      transition: background 0.2s, box-shadow 0.2s;
    }
    .chat-input input[type="text"]:focus {
      outline: none;
      background: #fff;
      box-shadow: 0 2px 8px rgba(78,84,200,0.10);
    }

    .chat-input button {
      background: linear-gradient(90deg, #4e54c8 60%, #8f94fb 100%);
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      cursor: pointer;
      transition: background 0.2s;
      margin-left: 0;
      padding: 0;
    }
    .chat-input button:hover {
      background: linear-gradient(90deg, #222 60%, #4e54c8 100%);
    }

    /* Custom scrollbar */
    .chat-messages::-webkit-scrollbar {
      width: 6px;
      background: #f5f6fa;
    }
    .chat-messages::-webkit-scrollbar-thumb {
      background: #e4e6eb;
      border-radius: 6px;
    }

    @media (max-width: 480px) {
      #chat-box {
        width: 100vw;
        height: 100vh;
        right: 0;
        bottom: 0;
        border-radius: 0;
      }
      #chat-toggle {
        right: 10px;
        bottom: 10px;
      }
    }
  </style>
</head>
<body>

  <button id="chat-toggle">💬</button>

  <div id="chat-box">
    <div class="chat-header">Eissa AI Assistant</div>
    <div class="chat-messages" id="chat-messages">
      <div class="message bot-message">Hi there! Welcome to Luxuria 👋 I'm Eissa AI, your virtual assistant. Let me know how I can help you today.</div>
    </div>
    <form id="chat-form" class="chat-input">
      <input type="text" id="question" placeholder="Type your question..." required />
      <input type="hidden" id="document_id" value="32" />
      <button type="submit">Send</button>
    </form>
  </div>

  <script>
    const chatBox = document.getElementById('chat-messages');

    function appendMessage(type, message) {
      const msg = document.createElement('div');
      msg.className = 'message ' + (type === 'question' ? 'user-message' : 'bot-message');
      const urlMatch = message.match(/https?:\/\/[^\s]+/);
      if (type === 'answer' && urlMatch) {
        const link = document.createElement('a');
        link.href = urlMatch[0];
        link.textContent = 'Open Link';
        link.target = '_blank';
        link.style.background = '#000';
        link.style.color = '#fff';
        link.style.padding = '8px 12px';
        link.style.borderRadius = '16px';
        link.style.textDecoration = 'none';
        link.style.display = 'inline-block';
        link.style.marginTop = '5px';
        msg.appendChild(link);
      } else {
        msg.textContent = message;
      }
      chatBox.appendChild(msg);
      chatBox.scrollTop = chatBox.scrollHeight;
    }

    document.getElementById('chat-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const question = document.getElementById('question').value;
      const documentId = document.getElementById('document_id').value;

      appendMessage('question', question);
      document.getElementById('question').value = '';

      fetch('/chat/' + documentId, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ question: question })
      })
      .then(response => response.json())
      .then(data => {
        const typing = document.createElement('div');
        typing.className = 'message bot-message';
        typing.textContent = 'Eissa AI is typing...';
        typing.id = 'typing-indicator';
        typing.style.fontStyle = 'italic';
        typing.style.color = '#999';
        chatBox.appendChild(typing);
        chatBox.scrollTop = chatBox.scrollHeight;

        setTimeout(() => {
          const indicator = document.getElementById('typing-indicator');
          if (indicator) indicator.remove();
          appendMessage('answer', data.answer || 'No answer returned.');
        }, 2500);
      })
      .catch(error => {
        appendMessage('answer', 'Error: ' + error.message);
      });
    });

    const chatToggle = document.getElementById('chat-toggle');
    const chatBoxContainer = document.getElementById('chat-box');

    chatToggle.addEventListener('click', () => {
      chatBoxContainer.style.display = chatBoxContainer.style.display === 'flex' ? 'none' : 'flex';
      chatBoxContainer.style.flexDirection = 'column';
    });
  </script>

</body>
</html>
