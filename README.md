# README #

sudo docker run -p 9090:9090 --net="host" -v /var/www/application/codefest-healthcheck/prometheus-conf.yml:/etc/prometheus/prometheus.yml -v /var/www/application/codefest-healthcheck/alert.rules:/etc/prometheus/alert.rules prom/prometheus

sudo docker run -p 9093:9093 --net="host" -v /var/www/application/codefest-healthcheck/prometheus-alertmanager-conf.yaml:/etc/alertmanager/config.yml quay.io/prometheus/alertmanager

php -S localhost:8080