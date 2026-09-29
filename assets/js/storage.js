const StorageAdapter = {
    getKey() {
        return 'gw_messages_' + (window.GW_CONFIG?.roomId || 'default');
    },

    fetch() {
        try {
            const data = localStorage.getItem(this.getKey());
            return data ? JSON.parse(data) : [];
        } catch (e) {
            return [];
        }
    },

    append(msg) {
        const items = this.fetch();
        items.push(msg);

        const now = Math.floor(Date.now() / 1000);
        const ttl = window.GW_CONFIG?.ttl || 1800;
        const filtered = items.filter(item => (now - item.timestamp) < ttl);

        try {
            localStorage.setItem(this.getKey(), JSON.stringify(filtered));
        } catch (e) {
            console.error(e);
        }
    }
};
