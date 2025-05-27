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
      font-family: 'Arial', sans-serif;
      background: transparent;
    }

    #chat-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background-color: #000000;
      color: white;
      font-size: 28px;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    #chat-box {
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 380px;
      height: 420px;
      background: white;
      border-radius: 12px;
      display: none;
      flex-direction: column;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      z-index: 9998;
      overflow: hidden;
    }

    .chat-header {
      margin: 10px 10px 10px 0;
      background-color: #000000;
      color: white;
      padding: 12px 15px;
      font-weight: bold;
      text-align: center;
      font-size: 16px;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .chat-messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      background-color: #ffffff;
    }

    .message {
      max-width: 75%;
      margin: 6px 0;
      padding: 9px 13px;
      border-radius: 18px 18px 18px 6px;
      clear: both;
      font-size: 13.5px;
      line-height: 1.4;
      display: inline-block;
      word-wrap: break-word;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .bot-message {
      background-color: #e4e6eb;
      float: left;
      margin-left: 30px;
    }

    .user-message {
      background-color: #000000;
      color: white;
      float: right;
      border-radius: 18px 18px 6px 18px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .chat-input {
      display: flex;
      border-top: 1px solid #ddd;
      padding: 12px 15px;
      background: #fff;
      gap: 10px;
    }

    .chat-input input {
      flex: 1;
      padding: 8px 20px;
      border: 1px solid #ccc;
      border-radius: 20px;
      outline: none;
      font-size: 13px;
      width: 100%;
      max-width: 80%;
      margin-left: 30px;
      box-sizing: border-box;
    }

    .chat-input button {
      background-color:     #000000;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 9px 16px;
      margin-left: 10px;
      cursor: pointer;
      font-weight: bold;
      font-size: 13.5px;
      transition: background-color 0.3s ease;
    }
    .chat-input button:hover {
      background-color: #222;
    }
  </style>
</head>
<body>

  <button id="chat-toggle">💬</button>

  <div id="chat-box">
    <div class="chat-header">Eissa AI Assistant</div>
    <div class="chat-messages" id="chat-messages">
      <div class="message bot-message">Hi there! Welcome to Luxuria 👋 I’m Eissa AI, your virtual assistant. Let me know how I can help you today.</div>
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
