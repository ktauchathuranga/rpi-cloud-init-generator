<?php
/**
 * Cloud-Init Generator for Raspberry Pi
 * Generates meta-data, user-data, and network-config files
 */

// Prevent direct access without POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Check if this is a preview request (AJAX)
$isPreview = isset($_GET['preview']) && $_GET['preview'] === 'true';

/**
 * Escape YAML string value
 */
function escapeYamlString($value) {
    if (preg_match('/[:#\[\]{}|>&*! ? %@`]/', $value) || 
        preg_match('/^\s/', $value) || 
        preg_match('/\s$/', $value) ||
        preg_match('/^(true|false|yes|no|null|~)$/i', $value)) {
        return '"' . addslashes($value) . '"';
    }
    return $value;
}

/**
 * Generate meta-data
 */
function generateMetaData($data) {
    $instanceId = $data['instance_id'] ?? 'raspberry-pi-001';
    $localHostname = $data['local_hostname'] ?? 'raspberrypi';
    
    $metaData = "instance-id: " . escapeYamlString($instanceId) . "\n";
    $metaData .= "local-hostname: " . escapeYamlString($localHostname) . "\n";
    
    return $metaData;
}

/**
 * Generate user-data
 */
function generateUserData($data) {
    $yaml = "#cloud-config\n\n";
    
    // Hostname
    if (! empty($data['local_hostname'])) {
        $yaml .= "hostname: " .  escapeYamlString($data['local_hostname']) . "\n\n";
    }
    
    // Users configuration
    $yaml .= "users:\n";
    $yaml .= "  - name: " . escapeYamlString($data['username'] ?? 'pi') . "\n";
    $yaml .= "    groups:  [adm, dialout, cdrom, sudo, audio, video, plugdev, games, users, input, netdev, spi, i2c, gpio]\n";
    $yaml .= "    shell: /bin/bash\n";
    
    // Sudo configuration
    if (isset($data['sudo_nopasswd']) && $data['sudo_nopasswd']) {
        $yaml .= "    sudo: ALL=(ALL) NOPASSWD:ALL\n";
    } else {
        $yaml .= "    sudo: ALL=(ALL: ALL) ALL\n";
    }
    
    // Lock password if requested
    if (isset($data['lock_passwd']) && $data['lock_passwd']) {
        $yaml .= "    lock_passwd: true\n";
    } else {
        $yaml .= "    lock_passwd: false\n";
    }
    
    // SSH authorized keys
    if (! empty($data['ssh_authorized_keys'])) {
        $keys = array_filter(explode("\n", trim($data['ssh_authorized_keys'])));
        if (!empty($keys)) {
            $yaml .= "    ssh_authorized_keys:\n";
            foreach ($keys as $key) {
                $key = trim($key);
                if (! empty($key)) {
                    $yaml .= "      - " . escapeYamlString($key) . "\n";
                }
            }
        }
    }
    
    $yaml .= "\n";
    
    // Password configuration
    if (! empty($data['password'])) {
        $yaml .= "chpasswd:\n";
        $yaml .= "  expire: false\n";
        $yaml .= "  users:\n";
        $yaml .= "    - name: " .  escapeYamlString($data['username'] ?? 'pi') . "\n";
        $yaml .= "      password: " . escapeYamlString($data['password']) . "\n";
        $yaml .= "      type: text\n";
        $yaml .= "\n";
    }
    
    // SSH password authentication
    if (isset($data['ssh_pwauth']) && $data['ssh_pwauth']) {
        $yaml .= "ssh_pwauth: true\n\n";
    } else {
        $yaml .= "ssh_pwauth: false\n\n";
    }
    
    // Timezone
    if (!empty($data['timezone'])) {
        $yaml .= "timezone: " . escapeYamlString($data['timezone']) . "\n\n";
    }
    
    // Locale
    if (! empty($data['locale'])) {
        $yaml .= "locale: " . escapeYamlString($data['locale']) . "\n\n";
    }
    
    // Keyboard
    if (!empty($data['keyboard_layout'])) {
        $yaml .= "keyboard:\n";
        $yaml .= "  layout: " . escapeYamlString($data['keyboard_layout']) . "\n\n";
    }
    
    // Package management
    if (isset($data['package_update']) && $data['package_update']) {
        $yaml .= "package_update: true\n";
    }
    
    if (isset($data['package_upgrade']) && $data['package_upgrade']) {
        $yaml .= "package_upgrade: true\n";
    }
    
    // Additional packages
    if (!empty($data['packages'])) {
        $packages = array_filter(explode("\n", trim($data['packages'])));
        if (!empty($packages)) {
            $yaml .= "\npackages:\n";
            foreach ($packages as $package) {
                $package = trim($package);
                if (!empty($package)) {
                    $yaml .= "  - " . escapeYamlString($package) . "\n";
                }
            }
        }
    }
    
    $yaml .= "\n";
    
    // Write files
    if (!empty($data['write_files'])) {
        $files = json_decode($data['write_files'], true);
        if (!empty($files)) {
            $yaml .= "write_files:\n";
            foreach ($files as $file) {
                if (! empty($file['path']) && !empty($file['content'])) {
                    $yaml .= "  - path: " . escapeYamlString($file['path']) . "\n";
                    if (!empty($file['permissions'])) {
                        $yaml .= "    permissions: \"" . $file['permissions'] . "\"\n";
                    }
                    if (!empty($file['owner'])) {
                        $yaml .= "    owner: " . escapeYamlString($file['owner']) . "\n";
                    }
                    $yaml .= "    content: |\n";
                    $contentLines = explode("\n", $file['content']);
                    foreach ($contentLines as $line) {
                        $yaml .= "      " . $line . "\n";
                    }
                }
            }
            $yaml .= "\n";
        }
    }
    
    // Run commands - Add network activation commands for WiFi
    $runcmdItems = [];
    
    // Check if WiFi is enabled and add necessary commands
    $hasWifi = isset($data['wifi_enabled']) && $data['wifi_enabled'];
    if ($hasWifi) {
        // These commands help ensure WiFi comes up properly
        $runcmdItems[] = "rfkill unblock wifi";
        $runcmdItems[] = "netplan apply";
    }
    
    // Add user-defined commands
    if (!empty($data['runcmd'])) {
        $userCommands = array_filter(explode("\n", trim($data['runcmd'])));
        $runcmdItems = array_merge($runcmdItems, $userCommands);
    }
    
    if (!empty($runcmdItems)) {
        $yaml .= "runcmd:\n";
        foreach ($runcmdItems as $cmd) {
            $cmd = trim($cmd);
            if (!empty($cmd)) {
                $yaml .= "  - " . escapeYamlString($cmd) . "\n";
            }
        }
        $yaml .= "\n";
    }
    
    // Final message
    if (!empty($data['final_message'])) {
        $yaml .= "final_message: " . escapeYamlString($data['final_message']) . "\n\n";
    }
    
    // Power state
    if (! empty($data['power_state']) && $data['power_state'] !== 'none') {
        $yaml .= "power_state:\n";
        $yaml .= "  mode: " . $data['power_state'] . "\n";
        $yaml .= "  message: \"System is " . ($data['power_state'] === 'reboot' ? 'rebooting' : 'shutting down') . " after cloud-init\"\n";
        $yaml .= "  timeout: 30\n";
        $yaml .= "  condition: true\n";
    }
    
    return $yaml;
}

/**
 * Generate network-config (Netplan v2 format)
 */
function generateNetworkConfig($data) {
    // Check if network configuration is enabled
    if (!isset($data['enable_network']) || !$data['enable_network']) {
        return "# Network configuration disabled\n";
    }
    
    $yaml = "# Network configuration (Netplan v2 format)\n";
    $yaml .= "# Reference: https://cloudinit.readthedocs. io/en/latest/reference/network-config-format-v2.html\n\n";
    $yaml .= "network:\n";
    $yaml .= "  version: 2\n";
    $yaml .= "  renderer: networkd\n";
    
    $hasEthernet = isset($data['eth_enabled']) && $data['eth_enabled'];
    $hasWifi = isset($data['wifi_enabled']) && $data['wifi_enabled'];
    
    // Ethernet configuration
    if ($hasEthernet) {
        $ethInterface = $data['eth_interface'] ?? 'eth0';
        $yaml .= "  ethernets:\n";
        $yaml .= "    " . $ethInterface . ":\n";
        
        if (isset($data['eth_dhcp4']) && $data['eth_dhcp4']) {
            $yaml .= "      dhcp4: true\n";
        } else {
            $yaml .= "      dhcp4: false\n";
            
            if (! empty($data['eth_ip'])) {
                $yaml .= "      addresses:\n";
                $yaml .= "        - " . $data['eth_ip'] . "\n";
            }
            
            if (!empty($data['eth_gateway'])) {
                $yaml .= "      routes:\n";
                $yaml .= "        - to: default\n";
                $yaml .= "          via: " . $data['eth_gateway'] . "\n";
            }
            
            if (!empty($data['eth_dns'])) {
                $dnsServers = array_map('trim', explode(',', $data['eth_dns']));
                $yaml .= "      nameservers:\n";
                $yaml .= "        addresses:\n";
                foreach ($dnsServers as $dns) {
                    if (!empty($dns)) {
                        $yaml .= "          - " . $dns . "\n";
                    }
                }
            }
        }
        
        if (isset($data['eth_optional']) && $data['eth_optional']) {
            $yaml .= "      optional:  true\n";
        }
    }
    
    // WiFi configuration
    if ($hasWifi) {
        $wifiInterface = $data['wifi_interface'] ?? 'wlan0';
        $yaml .= "  wifis:\n";
        $yaml .= "    " . $wifiInterface . ":\n";
        
        if (isset($data['wifi_dhcp4']) && $data['wifi_dhcp4']) {
            $yaml .= "      dhcp4: true\n";
        } else {
            $yaml .= "      dhcp4: false\n";
            
            if (!empty($data['wifi_ip'])) {
                $yaml .= "      addresses:\n";
                $yaml .= "        - " . $data['wifi_ip'] . "\n";
            }
            
            if (!empty($data['wifi_gateway'])) {
                $yaml .= "      routes:\n";
                $yaml .= "        - to: default\n";
                $yaml .= "          via: " . $data['wifi_gateway'] . "\n";
            }
            
            if (! empty($data['wifi_dns'])) {
                $dnsServers = array_map('trim', explode(',', $data['wifi_dns']));
                $yaml .= "      nameservers:\n";
                $yaml .= "        addresses:\n";
                foreach ($dnsServers as $dns) {
                    if (! empty($dns)) {
                        $yaml .= "          - " . $dns . "\n";
                    }
                }
            }
        }
        
        if (isset($data['wifi_optional']) && $data['wifi_optional']) {
            $yaml .= "      optional: true\n";
        }
        
        // Regulatory domain for WiFi (important for some countries/networks)
        if (!empty($data['wifi_country'])) {
            $yaml .= "      regulatory-domain: " . strtoupper($data['wifi_country']) . "\n";
        }
        
        // Access points
        if (!empty($data['wifi_ssid'])) {
            $yaml .= "      access-points:\n";
            $ssid = $data['wifi_ssid'];
            $yaml .= "        \"" . addslashes($ssid) . "\":\n";
            
            $isOpenNetwork = isset($data['wifi_open_network']) && $data['wifi_open_network'];
            
            if ($isOpenNetwork) {
                // Open network - no authentication needed
                // Some versions need explicit empty, some need nothing
                $yaml .= "          auth:\n";
                $yaml .= "            key-management: none\n";
            } elseif (! empty($data['wifi_password'])) {
                // WPA/WPA2 password protected network
                $yaml .= "          password: \"" . addslashes($data['wifi_password']) . "\"\n";
            }
        }
    }
    
    return $yaml;
}

// Process the form data
$metaData = generateMetaData($_POST);
$userData = generateUserData($_POST);
$networkConfig = generateNetworkConfig($_POST);

// If preview mode, return JSON
if ($isPreview) {
    header('Content-Type: application/json');
    echo json_encode([
        'meta-data' => $metaData,
        'user-data' => $userData,
        'network-config' => $networkConfig
    ]);
    exit;
}

// Create ZIP file for download
$zipFile = tempnam(sys_get_temp_dir(), 'cloudinit_') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $zip->addFromString('meta-data', $metaData);
    $zip->addFromString('user-data', $userData);
    $zip->addFromString('network-config', $networkConfig);
    $zip->close();
    
    $hostname = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['local_hostname'] ?? 'raspberrypi');
    $filename = 'cloud-init-' . $hostname . '-' . date('Y-m-d') . '.zip';
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' .  filesize($zipFile));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    readfile($zipFile);
    unlink($zipFile);
    exit;
} else {
    header('Content-Type: text/plain');
    echo "=== meta-data ===\n";
    echo $metaData;
    echo "\n\n=== user-data ===\n";
    echo $userData;
    echo "\n\n=== network-config ===\n";
    echo $networkConfig;
}
?>