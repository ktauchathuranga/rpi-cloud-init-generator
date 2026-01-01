# cloud-init Generator for Raspberry Pi OS

This project provides a simple web interface to generate `cloud-init` files (`meta-data`, `network-config`, `user-data`) for Raspberry Pi OS. It allows users to easily configure Wi-Fi, users, and other settings without using the official Raspberry Pi Imager customization option.  

## Features

- Generate all necessary `cloud-init` files for Raspberry Pi OS.
- Customize Wi-Fi network settings.
- Set up default users and passwords.
- Simple web interface built with PHP, JavaScript, and CSS.
- Can be run using Docker for easy deployment.

## Usage

### Using Docker

1. Build and run the Docker container:

```bash
docker-compose up -d --build
````

2. Open your browser and go to `http://localhost:8080` (or the port you configured in `docker-compose.yml`).
3. Fill in your configuration options and generate the `cloud-init` files.
4. Copy the generated files (`meta-data`, `network-config`, `user-data`) to the boot partition of your Raspberry Pi OS image.

### Manual Setup

1. Install a web server with PHP support (e.g., Nginx + PHP-FPM).
2. Place the `src` folder contents in your web root.
3. Configure the server to serve the files.
4. Access the interface through your browser.

## License

This project is open-source and available under the MIT License.

## Contributing

Contributions are welcome. Please open issues or pull requests for improvements or bug fixes.
