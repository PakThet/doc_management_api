setup:
	@make docker-up-build
	@make composer-install
	@make set-permissions
	@make setup-env
	@make generate-key
	@make migrate-fresh-seed

docker-stop:
	docker compose stop

docker-up-build:
	docker compose up -d --build

composer-install:
	docker exec pos-app bash -c "composer install"

composer-update:
	docker exec pos-app bash -c "composer update"

set-permissions:
	docker exec pos-app bash -c "chmod -R 777 /var/www/storage"
	docker exec pos-app bash -c "chmod -R 777 /var/www/bootstrap"

setup-env:
	docker exec pos-app bash -c "cp .env.docker .env"

generate-key:
	docker exec pos-app bash -c "php artisan key:generate"

migrate-fresh-seed:
	docker exec pos-app bash -c "php artisan migrate:fresh --seed"