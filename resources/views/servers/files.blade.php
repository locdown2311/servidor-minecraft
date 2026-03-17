<x-app-layout>
    @section('title', $server->name . ' — Arquivos')

    <div class="mc-panel">
        @include('servers._sidebar')

        <div class="mc-panel-content">
            <div class="mc-panel-header">
                <h2 class="mc-panel-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                    Gerenciador de Arquivos
                </h2>
                @if($server->isRunning())
                    <div class="mc-fm-header-actions">
                        <button onclick="showCreateModal('file')" class="mc-btn mc-btn-outline mc-btn-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                            Novo Arquivo
                        </button>
                        <button onclick="showCreateModal('folder')" class="mc-btn mc-btn-outline mc-btn-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                            Nova Pasta
                        </button>
                        <label class="mc-btn mc-btn-primary mc-btn-sm mc-fm-upload-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Upload
                            <input type="file" id="upload-input" style="display:none" onchange="handleUpload(this)" multiple>
                        </label>
                    </div>
                @endif
            </div>

            @if(!$server->isRunning())
                <div class="mc-panel-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                    <h3>Servidor Offline</h3>
                    <p>Inicie o servidor para acessar os arquivos.</p>
                </div>
            @else
                {{-- Breadcrumbs --}}
                <nav class="mc-fm-breadcrumbs">
                    @foreach($breadcrumbs as $i => $crumb)
                        @if($i < count($breadcrumbs) - 1)
                            <a href="{{ route('servers.files', ['server' => $server, 'path' => $crumb['path']]) }}" class="mc-fm-breadcrumb">{{ $crumb['name'] }}</a>
                            <span class="mc-fm-breadcrumb-sep">/</span>
                        @else
                            <span class="mc-fm-breadcrumb mc-fm-breadcrumb-active">{{ $crumb['name'] }}</span>
                        @endif
                    @endforeach
                </nav>

                {{-- Upload Drop Zone --}}
                <div class="mc-fm-dropzone" id="dropzone" style="display: none;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p>Solte os arquivos aqui para enviar</p>
                </div>

                {{-- Upload Progress --}}
                <div id="upload-progress" class="mc-fm-upload-progress" style="display: none;">
                    <div class="mc-fm-upload-progress-text">Enviando...</div>
                    <div class="mc-resource-bar"><div class="mc-resource-bar-fill mc-resource-bar-cpu" id="upload-bar" style="width: 0%"></div></div>
                </div>

                {{-- File Table --}}
                <div class="mc-fm-table-wrapper">
                    <table class="mc-table mc-fm-table">
                        <thead>
                            <tr>
                                <th style="width: 50%">Nome</th>
                                <th>Tamanho</th>
                                <th>Modificado</th>
                                <th style="width: 120px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($path))
                                @php $parentPath = dirname($path) === '.' ? '' : dirname($path); @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('servers.files', ['server' => $server, 'path' => $parentPath]) }}" class="mc-fm-item">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                                            <span>..</span>
                                        </a>
                                    </td>
                                    <td></td><td></td><td></td>
                                </tr>
                            @endif

                            @forelse($files as $file)
                                <tr class="mc-fm-row" data-path="{{ $file['path'] }}" data-name="{{ $file['name'] }}" data-dir="{{ $file['is_dir'] ? '1' : '0' }}">
                                    <td>
                                        @if($file['is_dir'])
                                            <a href="{{ route('servers.files', ['server' => $server, 'path' => $file['path']]) }}" class="mc-fm-item mc-fm-item-dir">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                                                <span>{{ $file['name'] }}</span>
                                            </a>
                                        @else
                                            <span class="mc-fm-item mc-fm-item-file">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                <span>{{ $file['name'] }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="mc-text-muted">{{ $file['is_dir'] ? '—' : formatBytes($file['size']) }}</td>
                                    <td class="mc-text-muted">{{ $file['modified'] }}</td>
                                    <td>
                                        <div class="mc-fm-actions">
                                            @if($file['editable'])
                                                <button onclick="editFile('{{ $file['path'] }}')" class="mc-btn mc-btn-ghost mc-btn-xs" title="Editar">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </button>
                                            @endif
                                            @if(!$file['is_dir'])
                                                <a href="{{ route('servers.files.download', ['server' => $server, 'path' => $file['path']]) }}" class="mc-btn mc-btn-ghost mc-btn-xs" title="Download">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </a>
                                            @endif
                                            <button onclick="renameItem('{{ $file['path'] }}', '{{ $file['name'] }}')" class="mc-btn mc-btn-ghost mc-btn-xs" title="Renomear">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                            </button>
                                            <button onclick="deleteItem('{{ $file['path'] }}', '{{ $file['name'] }}')" class="mc-btn mc-btn-danger-ghost mc-btn-xs" title="Excluir">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="mc-text-muted" style="text-align:center; padding: 2rem;">Pasta vazia</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Editor Modal --}}
                <div class="mc-fm-editor-overlay" id="editor-overlay" style="display: none;">
                    <div class="mc-fm-editor-modal">
                        <div class="mc-fm-editor-header">
                            <h3 id="editor-filename">Editando arquivo</h3>
                            <div>
                                <button onclick="saveFile()" class="mc-btn mc-btn-primary mc-btn-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Salvar
                                </button>
                                <button onclick="closeEditor()" class="mc-btn mc-btn-ghost mc-btn-sm">Fechar</button>
                            </div>
                        </div>
                        <textarea id="editor-content" class="mc-fm-editor-textarea" spellcheck="false"></textarea>
                    </div>
                </div>

                {{-- Create Modal --}}
                <div class="mc-fm-editor-overlay" id="create-overlay" style="display: none;">
                    <div class="mc-fm-create-modal">
                        <h3 id="create-title">Novo Item</h3>
                        <input type="text" id="create-name" class="mc-input" placeholder="Nome do arquivo ou pasta">
                        <input type="hidden" id="create-type" value="file">
                        <div class="mc-fm-create-actions">
                            <button onclick="doCreate()" class="mc-btn mc-btn-primary mc-btn-sm">Criar</button>
                            <button onclick="closeCreateModal()" class="mc-btn mc-btn-ghost mc-btn-sm">Cancelar</button>
                        </div>
                    </div>
                </div>

                {{-- Status Toast --}}
                <div id="fm-toast" class="mc-fm-toast" style="display: none;"></div>
            @endif
        </div>
    </div>

    @if($server->isRunning())
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const currentPath = '{{ $path }}';
        const serverId = {{ $server->id }};
        const baseUrl = '{{ url("/servidores/{$server->id}/arquivos") }}';

        let currentEditPath = '';

        // ---- EDITOR ----
        function editFile(path) {
            document.getElementById('editor-overlay').style.display = 'flex';
            document.getElementById('editor-filename').textContent = path.split('/').pop();
            document.getElementById('editor-content').value = 'Carregando...';
            currentEditPath = path;

            fetch('{{ route("servers.files.read", $server) }}?path=' + encodeURIComponent(path), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('editor-content').value = 'Erro: ' + data.error;
                } else {
                    document.getElementById('editor-content').value = data.content;
                }
            })
            .catch(() => {
                document.getElementById('editor-content').value = 'Erro ao carregar arquivo.';
            });
        }

        function saveFile() {
            const content = document.getElementById('editor-content').value;

            fetch('{{ route("servers.files.save", $server) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ path: currentEditPath, content: content }),
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) closeEditor();
            })
            .catch(() => showToast('Erro ao salvar.', 'error'));
        }

        function closeEditor() {
            document.getElementById('editor-overlay').style.display = 'none';
            currentEditPath = '';
        }

        // ---- CREATE ----
        function showCreateModal(type) {
            document.getElementById('create-overlay').style.display = 'flex';
            document.getElementById('create-type').value = type;
            document.getElementById('create-title').textContent = type === 'folder' ? 'Nova Pasta' : 'Novo Arquivo';
            document.getElementById('create-name').value = '';
            document.getElementById('create-name').focus();
        }

        function closeCreateModal() {
            document.getElementById('create-overlay').style.display = 'none';
        }

        function doCreate() {
            const name = document.getElementById('create-name').value.trim();
            const type = document.getElementById('create-type').value;
            if (!name) return;

            const fullPath = currentPath ? currentPath + '/' + name : name;

            fetch('{{ route("servers.files.create", $server) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ path: fullPath, type: type }),
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeCreateModal();
                    location.reload();
                }
            })
            .catch(() => showToast('Erro ao criar.', 'error'));
        }

        // ---- RENAME ----
        function renameItem(path, currentName) {
            const newName = prompt('Novo nome:', currentName);
            if (!newName || newName === currentName) return;

            const dir = path.includes('/') ? path.substring(0, path.lastIndexOf('/')) : '';
            const newPath = dir ? dir + '/' + newName : newName;

            fetch('{{ route("servers.files.rename", $server) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ from: path, to: newPath }),
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) location.reload();
            })
            .catch(() => showToast('Erro ao renomear.', 'error'));
        }

        // ---- DELETE ----
        function deleteItem(path, name) {
            if (!confirm('Excluir "' + name + '"? Esta ação é irreversível.')) return;

            fetch('{{ route("servers.files.delete", $server) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ path: path }),
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) location.reload();
            })
            .catch(() => showToast('Erro ao excluir.', 'error'));
        }

        // ---- UPLOAD ----
        function handleUpload(input) {
            const files = input.files;
            if (!files.length) return;

            for (let i = 0; i < files.length; i++) {
                uploadSingleFile(files[i]);
            }
            input.value = '';
        }

        function uploadSingleFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('path', currentPath);

            const progress = document.getElementById('upload-progress');
            progress.style.display = 'block';

            fetch('{{ route("servers.files.upload", $server) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
            .then(r => r.json())
            .then(data => {
                progress.style.display = 'none';
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) location.reload();
            })
            .catch(() => {
                progress.style.display = 'none';
                showToast('Erro no upload.', 'error');
            });
        }

        // ---- DRAG & DROP ----
        const dropzone = document.getElementById('dropzone');
        const panelContent = document.querySelector('.mc-panel-content');

        panelContent.addEventListener('dragover', e => {
            e.preventDefault();
            dropzone.style.display = 'flex';
        });

        panelContent.addEventListener('dragleave', e => {
            if (!panelContent.contains(e.relatedTarget)) {
                dropzone.style.display = 'none';
            }
        });

        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.style.display = 'none';
            const files = e.dataTransfer.files;
            for (let i = 0; i < files.length; i++) {
                uploadSingleFile(files[i]);
            }
        });

        // ---- TOAST ----
        function showToast(message, type) {
            const toast = document.getElementById('fm-toast');
            toast.textContent = message;
            toast.className = 'mc-fm-toast mc-fm-toast-' + type;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 4000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditor();
                closeCreateModal();
            }
        });
    </script>
    @endif
</x-app-layout>
