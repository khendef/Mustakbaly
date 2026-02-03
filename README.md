# Mustakbaly — E-Learning Platform

**Mustakbaly** (Future) is an electronic distance-learning platform that brings together participating organizations responsible for organizing educational programs. Programs are funded by sponsoring entities and can include multiple courses across different domains. Course content is structured into units and lessons, prepared and reviewed by specialists to ensure quality. Successful students can earn certificates with their final grade upon completing required assessments.

---

## Table of Contents

-   [Overview](#overview)
-   [Features & Requirements](#features--requirements)
-   [System Goals](#system-goals)
-   [Tech Stack](#tech-stack)
-   [Prerequisites](#prerequisites)
-   [Installation](#installation)
-   [Running the Project](#running-the-project)
-   [Authentication](#authentication)
-   [Multi-Tenancy Architecture](#multi-tenancy-architecture)
-   [User Management & Roles](#user-management--roles)
-   [Project Structure](#project-structure)
-   [License](#license)

---

## Overview

The system is an **e-learning platform** that provides:

-   **Organizations** that run educational programs funded by donors
-   **Programs** that group courses in various fields
-   **Courses** whose content is organized into **units** and **lessons**
-   **Quality-assured content** prepared and reviewed by specialists
-   **Certificates** for successful students, including their final grade
-   **Pass criteria** defined by instructors (quizzes and assignments); students must pass these to be eligible for a certificate

---

## Features & Requirements

| Area                             | Description                                   |
| -------------------------------- | --------------------------------------------- |
| **User & permission management** | Users, roles, and permissions (RBAC)          |
| **Organizations & programs**     | Multi-tenant organizations and their programs |
| **Learning content**             | Courses, units, lessons, and enrollments      |
| **Certifications**               | Certificate generation and management         |
| **Assessments**                  | Quizzes, attempts, and grading                |

---

## System Goals

-   Create **educational programs** aimed at specific user groups with clear learning objectives
-   Create **courses** with type, description, content outline, expected duration, and budget
-   **Manage learning content** and structure it into units and lessons
-   Create **quizzes and assignments** and track attempts
-   **Generate certificates** when students pass all required assessments for a course

---

## Tech Stack

| Category                   | Technology                                                                                      |
| -------------------------- | ----------------------------------------------------------------------------------------------- |
| **Framework**              | Laravel 12                                                                                      |
| **Authentication**         | [php-open-source-saver/jwt-auth](https://github.com/php-open-source-saver/jwt-auth)             |
| **RBAC**                   | [spatie/laravel-permission](https://spatie.be/docs/laravel-permission)                          |
| **Multi-language**         | [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable)                   |
| **Media & files**          | [spatie/laravel-medialibrary](https://spatie.be/docs/laravel-medialibrary)                      |
| **Soft deletes (cascade)** | [dyrynda/laravel-cascade-soft-deletes](https://github.com/dyrynda/laravel-cascade-soft-deletes) |
| **Real-time**              | Laravel Reverb                                                                                  |
| **Cache & queues**         | [predis/predis](https://github.com/predis/predis) (Redis)                                       |
| **Activity logs**          | [spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog)                        |
| **Phone validation**       | [propaganistas/laravel-phone](https://github.com/propaganistas/laravel-phone)                   |
| **Modular architecture**   | [nwidart/laravel-modules](https://nwidart.com/laravel-modules)                                  |

---

## Prerequisites

Before running the project, ensure your environment has:

| Requirement | Version                                       |
| ----------- | --------------------------------------------- |
| PHP         | ≥ 8.2                                         |
| Composer    | Latest stable                                 |
| MySQL       | ≥ 8.0 (or SQLite for local dev)               |
| Git         | Latest                                        |
| Web server  | Apache / Nginx or Laravel `php artisan serve` |
| Optional    | XAMPP / WAMP / Laravel Sail                   |

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/khendef/Mustakbaly.git
cd Mustakbaly
```

### 2. Install dependencies

```bash
composer install
composer dump-autoload
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configure `.env` with your database (e.g. `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and other services as needed.

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Seed the database (roles & permissions)

```bash
php artisan db:seed --class="Modules\UserManagementModule\Database\Seeders\UserManagementModuleDatabaseSeeder"
```

---

## Running the Project

Start the development server:

```bash
php artisan serve
```

The API will be available at `http://localhost:8000` (or the URL shown in the terminal).

Optional: run the full dev stack (server, queue, logs, Vite) with:

```bash
composer dev
```

---

## Authentication

The API uses **JWT** for authentication.

### Quick test flow

1. **Register**  
   `POST /api/v1/auth/register`  
   Send the required registration fields in the request body.

2. **Login**  
   `POST /api/v1/auth/login`  
   Use the returned token in subsequent requests.

3. **Authenticated requests**  
   Add the JWT in the `Authorization` header:

    ```
    Authorization: Bearer <your-jwt-token>
    ```

### Example endpoints

| Method | Endpoint                | Description                          |
| ------ | ----------------------- | ------------------------------------ |
| POST   | `/api/v1/auth/register` | Register a new user                  |
| POST   | `/api/v1/auth/login`    | Login and get JWT                    |
| POST   | `/api/v1/auth/logout`   | Logout (requires auth)               |
| POST   | `/api/v1/auth/refresh`  | Refresh JWT (requires auth)          |
| GET    | `/api/v1/auth/profile`  | Current user profile (requires auth) |

---

## Multi-Tenancy Architecture

Mustakbaly uses a **multi-layered multi-tenancy** strategy so each organization’s data and access are isolated.

### 1. Contextual routing & identification

-   **Tenant identifier:** The `{organization}` slug or ID in the URL identifies the tenant.
-   **Context injection:** The `GetRequestedOrganization` middleware reads this and injects the current organization into the app context (e.g. `config(['app.current_organization_id' => $org])`).
-   **Parameter handling:** `forgetParameter()` is used so the organization is handled globally instead of in every controller.

### 2. Perimeter security (gates & middleware)

-   **RBAC:** Middleware such as `role:manager` ensures the user has the required role.
-   **Relationship-based checks:** The `manage-organization` gate verifies that the authenticated user is linked to the requested organization (e.g. via a pivot) with the right role (e.g. manager), so only authorized users can manage that organization.

### 3. Global scope (organization scope)

-   **Automatic tenant scoping:** Laravel global scopes apply organization-based filters to queries so that, by default, users only see data belonging to their organization.

### 4. Ownership-based access (OBAC)

-   **Course access scope:** Instructors see only courses they teach; students see only courses they are enrolled in, even when they belong to the same organization.

---

## User Management & Roles

### Data architecture

-   **Central identity:** The `users` table holds authentication and core profile data.
-   **Specialized profiles:** One-to-one profile entities (Instructor, Auditor, Student) are linked via `user_id` for normalization and future role extensions.
-   **Organizations:** Users are linked to organizations (e.g. via pivot) with roles (e.g. manager, technical, super admin).

### Permission model (PBAC)

Access is **permission-based**. Permissions are grouped by domain for fine-grained control.

### Role overview

| Role            | Scope            | Capabilities                                                                   |
| --------------- | ---------------- | ------------------------------------------------------------------------------ |
| **Super Admin** | System-wide      | Full CRUD on all entities, roles, and system configuration                     |
| **Manager**     | Organization     | Manage students, instructors, auditors, and programs within their organization |
| **Instructor**  | Assigned courses | Create and manage units, lessons, quizzes; track student attempts              |
| **Auditor**     | Read-only        | Review educational content for quality assurance                               |
| **Student**     | Consumer         | Browse content, take lessons, manage own quiz attempts and progress            |

---

## Project Structure

The application is built with **modular architecture** (nwidart/laravel-modules). Main modules:

| Module                   | Purpose                                                                    |
| ------------------------ | -------------------------------------------------------------------------- |
| **UserManagementModule** | Auth, users, roles, permissions, instructors, students, auditors, managers |
| **LearningModule**       | Courses, course types, units, lessons, enrollments                         |
| **AssesmentModule**      | Quizzes, questions, options, attempts, answers                             |
| **CertificationModule**  | Certificates and issuance                                                  |
| **OrganizationsModule**  | Organizations, programs, and tenant context                                |
| **ReportingModule**      | Dashboards and reports                                                     |
| **NotificationModule**   | Notifications                                                              |
| **MediaModule**          | Media and file handling                                                    |
| **SystemModule**         | System-wide configuration and utilities                                    |

API routes are versioned under `/api/v1/`. Learning and assessment features are exposed via their respective module routes (e.g. `/api/v1/courses`, `/api/v1/units`, `/api/v1/enrollments`).

---

## License

This project is licensed under the [MIT License](LICENSE).
