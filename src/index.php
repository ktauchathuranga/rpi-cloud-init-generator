<! DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raspberry Pi Cloud-Init Generator</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Raspberry Pi Cloud-Init Generator</h1>
            <p>Generate cloud-init configuration files for your Raspberry Pi OS</p>
        </header>

        <form id="cloud-init-form" method="POST" action="generate.php">
            <!-- Meta Data Section -->
            <section class="form-section">
                <h2>Meta Data</h2>
                <div class="form-group">
                    <label for="instance_id">Instance ID:</label>
                    <input type="text" id="instance_id" name="instance_id" value="raspberry-pi-001" required>
                    <small>Unique identifier for this instance (change this to re-run cloud-init)</small>
                </div>
                <div class="form-group">
                    <label for="local_hostname">Local Hostname:</label>
                    <input type="text" id="local_hostname" name="local_hostname" value="raspberrypi" required>
                    <small>The hostname for your Raspberry Pi</small>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="manage_etc_hosts" name="manage_etc_hosts" checked>
                        Manage /etc/hosts (enables . local hostname resolution)
                    </label>
                    <small>Installs avahi-daemon for mDNS support</small>
                </div>
            </section>

            <!-- SSH Configuration Section -->
            <section class="form-section">
                <h2>SSH Configuration</h2>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="enable_ssh" name="enable_ssh" checked>
                        <strong>Enable SSH Server</strong>
                    </label>
                    <small>Required to connect via SSH (installs openssh-server)</small>
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="ssh_pwauth" name="ssh_pwauth" checked>
                        Allow SSH password authentication
                    </label>
                    <small>If disabled, only SSH key authentication is allowed</small>
                </div>
                
                <div class="form-group">
                    <label for="ssh_authorized_keys">SSH Authorized Keys: </label>
                    <textarea id="ssh_authorized_keys" name="ssh_authorized_keys" rows="4" placeholder="ssh-rsa AAAAB3...  user@host&#10;ssh-ed25519 AAAAC3... user@host"></textarea>
                    <small>One key per line.  Paste your public SSH key(s) here for key-based authentication</small>
                </div>
            </section>

            <!-- User Data Section -->
            <section class="form-section">
                <h2>User Configuration</h2>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="pi" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter password">
                    <small>Required if SSH password authentication is enabled</small>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="sudo_nopasswd" name="sudo_nopasswd" checked>
                        Grant sudo without password
                    </label>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="lock_passwd" name="lock_passwd">
                        Lock password (SSH key only login)
                    </label>
                </div>
            </section>

            <!-- System Configuration -->
            <section class="form-section">
                <h2>System Configuration</h2>

                <div class="form-group checkbox-group" style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
    <label>
        <input type="checkbox" id="offline_mode" name="offline_mode">
        <strong>Offline Mode (Air-gapped)</strong>
    </label>
    <small style="color: #ffc107; display: block; margin-top: 5px;">
        Prevents all package installation attempts (apt-get). <br>
        Essential for booting without internet. Assumes OpenSSH is pre-installed.
    </small>
</div>
                
                <div class="form-group">
                    <label for="timezone">Timezone:</label>
                    <select id="timezone" name="timezone">
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">America/New_York</option>
                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                        <option value="America/Chicago">America/Chicago</option>
                        <option value="Europe/London">Europe/London</option>
                        <option value="Europe/Paris">Europe/Paris</option>
                        <option value="Europe/Berlin">Europe/Berlin</option>
                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                        <option value="Asia/Shanghai">Asia/Shanghai</option>
                        <option value="Asia/Kolkata">Asia/Kolkata</option>
                        <option value="Asia/Colombo" selected>Asia/Colombo</option>
                        <option value="Australia/Sydney">Australia/Sydney</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="locale">Locale:</label>
                    <input type="text" id="locale" name="locale" value="en_US.UTF-8">
                </div>
                
                <div class="form-group">
                    <label for="keyboard_layout">Keyboard Layout:</label>
                    <input type="text" id="keyboard_layout" name="keyboard_layout" value="us">
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="package_update" name="package_update" checked>
                        Update packages on first boot
                    </label>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="package_upgrade" name="package_upgrade">
                        Upgrade all packages on first boot
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="packages">Additional Packages to Install:</label>
                    <textarea id="packages" name="packages" rows="3" placeholder="vim&#10;htop&#10;git"></textarea>
                    <small>One package per line</small>
                </div>
            </section>

            <!-- Raspberry Pi Hardware Configuration -->
            <section class="form-section">
                <h2>Raspberry Pi Hardware</h2>
                
                <fieldset class="network-fieldset">
                    <legend>Interfaces</legend>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_serial" name="rpi_serial">
                            Enable Serial Port (UART)
                        </label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_i2c" name="rpi_i2c">
                            Enable I2C Interface
                        </label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_spi" name="rpi_spi">
                            Enable SPI Interface
                        </label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_onewire" name="rpi_onewire">
                            Enable 1-Wire Interface
                        </label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_rgpio" name="rpi_rgpio">
                            Enable Remote GPIO
                        </label>
                    </div>
                </fieldset>
                
                <fieldset class="network-fieldset">
                    <legend>Other Settings</legend>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="rpi_camera" name="rpi_camera">
                            Enable Camera
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="rpi_gpu_mem">GPU Memory (MB):</label>
                        <select id="rpi_gpu_mem" name="rpi_gpu_mem">
                            <option value="">Default</option>
                            <option value="16">16 MB</option>
                            <option value="32">32 MB</option>
                            <option value="64">64 MB</option>
                            <option value="128">128 MB</option>
                            <option value="256">256 MB</option>
                        </select>
                        <small>Memory allocated to GPU (lower = more RAM for system)</small>
                    </div>
                </fieldset>
            </section>

            <!-- Network Configuration Section -->
            <section class="form-section">
                <h2>Network Configuration</h2>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="enable_network" name="enable_network" checked>
                        Enable network configuration
                    </label>
                </div>

                <div id="network-config-section">
                    <!-- Ethernet Configuration -->
                    <fieldset class="network-fieldset">
                        <legend>Ethernet (eth0)</legend>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="eth_enabled" name="eth_enabled" checked>
                                Enable Ethernet
                            </label>
                        </div>
                        
                        <div id="eth-config">
                            <div class="form-group">
                                <label for="eth_interface">Interface Name:</label>
                                <input type="text" id="eth_interface" name="eth_interface" value="eth0">
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="eth_dhcp4" name="eth_dhcp4" checked>
                                    Use DHCP for IPv4
                                </label>
                            </div>
                            
                            <div id="eth-static-config" style="display: none;">
                                <div class="form-group">
                                    <label for="eth_ip">Static IP Address (CIDR):</label>
                                    <input type="text" id="eth_ip" name="eth_ip" placeholder="192.168.1.100/24">
                                </div>
                                
                                <div class="form-group">
                                    <label for="eth_gateway">Gateway:</label>
                                    <input type="text" id="eth_gateway" name="eth_gateway" placeholder="192.168.1.1">
                                </div>
                                
                                <div class="form-group">
                                    <label for="eth_dns">DNS Servers:</label>
                                    <input type="text" id="eth_dns" name="eth_dns" placeholder="8.8.8.8, 8.8.4.4">
                                    <small>Comma-separated list</small>
                                </div>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="eth_optional" name="eth_optional">
                                    Optional (don't wait for this interface)
                                </label>
                            </div>
                        </div>
                    </fieldset>

                    <!-- WiFi Configuration -->
                    <fieldset class="network-fieldset">
                        <legend>WiFi (wlan0)</legend>
                        
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="wifi_enabled" name="wifi_enabled">
                                Enable WiFi
                            </label>
                        </div>
                        
                        <div id="wifi-config" style="display: none;">
                            <div class="form-group">
                                <label for="wifi_interface">Interface Name:</label>
                                <input type="text" id="wifi_interface" name="wifi_interface" value="wlan0">
                            </div>

                            <div class="form-group">
                                <label for="wifi_country">WiFi Country Code:</label>
                                <select id="wifi_country" name="wifi_country">
                                    <option value="">-- Select Country --</option>
                                    <option value="US">US - United States</option>
                                    <option value="GB">GB - United Kingdom</option>
                                    <option value="DE">DE - Germany</option>
                                    <option value="FR">FR - France</option>
                                    <option value="JP">JP - Japan</option>
                                    <option value="CN">CN - China</option>
                                    <option value="IN">IN - India</option>
                                    <option value="AU">AU - Australia</option>
                                    <option value="CA">CA - Canada</option>
                                    <option value="LK" selected>LK - Sri Lanka</option>
                                    <option value="SG">SG - Singapore</option>
                                    <option value="NZ">NZ - New Zealand</option>
                                    <option value="BR">BR - Brazil</option>
                                    <option value="IT">IT - Italy</option>
                                    <option value="ES">ES - Spain</option>
                                </select>
                                <small>Required for WiFi regulatory compliance</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="wifi_ssid">WiFi SSID:</label>
                                <input type="text" id="wifi_ssid" name="wifi_ssid" placeholder="Your WiFi Network Name">
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="wifi_open_network" name="wifi_open_network">
                                    Open Network (no password)
                                </label>
                            </div>
                            
                            <div id="wifi-password-group" class="form-group">
                                <label for="wifi_password">WiFi Password:</label>
                                <input type="password" id="wifi_password" name="wifi_password" placeholder="WiFi Password">
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="wifi_dhcp4" name="wifi_dhcp4" checked>
                                    Use DHCP for IPv4
                                </label>
                            </div>
                            
                            <div id="wifi-static-config" style="display: none;">
                                <div class="form-group">
                                    <label for="wifi_ip">Static IP Address (CIDR):</label>
                                    <input type="text" id="wifi_ip" name="wifi_ip" placeholder="192.168.1.101/24">
                                </div>
                                
                                <div class="form-group">
                                    <label for="wifi_gateway">Gateway:</label>
                                    <input type="text" id="wifi_gateway" name="wifi_gateway" placeholder="192.168.1.1">
                                </div>
                                
                                <div class="form-group">
                                    <label for="wifi_dns">DNS Servers:</label>
                                    <input type="text" id="wifi_dns" name="wifi_dns" placeholder="8.8.8.8, 8.8.4.4">
                                    <small>Comma-separated list</small>
                                </div>
                            </div>
                            
                            <div class="form-group checkbox-group">
                                <label>
                                    <input type="checkbox" id="wifi_optional" name="wifi_optional" checked>
                                    Optional (don't wait for this interface)
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </section>

            <!-- Run Commands Section -->
            <section class="form-section">
                <h2>Run Commands</h2>
                <div class="form-group">
                    <label for="runcmd">Additional commands to run on first boot:</label>
                    <textarea id="runcmd" name="runcmd" rows="5" placeholder="echo 'Hello World'&#10;apt install -y neofetch"></textarea>
                    <small>One command per line.  These run at the end of cloud-init (SSH and network commands are added automatically)</small>
                </div>
            </section>

            <!-- Write Files Section -->
            <section class="form-section">
                <h2>Write Files</h2>
                <div id="write-files-container">
                    <!-- Dynamic file entries will be added here -->
                </div>
                <button type="button" id="add-file-btn" class="btn btn-secondary">+ Add File</button>
            </section>

            <!-- Final Message -->
            <section class="form-section">
                <h2>Final Message</h2>
                <div class="form-group">
                    <label for="final_message">Message to display after cloud-init completes:</label>
                    <textarea id="final_message" name="final_message" rows="2">Cloud-init completed!  System is ready after $UPTIME seconds.</textarea>
                </div>
            </section>

            <!-- Power State -->
            <section class="form-section">
                <h2>Power State</h2>
                <div class="form-group">
                    <label for="power_state">After configuration: </label>
                    <select id="power_state" name="power_state">
                        <option value="none">Do nothing</option>
                        <option value="reboot">Reboot</option>
                        <option value="poweroff">Power off</option>
                    </select>
                </div>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Download Cloud-Init Files</button>
                <button type="button" id="preview-btn" class="btn btn-secondary">Preview YAML</button>
            </div>
        </form>

        <!-- Preview Modal -->
        <div id="preview-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Preview Generated Files</h2>
                <div class="preview-tabs">
                    <button class="tab-btn active" data-tab="meta-data">meta-data</button>
                    <button class="tab-btn" data-tab="user-data">user-data</button>
                    <button class="tab-btn" data-tab="network-config">network-config</button>
                </div>
                <div id="preview-content">
                    <pre id="meta-data-preview" class="preview-pane active"></pre>
                    <pre id="user-data-preview" class="preview-pane"></pre>
                    <pre id="network-config-preview" class="preview-pane"></pre>
                </div>
            </div>
        </div>

        <footer>
            <p>Generated files should be placed on the boot partition of your Raspberry Pi SD card.</p>
            <p>Files:  <code>meta-data</code>, <code>user-data</code>, <code>network-config</code> → <code>/boot/firmware/</code></p>
            <p>Reference: <a href="https://cloudinit.readthedocs. io/en/latest/" target="_blank">cloud-init Documentation</a></p>
        </footer>
    </div>

    <script src="js/app.js"></script>
</body>
</html>