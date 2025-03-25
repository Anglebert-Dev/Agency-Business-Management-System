# Agency Business Management System

## Overview

This is a comprehensive Laravel-based agency business management system designed to handle financial transactions, customer management, and float account tracking. The application provides a robust solution for agencies to manage their financial operations efficiently.

## Features

### 1. Transaction Management
- Create multiple types of transactions:
  - Deposits
  - Withdrawals
  - Transfers
- Detailed transaction tracking
- Support for multiple transaction sources
- Handling of counterpart transactions
- Automatic fee calculation
- Transaction status tracking

### 2. Customer Management
- Customer creation and management
- Unique customer identification
- Support for multiple contact methods (phone, email)
- Transaction history per customer

### 3. Float Account Management
- Multiple float account tracking
- Bank-linked accounts
- Balance management
- Transfer capabilities between accounts

### 4. Bank Management
- Bank information storage
- Status tracking
- Logo and short name support

## Technical Architecture

### Models
- `Transaction`: Core transaction handling
- `TransactionDetail`: Detailed transaction tracking
- `Customer`: Customer information management
- `Bank`: Bank-related information
- `FloatAccount`: Account balance and tracking

### Key Functionalities
- Automatic UUID generation
- Soft delete capabilities
- Complex transaction processing
- Balance validation
- Flexible source and counterpart handling

## System Requirements

- PHP 8.1+
- Laravel 10.x
- Composer
- MySQL 5.7+ or PostgreSQL
- Node.js (for frontend assets)

## Installation Steps

1. Clone the repository
```bash
git clone https://github.com/Anglebert-Dev/Agency-Business-Management-System.git
cd agency-business-app
```

2. Install PHP dependencies
```bash
composer install
```

3. Copy environment file
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure database in `.env`

6. Run migrations
```bash
php artisan migrate
```

7. Install frontend dependencies
```bash
npm install
npm run dev
```

## API Endpoints

### Transactions
- `GET /api/transactions`: List all transactions
- `POST /api/transactions`: Create new transaction
- `GET /api/transactions/{id}`: Get transaction details

### Customers
- `GET /api/customers`: List all customers
- `POST /api/customers`: Create new customer
- `GET /api/customers/{id}`: Get customer details

### Banks
- `GET /api/banks`: List all banks
- `POST /api/banks`: Create new bank
- `GET /api/banks/{id}`: Get bank details

### Float Accounts
- `GET /api/float-accounts`: List all float accounts
- `POST /api/float-accounts`: Create new float account
- `GET /api/float-accounts/{id}`: Get float account details

## Security Features
- User authentication (built-in Laravel authentication)
- Role-based access control ready
- Soft delete for data preservation
- Secure transaction processing with database transactions

## Performance Considerations
- Eager loading for related models
- Efficient database queries
- Caching ready architecture

## Error Handling
- Comprehensive validation
- Detailed error responses
- Transactional rollback for failed operations

## Scalability
- Modular design
- API-driven architecture
- Ready for microservice integration

## Contribution Guidelines
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Future Roadmap
- [ ] Add comprehensive reporting
- [ ] Implement advanced analytics
- [ ] Create dashboard visualizations
- [ ] Add more complex fee structures
- [ ] Develop mobile app integration

## License
Distributed under the MIT License. See `LICENSE` for more information.

## Contact
Anglebert - anglebertsh@gmail.com
