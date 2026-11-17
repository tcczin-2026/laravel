const dbName = "LaravelOfflineDB";
const dbVersion = 1;
let db;

// Abrir ou criar o banco de dados IndexedDB
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, dbVersion);

        request.onupgradeneeded = (event) => {
            db = event.target.result;
            if (!db.objectStoreNames.contains("data")) {
                db.createObjectStore("data", { keyPath: "id" });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Salvar dados no IndexedDB
async function saveData(data) {
    const db = await openDatabase();
    const transaction = db.transaction("data", "readwrite");
    const store = transaction.objectStore("data");
    store.put(data);
}

// Recuperar dados do IndexedDB
async function getData() {
    const db = await openDatabase();
    const transaction = db.transaction("data", "readonly");
    const store = transaction.objectStore("data");
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}