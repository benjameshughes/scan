# 📦 Laravel Barcode Stock Sync

**Version: 1.0.6**  
A lightweight Laravel application for managing stock levels with barcode scanning and Linnworks synchronization.

---

## 🚀 Features

- **Barcode Scanning**: Use your phone's camera to scan barcodes and submit them to the database.
- **Stock Sync**: Automatically sync stock levels with Linnworks via a background job.
- **Modern Tech**: Built with the latest versions of PHP and Laravel for performance and security along with Livewire v3 for interactivity.

---

## 📂 Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/your-username/laravel-barcode-stock-sync.git
    cd laravel-barcode-stock-sync
    ```

2. Install dependencies:
    ```bash
    composer install
    npm install
    ```

3. Set up the environment:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Run migrations:
    ```bash
    php artisan migrate
    ```

5. Start the application:
    ```bash
    php artisan serve
    ```

---

## 🛠️ How It Works

1. Open the app on your browser or mobile device.
2. Use the barcode scanner to scan product barcodes.
3. Submit the data to the database.
4. A background job syncs stock levels with Linnworks automatically.

---

## 📜 Change Log

### Version 1.0.6
- Fixed versioning numbers. Still getting used to version control.

---

## 📄 License

This project is licensed under the **Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)**.  
You are free to:
- Share and adapt the code for personal or non-commercial use.

**Conditions**:
- You must give appropriate credit.
- You may not use the code for commercial purposes.

For more details, see the [LICENSE](LICENSE) file or visit the [Creative Commons website](https://creativecommons.org/licenses/by-nc/4.0/).

---

## 📧 Contact

For questions or feedback, feel free to reach out:
- **Email**: [ben@benjh.com](mailto:ben@benjh.com)
- **GitHub**: [benjameshughes](https://github.com/benjameshughes)
