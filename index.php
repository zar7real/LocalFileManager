<?php
session_start();

// Configurazione
define('USERNAME', 'admin');
define('PASSWORD', 'admin');
define('BASE_PATH', '/var/www/html/files'); // Modifica questo path secondo le tue esigenze

// Funzione per verificare il login
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Gestione logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Gestione login
if (isset($_POST['login'])) {
    if ($_POST['username'] === USERNAME && $_POST['password'] === PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = 'Credenziali non valide';
    }
}

// Funzioni per la gestione dei file
function sanitizePath($path) {
    $realPath = realpath(BASE_PATH . '/' . $path);
    if ($realPath === false || strpos($realPath, realpath(BASE_PATH)) !== 0) {
        return BASE_PATH;
    }
    return $realPath;
}

function getRelativePath($fullPath) {
    return str_replace(realpath(BASE_PATH), '', $fullPath);
}

// Gestione azioni file manager
if (isLoggedIn()) {
    $currentPath = isset($_GET['path']) ? $_GET['path'] : '';
    $fullPath = sanitizePath($currentPath);
    
    // Crea la directory base se non esiste
    if (!is_dir(BASE_PATH)) {
        mkdir(BASE_PATH, 0755, true);
    }
    
    // Gestione azioni AJAX
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        switch ($_POST['action']) {
            case 'upload':
                if (isset($_FILES['file'])) {
                    $targetPath = $fullPath . '/' . basename($_FILES['file']['name']);
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                        echo json_encode(['success' => true, 'message' => 'File caricato con successo']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Errore nel caricamento']);
                    }
                }
                exit;
                
            case 'createFolder':
                $folderName = $_POST['name'];
                $newPath = $fullPath . '/' . $folderName;
                if (mkdir($newPath, 0755)) {
                    echo json_encode(['success' => true, 'message' => 'Cartella creata con successo']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Errore nella creazione della cartella']);
                }
                exit;
                
            case 'rename':
                $oldName = $_POST['oldName'];
                $newName = $_POST['newName'];
                $oldPath = $fullPath . '/' . $oldName;
                $newPath = $fullPath . '/' . $newName;
                if (rename($oldPath, $newPath)) {
                    echo json_encode(['success' => true, 'message' => 'Rinominato con successo']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Errore nella rinomina']);
                }
                exit;
                
            case 'delete':
                $fileName = $_POST['name'];
                $filePath = $fullPath . '/' . $fileName;
                if (is_dir($filePath)) {
                    if (rmdir($filePath)) {
                        echo json_encode(['success' => true, 'message' => 'Cartella eliminata']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Errore nell\'eliminazione della cartella']);
                    }
                } else {
                    if (unlink($filePath)) {
                        echo json_encode(['success' => true, 'message' => 'File eliminato']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Errore nell\'eliminazione del file']);
                    }
                }
                exit;
                
            case 'saveFile':
                $fileName = $_POST['fileName'];
                $content = $_POST['content'];
                $filePath = $fullPath . '/' . $fileName;
                if (file_put_contents($filePath, $content) !== false) {
                    echo json_encode(['success' => true, 'message' => 'File salvato con successo']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio']);
                }
                exit;
                
            case 'getFileContent':
                $fileName = $_POST['fileName'];
                $filePath = $fullPath . '/' . $fileName;
                if (is_file($filePath) && is_readable($filePath)) {
                    $content = file_get_contents($filePath);
                    echo json_encode(['success' => true, 'content' => $content]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Impossibile leggere il file']);
                }
                exit;
        }
    }
    
    // Ottieni lista file e cartelle
    $items = [];
    if (is_dir($fullPath)) {
        $files = scandir($fullPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $itemPath = $fullPath . '/' . $file;
                $items[] = [
                    'name' => $file,
                    'type' => is_dir($itemPath) ? 'folder' : 'file',
                    'size' => is_file($itemPath) ? filesize($itemPath) : 0,
                    'modified' => filemtime($itemPath)
                ];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Web</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-card, .main-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .login-card {
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        
        .login-card h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        }
        
        .error {
            color: #ff6b6b;
            margin-top: 10px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .breadcrumb {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .file-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .file-item:hover {
            background-color: #f8f9fa;
        }
        
        .file-icon {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 20px;
        }
        
        .folder-icon {
            background: linear-gradient(135deg, #ffd93d 0%, #ff6b6b 100%);
            color: white;
        }
        
        .file-icon-default {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }
        
        .file-info {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-name {
            font-weight: 600;
            color: #333;
            cursor: pointer;
        }
        
        .file-meta {
            color: #666;
            font-size: 14px;
        }
        
        .file-actions {
            display: flex;
            gap: 5px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        textarea {
            width: 100%;
            height: 300px;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            resize: vertical;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: border-color 0.3s;
        }
        
        .upload-area:hover {
            border-color: #667eea;
        }
        
        .upload-area.dragover {
            border-color: #667eea;
            background-color: #f0f4ff;
        }
        
        #fileInput {
            display: none;
        }
        
        .message {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .file-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .file-info {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .toolbar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!isLoggedIn()): ?>
            <div class="login-card">
                <h1>🔐 File Manager</h1>
                <?php if (isset($loginError)): ?>
                    <div class="error"><?php echo $loginError; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn">Accedi</button>
                </form>
            </div>
        <?php else: ?>
            <div class="main-card">
                <div class="header">
                    <h1>📁 File Manager</h1>
                    <a href="?logout=1" class="btn btn-danger btn-small">Logout</a>
                </div>
                
                <div class="message" id="message"></div>
                
                <div class="breadcrumb">
                    <a href="?">Home</a>
                    <?php 
                    $pathParts = array_filter(explode('/', $currentPath));
                    $buildPath = '';
                    foreach ($pathParts as $part) {
                        $buildPath .= '/' . $part;
                        echo ' / <a href="?path=' . urlencode(ltrim($buildPath, '/')) . '">' . htmlspecialchars($part) . '</a>';
                    }
                    ?>
                </div>
                
                <div class="toolbar">
                    <button class="btn btn-success btn-small" onclick="showUploadModal()">📤 Carica File</button>
                    <button class="btn btn-small" onclick="showCreateFolderModal()">📁 Nuova Cartella</button>
                    <button class="btn btn-small" onclick="location.reload()">🔄 Aggiorna</button>
                </div>
                
                <div class="file-list">
                    <?php if ($currentPath): ?>
                        <div class="file-item">
                            <div class="file-icon folder-icon">📁</div>
                            <div class="file-info">
                                <div class="file-name">
                                    <a href="?path=<?php echo urlencode(dirname($currentPath)); ?>" style="text-decoration: none; color: inherit;">.. (Torna indietro)</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($items as $item): ?>
                        <div class="file-item">
                            <div class="file-icon <?php echo $item['type'] === 'folder' ? 'folder-icon' : 'file-icon-default'; ?>">
                                <?php echo $item['type'] === 'folder' ? '📁' : '📄'; ?>
                            </div>
                            <div class="file-info">
                                <div>
                                    <?php if ($item['type'] === 'folder'): ?>
                                        <div class="file-name">
                                            <a href="?path=<?php echo urlencode($currentPath . '/' . $item['name']); ?>" style="text-decoration: none; color: inherit;">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-name" onclick="editFile('<?php echo htmlspecialchars($item['name']); ?>')">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="file-meta">
                                        <?php if ($item['type'] === 'file'): ?>
                                            <?php echo number_format($item['size'] / 1024, 2); ?> KB - 
                                        <?php endif; ?>
                                        <?php echo date('d/m/Y H:i', $item['modified']); ?>
                                    </div>
                                </div>
                                <div class="file-actions">
                                    <button class="btn btn-small" onclick="renameItem('<?php echo htmlspecialchars($item['name']); ?>')">✏️</button>
                                    <button class="btn btn-danger btn-small" onclick="deleteItem('<?php echo htmlspecialchars($item['name']); ?>')">🗑️</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($items)): ?>
                        <div class="file-item">
                            <div style="text-align: center; width: 100%; color: #666; font-style: italic;">
                                Nessun file o cartella trovata
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal per upload file -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('uploadModal')">&times;</span>
            <h3>📤 Carica File</h3>
            <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                <p>Clicca qui o trascina i file per caricarli</p>
                <input type="file" id="fileInput" multiple>
            </div>
            <div id="uploadProgress"></div>
        </div>
    </div>
    
    <!-- Modal per creare cartella -->
    <div id="createFolderModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('createFolderModal')">&times;</span>
            <h3>📁 Crea Nuova Cartella</h3>
            <div class="form-group">
                <label for="folderName">Nome cartella:</label>
                <input type="text" id="folderName" placeholder="Nome della cartella">
            </div>
            <button class="btn" onclick="createFolder()">Crea Cartella</button>
        </div>
    </div>
    
    <!-- Modal per modificare file -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
            <h3 id="editTitle">✏️ Modifica File</h3>
            <textarea id="fileContent" placeholder="Contenuto del file..."></textarea>
            <div style="margin-top: 15px;">
                <button class="btn btn-success" onclick="saveFile()">💾 Salva</button>
                <button class="btn" onclick="closeModal('editModal')">Annulla</button>
            </div>
        </div>
    </div>

    <script>
        let currentEditingFile = '';
        
        function showMessage(message, type = 'success') {
            const messageEl = document.getElementById('message');
            messageEl.textContent = message;
            messageEl.className = `message ${type}`;
            messageEl.style.display = 'block';
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }
        
        function showUploadModal() {
            document.getElementById('uploadModal').style.display = 'block';
        }
        
        function showCreateFolderModal() {
            document.getElementById('createFolderModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function createFolder() {
            const folderName = document.getElementById('folderName').value.trim();
            if (!folderName) {
                alert('Inserisci un nome per la cartella');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'createFolder');
            formData.append('name', folderName);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    closeModal('createFolderModal');
                    location.reload();
                } else {
                    showMessage(data.message, 'error');
                }
            });
        }
        
        function editFile(fileName) {
            currentEditingFile = fileName;
            document.getElementById('editTitle').textContent = `✏️ Modifica: ${fileName}`;
            
            const formData = new FormData();
            formData.append('action', 'getFileContent');
            formData.append('fileName', fileName);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('fileContent').value = data.content;
                    document.getElementById('editModal').style.display = 'block';
                } else {
                    showMessage(data.message, 'error');
                }
            });
        }
        
        function saveFile() {
            const content = document.getElementById('fileContent').value;
            
            const formData = new FormData();
            formData.append('action', 'saveFile');
            formData.append('fileName', currentEditingFile);
            formData.append('content', content);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    closeModal('editModal');
                } else {
                    showMessage(data.message, 'error');
                }
            });
        }
        
        function renameItem(oldName) {
            const newName = prompt('Nuovo nome:', oldName);
            if (!newName || newName === oldName) return;
            
            const formData = new FormData();
            formData.append('action', 'rename');
            formData.append('oldName', oldName);
            formData.append('newName', newName);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    location.reload();
                } else {
                    showMessage(data.message, 'error');
                }
            });
        }
        
        function deleteItem(name) {
            if (!confirm(`Sei sicuro di voler eliminare "${name}"?`)) return;
            
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('name', name);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    location.reload();
                } else {
                    showMessage(data.message, 'error');
                }
            });
        }
        
        // Gestione upload file
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.querySelector('.upload-area');
        
        fileInput.addEventListener('change', handleFileUpload);
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            uploadFiles(files);
        });
        
        function handleFileUpload() {
            const files = fileInput.files;
            uploadFiles(files);
        }
        
        function uploadFiles(files) {
            Array.from(files).forEach(file => {
                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('file', file);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(`${file.name} caricato con successo`, 'success');
                    } else {
                        showMessage(`Errore nel caricamento di ${file.name}`, 'error');
                    }
                })
                .catch(() => {
                    showMessage(`Errore nel caricamento di ${file.name}`, 'error');
                });
            });
            
            setTimeout(() => {
                closeModal('uploadModal');
                location.reload();
            }, 1000);
        }
        
        // Chiudi modal cliccando fuori
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });
    </script>
</body>
</html>
