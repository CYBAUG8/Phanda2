# Phanda - Service Booking Platform

Phanda is a comprehensive service booking platform developed with Laravel and Tailwind CSS. It connects service providers with users, offering a seamless experience for booking, scheduling, and managing services.

## 🚀 Features

### User Portal
- **Dashboard**: Overview of current activity and upcoming bookings.
- **Service Discovery**: Browse and search for available services.
- **Booking Management**: Book services, view history, and cancel appointments.
- **Real-time Messaging**: Chat directly with service providers.
- **Reviews**: Leave ratings and reviews for services.
- **Profile Management**: Manage personal details, locations, and security settings.
- **Safety**: Emergency and recovery contact management.

### Provider Portal
- **Dashboard**: Monitor business performance and upcoming tasks.
- **Service Management**: List and update offered services.
- **Schedule**: Manage availability and bookings.
- **Earnings**: Track revenue and financial performance.
- **Client Communication**: Built-in messaging system.

## 🛠 Tech Stack

- **Framework**: [Laravel 12.x](https://laravel.com)
- **Frontend**: [Blade Templates](https://laravel.com/docs/blade) & [React](https://reactjs.org/)
- **Styling**: [Tailwind CSS v4](https://tailwindcss.com)
- **Build Tool**: [Vite](https://vitejs.dev)
- **Database**: SQLite / MySQL support

## 🔧 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/phanda.git
   cd phanda
   ```

2. **Install Backend Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   Copy the example environment file and configure your database settings.
   ```bash
   cp .env.example .env
   ```
   Open `.env` and update your database credentials (DB_DATABASE, DB_USERNAME, etc.).

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations & Seeders**
   Set up the database tables and populate them with initial data.
   ```bash
   php artisan migrate --seed
   ```

7. **Start Development Servers**
   You need to run both the Laravel backend and the Vite frontend server.
   
   **Terminal 1 (Laravel):**
   ```bash
   php artisan serve
   ```
   
   **Terminal 2 (Vite):**
   ```bash
   npm run dev
   ```

Access the application at `http://localhost:8000`.

## 📂 Project Structure

- `app/Http/Controllers` - Backend logic (Users, Providers, Bookings, etc.)
- `resources/views` - Blade templates for User and Provider interfaces.
- `routes/web.php` - Application routes.
- `database/migrations` - Database structure.

## 🤝 Contributing

Contributions are welcome! Please fork the repository and create a pull request with your features or fixes.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
