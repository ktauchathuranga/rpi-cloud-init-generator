document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // Dynamic Write Files Section
    // ============================================
    const writeFilesContainer = document.getElementById('write-files-container');
    const addFileBtn = document.getElementById('add-file-btn');
    let fileCount = 0;

    function createFileEntry() {
        fileCount++;
        const fileEntry = document.createElement('div');
        fileEntry.className = 'file-entry';
        fileEntry. innerHTML = `
            <div class="file-entry-header">
                <h4>File #${fileCount}</h4>
                <button type="button" class="btn btn-danger remove-file-btn">Remove</button>
            </div>
            <div class="form-group">
                <label>File Path:</label>
                <input type="text" class="file-path" placeholder="/etc/myconfig.conf" required>
            </div>
            <div class="form-group">
                <label>Permissions (optional):</label>
                <input type="text" class="file-permissions" placeholder="0644">
            </div>
            <div class="form-group">
                <label>Owner (optional):</label>
                <input type="text" class="file-owner" placeholder="root: root">
            </div>
            <div class="form-group">
                <label>Content:</label>
                <textarea class="file-content" rows="4" placeholder="File content here..."></textarea>
            </div>
        `;

        // Add remove button functionality
        fileEntry.querySelector('.remove-file-btn').addEventListener('click', function() {
            fileEntry.remove();
            updateFileNumbers();
        });

        return fileEntry;
    }

    function updateFileNumbers() {
        const entries = writeFilesContainer.querySelectorAll('.file-entry');
        entries.forEach((entry, index) => {
            entry.querySelector('h4').textContent = `File #${index + 1}`;
        });
        fileCount = entries.length;
    }

    addFileBtn.addEventListener('click', function() {
        writeFilesContainer.appendChild(createFileEntry());
    });

    // ============================================
    // Network Configuration Toggle
    // ============================================
    const enableNetwork = document.getElementById('enable_network');
    const networkConfigSection = document.getElementById('network-config-section');

    enableNetwork.addEventListener('change', function() {
        networkConfigSection.style.display = this.checked ? 'block' :  'none';
    });

    // Ethernet toggle
    const ethEnabled = document. getElementById('eth_enabled');
    const ethConfig = document.getElementById('eth-config');

    ethEnabled.addEventListener('change', function() {
        ethConfig. style.display = this.checked ?  'block' : 'none';
    });

    // Ethernet DHCP toggle
    const ethDhcp4 = document.getElementById('eth_dhcp4');
    const ethStaticConfig = document.getElementById('eth-static-config');

    ethDhcp4.addEventListener('change', function() {
        ethStaticConfig.style.display = this.checked ? 'none' : 'block';
    });

    // WiFi toggle
    const wifiEnabled = document. getElementById('wifi_enabled');
    const wifiConfig = document.getElementById('wifi-config');

    wifiEnabled.addEventListener('change', function() {
        wifiConfig. style.display = this.checked ?  'block' : 'none';
    });

    // WiFi DHCP toggle
    const wifiDhcp4 = document.getElementById('wifi_dhcp4');
    const wifiStaticConfig = document.getElementById('wifi-static-config');

    wifiDhcp4.addEventListener('change', function() {
        wifiStaticConfig.style.display = this. checked ? 'none' : 'block';
    });

    // WiFi Open Network toggle
    const wifiOpenNetwork = document.getElementById('wifi_open_network');
    const wifiPasswordGroup = document.getElementById('wifi-password-group');
    const wifiPasswordField = document.getElementById('wifi_password');

    wifiOpenNetwork. addEventListener('change', function() {
        if (this.checked) {
            wifiPasswordGroup.style.display = 'none';
            wifiPasswordField. value = '';
            wifiPasswordField.disabled = true;
        } else {
            wifiPasswordGroup.style.display = 'block';
            wifiPasswordField.disabled = false;
        }
    });

    // ============================================
    // Form Submission - Collect Write Files Data
    // ============================================
    const form = document.getElementById('cloud-init-form');

    form.addEventListener('submit', function(e) {
        // Collect write files data before submission
        const writeFilesData = collectWriteFilesData();
        
        // Create or update hidden input for write files
        let writeFilesInput = form.querySelector('input[name="write_files"]');
        if (!writeFilesInput) {
            writeFilesInput = document.createElement('input');
            writeFilesInput.type = 'hidden';
            writeFilesInput.name = 'write_files';
            form.appendChild(writeFilesInput);
        }
        writeFilesInput.value = JSON.stringify(writeFilesData);
    });

    function collectWriteFilesData() {
        const files = [];
        const entries = writeFilesContainer.querySelectorAll('.file-entry');
        
        entries.forEach(entry => {
            const path = entry.querySelector('.file-path').value.trim();
            const content = entry.querySelector('.file-content').value;
            const permissions = entry.querySelector('.file-permissions').value.trim();
            const owner = entry.querySelector('.file-owner').value.trim();
            
            if (path && content) {
                files.push({
                    path: path,
                    content: content,
                    permissions: permissions,
                    owner:  owner
                });
            }
        });
        
        return files;
    }

    // ============================================
    // Preview Modal
    // ============================================
    const previewBtn = document.getElementById('preview-btn');
    const modal = document.getElementById('preview-modal');
    const closeBtn = modal.querySelector('.close');
    const tabBtns = modal.querySelectorAll('.tab-btn');
    const previewPanes = modal.querySelectorAll('.preview-pane');

    previewBtn.addEventListener('click', async function() {
        // Collect form data
        const formData = new FormData(form);
        
        // Add write files data
        const writeFilesData = collectWriteFilesData();
        formData.set('write_files', JSON.stringify(writeFilesData));

        try {
            const response = await fetch('generate. php? preview=true', {
                method:  'POST',
                body:  formData
            });

            if (response.ok) {
                const data = await response.json();
                
                document.getElementById('meta-data-preview').textContent = data['meta-data'];
                document.getElementById('user-data-preview').textContent = data['user-data'];
                document.getElementById('network-config-preview').textContent = data['network-config'];
                
                modal.style.display = 'block';
            } else {
                alert('Error generating preview.  Please check your input.');
            }
        } catch (error) {
            console.error('Preview error:', error);
            alert('Error generating preview. Please try again.');
        }
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Tab switching
    tabBtns. forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this. dataset.tab;
            
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            previewPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === targetTab + '-preview') {
                    pane.classList.add('active');
                }
            });
        });
    });

    // ============================================
    // Lock Password Toggle
    // ============================================
    const lockPasswd = document.getElementById('lock_passwd');
    const passwordField = document.getElementById('password');
    const sshPwauth = document.getElementById('ssh_pwauth');

    lockPasswd.addEventListener('change', function() {
        if (this.checked) {
            passwordField.disabled = true;
            passwordField.value = '';
            sshPwauth.checked = false;
            sshPwauth.disabled = true;
        } else {
            passwordField.disabled = false;
            sshPwauth.disabled = false;
        }
    });

    // ============================================
    // Keyboard shortcuts
    // ============================================
    document.addEventListener('keydown', function(e) {
        // Escape to close modal
        if (e. key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
        
        // Ctrl+Enter to download
        if (e.ctrlKey && e.key === 'Enter') {
            form.submit();
        }
    });
});