# Task API

REST API для управления задачами на Laravel

## API Endpoints

- `GET /api/tasks` - список задач
- `POST /api/tasks` - создать задачу
- `GET /api/tasks/{id}` - получить задачу
- `PUT /api/tasks/{id}` - обновить задачу
- `DELETE /api/tasks/{id}` - удалить задачу

## Запуск

```bash
docker compose up -d
docker exec laravel_php composer install
docker exec laravel_php php artisan migrate