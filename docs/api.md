# Recruit Connect WP REST API Documentation

## Endpoints

### Get Vacancies
GET /wp-json/recruit-connect/v1/vacancies

Query Parameters:
- page (int): Page number
- per_page (int): Items per page
- category (string): Filter by category
- education (string): Filter by education level
- jobtype (string): Filter by job type
- salary_min (int): Minimum salary
- salary_max (int): Maximum salary
- search (string): Search term

### Get Single Vacancy
GET /wp-json/recruit-connect/v1/vacancies/{id}

### Submit Application
POST /wp-json/recruit-connect/v1/applications

Required Parameters:
- vacancy_id (int)
- first_name (string)
- last_name (string)
- email (string)

Optional Parameters:
- phone (string)
- motivation (string)

### Get Filter Options
GET /wp-json/recruit-connect/v1/filters

Returns available options for:
- Categories
- Education levels
- Job types
- Salary range
