<div align="center">
  

  # 🐾 PetCareHub

  **A Full-Stack Pet Adoption & Veterinary Management System**

  [![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
  [![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)](https://jwt.io/)
</div>

---

## 📖 Overview

**PetCareHub** is a comprehensive, full-stack web application designed to bridge the gap between pet adoption and veterinary care. Built with **Laravel 11**, it provides a seamless experience for users looking to adopt pets, and a robust management dashboard for veterinarians and administrators.

The platform recently underwent a major architectural upgrade to include a **fully stateless REST API layer powered by JWT authentication**, making it ready for integration with modern mobile apps or decoupled JavaScript frontends (React/Vue).

## ✨ Key Features

### 🏢 Role-Based Access Control (RBAC)
- **Users**: Can browse pets, adopt, add pets to favorites, and book veterinary appointments.
- **Doctors/Vets**: Have dedicated dashboards to manage their appointments, write prescriptions, and initiate automated Zoom consultations.
- **Admins**: Complete oversight of the platform, including user management, pet moderation, and system analytics.

### 🩺 Veterinary Clinic Integration
- Book appointments with preferred doctors based on available slots.
- **Automated Zoom Meetings**: Instantly generates Zoom meeting links for telehealth consultations.
- Digital prescription generation and printing.

### 🔌 RESTful API Layer (v1)
- Complete CRUD operations exposed via `/api/v1/*` endpoints.
- **JWT (JSON Web Token)** based stateless authentication using `firebase/php-jwt`.
- Comprehensive API Resources for clean, predictable JSON responses.
- Structured Form Requests for strict input validation.

## 🗄️ Database Architecture

The system utilizes a highly normalized relational database schema to ensure data integrity and performance.

![Database Diagram](Diagram/Diagram.png)

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL (XAMPP/WAMP or Docker)

### Steps
1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/PetCareHub.git
   cd PetCareHub
   ```
2. **Install dependencies**
   ```bash
   composer install
   ```
3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Make sure to configure your `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in the `.env` file.*
4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```
5. **Start the Development Server**
   ```bash
   php artisan serve
   ```

## 🧪 Testing the API
A complete **Postman Collection** is included in the repository (`PetCareHub_API.postman_collection.json`). Simply import this file into Postman to instantly test all 28 available endpoints with automated token management.

---
<div align="center">
  <i>Developed as part of the CSE 3100 Web Programming Laboratory</i>
</div>
