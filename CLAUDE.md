# CLAUDE.md - AI Assistant Guide

This document provides a comprehensive guide for AI assistants (like Claude) working with this Symfony 7.1 codebase. It explains the project structure, development workflows, architectural patterns, and key conventions to follow.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Architecture & Design Patterns](#architecture--design-patterns)
5. [Database Schema](#database-schema)
6. [Security & Authentication](#security--authentication)
7. [Frontend Architecture](#frontend-architecture)
8. [Development Workflows](#development-workflows)
9. [Testing Strategy](#testing-strategy)
10. [Key Conventions](#key-conventions)
11. [Common Tasks](#common-tasks)
12. [Important Files Reference](#important-files-reference)

---

## Project Overview

This is a **Symfony 7.1** educational/boilerplate project demonstrating modern Symfony best practices. The application features:

- **User Management**: Registration with email verification, authentication with form login
- **Task Management**: CRUD operations with ownership-based authorization
- **Multi-Step Forms**: Job application workflow using CraueFormFlowBundle
- **Modern Frontend**: Asset Mapper (no webpack), Stimulus.js, Turbo, Tailwind CSS
- **Async Processing**: Doctrine Messenger for email sending

**Language**: French (UI, comments, templates)
**PHP Version**: >= 8.2
**Database**: PostgreSQL (development uses Docker Compose)

---

## Technology Stack

### Backend

| Component | Version | Purpose |
|-----------|---------|---------|
| **Symfony Framework** | 7.1.* | Core PHP framework |
| **PHP** | >= 8.2 | Programming language |
| **Doctrine ORM** | 3.2 | Database ORM with attribute mapping |
| **Doctrine Migrations** | 3.3 | Database versioning |
| **PostgreSQL** | 16-alpine | Primary database |
| **Symfony Messenger** | 7.1.* | Async job processing |

### Third-Party Bundles

| Bundle | Version | Purpose |
|--------|---------|---------|
| **craue/formflow-bundle** | 3.7 | Multi-step form orchestration |
| **symfonycasts/verify-email-bundle** | 1.18 | Email verification workflow |
| **symfonycasts/tailwind-bundle** | 0.12.0 | Tailwind CSS integration |
| **symfony/ux-stimulus** | 2.19 | Stimulus.js integration |
| **symfony/ux-turbo** | 2.19 | Turbo.js for fast navigation |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Stimulus.js** | 3.2.2 | JavaScript framework (lightweight) |
| **Turbo** | 7.3.0 | Fast navigation without full page reloads |
| **Tailwind CSS** | 4.1.11 | Utility-first CSS framework |
| **Asset Mapper** | 7.1.* | Import maps (no Node.js bundler) |

### Development Tools

- **PHPUnit**: 9.5 (testing framework)
- **Docker Compose**: PostgreSQL + Mailpit for local development
- **Symfony Maker**: Code generation (entities, controllers, forms)
- **Doctrine Fixtures**: Test data seeding

---

## Directory Structure

```
symfony_base_MAKOGON_Dimitri/
├── assets/                      # Frontend assets (JS, CSS)
│   ├── app.js                   # Main JavaScript entry point
│   ├── bootstrap.js             # Stimulus initialization
│   ├── controllers/             # Stimulus controllers
│   │   ├── hello_controller.js
│   │   └── controllers.json
│   └── styles/
│       └── app.css              # Tailwind + custom CSS
│
├── bin/
│   └── console                  # Symfony console CLI
│
├── config/                      # All configuration files
│   ├── packages/                # Bundle configurations
│   │   ├── framework.yaml
│   │   ├── security.yaml        # IMPORTANT: Auth/authorization config
│   │   ├── doctrine.yaml        # Database config
│   │   ├── messenger.yaml       # Async job config
│   │   └── ...
│   ├── routes.yaml              # Route configuration
│   ├── services.yaml            # IMPORTANT: Service container config
│   └── bundles.php              # Enabled bundles
│
├── migrations/                  # Database migration files
│   └── Version20251210084411.php
│
├── public/
│   └── index.php                # Front controller (entry point)
│
├── src/                         # Application source code
│   ├── Controller/              # HTTP request handlers
│   │   ├── HomeController.php
│   │   ├── SecurityController.php
│   │   ├── TaskController.php
│   │   ├── CandidatureController.php
│   │   └── RegistrationController.php
│   ├── Entity/                  # Doctrine ORM entities
│   │   ├── User.php
│   │   ├── Task.php
│   │   └── Candidate.php
│   ├── Repository/              # Data access layer
│   │   ├── UserRepository.php
│   │   ├── TaskRepository.php
│   │   └── CandidateRepository.php
│   ├── Form/                    # Form types
│   │   ├── TaskType.php
│   │   ├── RegistrationFormType.php
│   │   ├── CandidatureType.php
│   │   └── Flow/
│   │       └── CandidateApplicationFlow.php
│   ├── Security/
│   │   ├── EmailVerifier.php   # Email verification service
│   │   └── Voter/
│   │       └── TaskVoter.php   # IMPORTANT: Custom authorization
│   ├── DataFixtures/
│   │   └── AppFixtures.php     # Test user seeding
│   └── Kernel.php               # Application kernel
│
├── templates/                   # Twig templates
│   ├── base.html.twig           # IMPORTANT: Base layout
│   ├── home/
│   ├── security/
│   ├── registration/
│   ├── task/
│   └── candidature/
│
├── tests/                       # PHPUnit tests
│   ├── LoginControllerTest.php
│   └── CandidatureControllerTest.php
│
├── translations/                # i18n files (if needed)
├── var/                         # Generated files (cache, logs)
├── .env                         # Environment variables (tracked)
├── .env.local                   # Local overrides (NOT tracked)
├── .env.test                    # Test environment
├── compose.yaml                 # Docker Compose configuration
├── composer.json                # PHP dependencies
├── importmap.php                # JavaScript import mappings
└── phpunit.xml.dist             # PHPUnit configuration
```

---

## Architecture & Design Patterns

### 1. **MVC Pattern** (Model-View-Controller)

- **Models**: Doctrine entities in `src/Entity/`
- **Views**: Twig templates in `templates/`
- **Controllers**: HTTP handlers in `src/Controller/`

### 2. **Repository Pattern**

All entities have corresponding repositories in `src/Repository/` for data access:

```php
// Example: TaskRepository
$tasks = $taskRepository->findBy(['author' => $user]);
```

### 3. **Voter Pattern** (Authorization)

Custom authorization logic using Symfony Voters:

**TaskVoter** (`src/Security/Voter/TaskVoter.php`):
- Attributes: `VIEW`, `EDIT`, `DELETE`
- Logic:
  - Admins have full access
  - Users can VIEW/EDIT their own tasks only
  - DELETE always returns false (deletion blocked)

Usage in controllers:
```php
#[IsGranted('EDIT', subject: 'task')]
public function edit(Task $task): Response
```

### 4. **Service Pattern**

Services are autowired and auto-configured:

**EmailVerifier** (`src/Security/EmailVerifier.php`):
- Sends verification emails with signed URLs
- Validates email verification tokens
- Marks users as verified

### 5. **Multi-Step Form Flow**

**CandidateApplicationFlow** implements a 5-step job application:

```
Step 1: Personal Info → Step 2: Experience (conditional) →
Step 3: Availability → Step 4: RGPD Consent → Step 5: Confirmation
```

Key features:
- Session-based state management
- Conditional step skipping (experience step skipped if `hasExperience=false`)
- Validation groups per step
- Progress bar in UI

### 6. **Lifecycle Callbacks**

Entities use Doctrine lifecycle callbacks for automatic timestamp management:

```php
#[ORM\PrePersist]
public function setCreatedAtValue(): void
{
    $this->createdAt = new \DateTimeImmutable();
}
```

### 7. **Form Types**

Separate form classes for each entity:
- `TaskType`: name, description
- `RegistrationFormType`: email, plainPassword, agreeTerms
- `CandidatureType`: Multi-step form with conditional fields

---

## Database Schema

### Tables

#### **app_user**
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| email | VARCHAR(180) | UNIQUE, NOT NULL |
| roles | JSON | NOT NULL |
| password | VARCHAR(255) | NOT NULL |
| is_verified | BOOLEAN | NOT NULL, DEFAULT false |

#### **task**
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| author_id | INT | FOREIGN KEY → user.id, NOT NULL |
| name | VARCHAR(255) | NOT NULL |
| description | TEXT | NULLABLE |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |

**Relationship**: `task.author_id` → `user.id` (ManyToOne, non-nullable)

#### **candidate**
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| first_name | VARCHAR(255) | NOT NULL |
| last_name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | NOT NULL |
| phone | VARCHAR(20) | NOT NULL |
| has_experience | BOOLEAN | NOT NULL |
| experience_details | TEXT | NULLABLE |
| availability_date | DATE | NULLABLE |
| is_immediately_available | BOOLEAN | NOT NULL |
| status | VARCHAR(50) | NOT NULL |
| created_at | TIMESTAMP | NOT NULL |
| updated_at | TIMESTAMP | NOT NULL |
| consent_rgpd | BOOLEAN | NOT NULL |

**No foreign keys** (standalone entity)

#### **messenger_messages**
System table for async job queue (emails, notifications)

### Relationships

```
User (1) ──< (∞) Task
    └─── author_id FK

Candidate (standalone, no relationships)
```

---

## Security & Authentication

### Authentication Flow

1. **Registration** (`/register`):
   - User submits email + password
   - Password is hashed (bcrypt in production, argon2i in test)
   - Verification email sent with signed URL
   - User auto-logged in after registration

2. **Email Verification** (`/verify/email`):
   - User clicks link in email
   - Token validated (expiration checked)
   - `isVerified` flag set to true

3. **Login** (`/login`):
   - Form login with CSRF protection
   - Email used as username property
   - Failed attempts return generic error (no user enumeration)

4. **Logout** (`/logout`):
   - Intercepted by security firewall
   - Session destroyed

### Authorization

**Firewall Configuration** (`config/packages/security.yaml`):
- Form login with CSRF token
- User provider: Doctrine entity (email property)
- Password hasher: auto (bcrypt/argon2i)

**Access Control**:
- Most routes require `ROLE_USER` (automatic for all users)
- Task operations use custom `TaskVoter` for fine-grained control
- Admin users have full access (role hierarchy)

**Password Requirements**:
- Minimum length: 6 characters (see `RegistrationFormType`)
- Hashing algorithm: auto (bcrypt in prod, argon2i in test)

**Security Best Practices Implemented**:
- ✅ CSRF protection on all forms
- ✅ Password hashing with modern algorithms
- ✅ Email verification before full account access
- ✅ No user enumeration (generic login errors)
- ✅ Signed URLs for email verification (prevent tampering)
- ✅ Custom voters for resource-level authorization

---

## Frontend Architecture

### Asset Pipeline (No Bundler!)

This project uses **Asset Mapper** instead of Webpack/Vite:

- No Node.js required for development
- JavaScript modules imported via import maps (`importmap.php`)
- CSS compiled via Tailwind CSS binary

**Entry Points**:
- JavaScript: `assets/app.js`
- CSS: `assets/styles/app.css`

### Stimulus.js Controllers

Controllers are auto-registered from `assets/controllers/`:

```javascript
// Example: assets/controllers/hello_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.textContent = 'Hello Stimulus!';
    }
}
```

**Usage in templates**:
```twig
<div data-controller="hello"></div>
```

### Turbo Integration

- Turbo Drive: Fast navigation without full page reloads
- Turbo Frames: Partial page updates
- Turbo Streams: Real-time updates (if needed)

### Tailwind CSS

**Custom Theme** (Valorant-inspired):
- Dark blue background (`#0f1923`)
- Neon cyan and red accents
- Rajdhani font family
- Custom button styling with clip-path polygon effects

**Configuration**: `config/packages/symfonycasts_tailwind.yaml`

**Build Commands**:
```bash
# Development (watch mode)
php bin/console tailwind:build --watch

# Production (minified)
php bin/console tailwind:build --minify
```

### Base Template

All templates extend `templates/base.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% block title %}My Page{% endblock %}

{% block body %}
    {# Your content here #}
{% endblock %}
```

**Key features**:
- Navbar with auth status (logged in/out)
- User info display (`app.user.userIdentifier`)
- Link to tasks, login, register, logout

---

## Development Workflows

### Initial Setup

**Option 1: IDX (Cloud Development)**:
```bash
# Project auto-starts PostgreSQL and Symfony server
composer install
# Database is pre-configured (MySQL in IDX)
```

**Option 2: Local Development**:
```bash
# Prerequisites: PHP 8.2+, Composer, Docker
composer install

# Start Docker services (PostgreSQL + Mailpit)
docker compose up -d

# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (optional)
php bin/console doctrine:fixtures:load

# Start Symfony server
symfony server:start
# OR
php -S localhost:8000 -t public/
```

### Daily Development Workflow

1. **Start services**:
   ```bash
   docker compose up -d
   symfony server:start
   ```

2. **Watch Tailwind CSS** (if modifying styles):
   ```bash
   php bin/console tailwind:build --watch
   ```

3. **Clear cache** (if config changes):
   ```bash
   php bin/console cache:clear
   ```

### Database Workflow

**Creating a new entity**:
```bash
php bin/console make:entity EntityName
# Follow prompts to add fields

# Generate migration
php bin/console make:migration

# Review migration in migrations/VersionXXX.php
# Apply migration
php bin/console doctrine:migrations:migrate
```

**Modifying existing entity**:
```bash
php bin/console make:entity EntityName
# Add/modify fields

php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

**Reset database** (development only):
```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Code Generation

**Controller**:
```bash
php bin/console make:controller MyController
```

**Form Type**:
```bash
php bin/console make:form MyFormType
# Optional: Bind to entity class
```

**CRUD**:
```bash
php bin/console make:crud EntityName
# Generates: Controller, templates, form type
```

**Voter**:
```bash
php bin/console make:voter MyVoter
```

**Repository method**:
```bash
php bin/console make:entity EntityName
# Select option to add repository method
```

### Testing Workflow

**Run all tests**:
```bash
php bin/phpunit
```

**Run specific test**:
```bash
php bin/phpunit tests/LoginControllerTest.php
```

**Test with coverage** (requires Xdebug):
```bash
php bin/phpunit --coverage-html var/coverage
```

**Writing tests**:
- Extend `Symfony\Bundle\FrameworkBundle\Test\WebTestCase`
- Use `static::createClient()` for HTTP testing
- Use transactions for database tests (see `CandidatureControllerTest`)

---

## Testing Strategy

### Test Files

**LoginControllerTest** (`tests/LoginControllerTest.php`):
- Tests authentication flow
- Validates no user enumeration (same error for invalid email/password)
- Checks successful login redirects to home

**CandidatureControllerTest** (`tests/CandidatureControllerTest.php`):
- Tests complete 5-step form submission
- Uses database transactions for cleanup
- Validates progress bar rendering
- Checks final database persistence

### Testing Conventions

**Setup**:
```php
protected function setUp(): void
{
    parent::setUp();

    // Start transaction for rollback
    $this->entityManager->beginTransaction();
}
```

**Teardown**:
```php
protected function tearDown(): void
{
    // Rollback transaction (no cleanup needed)
    $this->entityManager->rollback();
    parent::tearDown();
}
```

**Creating test users**:
```php
$user = new User();
$user->setEmail('test@example.com');
$user->setPassword(
    $this->passwordHasher->hashPassword($user, 'password')
);
$this->entityManager->persist($user);
$this->entityManager->flush();
```

**Making requests**:
```php
$client = static::createClient();
$client->request('GET', '/login');
$this->assertResponseIsSuccessful();
```

**Submitting forms**:
```php
$client->submitForm('Submit', [
    'form_field_name' => 'value',
]);
```

**Test Database Configuration** (`.env.test`):
- Uses separate test database
- Faster password hashing (argon2i with lower cost)
- No email sending (mailer DSN: null://null)

---

## Key Conventions

### Coding Standards

**Naming Conventions**:
- **Entities**: PascalCase, singular (e.g., `User`, `Task`, `Candidate`)
- **Controllers**: PascalCase + `Controller` suffix (e.g., `TaskController`)
- **Services**: PascalCase (e.g., `EmailVerifier`)
- **Form Types**: PascalCase + `Type` suffix (e.g., `TaskType`)
- **Repositories**: PascalCase + `Repository` suffix
- **Voters**: PascalCase + `Voter` suffix
- **Routes**: snake_case with prefix (e.g., `app_login`, `task_index`)

**Method Naming**:
- Controller actions: descriptive verbs (`index`, `create`, `edit`, `delete`)
- Repository methods: `find*`, `get*`, `count*`
- Service methods: descriptive verbs (`sendEmailConfirmation`)

**File Organization**:
- One class per file
- Namespace matches directory structure (`App\Controller\TaskController`)
- Templates match controller structure (`task/index.html.twig` for `TaskController::index`)

### Symfony-Specific Patterns

**Attribute-Based Routing**:
```php
#[Route('/task/{id}', name: 'task_view')]
public function view(Task $task): Response
```

**Attribute-Based Security**:
```php
#[IsGranted('ROLE_USER')]
#[IsGranted('EDIT', subject: 'task')]
```

**Attribute-Based Validation**:
```php
#[Assert\NotBlank]
#[Assert\Email]
private ?string $email = null;
```

**Dependency Injection**:
- Constructor injection for services
- No manual service registration (autowiring enabled)
- Type-hint interfaces when possible

**Template Naming**:
- Match controller structure: `{controller}/{action}.html.twig`
- Partial templates: prefix with underscore (`_form.html.twig`)
- Base layout: `base.html.twig`

### Database Conventions

**Entity Fields**:
- Use `?` nullable types in PHP 8.2+
- Use `DateTimeImmutable` for timestamps
- JSON fields for arrays (`roles`)

**Migrations**:
- Never modify existing migrations
- Always review generated migrations before applying
- Use descriptive class names (auto-generated with timestamp)

**Relationships**:
- Use `orphanRemoval=true` for tight ownership (e.g., User → Tasks)
- Use `cascade=["persist"]` for saving related entities
- Always specify `inversedBy`/`mappedBy` for bidirectional relations

### Form Conventions

**Form Types**:
- One form type per entity
- Use `DataClass` option to bind to entity
- Add validation in entity, not form
- Use form events for complex logic

**Multi-Step Forms** (CraueFormFlowBundle):
- Define steps in `loadStepsConfig()`
- Use validation groups per step
- Implement conditional skip logic in `skip` closure
- Store flow state in session

### Template Conventions

**Twig**:
- Use `{{ }}` for output
- Use `{% %}` for logic (if, for, extends, block)
- Use `{# #}` for comments
- Always escape output (auto-enabled)

**Translation-Ready**:
- Use `{{ 'key'|trans }}` for translatable strings
- Translation files in `translations/` (currently unused)

**Flash Messages**:
```php
// In controller
$this->addFlash('success', 'Task created!');
```
```twig
{# In template #}
{% for message in app.flashes('success') %}
    <div class="alert-success">{{ message }}</div>
{% endfor %}
```

---

## Common Tasks

### Adding a New Feature

**Example: Add a "Category" entity to tasks**

1. **Create entity**:
   ```bash
   php bin/console make:entity Category
   # Add fields: name (string, 255), description (text, nullable)

   php bin/console make:entity Task
   # Add field: category (relation, ManyToOne, Category)
   ```

2. **Generate migration**:
   ```bash
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate
   ```

3. **Create form type**:
   ```bash
   php bin/console make:form CategoryType
   ```

4. **Update TaskType** (`src/Form/TaskType.php`):
   ```php
   $builder
       ->add('name')
       ->add('description')
       ->add('category', EntityType::class, [
           'class' => Category::class,
           'choice_label' => 'name',
       ]);
   ```

5. **Create controller**:
   ```bash
   php bin/console make:crud Category
   ```

6. **Add routes to navbar** (`templates/base.html.twig`)

7. **Write tests**

### Adding Authentication to a Route

**Method 1: Route-level** (all users):
```php
#[Route('/admin', name: 'admin_dashboard')]
#[IsGranted('ROLE_ADMIN')]
public function dashboard(): Response
```

**Method 2: Access control** (in `config/packages/security.yaml`):
```yaml
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/task, roles: ROLE_USER }
```

**Method 3: Voter** (resource-level):
```php
#[IsGranted('EDIT', subject: 'task')]
public function edit(Task $task): Response
```

### Sending Emails

**Async (recommended)**:
```php
// In controller/service
$email = (new Email())
    ->from('noreply@example.com')
    ->to($user->getEmail())
    ->subject('Welcome!')
    ->html($this->renderView('emails/welcome.html.twig'));

$this->mailer->send($email);
// Automatically queued via Messenger (see config/packages/messenger.yaml)
```

**Process queue** (development):
```bash
php bin/console messenger:consume async -vv
```

### Adding a Stimulus Controller

1. **Create controller** (`assets/controllers/my_controller.js`):
   ```javascript
   import { Controller } from '@hotwired/stimulus';

   export default class extends Controller {
       static targets = ['output'];

       connect() {
           console.log('Controller connected!');
       }

       doSomething() {
           this.outputTarget.textContent = 'Hello!';
       }
   }
   ```

2. **Register** (auto-discovered, or add to `controllers.json`)

3. **Use in template**:
   ```twig
   <div data-controller="my">
       <button data-action="click->my#doSomething">Click me</button>
       <div data-my-target="output"></div>
   </div>
   ```

### Debugging

**Dump variables** (in controller):
```php
dump($variable);
dd($variable); // Dump and die
```

**Debug in template**:
```twig
{{ dump(variable) }}
```

**View profiler**:
- Available at bottom of page in dev mode
- Shows queries, performance, events, etc.

**View routes**:
```bash
php bin/console debug:router
```

**View services**:
```bash
php bin/console debug:container
```

**View events**:
```bash
php bin/console debug:event-dispatcher
```

---

## Important Files Reference

### Must Read Before Changes

| File | Purpose | When to Modify |
|------|---------|----------------|
| **config/packages/security.yaml** | Authentication & authorization | Adding roles, changing login, access control |
| **config/services.yaml** | Service container | Registering custom services, changing autowiring |
| **config/packages/doctrine.yaml** | Database configuration | Changing DB connection, naming strategy |
| **config/packages/messenger.yaml** | Async job configuration | Adding new async handlers, changing transports |
| **.env** | Environment variables | NEVER commit secrets here, use .env.local |
| **src/Entity/User.php** | User entity | Adding user fields, changing roles |
| **src/Security/Voter/TaskVoter.php** | Authorization logic | Changing task permissions |
| **templates/base.html.twig** | Base layout | Changing navbar, adding global CSS/JS |

### Configuration Files

- **Framework**: `config/packages/framework.yaml`
- **Routing**: `config/routes.yaml`
- **Twig**: `config/packages/twig.yaml`
- **Validation**: `config/packages/validator.yaml`
- **Translation**: `config/packages/translator.yaml`
- **Mailer**: `config/packages/mailer.yaml`
- **Tailwind**: `config/packages/symfonycasts_tailwind.yaml`
- **Multi-step forms**: `config/packages/craue_form_flow.yaml`

### Test Fixtures

**Load test users**:
```bash
php bin/console doctrine:fixtures:load
```

**Credentials** (see `src/DataFixtures/AppFixtures.php`):
- Admin: `admin@example.com` / `password`
- User: `user@example.com` / `password`

---

## AI Assistant Guidelines

### When Making Changes

1. **Read First**: Always read existing files before modifying
2. **Follow Patterns**: Match existing code style and patterns
3. **Use Makers**: Prefer Symfony Maker commands for generation
4. **Test Changes**: Write/update tests for new features
5. **Validate**: Run `php bin/console lint:twig` and `php bin/console lint:yaml`

### Common Pitfalls to Avoid

❌ **Don't**:
- Modify existing migrations (create new ones)
- Hardcode credentials in .env (use .env.local)
- Skip CSRF protection on forms
- Forget to hash passwords before persisting
- Use `DELETE` in TaskVoter (it's intentionally blocked)
- Mix Task/Candidate entities (they're separate domains)
- Bypass email verification (it's a security feature)

✅ **Do**:
- Use attribute-based routing/validation
- Autowire services (don't register manually)
- Use repository methods for queries
- Add validation to entities
- Use voters for resource-level authorization
- Use lifecycle callbacks for timestamps
- Test database changes with migrations

### Useful Commands Reference

```bash
# Development
symfony server:start                    # Start server
php bin/console cache:clear             # Clear cache
php bin/console debug:router            # List routes
php bin/console debug:container         # List services

# Database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console make:entity
php bin/console make:migration

# Code Generation
php bin/console make:controller
php bin/console make:form
php bin/console make:crud
php bin/console make:voter
php bin/console make:test

# Testing
php bin/phpunit
php bin/phpunit --testdox              # Readable output

# Assets
php bin/console tailwind:build --watch
php bin/console importmap:install

# Async Jobs
php bin/console messenger:consume async
```

---

## Questions or Issues?

- **Symfony Docs**: https://symfony.com/doc/current/index.html
- **Doctrine Docs**: https://www.doctrine-project.org/
- **Stimulus Handbook**: https://stimulus.hotwired.dev/handbook/introduction
- **Tailwind CSS**: https://tailwindcss.com/docs

**Project Maintainer**: This is an educational project. Update this README.md and CLAUDE.md as the project evolves!

---

**Last Updated**: 2025-12-12
**Symfony Version**: 7.1.*
**PHP Version**: 8.2+
