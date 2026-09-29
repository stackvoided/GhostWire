document.addEventListener('DOMContentLoaded', () => {
    const feed = document.getElementById('feed');
    const form = document.getElementById('dispatchForm');
    const attach = document.getElementById('attach');
    const indicator = document.getElementById('fileIndicator');
    const field = document.getElementById('messageField');

    if (!feed || !form) return;

    const sanitize = (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, tag => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[tag]);
    };

    const render = () => {
        const records = (typeof StorageAdapter !== 'undefined' && StorageAdapter.fetch) 
            ? StorageAdapter.fetch() 
            : [];

        feed.innerHTML = '';

        records.forEach(item => {
            const isSelf = item.sender === window.GW_CONFIG?.alias;
            const row = document.createElement('div');
            row.className = `msg-row ${isSelf ? 'out' : ''}`;

            const senderName = item.sender || 'Аноним';
            const avatarNode = item.avatar 
                ? `<img src="file.php?name=${sanitize(item.avatar)}" class="msg-avatar">`
                : `<div class="msg-avatar chip-fallback">${sanitize(senderName.charAt(0))}</div>`;

            let mediaBlock = '';
            if (item.file) {
                const fileHash = item.file.hash || item.file.name;
                const fileName = item.file.original || item.file.name || 'Вложение';
                
                if (item.file.mime && item.file.mime.startsWith('image/')) {
                    mediaBlock = `<div class="msg-media"><img src="file.php?name=${sanitize(fileHash)}"></div>`;
                } else {
                    mediaBlock = `<a href="file.php?name=${sanitize(fileHash)}" target="_blank" class="msg-file-link">📎 ${sanitize(fileName)}</a>`;
                }
            }

            const time = item.timestamp 
                ? new Date(item.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) 
                : '';

            row.innerHTML = `
                ${avatarNode}
                <div class="msg-bubble">
                    <div class="msg-author">${sanitize(senderName)}</div>
                    ${item.body ? `<div class="msg-text">${sanitize(item.body)}</div>` : ''}
                    ${mediaBlock}
                    <div class="msg-time">${time}</div>
                </div>
            `;

            feed.appendChild(row);
        });

        feed.scrollTop = feed.scrollHeight;
    };

    const syncFeed = async () => {
        if (!window.GW_CONFIG?.roomId) return;

        try {
            const res = await fetch(`api.php?action=fetch&room=${encodeURIComponent(window.GW_CONFIG.roomId)}`);
            if (!res.ok) return;

            const json = await res.json();
            const remoteMessages = json.data || json.messages;

            if (Array.isArray(remoteMessages)) {
                if (typeof StorageAdapter !== 'undefined' && StorageAdapter.append) {
                    remoteMessages.forEach(msg => StorageAdapter.append(msg));
                }
                render();
            }
        } catch (err) {
            console.error('Sync failure:', err);
        }
    };

    attach?.addEventListener('change', () => {
        if (!indicator) return;
        indicator.textContent = attach.files.length > 0 
            ? (attach.files[0].name.length > 15 ? attach.files[0].name.substring(0, 12) + '...' : attach.files[0].name)
            : '';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const textVal = field.value.trim();
        const hasFile = attach && attach.files && attach.files.length > 0;

        if (!textVal && !hasFile) return;

        const payload = new FormData(form);

        try {
            const res = await fetch('api.php', {
                method: 'POST',
                body: payload
            });

            const json = await res.json();

            if (res.ok && json.status === 'ok') {
                if (json.data && typeof StorageAdapter !== 'undefined' && StorageAdapter.append) {
                    StorageAdapter.append(json.data);
                }

                field.value = '';
                if (attach) attach.value = '';
                if (indicator) indicator.textContent = '';

                render();
            } else {
                console.error(json.error || 'Dispatch error');
            }
        } catch (err) {
            console.error('Network failure:', err);
        }
    });

    render();
    syncFeed();
    setInterval(syncFeed, 3000);
});
