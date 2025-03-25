# 📦 Laravel Barcode Stock Sync

**Version: 1.1.0**  
A lightweight Laravel application for managing stock levels with barcode scanning and Linnworks synchronization.

---

## 🚀 Features

- **Barcode Scanning**: Use your phone's camera to scan barcodes and submit them to the database.
- **Stock Sync**: Automatically sync stock levels with Linnworks via a background job.
- **Modern Tech**: Built with the latest versions of PHP and Laravel for performance and security along with Livewire v3 for interactivity.

---

## 🛠️ How It Works

1. Open the app on your browser or mobile device.
2. Use the barcode scanner to scan product barcodes.
3. Submit the data to the database.
4. A background job syncs stock levels with Linnworks automatically.

---

## 📜 Change Log

- Fixed the model product look up relationship
- Added flux copyable trait to scan views - WIP
- Removed orWhere Macro
- Ability to edit a scan - WIP
- Fixed product import and added auto mapping
- Added a feature to send an empty bay notification to admins (admins only)
- Added a simple permission system to set a user to either admin or user
- Updated the successful barcode scan message to show the product title of the scanned barcode
- Added validation for the barcode with a custom rule to check the barcode starts with the correct prefix

---

### To Do

- Update tables to allow for viewing/editing of resources

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
