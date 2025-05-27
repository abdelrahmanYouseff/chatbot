<!DOCTYPE html>
<html>
<head>
    <title>Smart PDF Chatbot</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(to right, #1e1e2f, #23233a);
    color: #fff;
    display: flex;
    flex-direction: column;
    height: 100vh;
}

header {
    padding: 20px;
    background: #2d2f47;
    text-align: center;
    font-size: 26px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
    letter-spacing: 1px;
}

.chat-container {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    background: #1b1c2b;
}

.chat-bubble {
    display: inline-block;
    max-width: 60%;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 15px;
    line-height: 1.6;
    word-wrap: break-word;
    animation: fadeIn 0.3s ease-in-out;
    position: relative;
}

.chat-bubble:hover {
    transform: scale(1.02);
}

.question {
    align-self: flex-end;
    background: linear-gradient(to right, #3b82f6, #2563eb);
    color: #fff;
    border-bottom-right-radius: 4px;
    text-align: right;
}

.answer {
    align-self: flex-start;
    background: linear-gradient(to right, #374151, #2c2f3a);
    color: #e5e7eb;
    border-bottom-left-radius: 4px;
    text-align: left;
}

.message-wrapper {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 8px;
    gap: 6px;
}

.question-wrapper {
    justify-content: flex-end;
}

.input-area {
    display: flex;
    background: #27293d;
    padding: 15px;
    border-top: 1px solid #333;
}

.input-area input {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 8px;
    margin-right: 10px;
    font-size: 14px;
    background-color: #1f2033;
    color: #fff;
}

.input-area input::placeholder {
    color: #aaa;
}

.input-area button {
    padding: 12px 24px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.input-area button:hover {
    background: #2563eb;
}

form.upload-form {
    padding: 20px;
    background: #111;
    text-align: center;
}

.timestamp {
    font-size: 11px;
    color: #999;
    margin-top: 6px;
    text-align: right;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
    </style>
</head>
<body>

    <header>Luxuria Chatbot</header>

    <!-- Progress Modal -->
    <div id="progressModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#000000cc; align-items:center; justify-content:center; z-index:9999;">
        <div style="background:#222; padding:30px; border-radius:10px; text-align:center; color:#fff; width:300px;">
            <p style="margin-bottom:15px;">🚀 Training AI on your document...</p>
            <div style="background:#444; border-radius:20px; overflow:hidden; height:20px;">
                <div id="progressBar" style="height:100%; width:0%; background:#3b82f6;"></div>
            </div>
            <p id="progressText" style="margin-top:10px;">0%</p>
        </div>
    </div>

    <form class="upload-form" action="/upload-pdf" method="POST" enctype="multipart/form-data" onsubmit="handleUpload(event)">
        @csrf
        <label>Upload PDF:</label>
        <input type="file" name="pdf" required>
        <button type="submit">Upload</button>
    </form>

    <div class="chat-container" id="chat-box">
        <!-- Chat messages will appear here -->
    </div>

    <form id="chat-form" class="input-area">
        <input type="text" id="question" placeholder="Type your question..." required>
        <input type="hidden" id="document_id" value="44">
        <button type="submit">Send</button>
    </form>

    <script>
        const chatBox = document.getElementById('chat-box');

        function appendMessage(type, message) {
            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper ' + (type === 'question' ? 'question-wrapper' : '');

            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble ' + type;

            const time = new Date().toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const messageText = document.createElement('div');
            messageText.innerText = message;

            const timestamp = document.createElement('div');
            timestamp.className = 'timestamp';
            timestamp.innerText = time;

            bubble.appendChild(messageText);
            bubble.appendChild(timestamp);
            wrapper.appendChild(bubble);

            chatBox.appendChild(wrapper);
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
                appendMessage('answer', data.answer || 'No answer returned.');
            })
            .catch(error => {
                appendMessage('answer', 'Error: ' + error.message);
            });
        });

        function handleUpload(e) {
            e.preventDefault();
            const modal = document.getElementById('progressModal');
            const bar = document.getElementById('progressBar');
            const text = document.getElementById('progressText');

            modal.style.display = 'flex';
            let progress = 0;

            const interval = setInterval(() => {
                progress += Math.floor(Math.random() * 5) + 1;
                if (progress >= 100) progress = 100;
                bar.style.width = progress + '%';
                text.textContent = progress + '%';
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 200);

            const form = e.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (res.ok) {
                    clearInterval(interval);
                    bar.style.width = '100%';
                    text.textContent = '100%';
                    setTimeout(() => {
                        modal.style.display = 'none';
                        document.querySelector('.upload-form').style.display = 'none';
                        document.getElementById('chat-form').style.display = 'flex';
                        document.getElementById('chat-box').style.display = 'block';
                        appendMessage('answer', 'Hi there! Welcome to Luxuria 👋 I’m Eissa AI, your virtual assistant. Let me know how I can help you today.');
                    }, 800);
                } else {
                    alert("Upload failed.");
                    modal.style.display = 'none';
                }
            });
        }
    </script>

</body>
</html>
