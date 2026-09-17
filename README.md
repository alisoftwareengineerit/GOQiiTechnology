# Task Manager

A small task management application built with a React frontend and a PHP REST API. The app lets users register, log in, create tasks, edit tasks, filter by status, and manage task ownership with role-based access control.

## Demo Credentials

For quick testing, use the following account:

- Email: `alice@example.com`
- Password: `secret`

## Project Structure

- `frontend/` – React + Vite client application
- `public/` – PHP entry point for the API
- `src/` – PHP controllers and database connection logic
- `migrate.sql` – SQL schema for the database

## Frontend Overview

The frontend is built with React and Vite in the `frontend/` folder.

### Main files

- `frontend/src/App.jsx` – checks for an existing JWT token in localStorage and renders either the login page or the task manager screen
- `frontend/src/api.js` – central API wrapper for HTTP requests to the backend
- `frontend/src/components/Login.jsx` – login form and authentication flow
- `frontend/src/components/TaskManager.jsx` – task listing, filtering, pagination, and delete/update actions
- `frontend/src/components/TaskForm.jsx` – add and edit task form

### Frontend features

- User login using email and password
- JWT token saved in browser storage after successful login
- Task list with filters by status
- Pagination with `limit` and `offset`
- Create, edit, and delete tasks
- Task status update from the list view
- Basic validation and error handling

### Run frontend locally

```bash
cd taskmanager/frontend
npm install
npm run dev
```

The app expects the PHP API to be available at the `VITE_API_BASE_URL` value or defaults to:

```text
http://localhost/ali/GOQiiTechnology/taskmanager/public/index.php
```

## API Overview

The API is implemented in PHP and served from `public/index.php`.

### Core backend files

- `public/index.php` – main router and request dispatcher
- `src/Database.php` – MySQL connection configuration
- `src/AuthController.php` – user registration, login, and JWT verification
- `src/TaskController.php` – task CRUD logic

### Authentication

The API uses JWT-based authentication.

#### Endpoints

- `POST /register` – create a new user
- `POST /login` – authenticate a user and return a JWT token

The token must be sent in the `Authorization` header as:

```http
Authorization: Bearer <token>
```

### Task endpoints

- `GET /tasks` – list tasks for the current user or all tasks for admins
- `POST /tasks` – create a new task
- `PATCH /tasks/{id}` – update a task
- `DELETE /tasks/{id}` – delete a task

### Access rules

- Regular users can only access their own tasks
- Admin users can access all tasks
- Request body uses JSON format
- CORS headers are enabled for browser requests

## Database

The app connects to MySQL using the database defined in `src/Database.php`:

- Host: `localhost`
- Database: `goqiitechnologydb`
- Username: `root`
- Password: empty string

The database schema is defined in `migrate.sql`.

## Typical Flow

1. User opens the frontend and signs in
2. Frontend sends credentials to `/login`
3. API validates the user and returns a JWT token
4. Frontend stores the token and calls protected `/tasks` endpoints
5. Tasks are created, updated, filtered, and deleted through the API

## Notes

- This project is designed for local development and uses a direct MySQL connection.
- For production, secure the JWT secret, database credentials, and environment configuration.
- The frontend and API are intentionally lightweight and easy to extend for additional task features.
