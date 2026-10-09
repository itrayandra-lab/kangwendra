(function () {
    'use strict';

    var root = document.getElementById('arayAssistant');
    if (!root) return;

    var launcher = document.getElementById('arayLauncher');
    var panel = document.getElementById('arayPanel');
    var closeButton = document.getElementById('arayClose');
    var form = document.getElementById('arayForm');
    var input = document.getElementById('arayInput');
    var sendButton = document.getElementById('araySend');
    var messages = document.getElementById('arayMessages');
    var suggestions = document.getElementById('araySuggestions');
    var invite = document.getElementById('arayInvite');
    var history = [];
    var busy = false;
    var lastUserMessage = '';

    function setOpen(open) {
        root.classList.toggle('is-open', open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            invite.classList.remove('is-visible');
            window.setTimeout(function () { input.focus({ preventScroll: true }); }, 250);
        }
    }

    /* ================================================================
       MARKDOWN RENDERER (safe, no external deps)
       Supports: headings (h1-h4), bold, italic, inline code, code blocks,
                 ordered/unordered lists, tables, blockquotes, hr, links.
       All input is HTML-escaped first, then selectively converted.
================================================================ */
    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderInline(text) {
        // text is already escaped. Now add formatting.
        // 1) inline code `code`
        var placeholders = [];
        text = text.replace(/`([^`\n]+?)`/g, function (m, code) {
            var i = placeholders.length;
            placeholders.push('<code>' + code + '</code>');
            return '\u0000CODE' + i + '\u0000';
        });
        // 2) bold **text** or __text__
        text = text.replace(/\*\*([^*\n]+?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/__([^_\n]+?)__/g, '<strong>$1</strong>');
        // 3) italic *text* or _text_ (avoid catching ** or __)
        text = text.replace(/(^|[^*\w])\*([^*\n]+?)\*(?!\w)/g, '$1<em>$2</em>');
        text = text.replace(/(^|[^_\w])_([^_\n]+?)_(?!\w)/g, '$1<em>$2</em>');
        // 4) strikethrough ~~text~~
        text = text.replace(/~~([^~\n]+?)~~/g, '<del>$1</del>');
        // 5) links [text](url) — only allow http(s) and relative
        text = text.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, function (m, label, url) {
            var safe = /^(https?:\/\/|\/|#)/i.test(url) ? url : '#';
            return '<a href="' + safe + '" target="_blank" rel="noopener noreferrer">' + label + '</a>';
        });
        // 6) restore code placeholders
        text = text.replace(/\u0000CODE(\d+)\u0000/g, function (m, i) {
            return placeholders[parseInt(i, 10)] || '';
        });
        return text;
    }

    function renderMarkdown(raw) {
        if (!raw) return '';
        var text = String(raw).replace(/\r\n/g, '\n');
        // Extract fenced code blocks first (preserve as-is)
        var codeBlocks = [];
        text = text.replace(/```(\w*)\n([\s\S]*?)```/g, function (m, lang, code) {
            var i = codeBlocks.length;
            codeBlocks.push('<pre><code class="lang-' + (lang || '') + '">' + escapeHtml(code.replace(/\n$/, '')) + '</code></pre>');
            return '\u0001CB' + i + '\u0001';
        });

        var lines = text.split('\n');
        var out = [];
        var i = 0;

        while (i < lines.length) {
            var line = lines[i];

            // Code block placeholder
            var cbMatch = line.match(/^\u0001CB(\d+)\u0001$/);
            if (cbMatch) {
                out.push(codeBlocks[parseInt(cbMatch[1], 10)]);
                i++;
                continue;
            }

            // Horizontal rule
            if (/^\s*(?:---|\*\*\*|___)\s*$/.test(line)) {
                out.push('<hr>');
                i++;
                continue;
            }

            // Headings (only flush any open list first)
            var headingMatch = line.match(/^(#{1,4})\s+(.+?)\s*#*\s*$/);
            if (headingMatch) {
                flushList();
                var level = headingMatch[1].length;
                out.push('<h' + level + '>' + renderInline(escapeHtml(headingMatch[2])) + '</h' + level + '>');
                i++;
                continue;
            }

            // Table — header line + separator + body rows
            if (/^\s*\|.+\|\s*$/.test(line) && i + 1 < lines.length && /^\s*\|?\s*:?-{3,}/.test(lines[i + 1])) {
                flushList();
                var headerCells = parseTableRow(lines[i]);
                i += 2; // skip header + separator
                var tableHtml = '<table><thead><tr>';
                headerCells.forEach(function (cell) {
                    tableHtml += '<th>' + renderInline(escapeHtml(cell)) + '</th>';
                });
                tableHtml += '</tr></thead><tbody>';
                while (i < lines.length && /^\s*\|.+\|\s*$/.test(lines[i])) {
                    var rowCells = parseTableRow(lines[i]);
                    tableHtml += '<tr>';
                    rowCells.forEach(function (cell) {
                        tableHtml += '<td>' + renderInline(escapeHtml(cell)) + '</td>';
                    });
                    tableHtml += '</tr>';
                    i++;
                }
                tableHtml += '</tbody></table>';
                out.push(tableHtml);
                continue;
            }

            // Blockquote
            if (/^\s*>\s?/.test(line)) {
                flushList();
                var quoteLines = [];
                while (i < lines.length && /^\s*>\s?/.test(lines[i])) {
                    quoteLines.push(lines[i].replace(/^\s*>\s?/, ''));
                    i++;
                }
                out.push('<blockquote>' + renderInline(escapeHtml(quoteLines.join('\n'))) + '</blockquote>');
                continue;
            }

            // Unordered list
            if (/^\s*[-*+]\s+/.test(line)) {
                startList('ul');
                var text2 = line.replace(/^\s*[-*+]\s+/, '');
                currentList.items.push(renderInline(escapeHtml(text2)));
                i++;
                continue;
            }

            // Ordered list
            if (/^\s*\d+\.\s+/.test(line)) {
                startList('ol');
                var text3 = line.replace(/^\s*\d+\.\s+/, '');
                currentList.items.push(renderInline(escapeHtml(text3)));
                i++;
                continue;
            }

            // Empty line — list terminator + paragraph break
            if (/^\s*$/.test(line)) {
                flushList();
                i++;
                continue;
            }

            // Paragraph — collect until empty line or special line
            flushList();
            var paraLines = [line];
            i++;
            while (i < lines.length
                && !/^\s*$/.test(lines[i])
                && !/^(#{1,4}\s|>\s?|[-*+]\s+|\d+\.\s+|```|\s*\|.+\|\s*$|---|\*\*\*|___)/.test(lines[i])) {
                paraLines.push(lines[i]);
                i++;
            }
            out.push('<p>' + renderInline(escapeHtml(paraLines.join(' '))) + '</p>');
        }

        flushList();

        // Restore code blocks that weren't in single-line form
        return out.join('').replace(/\u0001CB(\d+)\u0001/g, function (m, idx) {
            return codeBlocks[parseInt(idx, 10)] || '';
        });

        // ---- list helper state ----
        var currentList = null;

        function startList(type) {
            if (currentList && currentList.type === type) return;
            flushList();
            currentList = { type: type, items: [] };
        }
        function flushList() {
            if (!currentList) return;
            var html = '<' + currentList.type + '>';
            currentList.items.forEach(function (item) {
                html += '<li>' + item + '</li>';
            });
            html += '</' + currentList.type + '>';
            out.push(html);
            currentList = null;
        }
    }

    function parseTableRow(line) {
        var trimmed = line.trim().replace(/^\|/, '').replace(/\|$/, '');
        return trimmed.split('|').map(function (cell) { return cell.trim(); });
    }

    function appendMessage(role, content, loading) {
        var row = document.createElement('div');
        row.className = 'aray-message aray-message--' + role;

        if (role === 'assistant') {
            var avatar = document.createElement('span');
            avatar.className = 'aray-message-avatar';
            var image = document.createElement('img');
            image.src = '/assets/img/aray%20orb.png';
            image.alt = '';
            avatar.appendChild(image);
            row.appendChild(avatar);
        }

        var bubble = document.createElement('div');
        bubble.className = 'aray-message-bubble';
        if (loading) {
            bubble.innerHTML = '<span class="aray-typing" aria-label="ARAY sedang mengetik"><i></i><i></i><i></i></span>';
            row.dataset.loading = 'true';
        } else {
            bubble.innerHTML = renderMarkdown(content);
        }
        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
        return row;
    }

    function resizeInput() {
        input.style.height = '38px';
        input.style.height = Math.min(input.scrollHeight, 105) + 'px';
    }

    async function send(message) {
        message = String(message || '').trim();
        if (!message || busy) return;

        busy = true;
        sendButton.disabled = true;
        if (suggestions) suggestions.hidden = true;
        lastUserMessage = message;
        appendMessage('user', message, false);
        input.value = '';
        resizeInput();
        var loadingRow = appendMessage('assistant', '', true);

        try {
            var token = document.querySelector('meta[name="csrf-token"]');
            var response = await fetch(root.dataset.endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.content : ''
                },
                body: JSON.stringify({ message: message, history: history.slice(-8) })
            });
            var data = await response.json().catch(function () { return {}; });
            if (!response.ok) throw new Error(data.message || 'ARAY tidak dapat dihubungi.');

            loadingRow.remove();
            appendMessage('assistant', data.message, false);
            history.push({ role: 'user', content: message }, { role: 'assistant', content: data.message });
            history = history = history.slice(-8);
        } catch (error) {
            loadingRow.remove();
            var msg = error.message || 'Koneksi sedang terganggu. Silakan coba lagi.';
            var row = appendMessage('assistant', '', false);
            var bubble = row.querySelector('.aray-message-bubble');
            bubble.innerHTML =
                '<p style="color: rgba(247,242,234,0.78);">' + escapeText(msg) + '</p>' +
                '<button type="button" class="aray-retry" data-retry="1" ' +
                'style="margin-top:10px;padding:7px 14px;font-size:12px;color:var(--aray-ivory);' +
                'background:rgba(212,167,104,0.12);border:1px solid rgba(212,167,104,0.32);' +
                'border-radius:8px;cursor:pointer;font-family:inherit;">Coba lagi</button>';
            bubble.querySelector('.aray-retry').addEventListener('click', function () {
                row.remove();
                send(lastUserMessage);
            });
        } finally {
            busy = false;
            sendButton.disabled = false;
            input.focus({ preventScroll: true });
        }
    }

    function escapeText(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    launcher.addEventListener('click', function () { setOpen(!root.classList.contains('is-open')); });
    closeButton.addEventListener('click', function () { setOpen(false); });
    form.addEventListener('submit', function (event) { event.preventDefault(); send(input.value); });
    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); send(input.value); }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && root.classList.contains('is-open')) setOpen(false);
    });
    root.querySelectorAll('[data-prompt]').forEach(function (button) {
        button.addEventListener('click', function () { send(button.dataset.prompt); });
    });

    if (!sessionStorage.getItem('aray-invite-seen')) {
        window.setTimeout(function () {
            if (!root.classList.contains('is-open')) invite.classList.add('is-visible');
        }, 1800);
        window.setTimeout(function () { invite.classList.remove('is-visible'); }, 9000);
        sessionStorage.setItem('aray-invite-seen', '1');
    }
})();
