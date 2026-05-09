<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

checkLogin();
$user = getLoggedInUser();

$receiver_id = $_GET['receiver_id'] ?? null;
$product_id = $_GET['product_id'] ?? null;

if (!$receiver_id) {
    header("Location: beneficiary/home.php");
    exit();
}

$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$receiver_id]);
$receiver = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحادثة - <?php echo htmlspecialchars($receiver['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-box { background: #efeae2; border: 2px solid var(--dark-green); border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; height: 550px; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); }
        .message { max-width: 80%; padding: 10px 15px; border-radius: 12px; position: relative; font-size: 15px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .message.sent { background: var(--dark-green); color: #fff; align-self: flex-start; border-top-right-radius: 0; margin-right: 10px; }
        .message.received { background: #fff; color: var(--dark-green); align-self: flex-end; border-top-left-radius: 0; margin-left: 10px; border: 1px solid #ddd; }
        .product-context { background: #fff; border: 1px solid #ddd; border-radius: 15px; padding: 12px; margin: 10px auto; display: flex; align-items: center; gap: 15px; font-size: 14px; width: fit-content; }
        .product-context img { width: 50px; height: 50px; border-radius: 8px; }
        .chat-image { max-width: 100%; border-radius: 8px; cursor: pointer; }
        .chat-input { padding: 10px 20px; background: #f0f0f0; display: flex; gap: 12px; align-items: center; }
        .chat-input input[type="text"] { flex: 1; padding: 12px 20px; border-radius: 25px; border: none; outline: none; background: #fff; }
        .file-label { background: var(--dark-green); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <?php include 'includes/header.php'; ?>

    <h2 style="margin: 20px 0; display: flex; align-items: center; gap: 10px;">
        <a href="messages.php" style="color: inherit; text-decoration: none;"><i class="fa fa-arrow-right"></i></a>
        المحادثة مع <?php echo htmlspecialchars($receiver['name']); ?>
    </h2>

    <div class="chat-box">
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be loaded here via JS -->
        </div>
        
        <form id="chatForm" class="chat-input">
            <label class="file-label">
                <i class="fa fa-image"></i>
                <input type="file" name="chat_image" id="chatImage" accept="image/*" style="display: none;">
            </label>
            <input type="text" name="message" id="messageInput" placeholder="اكتب رسالتك هنا...">
            <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 20px;">إرسال</button>
        </form>
    </div>
</div>

<script>
    const receiverId = <?php echo $receiver_id; ?>;
    const productId = <?php echo $product_id ?: 'null'; ?>;
    const currentUserId = <?php echo $user['id']; ?>;
    const chatBox = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chatForm');
    let lastMsgCount = 0;

    function fetchMessages() {
        fetch(`fetch_messages.php?receiver_id=${receiverId}`)
            .then(res => res.json())
            .then(data => {
                if (data.messages.length !== lastMsgCount) {
                    renderMessages(data.messages);
                    lastMsgCount = data.messages.length;
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            });
    }

    function renderMessages(messages) {
        let html = '';
        let lastPId = null;
        messages.forEach(msg => {
            if (msg.product_id && msg.product_id != lastPId) {
                html += `<div class="product-context" style="align-self: center;">
                            <img src="${msg.p_img}" alt="p">
                            <span>بخصوص: <strong>${msg.p_name}</strong></span>
                         </div>`;
                lastPId = msg.product_id;
            }

            const isSent = msg.sender_id == currentUserId;
            const time = new Date(msg.created_at).toLocaleTimeString('ar-SA', {hour: '2-digit', minute:'2-digit'});
            const checks = isSent ? (msg.is_read == 1 ? '<i class="fa-solid fa-check-double" style="color: #34b7f1;"></i>' : '<i class="fa-solid fa-check"></i>') : '';

            html += `<div class="message ${isSent ? 'sent' : 'received'}">
                        ${msg.image ? `<img src="${msg.image}" class="chat-image" onclick="window.open(this.src)">` : ''}
                        ${msg.message ? `<div style="margin-top: 5px;">${msg.message}</div>` : ''}
                        <div style="font-size: 10px; opacity: 0.7; margin-top: 5px; display: flex; justify-content: space-between; align-items: center; gap: 5px;">
                            <span>${time}</span>
                            <span style="font-size: 14px;">${checks}</span>
                        </div>
                    </div>`;
        });
        chatBox.innerHTML = html;
    }

    chatForm.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(chatForm);
        formData.append('receiver_id', receiverId);
        if (productId) formData.append('product_id', productId);

        fetch('send_message_ajax.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            chatForm.reset();
            fetchMessages();
        });
    };

    // Poll every 2 seconds
    setInterval(fetchMessages, 2000);
    fetchMessages();
</script>
</body>
</html>
