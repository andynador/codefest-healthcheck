# README #

Проект реализует бота для Телеграма, который получает сообщения от системы мониторинга при недоступности сервисов, с которыми взаимодействует Личный кабинет. 
## Запуск: ##
- Настраиваем апишку бота https://core.telegram.org/bots/api
- Устанавливаем PHP 7.1, SQLite
- Устанавливаем зависимости для проекта ```composer install```
- Создаём в корне файл вида
```
return [
  'telegram' => [
    'apiKey' => '132:112313',
    'botName' => 'my_bot',
  ],
];
```
с указанием доступов к боту
- Запускаем локальный PHP-сервер для обработки запросов: `php -S localhost:8080`
- Запускаем Prometheus в докере:
```
sudo docker run -p 9090:9090 --net="host" -v /var/www/application/codefest-healthcheck/prometheus-conf.yml:/etc/prometheus/prometheus.yml -v /var/www/application/codefest-healthcheck/alert.rules:/etc/prometheus/alert.rules prom/prometheus
```
- Запускаем AlertManager в докере:
```
sudo docker run -p 9093:9093 --net="host" -v /var/www/application/codefest-healthcheck/prometheus-alertmanager-conf.yaml:/etc/alertmanager/config.yml quay.io/prometheus/alertmanager
```
- Подключаемся через Телеграм к боту и знакомимся с его функционалом :)
