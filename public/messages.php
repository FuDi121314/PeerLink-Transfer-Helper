<?php include 'config.php';

// Background for the whole page
$bgImage = '';
$fixedBgPath = __DIR__ . '/../customize/background/bg_messages.png';
if (file_exists($fixedBgPath)) {
    $bgImage = '/customize/background/bg_messages.png';
} else {
    $bgDir = __DIR__ . '/../customize/background/';
    $images = glob($bgDir . '*.png');
    if (!empty($images)) {
        $bgImage = '/customize/background/' . basename($images[array_rand($images)]);
    }
}

// Message box backgrounds – array of filenames (without path)
$msgBgDir = __DIR__ . '/../customize/messageBG/';
$msgBgImages = glob($msgBgDir . '*.{png,jpg,jpeg}', GLOB_BRACE);
$msgBgList = $msgBgImages ? array_map('basename', $msgBgImages) : [];

// Fetch messages from API
$messagesJson = file_get_contents(SERVER_URL . '/messages');
$messages = json_decode($messagesJson, true) ?: [];

// Reverse to show newest first (index 0 = latest, placed top‑left on page 1)
$messages = array_reverse($messages);

// Admin mode (delete buttons)
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages</title>
    <style>
        body {
            background: #121212;
            color: #e0e0e0;
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }
        <?php if($bgImage): ?>
        body {
            background-image: url('<?= $bgImage ?>');
            background-size: cover;
            background-position: center;
        }
        <?php endif; ?>

        .container {
            background: rgba(0,0,0,0.7);
            padding: 20px;
            border-radius: 10px;
            max-width: 1200px;
            margin: auto;
        }

        /* Multi-column grid: auto-fill with min width 333px, max 1fr */
        .message-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(333px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .msg-box {
            padding: 15px;
            border-radius: 8px;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.8);
            /* Default background if no image */
            background: #333;
            /* Let height adapt to content */
            height: auto;
            word-wrap: break-word;
            overflow-wrap: break-word;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* If a random messageBG image exists, it will be applied inline */

        .msg-box p {
            margin: 0 0 10px 0;
            white-space: pre-wrap;
        }

        .msg-buttons {
            display: flex;
            gap: 5px;
            margin-top: auto;
        }

        .copy-btn, .delete-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .delete-btn {
            background: rgba(255,0,0,0.5);
            margin-left: 10px;
        }

        textarea, input {
            background: #333;
            color: #fff;
            border: 1px solid #555;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        button {
            background: #444;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        a { color: #aaa; }

        .pagination {
            text-align: center;
            margin: 20px 0;
        }
        .pagination button {
            margin: 0 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Messages</h2>
    <a href="index.php">Back</a>
    <?php if($isAdmin) echo ' <span style="color:red">Admin Mode</span>'; ?>
    <hr>

    <?php if (empty($messages)): ?>
        <p>No messages yet.</p>
    <?php else: ?>
        <!-- Messages will be rendered by JavaScript -->
        <div id="messagesContainer" class="message-grid"></div>
        <div class="pagination" id="pagination"></div>
    <?php endif; ?>

    <hr>
    <form action="send_message.php" method="post">
        <textarea name="message" rows="3" placeholder="Type your message..." required></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Data from PHP (newest first already)
    const allMessages = <?= json_encode($messages) ?>;
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
    const msgBgImages = <?= json_encode($msgBgList) ?>;
    const msgBgPath = '/customize/messageBG/';

    const messagesPerPage = 9;
    let currentPage = 1;
    const totalPages = Math.ceil(allMessages.length / messagesPerPage);

    const container = document.getElementById('messagesContainer');
    const paginationDiv = document.getElementById('pagination');

    // Fallback colour palette (used if no images)
    const fallbackColors = ['#2c3e50', '#34495e', '#1e2f3f', '#2d2d2d', '#3a3a3a', '#4a235a', '#1b4f72'];

    function getRandomBg() {
        // Return a random image url() string, or a random solid colour
        if (msgBgImages.length > 0) {
            const randomImage = msgBgImages[Math.floor(Math.random() * msgBgImages.length)];
            //console.log(msgBgImages.length);      //debugger
            return `url('${msgBgPath}${randomImage}')`;
        }
        return fallbackColors[Math.floor(Math.random() * fallbackColors.length)];
    }

    function renderPage(page) {
        const start = (page - 1) * messagesPerPage;
        const end = start + messagesPerPage;
        const pageMessages = allMessages.slice(start, end);

        container.innerHTML = '';

        pageMessages.forEach(msg => {
            const box = document.createElement('div');
            box.className = 'msg-box';

            // Assign a new random background to EACH box
            const bg = getRandomBg();
            if (bg.startsWith('url(')) {
                box.style.backgroundImage = bg;
                box.style.backgroundSize = 'cover';
                box.style.backgroundPosition = 'center';
            } else {
                box.style.backgroundColor = bg;
            }

            const textP = document.createElement('p');
            textP.textContent = msg.text;
            box.appendChild(textP);

            const btnDiv = document.createElement('div');
            btnDiv.className = 'msg-buttons';

            // Copy button
            const copyBtn = document.createElement('button');
            copyBtn.className = 'copy-btn';
            copyBtn.textContent = 'Copy';
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(msg.text).then(() => {
                    copyBtn.textContent = 'Copied!';
                    setTimeout(() => copyBtn.textContent = 'Copy', 2000);
                }).catch(err => console.error('Copy failed', err));
            });
            btnDiv.appendChild(copyBtn);

            // Admin delete link
            if (isAdmin) {
                const deleteLink = document.createElement('a');
                deleteLink.className = 'delete-btn';
                deleteLink.textContent = 'Delete';
                deleteLink.href = `delete_message.php?id=${msg.id}`;
                deleteLink.onclick = (e) => {
                    if (!confirm('Delete this message?')) e.preventDefault();
                };
                btnDiv.appendChild(deleteLink);
            }

            box.appendChild(btnDiv);
            container.appendChild(box);
        });

        // Pagination controls
        paginationDiv.innerHTML = '';
        if (totalPages > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Previous';
            prevBtn.disabled = (currentPage === 1);
            prevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage(currentPage);
                    window.scrollTo(0, 0);
                }
            });
            paginationDiv.appendChild(prevBtn);

            const pageIndicator = document.createElement('span');
            pageIndicator.textContent = ` Page ${currentPage} of ${totalPages} `;
            paginationDiv.appendChild(pageIndicator);

            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next';
            nextBtn.disabled = (currentPage === totalPages);
            nextBtn.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage(currentPage);
                    window.scrollTo(0, 0);
                }
            });
            paginationDiv.appendChild(nextBtn);
        }
    }

    if (allMessages.length > 0) {
        renderPage(1);
    }
});
</script>
</body>
</html>