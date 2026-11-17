async function syncData() {
    const data = await getData(); // Recupera os dados do IndexedDB

    if (data.length > 0) {
        fetch('/api/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ data }),
        })
        .then(response => response.json())
        .then(result => {
            console.log(result.message);
        })
        .catch(error => {
            console.error('Erro ao sincronizar os dados:', error);
        });
    }
}

// Detectar quando o usuário volta a ficar online
window.addEventListener('online', () => {
    console.log('Conexão restabelecida. Sincronizando dados...');
    syncData();
});