# Budport WordPress Project

## Структура
- `wp-content/themes/` — кастомні теми
- `wp-content/plugins/` — плагіни
- `wp-content/uploads/` — не пушиться (в .gitignore)
- `vendor/`, `node_modules/` — не пушиться

## Робочий процес
- Всі зміни через Pull Request
- Гілка `main` — захищена
- Автоматична перевірка PHP 8.3 синтаксису

## CI/CD
- `.github/workflows/code-check.yml` — перевірка кодуТест workflow
