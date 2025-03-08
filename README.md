
# Mikrotik Hotspot Billing System

**Mikrotik Hotspot Billing System** is an open-source web application designed to automate and manage hotspot billing businesses using the MikroTik Router API. Built with **Laravel** (PHP framework) and **Tailwind CSS**, this system provides a comprehensive solution for managing customers, vouchers, routers, internet plans, and financial reports. It is ideal for businesses offering hotspot services and looking for a scalable, customizable, and user-friendly billing system.

---

## Features

The Mikrotik Hotspot Billing System comes with a wide range of features to streamline your hotspot management:

### 1. **Customer Management**
   - Add, edit, and manage customer details.
   - Track customer subscriptions and usage.
   - Automate customer onboarding and offboarding.

### 2. **Voucher Management**
   - Generate single or bulk vouchers for prepaid users.
   - Set voucher validity periods and usage limits.
   - Print vouchers directly from the system.

### 3. **Router Management**
   - Integrate with MikroTik routers via API.
   - Manage multiple routers from a single dashboard.
   - Monitor router status and performance.

### 4. **Internet Plans**
   - Create and manage customizable internet plans.
   - Assign plans to customers or vouchers.
   - Set pricing, duration, and data limits.

### 5. **Bandwidth Management**
   - Define and manage bandwidth profiles.
   - Assign bandwidth limits to customers and vouchers.
   - Monitor real-time bandwidth usage.

### 6. **Active User Sessions**
   - View and manage active user sessions.
   - Monitor session duration and data usage.
   - Terminate sessions remotely if needed.

### 7. **Finance Management**
   - Track payments and revenue.
   - Generate invoices and receipts.
   - View financial reports and analytics.

### 8. **Reports**
   - Generate detailed reports on customer usage, revenue, and more.
   - Export reports in PDF or CSV formats.
   - Schedule automated report generation.

### 9. **User-Friendly Interface**
   - Built with **Tailwind CSS** for a modern and responsive design.
   - Intuitive dashboard for easy navigation.
   - Customizable themes and layouts.

### 10. **Open Source**
   - Fully open-source, allowing developers to contribute and customize.
   - Regular updates and community-driven improvements.

---

## Installation

Follow these steps to set up the Mikrotik Hotspot Billing System on your server:

### Prerequisites
- PHP 8.0 or higher
- Composer
- MySQL or MariaDB
- MikroTik Router with API access
- Node.js and NPM (for frontend assets)

### Step 1: Clone the Repository
```bash
git clone https://github.com/mycosoft/Mikrotik-Hotspot-Billing-System.git
cd Mikrotik-Hotspot-Billing-System
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Configure Environment
Copy the `.env.example` file to `.env` and update the database and MikroTik API settings:
```bash
cp .env.example .env
```

Edit the `.env` file:
```env
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

MIKROTIK_HOST=your_mikrotik_ip
MIKROTIK_USER=your_mikrotik_user
MIKROTIK_PASS=your_mikrotik_password
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5: Run Migrations
```bash
php artisan migrate --seed
```

### Step 6: Compile Assets
```bash
npm run dev
```

### Step 7: Start the Application
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser to access the Mikrotik Hotspot Billing System dashboard.

---

## Contributing

We welcome contributions from the community! Whether you're a developer, designer, or tester, your help is appreciated.

### How to Contribute
1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Commit your changes and push to your branch.
4. Submit a pull request with a detailed description of your changes.

### Reporting Issues
If you encounter any issues, please open an issue on GitHub with the following details:
- A clear description of the problem.
- Steps to reproduce the issue.
- Screenshots or error logs (if applicable).

---

## License

The Mikrotik Hotspot Billing System is released under the **MIT License**. Feel free to use, modify, and distribute the software as per the license terms.

---

## Support

For support, questions, or feature requests, please:
- Open an issue on GitHub.
- Join our community forum (link to be added).

---

## Acknowledgments

We would like to thank the following for their contributions to the project:
- The **Laravel** community for an amazing framework.
- **Tailwind CSS** for making styling a breeze.
- All contributors who have helped improve the Mikrotik Hotspot Billing System.

---

## Screenshots

![Dashboard](link-to-dashboard-screenshot)  
*Dashboard Overview*

![Customer Management](link-to-customer-mgt-screenshot)  
*Customer Management Module*

![Voucher Management](link-to-voucher-mgt-screenshot)  
*Voucher Management Module*

---

## Roadmap

- [ ] Add multi-language support.
- [ ] Integrate payment gateways (PayPal, Stripe, etc.).
- [ ] Develop a mobile app for customer self-service.
- [ ] Add advanced analytics and reporting features.

---

Thank you for choosing the Mikrotik Hotspot Billing System! We look forward to your contributions and feedback to make this project even better. Happy coding! 🚀

---

This `README.md` provides a comprehensive overview of the project, its features, installation steps, and contribution guidelines. You can customize it further based on your specific needs or additional features in the repository. Let me know if you need further assistance!
